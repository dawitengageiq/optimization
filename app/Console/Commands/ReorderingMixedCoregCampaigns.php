<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ReorderingMixedCoregCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reorder-mixed-coreg-campaigns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mixed coreg campaigns reordering.';

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
        $this->info('Executing campaign reordering.');

        //dispatch the command
        $job = (new \App\Jobs\ReorderingMixedCoregCampaigns());
        dispatch($job);

        $this->info('Reordering of campaigns will be done soon.');
    }
}
