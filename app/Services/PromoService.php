<?php

namespace App\Services;

use Exception;
use App\Models\Promo;
use App\Enums\PromoStatus;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use App\Repositories\PromoRepository;
use App\Repositories\ReferentielRepository;
use App\Services\Interfaces\PromoServiceInterface;


class PromoService implements PromoServiceInterface
{
    protected $promoRepository;
    protected $uploadService;
    protected $repository;

    public function __construct(PromoRepository $promoRepository, UploadPhotoFirebaseService $uploadService, ReferentielRepository $repository)
    {
        $this->promoRepository = $promoRepository;
        $this->uploadService = $uploadService;
        $this->repository = $repository;
    }


    public function createPromo(array $data)
    {
        // Initialize photo URL
        $photoUrl = null;

        // Upload the photo if provided
        if (isset($data['photo_couverture']) && $data['photo_couverture'] instanceof UploadedFile) {
            $photoUrl = $this->uploadService->uploadPhoto(
                $data['photo_couverture'],
                'promotions',
                'promotion',
                $data['libelle']
            );
            $data['photo_couverture'] = $photoUrl;
        }

        // Validate required fields
        if (empty($data['libelle'])) {
            return [
                'status' => 400,
                'message' => 'Le libellé est obligatoire.'
            ];
        }
        // Ensure the ID (libelle) is unique
        if ($this->promoExists($data['libelle'])) {
            return [
                'status' => 409,
                'message' => 'Une promotion avec ce libellé existe déjà.'
            ];
        }

        if (empty($data['date_debut']) || empty($data['date_fin'])) {
            return [
                'status' => 400,
                'message' => 'Les dates de début et de fin sont obligatoires.'
            ];
        }


        // Calculate duration in months
        $dateDebut = new \DateTime($data['date_debut']);
        $dateFin = new \DateTime($data['date_fin']);
        $duration = $dateDebut->diff($dateFin)->m + ($dateDebut->diff($dateFin)->y * 12);
        $data['duree'] = $duration . ' mois';

        // **Retrieve the last existing ID and increment it**
        $lastPromo = $this->promoRepository->getLastPromo();  // Assuming `getLastPromo()` is a method that returns the last promotion sorted by ID.

        $newId = $lastPromo ? $lastPromo['id'] + 1 : 1;  // Start from 1 if no promotions exist
        // Set the new ID in the data
        $data['id'] = $newId;

        // Set the initial state to "Inactif"
        $data['etat'] = 'Inactif';

        // Check for the referentiel IDs
        $referentielIds = $data['referentiels'] ?? [];

        // Ensure $referentielIds is an array
        if (!is_array($referentielIds)) {
            $referentielIds = [$referentielIds]; // Convertir en tableau si c'est une chaîne
        }

        // Validate the referentiels and prepare the mapping for competencies
        $referentielsData = [];
        foreach ($referentielIds as $referentielId) {
            if (!$this->referentielExists($referentielId)) {
                return [
                    'status' => 404,
                    'message' => "Le référentiel avec l'ID $referentielId n'existe pas."
                ];
            }

            // Récupérer les données du référentiel
            $referentielData = $this->repository->find($referentielId);
            Log::info("Données du référentiel : " . json_encode($referentielData));

            if ($referentielData) {
                // Récupérer les compétences associées
                $competences = $this->repository->getCompetencesByReferentielId($referentielId);
                Log::info("Compétences récupérées : " . json_encode($competences));

                // Vérifiez ici si les compétences sont récupérées correctement
                if (empty($competences)) {
                    Log::warning("Aucune compétence trouvée pour le référentiel ID: $referentielId");
                }

                // Transformer l'objet Référentiel en tableau associatif
                $referentielArray = [
                    'id' => $referentielData->getId(),
                    'libelle' => $referentielData->libelle,
                    'description' => $referentielData->description,
                    'competences' => []
                ];

                // Structurer les compétences comme dans la méthode de création du référentiel
                foreach ($competences as $type => $typeCompetences) {
                    $competenceDataList = [];
                    foreach ($typeCompetences as $competence) {
                        $competenceData = [
                            'id' => $competence['id'],
                            'nom' => $competence['nom'],
                            'description' => $competence['description'],
                            'duree_aquisition' => $competence['duree_aquisition'],
                            'modules' => $competence['modules'] ?? []
                        ];
                        $competenceDataList[] = $competenceData;
                    }
                    $referentielArray['competences'][$type] = $competenceDataList;
                }

                $referentielsData[] = $referentielArray;
            }
        }

        // Create the promotion in Firestore
        try {
            $promoRef = $this->promoRepository->create([
                'id' =>  $data['id'],
                'libelle' => $data['libelle'],
                'date_debut' => $data['date_debut'],
                'date_fin' => $data['date_fin'],
                'duree' => $data['duree'],
                'etat' => $data['etat'],
                'photo_couverture' => $photoUrl,
                'referentiels' => $referentielsData
            ]);

            Log::info("Promotion créée avec succès : " . json_encode($promoRef));

            return [
                'status' => 201,
                'data' => $promoRef,
                'message' => 'Promotion créée avec succès.'
            ];
        } catch (Exception $e) {
            Log::error('Erreur lors de la création de la promotion : ' . $e->getMessage());
            return [
                'status' => 500,
                'message' => 'Erreur lors de la création de la promotion : ' . $e->getMessage()
            ];
        }
    }


