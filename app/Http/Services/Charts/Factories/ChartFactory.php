<?php

namespace App\Http\Services\Charts\Factories;

abstract class ChartFactory
{
    /**
     * When new chart will be created, this class should be extended.
     */

    /**
     * Save config for chart.
     * config\charts.php
     *
     * @var array
     */
    protected $config;

    /**
     * @var array
     * @var string
     */
    protected $data;

    protected $json;

    /**
     * Default value for type of chart
     * Range: column, bar, etc....
     *
     * @var string
     */
    protected $type = 'column';

    /**
     * Default value for width of each column
     *
     * @var int
     */
    protected $point_width = 18;

    /**
     * Container for high and critical categories
     *
     * @var array
     */
    protected $categories = [
        'high' => [],
        'critical' => [],
    ];

    /**
     * Container for high and critical series
     *
     * @var array
     */
    protected $series = [
        'high' => [],
        'critical' => [],
    ];

    /**
     * Default value for high and critical group padding
     *
     * @var array
     */
    protected $group_padding = [
        'high' => 0.3,
        'critical' => 0.3,
    ];

    /**
     * Container for high and critical actual rejection
     *
     * @var array
     */
    protected $actual_rejection = [
        'high' => [],
        'critical' => [],
    ];

    /**
     * Container for details of each column
     *
     * @var array
     */
    protected $column_extra_details = [];

    /**
     * Provide needed data for formating.
     *
     * @var array
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * Provide the chart type.
     * Range: column, bar, etc....
     *
     * @var string
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Return the categories .
     *
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Return the categories .
     *
     * @return array
     */
    public function getGroupPadding()
    {
        return $this->group_padding;
    }

    /**
     * Return the actual rejection .
     *
     * @return array
     */
    public function getActualRejection()
    {
        return $this->actual_rejection;
    }

    /**
     * Return the column extra details .
     *
     * @return array
     */
    public function getColumnExtraDetails()
    {
        return $this->column_extra_details;
    }

    /**
     * Set the number of index where the data will include in new array.
     * this is only for development so not to populate huge data.
     *
     * @var int
     */
    protected function offsetData($offsetKey)
    {
        // Grab all the keys of your actual array and put in another array.
        $n = array_keys($this->data);
        // Returns the position of the offset from this array using search.
        $count = array_search($offsetKey, $n);
        // Slice it with the 0 index as start and position+1 as the length parameter.
        $this->data = array_slice($this->data, 0, $count + 1, true);
    }

    /**
     * Parse the $var split to nice array for easy manipulation.
     *
     * @var string
     *
     * @return array
     */
    protected function parseSplitData($split)
    {
        $split = str_replace(', ', '&', $split);
        $split = str_replace(' = ', '=', $split);
        parse_str($split, $split);

        return $split;
    }

    /**
     * Generate the category of each column.
     * Push to @var categories
     *
     * @var string
     * @var array
     */
    protected function generateCategories($reject_rate, $category)
    {
        array_push($this->categories[strtolower($reject_rate)], $category);
    }

    /**
     * Generate the series of each column.
     * Push to @var series
     *
     * @var string
     * @var string
     * @var array
     */
    protected function generateSeries($reject_rate, $split, $datum)
    {
        array_push($this->series[strtolower($reject_rate)][0]['data'], $datum['lead_count']);

        array_push($this->series[strtolower($reject_rate)][1]['data'], array_key_exists('Duplicates', $split) ? $this->getRealValue($split['Duplicates'], $datum) : 0);

        array_push($this->series[strtolower($reject_rate)][2]['data'], array_key_exists('Others', $split) ? $this->getRealValue($split['Others'], $datum) : 0);

        array_push($this->series[strtolower($reject_rate)][3]['data'], array_key_exists('Filter_Issue', $split) ? $this->getRealValue($split['Filter_Issue'], $datum) : 0);

        array_push($this->series[strtolower($reject_rate)][4]['data'], array_key_exists('Pre-pop_Issue', $split) ? $this->getRealValue($split['Pre-pop_Issue'], $datum) : 0);
    }

    /**
     * Calculate the real value of each column .
     *
     * @var string
     * @var array
     *
     * @return int
     */
    protected function getRealValue($split_value, $datum)
    {
        $actual_value = $datum['lead_count'] * (floatval(strip_tags($datum['actual_rejection'])) / 100);
        $this->generateActualRejection((strip_tags($datum['actual_rejection'])), $actual_value);
        $this->columnExtraDetails($datum);

        return $actual_value * (floatval(strip_tags($split_value)) / 100);
    }

    /**
     * Generate the actual rejection of each column each column.
     * Push to @var series
     *
     * @var string
     * @var string
     */
    protected function generateActualRejection($actual_rejection, $actual_value)
    {
        $this->actual_rejection[$this->index_4_actual_rejection] = [
            'actual' => $actual_value,
            'percent' => $actual_rejection,
        ];
    }

    /**
     * Set th extra detail for each column in series.
     * Push to @var column_extra_details
     *
     * @var array
     */
    protected function columnExtraDetails($data)
    {
        $array = [];
        if (array_key_exists('affiliate_id', $data)) {
            $array['affiliate_id'] = $data['affiliate_id'];
        }
        if (array_key_exists('campaign_id', $data)) {
            $array['campaign_id'] = $data['campaign_id'];
        }
        if (array_key_exists('campaign', $data)) {
            $array['campaign_name'] = $data['campaign'];
        }

        $this->column_extra_details[$this->index_4_actual_rejection] = $array;
    }
}
