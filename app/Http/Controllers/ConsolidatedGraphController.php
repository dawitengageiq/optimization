<?php

namespace App\Http\Controllers;

use App\ConsolidatedGraph;
use App\Http\Services;
use App\Http\Services\Consolidated\Utils\Forms;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConsolidatedGraphController extends Controller
{
    /**
     * Default data for view file.
     *
     * @var array
     */
    protected $viewData = [
        'affiliates' => [],
        'series' => [],
        'categories' => [],
        'legends' => [],
        'records' => [],
        'colors' => [],
        'value_to_percent' => [],
        'inputs' => [
            'chart_type' => '#date_range_multiple',
            'revenue_tracker_id' => '',
            'predefine_dates' => '',
            'date_from' => '',
            'date_to' => '',
            'all_inbox_rev' => '',
            'message' => '',
            'legends' => [],
            'sib_s1' => 0,
            'sib_s2' => 0,
            'sib_s3' => 0,
            'sib_s4' => 0,
        ],
        'has_records' => false,
        'dummy' => false,
        'export_link' => '',
        'ab_testing' => [],
    ];

    /**
     * Required data in generating excel file on each type.
     *
     * @var array
     */
    protected $requirements = [
        // Required data for generating excel in date range
        'export_excel_date_range' => [
            'revenue_tracker_id',
            'date_from',
            'date_to',
        ],
        // Required data for generating excel in all affilate
        'export_excel_all_affiliate' => [
            'date_from',
        ],
        // Required data for generating excel in all inbox
        'export_excel_all_inbox' => [
            'revenue_tracker_id',
            'date_from',
        ],
        // Required data for generating excel in date range multiple
        'export_excel_date_range_multiple' => [
            'date_from',
            'date_to',
        ],
    ];

    /**
     * If the request data is incomplete.
     *
     * @var bool
     */
    protected $incompleteData = false;

    /**
     * Error message for incomplete data, partial ....
     *
     * @var string
     */
    protected $requiredMessage = '<span style="margin-left: 16px;">Please provide the following required data: </span>'."\n".'<ul>';

    /**
     * Instantiation process
     * Load the needed dependencies.
     * Register select affiliates, select legends and datepicker form as html builder.
     */
    public function __construct(
        // Forms\DatePicker $datpicker,
        Services\Consolidated\Utils\Affiliates $affiliates,
        Services\Contracts\ConsolidatedGraphContract $consolidatedGraph
    ) {
        $this->middleware('auth');
        $this->middleware('admin');
        // DB::enableQueryLog();
        $this->affiliates = $affiliates;
        $this->graph = $consolidatedGraph;
    }

    /**
     * Index method
     * Provide the legends from config.
     * Share to view the legends data.
     * Fetch all affiliates.
     * Share to view the affilaites data.
     * Share to view the request data.
     * Process Requests to generate chart.
     * Share to view the chart data.
     */
    public function view(Request $request)
    {
        // \Log::info($request->all());
        // DB::enableQueryLog();
        // DB::connection('secondary')->enableQueryLog();
        // Provide legends from config as column on chart
        $this->graph->setLegends(config('consolidatedgraph.legends'));

        // Provide the selected column for graph to display.
        $this->graph->setSelectedColumn($request->get('legends'));

        // Series per slide
        $this->graph->setSeriesPerSlide(3);
        // Share the data to view, use for modal and legends selections.
        $this->viewData['legends'] = $this->graph->legends();
        $this->viewData['columns'] = $this->graph->columns();

        // Get all the afilliates id, its company name and campaign id.
        $this->affiliates->pluck();
        // Share the data to view, use for affiliates selections
        $this->viewData['affiliates'] = $this->affiliates->get();

        // Override default inputs data with request, use to fill inputs and active selections.
        $this->viewData['inputs'] = array_replace_recursive($this->viewData['inputs'], $request->all());
        //Check Sub Ids
        if ($request->has('predefine_dates') || $request->has('date_from') || $request->has('date_to')) {
            $this->viewData['inputs']['sib_s1'] = $request->sib_s1;
            $this->viewData['inputs']['sib_s2'] = $request->sib_s2;
            $this->viewData['inputs']['sib_s3'] = $request->sib_s3;
            $this->viewData['inputs']['sib_s4'] = $request->sib_s4;
        }

        // Process Requests to generate chart.
        $this->generateChartData($request);

        // Get the list of legends were value was converted to percentage
        $this->viewData['value_to_percent'] = $this->graph->legendsValue2Percent();

        // Fetch column colors
        $this->viewData['colors'] = $this->graph->colors();

        // Process information
        if (! $this->viewData['inputs']['message']) {
            $this->viewData['inputs']['message'] = $this->graph->message();
        }

        if ($request->has('predefine_dates') || $request->has('date_from') || $request->has('date_to')) {
            //AB Testing
            $this->viewData['ab_testing'] = $this->getABTesting($request);
        }

        // \Log::info(DB::getQueryLog());
        // \Log::info(DB::connection('secondary')->getQueryLog());
        //\Log::info($this->viewData['records']);
        // echo '<pre>';
        // print_r($this->viewData);
        // echo '</pre>';
        // exit;
        // return DB::getQueryLog();
        // Log::info(str_replace('#', '', $this->viewData['inputs']['chart_type']));
        return view('admin.consolidated_graph.'.str_replace('#', '', $this->viewData['inputs']['chart_type']))->with($this->viewData);

    }

    /**
     * [export2ExcelByDateRange description]
     *
     * @return void
     */
    public function export2Excel(
        Request $request,
        \App\Http\Services\Consolidated\Export2Excel\Utils\Excel $excel
    ) {
        // \Log::info($request->all());
        // \DB::connection('secondary')->enableQueryLog();
        // \DB::enableQueryLog();
        // Dont generate data if ....
        if ($this->hasIncompleteData($request)) {
            return $this->redirect($request->getQueryString(), 'Some data were missing or some data is not convertable to excel.');
        }

        // Provide legends from config as column on chart
        $this->graph->setLegends(config('consolidatedgraph.legends'));

        // Provide the selected column for graph to display.
        $this->graph->setSelectedColumn($request->get('legends'));

        // Get all the afilliates id, its company name and campaign id.
        // $this->affiliates->pluck();

        // Provide request data
        $this->provideDataFromRequest($request);

        // Fetch all records base on the provide data above.
        $this->graph->getConsolidatedData();

        // Dont generate data if ....
        if (! $this->graph->hasRecords()) {
            return $this->redirect($request->getQueryString(), ' No records were found, please try again with different parameters.');
        }

        // Legends ...
        [$columns, $legendsWithDesc] = $this->setExcelHeadersAndWithDescription($request->get('chart_type'));
        // Log::info($this->graph->records());
        // Excel
        // ob_end_clean();
        // ob_start(); //At the very top of your program (first line)
        // Log::info($this->graph->getColumns());

        $records = $this->graph->records()->toArray();
        array_walk_recursive($records, 'self::stringifyZero');
        // \Log::info($records);

        $excel->setFileName('consolidated-graph')
            ->setTitle('EngageIq Consolidated Graph Report')
            ->setCreator('Jeremie Yunsay')
            ->setCompany('EngageIq')
            ->setDescription('Consolidated Graph Reports')
            ->setSheetName('Records');
        $excel->setRecordsHeader($columns)
            ->setFilters($request->all())
            ->setRecords($records)
            ->setPerSubIDRecords($this->graph->perSubIDRecords()->toArray())
            ->setSubIDSummaryRecords($this->graph->subIDSummaryRecords()->toArray())
            ->setRecordsFooter($columns)
            ->setLegendsWithDescription($legendsWithDesc)
            ->setABColumns($this->graph->getColumns())
            ->setABTesting($this->getABTesting($request));

        $excel->applyLegendsColor($this->applyLegendsColor($request->get('chart_type')));

        $excel->generate();

        // \Log::info(\DB::connection('secondary')->getQueryLog());
        // \Log::info(\DB::getQueryLog());

        $excel->export('xlsx');
        // ob_flush();
    }

    public static function stringifyZero(&$item, $key)
    {
        // \Log::info($key.' - '. $item);
        $keys = ['revenue_tracker_id', 's1', 's2', 's3', 's4', 's5'];
        if (! in_array($key, $keys) && (trim($item) == '' || $item == null)) {
            $item = 0;
        }
        $item = is_numeric($item) && ! in_array($key, $keys) ? (float) sprintf('%0.2f', $item) : (string) $item;

        // \Log::info($key.' - '. $item);
    }

    /**
     * Generate chart data if have Request
     */
    protected function generateChartData(Request $request)
    {
        if (count($request->all())) {
            // Dummy chart
            if ($this->dummyChart($request)) {
                return;
            }

            // Provide request data
            $this->provideDataFromRequest($request);

            // Fetch all records base on the provide data above.
            $this->graph->getConsolidatedData();

            // Set the series for chart
            if ($this->graph->hasRecords()) {
                $this->graph->setSeriesThenCategories();

                // Override default and share the data to view, use for drawing charts.
                $this->viewData['has_records'] = $this->graph->hasRecords();
                $this->viewData['records'] = $this->graph->records();
                $this->viewData['series'] = $this->graph->series();
                $this->viewData['categories'] = $this->graph->categories();
                $this->viewData['export_link'] = $this->exportlink($request);
                $this->viewData['inputs']['all_inbox_rev'] = $this->allInboxRevenue($request->get('chart_type'));
            }
        }
        //
        // printR($this->viewData['series']);
        // printR($this->viewData['categories']);
        // printR($this->viewData['records']);
    }

    /**
     * Generate dummy chart
     */
    protected function dummyChart(Request $request): bool
    {
        if ($request->get('dummy') == 1 || $request->get('dummy') == 'true') {

            // Override default and share the data to view, use for drawing charts.
            $this->viewData['dummy'] = true;
            $this->viewData['has_records'] = true;
            $this->viewData['series'] = config('consolidatedgraph.series');
            $this->viewData['categories'] = config('consolidatedgraph.categories');

            return true;
        }

        return false;
    }

    /**
     * Provide services class with data from request
     */
    protected function provideDataFromRequest(Request $request): void
    {
        $this->graph->setSubIDsInclude([
            's1' => $request->sib_s1,
            's2' => $request->sib_s2,
            's3' => $request->sib_s3,
            's4' => $request->sib_s4,
        ]);

        if ($request->get('chart_type') == '#all_affiliate'
        || $request->get('chart_type') == 'export_excel_all_affiliate'
        ) {
            $this->setDataForAllAffiliatesInSpecificDate($request);

            return;
        }
        if ($request->get('chart_type') == '#all_inbox'
        || $request->get('chart_type') == 'export_excel_all_inbox'
        ) {
            $this->setDataForAllInbox($request);

            return;
        }
        if ($request->get('chart_type') == '#date_range_multiple'
        || $request->get('chart_type') == 'export_excel_date_range_multiple'
        ) {
            $this->setDataForDateRangeMultiple($request);

            return;
        }
        $this->setDataForDateRange($request);

    }

    /**
     * Determine if to apply the legend colors to excel
     */
    protected function applyLegendsColor(string $chartType): bool
    {
        if ($chartType == 'export_excel_date_range_multiple') {
            return true;
        }

        return false;
    }

    /**
     * Graph data by date range.
     */
    protected function setDataForDateRange(Request $request)
    {
        // Provide the specific revenue tracker id for query.
        $this->graph->setRevenueTrackerID($request->get('revenue_tracker_id'));
        // Provide the date range.
        $this->graph->setDateRange($request->get('date_from'), $request->get('date_to'));
    }

    /**
     * Graph data by date range.
     */
    protected function setDataForDateRangeMultiple(Request $request)
    {
        // Provide the predefine dates.
        $this->graph->setPredefineDates($request->get('predefine_dates'));

        // Provide same as date range
        $this->setDataForDateRange($request);
    }

    /**
     * Graph data with all affiliates in specific date.
     */
    protected function setDataForAllAffiliatesInSpecificDate(Request $request)
    {
        // Provide the affilates to retrieve all revenue tracker ids.
        $this->graph->setRevenueTrackerIDs($this->affiliates->get());
        // Provide the specific date.
        $this->graph->setdate($request->get('date_from'));
    }

    /**
     * Graph data with all inbox revenue on specific date.
     */
    protected function setDataForAllInbox(Request $request)
    {
        // Provide the specific revenue tracker id for query.
        $this->graph->setRevenueTrackerID($request->get('revenue_tracker_id'));
        // Provide the specific date.
        $this->graph->setdate($request->get('date_from'));
        //
        $this->graph->setAllInboxRevenue($request->get('all_inbox_rev'));
    }

    /**
     * Link for export to excel button
     */
    protected function exportlink(Request $request): string
    {
        // $revenueTrackerID = (is_array($request->get('revenue_tracker_id'))) ? http_build_query($request->get('revenue_tracker_id')) : $request->get('revenue_tracker_id');

        if (is_array($request->get('revenue_tracker_id'))) {
            $revs['revenue_tracker_id'] = $request->get('revenue_tracker_id');
            $revenueTrackerID = http_build_query($revs);
        } else {
            $revenueTrackerID = 'revenue_tracker_id='.$request->get('revenue_tracker_id');
        }

        $legends = '';
        if (is_array($request->get('legends'))) {
            $legends = http_build_query(['legends' => $request->get('legends')]);
        }

        $chartType = str_replace('#', '', $request->get('chart_type'));

        $exportLink = 'export-excel-'.str_replace('_', '-', $chartType);
        $exportLink .= '?'.$revenueTrackerID;
        $exportLink .= '&amp;predefine_dates='.$request->get('predefine_dates');
        $exportLink .= '&amp;date_from='.$request->get('date_from');
        if ($request->has('date_to')) {
            $exportLink .= '&amp;date_to='.$request->get('date_to');
        }
        if ($legends) {
            $exportLink .= '&amp; '.$legends;
        }
        $exportLink .= '&amp;chart_type=export_excel_'.$chartType;

        // $exportLink .= '&amp;sib_s1=1&amp;sib_s2=1&amp;sib_s3=1&amp;sib_s4=1';

        // if($this->viewData['inputs']['sib_s1'] && $this->viewData['inputs']['sib_s1'] == 1) $exportLink .= '&amp;dsib_s1=1';
        // if($this->viewData['inputs']['sib_s2'] && $this->viewData['inputs']['sib_s2'] == 1) $exportLink .= '&amp;dsib_s2=1';
        // if($this->viewData['inputs']['sib_s3'] && $this->viewData['inputs']['sib_s3'] == 1) $exportLink .= '&amp;dsib_s3=1';
        // if($this->viewData['inputs']['sib_s4'] && $this->viewData['inputs']['sib_s4'] == 1) $exportLink .= '&amp;dsib_s4=1';

        if ($this->viewData['inputs']['sib_s1'] && $this->viewData['inputs']['sib_s1'] == 1) {
            $exportLink .= '&amp;sib_s1=1';
        }
        if ($this->viewData['inputs']['sib_s2'] && $this->viewData['inputs']['sib_s2'] == 1) {
            $exportLink .= '&amp;sib_s2=1';
        }
        if ($this->viewData['inputs']['sib_s3'] && $this->viewData['inputs']['sib_s3'] == 1) {
            $exportLink .= '&amp;sib_s3=1';
        }
        if ($this->viewData['inputs']['sib_s4'] && $this->viewData['inputs']['sib_s4'] == 1) {
            $exportLink .= '&amp;sib_s4=1';
        }

        return $exportLink;
    }

    /**
     * All Inbox revenue
     */
    protected function allInboxRevenue(string $chartType)
    {
        if ($chartType == '#all_inbox' && array_key_exists($this->viewData['inputs']['date_from'], $this->viewData['records'])) {
            return $this->viewData['records'][$this->viewData['inputs']['date_from']]['all_inbox_revenue'];
        }

        return '';
    }

    /**
     * Dtermine if import to excel request parameters are complete.
     */
    protected function hasIncompleteData(Request $request): bool
    {

        if (array_key_exists($request->get('chart_type'), $this->requirements)) {
            foreach ($this->requirements[$request->get('chart_type')] as $required) {
                if ($required == 'date_from' || $required == 'date_to') {
                    if (! $request->has($required) && (! $request->has('predefine_dates') || $request->get('predefine_dates') == '')) {
                        $this->incompleteData = true;
                        $this->requiredMessage .= '<li>'.ucwords(str_replace('_', ' ', $required)).'.</li>';
                    }
                } elseif (! $request->has($required)) {
                    $this->incompleteData = true;
                    $this->requiredMessage .= '<li>'.ucwords(str_replace('_', ' ', $required)).'.</li>';
                }
            }
            if ($this->incompleteData) {
                $this->requiredMessage = '<br />'.urlencode($this->requiredMessage).'</ul>';

                return true;
            }

            return false;
        }

        $this->incompleteData = true;
        $this->requiredMessage .= '<li> Chart Type.</li>';

        return true;
    }

    /**
     * Redirect page tp graph page.
     */
    protected function redirect($queryString, $errorMessage): static
    {
        return redirect()
            ->route('consolidated_graph', $this->redirectData($queryString))
            ->withErrors('Export Excel Failed! '.$errorMessage.$this->requiredMessage.'</ul>');
    }

    /**
     * Request data for redirection
     *
     * @param  string  $msg
     */
    protected function redirectData($queryString): array
    {
        parse_str($queryString, $array);
        if (array_key_exists('chart_type', $array)) {
            $array['chart_type'] = str_replace('export_excel_', '#', $array['chart_type']);
        }

        return $array;
    }

    /**
     * Set legends as header and set the legends with description
     */
    protected function setExcelHeadersAndWithDescription(string $chartType): array
    {
        $legends = $this->graph->legends();
        $legendsWithDesc = [];
        $columns = ['Date'];

        if ($chartType == 'export_excel_all_affiliate' || 'export_excel_date_range_multiple') {
            $columns[] = 'Rev Track ID';
            $columns[] = 'S1';
            $columns[] = 'S2';
            $columns[] = 'S3';
            $columns[] = 'S4';
            $columns[] = 'S5';
        }

        foreach ($this->graph->columns() as $column) {
            $columns[] = $legends[$column]['alias'];
            $legendsWithDesc[] = [$legends[$column]['alias'], $legends[$column]['color'], $legends[$column]['desc']];
        }

        return [$columns, $legendsWithDesc];
    }

    protected function getABTesting(Request $request)
    {
        // DB::enableQueryLog();

        $date_from = $request->date_from != '' ? Carbon::parse($request->date_from)->toDateString() : '';
        $date_to = $request->date_to != '' ? Carbon::parse($request->date_to)->toDateString() : '';
        $base_date = '';
        if ($request->predefine_dates != '') {
            switch ($request->predefine_dates) {
                case 'yesterday':
                    // $date_from = Carbon::yesterday()->startOfDay();
                    // $date_to = Carbon::yesterday()->endOfDay();
                    $base_date = Carbon::yesterday()->toDateString();
                    break;
                case 'week_to_date':
                    $date_from = Carbon::now()->startOfWeek();
                    $date_to = Carbon::now()->endOfDay();
                    break;
                case 'month_to_date':
                    $date_from = Carbon::now()->startOfMonth();
                    $date_to = Carbon::now()->endOfDay();
                    break;
                case 'last_month':
                    $date_from = Carbon::now()->subMonth()->startOfMonth();
                    $date_to = Carbon::now()->subMonth()->endOfMonth();
                    break;
            }
        }

        $base = false;
        if ($base_date == '') {
            $qry = ConsolidatedGraph::whereBetween('created_at', [$date_from, $date_to])->orderBy('created_at', 'desc')->first();
            if ($qry) {
                $base_date = Carbon::parse($qry->created_at)->toDateString();
                // Log::info('Today: '. $base_date);
            }
        }
        if ($base_date != '') {
            $base = ConsolidatedGraph::getABTesting($base_date, $base_date, $request->all())->select(DB::RAW($this->graph->getColumnSumQry()))->first();
        }

        //Yesterday
        $yesterday = false;
        $qry = ConsolidatedGraph::where(DB::RAW('created_at'), '<', $base_date)->orderBy('created_at', 'desc')->first();
        $yesterday_date = $qry ? Carbon::parse($qry->created_at)->toDateString() : '';
        // Log::info('Yesterday: '. $yesterday_date);
        if ($yesterday_date != '') {
            $yesterday = ConsolidatedGraph::getABTesting($yesterday_date, $yesterday_date, $request->all())->select(DB::RAW($this->graph->getColumnSumQry()))->first();
        }

        $thirty = false;
        $thirty_from = '';
        $thirty_to = '';
        if ($base_date != '') {
            $thirty_from = Carbon::parse($base_date)->subDay(30)->toDateString();
            $thirty_to = Carbon::parse($base_date)->subDay(1)->toDateString();
            // Log::info('30: '. $thirty_from . $thirty_to);
            $thirty = ConsolidatedGraph::getABTesting($thirty_from, $thirty_to, $request->all())->select(DB::RAW($this->graph->getColumnSumQry()))->first();
        }

        // Log::info(DB::getQueryLog());
        // Log::info($base);
        // Log::info($yesterday);
        // Log::info($thirty);

        $results = [
            'base_date' => $base_date,
            'yesterday_date' => $yesterday_date,
            'thirty_date_from' => $thirty_from,
            'thirty_date_to' => $thirty_to,
        ];
        foreach ($this->graph->getColumns() as $col) {
            $base_col = $base ? $base->$col : 0;
            $yesterday_col = $yesterday ? $yesterday->$col : 0;
            $result = $base_col - $yesterday_col;
            $perc = $base_col != 0 && $result != 0 ? (($result / $base_col) * 100) : 0;
            $results['yesterday'][$col] = [
                'r' => sprintf('%0.2f', $result),
                'p' => $perc,
                'c' => $this->getColorBasedOnPercentage($perc),
                'b' => $base_col,
                'y' => $yesterday_col,
            ];

            $thirty_col = $thirty ? $thirty->$col : 0;
            $avg = $thirty_col != 0 ? $thirty_col / 30 : 0;
            $result = $base_col - $avg;
            $perc = $base_col != 0 && $result != 0 ? (($result / $base_col) * 100) : 0;
            $results['30'][$col] = [
                'r' => sprintf('%0.2f', $result),
                'p' => $perc,
                'c' => $this->getColorBasedOnPercentage($perc),
                'b' => $base_col,
                't' => $thirty_col,
                'a' => $avg,
            ];
        }

        // Log::info($results);
        session(['revenue_funnel_results' => $results]);

        return $results;
    }

    protected function getColorBasedOnPercentage($result)
    {
        if ($result >= 5) {
            return 'green';
        } elseif ($result <= -5 && $result > -10) {
            return 'yellow';
        } elseif ($result <= -10) {
            return 'red';
        } else {
            return '';
        }
    }
}
