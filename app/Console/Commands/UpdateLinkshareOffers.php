<?php
/**
 * Cronjob: update affiliates' offers periodically
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Backend\LinkShareController;

class UpdateLinkshareOffers extends Command
{
    /**
     * The name and signature of the console command
     */
    protected $signature = 'offer:update-linkshare';

    protected $description = 'Update Linkshare offers';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command
     */
    public function handle()
    {
        $ls = new LinkShareController();
        $ls->updateOffers();
    }
}
