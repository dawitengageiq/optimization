<?php

namespace App;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;

class HandPAffiliateReport extends Model
{
    protected $connection;

    protected $table = 'handp_affiliate_reports';

    public $timestamps = false;

    protected $fillable = [
        'affiliate_id',
        'campaign_id',
        's1',
        's2',
        's3',
        's4',
        's5',
        'lead_count',
        'received',
        'payout',
        'created_at',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') != 'reports') {
            $this->connection = 'secondary';
        }
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
            case 'week_to_date':
                $date['from'] = Carbon::now()->startOfWeek()->toDateString();
                $date['to'] = Carbon::now()->toDateString();
                break;
            case 'month_to_date':
                $date['from'] = Carbon::now()->startOfMonth()->toDateString();
                $date['to'] = Carbon::now()->toDateString();
                break;
            case 'year_to_date':
                $date['from'] = Carbon::now()->startOfYear()->toDateString();
                $date['to'] = Carbon::now()->toDateString();
                break;
            default:
                $date['from'] = Carbon::now()->toDateString();
                $date['to'] = Carbon::now()->toDateString();
                break;
        }

        return $date;
    }

    public function scopeHandPAffiliateRevenueStats($query, $params)
    {
        $columns = [ //for ordering
            'company',
            'leads',
            'payout',
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

        $query->select(
            'affiliate_id',
            DB::raw('SUM(lead_count) AS leads'),
            DB::raw('SUM(payout) AS payout'),
            DB::raw('SUM(received) AS revenue'),
            DB::raw('(SUM(received) - SUM(payout)) AS we_get'),
            DB::raw('(SUM(received) - SUM(payout))/SUM(received) AS margin'))
            ->whereRaw('handp_affiliate_reports.created_at >= ? AND handp_affiliate_reports.created_at <= ?', [$dateFrom, $dateTo]);

        // if (isset($params['search']['value']) && $params['search']['value'] != '')
        // {
        //     $query->where('affiliates.company', 'LIKE', '%' . $params['search']['value'] . '%');
        // }

        $query->groupBy('handp_affiliate_reports.affiliate_id');

        // if (isset($params['order'])) {
        //     $order_col = $columns[$params['order'][0]['column']];
        //     $order_dir = $params['order'][0]['dir'];
        //     $query->orderBy($order_col, $order_dir);
        // }

        return $query;
    }

    public function scopeHandPAffiliateSubIDRevenueStats($query, $params)
    {
        $columns = [ //for ordering
            'affiliate_id',
            's1',
            's2',
            's3',
            's4',
            's5',
            'leads',
            'payout',
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

        $query->select('affiliate_id',
            's1',
            's2',
            's3',
            's4',
            's5',
            DB::raw('SUM(lead_count) AS leads'),
            DB::raw('SUM(payout) AS payout'),
            DB::raw('SUM(received) AS revenue'),
            DB::raw('(SUM(received) - SUM(payout)) AS we_get'),
            DB::raw('((SUM(received) - SUM(payout))/SUM(received) * 100) AS margin'))
                // ->join('affiliates','affiliates.id','=','handp_affiliate_reports.affiliate_id')
            ->whereRaw('handp_affiliate_reports.created_at >= ? AND handp_affiliate_reports.created_at <= ?', [$dateFrom, $dateTo]);

        if (isset($params['search']['value']) && $params['search']['value'] != '') {
            $search = $params['search']['value'];
            $query->where(function ($q) {
                $q->where('s1', 'LIKE', '%'.$params['search']['value'].'%')
                    ->orWhere('s2', 'LIKE', '%'.$params['search']['value'].'%')
                    ->orWhere('s3', 'LIKE', '%'.$params['search']['value'].'%')
                    ->orWhere('s4', 'LIKE', '%'.$params['search']['value'].'%')
                    ->orWhere('s5', 'LIKE', '%'.$params['search']['value'].'%');
            });
        }

        if (isset($params['affiliate_id'])) {
            $query->where('affiliate_id', '=', $params['affiliate_id']);
        }

        $query->groupBy('handp_affiliate_reports.s1')
            ->groupBy('handp_affiliate_reports.s2')
            ->groupBy('handp_affiliate_reports.s3')
            ->groupBy('handp_affiliate_reports.s4')
            ->groupBy('handp_affiliate_reports.s5');

        if (isset($params['order'])) {
            $order_col = $columns[$params['order'][0]['column']];
            $order_dir = $params['order'][0]['dir'];
            $query->orderBy($order_col, $order_dir);
        }

        return $query;
    }

    public function scopeHandPSubIDCampaignRevenueStats($query, $params)
    {
        $columns = [ //for ordering
            'campaign',
            'leads',
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

        $dateFrom = $date['from'];
        $dateTo = $date['to'];

        $query->select(
            'campaign_id',
            DB::raw('SUM(lead_count) AS leads'),
            DB::raw('SUM(received) AS revenue'),
            DB::raw('SUM(payout) AS payout'))
            ->where(function ($subQuery) use ($dateFrom, $dateTo) {
                $subQuery->whereRaw('handp_affiliate_reports.created_at >= ? AND handp_affiliate_reports.created_at <= ?', [$dateFrom, $dateTo]);
            });

        // if(isset($params['search']['value']) && $params['search']['value'] != '')
        // {
        //     $query->where('campaigns.name', 'LIKE', '%' . $params['search']['value'] . '%');
        // }

        if (isset($params['affiliate_id'])) {
            $query->where('affiliate_id', '=', $params['affiliate_id']);
        }

        /*
        if(isset($params['s1']))
        {
            if(empty($params['s1']))
            {
                $query->where(function($subQuery){
                    $subQuery->whereNull('s1');
                    $subQuery->orWhere('s1','=','');
                });
            }
            else
            {
                $query->where('s1','=',$params['s1']);
            }
        }
        else
        {
            $query->where(function($subQuery){
                $subQuery->whereNull('s1');
                $subQuery->orWhere('s1','=','');
            });
        }
        */

        if (isset($params['s1']) && $params['s1'] != '') {
            if (empty($params['s1'])) {
                $query->where(function ($subQuery) {
                    $subQuery->whereNull('s1');
                    $subQuery->orWhere('s1', '=', '');
                });
            } else {
                $query->where('s1', '=', $params['s1']);
            }
        }
        // else
        // {
        //     $query->where(function($subQuery){
        //         $subQuery->whereNull('s1');
        //         $subQuery->orWhere('s1','=','');
        //     });
        // }

        $query->groupBy('handp_affiliate_reports.affiliate_id', 'handp_affiliate_reports.s1', 'handp_affiliate_reports.campaign_id');

        if (isset($params['order'])) {
            $order_col = $columns[$params['order'][0]['column']];
            $order_dir = $params['order'][0]['dir'];
            $query->orderBy($order_col, $order_dir);
        }

        return $query;
    }

    public function scopeHandPAffiliateStats($query, $params)
    {
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

        $query->select(
            //'affiliates.company',
            'affiliate_id',
            'campaign_id',
            //'campaigns.name AS campaign',
            's1',
            DB::raw('SUM(lead_count) AS leads'),
            DB::raw('SUM(payout) AS payout'),
            DB::raw('SUM(received) AS revenue'))
                        // ->join('campaigns','campaigns.id','=','handp_affiliate_reports.campaign_id')
                        // ->join('affiliates','affiliates.id','=','handp_affiliate_reports.affiliate_id')
            ->where(function ($subQuery) use ($dateFrom, $dateTo) {
                $subQuery->whereRaw('handp_affiliate_reports.created_at >= ? AND handp_affiliate_reports.created_at <= ?', [$dateFrom, $dateTo]);
            });

        if (isset($params['affiliate_id'])) {
            $query->where('affiliate_id', '=', $params['affiliate_id']);
        }

        $query->groupBy('handp_affiliate_reports.affiliate_id', 'handp_affiliate_reports.s1', 'handp_affiliate_reports.campaign_id');
        $query->orderBy('handp_affiliate_reports.affiliate_id', 'asc');

        return $query;
    }

    public function scopeWebsiteRevenueStats($query, $params)
    {
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

        $query->select(
            'campaign_id',
            'affiliate_id',
            DB::raw('s1 AS website'),
            DB::raw('SUM(lead_count) AS leads'),
            DB::raw('SUM(received) AS revenue'),
            DB::raw('SUM(payout) AS payout'))
            ->where(function ($subQuery) use ($dateFrom, $dateTo) {
                $subQuery->whereRaw('handp_affiliate_reports.created_at >= ? AND handp_affiliate_reports.created_at <= ?', [$dateFrom, $dateTo]);
            }
            );

        if (isset($params['affiliate_id'])) {
            $query->where('affiliate_id', '=', $params['affiliate_id']);
        }

        if (isset($params['campaign_id'])) {
            $query->where('campaign_id', '=', $params['campaign_id']);
        }

        $query->groupBy('handp_affiliate_reports.s1');

        if (isset($params['order'])) {
            $order_col = $columns[$params['order'][0]['column']];
            $order_dir = $params['order'][0]['dir'];
            $query->orderBy($order_col, $order_dir);
        }

        return $query;
    }
}
