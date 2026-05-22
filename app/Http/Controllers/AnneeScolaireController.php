<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AnneeScolaireService;
use App\Http\Requests\StoreAnneeScolaireRequest;
use App\Http\Requests\UpdateAnneeScolaireRequest;

class AnneeScolaireController extends Controller
{
    public function __construct(
        protected AnneeScolaireService $service
    ) {}

    public function index()
    {
        $annees = $this->service->lister();
        return response()->json(['data' => $annees], 200);
    }

    public function store(StoreAnneeScolaireRequest $request)
    {
        $result = $this->service->creer($request->validated());
        return response()->json(['message' => $result['message'], 'data' => $result['data'] ?? null], $result['status']);
    }

    public function show(int $id)
    {
        $annee = $this->service->trouver($id);
        return response()->json(['data' => $annee], 200);
    }

    public function update(UpdateAnneeScolaireRequest $request, int $id)
    {
        $result = $this->service->modifier($id, $request->validated());
        return response()->json(['message' => $result['message'], 'data' => $result['data'] ?? null], $result['status']);
    }

    public function destroy(int $id)
    {
        $result = $this->service->supprimer($id);
        return response()->json(['message' => $result['message']], $result['status']);
    }

    public function planifierClasse(Request $request, int $anneeId)
    {
        $request->validate(['classe_id' => 'required|integer|exists:classes,id']);
        $result = $this->service->planifierClasse($anneeId, $request->classe_id);
        return response()->json(['message' => $result['message']], $result['status']);
    }

    public function retirerClasse(int $anneeId, int $classeId)
    {
        $result = $this->service->retirerClasse($anneeId, $classeId);
        return response()->json(['message' => $result['message']], $result['status']);
    }

    public function classesPlanifiees(int $anneeId)
    {
        $classes = $this->service->classesPlanifiees($anneeId);
        return response()->json(['data' => $classes], 200);
    }
}
