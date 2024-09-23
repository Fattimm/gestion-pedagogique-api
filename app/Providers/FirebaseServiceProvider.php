<?php

namespace App\Providers;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Firestore;
use Illuminate\Support\ServiceProvider;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register()
    {

        $this->app->singleton(Firestore::class, function ($app) {
            $factory = (new Factory)
                ->withServiceAccount(env('FIREBASE_CREDENTIALS'))
                ->withDatabaseUri(env('FIREBASE_DATABASE_URL'));

            return $factory->createFirestore();
        });

    }

    public function boot()
    {
        //
    }
}
