<?php

namespace App\Http\Services\Consolidated;

use App\ConsolidatedGraph;
use Carbon\Carbon;

/**
 * Consolidate graph class.
 * Extended by GraphByRevenueTrackerID::class
 */
class GraphAllInbox extends GraphByDateRange implements \App\Http\Services\Contracts\ConsolidatedGraphContract
{
    /**
     * Container for supplied date.
     *
     * @var string
     */
    protected $date;

    /**
     * All inbox revenue.
     *
     * @var  int
     */
    protected $allInboxRevenue = 0;

    /**
     * Instantiate.
     * Provide the eloquent model.
     */
    public function __construct(ConsolidatedGraph $model, Carbon $carbon)
    {
        $this->model = $model;
        $this->carbon = $carbon;
    }

    /**
     * Set the date.
     */
    public function setdate(string $date)
    {
        // Use for Query
        $this->date = $date;
        // Use for chart categories
        $this->dateRange = [$date];
    }

    /**
     * Set the .
     *
     * @param  string  $date
     */
    public function setAllInboxRevenue($allInboxRevenue)
    {
        $this->allInboxRevenue = $allInboxRevenue;
    }

    /**
     * [getConsolidatedData description]
     *
     * @return [type] [description]
     */
    public function getConsolidatedData()
    {
        // First fetch the record exists.
        $records = $this->model->where('revenue_tracker_id', $this->revenueTrackerID)
            ->whereDate('created_at', '=', $this->date)
            ->first();

        // If not exists.
        if (! $records && $this->allInboxRevenue) {
            // $records = new ConsolidatedGraph;
            // $records->revenue_tracker_id = $this->revenueTrackerID;
        }

        if ($records) {
            if ($this->allInboxRevenue) {
                $records->all_inbox_revenue = number_format($this->allInboxRevenue, 2, '.', '');
                $records->save();
                $this->message = 'Updated! All inbox revenue was updated successfully.';
            }

            $this->records = [$this->date => $records->toArray()];
        }
    }
}
