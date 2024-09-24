<?php

namespace App\Http\Controllers;

use App\Models\Referentiel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\ReferentielService;

class ReferentielController extends Controller
{
    protected $referentielService;

    public function __construct(ReferentielService $referentielService)
    {
        $this->referentielService = $referentielService;
    }


    public function store(Request $request)
    {
        return $this->referentielService->create($request->all());
    }

    public function index(Request $request)
    {
        // Vérifiez si un filtre est appliqué pour le statut
        $status = $request->query('statut'); // On attend une requête comme ?statut=actif

        // Si un statut est fourni, on récupère les référentiels par statut
        if ($status) {
            $referentiels = $this->referentielService->getReferentielsByStatut($status);
            return response()->json([
                'status' => 200,
                'data' => $referentiels,
                'message' => 'Référentiels filtrés récupérés avec succès.'
            ]);
        }

        // Sinon, on récupère tous les référentiels
        $referentiels = $this->referentielService->getAllReferentiel();
        return response()->json([
            'status' => 200,
            'data' => $referentiels,
            'message' => 'Tous les référentiels récupérés avec succès.'
        ]);
    }


    public function show($id)
    {
        // Récupérer le référentiel par son ID
        $referentiel = $this->referentielService->getReferentiel($id);

        // Vérifier si le référentiel existe
        if (!$referentiel) {
            return response()->json([
                'status' => 404,
                'message' => 'Référentiel non trouvé.'
            ], 404);
        }

        // Récupérer les compétences si demandé
        $competences = [];
        if (request()->query('competences') === 'true') {
            $competences = $this->referentielService->getCompetences($referentiel->id); // Assurez-vous que `id` correspond bien à votre structure

            // Vérifiez si des compétences ont été récupérées
            if (empty($competences)) {
                return response()->json([
                    'status' => 200,
                    'data' => [
                        'referentiel' => $referentiel,
                        'competences' => [], // retourner un tableau vide
                    ],
                    'message' => 'Aucune compétence trouvée pour ce référentiel.'
                ]);
            }
        }

        // Récupérer les modules si demandé
        $modules = [];
        if (request()->query('modules') === 'true') {
            if (!empty($competences)) {
                foreach ($competences as $competence) {
                    // Récupérer les modules pour chaque compétence
                    $modules[$competence['id']] = $this->referentielService->getModules($referentiel->id, $competence['id']);
                }
            }
        }

        // Construire la réponse
        $responseData = [
            'referentiel' => $referentiel,
        ];

        // Ajouter les compétences si elles existent
        if (!empty($competences)) {
            $responseData['competences'] = $competences;
        }

        // Ajouter les modules si ils existent
        if (!empty($modules)) {
            $responseData['modules'] = $modules;
        }

        // Retourner la réponse avec les détails du référentiel, compétences et modules
        return response()->json([
            'status' => 200,
            'data' => $responseData,
            'message' => 'Détails du référentiel récupérés avec succès.'
        ]);
    }

    public function destroy($id)
    {
        return $this->referentielService->softDelete($id);
    }

    public function archive()
    {
        $referentiels = $this->referentielService->getArchivedReferentiels();
        return response()->json([
            'status' => 200,
            'data' => $referentiels,
            'message' => 'Référentiels archivés récupérés avec succès.'
        ]);
    }

    public function update($id, Request $request)
    {
        // Valider les données avant de les envoyer
        $data = $request->all();

        // Appeler la méthode updateReferentiel avec les données et l'ID
        $response = $this->referentielService->updateReferentiel($id, $data);

        return response()->json($response);
    }
}
