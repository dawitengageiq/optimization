<?php

namespace App\Http\Services\Consolidated;

use App\ConsolidatedGraph;
use Carbon\Carbon;

/**
 * Consolidate graph class.
 * Extended by GraphByRevenueTrackerID::class
 */
class GraphByDateRange implements \App\Http\Services\Contracts\ConsolidatedGraphContract
{
    /**
     * Revenue tracker id container, use for filtering Consolidated data query.
     *
     * @var string
     */
    protected $revenueTrackerID = '';

    /**
     * Container for supplied date.
     *
     * @var string
     */
    protected $dateFrom;

    protected $dateTo;

    /**
     * Chart series container, we are using highcharts here.
     *
     * @var array
     */
    protected $series = [];

    /**
     * Date range container, use to goup series by category.
     *
     * @var array
     */
    protected $dateRange = [];

    /**
     * Categories container, hart x-axis label.
     *
     * @var array
     */
    protected $categories = [];

    /**
     * Counters container, Every legend have its own data counter and date counter.
     * Data counter will be compare to series per slide value, if equal it will reset back to 1
     * Date counter is for indexing/grouping series per date. If data counter was reset, date counter will be incremented.
     *
     * @var array
     */
    protected $counters = [];

    /**
     * Records fetch.
     *
     * @var array
     */
    protected $records = [];

    protected $perSubIDRecords = [];

    protected $subIDSummaryRecords = [];

    /**
     * Series per slide container, how many date/x-axis in one slide. We are using bootstrap carousel here.
     *
     * @var int
     */
    protected $seriesPerSlide = 4;

    /**
     * Process message.
     *
     * @var string
     */
    protected $message = '';

    /**
     * Legends value that will be converted to percentage
     *
     * @var array
     */
    protected $valueToPercent = [];

    /**
     * Column colors
     *
     * @var array
     */
    protected $colors = [];

    /**
     * Chart legends container.
     *
     * @var array
     */
    protected $legends = [];

    /**
     * Selected columns container
     *
     * @var array
     */
    protected $columns = [
        'survey_takers',
        'source_revenue',
        'source_revenue_per_survey_takers',
        'all_clicks',
        'source_revenue_per_all_clicks',
        'margin',
        'cpa_revenue',
        'cpa_revenue_per_views',
        'lsp_revenue',
        'lsp_views',
        'lsp_revenue_vs_views',
        'pd_revenue',
        'pd_revenue_vs_views',
        'tb_revenue',
        'tb_revenue_vs_views',
        'iff_revenue',
        'iff_revenue_vs_views',
        'rexadz_revenue',
        'rexadz_revenue_vs_views',
        // 'adsmith_revenue',
        // 'adsmith_revenue_vs_views',
        'all_inbox_revenue',
        'coreg_p1_revenue',
        'coreg_p1_revenue_vs_views',
        'coreg_p2_revenue',
        'coreg_p2_revenue_vs_views',
        'survey_takers_per_clicks',
        'cpa_per_survey_takers',
        'mp_per_views',
    ];

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
     * Set the revenue tracker, provided by Controller.
     */
    public function setRevenueTrackerID(int $revenueTrackerID)
    {
        $this->revenueTrackerID = $revenueTrackerID;
    }

    /**
     * Set legends, provided by controller from config.consolidatedgraph.legends.
     * We have to set immediatly the counters for generating chart series.
     */
    public function setLegends(array $legends)
    {
        $this->legends = $legends;
        // $this->legends = array_filter($legends, function ($key)
        // {
        //     // returns whether the input integer is odd
        //     return(in_array($key, $this->legends));
        // }, ARRAY_FILTER_USE_KEY);

        $this->setcounters();
    }

    /**
     * Get legends.
     */
    public function legends(): array
    {
        return $this->legends;
    }

    /**
     * Set the selected column to display
     *
     * @param  array  $column
     */
    public function setSelectedColumn($columns)
    {
        if (! $columns || @$columns[0] == 'all') {
            return;
        }

        $this->columns = array_filter($this->columns, function ($val) use ($columns) {
            // returns whether the input integer is odd
            return in_array($val, $columns);
        });

    }

    /**
     * Get columns.
     */
    public function columns(): array
    {
        return $this->columns;
    }

    /**
     * Set counters for generating chart series, index is from config.consolidatedgraph.legends.
     */
    public function setcounters()
    {
        // Initiate counters
        $this->counters = collect(array_keys($this->legends))->flatMap(function ($legend) {
            return [$legend => [
                'date_count' => 0,
                'data_count' => 0,
            ]];
        })->toArray();

    }

    /**
     * Set the date range for chart x-axis label.
     *
     * @param  string  $from
     * @param  string  $to
     */
    public function setDateRange($dateFrom, $dateTo)
    {
        // Use for chart categories
        $this->dateRange = $this->generateDateRange($this->carbon->parse($dateFrom), $this->carbon->parse($dateTo));

        // Use for Query
        $this->dateFrom = $this->carbon->parse($dateFrom);
        $this->dateTo = $this->carbon->parse($dateTo);
    }

    /**
     * Set series p ers lide.
     */
    public function setSeriesPerSlide(int $seriesPerSlide)
    {
        $this->seriesPerSlide = $seriesPerSlide;
    }

    /**
     * Get date ranfe.
     */
    public function dateRange(): array
    {
        return $this->dateRange;
    }

    /**
     * Get categories.
     */
    public function categories(): array
    {
        return $this->categories;
    }

    /**
     * Get column colors.
     */
    public function colors(): array
    {
        return $this->colors;
    }

