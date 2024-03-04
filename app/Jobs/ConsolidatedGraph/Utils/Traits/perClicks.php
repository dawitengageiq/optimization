<?php

namespace App\Jobs\ConsolidatedGraph\Utils\Traits;

trait perClicks
{
    /**
     * Legends value needs to convert to percentage.
     *
     * @var array
     */
    protected $clicksValue2Percent = [];

    /**
     * Division
     */
    protected function perClicks(string $idx, float $dividend, float $divisor)
    {
        $total = ($divisor > 0) ? ($dividend / $divisor) : 0;
        if (array_key_exists($idx, $this->clicksValue2Percent)) {
            $this->consolidatedData->$idx = number_format(($total * 100), 2, '.', '');
        } else {
            $this->consolidatedData->$idx = number_format($total, 2, '.', '');
        }
    }

    /**
     * Value for revenue per all clicks.
     */
    protected function sourceRevenuePerAllClicks(string $idx): void
    {
        if (! array_key_exists('source_revenue', $this->params)
        || ! array_key_exists('all_clicks', $this->params)) {
            $this->consolidatedData->$idx = number_format(0, 2, '.', '');

            return;
        }

        $this->perClicks(
            $idx,
            $this->params['source_revenue'],
            $this->params['all_clicks']
        );

    }

    /**
     * Value for survey takers per clicks.
     */
    protected function surveyTakersPerClicks(string $idx): void
    {
        if (! array_key_exists('survey_takers', $this->params)
        || ! array_key_exists('all_clicks', $this->params)) {
            $this->consolidatedData->$idx = number_format(0, 2, '.', '');

            return;
        }

        $this->perClicks(
            $idx,
            $this->params['survey_takers'],
            $this->params['all_clicks']
        );

    }

    /**
     * Value for cost per clicks.
     */
    protected function costPerAllClicks(string $idx): void
    {
        if (! array_key_exists('cost', $this->params)
        || ! array_key_exists('all_clicks', $this->params)) {
            $this->consolidatedData->$idx = number_format(0, 2, '.', '');

            return;
        }

        $this->perClicks(
            $idx,
            $this->params['cost'],
            $this->params['all_clicks']
        );

    }
}
