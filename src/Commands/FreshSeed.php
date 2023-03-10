<?php

namespace QRFeedz\Migrations\Commands;

use Illuminate\Console\Command;

class FreshSeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qrfeedz:fresh {--test : Seed testing data} {--seeder= : A custom seeder classname prefix}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs the QR Feedz database and optionally populates testing data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('=> Installing QR Feedz schema...');

        /**
         * Create all tables and indexes.
         */
        $this->call('migrate:fresh', [
            '--force' => 1,
            '--quiet' => 1,
        ]);

        /**
         * The foundation seeder populates de system tables with the
         * initial data that is needed to use qrfeedz.
         */
        $this->call('db:seed', [
            '--class' => 'QRFeedz\Database\Seeders\SchemaFoundationSeeder',
            '--quiet' => 1,
        ]);

        if ($this->option('test') && app()->environment() != 'production') {
            $this->info('=> Seeding database with testing data ...');
            $this->call('db:seed', [
                '--class' => 'QRFeedz\Database\Seeders\SchemaTestSeeder',
                '--quiet' => 1,
            ]);
        }

        if ($this->option('seeder') && app()->environment() != 'production') {
            $classname = $this->option('seeder');

            $this->info('=> Seeding database with '.$classname.' class ...');
            $this->call('db:seed', [
                '--class' => 'QRFeedz\Database\Seeders\\'.$classname,
                '--quiet' => 1,
            ]);
        }

        return 0;
    }
}
