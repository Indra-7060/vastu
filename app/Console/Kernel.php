<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Commands registered explicitly (this project ships without composer.json, so
     * automatic discovery of app/Console/Commands is not available).
     */
    protected $commands = [
        \App\Console\Commands\ImportLiveCatalog::class,
        \App\Console\Commands\MakeTileImages::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        require base_path('routes/console.php');
    }
}
