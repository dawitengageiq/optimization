<?php

namespace App\Http\Services\Campaigns\Utils\Lists\Limit\StackType;

final class ByMixCoreg extends ByPathType implements \App\Http\Services\Campaigns\Utils\Lists\Contracts\LimitContract
{
    public $mixedCoregLimit;

    protected $revenueTrackerLimit;

    /**
     * Default variables
     */
    protected $hasPathLimit = false;

    /**
     * [$counter description]
     *
     * @var array
     */
    protected $counter = [
        1 => 0, 2 => 0, 8 => 0, 3 => 0, 7 => 0, 4 => 0, 5 => 0,
        6 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0, 13 => 0,
    ];

    /**
     * [$pathLimit description]
     *
     * @var array
     */
    protected $pathLimit = [
        1 => '', 2 => '', 8 => '', 3 => '', 7 => '', 4 => '', 5 => '',
        6 => '', 9 => '', 10 => '', 11 => '', 12 => '', 13 => '',
    ];

    /**
     * [$limitPerPage description]
     *
     * @var string
     */
    protected $limitPerPage = '';

    public function __construct(Utils\LimitMixCoregByMixCoregStacking $mixedCoregLimit)
    {
        $this->mixedCoregLimit = $mixedCoregLimit;
    }

    /**
     * Set limits
     *
     * @param  string  $campaignTypeLimit
     *
     * @var array
     */
    public function set($campaignTypeLimit)
    {
        // Update ampaign type path Limits if use campaign type limit is enable
        if (count($campaignTypeLimit)) {
            $this->pathLimit = $campaignTypeLimit + $this->pathLimit;
            $this->limitPerPage = $this->pathLimit[1];
            $this->hasPathLimit = true;
        }
    }

    /**
     * Set limits
     *
     * @param  string  $campaignTypeLimit
     *
     * @var array
     */
    public function limitPerPage()
    {
        return $this->limitPerPage;
    }

    /**
     * Check limit each campaign type
     * NOTE: The limit of campaign type non mix-coregs is not check here, refer to applyFirstLevelLimitThenLimitPerPage.
     *
     * @param  collection  $campaign
     * @return bool
     */
    public function exceed($campaignType)
    {
        if (! $this->pathLimit[$campaignType]) {
            return false;
        }

        if ($this->counter[$campaignType] < $this->pathLimit[$campaignType]) {
            $this->counter[$campaignType]++;

            return false;
        }

        return true;
    }
}
