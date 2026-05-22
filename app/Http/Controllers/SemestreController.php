<?php

namespace App\Http\Controllers;

use App\Services\SemestreService;
use App\Http\Requests\StoreSemestreRequest;

class SemestreController extends Controller
{
    public function __construct(
        protected SemestreService $service
    ) {}

    public function index()
    {
        return response()->json(['data' => $this->service->lister()], 200);
    }

    public function store(StoreSemestreRequest $request)
    {
        $result = $this->service->creer($request->validated());
        return response()->json(['message' => $result['message'], 'data' => $result['data'] ?? null], $result['status']);
    }

    public function show(int $id)
    {
        return response()->json(['data' => $this->service->trouver($id)], 200);
    }

    public function update(StoreSemestreRequest $request, int $id)
    {
        $result = $this->service->modifier($id, $request->validated());
        return response()->json(['message' => $result['message'], 'data' => $result['data'] ?? null], $result['status']);
    }

    public function destroy(int $id)
    {
        $result = $this->service->supprimer($id);
        return response()->json(['message' => $result['message']], $result['status']);
    }

    // Semestres d'une année scolaire spécifique
    public function parAnnee(int $anneeId)
    {
        return response()->json(['data' => $this->service->listerParAnnee($anneeId)], 200);
    }
}
