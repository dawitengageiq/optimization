<?php

namespace App\Console\Commands;

use App\Http\Services\Charts\CreateChartImage;
use Cache;
use Illuminate\Console\Command;

class GenerateCharts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'charts:generate {version}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generation of leads chart. This is called within High Rejection Alert Report artisan command.';

    protected $charts;

    /**
     * Create a new command instance.
     */
    public function __construct(CreateChartImage $charts)
    {
        $this->charts = $charts;
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (Cache::has($this->argument('version').'_rejection_report')) {
            // set the data
            //$this->charts->dummyData(true);
            $this->charts->setData(Cache::get($this->argument('version').'_rejection_report'));

            // Note: need call generateImage twice for the code is set to create only one chart,
            // modify this if theres a process to generate multiple chart in one image.
            // Generate the status: high chart
            $this->charts->generateImage('high');
            // Generate the status: critical chart
            $this->charts->generateImage('critical');
        }
    }
}
