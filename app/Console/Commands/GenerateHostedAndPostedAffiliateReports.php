<?php

namespace App\Console\Commands;

use App\Jobs\Reports\HandPReports;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateHostedAndPostedAffiliateReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:hosted-and-posted-affiliate-reports
                            {--from= : The start date of the query query for all the leads (YYYY-MM-DD).}
                            {--to= : The end date of the query for all the leads (YYYY-MM-DD).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This console command will generate hosted and posted affiliate reports for the current date';

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
        $this->info('Initiating generating of H and P reports...');

        $dateYesterdayStr = $this->option('from');
        $dateNowStr = $this->option('to');

        if (empty($dateYesterdayStr) || empty($dateNowStr)) {
            $dateYesterdayStr = Carbon::now()->subDay()->toDateString();
            //$dateNowStr = Carbon::now()->toDateString();
            $dateNowStr = $dateYesterdayStr;
        }

        $this->info("$dateYesterdayStr - $dateNowStr");

        //execute the job here
        $job = (new HandPReports($dateYesterdayStr, $dateNowStr))->delay(5);
        dispatch($job);

        $this->info('Generate H and P Report is being processed results will be available soon!');
    }
}
