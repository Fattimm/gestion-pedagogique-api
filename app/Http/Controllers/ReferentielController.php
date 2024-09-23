<?php

namespace App\Http\Controllers;

use App\Services\ReferentielService;
use Illuminate\Support\Facades\Request;

class ReferentielController extends Controller
{
    protected $referentielService;

    public function __construct(ReferentielService $referentielService)
    {
        $this->referentielService = $referentielService;
    }

    public function index()
    {
        return $this->referentielService->listerReferentielsActifs();
    }

    public function store(Request $request)
    {
        // Validez les données ici
        return $this->referentielService->creerReferentiel($request->all());
    }

    // Ajoutez d'autres méthodes pour show, update, destroy...
}
