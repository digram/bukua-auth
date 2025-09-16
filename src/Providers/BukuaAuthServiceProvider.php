<?php

namespace BukuaAuth\Providers;

use Illuminate\Support\ServiceProvider;
use BukuaAuth\Controllers\BukuaAuthApiController;

class BukuaAuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . "/../routes/bukua-auth.php");
        $this->mergeConfigFrom(__DIR__ . '/../config/bukua-auth.php', 'services');
    }

    public function register()
    {
        // register BukuaAuth facade
        $this->app->singleton('bukuaauth', function ($app) {
            return new BukuaAuthApiController();
        });
    }
}
