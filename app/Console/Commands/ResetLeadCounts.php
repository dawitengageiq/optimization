<?php

namespace App\Console\Commands;

use App\AffiliateCampaign;
use App\Campaign;
use App\Helpers\Repositories\LeadCounts;
use App\LeadCount;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Log;

class ResetLeadCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:lead-counters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will reset the lead counts';

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
    public function handle(LeadCounts $leadCountsHelper): void
    {
        $this->info(Carbon::now()->toDateTimeString());
        $this->info('Resetting lead counters...');

        Log::info('Reset Lead Counts Start');
        $cids = [];
        $affids = [];
        $leadCounts = LeadCount::all();
        // \Log::info($leadCounts);
        foreach ($leadCounts as $lc) {
            if (! in_array($lc->campaign_id, $cids)) {
                $cids[] = $lc->campaign_id;
            }
            // if(!in_array($lc->affiliate_id, $affids) && $lc->affiliate_id != '') $affids[] = $lc->affiliate_id;
            // $aff_cmp[$lc->campaign_id][$lc->affiliate_id] = $lc;
        }

        $campaigns = [];
        $cmps = Campaign::whereIn('id', $cids)->select('id', 'lead_cap_type', 'lead_cap_value')->get();
        foreach ($cmps as $cmp) {
            $campaigns[$cmp->id] = $cmp;
        }

        $affiliate_campaigns = [];
        $affscmps = AffiliateCampaign::whereIn('campaign_id', $cids)->get();
        foreach ($affscmps as $affcmp) {
            $affiliate_campaigns[$affcmp->campaign_id][$affcmp->affiliate_id] = $affcmp;
        }
        //\Log::info($affiliate_campaigns);

        $updates = [];
        $deletes = [];
        $leadCapTypes = config('constants.LEAD_CAP_TYPES');
        $timeZone = config('app.timezone', 'America/Los_Angeles');

        foreach ($leadCounts as $count) {
            //Get Campaign Cap
            $campaignCapDetails = $campaigns[$count->campaign_id];
            $campaignCapType = $leadCapTypes[$campaignCapDetails->lead_cap_type];

            //Get Affiliate Cap
            if (isset($affiliate_campaigns[$count->campaign_id][$count->affiliate_id])) { //get the cap type for affiliate campaign
                $affiliateCampaignCapDetails = $affiliate_campaigns[$count->campaign_id][$count->affiliate_id];
                $affiliateCampaignCapType = $leadCapTypes[$affiliateCampaignCapDetails->lead_cap_type];
            } else {
                $affiliateCampaignCapType = $leadCapTypes[0]; //no existing affiliate campaign record therefore it is considered unlimited
            }

            // \Log::info($count);
            // \Log::info($campaignCapType);
            // \Log::info($affiliateCampaignCapType);

            if ($count->affiliate_id == null) { //Campaign Level
                if ($campaignCapType == 'Unlimited') { //Unlimited
                    $deletes[] = $count->id;
                } else {
                    $lcount = $leadCountsHelper->executeReset($count, $count->campaign_id, $count->affiliate_id, $campaignCapType, $timeZone, false);
                    $updates[] = $lcount->toArray();
                }
            } else { //Affiliate
                if ($affiliateCampaignCapType == 'Unlimited') { //Unlimited
                    $deletes[] = $count->id;
                } else {
                    $lcount = $leadCountsHelper->executeReset($count, $count->campaign_id, $count->affiliate_id, $affiliateCampaignCapType, $timeZone, false);
                    $updates[] = $lcount->toArray();
                }
            }
            // \Log::info('---------');
        }

        // \Log::info('Updated:');
        // \Log::info($updates);
        // \Log::info('Delte:');
        // \Log::info($deletes);

        $connection = config('app.type') == 'reports' ? 'secondary' : 'mysql';
        if (count($updates) > 0) {
            Log::info('Delete Existing');
            DB::connection($connection)->table('lead_counts')->delete();
            Log::info('Add updated');
            $chunks = array_chunk($updates, 1000);
            foreach ($chunks as $chunk) {
                try {
                    DB::connection($connection)->table('lead_counts')->insert($chunk);
                    //Log::info('Pre Pop Stats Chunks total: ' . count($chunk));
                } catch (QueryException $e) {
                    Log::info('Lead Count Error!');
                    Log::info($e->getMessage());
                }
            }
        } else {
            Log::info('No new updates');
            if (count($deletes) > 0) {
                Log::info('Delete unlimited');
                DB::connection($connection)->table('lead_counts')->whereIn('id', $deletes)->delete();
            }
        }

        Log::info('Reset Lead Counts End');

        // foreach($leadCounts as $count)
        // {
        //     $campaignCapDetails = Campaign::getCapDetails($count->campaign_id)->first();
        //     $affiliateCampaignCapDetails = AffiliateCampaign::getCapDetails(['campaign_id'=>$count->campaign_id,'affiliate_id'=>$count->affiliate_id])->first();

        //     $leadCapTypes = config('constants.LEAD_CAP_TYPES');
        //     $campaignCapType = $leadCapTypes[$campaignCapDetails->lead_cap_type];

        //     //get the cap type for affiliate campaign
        //     if($affiliateCampaignCapDetails)
        //     {
        //         $affiliateCampaignCapType = $leadCapTypes[$affiliateCampaignCapDetails->lead_cap_type];
        //     }
        //     else
        //     {
        //         //no existing affiliate campaign record therefore it is considered
        //         $affiliateCampaignCapType = $leadCapTypes[0];
        //     }

        //     //get the timezone this new repository methods requires timezone. This functions came from the special branch for testing purposes. This is applied here for the improvements.
        //     $timeZone = config('app.timezone','America/Los_Angeles');

        //     if($count->affiliate_id==null)
        //     {
        //         $leadCountsHelper->executeReset($count, $count->campaign_id, $count->affiliate_id, $campaignCapType, $timeZone);
        //         //$this->executeReset($count,$count->campaign_id,$count->affiliate_id,$campaignCapType);
        //     }
        //     else
        //     {
        //         $leadCountsHelper->executeReset($count, $count->campaign_id, $count->affiliate_id, $affiliateCampaignCapType, $timeZone);
        //         //$this->executeReset($count,$count->campaign_id,$count->affiliate_id,$affiliateCampaignCapType);
        //     }
        // }

        $this->info('Resetting of counters is finished!');
    }

    /*
    public function executeReset($campaignCounts, $campaignID, $affiliateID=null,$capType='Unlimited')
    {
        $this->info('Reset the campaign_id = '.$campaignID);
        $this->info('Reset the affiliate_id = '.$affiliateID);

        //check campaign cap
        switch($capType)
        {
            case 'Daily':

                //daily campaign count reset
                $dailyReferenceDate = Carbon::parse($campaignCounts->reference_date);
                if($dailyReferenceDate->startOfDay()->diffInDays(Carbon::now()->startOfDay())>0)
                {
                    //this means already past one day and count needs to reset
                    $campaignCounts->count = 0;
                    //change the reference date as well
                    $campaignCounts->reference_date = Carbon::now()->toDateTimeString();
                }

                break;

            case 'Weekly':

                //reset if start of the week
                $refWeekOfMonth = Carbon::parse($campaignCounts->reference_date)->weekOfMonth;
                $currentWeekOfMonth = Carbon::now()->weekOfMonth;

                if($currentWeekOfMonth!=$refWeekOfMonth)
                {
                    //this means already shifted to different week
                    $campaignCounts->count = 0;
                    //set the new reference date for the current week
                    $campaignCounts->reference_date = Carbon::now()->toDateTimeString();
                }

                break;

            case 'Monthly':

                //reset if start of the month
                $refMonth = Carbon::parse($campaignCounts->reference_date)->month;
                $currentMonth = Carbon::now()->month;

                if($currentMonth!=$refMonth)
                {
                    //this means already shifted to different month
                    $campaignCounts->count = 0;
                    //set the new reference date for the current month
                    $campaignCounts->reference_date = Carbon::now()->toDateTimeString();
                }

                break;

            case 'Yearly':

                //reset if start of year
                $refYear = Carbon::parse($campaignCounts->reference_date)->year;
                $currentYear = Carbon::now()->year;

                if($currentYear!=$refYear)
                {
                    //this means already shifted to different year
                    $campaignCounts->count = 0;
                    //set the new reference date for the current year
                    $campaignCounts->reference_date = Carbon::now()->toDateTimeString();
                }

                break;

            default:
                //unlimited
        }

        $campaignCounts->save();
    }
    */
}
