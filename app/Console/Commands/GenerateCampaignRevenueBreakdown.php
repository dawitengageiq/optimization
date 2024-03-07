<?php

namespace App\Console\Commands;

use App\Jobs\CampaignRevenueBreakdownJob;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateCampaignRevenueBreakdown extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:campaign-revenue-breakdown
        {--date= : The date of the query for all the leads (YYYY-MM-DD).}
        {--campaign= : The campaign to breakdown}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This console command will generate campaign revenue, average revenue and records for the campaign on the date specified';

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
        $campaign = $this->option('campaign');

        if (empty($campaign)) {
            return $this->info('Please input a campaign id.');
        }

        if (empty($date)) {
            $the_date = Carbon::yesterday()->toDateString();
        } else {
            $the_date = Carbon::parse($date)->toDateString();
        }

        $this->info('Generating Campaign Revenue Breakdown for Campaign ID: '.$campaign.' Date: '.$the_date);

        $job = (new CampaignRevenueBreakdownJob($the_date, $campaign))->delay(5);
        dispatch($job);

        $this->info('Campaign Revenue Breakdown is being processed, results will be available soon!');
    }
}
