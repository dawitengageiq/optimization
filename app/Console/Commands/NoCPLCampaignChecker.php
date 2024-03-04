<?php

namespace App\Console\Commands;

use App\Jobs\NoCPLReminder;
use Illuminate\Console\Command;

class NoCPLCampaignChecker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cpl-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for campaigns with no cost per lead';

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
        $this->info('Check for campaigns with no cost per lead....');
        $job = (new NoCPLReminder());
        dispatch($job);
    }
}
