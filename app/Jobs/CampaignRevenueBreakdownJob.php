<?php

namespace App\Jobs;

use App\AffiliateReport;
use App\CampaignRevenueBreakdown;
use App\LeadUser;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class CampaignRevenueBreakdownJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $date;

    protected $date_from;

    protected $date_to;

    protected $campaign;

    protected $thirty_date;

    protected $all_inbox_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($date, $campaign)
    {
        $this->date = Carbon::parse($date)->toDateString();
        $this->date_from = Carbon::parse($date)->startOfDay();
        $this->date_to = Carbon::parse($date)->endOfDay();
        $this->campaign = trim($campaign);

        $this->thirty_date = Carbon::parse($date)->subDay(30)->toDateString();

        $this->all_inbox_id = env('ALL_INBOX_CAMPAIGN_ID', 286);

        // Log::info($this->campaign);
        // Log::info($this->date);
        // Log::info($this->date_from);
        // Log::info($this->date_to);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //DB::enableQueryLog();
        if ($this->campaign == $this->all_inbox_id) {
            // Log::info('All Inbox');
            $records = LeadUser::whereBetween('created_at', [$this->date_from, $this->date_to])->where('status', 1)->where('response', 'like', '%<success><code>200</code><data>Data Accepted%')->count();
            $revenue = AffiliateReport::where('created_at', $this->date)->where('campaign_id', $this->campaign)
                ->select(DB::RAW('SUM(revenue) as revenue'))->first()->revenue;
        } else {
            $data = AffiliateReport::where('created_at', $this->date)->where('campaign_id', $this->campaign)
                ->select(DB::RAW('SUM(revenue) as revenue, SUM(lead_count) as records'))->first();
            $revenue = $data->revenue;
            $records = $data->records;
        }

        // $qry = "SELECT AVG(revenue) as revenue FROM (
        //     SELECT SUM(revenue) as revenue FROM affiliate_reports WHERE campaign_id = $this->campaign AND created_at BETWEEN '$this->thirty_date' AND '$this->date' GROUP BY created_at) t";
        $connect = config('app.type') == 'reports' ? 'mysql' : 'secondary';
        $qry = "SELECT (SUM(revenue) / 30) as revenue FROM affiliate_reports WHERE campaign_id = $this->campaign AND created_at BETWEEN '$this->thirty_date' AND '$this->date'";
        $data = DB::connection($connect)->select($qry);
        $average = $data[0]->revenue;

        $breakdown = CampaignRevenueBreakdown::firstOrNew([
            'created_at' => $this->date,
            'campaign_id' => $this->campaign,
        ]);
        $breakdown->revenue = $revenue == null ? 0 : $revenue;
        $breakdown->records = $records == null ? 0 : $records;
        $breakdown->average_revenue = $average == null ? 0 : $average;
        $breakdown->save();
        // Log::info(DB::getQueryLog());
        Log::info('Campaign Revenue Breakdown Campaign ID: '.$this->campaign.' on Date: '.$this->date);
    }
}
