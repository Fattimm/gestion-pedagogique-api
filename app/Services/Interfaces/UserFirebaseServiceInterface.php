<?php

namespace App\Services\Interfaces;

interface UserFirebaseServiceInterface
{
    public function createUser(array $data);
    public function updateUser(string $id, array $data);
    public function deleteUser(string $id);
    public function listUsers(array $filters = []);
}
