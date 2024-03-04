<?php

namespace App;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Log;

class ClicksVsRegistrationStatistics extends Model
{
    protected $connection;

    protected $table = 'clicks_vs_registration_statistics';

    public $timestamps = false;

    protected $fillable = [
        'affiliate_id',
        'revenue_tracker_id',
        'registration_count',
        'clicks',
        'percentage',
        's1',
        's2',
        's3',
        's4',
        's5',
        'created_at',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') != 'reports') {
            $this->connection = 'secondary';
        }
    }

    public function scopeClicksRegistrationStats($query, $columns, $params)
    {
        $dateFrom = $params['date_from'];
        $dateTo = $params['date_to'];

        $query->select(
            // 'affiliates.company',
            'clicks_vs_registration_statistics.affiliate_id',
            'clicks_vs_registration_statistics.revenue_tracker_id',
            'clicks_vs_registration_statistics.created_at',
            's1',
            's2',
            's3',
            's4',
            's5',
            DB::raw('SUM(clicks_vs_registration_statistics.registration_count) AS registration_count'),
            DB::raw('SUM(clicks_vs_registration_statistics.clicks) AS clicks'),
            DB::raw('SUM(clicks_vs_registration_statistics.percentage) / COUNT(clicks_vs_registration_statistics.id) AS percentage')
        );
        //->join('affiliates', 'clicks_vs_registration_statistics.affiliate_id', '=', 'affiliates.id');

        if (isset($params['affiliate_id']) && ! empty($params['affiliate_id'])) {
            Log::info($params['affiliate_id']);

            if (is_array($params['affiliate_id'])) {
                $affiliateIDs = $params['affiliate_id'];
                $query->where(function ($subQuery) use ($affiliateIDs) {
                    $subQuery->whereIn('clicks_vs_registration_statistics.revenue_tracker_id', $affiliateIDs);
                    $subQuery->orWhereIn('clicks_vs_registration_statistics.affiliate_id', $affiliateIDs);
                });
            } else {
                $affiliateID = $params['affiliate_id'];
                $query->where(function ($subQuery) use ($affiliateID) {
                    $subQuery->where('clicks_vs_registration_statistics.revenue_tracker_id', '=', $affiliateID);
                    $subQuery->orWhere('clicks_vs_registration_statistics.affiliate_id', '=', $affiliateID);
                });
            }

            /*
            $affiliateID = $params['affiliate_id'];
            $query->where(function($subQuery) use ($affiliateID)
            {
                $subQuery->where('clicks_vs_registration_statistics.revenue_tracker_id', '=', $affiliateID);
                $subQuery->orWhere('clicks_vs_registration_statistics.affiliate_id', '=', $affiliateID);
            });
            */
        }

        if (isset($params['s1']) && $params['s1'] !== '') {
            $query->where('s1', '=', $params['s1']);
        }

        if (isset($params['s2']) && $params['s2'] !== '') {
            $query->where('s2', '=', $params['s2']);
        }

        if (isset($params['s3']) && $params['s3'] !== '') {
            $query->where('s3', '=', $params['s3']);
        }

        if (isset($params['s4']) && $params['s4'] !== '') {
            $query->where('s4', '=', $params['s4']);
        }

        if (isset($params['s5']) && $params['s5'] !== '') {
            $query->where('s5', '=', $params['s5']);
        }

        if ((isset($dateFrom) && isset($dateTo)) && (! empty($dateFrom) && ! empty($dateTo))) {
            $query->where(function ($subQuery) use ($dateFrom, $dateTo) {
                $subQuery->where('clicks_vs_registration_statistics.created_at', '>=', $dateFrom);
                $subQuery->where('clicks_vs_registration_statistics.created_at', '<=', $dateTo);
            });
        } elseif ((! isset($dateFrom) && ! isset($dateTo) || (empty($dateFrom) && empty($dateTo)))) {
            $dateFrom = Carbon::now()->toDateString();
            $dateTo = $dateFrom;

            $query->where(function ($subQuery) use ($dateFrom, $dateTo) {
                $subQuery->where('clicks_vs_registration_statistics.created_at', '>=', $dateFrom);
                $subQuery->where('clicks_vs_registration_statistics.created_at', '<=', $dateTo);
            });
        }

        /*
        if(isset($params['search']['value']) && $params['search']['value'] != '')
        {
            $query->where('affiliates.company', 'LIKE', '%' . $params['search']['value'] . '%');

            //Search
            if(is_numeric($params['search']['value']))
            {
                $query->orWhere('clicks_vs_registration_statistics.affiliate_id', '=', $params['search']['value']);
                $query->orWhere('clicks_vs_registration_statistics.revenue_tracker_id', '=', $params['search']['value']);
            }
        }
        */

        // if(isset($params['search']['value']) && $params['search']['value'] != '')
        // {
        //     //Search
        //     if(is_numeric($params['search']['value']))
        //     {
        //         $paramValue = $params['search']['value'];

        //         $query->where(function ($subQuery) use ($paramValue){
        //             $subQuery->where('clicks_vs_registration_statistics.affiliate_id', '=', $paramValue);
        //             $subQuery->orWhere('clicks_vs_registration_statistics.revenue_tracker_id', '=', $paramValue);
        //         });
        //     }
        //     else
        //     {
        //         $query->where('affiliates.company', 'LIKE', '%' . $params['search']['value'] . '%');
        //     }
        // }

        //$query->groupBy('clicks_vs_registration_statistics.affiliate_id', 'clicks_vs_registration_statistics.revenue_tracker_id');
        if (isset($params['group_by'])) {
            if ($params['group_by'] == 'created_at') {
                $query->groupBy('clicks_vs_registration_statistics.created_at', 'clicks_vs_registration_statistics.revenue_tracker_id');
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
                // $query->groupBy('clicks_vs_registration_statistics.created_at', 'clicks_vs_registration_statistics.revenue_tracker_id', 's1', 's2', 's3', 's4', 's5');
            } elseif ($params['group_by'] == 'revenue_tracker_id') {
                $query->groupBy('clicks_vs_registration_statistics.revenue_tracker_id');
            } elseif ($params['group_by'] == 'custom') {
                $query->groupBy('clicks_vs_registration_statistics.created_at', 'clicks_vs_registration_statistics.revenue_tracker_id');
            } elseif ($params['group_by'] == 'per_sub_id') {
                // $query->groupBy('clicks_vs_registration_statistics.revenue_tracker_id', 's1', 's2', 's3', 's4', 's5');
                $query->groupBy('clicks_vs_registration_statistics.revenue_tracker_id');
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
        }

        // if (isset($params['order']))
        // {
        //     $order_col = $columns[$params['order'][0]['column']];
        //     $order_dir = $params['order'][0]['dir'];
        //     $query->orderBy($order_col, $order_dir);
        // }
        // else
        // {
        //     $query->orderBy('clicks_vs_registration_statistics.revenue_tracker_id', 'asc');
        //     $query->orderBy('clicks_vs_registration_statistics.created_at', 'asc');
        // }

        // Override the default ordering during download
        if (isset($params['is_download']) && $params['is_download']) {
            // $query->groupBy('clicks_vs_registration_statistics.created_at', 'clicks_vs_registration_statistics.revenue_tracker_id');
            // $query->orderBy('clicks_vs_registration_statistics.created_at', 'clicks_vs_registration_statistics.revenue_tracker_id');
            $order_col = '';
            $order_dir = 'asc';

            if (isset($params['order_column'])) {
                $order_col = $columns[$params['order_column']];
            }

            if (isset($params['order_dir']) && ! empty($params['order_dir'])) {
                $order_dir = $params['order_dir'];
            }

            if ($order_col != '') {
                // this means there is no column ordering specified
                $query->orderBy($columns[0], 'desc');
                $query->orderBy($order_col, $order_dir);
            }
        }

        return $query;
    }
}
