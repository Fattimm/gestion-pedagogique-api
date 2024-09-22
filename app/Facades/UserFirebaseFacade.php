<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class UserFirebaseFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'firebase.database';  
    }
}

