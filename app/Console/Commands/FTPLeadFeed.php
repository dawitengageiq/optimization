<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class FTPLeadFeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:ftp-lead-feed-csv
                            {--date= : The date of the query for all the lead users. (YYYY-MM-DD).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send ftp lead feed.';

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
        $date = $this->option('date');
        if (empty($date)) {
            $date = Carbon::now()->subDay()->toDateString();
        }

        $job = (new \App\Jobs\FTPLeadFeed($date))->delay(3);
        dispatch($job);

        $this->info('Generating CSV files and will be uploaded soon.');
    }
}
