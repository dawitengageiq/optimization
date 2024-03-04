<?php

namespace App\Console\Commands;

use App\Jobs\ConsolidatedGraph\GenerateDataByDispatch;
use Illuminate\Console\Command;

class ConsolidatedGraphDataGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consolidated-graph:data-generator {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate data for consolidated graph.';

    /**
     * Create a new command instance.
     *
     * @param  \App\Jobs\ConsolidatedGraph\GenerateData  $generator
     */
    public function __construct(
        // \App\Jobs\ConsolidatedGraph\GenerateData $generator
    ) {
        // $this->generator = $generator;
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Executing consolidated graph data generator.');
        //$this->generator->execute($this->argument('date'));

        $job = (new GenerateDataByDispatch($this->argument('date')))->delay(3);
        dispatch($job);
    }
}
