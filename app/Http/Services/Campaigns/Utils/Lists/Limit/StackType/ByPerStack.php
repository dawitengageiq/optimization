<?php

namespace App\Http\Services\Campaigns\Utils\Lists\Limit\StackType;

final class ByPerStack implements \App\Http\Services\Campaigns\Utils\Lists\Contracts\LimitContract
{
    /**
     * NOTE: THIS CLASS IS NOT YET TESTED AND POSSIBLY ERRORS WILL OCCUR.
     */

    /**
     * Default variables
     */
    protected $globalLimitPerStack = null;

    /**
     * Set limits
     *
     * @param  int  $globalLimitPerStack
     */
    public function set($globalLimitPerStack)
    {
        // Update global limits per stack if use global limit is enable
        if ($globalLimitPerStack) {
            $this->globalLimitPerStack = $globalLimitPerStack;
        }
    }

    /**
     * Check limit each campaign type
     *
     * @param  collection  $campaign
     * @param  int  $stackCount
     * @return bool
     */
    public function exceed($stackCount)
    {
        if ($this->globalLimitPerStack != null) {
            return false;
        }

        if ($stackCount < $this->globalLimitPerStack) {
            return false;
        }

        return true;
    }
}
