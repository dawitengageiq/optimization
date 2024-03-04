<?php

namespace App\Jobs\ConsolidatedGraph\Utils;

use App\ConsolidatedGraph;
use Carbon\Carbon;

class RevenueAndViews
{
    /**
     * Calculation traits.
     */
    use Traits\perViews,
        Traits\perClicks,
        Traits\perSurveyTakers,
        Traits\Margin,
        Traits\Views,
        Traits\Revenue;

    /**
     * Data from eloquent traits.
     */
    use Traits\ClicksVsRegistrationStatistics,
        Traits\CakeRevenue,
        Traits\AffiliateReport,
        Traits\RevenueTrackerCakeStatistic,
        Traits\PageViewStatistics,
        Traits\Payout;

    /**
     * Current time container.
     *
     * @var Carbon\Carbon
     */
    protected $carbon;

    /**
     * Legends container
     *
     * @var array
     */
    protected $legends;

    /**
     * Active model container, use to save data.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $consolidatedData;

    /**
     * Records container.
     *
     * @var array
     */
    protected $records;

    /**
     * List of affiliate affiliate reports
     *
     * @var [type]
     */
    protected $affiliateReports = [];

    /**
     * Campaign types
     *
     * @var array
     */
    protected $mixCoreg1 = 1;

    protected $mixCoreg2 = 2;

    /**
     * Default revenue value
     *
     * @var array
     */
    protected $revenues = [
        'source_revenue' => 0,
        'lsp_revenue' => 0,
        'cpa_revenue' => 0,
        'pd_revenue' => 0,
        'tb_revenue' => 0,
        'iff_revenue' => 0,
        'rexadz_revenue' => 0,
        'all_inbox_revenue' => 0,
        'coreg_p1_revenue' => 0,
        'coreg_p2_revenue' => 0,
        'coreg_p3_revenue' => 0,
        'coreg_p4_revenue' => 0,
        'push_revenue' => 0,
    ];

    /**
     * Default survey_takers, all_clicks, margin, and payout value
     *
     * @var array
     */
    protected $clicksRegStats = [
        'survey_takers' => 0,
        'all_clicks' => 0,
        'margin' => 0,
        'payout' => 0,
    ];

    /**
     * [Default view stats value
     *
     * @var array
     */
    protected $pageViewStats = [
        'exit_page_views' => 0,
        'cpa_views' => 0,
        'pd_views' => 0,
        'tb1_views' => 0,
        'tb2_views' => 0,
        'iff_views' => 0,
        'rexadz_views' => 0,
        'coreg_p1_views' => 0,
        'coreg_p2_views' => 0,
        'coreg_p3_views' => 0,
        'coreg_p4_views' => 0,
    ];

    /**
     * Legends that don't need calculation.
     * Don't need a method to genrate value
     *
     * @var array
     */
    protected $indexsNoCalc = [
        'survey_takers',
        'all_clicks',
        'margin',
        'source_revenue',
        'lsp_revenue',
        'pd_revenue',
        'tb_revenue',
        'iff_revenue',
        'rexadz_revenue',
        'all_inbox_revenue',
        'coreg_p1_revenue',
        'coreg_p2_revenue',
        'coreg_p3_revenue',
        'coreg_p4_revenue',
        'cost',
        'push_revenue',
    ];

    /**
     * Instantiate.
     * Provide needed model and Carbon::class.
     * the legends/column for graph was provided from config.
     */
    public function __construct(
        ConsolidatedGraph $model,
        Carbon $carbon
    ) {
        $this->model = $model;

        $this->carbon = $carbon;

        $this->legends = array_keys(config('consolidatedgraph.legends'));
    }

    /**
     * Set benchmarks, benchmarks is an array of selected campaign ids for each campaign type
     * to be used for each campaign type to determine campaign type stats,
     * Like campaign type revenue, clicks, etc....
     */
    public function setBenchmarks(array $benchmarks)
    {
        $this->benchmarks = $benchmarks;
    }

