<?php

namespace App\Jobs\Reordering\Helpers;

class MixedCoregCampaignOrder
{
    /**
     * Inintialize
     */
    public function __construct()
    {
    }

    /**
     * set mix coreg campaign type ids
     */
    public function set(array $mixedCoreg)
    {
        $this->mixedCoreg = $mixedCoreg;
    }

    /**
     * Set current date
     * It will be used on saving neworder reference date.
     *
     * @param  string|timestamp  $now
     */
    public function setCurrentDate($now)
    {
        $this->newReferenceDate = $now;
    }

    /**
     * get the order of campaign id
     */
    public function campaignIdOrder(): array
    {
        return $this->mixedCoreg->campaign_id_order;
    }

    /**
     * Save the new order
     *
     * @param  string|json  $newOrders
     */
    public function save($newOrders)
    {
        // reset the reference date and order
        $this->mixedCoreg->campaign_id_order = $newOrders;
        $this->mixedCoreg->reorder_reference_date = $this->newReferenceDate;
        $this->mixedCoreg->save();
    }
}
