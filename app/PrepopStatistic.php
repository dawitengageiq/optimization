<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class PrepopStatistic extends Model
{
    protected $connection;

    protected $table = 'prepop_statistics';

    public $timestamps = false;

    protected $fillable = [
        'created_at',
        'affiliate_id',
        'revenue_tracker_id',
        'total_clicks',
        'prepop_count',
        'no_prepop_count',
        'prepop_with_errors_count',
        'no_prepop_percentage',
        'prepop_with_errors_percentage',
        'profit_margin',
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

    public function scopeGetStatistics($query, $params)
    {
        $query->select(
            'affiliate_id',
            'revenue_tracker_id',
            's1',
            's2',
            's3',
            's4',
            's5',
            DB::raw('SUM(total_clicks) as total_clicks'),
            DB::raw('SUM(prepop_count) as prepop_count'),
            DB::raw('SUM(no_prepop_count) as no_prepop_count'),
            DB::raw('SUM(prepop_with_errors_count) as prepop_with_errors_count'),
            DB::raw('AVG(no_prepop_percentage) as no_prepop_percentage'),
            DB::raw('AVG(prepop_with_errors_percentage) as prepop_with_errors_percentage'),
            DB::raw('SUM(profit_margin) as profit_margin'),
            'created_at');

        if (isset($params['affiliate_id']) && ! empty($params['affiliate_id'])) {
            // $query->where('affiliate_id','=',$params['affiliate_id'])
            //     ->orWhere('revenue_tracker_id','=',$params['affiliate_id']);

            $query->whereRaw('(affiliate_id = '.$params['affiliate_id'].' OR revenue_tracker_id = '.$params['affiliate_id'].')');
        }

        if ((isset($params['date_from']) && ! empty($params['date_from'])) && (isset($params['date_to']) && ! empty($params['date_to']))) {
            $query->where('created_at', '>=', $params['date_from']);
            $query->where('created_at', '<=', $params['date_to']);
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

        if (isset($params['group_by']) && ! empty($params['group_by'])) {
            //$query->groupBy($params['group_by']);
            switch ($params['group_by']) {
                case 'affiliate_id':

                    $query->groupBy('affiliate_id');

                    break;

                case 'revenue_tracker_id':

                    $query->groupBy('affiliate_id', 'revenue_tracker_id');

                    break;

                default:
                    // $query->groupBy('affiliate_id', 'revenue_tracker_id', 'created_at', 's1', 's2', 's3', 's4', 's5');
                    $query->groupBy('affiliate_id', 'revenue_tracker_id', 'created_at');
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
                    break;
            }
        }

        if (isset($params['order_column']) && isset($params['order_direction'])) {
            $query->orderBy($params['order_column'], $params['order_direction']);
        }

        return $query;
    }
}
