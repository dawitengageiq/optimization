<?php

namespace App\Http\Services\Charts;

use Carbon\Carbon;
use Config;

final class NLRChart extends Factories\ChartFactory implements \App\Http\Services\Contracts\ChartContract
{
    use Utils\DummyData;

    /**
     * Version of lead Reactor
     * Range: olr and nlr
     *
     * @var string
     */
    protected $version = 'nlr';

    /**
     * Group of series
     *
     * @var array
     */
    protected $group_series = [];

    /**
     * Group of categories
     *
     * @var array
     */
    protected $group_categories = [];

    /**
     * Group of actual rejection
     *
     * @var array
     */
    protected $group_actual_rejection = [];

    /**
     * Default value for high and critical group padding
     *
     * @var array
     */
    protected $group_padding = [
        'default' => 0.3,
    ];

    /**
     * Load the  needed configuration
     */
    public function __construct()
    {
        //Initial Vars
        $this->config = Config::get('charts');
    }

    /**
     * Provide needed data for formating.
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * Return the series data in group that will be use in views .
     *
     * @return array
     */
    public function getGroupSeries()
    {
        return $this->group_series;
    }

    /**
     * Return the series data in group of categories that will be use in views .
     *
     * @return array
     */
    public function getGroupCategories()
    {
        return $this->group_categories;
    }

    /**
     * Return the data that will be use in views .
     *
     * @return array
     */
    public function getData()
    {
        $data['version'] = $this->version;
        $data['group_series'] = $this->getGroupSeries();
        $data['group_padding'] = $this->getGroupPadding();
        $data['group_categories'] = $this->getGroupCategories();
        $data['actual_rejection'] = $this->getActualRejection();
        $data['column_extra_details'] = $this->getColumnExtraDetails();
        if (count($data['group_series']) == 0) {
            $data['has_data'] = false;
        }

        // echo '<pre>';
        // print_r($data);
        // echo '</pre>';
        return $data;
    }

    /**
     * The initial data for series.
     *
     * @param  string  $type
     */
    protected function initialSeriesData($type = 'all')
    {
        $series[0]['name'] = 'LEADS';
        $series[0]['stack'] = '1';
        $series[0]['data'] = [];
        $series[0]['URLs'] = [];

        $series[1]['name'] = 'DUPLICATES';
        $series[1]['stack'] = '2';
        $series[1]['data'] = [];
        $series[1]['URLs'] = [];

        $series[2]['name'] = 'OTHERS';
        $series[2]['stack'] = '2';
        $series[2]['data'] = [];
        $series[2]['URLs'] = [];

        $series[3]['name'] = 'FILTER ISSUE';
        $series[3]['stack'] = '2';
        $series[3]['data'] = [];
        $series[3]['URLs'] = [];

        $series[4]['name'] = 'PRE-POP ISSUE';
        $series[4]['stack'] = '2';
        $series[4]['data'] = [];
        $series[4]['URLs'] = [];

        if ($type == 'critical' || $type == 'all') {
            $this->series['critical'] = $series;
        }
        if ($type == 'high' || $type == 'all') {
            $this->series['high'] = $series;
        }
    }

    /**
     * Process on formatting data.
     *
     * @param  Bolean  $view
     */
    public function formatData($view = true)
    {
        $this->initialSeriesData();

        $this->number_of_column_per_chart = $number_of_column_per_chart = 5;

        $group_critical = 0;
        $group_high = 0;
        $lead_count = 0;

        foreach ($this->data as $key => $datum) {
            $split = $this->parseSplitData($datum['split']);

            $this->index_4_actual_rejection = $category = $datum['affiliate_id'].' - '.$datum['campaign_id'];

            $this->generateCategories($datum['reject_rate'], $category);
            $this->generateSeries($datum['reject_rate'], $split, $datum);
            $this->generateUrls($datum['reject_rate'], $datum);

            if (count($this->series['critical'][0]['data']) == $number_of_column_per_chart) {
                $this->pushSeries2Group($group_critical, 'critical');
                $group_critical++;
            }
            if (count($this->series['high'][0]['data']) == $number_of_column_per_chart) {
                $this->pushSeries2Group($group_high, 'high');
                $group_high++;
            }

            if ($lead_count == (count($this->data) - 1)) {

                $this->push2GroupWhenLoopsEnd($group_critical, $group_high);
            }
            $lead_count++;
        }
    }

