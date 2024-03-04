<?php

namespace App\Console\Commands;

use App\Jobs\SendRegPathRevenueEmailReportJob;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendRegPathRevenueEmailReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:affiliate-reg-path-revenue-report  {--date= : The date of the query for all the leads (YYYY-MM).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send monthly reg path revenue report to affiliate';

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
        $this->info('Send affiliate reg path revenue report.....');

        $date = $this->option('date');
        if ($date != '') {
            $date = $date.'-01';
            $from = Carbon::parse($date)->startOfMonth()->toDateString();
            $to = Carbon::parse($date)->endOfMonth()->toDateString();
        } else {
            $from = Carbon::yesterday()->startOfMonth()->toDateString();
            $to = Carbon::yesterday()->toDateString();
        }

        $this->info('Processing: '.$from.' to '.$to);

        $job = (new SendRegPathRevenueEmailReportJob($from, $to));
        dispatch($job);

        $this->info('Process results will be available soon!');
    }
}
