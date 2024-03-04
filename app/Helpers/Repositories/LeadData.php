<?php

namespace App\Helpers\Repositories;

use App\LeadCount;
use App\LeadDataAdv;
use App\LeadDataCsv;
use App\LeadMessage;
use App\LeadSentResult;
use Log;
use PDOException;

class LeadData implements LeadDataInterface
{
    public function updateOrCreateLeadADV($lead, $advertiserURL)
    {
        if (isset($lead->leadDataADV)) {
            $lead->leadDataADV->value = $advertiserURL;
            $lead->leadDataADV->push();
        } else {
            try {

                $leadDataAdv = new LeadDataAdv();
                $leadDataAdv->value = $advertiserURL;
                $lead->leadDataADV()->save($leadDataAdv);

            } catch (PDOException $e) {
                if ($e->getCode() == 1062) {

                    Log::info($e->getMessage());

                    $leadDataAdv = LeadDataAdv::find($lead->id);
                    $leadDataAdv->value = $advertiserURL;
                    $leadDataAdv->save();

                }
            }
        }

        return $lead;
    }

    public function updateOrCreateLeadMessage($lead, $leadMessage)
    {
        if (isset($lead->leadMessage)) {
            $lead->leadMessage->value = $leadMessage;
            $lead->leadMessage->push();
        } else {
            try {

                $leadDataMessage = new LeadMessage();
                $leadDataMessage->value = $leadMessage;
                $lead->leadMessage()->save($leadDataMessage);

            } catch (PDOException $e) {
                if ($e->getCode() == 1062) {

                    Log::info($e->getMessage());

                    //somehow the data indeed exist therefore just update it
                    $leadDataMessage = LeadMessage::find($lead->id);
                    $leadDataMessage->value = $leadMessage;
                    $leadDataMessage->save();
                }
            }
        }

        return $lead;
    }

    public function updateOrCreateLeadSentResult($lead, $leadSentResult)
    {
        if (isset($lead->leadSentResult)) {
            $lead->leadSentResult->value = $leadSentResult;
            $lead->leadSentResult->push();
        } else {
            try {

                $leadDataSentResult = new LeadSentResult();
                $leadDataSentResult->value = $leadSentResult;
                $lead->leadSentResult()->save($leadDataSentResult);

            } catch (PDOException $e) {
                if ($e->getCode() == 1062) {

                    Log::info($e->getMessage());

                    $leadDataSentResult = LeadSentResult::find($lead->id);
                    $leadDataSentResult->value = $leadSentResult;
                    $leadDataSentResult->save();

                }
            }
        }

        return $lead;
    }

    public function updateOrCreateLeadCSV($lead, $leadCsv)
    {
        if (isset($lead->leadDataCSV)) {
            $lead->leadDataCSV->value = $leadCsv;
            $lead->leadDataCSV->push();
        } else {
            try {

                $leadCSVData = new LeadDataCsv();
                $leadCSVData->value = $leadCsv;
                $lead->leadDataCSV()->save($leadCSVData);

            } catch (PDOException $e) {

                if ($e->getCode() == 1062) {

                    Log::info($e->getMessage());

                    $leadCSVData = LeadDataCsv::find($lead->id);
                    $leadCSVData->value = $leadCsv;
                    $leadCSVData->save();
                }

            }
        }

        return $lead;
    }

    public function incrementLeadCount($campaignID, $affiliateID)
    {
        //get the campaign and affiliate campaign counts record for handling the capping
        $campaignCounts = LeadCount::getCount(['campaign_id' => $campaignID])->first();
        $campaignAffiliateCounts = LeadCount::getCount(['campaign_id' => $campaignID, 'affiliate_id' => $affiliateID])->first();

        //increment the counter here
        $campaignCounts->count = intval($campaignCounts->count) + 1;
        $campaignAffiliateCounts->count = intval($campaignAffiliateCounts->count) + 1;
        $campaignCounts->save();
        $campaignAffiliateCounts->save();
    }
}
