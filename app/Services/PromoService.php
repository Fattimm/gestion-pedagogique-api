<?php

namespace App\Services;

use Exception;
use App\Models\Promo;
use App\Enums\PromoStatus;
use Illuminate\Http\UploadedFile;
use App\Repositories\PromoRepository;
use App\Services\Interfaces\PromoServiceInterface;
use Illuminate\Support\Str;


class PromoService implements PromoServiceInterface
{
    protected $promoRepository;
    protected $uploadService;

    public function __construct(PromoRepository $promoRepository, UploadPhotoFirebaseService $uploadService)
    {
        $this->promoRepository = $promoRepository;
        $this->uploadService = $uploadService;
    }

    public function createPromo(array $data)
{
    // Initialize photo URL
    $photoUrl = null;

    // Upload the photo if provided
    if (isset($data['photo_couverture']) && $data['photo_couverture'] instanceof UploadedFile) {
        $uploadService = new UploadPhotoFirebaseService(app('firebase.storage'));
        $photoUrl = $uploadService->uploadPhoto($data['photo_couverture']);
        $data['photo_couverture'] = $photoUrl; // Add the URL to the data
    }

    // Generate a unique ID for the promotion
    $data['id'] = Str::uuid()->toString();

    // Create the promotion
    try {
        $promo = $this->promoRepository->create($data);

        // If active referentiels are provided, assign them to the promotion
        if (isset($data['referentiels']) && is_array($data['referentiels'])) {
            $this->promoRepository->addReferentiels($promo, $data['referentiels']);
        }

        return [
            'status' => 201,
            'data' => $promo,
            'message' => 'Promotion créée avec succès.'
        ];
    } catch (Exception $e) {
        return [
            'status' => 500,
            'message' => 'Erreur lors de la création de la promotion : ' . $e->getMessage()
        ];
    }
}


public function updatePromo(array $data, string $id): array 
{
    // Vérifiez si une photo de couverture est fournie et téléchargez-la si c'est le cas
    if (isset($data['photo_couverture']) && $data['photo_couverture'] instanceof UploadedFile) {
        $uploadService = new UploadPhotoFirebaseService(app('firebase.storage'));
        $photoUrl = $uploadService->uploadPhoto($data['photo_couverture']);
        $data['photo_couverture'] = $photoUrl; // Mettez à jour l'URL de la photo
    }
    
    try {
        // Trouvez la promotion existante par ID dans Firestore
        $promo = $this->promoRepository->find($id);
        if (!$promo) {
            return [
                'status' => 404, 
                'message' => 'Promotion not found.'
            ];
        }

        // Mettez à jour les propriétés de la promotion
        foreach ($data as $key => $value) {
            // Assurez-vous que la clé existe dans l'objet promo
            if (property_exists($promo, $key)) {
                $promo->$key = $value;
            }
        }

        // Convertir l'objet promo en tableau associatif
        $promoData = json_decode(json_encode($promo), true);

        // Enregistrez les modifications dans Firestore
        $this->promoRepository->update($promo, $promoData);

        return [
            'status' => 200,
            'data' => $promoData,
            'message' => 'Promotion updated successfully.'
        ];
    } catch (Exception $e) {
        return [
            'status' => 500,
            'message' => 'Error updating promotion: ' . $e->getMessage()
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
