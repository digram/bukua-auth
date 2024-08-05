<?php

namespace BukuaAuth\Providers;

use Illuminate\Support\ServiceProvider;

class BukuaAuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . "/../routes/bukua-auth.php");
        $this->mergeConfigFrom(__DIR__ . '/../config/bukua-auth.php', 'services');
    }

    public function register()
    {
        // Pass
    }
}
