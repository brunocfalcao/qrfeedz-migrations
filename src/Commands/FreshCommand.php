<?php

namespace QRFeedz\Migrations\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class FreshCommand extends Command
{
    protected $signature = 'qrfeedz:fresh {--seeder= : The seeder to run}';

    protected $description = 'Run migrate:fresh and specific seeder';

    public function handle()
    {
        $seeder = $this->option('seeder');

        $this->info('Running migrate:fresh...');

        $migrateFreshProcess = new Process(['php', 'artisan', 'migrate:fresh', '--force']);
        $migrateFreshProcess->run();

        try {
            if (! $migrateFreshProcess->isSuccessful()) {
                throw new ProcessFailedException($migrateFreshProcess);
            }
        } catch (ProcessFailedException $e) {
            return $this->error($e->getMessage());
        }

        if ($seeder) {
            $seederClass = "QRFeedz\\Database\\Seeders\\{$seeder}";

            $this->info("Running seeder: {$seederClass}...");

            $seedProcess = new Process(['php', 'artisan', 'db:seed', '--class='.$seederClass, '--force']);
            $seedProcess->run();

            try {
                if (! $seedProcess->isSuccessful()) {
                    throw new ProcessFailedException($seedProcess);
                }
            } catch (ProcessFailedException $e) {
                return $this->error($e->getMessage());
            }
        }

        $this->info('Operation completed successfully.');
    }
}
