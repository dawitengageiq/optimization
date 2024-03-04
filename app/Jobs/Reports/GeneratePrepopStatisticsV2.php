<?php

namespace App\Jobs\Reports;

use App\AffiliateReport;
use App\Jobs\Job;
use App\PrepopStatistic;
use App\RevenueTrackerCakeStatistic;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class GeneratePrepopStatisticsV2 extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $date;

    /**
     * Create a new job instance.
     *
     * @param $skip
     * @param $take
     * @param $dateFromStr
     * @param $dateToStr
     */
    public function __construct($date)
    {
        $this->date = $date;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->attempts() > 1) {
            return;
        }

        // Log::info("Prepop Group Starting...");
        // DB::enableQueryLog();
        $revenue_reports = AffiliateReport::where('created_at', $this->date)
            ->selectRAW('revenue_tracker_id, affiliate_id, s1, s2, s3, s4, s5, SUM(revenue) as revenue')
            ->groupBy(['revenue_tracker_id', 'affiliate_id', 's1', 's2', 's3', 's4', 's5'])
            ->get();

        $revenues = [];
        foreach ($revenue_reports as $report) {
            $revenues[$report->affiliate_id][$report->revenue_tracker_id][$report->s1][$report->s2][$report->s3][$report->s4][$report->s5] = $report->revenue;
        }

        $payout_reports = RevenueTrackerCakeStatistic::where('revenue_tracker_cake_statistics.created_at', $this->date)->leftJoin('affiliate_revenue_trackers', function ($sq) {
            $sq->on('revenue_tracker_cake_statistics.affiliate_id', '=', 'affiliate_revenue_trackers.affiliate_id')
                ->where('revenue_tracker_cake_statistics.revenue_tracker_id', '=', 'affiliate_revenue_trackers.revenue_tracker_id')
                ->where('revenue_tracker_cake_statistics.cake_campaign_id', '=', 'affiliate_revenue_trackers.campaign_id');
        })
            ->selectRAW('revenue_tracker_cake_statistics.*')
            ->get();

        $payouts = [];
        foreach ($payout_reports as $report) {
            $payouts[$report->affiliate_id][$report->revenue_tracker_id][$report->s1][$report->s2][$report->s3][$report->s4][$report->s5] = $report->payout;
        }

        $prepops = PrepopStatistic::where('created_at', $this->date)
            ->get();

        foreach ($prepops as $prepop) {
            $margin = 0;
            $aff = $prepop->affiliate_id;
            $rt = $prepop->revenue_tracker_id;
            $s1 = $prepop->s1;
            $s2 = $prepop->s2;
            $s3 = $prepop->s3;
            $s4 = $prepop->s4;
            $s5 = $prepop->s5;

            $revenue = isset($revenues[$aff][$rt][$s1][$s2][$s3][$s4][$s5]) ? $revenues[$aff][$rt][$s1][$s2][$s3][$s4][$s5] : 0;
            $payout = isset($payouts[$aff][$rt][$s1][$s2][$s3][$s4][$s5]) ? $payouts[$aff][$rt][$s1][$s2][$s3][$s4][$s5] : 0;
            $numerator = floatval($revenue) - floatval($payout);
            if ($numerator > 0 && $revenue > 0) {
                $margin = ($numerator / floatval($revenue));

                if ($margin != 0) {
                    $prepop->profit_margin = $margin;
                    $prepop->save();
                }
            }
        }

        // Log::info(DB::getQueryLog());
    }
}