    /**
     * Push the series to group when @var $number_of_column_per_chart is reach.
     *
     * @param  string  $group_error_type
     * @param  string  $error_type
     */
    protected function pushSeries2Group($group_error_type, $error_type)
    {
        $this->setChartAttributes(count($this->categories[$error_type]), $group_error_type, $error_type);

        $this->group_categories[$error_type]['chart_'.$group_error_type][] = $this->categories[$error_type];
        $this->categories[$error_type] = [];

        $this->group_series[$error_type]['chart_'.$group_error_type] = $this->series[$error_type];
        $this->initialSeriesData($error_type);
    }

    /**
     * Push the series to group even the count is not reach the @var $number_of_column_per_chart.
     *
     * @param  string  $group_critical
     * @param  string  $group_high
     */
    protected function push2GroupWhenLoopsEnd($group_critical, $group_high)
    {

        $this->setChartAttributes(count($this->categories['critical']), $group_critical, 'critical');
        $this->setChartAttributes(count($this->categories['high']), $group_high, 'high');

        if (count($this->categories['critical']) > 0) {
            $this->group_categories['critical']['chart_'.$group_critical][] = $this->categories['critical'];
            $this->categories['critical'] = [];
        }

        if (count($this->categories['high']) > 0) {
            $this->group_categories['high']['chart_'.$group_high][] = $this->categories['high'];
            $this->categories['high'] = [];
        }

        if (count($this->series['critical'][0]['data']) > 0) {
            $this->group_series['critical']['chart_'.$group_critical] = $this->series['critical'];
        }

        if (count($this->series['high'][0]['data']) > 0) {
            $this->group_series['high']['chart_'.$group_high] = $this->series['high'];
        }
    }

    /**
     * Generate the url of each column.
     * Push to @var series
     *
     * @param  string  $reject_rate
     * @param  array  $datum
     */
    protected function generateUrls($reject_rate, $datum)
    {

        array_push($this->series[strtolower($reject_rate)][0]['URLs'], $this->urlQueryParams($datum, ''));
        array_push($this->series[strtolower($reject_rate)][1]['URLs'], $this->urlQueryParams($datum, 'duplicates'));
        array_push($this->series[strtolower($reject_rate)][2]['URLs'], $this->urlQueryParams($datum, 'others'));
        array_push($this->series[strtolower($reject_rate)][3]['URLs'], $this->urlQueryParams($datum, 'filter_issues'));
        array_push($this->series[strtolower($reject_rate)][4]['URLs'], $this->urlQueryParams($datum, 'pre_pop_issues'));
    }

    /**
     * Format the query parameters.
     *
     * @param  array  $datum
     * @param  string  $rejected_type
     * @return string
     */
    protected function urlQueryParams($datum, $rejected_type)
    {
        $query_params = '';
        $query_params .= 'admin/searchLeads/?';
        $query_params .= 'campaign_id='.$datum['campaign_id'];
        $query_params .= '&affiliate_id='.$datum['affiliate_id'];
        $query_params .= '&limit_rows=50000';
        $query_params .= '&table=leads';
        // $query_params .= '&lead_status=2';
        // $query_params .= '&rejected_type=' . 'filter_issues';
        if ($rejected_type) {
            $query_params .= '&lead_status=2&';
        }
        if ($rejected_type) {
            $query_params .= '&rejected_type='.$rejected_type;
        }
        $query_params .= '&lead_date_from='.Carbon::yesterday()->toDateString();
        $query_params .= '&lead_date_to='.Carbon::yesterday()->toDateString();

        return $query_params;
    }

    /**
     * Set the column width and group padding for the chart is expanding.
     *
     * @var int
     * @var int
     * @var string
     */
    protected function setChartAttributes($count = 0, $chart_num = 0, $type = '')
    {
        if ($count == 0) {
            return;
        }

        $column_attributes = $this->config['column_attributes'];

        // Add group padding per column, override default
        if (array_key_exists($count, $column_attributes)) {
            $this->group_padding[$type][$chart_num] = $column_attributes[$count]['group_padding'];
        }
    }
}
