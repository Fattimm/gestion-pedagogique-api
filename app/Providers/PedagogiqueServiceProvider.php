<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Repositories\AnneeScolaireRepository;
use App\Repositories\ClasseRepository;
use App\Repositories\SalleRepository;
use App\Repositories\SemestreRepository;
use App\Repositories\ModuleRepository;
use App\Repositories\CoursRepository;
use App\Repositories\SessionDeCoursRepository;

use App\Repositories\Interfaces\AnneeScolaireRepositoryInterface;
use App\Repositories\Interfaces\ClasseRepositoryInterface;
use App\Repositories\Interfaces\SalleRepositoryInterface;
use App\Repositories\Interfaces\SemestreRepositoryInterface;
use App\Repositories\Interfaces\ModuleRepositoryInterface;
use App\Repositories\Interfaces\CoursRepositoryInterface;
use App\Repositories\Interfaces\SessionDeCoursRepositoryInterface;

use App\Services\AnneeScolaireService;
use App\Services\ClasseService;
use App\Services\SalleService;
use App\Services\SemestreService;
use App\Services\ModuleService;
use App\Services\CoursService;
use App\Services\SessionDeCoursService;

use App\Services\Interfaces\AnneeScolaireServiceInterface;
use App\Services\Interfaces\ClasseServiceInterface;
use App\Services\Interfaces\SalleServiceInterface;
use App\Services\Interfaces\SemestreServiceInterface;
use App\Services\Interfaces\ModuleServiceInterface;
use App\Services\Interfaces\CoursServiceInterface;
use App\Services\Interfaces\SessionDeCoursServiceInterface;

class PedagogiqueServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories
        $this->app->singleton(AnneeScolaireRepositoryInterface::class, AnneeScolaireRepository::class);
        $this->app->singleton(ClasseRepositoryInterface::class, ClasseRepository::class);
        $this->app->singleton(SalleRepositoryInterface::class, SalleRepository::class);
        $this->app->singleton(SemestreRepositoryInterface::class, SemestreRepository::class);
        $this->app->singleton(ModuleRepositoryInterface::class, ModuleRepository::class);

        $this->app->singleton(CoursRepositoryInterface::class, CoursRepository::class);
        $this->app->singleton(SessionDeCoursRepositoryInterface::class, SessionDeCoursRepository::class);

        // Services
        $this->app->singleton(AnneeScolaireServiceInterface::class, AnneeScolaireService::class);
        $this->app->singleton(ClasseServiceInterface::class, ClasseService::class);
        $this->app->singleton(SalleServiceInterface::class, SalleService::class);
        $this->app->singleton(SemestreServiceInterface::class, SemestreService::class);
        $this->app->singleton(ModuleServiceInterface::class, ModuleService::class);
        $this->app->singleton(CoursServiceInterface::class, CoursService::class);
        $this->app->singleton(SessionDeCoursServiceInterface::class, SessionDeCoursService::class);
    }
}
