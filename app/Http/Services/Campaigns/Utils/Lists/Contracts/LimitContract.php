<?php

namespace App\Http\Services\Campaigns\Utils\Lists\Contracts;

interface LimitContract
{
    /**
     * Set limits
     *
     * @param array limit,
     * @param  int  $globalLimitPerStack,
     * @param  string  $campaignTypeLimit
     */
    public function set($limit);

    /**
     * Check limit each campaign type
     *
     * @param  collection  $campaign
     * @param  int  $stackCount
     * @return bool
     */
    public function exceed($type);
}
