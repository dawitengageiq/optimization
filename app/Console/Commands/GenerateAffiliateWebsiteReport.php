<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateAffiliateWebsiteReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:affiliate-website-report
                            {--date= : The start date of the query query for all the leads (YYYY-MM-DD).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This artisan command executes the job for generating Affiliate Website Report';

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
        $this->info('Initiating generating of Affiliate Website Report...');
        $date = $this->option('date');

        //NEW VERSION
        if (empty($date)) {
            $date = Carbon::now()->subDay()->toDateString();
        } else {
            $date = Carbon::parse($date)->toDateString();
        }

        $job = (new \App\Jobs\Reports\GenerateAffiliateWebsiteReport($date))->delay(3);
        dispatch($job);

        $this->info('Generate Affiliate Website Report is being processed results will be available soon!');
    }
}
