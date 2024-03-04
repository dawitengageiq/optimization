<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateRevTrackerLandingUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:rev-tracker-landing-url';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update revenue tracker landing url';

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
        $this->info('Updating Revenue Tracker Landing URL');

        $job = (new \App\Jobs\UpdateRevTrackerLandingUrl());
        dispatch($job);
    }
}
