<?php

namespace App\Console\Commands;

use App\Jobs\SendBugReportsV2;
use Illuminate\Console\Command;
use Log;

class SendBugReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:bug-reports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sending of bug reports';

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
        $job = (new SendBugReportsV2())->delay(2);
        dispatch($job);

        $this->info('Bug Report will be sent later.');
        Log::info('Bug Report will be sent later.');
    }
}
