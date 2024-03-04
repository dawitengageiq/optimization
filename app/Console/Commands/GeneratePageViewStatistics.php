<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class GeneratePageViewStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:page-view-statistics
                            {--date= : The start date of the query query for all the leads (YYYY-MM-DD).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This artisan command executes the job for generating Page View Statistics';

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
        $this->info('Initiating generating of Page View Statistics...');
        $date = $this->option('date');

        //NEW VERSION
        if (empty($date)) {
            $date = Carbon::now()->subDay()->toDateString();
        } else {
            $date = Carbon::parse($date)->toDateString();
        }

        $job = (new \App\Jobs\Reports\GeneratePageViewStatistics($date))->delay(3);
        dispatch($job);

        $this->info('Generate Page View Statistics is being processed results will be available soon!');
    }
}
