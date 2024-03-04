<?php

namespace App\Console\Commands;

use Carbon\Carbon;
// use Log;
use Illuminate\Console\Command;

class ConsolidatedGraphGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:consolidated-graph {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consolidated Graph Date Generator';

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
        $date = $this->argument('date') != '' ? $this->argument('date') : Carbon::yesterday()->toDateString();

        $job = (new \App\Jobs\ConsolidatedGraphGenerator($date))->delay(5);
        dispatch($job);

        $this->info('The Consolidated Graph for '.$date.' is being processed. Results will be available soon!');

    }
}
