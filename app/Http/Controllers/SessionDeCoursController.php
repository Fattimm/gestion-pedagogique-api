<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\SessionDeCours;
use App\Models\Inscription;
use App\Services\SessionDeCoursService;
use App\Http\Requests\StoreSessionDeCoursRequest;

class SessionDeCoursController extends Controller
{
    public function __construct(
        protected SessionDeCoursService $service
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['statut', 'a_valider']);
        return response()->json(['data' => $this->service->lister($filters)], 200);
    }

    public function store(StoreSessionDeCoursRequest $request)
    {
        $result = $this->service->creer($request->validated());
        return response()->json(['message' => $result['message'], 'data' => $result['data'] ?? null], $result['status']);
    }

    public function show(int $id)
    {
        return response()->json(['data' => $this->service->trouver($id)], 200);
    }

    public function update(StoreSessionDeCoursRequest $request, int $id)
    {
        $result = $this->service->modifier($id, $request->validated());
        return response()->json(['message' => $result['message'], 'data' => $result['data'] ?? null], $result['status']);
    }

    public function annuler(int $id)
    {
        $result = $this->service->annuler($id);
        return response()->json(['message' => $result['message'], 'data' => $result['data'] ?? null], $result['status']);
    }

    public function parCours(int $coursId)
    {
        return response()->json(['data' => $this->service->listerParCours($coursId)], 200);
    }

    // Sessions d'un professeur filtrées par période (vue COACH)
    public function parProfesseur(Request $request, int $profId)
    {
        $request->validate([
            'periode' => 'sometimes|in:jour,semaine',
            'date'    => 'sometimes|date',
        ]);

        $query = SessionDeCours::with(['cours.module', 'cours.classes', 'salle'])
            ->whereHas('cours', fn($q) => $q->where('professeur_id', $profId))
            ->where('statut', '!=', 'annulee')
            ->orderBy('date')->orderBy('heure_debut');

        if ($request->filled('periode')) {
            $date = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();
            if ($request->periode === 'jour') {
                $query->whereDate('date', $date);
            } else {
                $query->whereBetween('date', [
                    $date->startOfWeek()->toDateString(),
                    $date->copy()->endOfWeek()->toDateString(),
                ]);
            }
        }

        return response()->json(['data' => $query->get()], 200);
    }

    // Sessions d'un étudiant filtrées par période et/ou module (vue APPRENANT)
    public function parEtudiant(Request $request, int $etudiantId)
    {
        $request->validate([
            'periode'   => 'sometimes|in:jour,semaine',
            'date'      => 'sometimes|date',
            'module_id' => 'sometimes|integer|exists:modules,id',
        ]);

        $classeIds = Inscription::where('etudiant_id', $etudiantId)->pluck('classe_id');

        $query = SessionDeCours::with(['cours.module', 'cours.professeur', 'salle'])
            ->whereHas('cours', function ($q) use ($classeIds, $request) {
                $q->whereHas('classes', fn($q2) => $q2->whereIn('classes.id', $classeIds));
                if ($request->filled('module_id')) {
                    $q->where('module_id', $request->module_id);
                }
            })
            ->where('statut', '!=', 'annulee')
            ->orderBy('date')->orderBy('heure_debut');

        if ($request->filled('periode')) {
            $date = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();
            if ($request->periode === 'jour') {
                $query->whereDate('date', $date);
            } else {
                $query->whereBetween('date', [
                    $date->startOfWeek()->toDateString(),
                    $date->copy()->endOfWeek()->toDateString(),
                ]);
            }
        }

        return response()->json(['data' => $query->get()], 200);
    }

    // Heures du mois d'un professeur filtrées par module (vue COACH / CM)
    public function heuresMois(Request $request, int $profId)
    {
        $request->validate([
            'mois'      => 'sometimes|integer|min:1|max:12',
            'annee'     => 'sometimes|integer|min:2000',
            'module_id' => 'sometimes|integer|exists:modules,id',
        ]);

        $mois  = $request->input('mois', now()->month);
        $annee = $request->input('annee', now()->year);

        $query = SessionDeCours::with(['cours.module'])
            ->whereHas('cours', function ($q) use ($profId, $request) {
                $q->where('professeur_id', $profId);
                if ($request->filled('module_id')) {
                    $q->where('module_id', $request->module_id);
                }
            })
            ->where('statut', 'effectuee')
            ->whereMonth('date', $mois)
            ->whereYear('date', $annee);

        $sessions     = $query->get();
        $totalHeures  = $sessions->sum('nbre_heure');

        return response()->json([
            'data'         => $sessions,
            'total_heures' => $totalHeures,
            'mois'         => $mois,
            'annee'        => $annee,
        ], 200);
    }

    // Vue Attaché : sessions d'un prof du mois avec heures globales/déroulées/restantes
    public function bilanProfesseur(Request $request, int $profId)
    {
        $request->validate([
            'mois'      => 'sometimes|integer|min:1|max:12',
            'annee'     => 'sometimes|integer|min:2000',
            'module_id' => 'sometimes|integer|exists:modules,id',
        ]);

        $mois  = $request->input('mois', now()->month);
        $annee = $request->input('annee', now()->year);

        $coursQuery = \App\Models\Cours::with(['module', 'sessions' => function ($q) use ($mois, $annee) {
            $q->whereMonth('date', $mois)->whereYear('date', $annee);
        }])->where('professeur_id', $profId);

        if ($request->filled('module_id')) {
            $coursQuery->where('module_id', $request->module_id);
        }

        $cours = $coursQuery->get()->map(function ($c) {
            $heuresDeroulees = $c->sessions->where('statut', 'effectuee')->sum('nbre_heure');
            return [
                'cours'             => $c->load('module'),
                'quota_global'      => $c->quota_horaire_global,
                'heures_deroulees'  => $heuresDeroulees,
                'heures_restantes'  => max(0, $c->quota_horaire_global - $c->heuresPlanifiees()),
                'sessions_du_mois'  => $c->sessions,
            ];
        });

        return response()->json(['data' => $cours], 200);
    }
}
