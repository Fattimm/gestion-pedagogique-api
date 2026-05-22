<?php

namespace App\Providers;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Firestore;
use App\Services\UserFirebaseService;
use Illuminate\Support\ServiceProvider;
use App\Repositories\UserFirebaseRepository;
use App\Services\Interfaces\UserFirebaseServiceInterface;
use App\Repositories\Interfaces\UserFirebaseRepositoryInterface;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register()
    {

        $this->app->singleton(Firestore::class, function ($app) {
            try {
                $credentials = base_path(env('FIREBASE_CREDENTIALS'));
                if (empty($credentials) || !file_exists($credentials)) {
                    return null;
                }
                return (new Factory)
                    ->withServiceAccount($credentials)
                    ->withDatabaseUri(env('FIREBASE_DATABASE_URL'))
                    ->createFirestore();
            } catch (\Throwable $e) {
                // Firebase indisponible (grpc manquant ou credentials invalides)
                logger()->warning('Firebase Firestore non disponible : ' . $e->getMessage());
                return null;
            }
        });
    }

    public function boot()
    {
        //
    }
}
