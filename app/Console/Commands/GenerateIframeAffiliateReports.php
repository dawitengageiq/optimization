<?php

namespace App\Console\Commands;

use App\Jobs\Reports\IframeAffiliateReports;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateIframeAffiliateReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:iframe-affiliate-reports
                            {--from= : The start date of the query query for all the leads (YYYY-MM-DD).}
                            {--to= : The end date of the query for all the leads (YYYY-MM-DD).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This console command will generate iframe affiliate reports for the current date';

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
        $this->info('Initiating generating of Iframe Affiliate reports...');

        $dateYesterdayStr = $this->option('from');
        $dateNowStr = $this->option('to');

        if (empty($dateYesterdayStr) || empty($dateNowStr)) {
            $dateYesterdayStr = Carbon::now()->subDay()->toDateString();
            //$dateNowStr = Carbon::now()->toDateString();
            $dateNowStr = $dateYesterdayStr;
        }

        $this->info("$dateYesterdayStr - $dateNowStr");

        //execute the job here
        $job = (new IframeAffiliateReports($dateYesterdayStr, $dateNowStr))->delay(5);
        dispatch($job);

        $this->info('Generate Iframe Affiliate Reports are being processed results will be available soon!');
    }
}