    /**
     * Set the campaign types
     */
    public function setCampaignTypes(array $campaignTypes)
    {
        // mix coreg campaig types
        $counter = 1;
        collect([
            'Mixed Co-reg (1st Grp)',
            'Mixed Co-reg (2st Grp)',
            'Mixed Co-reg (3rd Grp))',
            'Mixed Co-reg (4th Grp))',
        ])
            ->map(function ($type) use ($campaignTypes, &$counter) {
                $coreg = 'mixCoreg'.$counter;
                if ($$coreg = array_search($type, $campaignTypes)) {
                    $this->$coreg = $$coreg;
                }
                $counter++;
            });
    }

    /**
     * Extract the data from otherTables needed to generate records.
     * Generate the parametrs that will be use to generate records.
     */
    public function extractDataFromOtherTables(\App\AffiliateRevenueTracker $revenueTracker, $campaigns, $date): array
    {
        $this->processClicksRegStats($revenueTracker->clicksVsRegistrationStatistics);
        $this->processCakeRevenue($revenueTracker->cakeRevenue, $revenueTracker->revenue_tracker_id, $revenueTracker->exit_page_id, $date);
        $this->processAffiliateReport($revenueTracker->affiliateReport, $campaigns);
        $this->processRevTrackerCakeStats($revenueTracker->revenueTrackerCakeStatistic);
        $this->processPageViewStats($revenueTracker->pageViewStatistics);
        // $this->processMixcoregPayout($revenueTracker->leads);
        $this->processMargin();

        // printR($affiliate->revenueTracker->affiliate_id . ' - ' . $affiliate->revenueTracker->revenue_tracker_id);

        $inputs = [
            'revenue_tracker_id' => $revenueTracker->revenue_tracker_id,
            'survey_takers' => $this->clicksRegStats['survey_takers'],
            'source_revenue' => $this->revenues['source_revenue'],
            'all_clicks' => $this->clicksRegStats['all_clicks'],
            'margin' => $this->clicksRegStats['margin'],
            'cpa_revenue' => $this->revenues['cpa_revenue'],
            'cpa_views' => $this->pageViewStats['cpa_views'],
            'lsp_revenue' => $this->revenues['lsp_revenue'],
            'exit_page_views' => $this->pageViewStats['exit_page_views'],
            'pd_revenue' => $this->revenues['pd_revenue'],
            'pd_views' => $this->pageViewStats['pd_views'],
            'tb_revenue' => $this->revenues['tb_revenue'],
            'tb1_views' => $this->pageViewStats['tb1_views'],
            'tb2_views' => $this->pageViewStats['tb2_views'],
            'iff_revenue' => $this->revenues['iff_revenue'],
            'iff_views' => $this->pageViewStats['iff_views'],
            'rexadz_revenue' => $this->revenues['rexadz_revenue'],
            'rexadz_views' => $this->pageViewStats['rexadz_views'],
            'all_inbox_revenue' => $this->revenues['all_inbox_revenue'],
            'coreg_p1_revenue' => $this->revenues['coreg_p1_revenue'],
            'coreg_p1_views' => $this->pageViewStats['coreg_p1_views'],
            'coreg_p2_revenue' => $this->revenues['coreg_p2_revenue'],
            'coreg_p2_views' => $this->pageViewStats['coreg_p2_views'],
            'coreg_p3_revenue' => $this->revenues['coreg_p3_revenue'],
            'coreg_p3_views' => $this->pageViewStats['coreg_p3_views'],
            'coreg_p4_revenue' => $this->revenues['coreg_p4_revenue'],
            'coreg_p4_views' => $this->pageViewStats['coreg_p4_views'],
            'cost' => $this->clicksRegStats['payout'],
            'push_revenue' => $this->revenues['push_revenue'],
        ];

        return $inputs;
    }

