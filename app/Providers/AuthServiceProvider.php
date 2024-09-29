<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Promo;
use App\Models\Referentiel;
use App\Policies\UserPolicy;
use App\Policies\PromoPolicy;
use App\Policies\ReferentielPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Promo::class => PromoPolicy::class,
        Referentiel::class => ReferentielPolicy::class,

    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Passport routes
        // Passport::routes();
    }
}
