<?php

namespace App\Services;

use Exception;
use App\Models\Referentiel;
use App\Enums\StatutReferentiel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use App\Services\Interfaces\ReferentielServiceInterface;
use App\Repositories\Interfaces\ReferentielRepositoryInterface;

class ReferentielService implements ReferentielServiceInterface
{
    protected $repository;
    protected $uploadService;
    protected $firestore;
    protected $firebaseCollection = 'referentiels';


    public function __construct(ReferentielRepositoryInterface $repository, UploadPhotoFirebaseService $uploadService)
    {
        $this->repository = $repository;
        $this->uploadService = $uploadService;
        $this->firestore = app('firebase.firestore')->database(); // Initialisation de Firestore

    }

    private function validateData(array $data)
    {
        $requiredFields = ['code', 'libelle', 'description'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                Log::warning("Champ requis manquant : $field");
                throw new \InvalidArgumentException("Le champ $field est requis.");
            }
        }
    }

    public function create(array $data): Referentiel
    {
        // Validate the incoming data
        $this->validateData($data);

        DB::beginTransaction();
        try {
            Log::info('Début de la création du référentiel');

            // Check for uniqueness of code and libelle
            if ($this->repository->findByCodeOrLibelle($data['code'], $data['libelle'])) {
                throw new Exception('Le code ou le libellé existe déjà.');
            }

            // Handle photo file
            $photoUrl = null; // Initialiser l'URL de la photo

            if (isset($data['photo']) && $data['photo'] instanceof \Illuminate\Http\UploadedFile) {
                // Stocker la photo localement
                $path = $data['photo']->store('referentiels/photos', 'public');

                // Upload la photo sur Firebase
                $photoUrl = $this->uploadService->uploadPhoto($data['photo'], 'referentiels/photos', 'referentiel', $data['libelle']);

                // Mettre à jour le chemin d'accès de la photo dans les données
                $data['photo'] = asset("storage/{$path}");
            }

            // Create the referentiel in the repository
            $referentiel = $this->repository->create($data);

            // Prepare data structure for Firestore
            $firestoreData = [
                'libelle' => $referentiel->libelle,
                'description' => $referentiel->description,
                'code' => $referentiel->code,
                'photo' => $referentiel->photo,
                'statut' => 'inactif',
                'competences' => []
            ];

            // Decode the 'types' JSON string into an array
            $types = json_decode($data['types'], true);

            // Check if decoding was successful
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Erreur de décodage JSON pour les types: ' . json_last_error_msg());
            }

            // Iterate through the types (Backend, Frontend, etc.)
            foreach ($types as $type => $competences) {
                $competenceDataList = [];
                foreach ($competences as $competenceData) {
                    // Create competence
                    $competenceId = $this->repository->addCompetence($referentiel->getId(), $type, $competenceData);
                    $competenceDataList[] = [
                        'id' => $competenceId,
                        'nom' => $competenceData['nom'],
                        'description' => $competenceData['description'],
                        'duree_aquisition' => $competenceData['duree_aquisition'],
                        'modules' => [] // Initialize an empty list for modules
                    ];

                    // Add associated modules to the competence
                    if (!empty($competenceData['modules'])) {
                        foreach ($competenceData['modules'] as $moduleData) {
                            // Call addModule with the correct parameters
                            $moduleId = $this->repository->addModule(
                                $referentiel->getId(),  // The ID of the referentiel
                                $type,                  // The type of competence
                                $competenceId,         // The competence ID
                                $moduleData            // The module data
                            );

                            // Add module information to the last competence in the list
                            $competenceDataList[count($competenceDataList) - 1]['modules'][] = [
                                'id' => $moduleId,
                                'nom' => $moduleData['nom'],
                                'description' => $moduleData['description'],
                                'duree_aquisition' => $moduleData['duree_aquisition'],
                            ];
                        }
                    }
                }

                // Add the competence data to the firestore data
                $firestoreData['competences'][$type] = $competenceDataList;
            }

            // Use a valid document name in Firestore (avoiding slashes)
            $documentName = preg_replace('/[\/\\\]/', '_', $referentiel->libelle);
            $this->firestore->collection($this->firebaseCollection)->document($documentName)->set($firestoreData);

            DB::commit();
            return $referentiel;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du référentiel : ' . $e->getMessage());
            throw $e;
        }
    }


    public function all(): Collection
    {
        Log::info('Récupération de tous les référentiels depuis Firestore');

        // Assurez-vous que vous avez initialisé Firestore correctement
        $snapshot = $this->repository->getAllFromFirestore(); // Si vous avez une méthode dédiée dans le repository
        $referentiels = new Collection();

        foreach ($snapshot as $document) {
            if ($document->exists()) {
                $data = $document->data();
                $referentiel = new Referentiel($data);
                $referentiel->setId($document->id());
                $referentiels->push($referentiel);
            }
        }

        Log::info('Nombre de référentiels récupérés : ' . $referentiels->count());

        return $referentiels;
    }


    public function softDelete($id)
    {
        try {
            Log::info('Début de la suppression logique du référentiel', ['id' => $id]);
            $this->repository->softDelete($id);
            Log::info('Fin de la suppression logique du référentiel', ['id' => $id]);
        } catch (Exception $e) {
            Log::error('Erreur lors de la suppression logique du référentiel : ' . $e->getMessage(), [
                'exception' => $e,
                'id' => $id
            ]);
            throw $e;
        }
    }



    public function getAllReferentiel()
    {
        return $this->repository->all();
    }

    public function getReferentiel($id)
    {
        return $this->repository->find($id);
    }

    public function getReferentielsByStatut($etat): Collection
    {
        Log::info('Récupération des référentiels avec statut : ' . $etat);
        return $this->repository->findByStatut($etat);
    }


    public function getCompetences($referentielId): array
    {
        return $this->repository->getCompetences($referentielId);
    }

    public function getModules($referentielId, $competenceId): array
    {
        return $this->repository->getModules($referentielId, $competenceId);
    }

    public function getArchivedReferentiels(): Collection
    {
        // Récupération des référentiels ayant le statut "archivé"
        return $this->repository->findByStatut('archivé');
    }


    public function updateReferentiel(string $id, array $data): Referentiel
    {
        DB::beginTransaction();
        try {
            Log::info("Updating referentiel with ID: $id");

            // Récupérer le référentiel depuis Firestore
            $referentiel = $this->repository->find($id);
            if (!$referentiel) {
                throw new Exception("Referentiel not found.");
            }

            // Mettre à jour le référentiel (document principal)
            $this->repository->update($id, $data);

            // Ajouter les compétences et leurs modules
            if (!empty($data['competences'])) {
                foreach ($data['competences'] as $competenceData) {
                    $type = $competenceData['type'] ?? 'default_type';

                    // Ajout ou mise à jour de la compétence
                    $competenceId = $this->repository->addCompetence($id, $type, $competenceData);

                    // Ajouter ou mettre à jour les modules
                    if (!empty($competenceData['modules'])) {
                        foreach ($competenceData['modules'] as $moduleData) {
                            $this->repository->addModule($id, $type, $competenceId, $moduleData);
                        }
                    }
                }
            }

            // Suppression logique des compétences
            if (!empty($data['removed_competences'])) {
                foreach ($data['removed_competences'] as $competenceId) {
                    $this->repository->softDeleteCompetence($id, $competenceId);
                }
            }

            // Suppression logique des modules
            if (!empty($data['removed_modules'])) {
                foreach ($data['removed_modules'] as $moduleData) {
                    $this->repository->softDeleteModule($id, $moduleData['competence_id'], $moduleData['module_id']);
                }
            }

            DB::commit();
            return $referentiel;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating referentiel: ' . $e->getMessage());
            throw $e;
        }
    }
}
