<?php

namespace App\Http\Services\Campaigns\Utils\Lists;

use Bus;
use RandomProbability;

final class Creatives
{
    /*
     * Default variables
     *
     */
    protected $creatives = [];

    /**
     * Set creatives
     *
     * @param  array  $creatives
     *
     * @var array
     */
    public function set($creatives, $campaignID)
    {
        if ($creatives && count($creatives) > 0) {
            if ($creativeID = Bus::dispatch(new RandomProbability(collect($creatives)->pluck('weight', 'id')))) {
                $this->creatives[$campaignID] = $creativeID; //Save creative id of campaign
            }
        }
    }

    /**
     * Get creatives
     *
     * @return array
     */
    public function get()
    {
        return $this->creatives;
    }
}
