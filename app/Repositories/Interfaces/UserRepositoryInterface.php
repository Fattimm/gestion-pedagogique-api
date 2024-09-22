<?php

namespace App\Repositories\Interfaces;

interface UserRepositoryInterface
{
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function find($id);
    public function All();
    public function findByRole(string $role);


}