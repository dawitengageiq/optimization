<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateCakePageViewStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:page-cake-view-statistics
                            {--from= : The start date of the query query for all the leads (YYYY-MM-DD).}
                            {--to= : The end date of the query for all the leads (YYYY-MM-DD).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This artisan command executes the job for generating Page View Statistics from Cake';

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
        $this->info('Initiating generating of Page View Statistics...');
        $dateYesterdayStr = $this->option('from');
        $dateNowStr = $this->option('to');

        if (empty($dateYesterdayStr) || empty($dateNowStr)) {
            $dateYesterdayStr = Carbon::now()->subDay()->toDateString();
            $dateNowStr = Carbon::now()->toDateString();
        }

        $this->info("$dateYesterdayStr - $dateNowStr");

        $job = (new \App\Jobs\Reports\GenerateCakePageViewStatistics($dateYesterdayStr, $dateNowStr))->delay(3);
        dispatch($job);

        $this->info('Generate Page View Statistics is being processed results will be available soon!');
    }
}
