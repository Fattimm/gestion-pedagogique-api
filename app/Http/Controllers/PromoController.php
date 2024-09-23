<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\PromoServiceInterface;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    protected $promoService;

    public function __construct(PromoServiceInterface $promoService)
    {
        $this->promoService = $promoService;
    }

    public function store()
    {
        $data = request()->all(); // Récupérer les données de la requête

        $response = $this->promoService->createPromo($data);

        return response()->json($response, $response['status']);
    }

    public function update(Request $request, $id)
    {
        // Récupérer les données de la requête sous forme de tableau
        $data = $request->all();

        // Vérifier que les données sont bien un tableau
        if (!is_array($data)) {
            return response()->json(['status' => 400, 'message' => 'Invalid data format.'], 400);
        }

        // Appeler la méthode updatePromo avec les données et l'ID
        $response = $this->promoService->updatePromo($data, $id);

        return response()->json($response);
    }


    public function updateReferentiels(Request $request, $id)
    {
        $validatedData = $request->validate([
            'add' => 'array',
            'remove' => 'array'
        ]);

        if (isset($validatedData['add'])) {
            $this->promoService->addReferentielsToPromo($id, $validatedData['add']);
        }
        if (isset($validatedData['remove'])) {
            $this->promoService->removeReferentielsFromPromo($id, $validatedData['remove']);
        }

        return response()->json(['message' => 'Referentiels updated successfully']);
    }

    public function updateStatus(Request $request, $id)
    {
        $validatedData = $request->validate([
            'etat' => 'required|in:Actif,Inactif,Cloturer'
        ]);

        $this->promoService->updatePromoStatus($id, $validatedData['etat']);
        return response()->json(['message' => 'Status updated successfully']);
    }

    public function index()
    {
        $response = $this->promoService->getAllPromos();

        return response()->json($response, $response['status']);
    }
    
    public function getCurrentPromo()
    {
        $promo = $this->promoService->getCurrentPromo();
        return response()->json($promo);
    }

    public function getStats($id)
    {
        $stats = $this->promoService->getPromoStats($id);
        return response()->json($stats);
    }

    public function closePromo($id)
    {
        $this->promoService->closePromo($id);
        return response()->json(['message' => 'Promotion closed successfully']);
    }

    public function getReferentiels($id)
    {
        $referentiels = $this->promoService->getPromoReferentiels($id);
        return response()->json($referentiels);
    }
}
