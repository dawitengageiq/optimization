<?php

namespace App\Jobs;

use App\AffiliateRevenueTracker;
use App\CakeRevenue;
use App\Helpers\Repositories\AffiliateReportCurl;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Log;

class GenerateCakeRevenues extends Job implements ShouldQueue
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
        $prefix = config('constants.CAKE_SABRE_PREFIX_V5');
        $subPrefix = config('constants.CAKE_SABRE_SUB_PREFIX_V11');

        foreach ($revenueTrackers as $tracker) {
            $campaignSummaryBaseURL = config('constants.CAKE_API_CAMPAIGN_SUMMARY_ALL_CAMPAIGNS_BASE_URL_V5');
            $affiliateID = $tracker->revenue_tracker_id;
            $campaignSummaryBaseURL = str_replace('source_affiliate_id=0', "source_affiliate_id=$affiliateID", $campaignSummaryBaseURL);

            $campaigns = $affiliateReportCurl->campaignSummary($campaignSummaryBaseURL, $prefix, null, $this->dateFromStr, $this->dateToStr);

            foreach ($campaigns as $campaign) {
                $offerID = $campaign['value'][$prefix.'site_offer'][$subPrefix.'site_offer_id'];
                $revenue = $campaign['value'][$prefix.'revenue'];

                $cakeRevenue = CakeRevenue::firstOrNew([
                    'affiliate_id' => $tracker->affiliate_id,
                    'revenue_tracker_id' => $tracker->revenue_tracker_id,
                    'offer_id' => $offerID,
                    'created_at' => $this->dateFromStr,
                ]);

                Log::info('revenue_tracker_id: '.$tracker->revenue_tracker_id);
                Log::info('offer_id: '.$offerID);
                Log::info('revenue: '.$revenue);

                $cakeRevenue->revenue = $revenue;
                $cakeRevenue->save();
            }
        }

        $emailNotificationRecipient = env('REPORTS_EMAIL_NOTIFICATION_RECIPIENT', 'marwilburton@hotmail.com');

        // send email to Burt to notify that Affiliate Report Queue was successfully finished
        Mail::send('emails.cake_revenues',
            ['startDate' => $this->dateFromStr, 'endDate' => $this->dateToStr],
            function ($m) use ($emailNotificationRecipient) {
                $m->from('ariel@engageiq.com', 'Ariel Magbanua');
                $m->to($emailNotificationRecipient, 'Marwil Burton')->subject('Cake Revenues Job Queue Successfully Executed!');
            });

        Log::info('Clicks Vs Registrations Job Queue Successfully Executed!');
    }
}
