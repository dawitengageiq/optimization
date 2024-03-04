<?php

namespace App\Console\Commands;

use App\CampaignNoTracker;
use App\CampaignNoTrackerArchive;
use App\Helpers\Repositories\Settings;
use App\Lead;
use App\LeadArchive;
use App\LeadDataAdvArchive;
use App\LeadDataCsvArchive;
use App\LeadMessageArchive;
use App\LeadSentResultArchive;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Mail;
use Log;

class ArchiveLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'archive:leads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will archive leads';

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
    public function handle(Settings $settings): void
    {
        $this->info('Initiating archiving of leads...');

        //get the settings
        $days = $settings->getValue('leads_archiving_age_in_days');
        $counter = 0;

        Log::info("Fetching $days days old leads...");

        $firstLeadID = 0;
        $lastLeadID = 0;
        $leadsToArchive = 0;

        while (true) {
            $leads = Lead::withDaysOld($days)->with('leadMessage', 'leadDataADV', 'leadDataCSV', 'leadSentResult')->take(1000)->get();
            $leadIDsToDelete = [];

            if ($leads->count() == 0) {
                break;
            }

            foreach ($leads as $lead) {
                if ($firstLeadID == 0) {
                    $firstLeadID = $lead->id;
                }

                $leadArchive = new LeadArchive;

                try {
                    $leadArchive->id = $lead->id;
                    $leadArchive->campaign_id = $lead->campaign_id;
                    $leadArchive->affiliate_id = $lead->affiliate_id;
                    $leadArchive->creative_id = $lead->creative_id;
                    $leadArchive->path_id = $lead->path_id;
                    $leadArchive->s1 = $lead->s1;
                    $leadArchive->s2 = $lead->s2;
                    $leadArchive->s3 = $lead->s3;
                    $leadArchive->s4 = $lead->s4;
                    $leadArchive->s5 = $lead->s5;
                    $leadArchive->lead_status = $lead->lead_status;
                    $leadArchive->lead_email = $lead->lead_email;
                    $leadArchive->received = $lead->received;
                    $leadArchive->payout = $lead->payout;
                    $leadArchive->retry_count = $lead->retry_count;
                    $leadArchive->last_retry_date = $lead->last_retry_date;
                    $leadArchive->created_at = $lead->created_at;
                    $leadArchive->updated_at = $lead->updated_at;
                    $leadArchive->save();

                    if ($lead->leadDataADV != null) {
                        $leadDataAdvArchive = new LeadDataAdvArchive;
                        $leadDataAdvArchive->id = $lead->id;
                        $leadDataAdvArchive->value = $lead->leadDataADV->value;
                        $leadDataAdvArchive->created_at = $lead->leadDataADV->created_at;
                        $leadDataAdvArchive->updated_at = $lead->leadDataADV->updated_at;
                        $leadArchive->leadDataADV()->save($leadDataAdvArchive);
                    }

                    if ($lead->leadDataCSV != null) {
                        $leadDataCsvArchive = new LeadDataCsvArchive;
                        $leadDataCsvArchive->id = $lead->id;
                        $leadDataCsvArchive->value = $lead->leadDataCSV->value;
                        $leadDataCsvArchive->created_at = $lead->leadDataCSV->created_at;
                        $leadDataCsvArchive->updated_at = $lead->leadDataCSV->updated_at;
                        $leadArchive->leadDataCSV()->save($leadDataCsvArchive);
                    }

                    if ($lead->leadMessage != null) {
                        $leadMessageArchive = new LeadMessageArchive;
                        $leadMessageArchive->id = $lead->id;
                        $leadMessageArchive->value = $lead->leadMessage->value;
                        $leadMessageArchive->created_at = $lead->leadMessage->created_at;
                        $leadMessageArchive->updated_at = $lead->leadMessage->updated_at;
                        $leadArchive->leadMessage()->save($leadMessageArchive);
                    }

                    if ($lead->leadSentResult != null) {
                        $leadSentResultArchive = new LeadSentResultArchive;
                        $leadSentResultArchive->id = $lead->id;
                        $leadSentResultArchive->value = $lead->leadSentResult->value;
                        $leadSentResultArchive->created_at = $lead->leadSentResult->created_at;
                        $leadSentResultArchive->updated_at = $lead->leadSentResult->updated_at;
                        $leadArchive->leadSentResult()->save($leadSentResultArchive);
                    }

                    array_push($leadIDsToDelete, $lead->id);
                } catch (QueryException $e) {
                    Log::info($e->getMessage());
                    Log::info($e->getCode());

                    if ($e->getCode() == 23000) {
                        //remove the lead
                        array_push($leadIDsToDelete, $lead->id);
                    }
                }

                $lastLeadID = $lead->id;
                $leadsToArchive++;
            }

            // remove the archived leads
            Lead::whereIn('id', $leadIDsToDelete)->delete();
        }

        /*
        //first lead id
        $firstLead = Lead::withDaysOld($days)->first();

        if(isset($firstLead))
        {
            $firstLeadID = $firstLead->id;
        }

        $leadsToArchive = count($leads);

        foreach($leads as $lead)
        {
            $leadArchive = new LeadArchive;

            try
            {
                $leadArchive->id = $lead->id;
                $leadArchive->campaign_id = $lead->campaign_id;
                $leadArchive->affiliate_id = $lead->affiliate_id;
                $leadArchive->creative_id = $lead->creative_id;
                $leadArchive->path_id = $lead->path_id;
                $leadArchive->s1 = $lead->s1;
                $leadArchive->s2 = $lead->s2;
                $leadArchive->s3 = $lead->s3;
                $leadArchive->s4 = $lead->s4;
                $leadArchive->s5 = $lead->s5;
                $leadArchive->lead_status = $lead->lead_status;
                $leadArchive->lead_email = $lead->lead_email;
                $leadArchive->received = $lead->received;
                $leadArchive->payout = $lead->payout;
                $leadArchive->retry_count = $lead->retry_count;
                $leadArchive->last_retry_date = $lead->last_retry_date;
                $leadArchive->created_at = $lead->created_at;
                $leadArchive->updated_at = $lead->updated_at;
                $leadArchive->save();

                if($lead->leadDataADV!=null)
                {
                    $leadDataAdvArchive = new LeadDataAdvArchive;
                    $leadDataAdvArchive->id = $lead->id;
                    $leadDataAdvArchive->value = $lead->leadDataADV->value;
                    $leadDataAdvArchive->created_at = $lead->leadDataADV->created_at;
                    $leadDataAdvArchive->updated_at = $lead->leadDataADV->updated_at;
                    $leadArchive->leadDataADV()->save($leadDataAdvArchive);
                }

                if($lead->leadDataCSV!=null)
                {
                    $leadDataCsvArchive = new LeadDataCsvArchive;
                    $leadDataCsvArchive->id = $lead->id;
                    $leadDataCsvArchive->value = $lead->leadDataCSV->value;
                    $leadDataCsvArchive->created_at = $lead->leadDataCSV->created_at;
                    $leadDataCsvArchive->updated_at = $lead->leadDataCSV->updated_at;
                    $leadArchive->leadDataCSV()->save($leadDataCsvArchive);
                }

                if($lead->leadMessage!=null)
                {
                    $leadMessageArchive = new LeadMessageArchive;
                    $leadMessageArchive->id = $lead->id;
                    $leadMessageArchive->value = $lead->leadMessage->value;
                    $leadMessageArchive->created_at = $lead->leadMessage->created_at;
                    $leadMessageArchive->updated_at = $lead->leadMessage->updated_at;
                    $leadArchive->leadMessage()->save($leadMessageArchive);
                }

                if($lead->leadSentResult!=null)
                {
                    $leadSentResultArchive = new LeadSentResultArchive;
                    $leadSentResultArchive->id = $lead->id;
                    $leadSentResultArchive->value = $lead->leadSentResult->value;
                    $leadSentResultArchive->created_at = $lead->leadSentResult->created_at;
                    $leadSentResultArchive->updated_at = $lead->leadSentResult->updated_at;
                    $leadArchive->leadSentResult()->save($leadSentResultArchive);
                }

                // remove the lead
                $lead->delete();
            }
            catch(QueryException $e)
            {
                Log::info($e->getMessage());
                Log::info($e->getCode());

                if($e->getCode()==23000)
                {
                    //remove the lead
                    $lead->delete();
                }
            }

            $lastLeadID = $lead->id;
            ++$counter;
        }
        */

        // /* Campaign No Tracking */
        // $trackers = CampaignNoTracker::withDaysOld($days)->get();
        // $idsToDelete = [];

        // foreach($trackers as $tracker)
        // {
        //     $trackArchive = CampaignNoTrackerArchive::firstOrNew([
        //         'campaign_id'   => $tracker->campaign_id,
        //         'email'         => $tracker->email
        //     ]);

        //     if($trackArchive->exists)
        //     {
        //         $trackArchive->count += $tracker->count;
        //     }
        //     else
        //     {
        //         $trackArchive->created_at = $tracker->created_at;
        //         $trackArchive->count = $tracker->count;
        //     }

        //     $trackArchive->last_session = $tracker->last_session;
        //     $trackArchive->updated_at = $tracker->updated_at;
        //     $trackArchive->save();

        //     // $tracker->delete();
        //     array_push($idsToDelete, $tracker->id);
        // }

        // // delete the tracker
        // CampaignNoTracker::whereIn('id', $idsToDelete);

        $emailNotificationRecipient = env('REPORTS_EMAIL_NOTIFICATION_RECIPIENT', 'marwilburton@hotmail.com');

        //send email to Burt to notify that Atchive Leads Queue was successfully finished
        Mail::send('emails.archive_leads',
            ['days' => $days, 'leadsToArchive' => $leadsToArchive, 'firstLeadID' => $firstLeadID, 'lastLeadID' => $lastLeadID],
            function ($m) use ($emailNotificationRecipient) {
                $m->from('ariel@engageiq.com', 'Ariel Magbanua');
                $m->to($emailNotificationRecipient, 'Marwil Burton');
                // $m->cc('ariel@engageiq.com', 'Ariel Magbanua');
                $m->subject('Archive Leads Job Queue Successfully Executed!');
            });

        Log::info("There are $counter leads are archived!");

        // $job = (new \App\Jobs\ArchiveLeads())->delay(3);
        // dispatch($job);
        $this->info('Archiving is done!');
    }
}
