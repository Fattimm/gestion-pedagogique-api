<?php

namespace App\Http\Controllers;

use App\Services\ClasseService;
use App\Http\Requests\StoreClasseRequest;

class ClasseController extends Controller
{
    public function __construct(
        protected ClasseService $service
    ) {}

    public function index()
    {
        return response()->json(['data' => $this->service->lister()], 200);
    }

    public function store(StoreClasseRequest $request)
    {
        $result = $this->service->creer($request->validated());
        return response()->json(['message' => $result['message'], 'data' => $result['data'] ?? null], $result['status']);
    }

    public function show(int $id)
    {
        return response()->json(['data' => $this->service->trouver($id)], 200);
    }

    public function update(StoreClasseRequest $request, int $id)
    {
        $result = $this->service->modifier($id, $request->validated());
        return response()->json(['message' => $result['message'], 'data' => $result['data'] ?? null], $result['status']);
    }

    public function destroy(int $id)
    {
        $result = $this->service->supprimer($id);
        return response()->json(['message' => $result['message']], $result['status']);
    }
}
