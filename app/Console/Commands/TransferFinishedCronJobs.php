<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Curl\Curl;
use Illuminate\Console\Command;

class TransferFinishedCronJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transfer:finished-cron-jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'In future move the transfer finished cron jobs function process here. The following code would be temporary only.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        //In future move the transfer finished cron jobs function process here. The following code would be temporary only.
        $this->info(Carbon::now()->toDateTimeString());
        $this->info('Executing transferFinishedCronJobs CURL...');

        $curl = new Curl();
        //$curl->get('http://leadreactor.app/cron/transferFinishedCronJobs'); //development only
        //$curl->get('http://testleadreactor.engageiq.com/cron/transferFinishedCronJobs');
        $curl->get(config('constants.APP_BASE_URL').'/cron/transferFinishedCronJobs');
        //$curl->get('http://leadreactor.engageiq.com/cron/transferFinishedCronJobs');

        $this->info('CURL for transferFinishedCronJobs is finished!');
    }
}
