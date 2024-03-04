<?php

namespace App;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;

class AffiliateReport extends Model
{
    protected $connection;

    protected $table = 'affiliate_reports';

    public $timestamps = false;

    protected $fillable = [
        'affiliate_id',
        'revenue_tracker_id',
        'campaign_id',
        'lead_count',
        'revenue',
        'created_at',
        'reject_count',
        'failed_count',
        's1',
        's2',
        's3',
        's4',
        's5',
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

    public static function getSnapShotPeriodRange($value)
    {
        $date = [];

        switch ($value) {
            case 'today' :
                $date['from'] = Carbon::now()->toDateString();
                $date['to'] = Carbon::now()->toDateString();
                break;
            case 'yesterday' :
                $date['from'] = Carbon::yesterday()->toDateString();
                $date['to'] = Carbon::yesterday()->toDateString();
                break;
            case 'last_week' :
                $date['from'] = Carbon::now()->subWeek()->startOfWeek()->toDateString();
                $date['to'] = Carbon::now()->subWeek()->endOfWeek()->toDateString();
                break;
            case 'last_month':
                $date['from'] = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                $date['to'] = Carbon::now()->subMonth()->endOfMonth()->toDateString();
                break;
            default:
                $date['from'] = Carbon::now()->toDateString();
                $date['to'] = Carbon::now()->toDateString();
                break;
        }

        return $date;
    }

    public function scopeGetStats($query, $params)
    {

        $columns = [ //for ordering
            'affiliate_name',
            'clicks',
            'payout',
            'leads',
            'revenue',
            'we_get',
            'margin',
        ];

        if (isset($params['affiliate_id']) && $params['affiliate_id'] != '') {
            //Stats for website/revenue tracker
            $columns[0] = 'revenue_tracker_name';
        }

        if ($params['affiliate_type'] != 0) {
            $query->where('type', $params['affiliate_type']); //Internal = 1, H&P = 2 or Both = 0
        }

        if ($params['search']['value'] != '') {
            //Search
            if (isset($params['affiliate_id']) && $params['affiliate_id'] != '') {
                //website/revenue tracker
                $query->where('revenue_tracker_name', 'like', '%'.$params['search']['value'].'%');
            } else {
                //affiliate
                $query->where('affiliate_name', 'like', '%'.$params['search']['value'].'%');
            }
        }

        if (isset($params['affiliate_id']) && $params['affiliate_id'] != '') { //Stats for website/revenue tracker
            $query->where('affiliate_id', $params['affiliate_id']);
        }

        if (isset($params['revenue_tracker_id']) && $params['revenue_tracker_id'] != '') { //Stats for website/revenue tracker
            $query->where('revenue_tracker_id', $params['revenue_tracker_id']);
        }

        if (isset($params['period'])) {
            if ($params['period'] == 'none' && (! empty($params['start_date']) && ! empty($params['end_date']))) {
                //use the date range
                $date['from'] = $params['start_date'];
                $date['to'] = $params['end_date'];
            } else {
                $date = self::getSnapShotPeriodRange($params['period']);
            }
        } else {
            $date = self::getSnapShotPeriodRange('none');

            if (! empty($params['start_date']) && ! empty($params['end_date'])) {
                $date['from'] = $params['start_date'];
                $date['to'] = $params['end_date'];
            }
        }

        $query->whereRaw('created_at >= ? and created_at <= ?',
            [
                $date['from'],
                $date['to'],
            ]);

        if (isset($params['order'])) {
            $order_col = $columns[$params['order'][0]['column']];
            $order_dir = $params['order'][0]['dir'];
            $query->orderBy($order_col, $order_dir);
        } else {
            $query->orderBy('affiliate_name', 'asc');
        }
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
                $date = self::getSnapShotPeriodRange($params['period']);
            }
        } else {
            $date = self::getSnapShotPeriodRange('none');

            if (! empty($params['start_date']) && ! empty($params['end_date'])) {
                $date['from'] = $params['start_date'];
                $date['to'] = $params['end_date'];
            }
        }

        //temporary
        //$date['from'] = Carbon::parse('2016-12-13');
        //$date['to'] =  Carbon::parse('2016-12-13');

        $dateFrom = $date['from'];
        $dateTo = $date['to'];

        $query->select('affiliate_reports.*');

