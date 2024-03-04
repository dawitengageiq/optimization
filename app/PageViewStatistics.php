<?php

namespace App;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;

class PageViewStatistics extends Model
{
    protected $connection;

    protected $table = 'page_view_statistics';

    public $timestamps = false;

    protected $fillable = [
        'affiliate_id',
        'revenue_tracker_id',
        'lp',
        'rp',
        'to1',
        'to2',
        'mo1',
        'mo2',
        'mo3',
        'mo4',
        'lfc1',
        'lfc2',
        'tbr1',
        'pd',
        'tbr2',
        'iff',
        'rex',
        'cpawall',
        'exitpage',
        's1',
        's2',
        's3',
        's4',
        's5',
        'created_at',
        'ads',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') != 'reports') {
            $this->connection = 'secondary';
        }
    }

    public function scopeGetStats($query, $columns, $params)
    {
        $dateFrom = $params['date_from'];
        $dateTo = $params['date_to'];

        $query->select(
            'page_view_statistics.created_at',
            'page_view_statistics.affiliate_id',
            'page_view_statistics.revenue_tracker_id',
            'page_view_statistics.s1',
            'page_view_statistics.s2',
            'page_view_statistics.s3',
            'page_view_statistics.s4',
            'page_view_statistics.s5',
            DB::raw('SUM(page_view_statistics.lp) AS lp'),
            DB::raw('SUM(page_view_statistics.rp) AS rp'),
            DB::raw('SUM(page_view_statistics.to1) AS to1'),
            DB::raw('SUM(page_view_statistics.to2) AS to2'),
            DB::raw('SUM(page_view_statistics.mo1) AS mo1'),
            DB::raw('SUM(page_view_statistics.mo2) AS mo2'),
            DB::raw('SUM(page_view_statistics.mo3) AS mo3'),
            DB::raw('SUM(page_view_statistics.mo4) AS mo4'),
            DB::raw('SUM(page_view_statistics.lfc1) AS lfc1'),
            DB::raw('SUM(page_view_statistics.lfc2) AS lfc2'),
            DB::raw('SUM(page_view_statistics.tbr1) AS tbr1'),
            DB::raw('SUM(page_view_statistics.pd) AS pd'),
            DB::raw('SUM(page_view_statistics.tbr2) AS tbr2'),
            DB::raw('SUM(page_view_statistics.iff) AS iff'),
            DB::raw('SUM(page_view_statistics.rex) AS rex'),
            DB::raw('SUM(page_view_statistics.cpawall) AS cpawall'),
            DB::raw('SUM(page_view_statistics.exitpage) AS exitpage'),
            DB::raw('SUM(page_view_statistics.ads) AS ads')
        );

        if (isset($params['affiliate_id']) && ! empty($params['affiliate_id'])) {
            if (is_array($params['affiliate_id'])) {
                $affiliateIDs = $params['affiliate_id'];
                $query->where(function ($subQuery) use ($affiliateIDs) {
                    $subQuery->whereIn('page_view_statistics.revenue_tracker_id', $affiliateIDs);
                    $subQuery->orWhereIn('page_view_statistics.affiliate_id', $affiliateIDs);
                });
            } else {
                $affiliateID = $params['affiliate_id'];
                $query->where(function ($subQuery) use ($affiliateID) {
                    $subQuery->where('page_view_statistics.revenue_tracker_id', '=', $affiliateID);
                    $subQuery->orWhere('page_view_statistics.affiliate_id', '=', $affiliateID);
                });
            }
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
                $subQuery->where('page_view_statistics.created_at', '>=', $dateFrom);
                $subQuery->where('page_view_statistics.created_at', '<=', $dateTo);
            });
        } elseif ((! isset($dateFrom) && ! isset($dateTo) || (empty($dateFrom) && empty($dateTo)))) {
            $dateFrom = Carbon::now()->toDateString();
            $dateTo = $dateFrom;

            $query->where(function ($subQuery) use ($dateFrom, $dateTo) {
                $subQuery->where('page_view_statistics.created_at', '>=', $dateFrom);
                $subQuery->where('page_view_statistics.created_at', '<=', $dateTo);
            });
        }

        if (isset($params['search']['value']) && $params['search']['value'] != '') {
            //Search
            if (is_numeric($params['search']['value'])) {
                $paramValue = $params['search']['value'];

                $query->where(function ($subQuery) use ($paramValue) {
                    $subQuery->where('page_view_statistics.affiliate_id', '=', $paramValue);
                    $subQuery->orWhere('page_view_statistics.revenue_tracker_id', '=', $paramValue);
                });
            } else {
                // $query->where('affiliates.company', 'LIKE', '%' . $params['search']['value'] . '%');
            }
        }

        //$query->groupBy('page_view_statistics.affiliate_id', 'page_view_statistics.revenue_tracker_id');
        if (isset($params['group_by'])) {
            if ($params['group_by'] == 'created_at') {
                $query->groupBy('page_view_statistics.created_at', 'page_view_statistics.revenue_tracker_id');
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

            } elseif ($params['group_by'] == 'revenue_tracker_id') {
                $query->groupBy('page_view_statistics.revenue_tracker_id');
            } elseif ($params['group_by'] == 'custom') {
                $query->groupBy('page_view_statistics.created_at', 'page_view_statistics.revenue_tracker_id');
            }
        }

        if (isset($params['order'])) {
            $order_col = $columns[$params['order'][0]['column']];
            $order_dir = $params['order'][0]['dir'];
            $query->orderBy(DB::RAW($order_col), $order_dir);
        } else {
            $query->orderBy('page_view_statistics.revenue_tracker_id', 'asc');
            $query->orderBy('page_view_statistics.created_at', 'asc');
        }

        // Override the default ordering during download
        if (isset($params['is_download']) && $params['is_download']) {
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

    public function scopePageOptInRateStats($query, $returnCountOnly, $params)
    {
        if ($returnCountOnly) {
            $query->selectRaw('COUNT(*) AS total_count');

            if ($params['date_to'] != $params['date_from']) {
                $query->groupBy('revenue_tracker_id');
                //$query->groupBy('s1','s2','s3','s4','s5');
            }

            if ($params['group_by'] == 'day') {
                $query->groupBy('page_view_statistics.created_at');

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
        } else {
            $query->selectRaw('page_view_statistics.affiliate_id,page_view_statistics.s1, page_view_statistics.s2, page_view_statistics.s3, page_view_statistics.s4, page_view_statistics.s5, affiliates.company, page_view_statistics.revenue_tracker_id, SUM(lp) as lp, SUM(rp) as rp, SUM(to1) as to1, SUM(to2) as to2, SUM(mo1) as mo1, SUM(mo2) as mo2, SUM(mo3) as mo3, SUM(mo4) as mo4, SUM(lfc1) as lfc1, SUM(lfc2) as lfc2, SUM(tbr1) as tbr1, SUM(pd) as pd, SUM(tbr2) as tbr2, SUM(iff) as iff, SUM(rex) as rex, SUM(cpawall) as cpawall, SUM(exitpage) as exitpage, SUM(ads) as ads, page_view_statistics.created_at');

            $query->groupBy('revenue_tracker_id');

            if ($params['group_by'] == 'day') {
                // $query->groupBy('s1','s2','s3','s4','s5');
                $query->groupBy('page_view_statistics.created_at');
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

        $query->whereRaw('page_view_statistics.created_at BETWEEN "'.$params['date_from'].'" AND "'.$params['date_to'].'"');

        if ($params['affiliate_id'] != '') {
            if (is_array($params['affiliate_id'])) {
                $affs = implode(',', $params['affiliate_id']);
                $query->whereRaw('(affiliate_id IN ('.$affs.') OR revenue_tracker_id IN ('.$affs.'))');
            } else {
                $query->whereRaw('(affiliate_id = '.$params['affiliate_id'].' OR revenue_tracker_id = '.$params['affiliate_id'].')');
            }
        }

        if (! $returnCountOnly) {
            $query->leftJoin('affiliates', 'affiliates.id', '=', 'page_view_statistics.affiliate_id');

            if (isset($params['order'])) {
                $query->orderBy($params['order_col'], $params['order'][0]['dir']);
            }
            $query->orderBy('page_view_statistics.revenue_tracker_id', 'ASC');
            $query->orderBy('page_view_statistics.created_at', 'ASC');

            if (isset($params['start']) && $params['start'] != null) {
                $query->skip($params['start']);
            }

            if (isset($params['length']) && $params['length'] != null) {
                $query->take($params['length']);
            }
        }

        return $query;
    }
}
