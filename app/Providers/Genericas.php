<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class Genericas extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        require_once app_path() . '/Helpers/Genericas/filtroFecha.php';
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
