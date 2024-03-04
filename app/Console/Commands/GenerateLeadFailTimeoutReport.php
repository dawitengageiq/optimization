<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateLeadFailTimeoutReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:leads-fail-timeout-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $job = (new \App\Jobs\Reports\GenerateLeadFailTimeoutReport());
        dispatch($job);

        $this->info('Report will be generated and emailed soon!');
    }
}
