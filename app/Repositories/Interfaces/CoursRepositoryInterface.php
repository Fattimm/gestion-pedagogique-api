<?php

namespace App\Repositories\Interfaces;

interface CoursRepositoryInterface
{
    public function all(array $filters = []);
    public function find(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function attachClasses(int $coursId, array $classeIds): void;
}
