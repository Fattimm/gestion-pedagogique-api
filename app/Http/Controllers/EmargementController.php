<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Emargement;
use App\Models\SessionDeCours;
use App\Models\Absence;
use App\Models\Inscription;
use App\Notifications\AbsenceAvertissementNotification;
use App\Notifications\AbsenceConvocationNotification;

class EmargementController extends Controller
{
    // Un étudiant signe sa présence
    public function signer(Request $request, int $sessionId)
    {
        $request->validate(['etudiant_id' => 'required|integer|exists:users,id']);

        $session = SessionDeCours::findOrFail($sessionId);

        // La liste est disponible 30 min après le début du cours
        $debutDisponibilite = Carbon::parse($session->date->format('Y-m-d') . ' ' . $session->heure_debut)
            ->addMinutes(30);

        if (Carbon::now()->lt($debutDisponibilite)) {
            return response()->json([
                'message' => "La liste d'émargement n'est disponible qu'à partir de {$debutDisponibilite->format('H:i')}."
            ], 403);
        }

        $emargement = Emargement::updateOrCreate(
            ['session_de_cours_id' => $sessionId, 'etudiant_id' => $request->etudiant_id],
            ['signe_a' => Carbon::now(), 'statut' => 'en_attente']
        );

        return response()->json(['message' => 'Présence enregistrée.', 'data' => $emargement], 200);
    }

    // L'attaché valide la session et génère les absences
    public function validerSession(Request $request, int $sessionId)
    {
        $request->validate(['attache_id' => 'required|integer|exists:users,id']);

        $session = SessionDeCours::with('cours.classes')->findOrFail($sessionId);

        // Récupérer tous les étudiants inscrits dans les classes de ce cours
        $classeIds  = $session->cours->classes->pluck('id');
        $etudiantIds = Inscription::whereIn('classe_id', $classeIds)
            ->where('annee_scolaire_id', $session->cours->semestre->annee_scolaire_id)
            ->pluck('etudiant_id');

        // Étudiants ayant signé et validés
        $presents = Emargement::where('session_de_cours_id', $sessionId)
            ->whereIn('statut', ['en_attente', 'valide'])
            ->pluck('etudiant_id');

        // Créer une absence pour chaque absent
        $absents = $etudiantIds->diff($presents);
        foreach ($absents as $etudiantId) {
            $absence = Absence::firstOrCreate([
                'etudiant_id'        => $etudiantId,
                'session_de_cours_id' => $sessionId,
            ], [
                'nbre_heure' => $session->nbre_heure,
                'date'       => $session->date,
            ]);

            // Calcul du total d'heures d'absences non justifiées du semestre
            $semestreId = $session->cours->semestre_id;
            $totalHeures = Absence::where('etudiant_id', $etudiantId)
                ->whereHas('session.cours', fn($q) => $q->where('semestre_id', $semestreId))
                ->where('statut_justification', '!=', 'acceptee')
                ->sum('nbre_heure');

            $etudiant = \App\Models\User::find($etudiantId);
            if ($totalHeures >= 20) {
                $etudiant->notify(new AbsenceConvocationNotification($totalHeures));
            } elseif ($totalHeures >= 10) {
                $etudiant->notify(new AbsenceAvertissementNotification($totalHeures));
            }
        }

        // Valider les émargements de la session
        Emargement::where('session_de_cours_id', $sessionId)
            ->update(['statut' => 'valide', 'valide_par' => $request->attache_id]);

        // Marquer la session comme effectuée
        $session->update(['statut' => 'effectuee']);

        return response()->json([
            'message'        => 'Session validée.',
            'presents'       => $presents->count(),
            'absents_generes' => $absents->count(),
        ], 200);
    }

    // L'attaché invalide (annule) une validation déjà faite
    public function invaliderSession(int $sessionId)
    {
        $session = SessionDeCours::findOrFail($sessionId);

        if ($session->statut !== 'effectuee') {
            return response()->json(['message' => 'Seule une session déjà validée peut être invalidée.'], 422);
        }

        // Supprimer les absences générées par cette session
        Absence::where('session_de_cours_id', $sessionId)->delete();

        // Remettre les émargements à "en_attente"
        Emargement::where('session_de_cours_id', $sessionId)
            ->update(['statut' => 'en_attente', 'valide_par' => null]);

        // Remettre la session à "planifiee"
        $session->update(['statut' => 'planifiee']);

        return response()->json(['message' => 'Validation de la session annulée. Les absences générées ont été supprimées.'], 200);
    }

    // Lister les émargements d'une session
    public function parSession(int $sessionId)
    {
        $emargements = Emargement::with('etudiant')
            ->where('session_de_cours_id', $sessionId)
            ->get();

        return response()->json(['data' => $emargements], 200);
    }
}
