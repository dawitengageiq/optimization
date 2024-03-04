<?php

namespace App\Http\Services\Campaigns\Utils\Lists;

class ExitPage
{
    /**
     * RAndom exit page
     *
     * @param  array  $campaign_stack
     */
    public function randomID(
        $stack,
        $index): array
    {
        if (count($stack[$index][0]) == 0) {
            return [0 => []];
        }

        return [0 => [$stack[$index][0][array_rand($stack[$index][0], 1)]]];
    }
}
