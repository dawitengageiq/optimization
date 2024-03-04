<?php

namespace App\Console\Commands;

use App\Jobs\GetOneTrustEmailJob;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GetOneTrustEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:one-trust-emails {--date= : The date of the query for all the leads (YYYY-MM-DD).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get One Trust request user emails';

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
        $this->info('Getting One Trust Emails.....');
        $date = $this->option('date');

        if (empty($date)) {
            $date = Carbon::now()->subDay(7)->toDateString();
        }

        $job = (new GetOneTrustEmailJob($date));
        dispatch($job);

        $this->info('One Trust Emails is being processed results will be available soon!');
    }
}
