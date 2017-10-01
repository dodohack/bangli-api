<?php
/**
 * Cronjob: Send emails in queue periodically by schedular
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
//use App\Models\User;

class SendEmails extends Command
{
    /**
     * The name and signature of the console command
     */
    protected $signature = 'email:sent {user}';

    /**
     * Description of the console command
     */
    protected $description = 'Send email to end user';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Actually email sending code goes here
     */
    public function handle()
    {

    }

}