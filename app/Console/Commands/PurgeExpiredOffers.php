<?php
/**
 * Cronjob: Purge expired affiliates' offers periodically
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Backend\AffiliateController;

class PurgeExpiredOffers extends Command
{
    /**
     * The name and signature of the console command
     */
    protected $signature = 'offer:purge-expired';

    protected $description = 'Purge expired affiliates offers';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command
     */
    public function handle()
    {
        $awin = new AffiliateController();
        $awin->purgeExpiredOffers();
    }
}
