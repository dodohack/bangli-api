<?php
/**
 * Cronjob: update affiliates' offers periodically
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Backend\WebgainController;

class UpdateWebgainOffers extends Command
{
    /**
     * The name and signature of the console command
     */
    protected $signature = 'offer:update-webgain';

    protected $description = 'Update Webgain offers';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command
     */
    public function handle()
    {
        $wg = new WebgainController();
        $wg->updateOffers();
    }
}
