<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class UserFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'user.service';  // C'est l'alias défini dans le service provider
    }
}