        if (isset($params['affiliate_id']) && $params['affiliate_id'] != '') {
            //Stats for website/revenue tracker
            $query->where('affiliate_reports.affiliate_id', $params['affiliate_id']);
        }

        if (isset($params['revenue_tracker_id'])) {
            $query->where('affiliate_reports.revenue_tracker_id', '=', $params['revenue_tracker_id']);
        }

        if (! isset($params['sib_s1']) || (isset($params['sib_s1']) && $params['sib_s1'] == 'true')) {
            $query->where('affiliate_reports.s1', '=', $params['s1']);
            //$query->groupBy('affiliate_reports.s1');
        }
        if (! isset($params['sib_s2']) || (isset($params['sib_s2']) && $params['sib_s2'] == 'true')) {
            $query->where('affiliate_reports.s2', '=', $params['s2']);
            //$query->groupBy('affiliate_reports.s2');
        }
        if (! isset($params['sib_s3']) || (isset($params['sib_s3']) && $params['sib_s3'] == 'true')) {
            $query->where('affiliate_reports.s3', '=', $params['s3']);
            //$query->groupBy('affiliate_reports.s3');
        }
        if (! isset($params['sib_s4']) || (isset($params['sib_s4']) && $params['sib_s4'] == 'true')) {
            $query->where('affiliate_reports.s4', '=', $params['s4']);
            //$query->groupBy('affiliate_reports.s4');
        }

        $query->groupBy('campaign_id');

        // if(isset($params['s1']))
        // {
        //     $query->where('affiliate_reports.s1','=',$params['s1']);
        // }

        // if(isset($params['s2']))
        // {
        //     $query->where('affiliate_reports.s2','=',$params['s2']);
        // }

        // if(isset($params['s3']))
        // {
        //     $query->where('affiliate_reports.s3','=',$params['s3']);
        // }

        // if(isset($params['s4']))
        // {
        //     $query->where('affiliate_reports.s4','=',$params['s4']);
        // }

        // if(isset($params['s5']))
        // {
        //     $query->where('affiliate_reports.s5','=',$params['s5']);
        // }

        $query->whereRaw('affiliate_reports.created_at >= ? and affiliate_reports.created_at <= ?',
            [
                $dateFrom,
                $dateTo,
            ]);

        $query->selectRaw('affiliate_reports.id, affiliate_reports.affiliate_id, affiliate_reports.revenue_tracker_id, affiliate_reports.campaign_id, SUM(lead_count) as lead_count, SUM(revenue) as revenue');

