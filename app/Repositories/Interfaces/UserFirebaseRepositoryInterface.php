<?php

namespace App\Repositories\Interfaces;

interface UserFirebaseRepositoryInterface
{
    public function store(array $data);
    public function update(string $id, array $data);
    public function delete(string $id);
    public function find(string $id);
    public function all(array $filters = []);
}
