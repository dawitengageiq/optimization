<?php

namespace App\Console\Commands;

use App\Jobs\SendOptOutReportJob;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendOptOutReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:opt-out-report 
                {--from= : The start date of the query query for all the leads (YYYY-MM-DD).}
                {--to= : The end date of the query for all the leads (YYYY-MM-DD).}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send opt out report';

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
        $this->info('Send opt out report.....');

        $date_from = $this->option('from');
        $date_to = $this->option('to');

        if (empty($date_from) || empty($date_to)) {
            $date_from = Carbon::now()->subDay(30)->toDateString();
            $date_to = Carbon::now()->toDateString();
        }

        $this->info("$date_from - $date_to");

        $job = (new SendOptOutReportJob($date_from, $date_to));
        dispatch($job);
    }
}
