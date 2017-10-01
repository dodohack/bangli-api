<?php
/**
 * Cronjob: update affiliates' offers periodically
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateOffers extends Command
{
    /**
     * The name and signature of the console command
     */
    protected $signature = 'offer:updated';

    protected $description = 'Update affiliates offers';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command
     */
    public function handle()
    {

    }
}