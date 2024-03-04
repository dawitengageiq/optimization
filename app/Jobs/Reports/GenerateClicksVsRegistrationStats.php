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

class GenerateClicksVsRegistrationStats extends Job implements ShouldQueue
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
        $prefix = config('constants.CAKE_SABRE_PREFIX_V12');
        $cakeBaseClicksURL = config('constants.CAKE_CLICKS_REPORTS_API_V12');

        foreach ($revenueTrackers as $tracker) {
            $affiliateID = $tracker->affiliate_id;
            $campaignID = $tracker->campaign_id;
            $rowStart = 0;

            while (true) {
                $cakeBaseClicksURL = str_replace('offer_id=0', "offer_id=$tracker->offer_id", $cakeBaseClicksURL);
                $cakeBaseClicksURL = str_replace('start_at_row=0', "start_at_row=$rowStart", $cakeBaseClicksURL);
                $cakeBaseClicksURL = str_replace('row_limit=0', 'row_limit=1000', $cakeBaseClicksURL);

                $response = $affiliateReportCurl->clicksReport($cakeBaseClicksURL, $prefix, $affiliateID, $campaignID, $this->dateFromStr, $this->dateToStr);
                // $rowCount = intval($response['value'][$prefix.'row_count']);

                $clicks = $response['value'][$prefix.'clicks'];

                if (count($clicks) <= 0) {
                    break;
                } else {
                    $dateFromStr = $this->dateFromStr;
                    //$dateToStr = $this->dateToStr;

                    $clickCountCollection = collect([]);

                    foreach ($clicks as $click) {
                        $s1 = $click['value'][$prefix.'sub_id_1'];
                        $s2 = $click['value'][$prefix.'sub_id_2'];
                        $s3 = $click['value'][$prefix.'sub_id_3'];
                        $s4 = $click['value'][$prefix.'sub_id_4'];
                        $s5 = $click['value'][$prefix.'sub_id_5'];

                        if ($clickCountCollection->has("$s1 $s2 $s3 $s4 $s5")) {
                            $val = $clickCountCollection->get("$s1 $s2 $s3 $s4 $s5") + 1;
                            $clickCountCollection->put("$s1 $s2 $s3 $s4 $s5", $val);
                        } else {
                            $clickCountCollection->put("$s1 $s2 $s3 $s4 $s5", 1);
                        }
                    }

                    // get each registration per subid set
                    $registrationsCollection = $clickCountCollection->map(function ($item, $key) use ($dateFromStr, $tracker) {
                        // Log::info("$key => $item");

                        // get all subids
                        $subIDPieces = explode(' ', $key);
                        $s1 = isset($subIDPieces[0]) ? $subIDPieces[0] : '';
                        $s2 = isset($subIDPieces[1]) ? $subIDPieces[1] : '';
                        $s3 = isset($subIDPieces[2]) ? $subIDPieces[2] : '';
                        $s4 = isset($subIDPieces[3]) ? $subIDPieces[3] : '';
                        $s5 = isset($subIDPieces[4]) ? $subIDPieces[4] : '';

                        // Log::info("sub_ids: ");
                        // Log::info("$s1 $s2 $s3 $s4 $s5");

                        //get the registered users. Use the same date since this assumes we are generating for a particular date.
                        $registeredUsers = LeadUser::where('revenue_tracker_id', '=', $tracker->revenue_tracker_id)
                            ->where('affiliate_id', '=', $tracker->affiliate_id)
                            ->where(function ($query) use ($s1, $s2, $s3, $s4, $s5) {
                                $query->where('s1', '=', $s1);
                                $query->where('s2', '=', $s2);
                                $query->where('s3', '=', $s3);
                                $query->where('s4', '=', $s4);
                                $query->where('s5', '=', $s5);
                            })
                            ->whereRaw("DATE(created_at) = DATE('$dateFromStr')")
                            ->count();

                        // return [$key => $registeredUsers];
                        return $registeredUsers;
                    });

                    $registrationsCollection->map(function ($item, $key) use ($clickCountCollection, $dateFromStr, $tracker) {
                        $clicks = $clickCountCollection->get($key);
                        $registrations = $item;

                        // Log::info('$registrationsCollection: registration and clicks');
                        // Log::info($registrations);
                        // Log::info($clicks);
                        // Log::info('$registrationsCollection: key and item');
                        // Log::info($key);
                        // Log::info($item);

                        $subIDPieces = explode(' ', $key);
                        $s1 = isset($subIDPieces[0]) ? $subIDPieces[0] : '';
                        $s2 = isset($subIDPieces[1]) ? $subIDPieces[1] : '';
                        $s3 = isset($subIDPieces[2]) ? $subIDPieces[2] : '';
                        $s4 = isset($subIDPieces[3]) ? $subIDPieces[3] : '';
                        $s5 = isset($subIDPieces[4]) ? $subIDPieces[4] : '';

                        $clicksRegistrationStats = ClicksVsRegistrationStatistics::firstOrNew([
                            'affiliate_id' => $tracker->affiliate_id,
                            'revenue_tracker_id' => $tracker->revenue_tracker_id,
                            'created_at' => $dateFromStr,
                            's1' => $s1,
                            's2' => $s2,
                            's3' => $s3,
                            's4' => $s4,
                            's5' => $s5,
                        ]);

                        $clicksRegistrationStats->registration_count = $registrations;
                        $clicksRegistrationStats->clicks = $clicks;

                        $percentage = 0.00;

                        if ($registrations > 0 && $clicks > 0) {
                            $percentage = floatval($registrations / $clicks);
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
                    });
                }
            }
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
}
