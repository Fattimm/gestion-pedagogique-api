<?php

namespace App\Providers;

use App\Services\ReferentielService;
use Illuminate\Support\ServiceProvider;
use App\Repositories\ReferentielRepository;
use App\Services\Interfaces\ReferentielServiceInterface;
use App\Repositories\Interfaces\ReferentielRepositoryInterface;

class ReferentielProvider extends ServiceProvider
{
    public function register()
    {
        // Enregistrement des interfaces et implémentations
        $this->app->bind(ReferentielRepositoryInterface::class, ReferentielRepository::class);
        $this->app->bind(ReferentielServiceInterface::class, ReferentielService::class);
    }

    public function boot()
    {
        //
    }
}
