<?php

namespace App\Jobs\Reports;

use App\AffiliateRevenueTracker;
use App\ClicksVsRegistrationStatistics;
use App\Helpers\Repositories\AffiliateReportCurl;
use App\Jobs\Job;
use App\LeadUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Log;

class GenerateClicksVsRegistrationStatistics extends Job implements ShouldQueue
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
        $revenueTrackers = AffiliateRevenueTracker::where('offer_id', '!=', 1)->get();

        foreach ($revenueTrackers as $tracker) {
            $cakeBaseClicksURL = config('constants.CAKE_CLICKS_REPORTS_API_V12');
            $cakeBaseClicksURL = str_replace('offer_id=0', "offer_id=$tracker->offer_id", $cakeBaseClicksURL);

            $prefix = config('constants.CAKE_SABRE_PREFIX_V12');
            $affiliateID = $tracker->affiliate_id;
            $campaignID = $tracker->campaign_id;

            $response = $affiliateReportCurl->clicksReport($cakeBaseClicksURL, $prefix, $affiliateID, $campaignID, $this->dateFromStr, $this->dateToStr);
            Log::info($response);

            $allClicks = intval($response['value'][$prefix.'row_count']);

            $dateFromStr = $this->dateFromStr;
            //$dateToStr = $this->dateToStr;

            //get the registered users. Use the same date since this assumes we are generating for a particular date.
            $registeredUsers = LeadUser::where('revenue_tracker_id', '=', $tracker->revenue_tracker_id)
                ->where('affiliate_id', '=', $tracker->affiliate_id)
                ->whereRaw("DATE(created_at) = DATE('$dateFromStr')")
                ->count();

            $clicksRegistrationStats = ClicksVsRegistrationStatistics::firstOrNew([
                'affiliate_id' => $tracker->affiliate_id,
                'revenue_tracker_id' => $tracker->revenue_tracker_id,
                'created_at' => $dateFromStr,
            ]);

            // sub ids
            //            $s1 = $click['value'][$prefix.'sub_id_1'];
            //            $s2 = $click['value'][$prefix.'sub_id_2'];
            //            $s3 = $click['value'][$prefix.'sub_id_3'];
            //            $s4 = $click['value'][$prefix.'sub_id_4'];
            //            $s5 = $click['value'][$prefix.'sub_id_5'];

            $clicksRegistrationStats->registration_count = $registeredUsers;
            $clicksRegistrationStats->clicks = $allClicks;

            $percentage = 0.00;

            if ($registeredUsers > 0 && $allClicks > 0) {
                $percentage = floatval($registeredUsers / $allClicks);
            }

            $clicksRegistrationStats->percentage = $percentage;

            if ($clicksRegistrationStats->registration_count == 0 && $clicksRegistrationStats->clicks == 0) {
                //delete if exist
                if (isset($clicksRegistrationStats->id)) {
                    $clicksRegistrationStats->delete();
                }
            } else {
                //save or update the record
                $clicksRegistrationStats->save();
            }

            //this will refresh the TTR of beanstalkd
            // $this->touch();
        }

        $emailNotificationRecipient = env('REPORTS_EMAIL_NOTIFICATION_RECIPIENT', 'marwilburton@hotmail.com');

        //send email to Burt to notify that Affiliate Report Queue was successfully finished
        Mail::send('emails.clicks_vs_registrations',
            ['startDate' => $this->dateFromStr, 'endDate' => $this->dateToStr],
            function ($m) use ($emailNotificationRecipient) {
                $m->from('ariel@engageiq.com', 'Ariel Magbanua');
                $m->to($emailNotificationRecipient, 'Marwil Burton')->subject('Clicks Vs Registrations Job Queue Successfully Executed!');
            });

        Log::info('Clicks Vs Registrations Job Queue Successfully Executed!');
    }

    public function touch()
    {
        if (method_exists($this->job, 'getPheanstalk')) {
            $this->job->getPheanstalk()->touch($this->job->getPheanstalkJob());

            Log::info('Current job timer refresh!');
        }
    }
}
