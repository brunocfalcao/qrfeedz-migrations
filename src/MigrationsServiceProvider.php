<?php

namespace QRFeedz\Migrations;

use QRFeedz\Foundation\Abstracts\QRFeedzServiceProvider;

class MigrationsServiceProvider extends QRFeedzServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register()
    {
        //
    }
}
