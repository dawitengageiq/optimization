<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class InternalIframeAffiliateReport extends Model
{
    protected $connection;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'iframe_affiliate_reports';

    public $timestamps = false;

    protected $fillable = [
        'affiliate_id',
        'revenue_tracker_id',
        'campaign_id',
        'lead_count',
        'revenue',
        'created_at',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') != 'reports') {
            $this->connection = 'secondary';
        }
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function scopeGetReports($query, $params)
    {
        $columns = [ //for ordering
            'campaigns.name',
            'lead_count',
            'revenue',
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

        $query->select('iframe_affiliate_reports.*', 'campaigns.id');

        $query->join('campaigns', 'campaigns.id', '=', 'iframe_affiliate_reports.campaign_id');

        $query->whereExists(function ($exists) {
            $exists->select(DB::raw(1))
                ->from('affiliate_revenue_trackers')
                ->whereRaw('affiliate_revenue_trackers.affiliate_id = iframe_affiliate_reports.affiliate_id')
                ->whereRaw('affiliate_revenue_trackers.revenue_tracker_id = iframe_affiliate_reports.revenue_tracker_id');
        });

        if (isset($params['affiliate_id']) && $params['affiliate_id'] != '') {
            //Stats for website/revenue tracker
            $query->where('iframe_affiliate_reports.affiliate_id', $params['affiliate_id']);
        }

        if (isset($params['revenue_tracker_id'])) {
            $query->where('iframe_affiliate_reports.revenue_tracker_id', '=', $params['revenue_tracker_id']);
        }

        if ($params['search']['value'] != '') {
            $query->where('campaigns.name', 'LIKE', '%'.$params['search']['value'].'%');
        }

        $query->whereRaw('iframe_affiliate_reports.created_at >= ? and iframe_affiliate_reports.created_at <= ?',
            [
                $dateFrom,
                $dateTo,
            ]);

        if (isset($params['order'])) {
            $order_col = $columns[$params['order'][0]['column']];
            $order_dir = $params['order'][0]['dir'];
            $query->orderBy($order_col, $order_dir);
        }

        return $query;
    }

    public function scopeAllRevenueTracker($query, $params)
    {
        $query->select('iframe_affiliate_reports.revenue_tracker_id', 'campaigns.name');

        $query->join('revenue_tracker_webview_statistics', function ($join) {
            $join->on('iframe_affiliate_reports.affiliate_id', '=', 'revenue_tracker_webview_statistics.affiliate_id')
                ->on('iframe_affiliate_reports.revenue_tracker_id', '=', 'revenue_tracker_webview_statistics.revenue_tracker_id');
        });

        $query->join('campaigns', 'iframe_affiliate_reports.campaign_id', '=', 'campaigns.id');

        $query->whereExists(function ($exists) {
            $exists->select(DB::raw(1))
                ->from('affiliate_revenue_trackers')
                ->whereRaw('affiliate_revenue_trackers.affiliate_id = revenue_tracker_webview_statistics.affiliate_id AND affiliate_revenue_trackers.revenue_tracker_id = revenue_tracker_webview_statistics.revenue_tracker_id');
        });

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

        $query->where(function ($groupQuery) use ($dateFrom, $dateTo) {
            $groupQuery->whereRaw('iframe_affiliate_reports.created_at >= ? AND iframe_affiliate_reports.created_at <= ?', [$dateFrom, $dateTo]);
        });

        $query->groupBy('iframe_affiliate_reports.revenue_tracker_id')
            ->orderBy('iframe_affiliate_reports.revenue_tracker_id', 'asc');

        return $query;
    }

    public function scopeGetRevenueTrackerAffiliateReports($query, $params)
    {
        if (isset($params['revenue_tracker_id'])) {
            $query->where('revenue_tracker_id', '=', $params['revenue_tracker_id']);
        }

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

        $query->where(function ($groupQuery) use ($dateFrom, $dateTo) {
            $groupQuery->whereRaw('created_at >= ? AND created_at <= ?', [$dateFrom, $dateTo]);
        });

        $query->orderBy('campaign_id', 'asc');

        return $query;
    }
}
