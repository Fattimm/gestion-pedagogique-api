<?php

namespace App\Repositories\Interfaces;

use App\Models\Promo;
use Illuminate\Database\Eloquent\Collection;

interface PromoRepositoryInterface
{
    public function create(array $data): Promo;
    public function update(Promo $promo, array $data): Promo;
    public function delete(Promo $promo): bool;
    public function find(string $id): ?Promo;
    public function all(array $filters = []);
    public function getCurrentPromo(): ?Promo;
    public function getPromoStats(string $id): array;
    public function addReferentiels(Promo $promo, array $referentielIds): void;
    public function removeReferentiels(Promo $promo, array $referentielIds): void;
    public function updateStatus(Promo $promo, string $status): void;
    public function closePromo(Promo $promo): void;
    public function getPromoReferentiels($id);
}