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
    protected $signature = 'qrfeedz:fresh {--seed : Seed testing data}';

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

        $this->call('migrate:fresh', [
            '--force' => 1,
            '--quiet' => 1,
        ]);

        if ($this->option('seed') && app()->environment() != 'production') {
            $this->info('=> Seeding database with testing data ...');
            $this->call('db:seed', [
                '--class' => 'QRFeedz\Database\Seeders\SchemaTestSeeder',
                '--quiet' => 1,
            ]);
        }

        return 0;
    }
}
