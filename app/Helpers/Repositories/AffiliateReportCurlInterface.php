<?php

namespace App\Helpers\Repositories;

interface AffiliateReportCurlInterface
{
    public function campaignSummary($campaignSummaryBaseURL, $prefix, $subPrefix, $dateFromStr, $dateToStr);

    public function engageIQCPAWALLAffiliateSummary($affiliateSummaryBaseURL, $prefix, $revenueTrackerID, $dateFromStr, $dateToStr);

    public function campaignSummaryForCPAWALLDeduction($campaignSummaryBaseURL, $prefix, $subPrefix, $revenueTrackerID, $dateFromStr, $dateToStr);

    public function thirdPartyCPAWALLCampaignSummaryWithCD($affiliateSummaryBaseURL, $prefix, $revenueTrackerID, $dateFromStr, $dateToStr);

    public function clicksReport($clicksReportBaseURL, $prefix, $affiliateID, $campaignID, $dateFromStr, $dateToStr);
}
