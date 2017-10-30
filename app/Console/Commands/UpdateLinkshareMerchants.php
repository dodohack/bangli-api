<?php
/**
 * Cronjob: update linkshare affiliates periodically
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Backend\LinkShareController;
use phpDocumentor\Reflection\DocBlock\Tags\Link;

class UpdateLinkshareMerchants extends Command
{
    /**
     * The name and signature of the console command
     */
    protected $signature = 'merchant:update-linkshare';

    protected $description = 'Update Linkshare affiliates';

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
        $ls->updateMerchants();
    }
}
