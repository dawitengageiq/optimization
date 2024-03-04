<?php

namespace App\Jobs;

use App\OfferGoesDown;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class OfferGoesDownJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $campaign;

    protected $affiliates;

    protected $start_date;

    protected $end_date;

    protected $lead_conn;

    protected $now;

    protected $option;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($campaign_id, $affiliates, $option)
    {
        $this->campaign = $campaign_id;
        $this->affiliates = $affiliates;

        $this->now = Carbon::now()->toDateString();
        $this->end_date = Carbon::yesterday()->toDateString();
        $this->start_date = Carbon::yesterday()->subDay(6)->toDateString();

        $this->lead_conn = config('app.type') == 'reports' ? 'secondary' : 'mysql';

        $this->option = $option;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->option == 'p2p') { //Public to Private Option
            //Get Removed Affilaites
            $stats = DB::select("SELECT revenue_tracker_id FROM affiliate_reports 
            LEFT JOIN affiliate_campaign ON affiliate_campaign.campaign_id = affiliate_reports.campaign_id  AND affiliate_campaign.affiliate_id = affiliate_reports.revenue_tracker_id  
            WHERE affiliate_reports.campaign_id = $this->campaign AND affiliate_reports.created_at BETWEEN '$this->start_date' AND '$this->end_date' AND affiliate_campaign.id IS NULL GROUP BY revenue_tracker_id");
            $affiliates = [];
            foreach ($stats as $st) {
                $affiliates[] = $st->revenue_tracker_id;
            }

            if (count($affiliates)) {
                return;
            } else {
                $this->affiliates = $affiliates;
            }
        }

        $data = OfferGoesDown::firstOrNew([
            'campaign_id' => $this->campaign,
            'date' => $this->now,
        ]);

        if ($data->exists) { //Check if has existing;
            if ($this->affiliates != null) {
                $existing_affiliates = explode(',', $data->affiliates);
                $this->affiliates = array_merge($existing_affiliates, $this->affiliates);
            }
        }

        if ($this->affiliates == null) { //ALL
            $hasAffQry = '';
            $isAll = true;
        } else {
            $affs = implode(',', $this->affiliates);
            $hasAffQry = 'AND affiliate_id IN ('.$affs.')';
            $isAll = false;
        }

        $qry = "SELECT affiliate_id, SUM(lead_count) as total FROM affiliate_reports WHERE campaign_id = $this->campaign $hasAffQry AND created_at BETWEEN '$this->start_date' AND '$this->end_date' GROUP BY affiliate_id ORDER BY total DESC";

        $connect = config('app.type') == 'reports' ? 'mysql' : 'secondary';
        $stats = DB::connection($connect)->select($qry);
        $affiliates = [];
        foreach ($stats as $st) {
            $affiliates[] = $st->affiliate_id;
        }

        if (is_array($this->affiliates)) {
            $diff = array_diff($this->affiliates, $affiliates);
            $affiliates = array_merge($affiliates, $diff);
        }

        // $qry = "SELECT AVG(revenue) as average FROM (
        // SELECT SUM(revenue) as revenue FROM affiliate_reports WHERE campaign_id = $this->campaign AND created_at BETWEEN '$this->start_date' AND '$this->end_date' GROUP BY created_at) t";
        $qry = "SELECT (SUM(revenue)/7) as revenue FROM affiliate_reports WHERE campaign_id = $this->campaign AND created_at BETWEEN '$this->start_date' AND '$this->end_date'";
        $stats = DB::connection($connect)->select($qry);
        $average = $stats[0]->revenue;

        // Log::info('Offer Goes Down Average');
        // Log::info($connect);
        // Log::info($qry);
        // Log::info($average);
        if ($isAll) {
            $affiliates = array_merge(['All'], $affiliates);
        }
        $data->affiliates = implode(',', $affiliates);
        $data->revenue = $average == null ? 0 : $average;
        $data->save();
    }
}
