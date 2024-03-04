<?php

namespace App\Console\Commands;

use App\Jobs\Reports\GenerateClicksVsRegistrationStatistics;
use App\Jobs\Reports\GenerateClicksVsRegistrationStats;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateClicksRegistrationStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:clicks-registration-stats
                            {--from= : The start date of the query query for all the leads (YYYY-MM-DD).}
                            {--to= : The end date of the query for all the leads (YYYY-MM-DD).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is for generating clicks vs. registration stats.';

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
        $this->info('Initiating generating of clicks vs registration stats...');
        $dateYesterdayStr = $this->option('from');
        $dateNowStr = $this->option('to');

        if (empty($dateYesterdayStr) || empty($dateNowStr)) {
            $dateYesterdayStr = Carbon::now()->subDay()->toDateString();
            $dateNowStr = Carbon::now()->toDateString();
        }

        $this->info("$dateYesterdayStr - $dateNowStr");

        // $job = (new GenerateClicksVsRegistrationStatistics($dateYesterdayStr, $dateNowStr))->delay(3);
        $job = (new GenerateClicksVsRegistrationStats($dateYesterdayStr, $dateNowStr))->delay(3);
        dispatch($job);

        $this->info('Clicks vs Registration stats is being processed results will be available soon!');
    }
}
