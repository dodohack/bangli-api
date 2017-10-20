<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\ResetEditLock::class,
        \App\Console\Commands\SendEmails::class,
        \App\Console\Commands\UpdateOffers::class,
        \App\Console\Commands\PurgeExpiredOffers::class,
        \App\Console\Commands\UpdateMerchants::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //$schedule->command('reset:edit_lock')->everyFiveMinutes();

        // Purge expired offer daily
        $schedule->command('offer:purge-expired')->daily();

        // Update offer daily
        $schedule->command('offer:update')->daily();

        // Update merchant weekly
        $schedule->command('merchant:update')->weekly();

    }
}
