<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ClickLogTraceInfo extends Model
{
    protected $connection;

    protected $table = 'click_log_trace_infos';

    protected $fillable = [
        'click_date',
        'click_id',
        'email',
        'affiliate_id',
        'revenue_tracker_id',
        'ip',
        'is_dbprepoped',
        'reg_count',
        'first_entry_rev_id',
        'first_entry_timestamp',
        'last_entry_rev_id',
        'last_entry_timestamp',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') != 'reports') {
            $this->connection = 'secondary';
        }
    }

    public function scopeSearch($query, $params)
    {
        $dateFrom = $params['date_from'];
        $dateTo = $params['date_to'];

        if (isset($params['affiliate_id']) && ! empty($params['affiliate_id'])) {
            if (is_array($params['affiliate_id'])) {
                $affiliateIDs = $params['affiliate_id'];
                $query->where(function ($subQuery) use ($affiliateIDs) {
                    $subQuery->whereIn('revenue_tracker_id', $affiliateIDs);
                    $subQuery->orWhereIn('affiliate_id', $affiliateIDs);
                });
            } else {
                $affiliateID = $params['affiliate_id'];
                $query->where(function ($subQuery) use ($affiliateID) {
                    $subQuery->where('revenue_tracker_id', '=', $affiliateID);
                    $subQuery->orWhere('affiliate_id', '=', $affiliateID);
                });
            }
        }

        if ((isset($dateFrom) && isset($dateTo)) && (! empty($dateFrom) && ! empty($dateTo))) {
            $query->where(function ($subQuery) use ($dateFrom, $dateTo) {
                $subQuery->where('click_date', '>=', $dateFrom.' 00:00:00');
                $subQuery->where('click_date', '<=', $dateTo.' 23:59:59');
            });
        } elseif ((! isset($dateFrom) && ! isset($dateTo) || (empty($dateFrom) && empty($dateTo)))) {
            $dateFrom = Carbon::now()->toDateString();
            $dateTo = $dateFrom;

            $query->where(function ($subQuery) use ($dateFrom, $dateTo) {
                $subQuery->where('click_date', '>=', $dateFrom.' 00:00:00');
                $subQuery->where('click_date', '<=', $dateTo.' 23:59:59');
            });
        }

        if (isset($params['hide_duplicate_email']) && $params['hide_duplicate_email'] == '1') {
            $query->groupBy('email');
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
