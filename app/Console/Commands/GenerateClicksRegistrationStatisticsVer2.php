<?php

namespace App\Console\Commands;

use App\Jobs\Reports\GenerateClicksVsRegistrationStatsV2;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateClicksRegistrationStatisticsVer2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:clicks-registration-stats-v2 {--date= : (YYYY-MM-DD).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is for generating clicks vs. registration stats.';

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
        $this->info('Initiating generating of clicks vs registration stats...');
        $date = $this->option('date');

        if (empty($date)) {
            $date = Carbon::now()->subDay()->toDateString();
        }

        $this->info("$date");

        $job = (new GenerateClicksVsRegistrationStatsV2($date))->delay(3);
        dispatch($job);

        $this->info('Clicks vs Registration stats is being processed results will be available soon!');
    }
}
