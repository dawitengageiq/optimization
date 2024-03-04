<?php

namespace App\Jobs\ConsolidatedGraph\Utils\Traits;

trait perViews
{
    /**
     * Legends value needs to convert to percentage.
     *
     * @var array
     */
    protected $viewsValue2Percent = [];

    /**
     * Division
     */
    protected function perViews(string $idx, float $dividend, float $divisor)
    {
        $total = ($divisor > 0) ? ($dividend / $divisor) : 0;

        if (! $idx) {
            return number_format($total, 2, '.', '');
        }

        if (array_key_exists($idx, $this->viewsValue2Percent)) {
            $this->consolidatedData->$idx = number_format(($total * 100), 2, '.', '');
        } else {
            $this->consolidatedData->$idx = number_format($total, 2, '.', '');
        }
    }

    /**
     * Value for cpa revenue per views.
     */
    protected function cpaRevenuePerViews(string $idx): void
    {
        if (! array_key_exists('cpa_revenue', $this->params)
        || ! array_key_exists('cpa_views', $this->params)) {
            $this->consolidatedData->$idx = number_format(0, 2, '.', '');

            return;
        }

        $this->perViews(
            $idx,
            $this->params['cpa_revenue'],
            $this->params['cpa_views']
        );

    }

    /**
     * Value for exit page revenue per views.
     */
    protected function lspRevenueVsViews(string $idx): void
    {
        if (! array_key_exists('lsp_revenue', $this->params)
        || ! array_key_exists('exit_page_views', $this->params)) {
            $this->consolidatedData->$idx = number_format(0, 2, '.', '');

            return;
        }

        $this->perViews(
            $idx,
            $this->params['lsp_revenue'],
            $this->params['exit_page_views']
        );

    }

    /**
     * Value for permission data revenue per views.
     */
    protected function pdRevenueVsViews(string $idx): void
    {
        if (! array_key_exists('pd_revenue', $this->params)
        || ! array_key_exists('pd_views', $this->params)) {
            $this->consolidatedData->$idx = number_format(0, 2, '.', '');

            return;
        }

        $this->perViews(
            $idx,
            $this->params['pd_revenue'],
            $this->params['pd_views']
        );

    }

    /**
     * Value for tiburon revenue per views.
     */
    protected function tbRevenueVsViews(string $idx): void
    {
        if (! array_key_exists('tb1_views', $this->params)) {
            $this->params['tb1_views'] = 0;
        }
        if (! array_key_exists('tb2_views', $this->params)) {
            $this->params['tb2_views'] = 0;
        }

        $tbViews = $this->params['tb1_views'] + $this->params['tb2_views'];

        if (! array_key_exists('tb_revenue', $this->params)
        || ! $tbViews) {
            $this->consolidatedData->$idx = number_format(0, 2, '.', '');

            return;
        }

        $this->perViews(
            $idx,
            $this->params['tb_revenue'],
            $tbViews
        );

    }

    /**
     * Value for iffecient revenue per views.
     */
    protected function iffRevenueVsViews(string $idx): void
    {
        if (! array_key_exists('iff_revenue', $this->params)
        || ! array_key_exists('iff_views', $this->params)) {
            $this->consolidatedData->$idx = number_format(0, 2, '.', '');

            return;
        }

        $this->perViews(
            $idx,
            $this->params['iff_revenue'],
            $this->params['iff_views']
        );

    }

    /**
     * Value for rexadz revenue per views.
     */
    protected function rexadzRevenueVsViews(string $idx): void
    {
        if (! array_key_exists('rexadz_revenue', $this->params)
        || ! array_key_exists('rexadz_views', $this->params)) {
            $this->consolidatedData->$idx = number_format(0, 2, '.', '');

            return;
        }

        $this->perViews(
            $idx,
            $this->params['rexadz_revenue'],
            $this->params['rexadz_views']
        );

    }

    /**
     * All coreg revenue per all coreg views
     */
    protected function allCoregRevenuePerAllCoregViews(string $idx)
    {
        if (! array_key_exists('coreg_p1_views', $this->params)) {
            $this->params['coreg_p1_views'] = 0;
        }
        if (! array_key_exists('coreg_p2_views', $this->params)) {
            $this->params['coreg_p2_views'] = 0;
        }
        if (! array_key_exists('coreg_p3_views', $this->params)) {
            $this->params['coreg_p3_views'] = 0;
        }
        if (! array_key_exists('coreg_p4_views', $this->params)) {
            $this->params['coreg_p4_views'] = 0;
        }

        $this->perViews(
            $idx,
            $this->consolidatedData->all_coreg_revenue,
            (
                $this->params['coreg_p1_views'] +
                $this->params['coreg_p2_views'] +
                $this->params['coreg_p3_views'] +
                $this->params['coreg_p4_views']
            )
        );
    }

    /**
     * Value for coreg page 1 revenue per views.
     */
    protected function coregP1RevenueVsViews(string $idx): void
    {
        if (! array_key_exists('coreg_p1_revenue', $this->params)
        || ! array_key_exists('coreg_p1_views', $this->params)) {
            $this->consolidatedData->$idx = number_format(0, 2, '.', '');

            return;
        }

        $this->perViews(
            $idx,
            $this->params['coreg_p1_revenue'],
            $this->params['coreg_p1_views']
        );

    }

    /**
     * Value for coreg page 2 revenue per views.
     */
    protected function coregP2RevenueVsViews(string $idx): void
    {
        if (! array_key_exists('coreg_p2_revenue', $this->params)
        || ! array_key_exists('coreg_p2_views', $this->params)) {
            $this->consolidatedData->$idx = number_format(0, 2, '.', '');

            return;
        }

        $this->perViews(
            $idx,
            $this->params['coreg_p2_revenue'],
            $this->params['coreg_p2_views']
        );

    }

    /**
     * Value for coreg page 3 revenue per views.
     */
    protected function coregP3RevenueVsViews(string $idx): void
    {
        if (! array_key_exists('coreg_p3_revenue', $this->params)
        || ! array_key_exists('coreg_p3_views', $this->params)) {
            $this->consolidatedData->$idx = number_format(0, 2, '.', '');

            return;
        }

        $this->perViews(
            $idx,
            $this->params['coreg_p3_revenue'],
            $this->params['coreg_p3_views']
        );

    }

    /**
     * Value for coreg page 3 revenue per views.
     */
    protected function coregP4RevenueVsViews(string $idx): void
    {
        if (! array_key_exists('coreg_p4_revenue', $this->params)
        || ! array_key_exists('coreg_p4_views', $this->params)) {
            $this->consolidatedData->$idx = number_format(0, 2, '.', '');

            return;
        }

        $this->perViews(
            $idx,
            $this->params['coreg_p4_revenue'],
            $this->params['coreg_p4_views']
        );

    }
}
