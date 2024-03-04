<?php

namespace Database\Seeders;

use App\CampaignViewReport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CampaignViewReportsCleaner extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*// DB::enableQueryLog();
        // $creativeReport = CampaignViewReport::orderBy('revenue_tracker_id','ASC')->orderBy('campaign_id','ASC')->get();
        $creativeReport = DB::select('SELECT campaign_type_id, campaign_id, SUM(total_view_count) as total_view_count, SUM(current_view_count) as current_view_count, revenue_tracker_id
            FROM campaign_view_reports
            GROUP BY campaign_id, revenue_tracker_id
            ORDER BY revenue_tracker_id ASC, campaign_id ASC');

        // Log::info($creativeReport);
        DB::table('campaign_view_reports')->truncate();

        foreach($creativeReport as $report) {
            // Log::info($report->revenue_tracker_id.' - '.$report->campaign_id.' - '.$report->campaign_type_id);
            CampaignViewReport::firstOrCreate([
                'campaign_type_id'		=> $report->campaign_type_id,
                'campaign_id'			=> $report->campaign_id,
                'total_view_count'		=> $report->total_view_count,
                'current_view_count'	=> $report->current_view_count,
                'revenue_tracker_id'	=> $report->revenue_tracker_id
            ]);
        }

        // Log::info($creativeReport);
        // Log::info(DB::getQueryLog());*/

        $creativeReport = DB::select('SELECT campaign_type_id, campaign_id, SUM(total_view_count) as total_view_count, SUM(current_view_count) as current_view_count, revenue_tracker_id 
            FROM campaign_view_reports
            GROUP BY campaign_id, revenue_tracker_id
            ORDER BY revenue_tracker_id ASC, campaign_id ASC');

        DB::table('campaign_view_reports')->truncate();

        foreach ($creativeReport as $report) {
            // Log::info($report->revenue_tracker_id.' - '.$report->campaign_id.' - '.$report->campaign_type_id);
            $campaign_report = CampaignViewReport::firstOrNew([
                'campaign_id' => $report->campaign_id,
                'revenue_tracker_id' => $report->revenue_tracker_id,
            ]);

            $campaign_report->campaign_type_id = $report->campaign_type_id;
            $campaign_report->total_view_count = $report->total_view_count;
            $campaign_report->current_view_count = $report->current_view_count;
            $campaign_report->save();
        }
    }
}
