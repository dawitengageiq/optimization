<?php

namespace App\Console\Commands;

use Curl\Curl;
use Illuminate\Console\Command;

class SendPendingLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:pending-leads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send pending leads.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws \ErrorException
     */
    public function handle(): void
    {
        $curl = new Curl();
        //$curl->get('http://leadreactor.app/sendPendingLeads'); //development only
        $curl->get(config('constants.APP_BASE_URL').'/sendPendingLeads');
        //$curl->get('http://leadreactor.engageiq.com/sendPendingLeads');
    }
}
