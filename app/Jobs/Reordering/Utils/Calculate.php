<?php

namespace App\Jobs\Reordering\Utils;

class Calculate
{
    /**
     * Default variables
     */
    protected $orders = [];

    /**
     * Set campaign order
     * This is the saved campaign ids order from previous ordering
     *
     * @param  array  $campaignOrder
     */
    public function setCampaignOrder(array $campaignOrder)
    {
        $this->campaignOrder = $campaignOrder;
    }

    /**
     * Get campaign ids Order with calculated revenue per views
     *
     * @return array
     */
    public function getOrders(): array
    {
        return $this->orders;
    }

    /**
     * Go through all campaign ids order and execute calculation
     *
     * @param  eloquentCollection  $leads
     */
    public function revenuePerViews(eloquentCollection $leads)
    {
        $this->orders = iterator_to_array($this->getCalculation($leads));
    }

    /**
     * Calculate per campaign id
     *
     * @return yield
     */
    protected function getCalculation($leads)
    {
        for ($i = 0; $i < count($this->campaignOrder); $i++) {
            $campaignID = $this->campaignOrder[$i];
            yield $campaignID => $this->calculate($campaignID, $leads);
        }
    }

    /**
     * Execute the calculation of revenue per views
     *
     * @param  int  $campaignID
     * @param  eloquentCollection  $leads
     * @return float|int
     */
    protected function calculate(int $campaignID, eloquentCollection $leads)
    {
        if (array_key_exists($campaignID, $leads->toArray())) {
            if ($leads[$campaignID]->campaignViewReport->current_view_count <= 0
            || ($leads[$campaignID]->revenue == 0 || $leads[$campaignID]->revenue == 0.0)
            ) {
                return 0;
            }

            return floatval($leads[$campaignID]->revenue) / floatval($leads[$campaignID]->campaignViewReport->current_view_count);
        }

        return 0;
    }
}
