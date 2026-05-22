<?php

namespace App\Http\Controllers;

use App\Services\ModuleService;
use App\Http\Requests\StoreModuleRequest;

class ModuleController extends Controller
{
    public function __construct(
        protected ModuleService $service
    ) {}

    public function index()
    {
        return response()->json(['data' => $this->service->lister()], 200);
    }

    public function store(StoreModuleRequest $request)
    {
        $result = $this->service->creer($request->validated());
        return response()->json(['message' => $result['message'], 'data' => $result['data'] ?? null], $result['status']);
    }

    public function show(int $id)
    {
        return response()->json(['data' => $this->service->trouver($id)], 200);
    }

    public function update(StoreModuleRequest $request, int $id)
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
