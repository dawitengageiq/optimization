<?php

namespace App\Jobs\ConsolidatedGraph\Utils\Traits;

trait Revenue
{
    /**
     * Set rvenue to override default value of revenues container : @var $revenues
     */
    protected function setRevenue(string $indx, int $value, string $env, int $subtrahend = 0): void
    {
        if (! $env) {
            $this->revenues[$indx] = $value;

            return;
        }

        if (array_key_exists($value, $this->affiliateReports)) {
            $this->revenues[$indx] = number_format(($this->affiliateReports[$value]['revenue'] - $subtrahend), 3, '.', '');

            return;
        }

        $this->revenues[$indx] = number_format(0, 3, '.', '');

    }

    /**
     * Value for cpa revenue.
     */
    protected function cpaRevenue(string $idx)
    {
        $idx = number_format($this->params['cpa_revenue'], 3, '.', '');
    }

    /**
     * Value for coreg page 3 revenue per views.
     */
    protected function allMpRevenue(string $idx): void
    {
        if (! array_key_exists('pd_revenue', $this->params)) {
            $this->params['pd_revenue'] = 0;
        }
        if (! array_key_exists('tb_revenue', $this->params)) {
            $this->params['tb_revenue'] = 0;
        }
        if (! array_key_exists('iff_revenue', $this->params)) {
            $this->params['iff_revenue'] = 0;
        }
        if (! array_key_exists('rexadz_revenue', $this->params)) {
            $this->params['rexadz_revenue'] = 0;
        }
        // if(!array_key_exists('all_inbox_revenue', $this->params)) $this->params['all_inbox_revenue'] = 0;

        $external = $this->params['pd_revenue'];
        $external += $this->params['tb_revenue'];
        $external += $this->params['iff_revenue'];
        $external += $this->params['rexadz_revenue'];
        // $external += $this->params['all_inbox_revenue'];

        $idx = number_format($external, 3, '.', '');
    }

    /**
     * All coreg revenue
     */
    protected function allCoregRevenue(string $idx)
    {
        if (! array_key_exists('coreg_p1_revenue', $this->params)) {
            $this->params['coreg_p1_revenue'] = 0;
        }
        if (! array_key_exists('coreg_p2_revenue', $this->params)) {
            $this->params['coreg_p2_revenue'] = 0;
        }
        if (! array_key_exists('coreg_p3_revenue', $this->params)) {
            $this->params['coreg_p3_revenue'] = 0;
        }
        if (! array_key_exists('coreg_p4_revenue', $this->params)) {
            $this->params['coreg_p4_revenue'] = 0;
        }

        $coreg = $this->params['coreg_p1_revenue'];
        $coreg += $this->params['coreg_p2_revenue'];
        $coreg += $this->params['coreg_p3_revenue'];
        $coreg += $this->params['coreg_p4_revenue'];

        $idx = number_format($coreg, 3, '.', '');
    }
}
