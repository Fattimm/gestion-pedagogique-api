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
            return (new Factory)
                ->withServiceAccount(env('FIREBASE_CREDENTIALS'))
                ->withDatabaseUri(env('FIREBASE_DATABASE_URL'))
                ->createFirestore();
        });
    }

    public function boot()
    {
        //
    }
}
