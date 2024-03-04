<?php

namespace Database\Seeders;

use App\AffiliateRevenueTracker;
use App\Campaign;
use App\CampaignViewReport;
use App\MixedCoregCampaignOrder;
use Illuminate\Database\Seeder;

class MixedCoregCampaignOrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mixeCoregTypes = config('constants.MIXED_COREG_TYPE_FOR_ORDERING');
        $revenueTrackers = AffiliateRevenueTracker::select('revenue_tracker_id')->get();

        foreach ($revenueTrackers as $revenueTracker) {
            //get all campaign per type and create record for campaign view reports
            foreach ($mixeCoregTypes as $key => $value) {

                //get all campaigns
                $campaigns = Campaign::where('campaign_type', '=', $key)->get();

                foreach ($campaigns as $campaign) {
                    $campaignViewReport = CampaignViewReport::firstOrNew([
                        'campaign_type_id' => $key,
                        'campaign_id' => $campaign->id,
                        'revenue_tracker_id' => $revenueTracker->revenue_tracker_id,
                    ]);

                    $campaignViewReport->total_view_count = 6000;
                    $campaignViewReport->current_view_count = 6000;
                    $campaignViewReport->save();
                }

            }
        }

        $allMixedCoregCampaigns = Campaign::select('id')->where(function ($query) use ($mixeCoregTypes) {
            foreach ($mixeCoregTypes as $key => $value) {
                $query->orWhere('campaign_type', '=', $key);
            }
        })->orderBy('priority', 'asc')->get();

        $defaultCampaignOrder = [];

        foreach ($allMixedCoregCampaigns as $campaign) {
            array_push($defaultCampaignOrder, $campaign->id);
        }

        foreach ($revenueTrackers as $revenueTracker) {
            $mixedCoregCampaignTypeOrder = MixedCoregCampaignOrder::firstOrNew([
                'revenue_tracker_id' => $revenueTracker->revenue_tracker_id,
            ]);

            $defaultOrderJson = json_encode($defaultCampaignOrder);
            $mixedCoregCampaignTypeOrder->campaign_id_order = $defaultOrderJson;
            $mixedCoregCampaignTypeOrder->reorder_reference_date = \Carbon\Carbon::now()->subDays(5)->toDateTimeString();
            $mixedCoregCampaignTypeOrder->save();
        }
    }
}