        return $query;
    }

    public function scopeAllRevenueTracker($query, $params)
    {
        $query->select('affiliate_reports.revenue_tracker_id', 'revenue_tracker_cake_statistics.type', 'campaigns.name');

        $query->join('revenue_tracker_cake_statistics', function ($join) {
            $join->on('affiliate_reports.affiliate_id', '=', 'revenue_tracker_cake_statistics.affiliate_id')
                ->on('affiliate_reports.revenue_tracker_id', '=', 'revenue_tracker_cake_statistics.revenue_tracker_id');
        });

        $query->join('campaigns', 'affiliate_reports.campaign_id', '=', 'campaigns.id');

        $query->whereExists(function ($exists) {
            $exists->select(DB::raw(1))
                ->from('affiliate_revenue_trackers')
                ->whereRaw('affiliate_revenue_trackers.affiliate_id = revenue_tracker_cake_statistics.affiliate_id AND affiliate_revenue_trackers.revenue_tracker_id = revenue_tracker_cake_statistics.revenue_tracker_id');
        });

        if (isset($params['type'])) {
            $query->where('revenue_tracker_cake_statistics.type', '=', $params['affiliate_type']);
        }

        $date = [];

        if (isset($params['period'])) {
            if ($params['period'] == 'none' && (! empty($params['start_date']) && ! empty($params['end_date']))) {
                //use the date range
                $date['from'] = $params['start_date'];
                $date['to'] = $params['end_date'];
            } else {
                $date = self::getSnapShotPeriodRange($params['period']);
            }
        } else {
            $date = self::getSnapShotPeriodRange('none');

            if (! empty($params['start_date']) && ! empty($params['end_date'])) {
                $date['from'] = $params['start_date'];
                $date['to'] = $params['end_date'];
            }
        }

        $dateFrom = $date['from'];
        $dateTo = $date['to'];

        $query->where(function ($groupQuery) use ($dateFrom, $dateTo) {
            $groupQuery->whereRaw('affiliate_reports.created_at >= ? AND affiliate_reports.created_at <= ?', [$dateFrom, $dateTo]);
        });

        $query->groupBy('affiliate_reports.revenue_tracker_id')
            ->orderBy('affiliate_reports.revenue_tracker_id', 'asc');

        return $query;
    }

    public function scopeGetRevenueTrackerAffiliateReports($query, $params)
    {
        if (isset($params['revenue_tracker_id'])) {
            $query->where('revenue_tracker_id', '=', $params['revenue_tracker_id']);
        }

        $date = [];

        /*
        if(isset($params['period']))
        {
            $date = self::getSnapShotPeriodRange($params['period']);
        }
        else
        {
            $date = self::getSnapShotPeriodRange('none');
        }
       */

        if (isset($params['period'])) {
            if ($params['period'] == 'none' && (! empty($params['start_date']) && ! empty($params['end_date']))) {
                //use the date range
                $date['from'] = $params['start_date'];
                $date['to'] = $params['end_date'];
            } else {
                $date = self::getSnapShotPeriodRange($params['period']);
            }
        } else {
            $date = self::getSnapShotPeriodRange('none');

            if (! empty($params['start_date']) && ! empty($params['end_date'])) {
                $date['from'] = $params['start_date'];
                $date['to'] = $params['end_date'];
            }
        }

        //temporary
        //$date['from'] = Carbon::parse('2016-12-13');
        //$date['to'] = Carbon::parse('2016-12-13');

        $dateFrom = $date['from'];
        $dateTo = $date['to'];

        $query->where(function ($groupQuery) use ($dateFrom, $dateTo) {
            $groupQuery->whereRaw('created_at >= ? AND created_at <= ?', [$dateFrom, $dateTo]);
        });

        $query->orderBy('campaign_id', 'asc');

        return $query;
    }

    public function scopeGetReportsByRevenueTrackerAndDate($query, $affiliateID, $revenueTrackerID, $date)
    {
        return $query->where('affiliate_id', '=', $affiliateID)
            ->where('revenue_tracker_id', '=', $revenueTrackerID)
            ->where('created_at', '=', $date);
    }

    public function scopePageOptInRateStats($query, $external_ids, $params)
    {

        if (is_array($external_ids)) {
            $query->selectRaw('campaign_id, revenue_tracker_id, SUM(revenue) as revenue, SUM(lead_count) as lead_count, SUM(reject_count) as reject_count, SUM(failed_count) as failed_count, created_at, s1, s2, s3, s4, s5');
            $query->whereIn('campaign_id', $external_ids);
            $query->groupBy('campaign_id', 'revenue_tracker_id');
        } else {
            $query->where('campaign_id', $external_ids);

            if ($params['date_from'] != $params['date_to']) {
                $query->selectRaw('revenue_tracker_id, SUM(revenue) as revenue, SUM(revenue) as revenue, SUM(lead_count) as lead_count, SUM(reject_count) as reject_count, SUM(failed_count) as failed_count, created_at, s1, s2, s3, s4, s5');
                $query->groupBy('revenue_tracker_id');
            } else {
                $query->selectRaw('revenue_tracker_id, revenue, lead_count, reject_count, failed_count, created_at, s1, s2, s3, s4, s5');
            }
        }

        if ($params['group_by'] == 'day') {
            // $query->groupBy('s1','s2','s3','s4','s5');
            $query->groupBy('created_at');
            if (! isset($params['sib_s1']) || (isset($params['sib_s1']) && $params['sib_s1'] == 'true')) {
                $query->groupBy('s1');
            }
            if (! isset($params['sib_s2']) || (isset($params['sib_s2']) && $params['sib_s2'] == 'true')) {
                $query->groupBy('s2');
            }
            if (! isset($params['sib_s3']) || (isset($params['sib_s3']) && $params['sib_s3'] == 'true')) {
                $query->groupBy('s3');
            }
            if (! isset($params['sib_s4']) || (isset($params['sib_s4']) && $params['sib_s4'] == 'true')) {
                $query->groupBy('s4');
            }
        }

        $query->whereRaw('created_at BETWEEN "'.$params['date_from'].'" AND "'.$params['date_to'].'"');

        if ($params['affiliate_id'] != '') {
            if (is_array($params['affiliate_id'])) {
                $affs = implode(',', $params['affiliate_id']);
                $query->whereRaw('(affiliate_id IN ('.$affs.') OR revenue_tracker_id IN ('.$affs.'))');
            } else {
                $query->whereRaw('(affiliate_id = '.$params['affiliate_id'].' OR revenue_tracker_id = '.$params['affiliate_id'].')');
            }
            // $query->whereRaw('affiliate_id = '.$params['affiliate_id'].' OR revenue_tracker_id = '. $params['affiliate_id']);
        }

        return $query;
    }

    public function scopeGetCampaignsByAffiliateIDs($query, $affiliateIDs, $params)
    {
        $query->select('campaign_id');

        if (count($affiliateIDs) > 0) {
            $query->whereIn('revenue_tracker_id', $affiliateIDs);
        }

        $date = [];

        if (isset($params['predefined_date_range'])) {
            if ($params['predefined_date_range'] == 'none' && (! empty($params['start_date']) && ! empty($params['end_date']))) {
                //use the date range
                $date['from'] = $params['start_date'];
                $date['to'] = $params['end_date'];
            } else {
                $date = self::getSnapShotPeriodRange($params['predefined_date_range']);
            }
        } else {
            $date = self::getSnapShotPeriodRange('none');

            if (! empty($params['start_date']) && ! empty($params['end_date'])) {
                $date['from'] = $params['start_date'];
                $date['to'] = $params['end_date'];
            }
        }

        if (isset($params['predefined_date_range'])) {
            $dateFrom = $date['from'];
            $dateTo = $date['to'];

            $query->where(function ($groupQuery) use ($dateFrom, $dateTo) {
                $groupQuery->whereRaw('created_at >= ? AND created_at <= ?', [$dateFrom, $dateTo]);
            });
        }

        $query->groupBy('campaign_id');

        return $query;
    }

    public function scopeGetRevTrackerPerCampaignTotalRevenue($query, $params)
    {
        $query->select(['affiliate_id', 'revenue_tracker_id', 'campaign_id', 's1', 's2', 's3', 's4', 's5', DB::RAW('SUM(revenue) as revenue'), DB::RAW('SUM(lead_count) as lead_count'), 'campaigns.name as campaign']);

        $query->leftJoin('campaigns', 'affiliate_reports.campaign_id', '=', 'campaigns.id');
        $date = [];

        if (isset($params['period'])) {
            if ($params['period'] == 'none' && (! empty($params['start_date']) && ! empty($params['end_date']))) {
                //use the date range
                $date['from'] = $params['start_date'];
                $date['to'] = $params['end_date'];
            } else {
                $date = self::getSnapShotPeriodRange($params['period']);
            }
        } else {
            $date = self::getSnapShotPeriodRange('none');

            if (! empty($params['start_date']) && ! empty($params['end_date'])) {
                $date['from'] = $params['start_date'];
                $date['to'] = $params['end_date'];
            }
        }

        $dateFrom = $date['from'];
        $dateTo = $date['to'];

        $query->where(function ($groupQuery) use ($dateFrom, $dateTo) {
            $groupQuery->whereRaw('affiliate_reports.created_at BETWEEN  ? AND ?', [$dateFrom, $dateTo]);
        });

        $query->groupBy(['affiliate_id', 'revenue_tracker_id', 'campaign_id', 's1', 's2', 's3', 's4', 's5']);

        $query->orderBy('revenue_tracker_id', 'asc')
            ->orderBy('s1', 'asc')
            ->orderBy('s2', 'asc')
            ->orderBy('s3', 'asc')
            ->orderBy('s4', 'asc')
            ->orderBy('s5', 'asc')
            ->orderBy('campaigns.name', 'asc');

        return $query;
    }

    public function scopeGetRevTrackerPerCampaignTotalRevenueNoSubIDBreakdown($query, $params)
    {
        $query->select(['affiliate_id', 'revenue_tracker_id', 'campaign_id', DB::RAW('SUM(revenue) as revenue'), DB::RAW('SUM(lead_count) as lead_count')]);

        $date = [];

        if (isset($params['period'])) {
            if ($params['period'] == 'none' && (! empty($params['start_date']) && ! empty($params['end_date']))) {
                //use the date range
                $date['from'] = $params['start_date'];
                $date['to'] = $params['end_date'];
            } else {
                $date = self::getSnapShotPeriodRange($params['period']);
            }
        } else {
            $date = self::getSnapShotPeriodRange('none');

            if (! empty($params['start_date']) && ! empty($params['end_date'])) {
                $date['from'] = $params['start_date'];
                $date['to'] = $params['end_date'];
            }
        }

        $dateFrom = $date['from'];
        $dateTo = $date['to'];

        $query->where(function ($groupQuery) use ($dateFrom, $dateTo) {
            $groupQuery->whereRaw('affiliate_reports.created_at BETWEEN ? AND ?', [$dateFrom, $dateTo]);
        });

        $query->groupBy(['affiliate_id', 'revenue_tracker_id', 'campaign_id']);

        $query->orderBy('revenue_tracker_id', 'asc');
        //->orderBy('campaigns.name','asc');

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
                $date = self::getSnapShotPeriodRange($params['period']);
            }
        } else {
            $date = self::getSnapShotPeriodRange('none');

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
                ->whereRaw('affiliate_revenue_trackers.affiliate_id = affiliate_reports.affiliate_id AND affiliate_revenue_trackers.revenue_tracker_id = affiliate_reports.revenue_tracker_id');
        });

        $query->where('affiliate_reports.affiliate_id', $params['affiliate_id']);
        $query->where('affiliate_reports.revenue_tracker_id', $params['revenue_tracker_id']);

        $query->whereRaw('affiliate_reports.created_at >= ? AND affiliate_reports.created_at <= ?', [$dateFrom, $dateTo]);

        if (isset($params['search']['value']) && $params['search']['value'] != '') {
            $query->where('s1', 'LIKE', '%'.$params['search']['value'].'%')
                ->orWhere('s2', 'LIKE', '%'.$params['search']['value'].'%')
                ->orWhere('s3', 'LIKE', '%'.$params['search']['value'].'%')
                ->orWhere('s4', 'LIKE', '%'.$params['search']['value'].'%')
                ->orWhere('s5', 'LIKE', '%'.$params['search']['value'].'%');
        }

        // $query->groupBy('affiliate_reports.s1','affiliate_reports.s2','affiliate_reports.s3','affiliate_reports.s4','affiliate_reports.s5');

        $clicksQry = 'SELECT SUM(clicks) FROM revenue_tracker_cake_statistics WHERE revenue_tracker_cake_statistics.affiliate_id = affiliate_reports.affiliate_id AND revenue_tracker_cake_statistics.revenue_tracker_id = affiliate_reports.revenue_tracker_id';
        $payoutQry = 'SELECT SUM(payout) FROM revenue_tracker_cake_statistics WHERE revenue_tracker_cake_statistics.affiliate_id = affiliate_reports.affiliate_id AND revenue_tracker_cake_statistics.revenue_tracker_id = affiliate_reports.revenue_tracker_id';

        if (! isset($params['sib_s1']) || (isset($params['sib_s1']) && $params['sib_s1'] == 'true')) {
            $query->groupBy('affiliate_reports.s1');

            $clicksQry .= ' AND revenue_tracker_cake_statistics.s1 = affiliate_reports.s1';
            $payoutQry .= ' AND revenue_tracker_cake_statistics.s1 = affiliate_reports.s1';
        }
        if (! isset($params['sib_s2']) || (isset($params['sib_s2']) && $params['sib_s2'] == 'true')) {
            $query->groupBy('affiliate_reports.s2');

            $clicksQry .= ' AND revenue_tracker_cake_statistics.s2 = affiliate_reports.s2';
            $payoutQry .= ' AND revenue_tracker_cake_statistics.s2 = affiliate_reports.s2';
        }
        if (! isset($params['sib_s3']) || (isset($params['sib_s3']) && $params['sib_s3'] == 'true')) {
            $query->groupBy('affiliate_reports.s3');

            $clicksQry .= ' AND revenue_tracker_cake_statistics.s3 = affiliate_reports.s3';
            $payoutQry .= ' AND revenue_tracker_cake_statistics.s3 = affiliate_reports.s3';
        }
        if (! isset($params['sib_s4']) || (isset($params['sib_s4']) && $params['sib_s4'] == 'true')) {
            $query->groupBy('affiliate_reports.s4');

            $clicksQry .= ' AND revenue_tracker_cake_statistics.s4 = affiliate_reports.s4';
            $payoutQry .= ' AND revenue_tracker_cake_statistics.s4 = affiliate_reports.s4';
        }

        $clicksQry .= " AND revenue_tracker_cake_statistics.created_at >= '$dateFrom' AND revenue_tracker_cake_statistics.created_at <= '$dateTo'";
        $payoutQry .= " AND revenue_tracker_cake_statistics.created_at >= '$dateFrom' AND revenue_tracker_cake_statistics.created_at <= '$dateTo'";
        if (! $countOnly) {
            $query->select(
                'affiliate_reports.affiliate_id',
                'affiliate_reports.revenue_tracker_id',
                'affiliate_reports.s1',
                'affiliate_reports.s2',
                'affiliate_reports.s3',
                'affiliate_reports.s4',
                'affiliate_reports.s5',
                DB::RAW('SUM(lead_count) AS leads'),
                DB::RAW('SUM(revenue) AS revenue'),
                DB::RAW("($clicksQry) as clicks"),
                DB::RAW("($payoutQry) as payout")
            );
        }

        return $query;
    }

    public function scopeRevTrackerSubIDLeadsRevenue($query, $params, $countOnly = false)
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
                $date = self::getSnapShotPeriodRange($params['period']);
            }
        } else {
            $date = self::getSnapShotPeriodRange('none');

            if (! empty($params['start_date']) && ! empty($params['end_date'])) {
                $date['from'] = $params['start_date'];
                $date['to'] = $params['end_date'];
            }
        }

        $dateFrom = $date['from'];
        $dateTo = $date['to'];

        // $query->whereExists(function ($exists)
        // {
        //     $exists->select(DB::raw(1))
        //             ->from('affiliate_revenue_trackers')
        //             ->whereRaw('affiliate_revenue_trackers.affiliate_id = affiliate_reports.affiliate_id AND affiliate_revenue_trackers.revenue_tracker_id = affiliate_reports.revenue_tracker_id');
        // });

        $query->where('affiliate_reports.affiliate_id', $params['affiliate_id']);
        $query->where('affiliate_reports.revenue_tracker_id', $params['revenue_tracker_id']);

        $query->whereRaw('affiliate_reports.created_at >= ? AND affiliate_reports.created_at <= ?', [$dateFrom, $dateTo]);

        if (isset($params['search']['value']) && $params['search']['value'] != '') {
            $query->where('s1', 'LIKE', '%'.$params['search']['value'].'%')
                ->orWhere('s2', 'LIKE', '%'.$params['search']['value'].'%')
                ->orWhere('s3', 'LIKE', '%'.$params['search']['value'].'%')
                ->orWhere('s4', 'LIKE', '%'.$params['search']['value'].'%')
                ->orWhere('s5', 'LIKE', '%'.$params['search']['value'].'%');
        }
        if (! isset($params['sib_s1']) || (isset($params['sib_s1']) && $params['sib_s1'] == 'true')) {
            $query->groupBy('affiliate_reports.s1');
        }
        if (! isset($params['sib_s2']) || (isset($params['sib_s2']) && $params['sib_s2'] == 'true')) {
            $query->groupBy('affiliate_reports.s2');
        }
        if (! isset($params['sib_s3']) || (isset($params['sib_s3']) && $params['sib_s3'] == 'true')) {
            $query->groupBy('affiliate_reports.s3');
        }
        if (! isset($params['sib_s4']) || (isset($params['sib_s4']) && $params['sib_s4'] == 'true')) {
            $query->groupBy('affiliate_reports.s4');
        }

        if (! $countOnly) {
            $query->select(
                'affiliate_reports.affiliate_id',
                'affiliate_reports.revenue_tracker_id',
                'affiliate_reports.s1',
                'affiliate_reports.s2',
                'affiliate_reports.s3',
                'affiliate_reports.s4',
                'affiliate_reports.s5',
                DB::RAW('SUM(lead_count) AS leads'),
                DB::RAW('SUM(revenue) AS revenue')
            );
        }

        return $query;
    }
}
