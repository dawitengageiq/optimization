<?php

namespace App\Http\Services\Consolidated;

use App\ConsolidatedGraph;

class GraphByRevenueTrackerID extends GraphByDateRange implements \App\Http\Services\Contracts\ConsolidatedGraphContract
{
    /**
     * Date container
     *
     * @var string
     */
    protected $date;

    /**
     * Categories container, hart x-axis label.
     *
     * @var array
     */
    protected $categories = [];

    /**
     * an array of revenue tracker ids container,
     * Used in query in ConsolidatedGraph
     *
     * @var [type]
     */
    protected $revenueTrackerIDs = [];

    /**
     * Series per slide container, how many date/x-axis in one slide. We are using bootstrap carousel here.
     *
     * @var int
     */
    protected $seriesPerslide = 4;

    /**
     * Instantiate.
     * Provide the eloquent model.
     */
    public function __construct(ConsolidatedGraph $model)
    {
        $this->model = $model;
    }

    /**
     * Set the revenue tracker ids from affilates.
     */
    public function setRevenueTrackerIDs(array $affiliates)
    {
        foreach ($affiliates as $affiliate) {
            foreach ($affiliate['revenue_tracker'] as $revenueTracker) {
                array_push($this->revenueTrackerIDs, $revenueTracker['revenue_tracker_id']);
            }
        }
    }

    /**
     * Get thelist/array of rerevenue tracker ids.
     */
    public function revenueTrackerIDs(): array
    {
        return $this->revenueTrackerIDs;
    }

    /**
     * Set the date.
     */
    public function setdate(string $date)
    {
        // Use for Query
        $this->date = $date;
    }

    /**
     * Get the date.
     */
    public function date(): string
    {
        return $this->date;
    }

    /**
     * Get the consilidated data to be use in chart.
     *
     * @model ConsolidatedGraph.
     */
    public function getConsolidatedData()
    {
        $this->records = $this->model
            ->whereIn('revenue_tracker_id', $this->revenueTrackerIDs)
            ->whereDate('created_at', '=', $this->date)
            ->get()
            ->keyBy('revenue_tracker_id')
            ->toArray();
    }

    /**
     *  Set the chart series.
     *  1. Loop through records/x-axis of Chart.
     *  2. loop through legends for the legends were the column of chart.
     *  3. Check if legends will be include baseon the selected column.
     *  4. Check if date count grouping already an index in series container,
     *  if not push data to series as new date count grouping with new column/data.
     *  5. Check if legend alias was set as index inside array of date count grouping/coulmn.
     *  if not push data to series as new date count grouping with new column/data.
     *  6. Check the counter of data count per legend didn't exceed the series per slide value.
     *  if exceed then increment the date counter and reset data counter to 1 and push data to series as new date count grouping with new column/data.
     *  7. If passed #3, #4, and #5 push data to the existing date count grouping and existing column/data.
     *  8. Fetch colors for colmn.
     *  9. Group the revenue_tracker_ids/categories per slide.
     */
    public function setSeriesThenCategories()
    {
        // #1 ....
        foreach ($this->records as $record) {
            // #2 ....
            foreach ($this->legends as $legend => $details) {
                //3 ....
                if ($this->excludeThisLegend($legend)) {
                    continue;
                }
                // Increment the legend counter
                $this->counters[$legend]['data_count']++;
                // Parameters for functions.
                $param = [$legend, $details['alias'], $record['revenue_tracker_id']];
                // #4 ....
                if ($this->dateCountGroupingExistsInSeries(...$param)) {
                    // #5 ....
                    if ($col = $this->aliasExistsInSeriesColumn(...$param)) { // search value in the array
                        // #6 ....
                        if ($this->counterDatacountIsLessThanSeriesPerslide(...$param)) {
                            // #7 ....
                            $this->pushDAtaToSeries(...(array_merge($param, [$details['percentage'], $col])));
                            // Skip code below, next legend.
                            continue;
                        }
                        // If exceed as #6 ...
                        $this->incrementDateCountAndResetDataCount(...$param);
                    }
                }
                // If failed as #4 and #5 ...
                $this->pushDAtaToSeriesAsNewDateCountGrouping(...(array_merge($param, [$details['percentage']])));

                // #8 ....
                if (! in_array($details['color'], $this->colors)) {
                    $this->colors[] = $details['color'];
                }
            }
            // #9 ....
            $this->categories['slide_'.$this->counters['mp_per_views']['date_count']][] = 'CD'.$record['revenue_tracker_id'];
        }
    }
}
