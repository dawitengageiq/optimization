<?php

namespace App\Jobs\ConsolidatedGraph\Utils\Traits;

trait Payout
{
    /**
     *  Process payoutfor mis coreg 1 nad mix core 2.
     *  Use benchmark to determine the campaign id to be used in each campaign type.
     *
     * @param  \Illuminate\Database\Eloquent\Collection|Empty  $leads
     */
    protected function processMixcoregPayout($leads): void
    {
        if (! $leads instanceof \Illuminate\Database\Eloquent\Collection) {
            return;
        }

        if (count($leads)) {
            $groupLeads = $leads->groupBy('campaign_id');

            if (array_key_exists($this->mixCoreg1, $this->benchmarks)) {
                if (array_key_exists($this->benchmarks[$this->mixCoreg1], $groupLeads->toArray())) {
                    $this->setRevenue('coreg_p1_revenue', $groupLeads[$this->benchmarks[$this->mixCoreg1]]->flatten()->sum('received'), false);
                }
            }

            if (array_key_exists($this->mixCoreg2, $this->benchmarks)) {
                if (array_key_exists($this->benchmarks[$this->mixCoreg2], $groupLeads->toArray())) {
                    $this->setRevenue('coreg_p2_revenue', $groupLeads[$this->benchmarks[$this->mixCoreg2]]->flatten()->sum('received'), false);
                }
            }

            if (array_key_exists($this->mixCoreg3, $this->benchmarks)) {
                if (array_key_exists($this->benchmarks[$this->mixCoreg3], $groupLeads->toArray())) {
                    $this->setRevenue('coreg_p3_revenue', $groupLeads[$this->benchmarks[$this->mixCoreg3]]->flatten()->sum('received'), false);
                }
            }

            if (array_key_exists($this->mixCoreg4, $this->benchmarks)) {
                if (array_key_exists($this->benchmarks[$this->mixCoreg4], $groupLeads->toArray())) {
                    $this->setRevenue('coreg_p4_revenue', $groupLeads[$this->benchmarks[$this->mixCoreg4]]->flatten()->sum('received'), false);
                }
            }
        }

    }
}
