<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class CampaignRevenueViewStatistic extends Model
{
    protected $connection;

    public $timestamps = false;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'campaign_revenue_view_statistics';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'revenue_tracker_id',
        'campaign_id',
        'revenue',
        'views',
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

    //    public function scopeGetChangeStats($query, $params)
    //    {
    //        $revenueQueryPart = "";
    //        $viewsQueryPart = "";
    //
    //        if(isset($params['predefined_date_range']))
    //        {
    //
    //        }
    //
    //        $query->select('campaign_id');
    //
    //        return $query;
    //    }

    public function scopeCampaignMostChanges($query, $date_range, $from, $to, $affiliates)
    {

        switch ($date_range) {
            case 'last_week' :
                $diffChecker = 'WEEK(campaign_revenue_view_statistics.created_at) as identifier';
                break;

            case 'last_month':
                $diffChecker = 'MONTH(campaign_revenue_view_statistics.created_at) as identifier';
                break;

            default: //yesterday
                $diffChecker = 'campaign_revenue_view_statistics.created_at as identifier';
                break;
        }

        $query->whereBetween('campaign_revenue_view_statistics.created_at', [$from, $to]);
        // ->leftJoin('campaigns', 'campaigns.id', '=', 'campaign_revenue_view_statistics.campaign_id')

        $query->where('views', '>', 0);

        if ($affiliates != '' && count($affiliates) > 0) {
            $query->whereIn('revenue_tracker_id', $affiliates);
        }

        $query->select(['campaign_id', DB::RAW('SUM(revenue) as revenue'), DB::RAW('SUM(views) as views'), DB::RAW('SUM(revenue) / SUM(views) as ratio'), DB::RAW($diffChecker)]);

        $query->groupBy(['campaign_id',  'identifier']);

        return $query;
    }
}
