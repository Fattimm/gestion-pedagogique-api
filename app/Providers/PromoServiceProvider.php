<?php

namespace App\Providers;

use App\Repositories\PromoRepository;
use Illuminate\Support\ServiceProvider;
use App\Services\Interfaces\PromoServiceInterface;
use App\Repositories\Interfaces\PromoRepositoryInterface;
use App\Services\PromoService; // L'implémentation concrète

class PromoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Liaison de l'interface au service
        $this->app->bind(PromoServiceInterface::class, PromoService::class);
        
        // Liaison de l'interface du repository à l'implémentation concrète
        $this->app->bind(PromoRepositoryInterface::class, PromoRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}

