<?php

namespace App\Providers;

use Kreait\Firebase\Database;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\FirebaseRepository;
use App\Services\FirebaseFirestoreService;
use Kreait\Laravel\Firebase\Facades\Firebase;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\FirebaseRepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(Database::class, function ($app) {
            return Firebase::database();
        });

        
        $this->app->singleton(UserRepositoryInterface::class, UserRepository::class);
    
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
