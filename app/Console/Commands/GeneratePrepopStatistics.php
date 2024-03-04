<?php

namespace App\Console\Commands;

use App\Jobs\Reports\GeneratePrepopStatisticsV2;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GeneratePrepopStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:prepop-statistics
                            {--from= : The start date of the query query for all the cake clicks (YYYY-MM-DD).}
                            {--to= : The end date of the query for all the cake clicks (YYYY-MM-DD).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will call cake APIs for clicks for prepop statistics';

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
        $this->info('Initiating generating of prepop statistics...');

        $dateYesterdayStr = $this->option('from');
        $dateNowStr = $this->option('to');

        if (empty($dateYesterdayStr) || empty($dateNowStr)) {
            $dateYesterdayStr = Carbon::now()->subDay()->toDateString();
            $dateNowStr = Carbon::now()->toDateString();

            // $dateYesterdayStr = Carbon::now()->subDay()->format('m/d/Y');
            // $dateNowStr = Carbon::now()->format('m/d/Y');
        }

        $this->info("$dateYesterdayStr - $dateNowStr");

        $job = (new GeneratePrepopStatisticsV2($dateYesterdayStr, $dateNowStr))->delay(3);
        dispatch($job);

        // //get all revenue trackers and group their execution
        // $revenueTrackersCount = AffiliateRevenueTracker::count();

        // $groupCount = intval($revenueTrackersCount / 50);
        // $remainder = $revenueTrackersCount % 50;

        // if($remainder>0)
        // {
        //     //add 1 group
        //     ++$groupCount;
        // }

        // for($i=0;$i<$groupCount;$i++)
        // {
        //     $skip = $i * 50;

        //     $job = (new \App\Jobs\Reports\GeneratePrepopStatistics($skip,50,$dateYesterdayStr,$dateNowStr));
        //     dispatch($job);
        // }

        //$job = (new \App\Jobs\GeneratePrepopStatistics($dateYesterdayStr,$dateNowStr));
        //dispatch($job);

        $this->info('Generate Prepop Statistics are being processed, results will be available soon!');
    }
}
