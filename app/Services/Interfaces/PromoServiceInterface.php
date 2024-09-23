<?php

namespace App\Services\Interfaces;

use App\Models\Promo;

interface PromoServiceInterface
{
    public function createPromo(array $data);
    public function updatePromo(array $data, string $id): array;
    public function deletePromo(string $id): bool;
    public function getPromo(string $id): ?Promo;
    public function getAllPromos();
    public function getCurrentPromo(): ?Promo;
    public function getPromoStats(string $id): array;
    public function addReferentielsToPromo(string $promoId, array $referentielIds): void;
    public function removeReferentielsFromPromo(string $promoId, array $referentielIds): void;
    public function updatePromoStatus(string $promoId, string $status): void;
    public function closePromo(string $promoId): void;
    public function getPromoReferentiels($id);
    
}