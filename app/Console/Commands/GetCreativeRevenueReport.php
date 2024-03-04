<?php

namespace App\Console\Commands;

use App\Jobs\Reports\GenerateCreativeRevenueReport;
use Carbon\Carbon;
// use App\Lead;
// use App\CampaignView;
// use App\CreativeReport;
use Illuminate\Console\Command;

class GetCreativeRevenueReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:creative-revenue-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "this will generate creative revenue report summary for yesterday and delete previous week's campaign views if yesterday was the first day of the week.";

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
        $date_yesterday = Carbon::yesterday()->toDateString();
        $this->info('Generating Report for '.$date_yesterday);

        $job = (new GenerateCreativeRevenueReport($date_yesterday))->delay(3);
        dispatch($job);

        $this->info('Report will be generated soon!');
    }
}
