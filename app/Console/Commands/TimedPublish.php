<?php
/**
 * Publish a post when current time >= published_at and post status is not
 * in 'publish'.
 */

namespace App\Console\Commands;

use App\Models\CmsPost;
use Illuminate\Console\Command;

class TimedPublish extends Command
{
    protected $signature = 'publish:timed';

    protected $description = 'Publish a post in the future';

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
        // TODO: Timezone!
        CmsPost::whereIn('status', ['draft', 'pending'])
            ->where('published_at', '<', date('Y-m-d H:i:s'))
            ->update(['state' => 'publish']);
    }
}