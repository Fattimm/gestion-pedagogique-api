<?php

namespace App\Repositories\Interfaces;

interface SemestreRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function findByAnnee(int $anneeId);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}
