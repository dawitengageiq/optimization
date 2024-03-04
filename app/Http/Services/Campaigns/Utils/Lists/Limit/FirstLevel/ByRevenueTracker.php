<?php

namespace App\Http\Services\Campaigns\Utils\Lists\Limit\FirstLevel;

final class ByRevenueTracker implements \App\Http\Services\Campaigns\Utils\Lists\Contracts\LimitContract
{
    protected $externalCounter = 0;

    protected $linkoutCounter = 0;

    protected $coregCounter = 0;

    protected $exitCounter = 0;

    protected $externalLimit = null;

    protected $linkOutLimit = null;

    protected $exitLimit = null;

    protected $coregLimit = null;

    /**
     * Set limits
     *
     * @param  array  $limit
     *
     * @var
     */
    public function set($limit)
    {
        if (array_key_exists('external', $limit)) {
            $this->externalLimit = $limit['external'];
        }
        if (array_key_exists('link_out', $limit)) {
            $this->linkOutLimit = $limit['link_out'];
        }
        if (array_key_exists('exit', $limit)) {
            $this->exitLimit = $limit['exit'];
        }
        if (array_key_exists('coreg', $limit)) {
            $this->coregLimit = $limit['coreg'];
        }
    }

    /**
     * Check limit each campaign type
     * NOTE: the limit of campaign type coregs is not check here, refer to either ByMixCoreg::Class or ByPathType::Class
     * NOTE: Only external, cpawall, long form and and others except the mixcoreg were process here.
     *
     * @param  collection  $campaign
     * @param  array  $limit
     * @return bolean
     */
    public function exceed($campaignType)
    {
        //External Path
        if ($campaignType == 4) {
            return $this->externalChecker();
        }
        //Link Out
        if ($campaignType == 5) {
            return $this->linkoutChecker();
        }
        //Exit Page
        if ($campaignType == 6) {
            return $this->exitChecker();
        }
        //Coreg Page
        return $this->coregChecker();
    }

    /**
     * Check limit check each campaign type external
     *
     * @param  array  $limit
     * @param  bolean  $limitChecker
     * @return bolean
     */
    protected function externalChecker()
    {
        if ($this->externalLimit == null) {
            return false;
        }

        if ($this->externalCounter < $this->externalLimit) {
            $this->externalCounter++;

            return false;
        }

        return true;
    }

    /**
     * Check limit check each campaign type linkout
     *
     * @param  array  $limit
     * @param  bolean  $limitChecker
     * @return bolean
     */
    protected function linkoutChecker()
    {
        if ($this->linkOutLimit == null) {
            return false;
        }

        if ($this->linkoutCounter < $this->linkOutLimit) {
            $this->linkoutCounter++;

            return false;
        }

        return true;
    }

    /**
     * Check limit check each campaign type exit
     *
     * @param  array  $limit
     * @param  bolean  $limitChecker
     * @return bolean
     */
    protected function exitChecker()
    {
        if ($this->exitLimit == null) {
            return false;
        }

        if ($this->exitCounter < $this->exitLimit) {
            $this->exitCounter++;

            return false;
        }

        return true;
    }

    /**
     * Check limit check each campaign type coreg
     *
     * @param  array  $limit
     * @param  bolean  $limitChecker
     * @return bolean
     */
    protected function coregChecker()
    {
        if ($this->coregLimit == null) {
            return false;
        }

        if ($this->coregCounter < $this->coregLimit) {
            $this->coregCounter++;

            return false;
        }

        return true;
    }
}
