<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class RevenueTrackerCakeStatistic extends Model
{
    protected $connection;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'revenue_tracker_cake_statistics';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'affiliate_id',
        'revenue_tracker_id',
        'cake_campaign_id',
        'type',
        'clicks',
        'payout',
        'created_at',
        's1',
        's2',
        's3',
        's4',
        's5',
    ];

    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') != 'reports') {
            $this->connection = 'secondary';
        }
    }

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function scopeWebsiteRevenueStatisticsForServersideRevised($query, $params)
    {
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

        $payoutSubquery = 'SUM(payout)';
        $revenueSubQuery = "(SELECT SUM(revenue) FROM affiliate_reports WHERE affiliate_reports.affiliate_id = revenue_tracker_cake_statistics.affiliate_id AND revenue_tracker_id=revenue_tracker_cake_statistics.revenue_tracker_id AND (affiliate_reports.created_at>='$dateFrom' AND affiliate_reports.created_at <= '$dateTo'))";

        $query->select(
            // 'affiliate_revenue_trackers.website',
            'revenue_tracker_cake_statistics.affiliate_id',
            'revenue_tracker_cake_statistics.revenue_tracker_id',
            DB::raw('SUM(clicks) AS clicks'),
            DB::raw("$payoutSubquery AS payout"),
            DB::raw("(SELECT SUM(lead_count) FROM affiliate_reports WHERE affiliate_id=revenue_tracker_cake_statistics.affiliate_id AND revenue_tracker_id=revenue_tracker_cake_statistics.revenue_tracker_id AND (affiliate_reports.created_at >= '$dateFrom' AND affiliate_reports.created_at <= '$dateTo')) AS leads"),
            DB::raw("$revenueSubQuery AS revenue"),
            DB::raw("$revenueSubQuery - $payoutSubquery AS we_get"),
            DB::raw("(($revenueSubQuery - $payoutSubquery)/$revenueSubQuery * 100) AS margin")
        );

        $query->where(function ($groupQuery) use ($dateFrom, $dateTo) {
            $groupQuery->whereRaw("revenue_tracker_cake_statistics.created_at >= '$dateFrom' AND revenue_tracker_cake_statistics.created_at <= '$dateTo'");
        });

        if (isset($params['affiliate_id'])) {
            $query->where('revenue_tracker_cake_statistics.affiliate_id', '=', $params['affiliate_id']);
        }

        if (isset($params['revenue_tracker_id'])) {
            $query->where('revenue_tracker_cake_statistics.revenue_tracker_id', '=', $params['revenue_tracker_id']);
        }

        if (isset($params['affiliate_type'])) {
            //Internal = 1, H&P = 2 or Both = 0
            if ($params['affiliate_type'] == 1) {
                $query->where('revenue_tracker_cake_statistics.type', '=', 1);
            } elseif ($params['affiliate_type'] == 2) {
                $query->where('revenue_tracker_cake_statistics.type', '=', 2);
            }
        }

        $query->groupBy('revenue_tracker_cake_statistics.affiliate_id', 'revenue_tracker_cake_statistics.revenue_tracker_id');

        return $query;
    }

    public function scopeWebsiteRevenueStatisticsForServerside($query, $params)
    {
        $columns = [ //for ordering
            'website',
            'clicks',
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

        $query->select('revenue_tracker_cake_statistics.*',
            'affiliate_revenue_trackers.website',
            DB::raw('SUM(affiliate_reports.lead_count) AS leads'),
            DB::raw('SUM(affiliate_reports.revenue) AS revenue'),
            DB::raw('(SUM(affiliate_reports.revenue) - payout) AS we_get'),
            DB::raw('(((SUM(affiliate_reports.revenue) - payout)/SUM(affiliate_reports.revenue)) * 100) AS margin'));

        $query->join('affiliate_revenue_trackers', function ($join) {
            $join->on('affiliate_revenue_trackers.affiliate_id', '=', 'revenue_tracker_cake_statistics.affiliate_id')
                ->on('affiliate_revenue_trackers.revenue_tracker_id', '=', 'revenue_tracker_cake_statistics.revenue_tracker_id');
        });

        $query->join('affiliate_reports', function ($join) {
            $join->on('affiliate_reports.affiliate_id', '=', 'revenue_tracker_cake_statistics.affiliate_id')
                ->on('affiliate_reports.revenue_tracker_id', '=', 'revenue_tracker_cake_statistics.revenue_tracker_id');
        });

        $query->where(function ($groupQuery) use ($dateFrom, $dateTo) {
            $groupQuery->whereRaw('revenue_tracker_cake_statistics.created_at >= ? AND revenue_tracker_cake_statistics.created_at <= ?', [$dateFrom, $dateTo]);
        });

        $query->where(function ($groupQuery) use ($dateFrom, $dateTo) {
            $groupQuery->whereRaw('affiliate_reports.created_at >= ? AND affiliate_reports.created_at <= ?', [$dateFrom, $dateTo]);
        });

        if (isset($params['affiliate_type'])) {
            //Internal = 1, H&P = 2 or Both = 0
            if ($params['affiliate_type'] == 1) {
                $query->where('revenue_tracker_cake_statistics.type', '=', 1);
            } elseif ($params['affiliate_type'] == 2) {
                $query->where('revenue_tracker_cake_statistics.type', '=', 2);
            }
        }

        if (isset($params['affiliate_id'])) {
            $query->where('revenue_tracker_cake_statistics.affiliate_id', '=', $params['affiliate_id']);
        }

        if (isset($params['revenue_tracker_id'])) {
            $query->where('revenue_tracker_cake_statistics.revenue_tracker_id', '=', $params['revenue_tracker_id']);
        }

        if (isset($params['search']['value']) && $params['search']['value'] != '') {
            $query->where('affiliate_revenue_trackers.website', 'LIKE', '%'.$params['search']['value'].'%');
        }

        $query->groupBy('revenue_tracker_cake_statistics.affiliate_id', 'revenue_tracker_cake_statistics.revenue_tracker_id');

        if (isset($params['order'])) {
            $order_col = $columns[$params['order'][0]['column']];
            $order_dir = $params['order'][0]['dir'];
            $query->orderBy($order_col, $order_dir);
        }

        return $query;
    }

    public function scopeRevenueStatisticsForServerside($query, $params)
    {
        $columns = [ //for ordering
            'company',
            'clicks',
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

        $payoutSubquery = 'SUM(revenue_tracker_cake_statistics.payout)';
        $revenueSubQuery = "(SELECT SUM(revenue) FROM affiliate_reports WHERE EXISTS(SELECT 1 FROM affiliate_revenue_trackers WHERE affiliate_revenue_trackers.affiliate_id = affiliate_reports.affiliate_id AND affiliate_revenue_trackers.revenue_tracker_id = affiliate_reports.revenue_tracker_id) AND affiliate_reports.affiliate_id = revenue_tracker_cake_statistics.affiliate_id AND (affiliate_reports.created_at >= '$dateFrom' AND affiliate_reports.created_at <= '$dateTo'))";

        $query->select('revenue_tracker_cake_statistics.affiliate_id',
            'revenue_tracker_cake_statistics.revenue_tracker_id',
            'affiliates.company',
            DB::raw('SUM(revenue_tracker_cake_statistics.clicks) AS clicks'),
            DB::raw("$payoutSubquery AS payout"),
            DB::raw("(SELECT SUM(lead_count) FROM affiliate_reports WHERE EXISTS(SELECT 1 FROM affiliate_revenue_trackers WHERE affiliate_revenue_trackers.affiliate_id = affiliate_reports.affiliate_id AND affiliate_revenue_trackers.revenue_tracker_id = affiliate_reports.revenue_tracker_id) AND affiliate_reports.affiliate_id = revenue_tracker_cake_statistics.affiliate_id AND (affiliate_reports.created_at >= '$dateFrom' AND affiliate_reports.created_at <= '$dateTo')) AS leads"),
            DB::raw("$revenueSubQuery AS revenue"),
            DB::raw("($revenueSubQuery - $payoutSubquery) AS we_get"))
            ->join('affiliates', 'revenue_tracker_cake_statistics.affiliate_id', '=', 'affiliates.id');

        $query->whereExists(function ($exists) {
            $exists->select(DB::raw(1))
                ->from('affiliate_revenue_trackers')
                ->whereRaw('affiliate_revenue_trackers.affiliate_id = revenue_tracker_cake_statistics.affiliate_id AND affiliate_revenue_trackers.revenue_tracker_id = revenue_tracker_cake_statistics.revenue_tracker_id');
        });

        $query->whereRaw('revenue_tracker_cake_statistics.created_at >= ? AND revenue_tracker_cake_statistics.created_at <= ?', [$dateFrom, $dateTo]);

        if (isset($params['affiliate_type'])) {
            //Internal = 1, H&P = 2 or Both = 0
            if ($params['affiliate_type'] == 1) {
                $query->where('revenue_tracker_cake_statistics.type', '=', 1);
            } elseif ($params['affiliate_type'] == 2) {
                $query->where('revenue_tracker_cake_statistics.type', '=', 2);
            }
        }

        if (isset($params['search']['value']) && $params['search']['value'] != '') {
            $query->where('affiliates.company', 'LIKE', '%'.$params['search']['value'].'%');
        }

        $query->groupBy('revenue_tracker_cake_statistics.affiliate_id');

        if (isset($params['order'])) {
            $order_col = $columns[$params['order'][0]['column']];
            $order_dir = $params['order'][0]['dir'];
            $query->orderBy($order_col, $order_dir);
        }

        return $query;
    }

    //For Affiliate Excel Report
    public function scopeSubIDBreakdown($query, $params)
    {
        $columns = [ //for ordering
            'company',
            'clicks',
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

        $payoutSubquery = 'SUM(revenue_tracker_cake_statistics.payout)';
        $revenueSubQuery = "(SELECT SUM(revenue) FROM affiliate_reports WHERE affiliate_reports.revenue_tracker_id = revenue_tracker_cake_statistics.revenue_tracker_id  AND affiliate_reports.affiliate_id = revenue_tracker_cake_statistics.affiliate_id AND affiliate_reports.s1 = revenue_tracker_cake_statistics.s1 AND affiliate_reports.s2 = revenue_tracker_cake_statistics.s2 AND affiliate_reports.s3 = revenue_tracker_cake_statistics.s3 AND affiliate_reports.s4 = revenue_tracker_cake_statistics.s4 AND affiliate_reports.s5 = revenue_tracker_cake_statistics.s5 AND (affiliate_reports.created_at>='$dateFrom' AND affiliate_reports.created_at <= '$dateTo'))";

        $query->select('revenue_tracker_cake_statistics.affiliate_id',
            'revenue_tracker_cake_statistics.revenue_tracker_id',
            // 'affiliates.company',
            'revenue_tracker_cake_statistics.s1',
            'revenue_tracker_cake_statistics.s2',
            'revenue_tracker_cake_statistics.s3',
            'revenue_tracker_cake_statistics.s4',
            'revenue_tracker_cake_statistics.s5',
            DB::raw('SUM(revenue_tracker_cake_statistics.clicks) AS clicks'),
            DB::raw("$payoutSubquery AS payout")
        );
        // ->join('affiliates', 'revenue_tracker_cake_statistics.affiliate_id', '=', 'affiliates.id');

        $query->whereRaw('revenue_tracker_cake_statistics.created_at >= ? AND revenue_tracker_cake_statistics.created_at <= ?', [$dateFrom, $dateTo]);

        if (isset($params['affiliate_type'])) {
            //Internal = 1, H&P = 2 or Both = 0
            if ($params['affiliate_type'] == 1) {
                $query->where('revenue_tracker_cake_statistics.type', '=', 1);
            } elseif ($params['affiliate_type'] == 2) {
                $query->where('revenue_tracker_cake_statistics.type', '=', 2);
            }
        }

        $query->groupBy('revenue_tracker_cake_statistics.affiliate_id', 'revenue_tracker_cake_statistics.revenue_tracker_id', 'revenue_tracker_cake_statistics.s1', 'revenue_tracker_cake_statistics.s2', 'revenue_tracker_cake_statistics.s3', 'revenue_tracker_cake_statistics.s4', 'revenue_tracker_cake_statistics.s5');

        $query->orderBy('revenue_tracker_cake_statistics.affiliate_id', 'asc')
            ->orderBy('revenue_tracker_cake_statistics.revenue_tracker_id', 'asc')
            ->orderBy('revenue_tracker_cake_statistics.s1', 'asc')
            ->orderBy('revenue_tracker_cake_statistics.s2', 'asc')
            ->orderBy('revenue_tracker_cake_statistics.s3', 'asc')
            ->orderBy('revenue_tracker_cake_statistics.s4', 'asc')
            ->orderBy('revenue_tracker_cake_statistics.s5', 'asc');

        return $query;
    }

    public function scopeRevTrackerBreakdown($query, $params)
    {
        $columns = [ //for ordering
            'company',
            'clicks',
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

        $payoutSubquery = 'SUM(revenue_tracker_cake_statistics.payout)';
        $revenueSubQuery = "(SELECT SUM(revenue) FROM affiliate_reports WHERE affiliate_reports.revenue_tracker_id = revenue_tracker_cake_statistics.revenue_tracker_id  AND affiliate_reports.affiliate_id = revenue_tracker_cake_statistics.affiliate_id AND (affiliate_reports.created_at >= '$dateFrom' AND affiliate_reports.created_at <= '$dateTo'))";

        $query->select('revenue_tracker_cake_statistics.affiliate_id',
            'revenue_tracker_cake_statistics.revenue_tracker_id',
            // 'affiliates.company',
            DB::raw('SUM(revenue_tracker_cake_statistics.clicks) AS clicks'),
            DB::raw("$payoutSubquery AS payout")
        );

        $query->whereRaw('revenue_tracker_cake_statistics.created_at >= ? AND revenue_tracker_cake_statistics.created_at <= ?', [$dateFrom, $dateTo]);

        if (isset($params['affiliate_type'])) {
            //Internal = 1, H&P = 2 or Both = 0
            if ($params['affiliate_type'] == 1) {
                $query->where('revenue_tracker_cake_statistics.type', '=', 1);
            } elseif ($params['affiliate_type'] == 2) {
                $query->where('revenue_tracker_cake_statistics.type', '=', 2);
            }
        }

        $query->groupBy('revenue_tracker_cake_statistics.affiliate_id', 'revenue_tracker_cake_statistics.revenue_tracker_id');

        $query->orderBy('revenue_tracker_cake_statistics.affiliate_id', 'asc')
            ->orderBy('revenue_tracker_cake_statistics.revenue_tracker_id', 'asc');

        return $query;
    }

    public function scopeAffiliateBreakdown($query, $params, $countOnly = false)
    {
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

        $query->whereRaw('revenue_tracker_cake_statistics.created_at >= ? AND revenue_tracker_cake_statistics.created_at <= ?', [$dateFrom, $dateTo]);

        if (isset($params['affiliate_type'])) {
            //Internal = 1, H&P = 2 or Both = 0
            if ($params['affiliate_type'] == 1) {
                $query->where('revenue_tracker_cake_statistics.type', '=', 1);
            } elseif ($params['affiliate_type'] == 2) {
                $query->where('revenue_tracker_cake_statistics.type', '=', 2);
            }
        }

        $query->groupBy('revenue_tracker_cake_statistics.affiliate_id');

        if (! $countOnly) {
            $query->select(
                'revenue_tracker_cake_statistics.affiliate_id',
                // 'affiliates.company',
                DB::RAW('SUM(revenue_tracker_cake_statistics.clicks) AS clicks'),
                DB::RAW('SUM(revenue_tracker_cake_statistics.payout) AS payout'),
                DB::RAW("(SELECT SUM(lead_count) FROM affiliate_reports WHERE affiliate_reports.affiliate_id = revenue_tracker_cake_statistics.affiliate_id AND affiliate_reports.created_at >= '$dateFrom' AND affiliate_reports.created_at <= '$dateTo') as leads"),
                DB::RAW("(SELECT SUM(revenue) FROM affiliate_reports WHERE affiliate_reports.affiliate_id = revenue_tracker_cake_statistics.affiliate_id AND affiliate_reports.revenue_tracker_id = revenue_tracker_cake_statistics.revenue_tracker_id AND affiliate_reports.created_at >= '$dateFrom' AND affiliate_reports.created_at <= '$dateTo') as revenue")
            );
        } else {
            $query->select(DB::RAW('COUNT(*)'));
        }

        return $query;
    }

    //For Affiliate Excel Report
    public function scopeRevTrackerSubIDBreakdown($query, $params, $countOnly = false)
    {
        $columns = [ //for ordering
            's1',
            's2',
            's3',
            's4',
            's5',
            'clicks',
            'payout',
            'leads',
            'revenue',
            '(revenue - payout)',
            '((revenue - payout) / revenue)',
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

        $query->whereExists(function ($exists) {
            $exists->select(DB::raw(1))
                ->from('affiliate_revenue_trackers')
                ->whereRaw('affiliate_revenue_trackers.affiliate_id = revenue_tracker_cake_statistics.affiliate_id AND affiliate_revenue_trackers.revenue_tracker_id = revenue_tracker_cake_statistics.revenue_tracker_id');
        });

        $query->where('revenue_tracker_cake_statistics.affiliate_id', $params['affiliate_id']);
        $query->where('revenue_tracker_cake_statistics.revenue_tracker_id', $params['revenue_tracker_id']);

        $query->whereRaw('revenue_tracker_cake_statistics.created_at >= ? AND revenue_tracker_cake_statistics.created_at <= ?', [$dateFrom, $dateTo]);

        if (isset($params['affiliate_type'])) {
            //Internal = 1, H&P = 2 or Both = 0
            if ($params['affiliate_type'] == 1) {
                $query->where('revenue_tracker_cake_statistics.type', '=', 1);
            } elseif ($params['affiliate_type'] == 2) {
                $query->where('revenue_tracker_cake_statistics.type', '=', 2);
            }
        }

        if (isset($params['search']['value']) && $params['search']['value'] != '') {
            $query->where('s1', 'LIKE', '%'.$params['search']['value'].'%')
                ->orWhere('s2', 'LIKE', '%'.$params['search']['value'].'%')
                ->orWhere('s3', 'LIKE', '%'.$params['search']['value'].'%')
                ->orWhere('s4', 'LIKE', '%'.$params['search']['value'].'%')
                ->orWhere('s5', 'LIKE', '%'.$params['search']['value'].'%');
        }

        $query->groupBy('revenue_tracker_cake_statistics.s1', 'revenue_tracker_cake_statistics.s2', 'revenue_tracker_cake_statistics.s3', 'revenue_tracker_cake_statistics.s4', 'revenue_tracker_cake_statistics.s5');

        if (! $countOnly) {
            $query->select(
                'revenue_tracker_cake_statistics.affiliate_id',
                'revenue_tracker_cake_statistics.revenue_tracker_id',
                'revenue_tracker_cake_statistics.s1',
                'revenue_tracker_cake_statistics.s2',
                'revenue_tracker_cake_statistics.s3',
                'revenue_tracker_cake_statistics.s4',
                'revenue_tracker_cake_statistics.s5',
                DB::RAW('SUM(revenue_tracker_cake_statistics.clicks) AS clicks'),
                DB::RAW('SUM(revenue_tracker_cake_statistics.payout) AS payout'),
                DB::RAW("(SELECT SUM(lead_count) FROM affiliate_reports WHERE affiliate_reports.affiliate_id = revenue_tracker_cake_statistics.affiliate_id AND affiliate_reports.revenue_tracker_id = revenue_tracker_cake_statistics.revenue_tracker_id AND affiliate_reports.s1 = revenue_tracker_cake_statistics.s1 AND affiliate_reports.s2 = revenue_tracker_cake_statistics.s2 AND affiliate_reports.s3 = revenue_tracker_cake_statistics.s3 AND affiliate_reports.s4 = revenue_tracker_cake_statistics.s4 AND affiliate_reports.s5 = revenue_tracker_cake_statistics.s5 AND affiliate_reports.created_at >= '$dateFrom' AND affiliate_reports.created_at <= '$dateTo') as leads"),
                DB::RAW("(SELECT SUM(revenue) FROM affiliate_reports WHERE affiliate_reports.affiliate_id = revenue_tracker_cake_statistics.affiliate_id AND affiliate_reports.revenue_tracker_id = revenue_tracker_cake_statistics.revenue_tracker_id AND affiliate_reports.s1 = revenue_tracker_cake_statistics.s1 AND affiliate_reports.s2 = revenue_tracker_cake_statistics.s2 AND affiliate_reports.s3 = revenue_tracker_cake_statistics.s3 AND affiliate_reports.s4 = revenue_tracker_cake_statistics.s4 AND affiliate_reports.s5 = revenue_tracker_cake_statistics.s5 AND affiliate_reports.created_at >= '$dateFrom' AND affiliate_reports.created_at <= '$dateTo') as revenue")
            );

            if (isset($params['order'])) {
                $order_col = $columns[$params['order'][0]['column']];
                $order_dir = $params['order'][0]['dir'];
                $query->orderBy(DB::RAW($order_col), $order_dir);
            }
        }

        return $query;
    }

    public function scopeRevTrackerSubIDClicksPayout($query, $params, $countOnly = false)
    {
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

        $query->where('revenue_tracker_cake_statistics.affiliate_id', $params['affiliate_id']);
        $query->where('revenue_tracker_cake_statistics.revenue_tracker_id', $params['revenue_tracker_id']);

        $query->whereRaw('revenue_tracker_cake_statistics.created_at >= ? AND revenue_tracker_cake_statistics.created_at <= ?', [$dateFrom, $dateTo]);

        if (isset($params['search']['value']) && $params['search']['value'] != '') {
            $query->where('s1', 'LIKE', '%'.$params['search']['value'].'%')
                ->orWhere('s2', 'LIKE', '%'.$params['search']['value'].'%')
                ->orWhere('s3', 'LIKE', '%'.$params['search']['value'].'%')
                ->orWhere('s4', 'LIKE', '%'.$params['search']['value'].'%')
                ->orWhere('s5', 'LIKE', '%'.$params['search']['value'].'%');
        }

        if (! isset($params['sib_s1']) || (isset($params['sib_s1']) && $params['sib_s1'] == 'true')) {
            $query->groupBy('revenue_tracker_cake_statistics.s1');
        }
        if (! isset($params['sib_s2']) || (isset($params['sib_s2']) && $params['sib_s2'] == 'true')) {
            $query->groupBy('revenue_tracker_cake_statistics.s2');
        }
        if (! isset($params['sib_s3']) || (isset($params['sib_s3']) && $params['sib_s3'] == 'true')) {
            $query->groupBy('revenue_tracker_cake_statistics.s3');
        }
        if (! isset($params['sib_s4']) || (isset($params['sib_s4']) && $params['sib_s4'] == 'true')) {
            $query->groupBy('revenue_tracker_cake_statistics.s4');
        }

        if (! $countOnly) {
            $query->select(
                'revenue_tracker_cake_statistics.affiliate_id',
                'revenue_tracker_cake_statistics.revenue_tracker_id',
                'revenue_tracker_cake_statistics.s1',
                'revenue_tracker_cake_statistics.s2',
                'revenue_tracker_cake_statistics.s3',
                'revenue_tracker_cake_statistics.s4',
                'revenue_tracker_cake_statistics.s5',
                DB::RAW('SUM(clicks) AS clicks'),
                DB::RAW('SUM(payout) AS payout')
            );
        }

        return $query;
    }
}
