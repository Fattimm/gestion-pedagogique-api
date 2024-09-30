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
        $firebase = file_get_contents(env('FIREBASE_CREDENTIALS'));
        $firebasepath = base64_decode($firebase);

        $factory = (new Factory)
            ->withServiceAccount(json_decode($firebasepath,true));

        $databaseUrl = 'https://gestion-pedagogique-8cc17.firebaseio.com';
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
