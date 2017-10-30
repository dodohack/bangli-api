<?php
/**
 * Cronjob: update affiliates periodically
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Backend\AffiliateWindowController;

class UpdateAWINMerchants extends Command
{
    /**
     * The name and signature of the console command
     */
    protected $signature = 'merchant:update-awin';

    protected $description = 'Update AWIN affiliates';

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
        $awin->updateMerchants();
    }
}
