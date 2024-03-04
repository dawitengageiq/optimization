<?php

namespace App\Console\Commands;

use App\Jobs\Reports\GetUniqueClicksReport;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateUniqueClicksReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:unique-clicks-reports 
                            {--date= : The date of the query query for all the leads (YYYY-MM-DD).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates Rev tracker cake statistics such as payout, click count and Prepop Statistics';

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
        $this->info('Initiating generation of unique clicks reports...');

        $date = $this->option('date');

        if (empty($date)) {
            $date = Carbon::now()->subDay()->toDateString();
            $date_to = Carbon::now()->toDateString();
        } else {
            $date = Carbon::parse($date)->toDateString();
            $date_to = $date->addDay()->toDateString();
        }

        $this->info("$date");

        $job = (new GetUniqueClicksReport($date, $date_to))->delay(3);
        dispatch($job);
    }
}
