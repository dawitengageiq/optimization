<?php

namespace App\Helpers;

use App\AffiliateReport;
use App\RevenueTrackerCakeStatistic;
use DB;
use Log;

class CleanAffiliateReportHelper
{
    protected $date;

    public function __construct($date)
    {
        $this->date = $date;
    }

    public function clean()
    {
        Log::info('Clean Affiliate Report');
        $con = config('app.type') == 'reports' ? 'mysql' : 'secondary';
        DB::connection($con)->enableQueryLog();
        $results = DB::connection($con)->select("SELECT rtcs.revenue_tracker_id, rtcs.s1, rtcs.s2, rtcs.s3, rtcs.s4, rtcs.s5, rtcs.clicks, rtcs.payout, 
        (SELECT SUM(lead_count) FROM affiliate_reports ar WHERE ar.revenue_tracker_id = rtcs.revenue_tracker_id 
            AND ar.s1 = rtcs.s1 AND ar.s2 = rtcs.s2 AND ar.s3 = rtcs.s3 AND ar.s4 = rtcs.s4 AND ar.s5 = rtcs.s5 AND ar.created_at = '$this->date') as lead_count,
        (SELECT SUM(revenue) FROM affiliate_reports ar WHERE ar.revenue_tracker_id = rtcs.revenue_tracker_id 
            AND ar.s1 = rtcs.s1 AND ar.s2 = rtcs.s2 AND ar.s3 = rtcs.s3 AND ar.s4 = rtcs.s4 AND ar.s5 = rtcs.s5 AND ar.created_at = '$this->date') as revenue
        FROM revenue_tracker_cake_statistics rtcs
        WHERE rtcs.created_at = '$this->date' 
        AND clicks = 0 AND payout = 0 
        HAVING lead_count = 0 AND revenue = 0");
        Log::info(DB::getQueryLog());
        Log::info($results);
        foreach ($results as $res) {
            AffiliateReport::where('revenue_tracker_id', $res->revenue_tracker_id)
                ->where('s1', $res->s1)->where('s2', $res->s2)->where('s3', $res->s3)
                ->where('s4', $res->s4)->where('s5', $res->s5)
                ->where('created_at', $this->date)
                ->delete();

            RevenueTrackerCakeStatistic::where('revenue_tracker_id', $res->revenue_tracker_id)
                ->where('s1', $res->s1)->where('s2', $res->s2)->where('s3', $res->s3)
                ->where('s4', $res->s4)->where('s5', $res->s5)
                ->where('created_at', $this->date)
                ->delete();
        }

        Log::info('Clean Affiliate Report Done');
    }
}
