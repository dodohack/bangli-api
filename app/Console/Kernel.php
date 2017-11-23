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
        \App\Console\Commands\PurgeExpiredOffers::class,
        \App\Console\Commands\UpdateAWINOffers::class,
        \App\Console\Commands\UpdateLinkshareOffers::class,
        \App\Console\Commands\UpdateWebgainOffers::class,
        \App\Console\Commands\UpdateAWINMerchants::class,
        \App\Console\Commands\UpdateLinkshareMerchants::class
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
        $schedule->command('offer:purge-expired')->dailyAt('2:00');

        // Update offer daily
        $schedule->command('offer:update-awin')->dailyAt('2:10');
        $schedule->command('offer:update-linkshare')->dailyAt('2:30');
        $schedule->command('offer:update-webgain')->dailyAt('2:40');

        // Update merchant monthly
        $schedule->command('merchant:update-awin')->monthly();
        $schedule->command('merchant:update-linkshare')->monthly();
    }
}
