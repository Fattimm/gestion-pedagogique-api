<?php

namespace App\Repositories\Interfaces;

interface AnneeScolaireRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function attachClasse(int $anneeId, int $classeId): void;
    public function detachClasse(int $anneeId, int $classeId): void;
    public function getClassesPlanifiees(int $anneeId);
}
