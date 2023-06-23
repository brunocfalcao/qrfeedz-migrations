<?php

namespace QRFeedz\Migrations;

use QRFeedz\Foundation\Abstracts\QRFeedzServiceProvider;
use QRFeedz\Migrations\Commands\FreshSeed;

class MigrationsServiceProvider extends QRFeedzServiceProvider
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
