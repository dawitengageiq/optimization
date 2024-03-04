<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class ChartController extends Controller
{
    /*
     * Service handler.
     *
     */
    protected $chart;

    /*
     * Inject dependency - chart.
     *
     */
    public function __construct(\App\Http\Services\Contracts\ChartContract $charts)
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $this->chart = $charts;
    }

    /*
     * Display the Chart for rejection rate per version(nlr, olr).
     * Chart data was pulled from Cache.
     * Cahed data was set by cron: App\Console\Commands\HighRejectionAlertReport.
     *
     * @param Object Illuminate\Http\Request $request
     * @param String $version
     * @return \Illuminate\Http\RedirectResponse
     */
    public function view(Request $request, $version = 'nlr')
    {
        $date = \Cache::has($version.'_rejection_report_date') ? \Cache::get($version.'_rejection_report_date') : Carbon::yesterday();
        $date = Carbon::parse($date)->toFormattedDateString();
        // Flag for script process
        $highcharts = ['has_data' => false, 'date' => $date];

        if (\Cache::has($version.'_rejection_report')) {
            $highcharts['has_data'] = true;

            // Data needed for chart,
            // The data was saved in cached done in app\console\commands\HighRejectionAlertReport
            $this->chart->setData(\Cache::get($version.'_rejection_report'));

            // format data for highchart.
            $this->chart->formatData();
        }
        // Cache is empty
        else {
            // For development, set $use_dummy_data to true to used the dummy data saved in config file.
            // Set $use_dummy_data to false for production
            $use_dummy_data = false;
            if ($request->dummy) {
                $use_dummy_data = true;
            }

            $this->chart->dummyData($use_dummy_data);
            $highcharts['has_data'] = $use_dummy_data;
        }

        // Data to pass on view
        return $highcharts = array_merge($highcharts, $this->chart->getData());

        // printR($highcharts);

        return view('admin.chart', compact('highcharts'));
    }
}
