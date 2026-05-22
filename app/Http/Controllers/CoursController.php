<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Cours;
use App\Models\Inscription;
use App\Services\CoursService;
use App\Http\Requests\StoreCoursRequest;

class CoursController extends Controller
{
    public function __construct(
        protected CoursService $service
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['statut']);
        return response()->json(['data' => $this->service->lister($filters)], 200);
    }

    public function store(StoreCoursRequest $request)
    {
        $data      = $request->except('classes');
        $classeIds = $request->input('classes', []);
        $result    = $this->service->creer($data, $classeIds);
        return response()->json(['message' => $result['message'], 'data' => $result['data'] ?? null], $result['status']);
    }

    public function show(int $id)
    {
        return response()->json(['data' => $this->service->trouver($id)], 200);
    }

    public function update(StoreCoursRequest $request, int $id)
    {
        $data      = $request->except('classes');
        $classeIds = $request->has('classes') ? $request->input('classes') : null;
        $result    = $this->service->modifier($id, $data, $classeIds);
        return response()->json(['message' => $result['message'], 'data' => $result['data'] ?? null], $result['status']);
    }

    public function destroy(int $id)
    {
        $result = $this->service->supprimer($id);
        return response()->json(['message' => $result['message']], $result['status']);
    }

    // Cours d'un professeur filtrés par période (jour/semaine)
    public function parProfesseur(Request $request, int $profId)
    {
        $request->validate([
            'periode'  => 'sometimes|in:jour,semaine',
            'date'     => 'sometimes|date',
            'statut'   => 'sometimes|in:planifie,en_cours,termine',
        ]);

        $query = Cours::with(['module', 'classes', 'sessions'])
            ->where('professeur_id', $profId);

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('periode')) {
            $date = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();
            $query->whereHas('sessions', function ($q) use ($request, $date) {
                if ($request->periode === 'jour') {
                    $q->whereDate('date', $date);
                } else {
                    $q->whereBetween('date', [$date->startOfWeek(), $date->copy()->endOfWeek()]);
                }
            });
        }

        return response()->json(['data' => $query->get()], 200);
    }

    // Cours d'un étudiant filtrés par période et/ou module
    public function parEtudiant(Request $request, int $etudiantId)
    {
        $request->validate([
            'periode'   => 'sometimes|in:jour,semaine',
            'date'      => 'sometimes|date',
            'module_id' => 'sometimes|integer|exists:modules,id',
        ]);

        // Récupérer les classes de l'étudiant
        $classeIds = Inscription::where('etudiant_id', $etudiantId)->pluck('classe_id');

        $query = Cours::with(['module', 'semestre', 'professeur', 'sessions'])
            ->whereHas('classes', fn($q) => $q->whereIn('classes.id', $classeIds));

        if ($request->filled('module_id')) {
            $query->where('module_id', $request->module_id);
        }

        if ($request->filled('periode')) {
            $date = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();
            $query->whereHas('sessions', function ($q) use ($request, $date) {
                if ($request->periode === 'jour') {
                    $q->whereDate('date', $date);
                } else {
                    $q->whereBetween('date', [$date->startOfWeek(), $date->copy()->endOfWeek()]);
                }
            });
        }

        return response()->json(['data' => $query->get()], 200);
    }

    // Étudiants d'un cours filtrés par classe (vue RP)
    public function etudiants(Request $request, int $coursId)
    {
        $request->validate([
            'classe_id' => 'sometimes|integer|exists:classes,id',
        ]);

        $cours     = Cours::with('classes')->findOrFail($coursId);
        $classeIds = $cours->classes->pluck('id');

        $query = Inscription::with(['etudiant', 'classe'])
            ->whereIn('classe_id', $classeIds);

        if ($request->filled('classe_id')) {
            $query->where('classe_id', $request->classe_id);
        }

        return response()->json(['data' => $query->get()], 200);
    }
}
