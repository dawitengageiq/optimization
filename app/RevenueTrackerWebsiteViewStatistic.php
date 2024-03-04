<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class RevenueTrackerWebsiteViewStatistic extends Model
{
    protected $connection;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'revenue_tracker_webview_statistics';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'affiliate_id',
        'revenue_tracker_id',
        'website_campaign_id',
        'passovers',
        'payout',
        'created_at',
    ];

    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') != 'reports') {
            $this->connection = 'secondary';
        }
    }

    public function scopeRevenueStatistics($query, $params)
    {
        $columns = [ //for ordering
            'company',
            'passovers',
            'payout',
            'leads',
            'revenue',
            'we_get',
            'margin',
        ];

        $date = [];

        if (isset($params['period'])) {
            if ($params['period'] == 'none' && (! empty($params['start_date']) && ! empty($params['end_date']))) {
                //use the date range
                $date['from'] = $params['start_date'];
                $date['to'] = $params['end_date'];
            } else {
                $date = AffiliateReport::getSnapShotPeriodRange($params['period']);
            }
        } else {
            $date = AffiliateReport::getSnapShotPeriodRange('none');

            if (! empty($params['start_date']) && ! empty($params['end_date'])) {
                $date['from'] = $params['start_date'];
                $date['to'] = $params['end_date'];
            }
        }

        $dateFrom = $date['from'];
        $dateTo = $date['to'];

        $payoutSubquery = 'SUM(revenue_tracker_webview_statistics.payout)';
        $revenueSubQuery = "(SELECT SUM(revenue) FROM iframe_affiliate_reports WHERE EXISTS(SELECT 1 FROM affiliate_revenue_trackers WHERE affiliate_revenue_trackers.affiliate_id = iframe_affiliate_reports.affiliate_id AND affiliate_revenue_trackers.revenue_tracker_id = iframe_affiliate_reports.revenue_tracker_id) AND iframe_affiliate_reports.affiliate_id = revenue_tracker_webview_statistics.affiliate_id AND (iframe_affiliate_reports.created_at>='$dateFrom' AND iframe_affiliate_reports.created_at<='$dateTo'))";

        $query->select(
            'revenue_tracker_webview_statistics.affiliate_id',
            'revenue_tracker_webview_statistics.revenue_tracker_id',
            'affiliates.company',
            DB::raw('SUM(revenue_tracker_webview_statistics.passovers) AS passovers'),
            DB::raw("$payoutSubquery AS payout"),
            DB::raw("(SELECT SUM(lead_count) FROM iframe_affiliate_reports WHERE EXISTS(SELECT 1 FROM affiliate_revenue_trackers WHERE affiliate_revenue_trackers.affiliate_id = iframe_affiliate_reports.affiliate_id AND affiliate_revenue_trackers.revenue_tracker_id = iframe_affiliate_reports.revenue_tracker_id) AND iframe_affiliate_reports.affiliate_id = revenue_tracker_webview_statistics.affiliate_id AND (iframe_affiliate_reports.created_at>='$dateFrom' AND iframe_affiliate_reports.created_at<='$dateTo')) AS leads"),
            DB::raw("$revenueSubQuery AS revenue"),
            DB::raw("($revenueSubQuery - $payoutSubquery) AS we_get"))
            ->join('affiliates', 'revenue_tracker_webview_statistics.affiliate_id', '=', 'affiliates.id');

        $query->whereExists(function ($exists) {
            $exists->select(DB::raw(1))
                ->from('affiliate_revenue_trackers')
                ->whereRaw('affiliate_revenue_trackers.affiliate_id = revenue_tracker_webview_statistics.affiliate_id AND affiliate_revenue_trackers.revenue_tracker_id = revenue_tracker_webview_statistics.revenue_tracker_id');
        });

        $query->whereRaw('DATE(revenue_tracker_webview_statistics.created_at) >= DATE(?) AND DATE(revenue_tracker_webview_statistics.created_at) <= DATE(?)', [$dateFrom, $dateTo]);

        if (isset($params['search']['value']) && $params['search']['value'] != '') {
            $query->where('affiliates.company', 'LIKE', '%'.$params['search']['value'].'%');
        }

        $query->groupBy('revenue_tracker_webview_statistics.affiliate_id');

        if (isset($params['order'])) {
            $order_col = $columns[$params['order'][0]['column']];
            $order_dir = $params['order'][0]['dir'];
            $query->orderBy($order_col, $order_dir);
        }

        return $query;
    }

    public function scopeIframeWebsiteRevenueStatistics($query, $params)
    {
        $columns = [ //for ordering
            'website',
            'passovers',
            'payout',
            'leads',
            'revenue',
            'we_get',
            'margin',
        ];

        $date = [];

        if (isset($params['period'])) {
            if ($params['period'] == 'none' && (! empty($params['start_date']) && ! empty($params['end_date']))) {
                //use the date range
                $date['from'] = $params['start_date'];
                $date['to'] = $params['end_date'];
            } else {
                $date = AffiliateReport::getSnapShotPeriodRange($params['period']);
            }
        } else {
            $date = AffiliateReport::getSnapShotPeriodRange('none');

            if (! empty($params['start_date']) && ! empty($params['end_date'])) {
                $date['from'] = $params['start_date'];
                $date['to'] = $params['end_date'];
            }
        }

        $dateFrom = $date['from'];
        $dateTo = $date['to'];

        $payoutSubquery = 'SUM(revenue_tracker_webview_statistics.payout)';
        $revenueSubQuery = "(SELECT SUM(revenue) FROM iframe_affiliate_reports WHERE iframe_affiliate_reports.affiliate_id = revenue_tracker_webview_statistics.affiliate_id AND revenue_tracker_id=revenue_tracker_webview_statistics.revenue_tracker_id AND (iframe_affiliate_reports.created_at>='$dateFrom' AND iframe_affiliate_reports.created_at<='$dateTo'))";

        $query->select(
            'affiliate_revenue_trackers.website',
            'revenue_tracker_webview_statistics.affiliate_id',
            'revenue_tracker_webview_statistics.revenue_tracker_id',
            DB::raw('SUM(revenue_tracker_webview_statistics.passovers) AS passovers'),
            DB::raw("$payoutSubquery AS payout"),
            DB::raw("(SELECT SUM(lead_count) FROM iframe_affiliate_reports WHERE affiliate_id=revenue_tracker_webview_statistics.affiliate_id AND revenue_tracker_id=revenue_tracker_webview_statistics.revenue_tracker_id AND (iframe_affiliate_reports.created_at>='$dateFrom' AND iframe_affiliate_reports.created_at<='$dateTo') ) AS leads"),
            DB::raw("$revenueSubQuery AS revenue"),
            DB::raw("(SELECT SUM(revenue) FROM iframe_affiliate_reports WHERE iframe_affiliate_reports.affiliate_id = revenue_tracker_webview_statistics.affiliate_id AND revenue_tracker_id=revenue_tracker_webview_statistics.revenue_tracker_id AND (iframe_affiliate_reports.created_at>='$dateFrom' AND iframe_affiliate_reports.created_at<='$dateTo')) - SUM(payout) AS we_get"),
            DB::raw("(($revenueSubQuery - $payoutSubquery)/$revenueSubQuery * 100) AS margin")
        );

        $query->join('affiliate_revenue_trackers', function ($join) {
            $join->on('affiliate_revenue_trackers.affiliate_id', '=', 'revenue_tracker_webview_statistics.affiliate_id')
                ->on('affiliate_revenue_trackers.revenue_tracker_id', '=', 'revenue_tracker_webview_statistics.revenue_tracker_id');
        });

        $query->where(function ($groupQuery) use ($dateFrom, $dateTo) {
            $groupQuery->whereRaw("DATE(revenue_tracker_webview_statistics.created_at) >= DATE('$dateFrom') AND DATE(revenue_tracker_webview_statistics.created_at) <= DATE('$dateTo')");
        });

        if (isset($params['affiliate_id'])) {
            $query->where('revenue_tracker_webview_statistics.affiliate_id', '=', $params['affiliate_id']);
        }

        if (isset($params['revenue_tracker_id'])) {
            $query->where('revenue_tracker_webview_statistics.revenue_tracker_id', '=', $params['revenue_tracker_id']);
        }

        if (isset($params['search']['value'])) {

            $query->where('affiliate_revenue_trackers.website', 'LIKE', '%'.$params['search']['value'].'%');
        }

        $query->groupBy('revenue_tracker_webview_statistics.affiliate_id', 'revenue_tracker_webview_statistics.revenue_tracker_id');

        if (isset($params['order'])) {
            $order_col = $columns[$params['order'][0]['column']];
            $order_dir = $params['order'][0]['dir'];
            $query->orderBy($order_col, $order_dir);
        }

        return $query;
    }
}
