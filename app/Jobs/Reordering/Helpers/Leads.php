<?php

namespace App\Jobs\Reordering\Helpers;

use App\Lead;
use DB;

class Leads
{
    /**
     * [protected description]
     *
     * @var iIlluminate\Database\Eloquent\Builder
     */
    protected $query;

    /**
     * Instantiate
     * Initial query
     */
    public function __construct()
    {
        $this->query = Lead::select(DB::raw('COUNT(*) AS leads'), DB::raw('SUM(received) AS revenue'), 'leads.campaign_id', 'leads.affiliate_id')
            ->where('leads.lead_status', '=', 1);
    }

    /**
     * Set current date that will be used to fetch leads between reference date and current date.
     *
     * @param  string|timestamp  $now
     */
    public function setCurrentDate($now)
    {
        $this->now = $now;
    }

    /**
     * Set campaign order that will be used to fetch leads that are in the list of this order.
     */
    public function setCampaignOrder(array $campaignOrder)
    {
        $this->query->whereIn('leads.campaign_id', $campaignOrder);
    }

    /**
     * Set revenue tracker id to be used on specific query
     */
    public function setRevenueTRrackerID(int $revenueRrackerID)
    {
        $this->revenueRrackerID = $revenueRrackerID;
        $this->query->where('leads.affiliate_id', '=', $this->revenueRrackerID);
    }

    /**
     * set reference dAte to be used on fetching leads between reference date and current date.
     *
     * @param  string|timestamp  $referenceDate
     */
    public function setReferenceDAte($referenceDate)
    {
        $this->query->whereBetween('leads.created_at', [
            $referenceDate,
            $this->now,
        ]);
    }

    /**
     * Check if there is leads
     */
    public function notExists(): bool
    {
        if (count($this->leads) <= 0) {
            return true;
        }

        return false;
    }

    /**
     * Get all leads
     */
    public function get(): array
    {
        return $this->leads;
    }

    /**
     * Query the leads
     *
     * @var eloquentCollection
     */
    public function query()
    {
        $this->leads = $this->query
            ->join('campaigns', 'leads.campaign_id', '=', 'campaigns.id')
            ->where(function ($query) {
                $query->where('campaigns.status', '=', 1);
                $query->orWhere('campaigns.status', '=', 2);
            })
            ->with(['campaignViewReport' => function ($query) {
                return $query->select('revenue_tracker_id', 'campaign_id', 'current_view_count')
                    ->where('revenue_tracker_id', '=', $this->revenueRrackerID);
            }])
            ->groupBy('leads.campaign_id', 'leads.affiliate_id')
            ->get()
            ->keyBy('campaign_id');
    }
}
