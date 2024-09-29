<?php

namespace App\Services;

use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\AuthException;

class AuthServiceFirebase
{
    protected $firebaseAuth;

    public function __construct(FirebaseAuth $firebaseAuth)
    {
        $this->firebaseAuth = $firebaseAuth;
    }

    public function login($email, $password)
    {
        try {
            $signInResult = $this->firebaseAuth->signInWithEmailAndPassword($email, $password);
            return $signInResult->data();
        } catch (AuthException $e) {
            return null;
        }
    }

    public function changePassword($email, $newPassword)
    {
        try {
            $user = $this->firebaseAuth->getUserByEmail($email);
            $this->firebaseAuth->changeUserPassword($user->uid, $newPassword);
        } catch (AuthException $e) {
            return null;
        }
    }
}
