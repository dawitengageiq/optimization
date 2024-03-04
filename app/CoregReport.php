<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CoregReport extends Model
{
    protected $connection;

    protected $table = 'coreg_reports';

    public $timestamps = false;

    protected $fillable = [
        'campaign_id',
        'affiliate_id',
        'revenue_tracker_id',
        'cost',
        'revenue_tracker_id',
        'cost',
        'revenue',
        'lf_total',
        'lf_filter_do',
        'lf_admin_do',
        'lf_nlr_do',
        'lr_total',
        'lr_rejected',
        'lr_failed',
        'lr_pending',
        'lr_cap',
        'olr_only',
        'source',
        'date',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') != 'reports') {
            $this->connection = 'secondary';
        }
    }

    public function scopeGetReport($query, $params, $start, $length, $order_col, $order_dir)
    {
        // if($order_col != null) {
        //     $query->leftJoin('campaigns','coreg_reports.campaign_id','=','campaigns.id');
        //     $query->leftJoin('affiliates','coreg_reports.affiliate_id','=','affiliates.id');
        // }

        if (isset($params['campaign_id']) && $params['campaign_id'] !== '') {
            $query->where('campaign_id', '=', $params['campaign_id']);
        }

        if (isset($params['campaign_ids']) && count($params['campaign_ids']) > 0) {
            $query->whereIn('campaign_id', $params['campaign_ids']);
        }

        if (isset($params['affiliate_id']) && $params['affiliate_id'] !== '') {
            $query->where('affiliate_id', '=', $params['affiliate_id']);
        }

        if (isset($params['revenue_tracker_id']) && $params['revenue_tracker_id'] !== '') {
            $query->where('revenue_tracker_id', '=', $params['revenue_tracker_id']);
        }

        if (isset($params['lead_date']) && $params['lead_date'] !== '') {
            $query->where('date', '=', $params['lead_date']);
        }

        if (isset($params['group_by_column']) && $params['group_by_column'] !== '') {
            $query->groupBy($params['group_by_column']);
        }

        if ($order_col != null && $order_dir != null) {
            if ($order_col > -1) {
                $query->orderBy($order_col, $order_dir);
            }
        }
        if (isset($params['excel']) && $params['excel'] == 1) {
            // $query->orderBy('revenue_tracker_id','asc');
            // $query->orderBy('campaign_name','asc');
            $query->orderBy('we_get', 'desc');
        }

        if ($start != null) {
            $query->skip($start);
        }

        if ($length != null) {
            $query->take($length);
        }

        return $query;
    }
}
