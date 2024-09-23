<?php

namespace App\Repositories;

use App\Models\Promo;
use App\Repositories\Interfaces\PromoRepositoryInterface;
use Illuminate\Support\Collection;
use Kreait\Laravel\Firebase\Facades\Firebase;

class PromoRepository implements PromoRepositoryInterface
{
    protected $firestore;
    protected $collection = 'promotions';

    public function __construct()
    {
        $this->firestore = Firebase::firestore()->database();
    }

    public function create(array $data): Promo
    {
        // Ajouter le document à Firestore avec l'ID fourni
        $this->firestore->collection($this->collection)->document($data['id'])->set($data);

        // Retourner l'objet Promo avec l'ID généré
        return new Promo($data);
    }


    public function getPromoByLibelle(string $libelle): ?Promo
    {
        $query = $this->firestore->collection($this->collection)
            ->where('libelle', '==', $libelle)
            ->limit(1) // Limiter à 1 pour obtenir au plus un document
            ->documents();

        if ($query->isEmpty()) {
            return null; // Aucune promotion trouvée
        }

        // Récupérer le premier document
        $document = $query->rows()[0];
        return new Promo($document->data() + ['id' => $document->id()]);
    }


    public function update(Promo $promo, array $data): Promo
    {
        // Update the document in Firestore
        $this->firestore->collection($this->collection)->document($promo->id)->set($data, ['merge' => true]);
        return $this->find($promo->id);
    }


    public function delete(Promo $promo): bool
    {
        $this->firestore->collection($this->collection)->document($promo->id)->delete();
        return true;
    }

    public function find(string $id): ?Promo
    {
        $snapshot = $this->firestore->collection($this->collection)->document($id)->snapshot();
        if (!$snapshot->exists()) {
            return null;
        }
        return new Promo($snapshot->data() + ['id' => $snapshot->id()]);
    }


    public function all(array $filters = [])
    {
        $snapshot = $this->firestore->collection($this->collection)->documents();
        
        $users = [];
        foreach ($snapshot as $document) {
            if ($document->exists()) {
                $users[] = array_merge(['id' => $document->id()], $document->data());
            }
        }

        return $users;
    }
    




    public function getCurrentPromo(): ?Promo
    {
        $snapshot = $this->firestore->collection($this->collection)
            ->where('etat', '==', 'Actif')
            ->limit(1)
            ->documents();

        $promo = $snapshot->rows();
        if (empty($promo)) {
            return null;
        }
        return new Promo($promo[0]->data() + ['id' => $promo[0]->id()]);
    }

    public function getPromoStats(string $id): array
    {
        $promo = $this->find($id);
        if (!$promo) {
            throw new \Exception("Promotion not found");
        }

        // Fetch related data (referentiels and users) from Firebase
        $referentiels = $this->firestore->collection('referentiels')
            ->where('promo_id', '==', $id)
            ->documents();

        $users = $this->firestore->collection('users')
            ->where('promo_id', '==', $id)
            ->documents();

        $usersCollection = collect($users->rows())->map(function ($document) {
            return $document->data() + ['id' => $document->id()];
        });

        return [
            'info' => $promo->toArray(),
            'nombre_apprenants' => $usersCollection->count(),
            'nombre_apprenants_actifs' => $usersCollection->where('etat', 'Actif')->count(),
            'nombre_apprenants_inactifs' => $usersCollection->where('etat', 'Inactif')->count(),
            'referentiels' => collect($referentiels->rows())->map(function ($document) use ($usersCollection) {
                $referentielData = $document->data() + ['id' => $document->id()];
                return [
                    'id' => $referentielData['id'],
                    'libelle' => $referentielData['libelle'],
                    'nombre_apprenants' => $usersCollection->where('referentiel_id', $referentielData['id'])->count()
                ];
            })
        ];
    }

    public function addReferentiels(Promo $promo, array $referentielIds): void
    {
        $promoRef = $this->firestore->collection($this->collection)->document($promo->id);
        $promoRef->update([
            ['path' => 'referentiels', 'value' => array_merge($promo->referentiels ?? [], $referentielIds)]
        ]);
    }

    public function removeReferentiels(Promo $promo, array $referentielIds): void
    {
        $currentReferentiels = $promo->referentiels ?? [];
        $updatedReferentiels = array_diff($currentReferentiels, $referentielIds);

        $promoRef = $this->firestore->collection($this->collection)->document($promo->id);
        $promoRef->update([
            ['path' => 'referentiels', 'value' => $updatedReferentiels]
        ]);
    }

    public function updateStatus(Promo $promo, string $status): void
    {
        $this->firestore->collection($this->collection)->document($promo->id)->update([
            ['path' => 'etat', 'value' => $status]
        ]);
    }

    public function closePromo(Promo $promo): void
    {
        $this->updateStatus($promo, 'Cloturer');
        // Add logic here to send report cards
    }

    public function getPromoReferentiels($id)
    {
        $promo = $this->find($id);
        if (!$promo) {
            throw new \Exception("Promotion not found");
        }
        return $promo->referentiels;
    }
}
