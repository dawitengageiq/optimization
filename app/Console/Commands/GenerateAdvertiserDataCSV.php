<?php

namespace App\Console\Commands;

use App\Jobs\GenerateLeadAdvertiserDataCSV;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateAdvertiserDataCSV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:lead-advertiser-data-csv
                           {--from= : The start date of the query query for all the leads (YYYY-MM-DD).}
                           {--to= : The end date of the query for all the leads (YYYY-MM-DD).}
                           {--campaign_id= : Campaign ID of the leads.}
                           {--campaign_name= : Name of the campaign.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for lead advertiser data csv generation.';

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
        $dateFrom = $this->option('from');
        $dateTo = $this->option('to');

        if (empty($dateFrom) || empty($dateTo)) {
            $dateFrom = Carbon::now()->subDay()->toDateString();
            $dateTo = Carbon::now()->toDateString();
        }

        $campaignID = $this->option('campaign_id');
        if (empty($campaignID)) {
            $this->info('Empty campaign id!');

            return;
        }

        $campaignName = $this->option('campaign_name');
        if (empty($campaignName)) {
            $this->info('Empty campaign name!');

            return;
        }

        /*
        $ftpHost = $this->option('ftphost');
        if(empty($ftpHost))
        {
            $this->info('Empty ftp host!');
            return;
        }

        $ftpUser = $this->option('ftpuser');
        if(empty($ftpUser))
        {
            $this->info('Empty ftp user!');
            return;
        }

        $ftpPassword = $this->option('ftppwd');
        if(empty($ftpPassword))
        {
            $this->info('Empty ftp password!');
            return;
        }
        */

        $job = (new GenerateLeadAdvertiserDataCSV($dateFrom, $dateTo, $campaignID, $campaignName))->delay(3);
        dispatch($job);

        $this->info('Generating CSV files and will be uploaded soon.');
    }
}
