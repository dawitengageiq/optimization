<?php

namespace App\Jobs\ConsolidatedGraph\Utils\Traits;

trait Views
{
    /**
     * Value for exit page views.
     */
    protected function lspViews(string $idx)
    {
        $this->consolidatedData->$idx = $this->params['exit_page_views'];
    }

    /**
     * Value for mp per views.
     */
    protected function mpPerViews(string $idx)
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
        if (! array_key_exists('all_inbox_revenue', $this->params)) {
            $this->params['all_inbox_revenue'] = 0;
        }

        $external = $this->params['pd_revenue'];
        $external += $this->params['tb_revenue'];
        $external += $this->params['iff_revenue'];
        $external += $this->params['rexadz_revenue'];
        $external += $this->params['all_inbox_revenue'];

        $external += $this->perViews(
            '',
            $this->params['pd_revenue'],
            ((array_key_exists('pd_views', $this->params)) ? $this->params['pd_views'] : 0)
        );
        $external += $this->perViews(
            '',
            $this->params['tb_revenue'],
            (
                ((array_key_exists('tb1_views', $this->params)) ? $this->params['tb1_views'] : 0) +
                ((array_key_exists('tb2_views', $this->params)) ? $this->params['tb2_views'] : 0)
            )
        );
        $external += $this->perViews(
            '',
            $this->params['iff_revenue'],
            ((array_key_exists('iff_views', $this->params)) ? $this->params['iff_views'] : 0)
        );
        $external += $this->perViews(
            '',
            $this->params['rexadz_revenue'],
            ((array_key_exists('rexadz_views', $this->params)) ? $this->params['rexadz_views'] : 0)
        );

        $this->perClicks(
            $idx,
            $external,
            $this->params['all_clicks']
        );
    }
}
