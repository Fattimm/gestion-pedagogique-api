<?php

namespace App\Services;

use App\Http\Requests\AuthRequest;
use Illuminate\Support\Facades\Auth;
use App\Services\Interfaces\AuthServiceInterface;

class AuthPassport implements AuthServiceInterface
{
    protected $authentificateService;


    public function __construct(){
    }

    public function login(AuthRequest $request)
    {
        $credentials = $request->only('login', 'password');

        if (Auth::attempt(['login' => $credentials['login'], 'password' => $credentials['password']])) {
            $user = Auth::user();

            // Création du jeton d'accès personnel
            $tokenResult = $user->createToken('AuthToken');

            return [
                'status' => 200,
                'data' => [
                    'accessToken' => $tokenResult,
                    'user' => $user,
                ],
                'message' => 'Login réussi'
            ];
        }

        return [
            'status' => 401,
            'message' => 'Non autorisé'
        ];
    }

    public function logout()
    {
        $user = Auth::user();

        if ($user) {
            // Révoquer tous les tokens de l'utilisateur authentifié
            $user->tokens->delete();
        }

        return [
            'status' => 200,
            'message' => 'Déconnexion réussie'
        ];
    }

}