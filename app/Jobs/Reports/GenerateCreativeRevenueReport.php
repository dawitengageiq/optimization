<?php

namespace App\Jobs\Reports;

use App\CampaignView;
use App\CreativeReport;
use App\Jobs\Job;
use App\Lead;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class GenerateCreativeRevenueReport extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $dateYesterday;

    /**
     * Create a new job instance.
     */
    public function __construct($dateYesterday)
    {
        $this->dateYesterday = $dateYesterday;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->attempts() > 1) {
            return;
        }

        $this->dateYesterday = Carbon::yesterday()->toDateString();
        // Log::info('Generating Report for '.$this->dateYesterday);

        $inputs['lead_date_today'] = $this->dateYesterday;

        $leads = Lead::creativeRevenueReport($inputs)
            ->select(DB::RAW('campaign_id, path_id, creative_id, lead_status, received, COUNT(*) as lead_count, affiliate_id'))
            ->get();

        $views = CampaignView::creativeRevenueReport($inputs)
            ->select(DB::RAW('campaign_id, path_id, creative_id, COUNT(*) as views, affiliate_id'))
            ->get();

        $reports = [];

        //PATH ID - CAMPAIGN ID - CREATIVE ID - AFFILIATE ID
        foreach ($leads as $lead) {
            if (! isset($reports[$lead->path_id][$lead->campaign_id][$lead->creative_id][$lead->affiliate_id])) {
                $reports[$lead->path_id][$lead->campaign_id][$lead->creative_id][$lead->affiliate_id]['lead_count'] = 0;
                $reports[$lead->path_id][$lead->campaign_id][$lead->creative_id][$lead->affiliate_id]['revenue'] = 0;
                $reports[$lead->path_id][$lead->campaign_id][$lead->creative_id][$lead->affiliate_id]['views'] = 0;
            }

            $reports[$lead->path_id][$lead->campaign_id][$lead->creative_id][$lead->affiliate_id]['lead_count'] += $lead->lead_count;

            if ($lead->lead_status == 1) {
                $reports[$lead->path_id][$lead->campaign_id][$lead->creative_id][$lead->affiliate_id]['revenue'] += $lead->received * $lead->lead_count;
            }
        }

        foreach ($views as $view) {
            if (! isset($reports[$view->path_id][$view->campaign_id][$view->creative_id][$view->affiliate_id])) {
                $reports[$view->path_id][$view->campaign_id][$view->creative_id][$view->affiliate_id]['lead_count'] = 0;
                $reports[$view->path_id][$view->campaign_id][$view->creative_id][$view->affiliate_id]['revenue'] = 0;
                $reports[$view->path_id][$view->campaign_id][$view->creative_id][$view->affiliate_id]['views'] = 0;
            }

            $reports[$view->path_id][$view->campaign_id][$view->creative_id][$view->affiliate_id]['views'] += $view->views;
        }

        foreach ($reports as $path_id => $campaign_details) {
            foreach ($campaign_details as $campaign_id => $creative_details) {
                foreach ($creative_details as $creative_id => $affiliate_details) {
                    foreach ($affiliate_details as $affiliate_id => $details) {
                        // Log::info($path_id .' - '. $campaign_id .' - '. $creative_id);
                        $data = CreativeReport::firstOrNew([
                            'path_id' => $path_id,
                            'campaign_id' => $campaign_id,
                            'creative_id' => $creative_id,
                            'affiliate_id' => $affiliate_id,
                            'date' => $this->dateYesterday,
                        ]);
                        $data->views = $details['views'];
                        $data->lead_count = $details['lead_count'];
                        $data->revenue = $details['revenue'];
                        $data->save();
                    }
                }
            }
        }

        //DELETING PREVIOUS CAMPAIGN VIEWS
        $firstDayOfWeek = Carbon::yesterday()->startOfWeek()->toDateString();
        $lastDayOfLastWeek = Carbon::yesterday()->subWeek()->endOfWeek()->toDateString();

        //if yesterday was the first day of the week, delete entries less than last day of last week
        if ($this->dateYesterday == $firstDayOfWeek) {
            // Log::info('Delete');
            // DB::enableQueryLog();
            $connection = config('app.type') == 'reports' ? 'secondary' : 'mysql';
            DB::connection($connection)->table('campaign_views')->whereRaw('date(created_at) <= "'.$lastDayOfLastWeek.'"')->delete();
            // Log::info(DB::getQueryLog());
        }

        Log::info('Creative revenue report is generated for '.$this->dateYesterday);
    }
}
