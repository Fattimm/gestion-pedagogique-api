<?php
namespace App\Services\Interfaces;

use Illuminate\Http\Request;
use App\Http\Requests\AuthRequest;
use App\Http\Requests\StoreUserRequest;

interface AuthServiceInterface
{
    public function login(AuthRequest $request);
    public function logout();
}
