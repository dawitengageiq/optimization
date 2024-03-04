<?php

namespace App\Helpers\Repositories;

interface LeadDataInterface
{
    public function updateOrCreateLeadADV($lead, $advertiserURL);

    public function updateOrCreateLeadMessage($lead, $leadMessage);

    public function updateOrCreateLeadSentResult($lead, $leadSentResult);

    public function incrementLeadCount($campaignID, $affiliateID);
}
