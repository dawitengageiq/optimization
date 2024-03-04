<?php

namespace App\Jobs;

use App\CampaignView;
use App\CampaignViewReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class SaveCampaignView extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $details;

    /**
     * Create a new job instance.
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->attempts() > 1) {
            return;
        }

        // extract($this->details);

        try {
            CampaignView::create($this->details);
            // CampaignView::create([  //save the view
            //     'campaign_id'   => $id,
            //     'affiliate_id'  => $affiliate_id,
            //     'session'       => $session,
            //     'path_id'       => $path,
            //     'creative_id'   => $creative_id
            // ]);
            //echo 'Campaign Saved!';

            $report = CampaignViewReport::firstOrNew([
                'campaign_id' => $this->details['campaign_id'],
                'revenue_tracker_id' => $this->details['affiliate_id'],
            ]);

            if ($report->exists) {
                $report->total_view_count = $report->total_view_count + 1;
                $report->current_view_count = $report->current_view_count + 1;
                $report->campaign_type_id = $this->details['campaign_type_id'];
            } else {
                $report->total_view_count = 1;
                $report->current_view_count = 1;
                $report->campaign_type_id = $this->details['campaign_type_id'];
            }

            $report->campaign_type_id = $this->details['campaign_type_id'];
            $report->save();
        } catch (QueryException $exception) {
            // $errorCode = $exception->errorInfo[1];
            // Log::info('Campaign View insert error code: '.$errorCode);
            // Log::info($exception->getMessage());
            //echo 'Campaign Not saved!';
        }
    }
}
