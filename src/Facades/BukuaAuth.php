<?php

namespace BukuaAuth\Facades;

use Illuminate\Support\Facades\Facade;

class BukuaAuth extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'bukuaauth';
    }
}
