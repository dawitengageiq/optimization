<?php

namespace App\Console\Commands;

use App\Jobs\GetLeadUserSubscribedCampaignsJob;
use Illuminate\Console\Command;

class GetLeadUserSubscribedCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:user-subscribed-campaigns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get subscribed campaigns for users who wants to optout';

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
        $this->info('Getting Subscribed Campaigns for users who wants to optout.....');

        $job = (new GetLeadUserSubscribedCampaignsJob());
        dispatch($job);

        $this->info('Subscribed campaigns process is being processed results will be available soon!');
    }
}
