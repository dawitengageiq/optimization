<?php

namespace App\Http\Services\Campaigns\Utils\Lists\Limit\StackType;

class ByPathType implements \App\Http\Services\Campaigns\Utils\Lists\Contracts\LimitContract
{
    public $mixedCoregLimit;

    /**
     * Default variables
     */
    protected $counter = [
        1 => 0, 2 => 0, 8 => 0, 3 => 0, 7 => 0, 4 => 0, 5 => 0,
        6 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0, 13 => 0, 14 => 0, 15 => 0, 16 => 0,
    ];

    protected $pathLimit = [
        1 => '', 2 => '', 8 => '', 3 => '', 7 => '', 4 => '', 5 => '',
        6 => '', 9 => '', 10 => '', 11 => '', 12 => '', 13 => '', 14 => '', 15 => '', 16 => '',
    ];

    public function __construct(Utils\LimitMixCoregByPathType $mixedCoregLimit)
    {
        $this->mixedCoregLimit = $mixedCoregLimit;
    }

    /**
     * Set limits
     *
     *
     * @var array
     */
    public function set($campaignTypeLimit)
    {
        // Update ampaign type path Limits if use campaign type limit is enable
        if (count($campaignTypeLimit)) {
            $this->pathLimit = $campaignTypeLimit + $this->pathLimit;
        }
    }

    /**
     * Get the path type limit
     */
    public function getPathLimit(): array
    {
        return $this->pathLimit;
    }

    /**
     * Check limit each campaign type
     * NOTE: the limit of campaign type non mix-coregs is not check here, refer to applyPathTypeLimitThenFirstLevelLimit
     *
     * @param  collection  $campaign
     */
    public function exceed($campaignType): bool
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
