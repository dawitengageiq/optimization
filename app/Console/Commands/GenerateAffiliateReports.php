<?php

namespace App\Console\Commands;

use App\Jobs\Reports\AffiliateReportsV3;
use App\Jobs\Reports\AffiliateReportsV4;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateAffiliateReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'generate:affiliate-reports
    //                         {--from= : The start date of the query query for all the leads (YYYY-MM-DD).}
    //                         {--to= : The end date of the query for all the leads (YYYY-MM-DD).}
    //                         {--date= : The date of the query for all the leads (YYYY-MM-DD).}
    //                         {--restrict= : Run only specific functions}';
    protected $signature = 'generate:affiliate-reports
                            {--date= : The date of the query for all the leads (YYYY-MM-DD).}
                            {--restrict= : Run only specific functions}
                            {--rev_tracker= : Get specific rev tracker cake stats only}
                            {--affiliate= : Get specific affiliate cake stats only}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This console command will generate affiliate reports for the current date';

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
        $this->info('Initiating generating of affiliate reports...');
        // $dateYesterdayStr = $this->option('from');
        // $dateNowStr = $this->option('to');
        $restriction = $this->option('restrict');
        $date = $this->option('date');
        $rev_tracker = $this->option('rev_tracker');
        $affiliate = $this->option('affiliate');

        //NEW VERSION
        if (empty($date)) {
            $from = Carbon::now()->subDay()->toDateString();
            $to = Carbon::now()->toDateString();
        } else {
            $from = Carbon::parse($date)->toDateString();
            $to = Carbon::parse($date)->addDay()->toDateString();
        }

        $job = (new AffiliateReportsV4($from, $to, $restriction, $affiliate, $rev_tracker))->delay(5);
        dispatch($job);

        //OLD VERSION
        // if(empty($dateYesterdayStr) || empty($dateNowStr))
        // {
        //     $dateYesterdayStr = Carbon::now()->subDay()->toDateString();
        //     $dateNowStr = Carbon::now()->toDateString();
        // }

        // $this->info("$dateYesterdayStr - $dateNowStr");

        // $job = (new AffiliateReportsV3($dateYesterdayStr,$dateNowStr))->delay(5);
        // dispatch($job);

        $this->info('Generate Affiliate Report is being processed results will be available soon!');
    }
}
