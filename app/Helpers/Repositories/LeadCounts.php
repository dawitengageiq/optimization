<?php
/**
 * Created by PhpStorm.
 * User: magbanua-ariel
 * Date: 8/22/2017
 * Time: 1:10 PM
 */

namespace App\Helpers\Repositories;

use Carbon\Carbon;
use Log;

class LeadCounts implements LeadCountsInterface
{
    protected $campaign_id;

    protected $affiliate_id;

    protected $cap_type;

    /**
     * @param  null  $affiliateID
     * @param  null  $timezone
     */
    public function executeReset($campaignCounts, $campaignID, $affiliateID = null, string $capType = 'Unlimited', $timezone = null, $updateMe = true)
    {
        // Log::info('Reset the campaign_id = '.$campaignID);
        // Log::info('Reset the affiliate_id = '.$affiliateID);
        Log::info("Reset C: $campaignID A: $affiliateID");
        //check campaign cap
        switch ($capType) {
            case 'Daily':

                //daily campaign count reset and set the timezone
                $dailyReferenceDate = Carbon::parse($campaignCounts->reference_date);
                //set the timezone of the campaign
                $dailyReferenceDate->timezone = $timezone;

                $dateNowWithCampaignTimezone = Carbon::now($timezone);

                //if($dailyReferenceDate->startOfDay()->diffInDays(Carbon::now()->startOfDay())>0)
                if ($dailyReferenceDate->startOfDay()->diffInDays($dateNowWithCampaignTimezone->startOfDay()) > 0) {
                    //this means already past one day and count needs to reset
                    $campaignCounts->count = 0;
                    //change the reference date as well
                    //$campaignCounts->reference_date = Carbon::now()->toDateTimeString();
                    //$campaignCounts->reference_date = $dateNowWithCampaignTimezone->toDateTimeString();
                    $campaignCounts->reference_date = Carbon::now($timezone)->toDateTimeString();
                }

                break;

            case 'Weekly':

                //reset if start of the week
                $dailyReferenceDate = Carbon::parse($campaignCounts->reference_date);
                //set the timezone of the campaign
                $dailyReferenceDate->timezone = $timezone;
                $refWeekOfMonth = $dailyReferenceDate->weekOfMonth;
                $refMonth = $dailyReferenceDate->month;

                $dateNowWithCampaignTimezone = Carbon::now($timezone);
                $currentWeekOfMonth = $dateNowWithCampaignTimezone->weekOfMonth;
                $currentMonth = $dateNowWithCampaignTimezone->month;

                if (($currentWeekOfMonth != $refWeekOfMonth) || (($currentWeekOfMonth == $refWeekOfMonth) && ($currentMonth != $refMonth))) {
                    //this means already shifted to different week
                    $campaignCounts->count = 0;
                    //set the new reference date for the current week
                    //$campaignCounts->reference_date = $dateNowWithCampaignTimezone->toDateTimeString();
                    $campaignCounts->reference_date = Carbon::now($timezone)->toDateTimeString();
                }

                break;

            case 'Monthly':

                //reset if start of the month
                $dailyReferenceDate = Carbon::parse($campaignCounts->reference_date);
                //set the timezone of the campaign
                $dailyReferenceDate->timezone = $timezone;

                $refMonth = $dailyReferenceDate->month;

                $dateNowWithCampaignTimezone = Carbon::now($timezone);
                $currentMonth = $dateNowWithCampaignTimezone->month;

                if ($currentMonth != $refMonth) {
                    //this means already shifted to different month
                    $campaignCounts->count = 0;
                    //set the new reference date for the current month
                    //$campaignCounts->reference_date = $dateNowWithCampaignTimezone->toDateTimeString();
                    $campaignCounts->reference_date = Carbon::now($timezone)->toDateTimeString();
                }

                break;

            case 'Yearly':

                //reset if start of year
                $dailyReferenceDate = Carbon::parse($campaignCounts->reference_date);
                //set the timezone of the campaign
                $dailyReferenceDate->timezone = $timezone;

                $refYear = $dailyReferenceDate->year;

                $dateNowWithCampaignTimezone = Carbon::now($timezone);
                $currentYear = $dateNowWithCampaignTimezone->year;

                if ($currentYear != $refYear) {
                    //this means already shifted to different year
                    $campaignCounts->count = 0;
                    //set the new reference date for the current year
                    //$campaignCounts->reference_date = $dateNowWithCampaignTimezone->toDateTimeString();
                    $campaignCounts->reference_date = Carbon::now($timezone)->toDateTimeString();
                }

                break;

            default:
                //unlimited
        }

        // Log::info("date now with timezone: $campaignCounts->reference_date");
        if ($updateMe) {
            $campaignCounts->save();
        } else {
            return $campaignCounts;
        }
    }

    public function returnDetails()
    {
        return [
            $this->campaign_id,
            $this->affiliate_id,
            $this->cap_type,
        ];
    }
}