    protected function promoExists($libelle)
    {
        // Vérifiez si la promotion existe dans la base de données
        return $this->promoRepository->existsByLibelle($libelle);
    }

    // Méthode pour vérifier l'existence d'un référentiel
    private function referentielExists($referentielId)
    {
        return $this->repository->find($referentielId) !== null;
    }


    public function updatePromo(array $data, string $id): array
    {
        try {
            // Trouver la promotion existante par ID
            $promo = $this->promoRepository->find($id);
            if (!$promo) {
                return [
                    'status' => 404,
                    'message' => 'Promotion non trouvée.'
                ];
            }

            // Vérifier si une nouvelle photo de couverture est fournie
            if (isset($data['photo_couverture']) && $data['photo_couverture'] instanceof UploadedFile) {
                $libelle = isset($data['libelle']) ? $data['libelle'] : $promo->libelle;

                // Téléchargement de la nouvelle photo
                $photoUrl = $this->uploadService->uploadPhoto(
                    $data['photo_couverture'],
                    'promotions',
                    'promotion',
                    $libelle
                );

                // Remplacer l'ancienne photo
                $promo->photo_couverture = $photoUrl;
            }

            // Mise à jour des autres champs (libelle, dates, etc.)
            if (isset($data['libelle'])) {
                if (!$this->promoExists($data['libelle'])) {
                    $promo->libelle = $data['libelle'];
                } else {
                    return [
                        'status' => 409,
                        'message' => 'Une promotion avec ce libellé existe déjà.'
                    ];
                }
            }

            if (isset($data['date_debut'])) {
                $promo->date_debut = $data['date_debut'];
            }

            if (isset($data['date_fin'])) {
                $promo->date_fin = $data['date_fin'];

                // Recalculer la durée si les deux dates sont disponibles
                if ($promo->date_debut) {
                    $dateDebut = new \DateTime($promo->date_debut);
                    $dateFin = new \DateTime($promo->date_fin);
                    $duration = $dateDebut->diff($dateFin)->m + ($dateDebut->diff($dateFin)->y * 12);
                    $promo->duree = $duration . ' mois';
                }
            }

            // Si des référentiels sont fournis, mettre à jour
            if (isset($data['referentiels'])) {
                $referentielIds = is_array($data['referentiels']) ? $data['referentiels'] : [$data['referentiels']];

                $referentielsData = [];
                foreach ($referentielIds as $referentielId) {
                    if ($this->referentielExists($referentielId)) {
                        $referentielData = $this->repository->find($referentielId);
                        $competences = $this->repository->getCompetencesByReferentielId($referentielId);

                        $referentielArray = [
                            'id' => $referentielData->getId(),
                            'libelle' => $referentielData->libelle,
                            'description' => $referentielData->description,
                            'competences' => []
                        ];

                        foreach ($competences as $type => $typeCompetences) {
                            $competenceDataList = [];
                            foreach ($typeCompetences as $competence) {
                                $competenceDataList[] = [
                                    'id' => $competence['id'],
                                    'nom' => $competence['nom'],
                                    'description' => $competence['description'],
                                    'duree_aquisition' => $competence['duree_aquisition'],
                                    'modules' => $competence['modules'] ?? []
                                ];
                            }
                            $referentielArray['competences'][$type] = $competenceDataList;
                        }

                        $referentielsData[] = $referentielArray;
                    } else {
                        return [
                            'status' => 404,
                            'message' => "Le référentiel avec l'ID $referentielId n'existe pas."
                        ];
                    }
                }

                // Mettre à jour les référentiels
                $promo->referentiels = $referentielsData;
            }

            // Mise à jour de l'état de la promotion si nécessaire
            if (isset($data['etat'])) {
                $promo->etat = $data['etat'];
            }

            // Convertir l'objet promo en tableau pour la mise à jour
            $promoData = json_decode(json_encode($promo), true);

            // Enregistrer les modifications
            $this->promoRepository->update($promo, $promoData);

            return [
                'status' => 200,
                'data' => $promoData,
                'message' => 'Promotion mise à jour avec succès.'
            ];
        } catch (Exception $e) {
            return [
                'status' => 500,
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
            ];
        }
    }



    public function getAllPromos()
    {
        $promos = $this->promoRepository->all();

        return [
            'status' => 200,
            'data' => $promos,
            'message' => 'Promotions retrieved successfully.'
        ];
    }


