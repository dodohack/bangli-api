<?php
/**
 * Reset a online edit_lock(edit_lock == 1) when updated_at is old than 5 mins
 * (client should ping server to update edit_lock less than 1 mins)
 */

namespace App\Console\Commands;

use App\Models\CmsPost;
use Illuminate\Console\Command;

class ResetEditLock extends Command
{
    protected $signature = 'reset:edit_lock';

    protected $description = 'Reset a post edit_lock back to 0';

    /**
     * Create a new command instance
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command
     */
    public function handle()
    {
        // 0 - unlocked, 1 - online lock, 2 - offline lock
        Activity::where('edit_lock', 1)
            ->where('updated_at', '<', date('Y-m-d H:i:s', time() - 300))
            ->delete();
    }
}