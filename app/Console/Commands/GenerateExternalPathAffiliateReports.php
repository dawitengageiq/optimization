<?php

namespace App\Console\Commands;

use App\Jobs\Reports\ExternalPathAffiliateReportJob;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateExternalPathAffiliateReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:external-path-affiliate-reports
                            {--date= : The date of the query query for all the leads (YYYY-MM-DD).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This console command will generate external path affiliate reports for the current date';

    /**
     * Create a new command instance.
     *
     * @return void
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
        $this->info('Initiating generating of External Path reports...');

        // $dateYesterdayStr = $this->option('from');
        // $dateNowStr = $this->option('to');
        // if(empty($dateYesterdayStr) || empty($dateNowStr))
        // {
        //     $dateYesterdayStr = Carbon::now()->subDay()->toDateString();
        //     //$dateNowStr = Carbon::now()->toDateString();
        //     $dateNowStr = $dateYesterdayStr;
        // }
        // $this->info("$dateYesterdayStr - $dateNowStr");

        $date = $this->option('date');
        if (empty($date)) {
            $date = Carbon::now()->subDay()->toDateString();
        }

        $this->info("$date");

        //execute the job here
        $job = (new ExternalPathAffiliateReportJob($date))->delay(5);
        dispatch($job);

        $this->info('Generate External Path Report is being processed results will be available soon!');
    }
}
