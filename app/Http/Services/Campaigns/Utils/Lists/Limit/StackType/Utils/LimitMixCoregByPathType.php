<?php

namespace App\Http\Services\Campaigns\Utils\Lists\Limit\StackType\Utils;

class LimitMixCoregByPathType
{
    protected $hasPathLimit = false;

    protected $pathLimit = [];

    /**
     * Apply the revenue tracker limit for coreg
     *
     * @param  array  $stacks
     */
    public function apply($param): array
    {
        [$stacks, $pathLimit, $revenueTrackerLimit] = $param;

        if (count($pathLimit)) {
            $this->pathLimit = $pathLimit;
            $this->hasPathLimit = true;
        }

        // Process the limit by path type and revenue tracker
        return $this->applyPathTypeLimitThenFirstLevelLimit($stacks, $revenueTrackerLimit['coreg']);
    }

    /**
     * Apply the revenue tracker limit for coreg
     */
    protected function applyPathTypeLimitThenFirstLevelLimit(array $stacks, $revLimit): array
    {
        //if(!$this->hasPathLimit && !$revLimit) return $stacks;

        $tempArray = [];
        $coregTypes = array_keys(config('constants.MIXED_COREG_TYPE_FOR_ORDERING'));

        // Merge all ids of coregs
        foreach ($coregTypes as $coregType) {
            if (array_key_exists($coregType, $stacks)) {
                // Apply limit per campaign type before applying the revenue tracker limit
                $stacks[$coregType][0] = $this->applyPathTypeLimit($stacks[$coregType][0], $coregType);
                // merge to temp array to able to count the overall campaign, if
                // exceed the revenue tracker limit it will be splice below.
                $tempArray = array_merge($tempArray, $stacks[$coregType][0]);
            }
        }

        // then,
        // Implement the revenue tracker limit here
        if ($revLimit) {
            $collection = collect($tempArray);
            $collection->splice($revLimit);
            $tempArray = $collection->toArray();
        }

        // And
        // Remove excess campaign ids before returning...
        return $this->removeExcessIDs($stacks, $tempArray, $coregTypes);
    }

    /**
     * Apply campaign path type limit
     *
     * @param  array  $coregTypes
     */
    protected function applyPathTypeLimit(array $stacks, $coregType): array
    {
        if ($this->hasPathLimit) {
            if ($limit = $this->pathLimit[$coregType]) {
                $collection = collect($stacks);
                $collection->splice($limit);
                // Now the limit was applied to per campaign type
                return $collection->toArray();
            }
        }

        return $stacks;
    }

    /**
     * Remove excess campaign ids due to limit
     */
    protected function removeExcessIDs(array $stacks, array $tempArray, array $coregTypes): array
    {
        foreach ($stacks as $idx => $stack) {
            if (in_array($idx, $coregTypes)) {
                foreach ($stack[0] as $indx => $ids) {
                    if (! in_array($ids, $tempArray)) {
                        unset($stacks[$idx][0][$indx]);
                    }
                }
            }
        }

        return $stacks;
    }
}
