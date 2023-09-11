<?php

namespace QRFeedz\Migrations\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class FreshCommand extends Command
{
    protected $signature = 'qrfeedz:fresh {--seeder= : The seeder to run}';
    protected $description = 'Run migrate:fresh and specific seeder';

    public function handle()
    {
        $seeder = $this->option('seeder');

        // Run migrate:fresh
        $migrateFreshProcess = new Process(['php', 'artisan', 'migrate:fresh', '--force']);
        $migrateFreshProcess->run();

        if (!$migrateFreshProcess->isSuccessful()) {
            return $this->error('migrate:fresh failed.');
        }

        // If seeder option is provided
        if ($seeder) {
            $seederClass = "QRFeedz\\Database\\Seeders\\{$seeder}";

            // Run seeder
            $seedProcess = new Process(['php', 'artisan', 'db:seed', '--class=' . $seederClass, '--force']);
            $seedProcess->run();

            if (!$seedProcess->isSuccessful()) {
                return $this->error('Seeder failed.');
            }
        }

        $this->info('Operation completed successfully.');
    }
}
