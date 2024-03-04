<?php

namespace App\Jobs;

use App\CampaignTypeOrder;
use App\CampaignTypeReport;
use App\Helpers\Repositories\Settings;
use App\Lead;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class ReorderCampaigns extends Job implements ShouldQueue
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
    public function handle(Settings $setting): void
    {
        // Log::info('REORDER');
        if ($this->attempts() > 1) {
            return;
        }

        //get all the global settings
        $globalOrderStatus = $setting->getValue('campaign_reordering_status');
        $globalViews = $setting->getValue('campaign_type_view_count');
        $date_time_now = Carbon::now()->toDateTimeString();

        $lead_report = [];

        if ($globalOrderStatus == 1) {
            //get all the campaign type reports
            // $reports = CampaignTypeReport::where('views','>',0)->with('revenueTracker')->get();
            $reports = CampaignTypeReport::join('affiliate_revenue_trackers', 'campaign_type_reports.revenue_tracker_id', '=', 'affiliate_revenue_trackers.revenue_tracker_id')
                ->join('campaign_type_orders', function ($join) {
                    $join->on('campaign_type_reports.campaign_type_id', '=', 'campaign_type_orders.campaign_type_id')
                        ->on('campaign_type_reports.revenue_tracker_id', '=', 'campaign_type_orders.revenue_tracker_id');
                })
                ->where('campaign_type_reports.views', '>', 0)
                ->select('campaign_type_reports.*', 'affiliate_revenue_trackers.order_by', 'affiliate_revenue_trackers.order_status', 'affiliate_revenue_trackers.views as rev_track_views', 'campaign_type_orders.campaign_id_order', 'campaign_type_orders.reorder_reference_date')
                ->get();
            // Log::info('REPORTS');
            // Log::info($reports);

            foreach ($reports as $report) {
                // Log::info('Campaign Type: '. $report->campaign_type_id);
                // Log::info('Rev Tracker: '. $report->revenue_tracker_id);

                $lead_report = [];

                $revenueTrackerOrderBy = $report->order_by;
                $revenueTrackerOrderStatus = $report->order_status;
                $revenueTrackerViews = $report->rev_track_views;

                //if revenue tracker views is set to 0 then follow the global views
                if ($revenueTrackerViews <= 0 && $globalViews > 0) {
                    $revenueTrackerViews = $globalViews;
                } elseif ($revenueTrackerViews <= 0 && $globalViews <= 0) {
                    //both 0 meaning no need to reorder
                    continue;
                }

                //calculate the ranking for campaigns
                if ($revenueTrackerViews > 0 && $revenueTrackerOrderStatus == 1) {
                    //get the campaign type order for revenue the revenue tracker
                    // $campaignTypeOrder = CampaignTypeOrder::where('revenue_tracker_id','=',$report->revenue_tracker_id)
                    //                                       ->where('campaign_type_id','=',$report->campaign_type_id)
                    //                                       ->whereNotNull('campaign_id_order')
                    //                                       ->first();

                    //Log::info("Campaign Type: $report->campaign_type_id");
                    //Log::info("Campaign Order: $campaignTypeOrder->campaign_id_order");
                    //Log::info("Report Views: $report->views");
                    //Log::info("Revenue Tracker Views: $revenueTrackerViews");

                    // if(!empty($campaignTypeOrder) && ($report->views>=$revenueTrackerViews))

                    if ($report->views >= $revenueTrackerViews) {
                        $campaignOrder = \GuzzleHttp\json_decode($report->campaign_id_order);
                        $leads = Lead::whereIn('campaign_id', $campaignOrder)
                            ->where('affiliate_id', $report->revenue_tracker_id)
                            ->where('lead_status', 1)
                            ->whereBetween('created_at', [$report->reorder_reference_date, $date_time_now])
                            ->select(DB::raw('COUNT(*) AS leads'), DB::raw('SUM(received) AS revenue'), 'campaign_id')
                            ->groupBy('campaign_id', 'affiliate_id')->get();

                        foreach ($leads as $lead) {
                            $lead_report[$report->revenue_tracker_id][$lead->campaign_id]['leads'] = $lead->leads;
                            $lead_report[$report->revenue_tracker_id][$lead->campaign_id]['revenue'] = $lead->revenue;

                            if ($lead->revenue == 0 || $lead->revenue == 0.0) {
                                $lead_report[$report->revenue_tracker_id][$lead->campaign_id]['revView'] = 0;
                            } else {
                                $lead_report[$report->revenue_tracker_id][$lead->campaign_id]['revView'] = floatval($lead->revenue) / floatval($report->views);
                            }
                        }

                        $campaignsOrderArray = [];

                        foreach ($campaignOrder as $campaignID) {
                            if (! isset($lead_report[$report->revenue_tracker_id][$campaignID])) {
                                $campaignsOrderArray[$campaignID] = 0;
                            } else {
                                $campaignsOrderArray[$campaignID] = $lead_report[$report->revenue_tracker_id][$campaignID]['revView'];
                            }
                        }

                        Log::info('Old Campaign Order: ');
                        Log::info($campaignsOrderArray);

                        $campaignTypeOrder = CampaignTypeOrder::where('revenue_tracker_id', '=', $report->revenue_tracker_id)
                            ->where('campaign_type_id', '=', $report->campaign_type_id)->first();

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

                            $newCampaignOrder = '[';
                            Log::info('New campaign order: ');
                            Log::info($campaignsOrderArray);

                            foreach ($campaignsOrderArray as $campaignID => $revenueValue) {
                                $newCampaignOrder = $newCampaignOrder.$campaignID.',';
                            }

                            $newCampaignOrder = rtrim($newCampaignOrder, ',').']';

                            //replace the old order

                            $campaignTypeOrder->campaign_id_order = $newCampaignOrder;

                        }

                        //reset the reference date and views
                        $report->views = 0;
                        $report->save();

                        $campaignTypeOrder->reorder_reference_date = $date_time_now;
                        $campaignTypeOrder->save();
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
