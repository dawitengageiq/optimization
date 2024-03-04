<?php

namespace App\Console\Commands;

use App\Lead;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class RemoveLeadDuplicates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remove:LeadDuplicates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to remove duplicate leads';

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
        $this->info(Carbon::now()->toDateTimeString());
        $this->info('Initiating duplicate removal...');
        $this->removeDuplicateLeads();
    }

    public function removeDuplicateLeads()
    {
        $duplicatedCampaignEmailSQL = 'SELECT campaign_id, lead_email, count(id) AS count FROM leads GROUP BY campaign_id, lead_email HAVING count(id) > 1 ORDER BY campaign_id';
        $duplicates = DB::select($duplicatedCampaignEmailSQL);

        $numberOfDeleted = 0;

        foreach ($duplicates as $duplicate) {
            $campaignID = $duplicate->campaign_id;
            $leadEmail = $duplicate->lead_email;

            $campaignEmailLeads = Lead::campaignEmailLeads(['campaign_id' => $campaignID, 'lead_email' => $leadEmail])->get();

            //get the successful lead to to remain
            $firstSuccess = Lead::campaignEmailLeads(['campaign_id' => $campaignID, 'lead_email' => $leadEmail, 'lead_status' => 1])->first();
            $firstPending = Lead::campaignEmailLeads(['campaign_id' => $campaignID, 'lead_email' => $leadEmail, 'lead_status' => 3])->first();
            $firstRejected = Lead::campaignEmailLeads(['campaign_id' => $campaignID, 'lead_email' => $leadEmail, 'lead_status' => 2])->first();
            $firstFail = Lead::campaignEmailLeads(['campaign_id' => $campaignID, 'lead_email' => $leadEmail, 'lead_status' => 0])->first();

            if ($firstSuccess) {
                $this->info('The lead will remain is: ');
                $this->info('id = '.$firstSuccess->id.' campaign_id: '.$firstSuccess->campaign_id.' lead_email: '.$firstSuccess->lead_email);
                $this->info('Deleting the rest of leads...');
                $numberOfDeleted = $numberOfDeleted + $this->removeExceptOne($firstSuccess, $campaignEmailLeads);
            } elseif ($firstPending) {
                $this->info('The lead will remain is: ');
                $this->info('id = '.$firstPending->id.' campaign_id: '.$firstPending->campaign_id.' lead_email: '.$firstPending->lead_email);
                $this->info('Deleting the rest of leads...');
                $numberOfDeleted = $numberOfDeleted + $this->removeExceptOne($firstPending, $campaignEmailLeads);
            } elseif ($firstRejected) {
                $this->info('The lead will remain is: ');
                $this->info('id = '.$firstRejected->id.' campaign_id: '.$firstRejected->campaign_id.' lead_email: '.$firstRejected->lead_email);
                $this->info('Deleting the rest of leads...');
                $numberOfDeleted = $numberOfDeleted + $this->removeExceptOne($firstRejected, $campaignEmailLeads);
            } elseif ($firstFail) {
                $this->info('The lead will remain is: ');
                $this->info('id = '.$firstFail->id.' campaign_id: '.$firstFail->campaign_id.' lead_email: '.$firstFail->lead_email);
                $this->info('Deleting the rest of leads...');
                $numberOfDeleted = $numberOfDeleted + $this->removeExceptOne($firstFail, $campaignEmailLeads);
            }

        }

        $this->info('Removing of Lead duplicates successful! Total Leads: '.$numberOfDeleted);
        //return ['delete_count' => $numberOfDeleted];
    }

    public function removeExceptOne($leadRemain, $leads)
    {
        $count = 0;

        foreach ($leads as $lead) {
            if ($lead->id != $leadRemain->id) {
                $lead->delete();
                $count++;
            }
        }

        return $count;
    }
}
