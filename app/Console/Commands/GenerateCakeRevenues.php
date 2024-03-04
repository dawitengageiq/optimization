<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateCakeRevenues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:cake-revenues
                            {--from= : The start date of the query query for all the leads (YYYY-MM-DD).}
                            {--to= : The end date of the query for all the leads (YYYY-MM-DD).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This console command will generate cake revenues with the specified date.';

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
        $this->info('Initiating generating of cake revenues...');
        $dateYesterdayStr = $this->option('from');
        $dateNowStr = $this->option('to');

        if (empty($dateYesterdayStr) || empty($dateNowStr)) {
            $dateYesterdayStr = Carbon::now()->subDay()->toDateString();
            $dateNowStr = Carbon::now()->toDateString();
        }

        $this->info("$dateYesterdayStr - $dateNowStr");

        $job = (new \App\Jobs\GenerateCakeRevenues($dateYesterdayStr, $dateNowStr))->delay(3);
        dispatch($job);

        $this->info('Cake revenues is being processed results will be available soon!');
    }
}
