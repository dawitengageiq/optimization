<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class ConsolidatedGraph extends Model
{
    protected $connection;

    protected $table = 'consolidated_graph';
    // public $timestamps = false;

    protected $fillable = [
        'revenue_tracker_id',
        'affiliate_id',
        'survey_takers',
        'source_revenue',
        'source_revenue_per_survey_takers',
        'all_clicks',
        'source_revenue_per_all_clicks',
        'margin',
        'cpa_revenue',
        'cpa_views',
        'cpa_revenue_per_views',
        'lsp_revenue',
        'lsp_views',
        'lsp_revenue_vs_views',
        'pd_revenue',
        'pd_views',
        'pd_revenue_vs_views',
        'tb_revenue',
        'tb_views',
        'tb_revenue_vs_views',
        'iff_revenue',
        'iff_views',
        'iff_revenue_vs_views',
        'rexadz_revenue',
        'rexadz_revenue',
        'rexadz_revenue_vs_views',
        'all_inbox_revenue',
        'coreg_p1_revenue',
        'coreg_p1_views',
        'coreg_p1_revenue_vs_views',
        'coreg_p2_revenue',
        'coreg_p2_views',
        'coreg_p2_revenue_vs_views',
        'coreg_p3_revenue',
        'coreg_p3_views',
        'coreg_p3_revenue_vs_views',
        'coreg_p4_revenue',
        'coreg_p4_views',
        'coreg_p4_revenue_vs_views',
        'survey_takers_per_clicks',
        'cpa_per_survey_takers',
        'created_at',
        's1',
        's2',
        's3',
        's4',
        's5',
        'all_coreg_revenue',
        'all_coreg_views',
        'all_coreg_revenue_per_all_coreg_views',
        'all_mp_revenue',
        'all_mp_views',
        'mp_per_views',
        'adsmith_revenue',
        'adsmith_views',
        'adsmith_revenue_vs_views',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') != 'reports') {
            $this->connection = 'secondary';
        }
    }

    public function scopeGetABTesting($query, $date_from, $date_to, $params)
    {
        // \Log::info($params);
        if (isset($params['revenue_tracker_id']) && is_array($params['revenue_tracker_id'])) {
            $query->whereIn('revenue_tracker_id', $params['revenue_tracker_id']);
        }

        if (isset($params['sib_s1'])) {
            $query->groupBy('s1');
        }
        if (isset($params['sib_s2'])) {
            $query->groupBy('s2');
        }
        if (isset($params['sib_s3'])) {
            $query->groupBy('s3');
        }
        if (isset($params['sib_s4'])) {
            $query->groupBy('s4');
        }

        $query->whereBetween(DB::RAW('created_at'), [$date_from.' 00:00:00', $date_to.' 23:59:59']);
    }
}
