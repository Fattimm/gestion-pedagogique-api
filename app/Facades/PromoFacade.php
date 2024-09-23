<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class PromoFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'promo';
    }
}