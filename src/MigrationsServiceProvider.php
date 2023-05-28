<?php

namespace QRFeedz\Migrations;

use Illuminate\Support\ServiceProvider;
use QRFeedz\Migrations\Commands\FreshSeed;

class MigrationsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->registerCommands();
    }

    public function register()
    {
        //
    }

    protected function registerCommands()
    {
        $this->commands([
            FreshSeed::class,
        ]);
    }
}
