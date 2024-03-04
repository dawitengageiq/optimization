<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ReorderCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reorder-campaigns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will execute reordering of campaigns per campaign type for a particular revenue tracker if the campaign type view count is more than the configured count.';

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
        $this->info('Executing campaign reordering.');

        //dispatch the command
        $job = (new \App\Jobs\ReorderCampaigns());
        dispatch($job);

        $this->info('Campaign ordering will be done soon.');
    }
}
