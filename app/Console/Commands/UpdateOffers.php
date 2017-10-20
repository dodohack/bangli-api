<?php
/**
 * Cronjob: update affiliates' offers periodically
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Backend\AffiliateWindowController;

class UpdateOffers extends Command
{
    /**
     * The name and signature of the console command
     */
    protected $signature = 'offer:update';

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
        $awin = new AffiliateWindowController();
        $awin->updateOffers();
    }
}
