<?php

namespace App\Services\Interfaces;

use App\Http\Requests\StoreUserRequest;

interface UserServiceInterface
{
    public function createUser(StoreUserRequest $request);

    public function updateUser(String $id, array $data);

    public function deleteUser(String $id);

    public function listUsers(array $data);

    public function getUserDetails($id);

}
