<?php

namespace App\Providers;

use App\Services\UserService;
use Kreait\Firebase\Database;
use App\Repositories\UserRepository;
use App\Repositories\UserFirebaseRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\UserFirebaseRepositoryInterface;
use App\Services\Interfaces\UserServiceInterface;
use Kreait\Firebase\Firestore; 


class UserServiceProvider extends ServiceProvider
{

public function register()
{
    $this->app->singleton(UserServiceInterface::class, function ($app) {
        return new UserService(
            $app->make(UserRepositoryInterface::class),
            $app->make(UserFirebaseRepositoryInterface::class)
        );
    });

    $this->app->singleton(UserRepositoryInterface::class, function ($app) {
        return new UserRepository(
            $app->make(Database::class)
        );
    });

    $this->app->singleton(UserFirebaseRepositoryInterface::class, function ($app) {
        return new UserFirebaseRepository(
            $app->make(Firestore::class) // Assurez-vous de passer l'instance de Firestore ici
        );
    });

    $this->app->alias(UserServiceInterface::class, 'user.service');
}

}