    public function updateReferentiels(string $promoId, array $referentiels, string $action)
    {
        // Trouver la promotion existante
        $promo = $this->promoRepository->find($promoId);
        if (!$promo) {
            return [
                'status' => 404,
                'message' => 'Promotion non trouvée.'
            ];
        }

        // Vérifier le rôle de l'utilisateur
        $userRole = auth()->user()->role; // Supposons que le rôle de l'utilisateur est récupéré ainsi
        $canRemoveEmptyReferentiel = $userRole === 'CM' || $userRole === 'Manager';

        foreach ($referentiels as $referentielId) {
            if (!$this->referentielExists($referentielId)) {
                return [
                    'status' => 404,
                    'message' => "Le référentiel avec l'ID $referentielId n'existe pas."
                ];
            }

            // Si l'action est 'remove', vérifier si le référentiel peut être retiré
            if ($action === 'remove') {
                $referentiel = $this->repository->find($referentielId);

                // // Vérifier s'il y a des apprenants associés à ce référentiel
                // $hasStudents = $this->hasStudents($referentielId); // Méthode à implémenter pour vérifier les apprenants
                // if ($hasStudents && $userRole === 'CM') {
                //     return [
                //         'status' => 403,
                //         'message' => "Le référentiel ne peut pas être retiré car il a des apprenants."
                //     ];
                // } elseif (!$hasStudents && !$canRemoveEmptyReferentiel) {
                //     return [
                //         'status' => 403,
                //         'message' => "Le rôle {$userRole} ne peut pas retirer ce référentiel."
                //     ];
                // }

                // Appliquer le soft delete
                $this->repository->softDelete($referentielId);
            }

            // Si l'action est 'add', vérifier que le référentiel n'est pas déjà associé
            if ($action === 'add') {
                if (in_array($referentielId, array_column($promo->referentiels, 'id'))) {
                    return [
                        'status' => 409,
                        'message' => "Le référentiel avec l'ID $referentielId est déjà associé à cette promotion."
                    ];
                }
                // Ajoutez le référentiel à la promotion
                $promo->referentiels[] = $this->repository->find($referentielId);
            }
        }

        // Mettez à jour la promotion
        $this->promoRepository->update($promo, json_decode(json_encode($promo), true));

        return [
            'status' => 200,
            'data' => $promo,
            'message' => 'Référentiels mis à jour avec succès.'
        ];
    }












    public function getCurrentPromo(): ?Promo
    {
        return $this->promoRepository->getCurrentPromo();
    }

    protected function validatePromoData(array $data): void
    {
        // Add validation logic here
    }

    protected function calculateDuration(string $startDate, string $endDate): int
    {
        // Calculate duration in months
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $interval = $start->diff($end);
        return $interval->y * 12 + $interval->m;
    }

    protected function calculateEndDate(string $startDate, int $duration): string
    {
        // Calculate end date based on start date and duration
        $start = new \DateTime($startDate);
        $end = clone $start;
        $end->modify("+$duration months");
        return $end->format('Y-m-d');
    }


    public function deletePromo(string $id): bool
    {
        $promo = $this->promoRepository->find($id);
        if (!$promo) {
            throw new \Exception("Promotion not found");
        }
        return $this->promoRepository->delete($promo);
    }

    public function getPromo(string $id): ?Promo
    {
        return $this->promoRepository->find($id);
    }

    public function getPromoStats(string $id): array
    {
        return $this->promoRepository->getPromoStats($id);
    }

    public function addReferentielsToPromo(string $promoId, array $referentielIds): void
    {
        $promo = $this->promoRepository->find($promoId);
        if (!$promo) {
            throw new \Exception("Promotion not found");
        }
        $this->promoRepository->addReferentiels($promo, $referentielIds);
    }

    public function removeReferentielsFromPromo(string $promoId, array $referentielIds): void
    {
        $promo = $this->promoRepository->find($promoId);
        if (!$promo) {
            throw new \Exception("Promotion not found");
        }
        $this->promoRepository->removeReferentiels($promo, $referentielIds);
    }

    public function updatePromoStatus(string $promoId, string $status): void
    {
        $promo = $this->promoRepository->find($promoId);
        if (!$promo) {
            throw new \Exception("Promotion not found");
        }

        if ($status === PromoStatus::ACTIF->value) {
            $currentActivePromo = $this->getCurrentPromo();
            if ($currentActivePromo && $currentActivePromo->id !== $promoId) {
                throw new \Exception("There is already an active promotion");
            }
        }

        $this->promoRepository->updateStatus($promo, $status);
    }

    public function closePromo(string $promoId): void
    {
        $promo = $this->promoRepository->find($promoId);
        if (!$promo) {
            throw new \Exception("Promotion not found");
        }

        if ($promo->date_fin > now()) {
            throw new \Exception("Cannot close a promotion before its end date");
        }

        $this->promoRepository->closePromo($promo);
    }

    public function getPromoReferentiels($id)
    {
        return $this->promoRepository->getPromoReferentiels($id);
    }
}