    /**
     * Set the parameters needed to generate records.
     * and Reset some records to default value.
     */
    public function setParams(array $params)
    {
        $this->params = $params;

        // Reset
        $this->affiliateReports = [];
        // Reset
        $this->revenues = [
            'source_revenue' => 0,
            'lsp_revenue' => 0,
            'cpa_revenue' => 0,
            'pd_revenue' => 0,
            'tb_revenue' => 0,
            'iff_revenue' => 0,
            'rexadz_revenue' => 0,
            'all_inbox_revenue' => 0,
            'coreg_p1_revenue' => 0,
            'coreg_p2_revenue' => 0,
            'coreg_p3_revenue' => 0,
            'coreg_p4_revenue' => 0,
            'push_revenue' => 0,
        ];
        // Reset
        $this->clicksRegStats = [
            'survey_takers' => 0,
            'all_clicks' => 0,
            'margin' => 0,
            'payout' => 0,
        ];
        // Reset
        $this->pageViewStats = [
            'exit_page_views' => 0,
            'cpa_views' => 0,
            'pd_views' => 0,
            'tb1_views' => 0,
            'tb2_views' => 0,
            'iff_views' => 0,
            'rexadz_views' => 0,
            'coreg_p1_views' => 0,
            'coreg_p2_views' => 0,
            'coreg_p3_views' => 0,
            'coreg_p4_views' => 0,
        ];
    }

    public function params()
    {
        return $this->params;
    }

    /**
     * Set the revenue tracker id, used to query records filtered by revenue tracker id and current date.
     * Check if database has already have a record.
     * If have records, assign the record to consolidatedData container,
     * if not, clone the modal as a new model to consolidatedData container.
     * Note: the param $data is empty in normal cron process but when an artisan call and date as arguments,
     * the if($date) function will be executed and no duplicate records occur.
     */
    public function checkRevenueTrackerExist(string $date = '')
    {
        if ($date) {
            $this->checkRevenueTrackerInReferenceInTodaysDateIfExists($this->carbon->parse($date));

            if (! $this->revenueTrackerHasRecords()) {
                $this->consolidatedData = clone $this->model;
            }

            $this->consolidatedData->revenue_tracker_id = $this->params['revenue_tracker_id'];

            return;
        }
        $this->consolidatedData = clone $this->model;

        $this->consolidatedData->revenue_tracker_id = $this->params['revenue_tracker_id'];

    }

    /**
     * Generate records, loop through legends and call methods by using string to camel case to call method.
     */
    public function generateRecords()
    {
        // go through all legends and call methods asscociated to legends.
        foreach ($this->legends as $legend) {
            // If legend don't need calculation
            if (in_array($legend, $this->indexsNoCalc)) {
                $this->setLegendValue($legend);
            }
            // Function for calculation.
            else {
                $this->{camelCase($legend)}($legend);
            }
        }
    }

    /**
     * Get records.
     */
    public function records(): array
    {
        return $this->consolidatedData;
    }

    /**
     * SAve the records.
     *
     * @param  Carbon|string  $date
     */
    public function save($date = '')
    {
        echo 'Saving ...<br />';
        if ($date) {
            $this->consolidatedData->created_at = $date;
            $this->consolidatedData->updated_at = $date;
        }
        // printR($this->consolidatedData, true);
        $this->consolidatedData->save();

        // Lets put it back to null.
        $this->consolidatedData = null;
    }

    /**
     * Clone the model for it will be used in loop.
     * Check if the database have record of specific revenue id in reference to current date.
     * Query database with filtering the revenue tracker id and crated at.
     */
    protected function checkRevenueTrackerInReferenceInTodaysDateIfExists(Carbon $date)
    {
        $clone = clone $this->model;
        $this->consolidatedData = $clone->where('revenue_tracker_id', $this->params['revenue_tracker_id'])
            ->whereDate('created_at', '=', $date->format('Y-m-d'))
            ->first();
    }

    /**
     *  Check if revenue tracker has a record on database.
     */
    protected function revenueTrackerHasRecords(): bool
    {
        if ($this->consolidatedData) {
            return true;
        }

        return false;
    }

    /**
     * Set revenue value of legend.
     */
    protected function setLegendValue(string $idx)
    {
        $this->consolidatedData->$idx = $this->params[$idx];
    }
}
