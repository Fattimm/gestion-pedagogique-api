<?php

namespace App\Repositories\Interfaces;

interface SessionDeCoursRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function findByCours(int $coursId);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function professeurOccupe(int $professeurId, string $date, string $heureDebut, string $heureFin, ?int $excludeId = null): bool;
    public function salleOccupee(int $salleId, string $date, string $heureDebut, string $heureFin, ?int $excludeId = null): bool;
    public function heuresPlanifieesParCours(int $coursId): float;
}
