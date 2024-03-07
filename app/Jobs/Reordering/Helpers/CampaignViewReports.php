<?php

namespace App\Jobs\Reordering\Helpers;

class CampaignViewReports
{
    /**
     * Initialization
     */
    public function __construct()
    {
    }

    /**
     * Set the campaign view reports Model
     * The model was already fetch in revenuTrackers::query
     *
     *
     * @var coleection
     */
    public function set(eloquentCollection $campaignViewReports)
    {
        $this->campaignViewReports = $campaignViewReports;
    }

    /**
     * Go through all campaign views and reset.
     */
    public function reset()
    {
        foreach ($this->getCampaignViewReport() as $campaignViewReport) {
            $this->saveReset($campaignViewReport);
        }
    }

    /**
     * Get specific campaign view reports
     *
     * @return yield
     */
    protected function getCampaignViewReport(): yield
    {
        for ($i = 0; $i < count($this->campaignViewReports); $i++) {
            yield $this->campaignViewReports[$i];
        }
    }

    /**
     * Reset the count to 0 and save.
     * Status should not be private or hiddem.
     * If status is public but record count is 0, exlude.
     */
    protected function saveReset(eloquentCollection $campaignViewReport): void
    {
        //exempt all inactive and hidden campaigns
        if ($campaignViewReport->campaignInfo->status != 1 && $campaignViewReport->campaignInfo->status != 2) {
            return;
        }

        //ignore if campaign is private and if revenue tracker does not belong to this campaign
        if ($campaignViewReport->campaignInfo->status == 1 && $campaignViewReport->affiliate_campaign_record->count == 0) {
            return;
        }

        $campaignViewReport->current_view_count = 0;
        $campaignViewReport->save();

    }
}