    /**
     * Get the consilidated data to be use in chart.
     *
     * @model ConsolidatedGraph.
     */
    public function getConsolidatedData()
    {
        $this->records = $this->model
            ->where('revenue_tracker_id', $this->revenueTrackerID)
            ->whereBetween('created_at', [$this->dateFrom, $this->dateTo])
            ->get()
            ->keyBy(function ($data) {
                return $this->carbon->parse($data->created_at)->format('Y-m-d');
            })
            ->toArray();

        // \Log::info($this->records);
    }

    /**
     * Check if has records.
     */
    public function hasRecords(): bool
    {
        if (count($this->records)) {
            return true;
        }

        return false;
    }

    /**
     * Get the records/ consolidated data.
     */
    public function records()
    {
        return $this->records;
    }

    public function perSubIDRecords()
    {
        return $this->perSubIDRecords;
    }

    public function subIDSummaryRecords()
    {
        return $this->subIDSummaryRecords;
    }

    /**
     * Get the process information.
     */
    public function message(): string
    {
        return $this->message;
    }

    /**
     * Get the list of legends that value were converted to percentage.
     */
    public function legendsValue2Percent(): array
    {
        return $this->valueToPercent;
    }

    /**
     * Get the chart series.
     */
    public function series(): array
    {
        return $this->series;
    }

    /**
     *  Set the chart series.
     *  1. Loop through date range/x-axis of Chart.
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
     *  9. Group the date/categories per slide.
     */
    public function setSeriesThenCategories()
    {
        // #1 ....
        foreach ($this->dateRange as $date) {
            // #2 ....
            foreach ($this->legends as $legend => $details) {
                //3 ....
                if ($this->excludeThisLegend($legend)) {
                    continue;
                }
                // Increment the legend counter
                $this->counters[$legend]['data_count']++;
                // Parameters for functions.
                $param = [$legend, $details['alias'], $date];
                // #4 ....
                if ($this->dateCountGroupingExistsInSeries(...$param)) {
                    // #5 ....
                    if ($col = $this->aliasExistsInSeriesColumn(...$param)) { // search value in the array
                        // #6 ....
                        if ($this->counterDatacountIsLessThanSeriesPerSlide(...$param)) {
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
            // #8 ....
            $this->categories['slide_'.$this->counters['mp_per_views']['date_count']][] = $date;
        }
    }

    /**
     * Exclude legend if not included in selected column and the selected is not all columns.
     */
    protected function excludeThisLegend(string $legend): bool
    {
        // if(count($this->columns) > 1 || (count($this->columns) == 1 && $this->columns[0] != 'all')) {
        if (count($this->columns) > 1 || (count($this->columns) == 1 && $this->columns[0] != 'all')) {
            if (! in_array($legend, $this->columns)) {
                return true;
            }
        }

        return false;
    }

    /**
     *  Check if the grouping by date exist in series container.
     */
    protected function dateCountGroupingExistsInSeries(string $legend): bool
    {
        if (array_key_exists('slide_'.$this->counters[$legend]['date_count'], $this->series)) {
            return true;
        }

        return false;
    }

    /**
     * If the legend alias is in the date grouping
     *
     * @return array|void
     */
    protected function aliasExistsInSeriesColumn(string $legend, string $alias)
    {
        $col = array_column($this->series['slide_'.$this->counters[$legend]['date_count']], 'name');

        if (in_array($alias, $col)) {
            return $col;
        }

    }

    /**
     * Check if the data counter exceed the series per slide value.
     */
    protected function counterDatacountIsLessThanSeriesPerSlide(string $legend): bool
    {
        if ($this->counters[$legend]['data_count'] <= $this->seriesPerSlide) {
            return true;
        }

        return false;
    }

    /**
     * Some column data is not really visible to graph, we have to add more points to make it visible.
     * Tooltip should reverse this function.
     */
    protected function addPoints2MakeColumnVisible(bool $percentage, string $alias, $data)
    {
        if ($percentage && $data > 0) {
            $data = ($data * 100) + 100;
            if (! in_array($alias, $this->valueToPercent)) {
                $this->valueToPercent[] = $alias;
            }
        }

        return $data;
    }

    /**
     * Push data to existing grouping.
     */
    protected function pushDAtaToSeries(string $legend, string $alias, string $date, $percentage, array $col): void
    {
        $data = (array_key_exists($date, $this->records)) ? (float) $this->records[$date][$legend] : 0;
        $data = $this->addPoints2MakeColumnVisible($percentage, $alias, $data);

        array_push(
            $this->series['slide_'.$this->counters[$legend]['date_count']][array_search($alias, $col)]['data'], // Array
            $data // data to push
        );
    }

    /**
     * Push data to new grouping and new column
     */
    protected function pushDAtaToSeriesAsNewDateCountGrouping(string $legend, string $alias, string $date, $percentage): void
    {
        $data = (array_key_exists($date, $this->records)) ? (float) $this->records[$date][$legend] : 0;
        $data = $this->addPoints2MakeColumnVisible($percentage, $alias, $data);

        $this->series['slide_'.$this->counters[$legend]['date_count']][] = [
            'name' => $alias,
            'data' => [$data],
        ];
    }

    /**
     * Increment the date counter and reset the data counter for the data counter exceed the series per slide value.
     */
    protected function incrementDateCountAndResetDataCount(string $legend)
    {
        $this->counters[$legend]['date_count']++;
        $this->counters[$legend]['data_count'] = 1;
    }

    /**
     * Generate the date ranges.
     *
     * @param  Carbon  $start_date
     * @param  Carbon  $end_date
     */
    protected function generateDateRange(Carbon $startDate, Carbon $endDate): array
    {
        $dates = [];

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        return $dates;
    }
}
