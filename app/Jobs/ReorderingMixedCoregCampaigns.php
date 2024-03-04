<?php

namespace App\Jobs;

use App\AffiliateRevenueTracker;
use App\CampaignViewReport;
use App\Helpers\Repositories\Settings;
use App\Lead;
use App\MixedCoregCampaignOrder;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class ReorderingMixedCoregCampaigns extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(Settings $settings): void
    {
        if ($this->attempts() > 1) {
            return;
        }

        $globalMixedCoregOrderStatus = $settings->getValue('mixed_coreg_campaign_reordering_status');
        $globalViews = $settings->getValue('campaign_type_view_count');
        $date_time_now = Carbon::now()->toDateTimeString();

        if ($globalMixedCoregOrderStatus == 1) {
            /*$revenueTrackers = AffiliateRevenueTracker::where('mixed_coreg_order_status','=',1)
                                                        ->join('mixed_coreg_campaign_orders','affiliate_revenue_trackers.revenue_tracker_id','=','mixed_coreg_campaign_orders.revenue_tracker_id')
                                                        ->get();*/

            $revenueTrackers = AffiliateRevenueTracker::where('mixed_coreg_recurrence', '=', 'views')
                ->join('mixed_coreg_campaign_orders', 'affiliate_revenue_trackers.revenue_tracker_id', '=', 'mixed_coreg_campaign_orders.revenue_tracker_id')
                ->get();

            $mixeCoregTypes = config('constants.MIXED_COREG_TYPE_FOR_ORDERING');

            foreach ($revenueTrackers as $revenueTracker) {
                if ($revenueTracker->mixed_coreg_order_status == 0) {
                    //skip this since this is disabled
                    continue;
                }

                $revenueTrackerOrderBy = $revenueTracker->mixed_coreg_order_by;

                if ($revenueTracker->mixed_coreg_campaign_views == 0) {
                    $revenueTracker->mixed_coreg_campaign_views = $globalViews;
                }

                /*
                $campaignViewReports = CampaignViewReport::where('revenue_tracker_id','=',$revenueTracker->revenue_tracker_id)
                    ->where(function($query) use ($mixeCoregTypes) {foreach($mixeCoregTypes as  $key => $value)
                    {
                        $query->orWhere('campaign_type_id','=',$key);
                    }
                })->get();
                */

                Log::info("revenue_tracker_id: $revenueTracker->revenue_tracker_id");

                $campaignViewReports = CampaignViewReport::select('campaign_view_reports.*', 'campaigns.status AS campaign_status', DB::raw("(SELECT COUNT(id) FROM affiliate_campaign WHERE affiliate_id=$revenueTracker->revenue_tracker_id and campaign_id=campaign_view_reports.campaign_id) AS affiliate_campaign_record"))
                    ->where('revenue_tracker_id', '=', $revenueTracker->revenue_tracker_id)
                    ->join('campaigns', 'campaign_view_reports.campaign_id', '=', 'campaigns.id')
                    ->where(function ($query) {
                        $query->where('campaigns.status', '=', 1);
                        $query->orWhere('campaigns.status', '=', 2);
                    })
                    ->where(function ($query) use ($mixeCoregTypes) {
                        foreach ($mixeCoregTypes as $key => $value) {
                            $query->orWhere('campaign_type_id', '=', $key);
                        }
                    })->get();

                //if not all campaigns reached the threshold then no reordering for this tracker
                $doNotSkipThisTracker = true;

                foreach ($campaignViewReports as $campaignViewReport) {
                    //ignore if campaign is private and if revenue tracker does not belong to this campaign
                    // if($campaignViewReport->campaign_status==1 && $campaignViewReport->affiliate_campaign_record==0)
                    if ($campaignViewReport->campaign_status == 1 && $campaignViewReport->affiliate_campaign_record->count == 0) {
                        continue;
                    }

                    if ($campaignViewReport->current_view_count == 0 || $campaignViewReport->current_view_count < $revenueTracker->mixed_coreg_campaign_views) {
                        Log::info("campaign $campaignViewReport->campaign_id does not reached the threshold with $campaignViewReport->current_view_count count!");

                        $doNotSkipThisTracker = false;
                        break;
                    }
                }

                /*
                if($skipReorderForThisTracker)
                {
                    //skip reordering for this tracker since one of the campaign did not reached the threshold
                    Log::info($skipReorderForThisTracker);
                    Log::info('Skipping...');
                    continue;
                }
                */

                if ($doNotSkipThisTracker) {
                    $campaignOrder = \GuzzleHttp\json_decode($revenueTracker->campaign_id_order);

                    /*
                    //proceed on reordering the campaigns base on revenue / view
                    $leads = Lead::whereIn('campaign_id', $campaignOrder)
                                     ->where('affiliate_id', $revenueTracker->revenue_tracker_id)
                                     ->where('lead_status', 1)
                                     ->whereBetween('created_at', [$revenueTracker->reorder_reference_date, $date_time_now])
                                     ->select(DB::raw('COUNT(*) AS leads'),DB::raw('SUM(received) AS revenue'),'campaign_id')
                                     ->groupBy('campaign_id','affiliate_id')->get();
                    */

                    /*
                    $leads = Lead::whereIn('leads.campaign_id', $campaignOrder)
                                    ->join('campaigns','leads.campaign_id','=','campaigns.id')
                                    ->join('campaign_view_reports', 'leads.campaign_id', '=', 'campaign_view_reports.campaign_id')
                                    ->where(function($query)
                                    {
                                        $query->where('campaigns.status','=',1);
                                        $query->orWhere('campaigns.status','=',2);
                                    })
                                    ->where('leads.affiliate_id', $revenueTracker->revenue_tracker_id)
                                    ->where('leads.lead_status', 1)
                                    ->whereBetween('leads.created_at', [$revenueTracker->reorder_reference_date, $date_time_now])
                                    ->select(DB::raw('COUNT(*) AS leads'), DB::raw('SUM(received) AS revenue'), 'leads.campaign_id', 'leads.affiliate_id', 'campaign_view_reports.current_view_count')
                                    ->groupBy('leads.campaign_id', 'leads.affiliate_id')->get();
                    */

                    $leads = Lead::whereIn('leads.campaign_id', $campaignOrder)
                        ->join('campaigns', 'leads.campaign_id', '=', 'campaigns.id')
                        ->join('campaign_view_reports', function ($query) {
                            $query->on('leads.campaign_id', '=', 'campaign_view_reports.campaign_id');
                            $query->on('leads.affiliate_id', '=', 'campaign_view_reports.revenue_tracker_id');
                        })
                        ->where(function ($query) {
                            $query->where('campaigns.status', '=', 1);
                            $query->orWhere('campaigns.status', '=', 2);
                        })
                        ->where('leads.affiliate_id', '=', $revenueTracker->revenue_tracker_id)
                        ->where('leads.lead_status', '=', 1)
                        ->whereBetween('leads.created_at', [$revenueTracker->reorder_reference_date, $date_time_now])
                        ->select(DB::raw('COUNT(*) AS leads'), DB::raw('SUM(received) AS revenue'), 'leads.campaign_id', 'leads.affiliate_id', 'campaign_view_reports.current_view_count')
                        ->groupBy('leads.campaign_id', 'leads.affiliate_id')
                        ->get();

                    $stopTheOrdering = false;

                    foreach ($leads as $lead) {
                        if ($lead->current_view_count <= 0) {
                            //at this point the campaign was newly added and stop the reordering
                            Log::info('Ordering stopped! current_view_count is 0');
                            $stopTheOrdering = true;
                            break;
                        }

                        $lead_report[$revenueTracker->revenue_tracker_id][$lead->campaign_id]['leads'] = $lead->leads;
                        $lead_report[$revenueTracker->revenue_tracker_id][$lead->campaign_id]['revenue'] = $lead->revenue;

                        if ($lead->revenue == 0 || $lead->revenue == 0.0) {
                            $lead_report[$revenueTracker->revenue_tracker_id][$lead->campaign_id]['revView'] = 0;
                        } else {
                            $lead_report[$revenueTracker->revenue_tracker_id][$lead->campaign_id]['revView'] = floatval($lead->revenue) / floatval($lead->current_view_count);
                        }
                    }

                    if (count($leads) <= 0 && ! isset($lead_report)) {
                        Log::info('Ordering stopped! no leads!');
                        $stopTheOrdering = true;
                    }

                    $campaignsOrderArray = [];

                    if ($stopTheOrdering) {
                        continue;
                    } else {
                        foreach ($campaignOrder as $campaignID) {
                            if (! isset($lead_report[$revenueTracker->revenue_tracker_id][$campaignID])) {
                                $campaignsOrderArray[$campaignID] = 0;
                            } else {
                                $campaignsOrderArray[$campaignID] = $lead_report[$revenueTracker->revenue_tracker_id][$campaignID]['revView'];
                            }
                        }
                    }

                    Log::info('old campaign order: ');
                    Log::info($campaignsOrderArray);

                    if (count($campaignsOrderArray) > 0) {
                        //sort it according to the order by field of affiliate revenue tracker
                        switch ($revenueTrackerOrderBy) {
                            //Order campaign ascending
                            case 1:
                                asort($campaignsOrderArray);
                                break;

                                //Order campaign descending
                            case 2:
                                arsort($campaignsOrderArray);
                                break;

                                //Randomize campaign order
                            case 3:
                                $campaignsOrderArray = $this->shuffle_assoc($campaignsOrderArray);
                                break;
                        }

                        Log::info('new campaign order: ');
                        Log::info($campaignsOrderArray);

                        $newCampaignOrder = '[';

                        foreach ($campaignsOrderArray as $campaignID => $revenueValue) {
                            $newCampaignOrder = $newCampaignOrder.$campaignID.',';
                        }

                        $newCampaignOrder = rtrim($newCampaignOrder, ',').']';

                        //replace the old order
                        $mixedCoregCampaignOrder = MixedCoregCampaignOrder::firstOrNew([
                            'revenue_tracker_id' => $revenueTracker->revenue_tracker_id,
                        ]);

                        $mixedCoregCampaignOrder->campaign_id_order = $newCampaignOrder;

                        //reset the reference date and views
                        $mixedCoregCampaignOrder->reorder_reference_date = $date_time_now;
                        $mixedCoregCampaignOrder->save();

                        /*
                        //reset all campaign views
                        foreach($campaignViewReports as $campaignViewReport)
                        {
                            $campaignViewReport->current_view_count = 0;
                            $campaignViewReport->save();
                        }
                        */
                    }
                }
            }

            //reset the current count regardless if reordering is enabled or not
            foreach ($revenueTrackers as $revenueTracker) {
                /*
                $campaignViewReports = CampaignViewReport::where('revenue_tracker_id','=',$revenueTracker->revenue_tracker_id)
                    ->join('campaigns','campaign_view_reports.campaign_id','=','campaigns.id')
                    ->where(function($query) use ($mixeCoregTypes) {foreach($mixeCoregTypes as  $key => $value)
                    {
                        $query->orWhere('campaign_type_id','=',$key);
                    }
                    })->get();
                */

                $campaignViewReports = CampaignViewReport::select('campaign_view_reports.*', 'campaigns.status AS campaign_status', DB::raw("(SELECT COUNT(id) FROM affiliate_campaign WHERE affiliate_id=$revenueTracker->revenue_tracker_id and campaign_id=campaign_view_reports.campaign_id) AS affiliate_campaign_record"))
                    ->where('revenue_tracker_id', '=', $revenueTracker->revenue_tracker_id)
                    ->join('campaigns', 'campaign_view_reports.campaign_id', '=', 'campaigns.id')
                    ->where(function ($query) use ($mixeCoregTypes) {
                        foreach ($mixeCoregTypes as $key => $value) {
                            $query->orWhere('campaign_type_id', '=', $key);
                        }
                    })->get();

                //if not all campaigns reached the threshold then no reordering for this tracker
                $skipReorderForThisTracker = false;

                foreach ($campaignViewReports as $campaignViewReport) {
                    //exempt all inactive and hidden campaigns
                    if ($campaignViewReport->campaign_status != 1 && $campaignViewReport->campaign_status != 2) {
                        continue;
                    }

                    //ignore if campaign is private and if revenue tracker does not belong to this campaign
                    // if($campaignViewReport->campaign_status==1 && $campaignViewReport->affiliate_campaign_record==0)
                    if ($campaignViewReport->campaign_status == 1 && $campaignViewReport->affiliate_campaign_record->count == 0) {
                        continue;
                    }

                    if ($campaignViewReport->current_view_count < $revenueTracker->mixed_coreg_campaign_views) {
                        Log::info("campaign $campaignViewReport->campaign_id does not reached the threshold with $campaignViewReport->current_view_count count! skip reset!");

                        $skipReorderForThisTracker = true;
                        break;
                    }
                }

                if ($skipReorderForThisTracker) {
                    continue;
                } else {
                    foreach ($campaignViewReports as $campaignViewReport) {
                        $campaignViewReport->current_view_count = 0;
                        $campaignViewReport->save();
                    }
                }
            }
        }
    }

    private function shuffle_assoc($array)
    {
        //Initialize
        $shuffled_array = [];

        //Get array's keys and shuffle them.
        $shuffled_keys = array_keys($array);
        shuffle($shuffled_keys);

        //Create same array, but in shuffled order.
        foreach ($shuffled_keys as $shuffled_key) {
            $shuffled_array[$shuffled_key] = $array[$shuffled_key];
        }

        return $shuffled_array;
    }
}
