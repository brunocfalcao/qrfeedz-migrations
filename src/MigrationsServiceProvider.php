<?php

namespace QRFeedz\Migrations;

use QRFeedz\Foundation\Abstracts\QRFeedzServiceProvider;
use QRFeedz\Migrations\Commands\FreshCommand;

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
        if ($this->app->runningInConsole()) {
            $this->commands([
                FreshCommand::class,
            ]);
        }
    }
}
