<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class CreativeReport extends Model
{
    protected $connection;

    protected $table = 'creative_reports';

    public $timestamps = false;

    protected $fillable = [
        'path_id',
        'campaign_id',
        'creative_id',
        'views',
        'lead_count',
        'revenue',
        'date',
        'affiliate_id',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') != 'reports') {
            $this->connection = 'secondary';
        }
    }

    public function setCreativeIdAttribute($value)
    {
        $this->attributes['creative_id'] = $value ?: null;
    }

    public function setPathIdAttribute($value)
    {
        $this->attributes['path_id'] = $value ?: null;
    }

    public function scopeGetReport($query, $params)
    {

        if (isset($params['campaign_id']) && $params['campaign_id'] !== '') {
            $query->where('campaign_id', '=', $params['campaign_id']);
        }

        if (isset($params['campaign_ids']) && count($params['campaign_ids']) > 0) {
            $query->whereIn('campaign_id', $params['campaign_ids']);
        }

        if (isset($params['creative_id']) && $params['creative_id'] !== '') {
            $query->where('creative_id', '=', $params['creative_id']);
        }

        if (isset($params['path_id']) && $params['path_id'] !== '') {
            $query->where('path_id', '=', $params['path_id']);
        }

        if (isset($params['affiliate_id']) && $params['affiliate_id'] !== '') {
            $query->where('affiliate_id', '=', $params['affiliate_id']);
        }

        if ((isset($params['lead_date_from']) && $params['lead_date_from'] !== '') &&
             (isset($params['lead_date_to']) && $params['lead_date_to'] !== '')) {
            $query->whereRaw('date >= ? and date <= ?',
                [
                    $params['lead_date_from'],
                    $params['lead_date_to'],
                ]);
        } elseif ((isset($params['lead_date_from']) && $params['lead_date_from'] !== '') &&
                (! isset($params['lead_date_to']) || $params['lead_date_to'] === '')) {
            $query->whereRaw('date = ?', [$params['lead_date_from']]);
        } elseif ((! isset($params['lead_date_from']) || $params['lead_date_from'] === '') &&
                (isset($params['lead_date_to']) && $params['lead_date_to'] !== '')) {
            $query->whereRaw('date = ?', [$params['lead_date_to']]);
        } else {
            $query->whereRaw('date = ?', [$params['lead_date_today']]);
        }

        // if($params['lead_date_from'] != '' && $params['lead_date_to'] != ''
        // 	&& $params['lead_date_from'] != $params['lead_date_to']) {
        // 	// $query->groupBy('path_id','affiliate_id','campaign_id','creative_id');
        // 	// $query->select(DB::RAW("campaign_id, path_id, creative_id, SUM(lead_count) as lead_count, SUM(views) as views, SUM(revenue) as revenue, affiliate_id"));
        //     $query->groupBy('affiliate_id','campaign_id','creative_id');
        //     $query->select(DB::RAW("campaign_id, creative_id, SUM(lead_count) as lead_count, SUM(views) as views, SUM(revenue) as revenue, affiliate_id"));
        // }

        $query->groupBy('affiliate_id', 'campaign_id', 'creative_id');
        $query->select(DB::RAW('campaign_id, creative_id, SUM(lead_count) as lead_count, SUM(views) as views, SUM(revenue) as revenue, affiliate_id'));

        // order by create date
        // $query->orderBy('date','desc');

        return $query;
    }
}
