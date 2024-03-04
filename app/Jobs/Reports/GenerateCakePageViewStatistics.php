<?php

namespace App\Jobs\Reports;

use App\AffiliateRevenueTracker;
use App\Helpers\Repositories\AffiliateReportCurl;
use App\Jobs\Job;
use App\PageViewStatistics;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class GenerateCakePageViewStatistics extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    public $dateFromStr;

    public $dateToStr;

    /**
     * Create a new job instance.
     */
    public function __construct($dateFromStr, $dateToStr)
    {
        $this->dateFromStr = $dateFromStr;
        $this->dateToStr = $dateToStr;
    }

    /**
     * Execute the job.
     */
    public function handle(AffiliateReportCurl $affiliateReportCurl): void
    {
        $revenueTrackers = AffiliateRevenueTracker::all();

        foreach ($revenueTrackers as $tracker) {
            $cakeBaseClicksURL = config('constants.CAKE_CLICKS_REPORTS_API_V12');

            //inject the offer id
            $cakeBaseClicksURL = str_replace('offer_id=0', 'offer_id=499', $cakeBaseClicksURL);
            //Log::info("click_base_url: $cakeBaseClicksURL");

            $prefix = config('constants.CAKE_SABRE_PREFIX_V12');
            $affiliateID = $tracker->revenue_tracker_id;
            $campaignID = 0;

            $response = $affiliateReportCurl->clicksReport($cakeBaseClicksURL, $prefix, $affiliateID, $campaignID, $this->dateFromStr, $this->dateToStr);
            $clicks = $response['value'][$prefix.'clicks'];

            //$subIDPageViewMapping = config('constants.PAGE_VIEW_TABLE_COLUMN_MAPPING');

            $pageViewStats = PageViewStatistics::firstOrNew([
                'affiliate_id' => $tracker->affiliate_id,
                'revenue_tracker_id' => $tracker->revenue_tracker_id,
                'created_at' => $this->dateFromStr,
            ]);

            //reset all fields first
            $pageViewStats->lp = 0;
            $pageViewStats->rp = 0;
            $pageViewStats->to1 = 0;
            $pageViewStats->to2 = 0;
            $pageViewStats->mo1 = 0;
            $pageViewStats->mo2 = 0;
            $pageViewStats->mo3 = 0;
            $pageViewStats->mo4 = 0;
            $pageViewStats->lfc1 = 0;
            $pageViewStats->lfc2 = 0;
            $pageViewStats->tbr1 = 0;
            $pageViewStats->pd = 0;
            $pageViewStats->tbr2 = 0;
            $pageViewStats->iff = 0;
            $pageViewStats->rex = 0;
            $pageViewStats->cpawall = 0;
            $pageViewStats->exitpage = 0;

            if ((! empty($clicks) && $clicks != '') && is_array($clicks)) {
                foreach ($clicks as $click) {
                    $subID = $click['value'][$prefix.'sub_id_1'];
                    //$totalClicks = $click['value'][$prefix.'total_clicks'];

                    switch ($subID) {
                        case 'LP':
                            $pageViewStats->lp = $pageViewStats->lp + 1;
                            break;

                        case 'RP':
                            $pageViewStats->rp = $pageViewStats->rp + 1;
                            break;

                        case 'TO1':
                            $pageViewStats->to1 = $pageViewStats->to1 + 1;
                            break;

                        case 'TO2':
                            $pageViewStats->to2 = $pageViewStats->to2 + 1;
                            break;

                        case 'MO1':
                            $pageViewStats->mo1 = $pageViewStats->mo1 + 1;
                            break;

                        case 'MO2':
                            $pageViewStats->mo2 = $pageViewStats->mo2 + 1;
                            break;

                        case 'MO3':
                            $pageViewStats->mo3 = $pageViewStats->mo3 + 1;
                            break;

                        case 'MO4':
                            $pageViewStats->mo4 = $pageViewStats->mo4 + 1;
                            break;

                        case 'LFC1':
                            $pageViewStats->lfc1 = $pageViewStats->lfc1 + 1;
                            break;

                        case 'LFC2':
                            $pageViewStats->lfc2 = $pageViewStats->lfc2 + 1;
                            break;

                        case 'TBR1':
                            $pageViewStats->tbr1 = $pageViewStats->tbr1 + 1;
                            break;

                        case 'PD':
                            $pageViewStats->pd = $pageViewStats->pd + 1;
                            break;

                        case 'TBR2':
                            $pageViewStats->tbr2 = $pageViewStats->tbr2 + 1;
                            break;

                        case 'IFF':
                            $pageViewStats->iff = $pageViewStats->iff + 1;
                            break;

                        case 'REX':
                            $pageViewStats->rex = $pageViewStats->rex + 1;
                            break;

                        case 'CPAWALL':
                            $pageViewStats->cpawall = $pageViewStats->cpawall + 1;
                            break;

                        case 'EXITPAGE':
                            $pageViewStats->exitpage = $pageViewStats->exitpage + 1;
                            break;
                    }
                }
            }

            if ($pageViewStats->lp == 0 && $pageViewStats->rp == 0 && $pageViewStats->mo1 == 0 && $pageViewStats->exitpage == 0) {
                //delete if exist
                if (isset($pageViewStats->id)) {
                    $pageViewStats->delete();
                }
            } else {
                //save the record
                $pageViewStats->save();
            }

            //this will refresh the TTR of beanstalkd
            // $this->touch();
        }

        $emailNotificationRecipient = env('REPORTS_EMAIL_NOTIFICATION_RECIPIENT', 'marwilburton@hotmail.com');

        //send email to Burt to notify that Affiliate Report Queue was successfully finished
        Mail::send('emails.page_views_statistics',
            ['startDate' => $this->dateFromStr, 'endDate' => $this->dateToStr],
            function ($m) use ($emailNotificationRecipient) {
                $m->from('ariel@engageiq.com', 'Ariel Magbanua');
                $m->to($emailNotificationRecipient, 'Marwil Burton')->subject('Page Views Statistics Job Queue Successfully Executed!');
            });

        Log::info('Page Views Statistics Job Queue Successfully Executed!');
    }

    public function touch()
    {
        if (method_exists($this->job, 'getPheanstalk')) {
            $this->job->getPheanstalk()->touch($this->job->getPheanstalkJob());

            Log::info('Current job timer refresh!');
        }
    }
}
