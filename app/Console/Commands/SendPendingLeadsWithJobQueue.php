<?php

namespace App\Console\Commands;

use App\Helpers\Repositories\Settings;
use App\Jobs\SendLeadToAdvertiser;
use App\Lead;
use App\LeadCount;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Log;

class SendPendingLeadsWithJobQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:pending-leads-job-queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send pending leads to respective Advertisers.';

    protected $settings;

    /**
     * Create a new command instance.
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Executing send pending leads...');

        //this is to prevent slave to execute at the same time as master server
        if (env('APP_SERVER', 'master') == 'slave') {
            sleep(5);
        }

        //get the number of leads to process at a time from settings
        $numberOfLeads = $this->settings->getValue('num_leads_to_process_for_send_pending_leads');

        $pendingLeads = Lead::where('lead_status', '=', 3)->with('leadMessage', 'leadDataADV', 'leadDataCSV', 'leadSentResult')->take($numberOfLeads);
        $queuedLeads = $pendingLeads->get();

        //update these leads to QUEUED
        $pendingLeads->update(['lead_status' => 4]);

        $leadCount = count($queuedLeads);

        foreach ($queuedLeads as $lead) {
            $lead->lead_status = 4;

            Log::info("lead id: $lead->id");

            //create lead count records here first since it is possible that in job you can have multiple new instance of the lead count
            $campaignCounts = LeadCount::getCount(['campaign_id' => $lead->campaign_id])->first();
            $campaignAffiliateCounts = LeadCount::getCount(['campaign_id' => $lead->campaign_id, 'affiliate_id' => $lead->affiliate_id])->first();

            $dateNowStr = Carbon::now()->toDateTimeString();

            //meaning no record of it yet so initialize
            if (! isset($campaignCounts)) {
                //this mean no record of it then create it
                $campaignCounts = new LeadCount;
                $campaignCounts->campaign_id = $lead->campaign_id;
                $campaignCounts->affiliate_id = null;
                $campaignCounts->reference_date = $dateNowStr;

                $campaignCounts->save();
            }

            //meaning no record of it yet so initialize
            if (! isset($campaignAffiliateCounts)) {
                //this mean no record of it then create it
                $campaignAffiliateCounts = new LeadCount;
                $campaignAffiliateCounts->campaign_id = $lead->campaign_id;
                $campaignAffiliateCounts->affiliate_id = $lead->affiliate_id;
                $campaignAffiliateCounts->reference_date = $dateNowStr;

                $campaignAffiliateCounts->save();
            }

            //$this->info("$lead->id $lead->lead_status");
            //dispatch or push the lead for processing to queue.
            $job = (new SendLeadToAdvertiser($lead))->delay(rand(0, 10));
            dispatch($job);
        }

        $this->info("$leadCount leads are pushed to queue and will be process soon!");
    }
}
