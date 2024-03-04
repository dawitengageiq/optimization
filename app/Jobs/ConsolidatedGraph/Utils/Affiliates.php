<?php

namespace App\Jobs\ConsolidatedGraph\Utils;

class Affiliates extends \App\Http\Services\Consolidated\Utils\Affiliates
{
    /**
     * Exit page offer id
     *
     * @var int
     */
    protected $offerID;

    /**
     * Benchmarks, campaign id for each campaign type
     *
     * @var array
     */
    protected $benchmarks = [];

    /**
     * List of Campaign ids from benchmarks
     *
     * @var array
     */
    protected $campaignIDs = [];

    /**
     * Set benchmarks, benchmarks has campaign id in each campaign type.
     * Use to gather leads for mix coreg 1 and 2.
     */
    public function setBenchmarks(array $benchmarks)
    {
        if (array_key_exists(1, $benchmarks)) {
            $this->campaignIDs[] = $benchmarks[1];
        }
        if (array_key_exists(2, $benchmarks)) {
            $this->campaignIDs[] = $benchmarks[2];
        }
    }

    /**
     * Seth e offer id of exit page.
     *
     * @param  \App\Campaign|\Illuminate\Database\Eloquent\Collection|Empty  $exitPage
     */
    public function setOfferID($exitPage)
    {
        if (! $exitPage) {
            return;
        }

        if ($exitPage instanceof \App\Campaign) {
            $this->offerID = $exitPage->linkout_offer_id;
        }
        if ($exitPage instanceof \Illuminate\Database\Eloquent\Collection) {
            if (count($exitPage) > 0) {
                $this->offerID = $exitPage[0]->linkout_offer_id;
            }
        }
    }

    /**
     * Fetch the affilate records from database.
     * Eager load the related records.
     */
    public function pluck()
    {
        $this->affiliates = $this->model->has('revenueTracker')
            ->with([
                'revenueTracker' => function ($q) {
                    return $q->select('affiliate_id', 'revenue_tracker_id', 'campaign_id')
                    // ->where('campaign_id', '>', 0)
                        ->with(['affiliateReport' => function ($q) {
                            return $q->select('revenue_tracker_id', 'campaign_id', 'revenue', 'created_at')
                                ->where('created_at', $this->date);
                        },
                        ])
                        ->with(['revenueTrackerCakeStatistic' => function ($q) {
                            return $q->select('revenue_tracker_id', 'payout', 'created_at')
                                ->where('created_at', $this->date);
                        },
                        ])
                        ->with(['cakeRevenue' => function ($q) {
                            return $q->select('affiliate_id', 'revenue_tracker_id', 'offer_id', 'revenue')
                                ->where('offer_id', $this->offerID)
                                ->whereDate('created_at', '=', $this->date);
                        },
                        ])
                        ->with(['clicksVsRegistrationStatistics' => function ($q) {
                            return $q->select('revenue_tracker_id', 'registration_count', 'clicks', 'percentage', 'created_at')
                                ->where('created_at', $this->date);
                        },
                        ])
                        ->with(['pageViewStatistics' => function ($q) {
                            return $q->where('created_at', $this->date);
                        },
                        ])
                        ->with(['leads' => function ($q) {
                            return $q->select('affiliate_id', 'campaign_id', 'received')
                                ->whereIn('campaign_id', $this->campaignIDs)
                                ->where('lead_status', 1)
                                ->whereDate('created_at', '=', $this->date);
                        },
                        ]);
                },
            ])
            // ->where('id', 3432)
            ->get(['id']);

        // printR($this->affiliates, true);
    }

    /**
     * Check records has affilates/ not empty.
     */
    public function hasRecord()
    {
        if (! $this->affiliates->isEmpty()) {
            return true;
        }

        return false;
    }
}
