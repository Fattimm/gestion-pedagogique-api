<?php

namespace App\Http\Controllers;

use App\Services\AuthPassport;
use App\Http\Requests\AuthRequest;


class AuthController extends Controller
{
    protected $authService;
    
    public function __construct(AuthPassport $authService)
    {
        $this->authService = $authService;
    }

    public function login(AuthRequest $request)
    {
        $credentials = $request->only('login', 'password');
        return $this->authService->login($request);
    }

    public function logout() {
        return $this->authService->logout();
    }


}