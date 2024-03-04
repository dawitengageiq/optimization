<?php

namespace App\Http\Services\Charts;

use Config;
use File;

final class CreateChartImage extends Factories\ChartFactory
{
    use Utils\JSonData;
    use Utils\DummyData;

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
     *
     * @var array
     */
    public function setData(array $data)
    {
        //$this->data = $data;
        $this->data = $this->config['dummy_serialized_data'];
        $this->offsetData(25);
    }

    /**
     * Process on generating image.
     * This wass called in app\Console\Commands\GenerateCharts
     * Console command "php artisan chart:generate"
     *
     * @var string
     */
    public function generateImage($reject_rate = 'high')
    {
        //$this->offsetData(30);

        // Delete first tje save image
        $this->deleteImage($reject_rate);
        // Format the data from cached
        // False is not include ui part of chart like tooltip.
        $this->formatData(false);
        // Generate the json data
        $this->generateJSon($reject_rate);
        // Save as file
        $this->saveJSon();
        // Create the image
        $this->createImage($reject_rate);
    }

    /**
     * Process on formatting data.
     *
     *
     * @var Bolean
     */
    public function formatData($view = true)
    {
        $this->initialSeriesData();

        foreach ($this->data as $key => $datum) {
            $split = $this->parseSplitData($datum['split']);

            if (array_key_exists('affiliate_id', $datum)) {
                $this->index_4_actual_rejection = $category = $datum['affiliate_id'].' - '.$datum['campaign_id'];
            } else {
                $this->index_4_actual_rejection = $category = $datum['campaign_id'];
            }

            $this->generateCategories($datum['reject_rate'], $category);
            $this->generateSeries($datum['reject_rate'], $split, $datum);
        }

        $this->setChartAttributes();

    }

    /**
     * Set the column width and group padding for the chart is expanding.
     */
    protected function setChartAttributes()
    {
        $column_attributes = $this->config['column_attributes'];
        $critical_count = count($this->categories['critical']);
        $high_count = count($this->categories['high']);

        for ($i = 0; $i <= 4; $i++) {
            $this->series['critical'][$i]['pointWidth'] = 4;
            $this->series['high'][$i]['pointWidth'] = 4;
        }

        if (array_key_exists($critical_count, $column_attributes)) {
            $this->group_padding['critical'] = $column_attributes[$critical_count]['group_padding'];
        }

        if (array_key_exists($high_count, $column_attributes)) {
            $this->group_padding['high'] = $column_attributes[$high_count]['group_padding'];
        }
    }

    /**
     * Delete the image of high and critical.
     *
     * @var string
     */
    protected function deleteImage($reject_rate)
    {
        if (File::exists($this->config['outfile'][$reject_rate])) {
            File::delete($this->config['outfile'][$reject_rate]);
        }
    }

    /**
     * The initial data for series.
     */
    protected function initialSeriesData()
    {
        $series[0]['name'] = 'LEADS';
        $series[0]['stack'] = '1';
        $series[0]['pointWidth'] = $this->point_width;
        $series[0]['data'] = [];

        $series[1]['name'] = 'DUPLICATES';
        $series[1]['stack'] = '2';
        $series[1]['pointWidth'] = $this->point_width;
        $series[1]['data'] = [];

        $series[2]['name'] = 'OTHERS';
        $series[2]['stack'] = '2';
        $series[2]['pointWidth'] = $this->point_width;
        $series[2]['data'] = [];

        $series[3]['name'] = 'FILTER ISSUE';
        $series[3]['stack'] = '2';
        $series[3]['pointWidth'] = $this->point_width;
        $series[3]['data'] = [];

        $series[4]['name'] = 'PRE-POP ISSUE';
        $series[4]['stack'] = '2';
        $series[4]['pointWidth'] = $this->point_width;
        $series[4]['data'] = [];

        $this->series['critical'] = $series;
        $this->series['high'] = $series;
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

        return $actual_value * (floatval(strip_tags($split_value)) / 100);
    }

    /**
     * Process oncreating image.
     * Required to use phantom js
     *
     * @var string
     */
    protected function createImage($reject_rate)
    {
        //Generate Charts now
        $phantomjs = $this->config['phantomjs'];
        $highcharts_convert = $this->config['highcharts_convert'];
        $infile = $this->config['infile'];
        $constr = $this->config['constr'];
        $outfile = $this->config['outfile'][$reject_rate];

        $command = "$phantomjs $highcharts_convert -infile $infile -constr $constr -outfile $outfile -scale 4 -width 1500";

        system($command, $output);
    }
}
