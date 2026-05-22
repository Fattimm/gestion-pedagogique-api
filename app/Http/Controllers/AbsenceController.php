<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absence;

class AbsenceController extends Controller
{
    // Absences d'un étudiant (avec total heures par semestre)
    public function parEtudiant(Request $request, int $etudiantId)
    {
        $request->validate([
            'semestre_id' => 'sometimes|integer|exists:semestres,id',
        ]);

        $query = Absence::with(['session.cours.module', 'session.cours.semestre'])
            ->where('etudiant_id', $etudiantId);

        if ($request->filled('semestre_id')) {
            $query->whereHas('session.cours', fn($q) => $q->where('semestre_id', $request->semestre_id));
        }

        $absences = $query->get();

        $totalHeures      = $absences->sum('nbre_heure');
        $heuresJustifiees = $absences->where('statut_justification', 'acceptee')->sum('nbre_heure');
        $heuresComptees   = $absences->where('statut_justification', '!=', 'acceptee')->sum('nbre_heure');

        return response()->json([
            'data'              => $absences,
            'total_heures'      => $totalHeures,
            'heures_justifiees' => $heuresJustifiees,
            'heures_comptees'   => $heuresComptees,
        ], 200);
    }

    // Un étudiant justifie son absence
    public function justifier(Request $request, int $absenceId)
    {
        $request->validate([
            'motif' => 'required|string|max:500',
            'date'  => 'required|date',
        ]);

        $absence = Absence::findOrFail($absenceId);

        if ($absence->statut_justification !== 'non_justifiee') {
            return response()->json(['message' => 'Cette absence a déjà été justifiée.'], 422);
        }

        $absence->update([
            'justification_motif' => $request->motif,
            'justification_date'  => $request->date,
            'statut_justification' => 'en_attente',
        ]);

        return response()->json(['message' => 'Justification soumise.', 'data' => $absence], 200);
    }

    // L'attaché traite une justification
    public function traiter(Request $request, int $absenceId)
    {
        $request->validate([
            'statut'     => 'required|in:acceptee,refusee',
            'attache_id' => 'required|integer|exists:users,id',
        ]);

        $absence = Absence::findOrFail($absenceId);

        if ($absence->statut_justification !== 'en_attente') {
            return response()->json(['message' => 'Aucune justification en attente pour cette absence.'], 422);
        }

        $absence->update([
            'statut_justification' => $request->statut,
            'traite_par'           => $request->attache_id,
            'traite_at'            => now(),
        ]);

        return response()->json(['message' => "Justification {$request->statut}.", 'data' => $absence], 200);
    }

    // L'attaché liste les absences d'un prof par mois avec filtre module
    public function parProfesseur(Request $request, int $professeurId)
    {
        $request->validate([
            'mois'      => 'sometimes|integer|min:1|max:12',
            'annee'     => 'sometimes|integer|min:2000',
            'module_id' => 'sometimes|integer|exists:modules,id',
        ]);

        $query = Absence::with(['etudiant', 'session.cours.module'])
            ->whereHas('session.cours', fn($q) => $q->where('professeur_id', $professeurId));

        if ($request->filled('mois') && $request->filled('annee')) {
            $query->whereMonth('date', $request->mois)->whereYear('date', $request->annee);
        }

        if ($request->filled('module_id')) {
            $query->whereHas('session.cours', fn($q) => $q->where('module_id', $request->module_id));
        }

        return response()->json(['data' => $query->get()], 200);
    }
}
