<?php

namespace App\Http\Services\Consolidated\Providers;

use App\Http\Services\Consolidated\Export2Excel\ByDateRange;
use App\Http\Services\Consolidated\Export2Excel\ByDateRangeMultiple;
use App\Http\Services\Consolidated\Export2Excel\ByDateWithAllInbox;
use App\Http\Services\Consolidated\Export2Excel\ByRevenueTrackerID;
use App\Http\Services\Consolidated\GraphAllInbox;
use App\Http\Services\Consolidated\GraphByDateRange;
use App\Http\Services\Consolidated\GraphByDateRangeMultiple;
use App\Http\Services\Consolidated\GraphByRevenueTrackerID;

class Graph
{
    /**
     * Application container, to be supplemented.
     */
    protected $app;

    /**
     * Current path.
     */
    // protected $path = '';

    /**
     * Path name of campaign list interface.
     */
    protected $contract = \App\Http\Services\Contracts\ConsolidatedGraphContract::class;

    /**
     * Path name list with class name equivalent.
     */
    protected $className = [
        'all_inbox' => GraphAllInbox::class,
        'date_range' => GraphByDateRange::class,
        'all_affiliate' => GraphByRevenueTrackerID::class,
        'date_range_multiple' => GraphByDateRangeMultiple::class,
        'export_excel_date_range' => ByDateRange::class,
        'export_excel_all_affiliate' => ByRevenueTrackerID::class,
        'export_excel_all_inbox' => ByDateWithAllInbox::class,
        'export_excel_date_range_multiple' => ByDateRangeMultiple::class,
    ];

    /**
     * Instantiate.
     */
    public function __construct($app)
    {
        $this->app = $app;

        return $this->execute();
    }

    /**
     * Static function.
     */
    public static function boot($app)
    {
        if ($app->request->path() == 'admin/consolidatedGraph'
        || $app->request->path() == 'admin/consolidatedGraph/export-excel-date-range'
        || $app->request->path() == 'admin/consolidatedGraph/export-excel-all-affiliate'
        || $app->request->path() == 'admin/consolidatedGraph/export-excel-all-inbox'
        || $app->request->path() == 'admin/consolidatedGraph/export-excel-date-range-multiple'
        ) {
            new static($app);
        }
    }

    /**
     * Bootstrap any application for campaign listing.
     */
    protected function execute(): void
    {
        if ($this->app->request->has('chart_type')) {
            $chartType = str_replace('#', '', $this->app->request->get('chart_type'));
        } else {
            $chartType = 'date_range_multiple';
        }

        $this->app->bind($this->contract, $this->className[$chartType]);
    }
}
