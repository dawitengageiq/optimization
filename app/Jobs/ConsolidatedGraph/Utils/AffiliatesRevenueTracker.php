<?php

namespace App\Jobs\ConsolidatedGraph\Utils;

use App\Affiliate;

class AffiliatesRevenueTracker extends \App\Http\Services\Consolidated\Utils\Affiliates
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
        collect([1, 2, 8, 13])->map(function ($campaignType) use ($benchmarks) {
            if (array_key_exists($campaignType, $benchmarks)) {
                $this->campaignIDs[] = $benchmarks[$campaignType];
            }
        });
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
        $this->affiliates = $this->model
            ->has('affiliateRevenueTracker')
            ->with([
                'affiliateRevenueTracker' => function ($q) {
                    return $q->select('affiliate_id', 'revenue_tracker_id', 'campaign_id', 'exit_page_id')
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
                        ]);
                    // ->with(['leads' => function ($q) {
                    // 		return $q->select('affiliate_id', 'campaign_id', 'received')
                    // 				->whereIn('campaign_id', $this->campaignIDs)
                    //                 ->where('lead_status', 1)
                    //                 ->whereDate('created_at', '=',  $this->date);
                    // 	}
                    // ])
                },
            ])
            ->where('status', 1)
            ->where('type', 1)
            // ->where('id', 7612)
            ->get(['id']);

        // printR($this->affiliates, true);
        // exit;
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
