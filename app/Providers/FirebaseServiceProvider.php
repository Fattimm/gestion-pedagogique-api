<?php

namespace App\Providers;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Storage;
use Kreait\Firebase\Firestore;
use Illuminate\Support\ServiceProvider;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Firestore::class, function ($app) {
            return $this->createFirebaseInstance()->createFirestore();
        });

        $this->app->singleton(Storage::class, function ($app) {
            return $this->createFirebaseInstance()->createStorage();
        });

        
    }

    public function boot()
    {
        //
    }

    private function createFirebaseInstance()
    {
        $factory = (new Factory)
            ->withServiceAccount(storage_path('app/firebase_credentials.json'));

        $databaseUrl = env('FIREBASE_DATABASE_URL');
        if ($databaseUrl) {
            $factory = $factory->withDatabaseUri($databaseUrl);
        }

        $storageBucket = env('FIREBASE_STORAGE_BUCKET');
        if ($storageBucket) {
            $factory = $factory->withDefaultStorageBucket($storageBucket);
        }

        return $factory;
    }
}
