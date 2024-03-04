<?php

namespace App;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;

class ExternalPathAffiliateReport extends Model
{
    protected $connection;

    protected $table = 'external_path_affiliate_reports';

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
                $date['to'] = Carbon::now()->endOfWeek()->toDateString(); //Carbon::now()->toDateString();
                break;
            case 'month_to_date':
                $date['from'] = Carbon::now()->startOfMonth()->toDateString();
                $date['to'] = Carbon::now()->endOfMonth()->toDateString(); //Carbon::now()->toDateString();
                break;
            case 'year_to_date':
                $date['from'] = Carbon::now()->startOfYear()->toDateString();
                $date['to'] = Carbon::now()->endOfYear()->toDateString(); //Carbon::now()->toDateString();
                break;
            default:
                $date['from'] = Carbon::now()->toDateString();
                $date['to'] = Carbon::now()->toDateString();
                break;
        }

        return $date;
    }

    public function scopeExternalPathRevenue($query, $params)
    {

        if (isset($params['affiliate_id']) && $params['affiliate_id'] != '') { //Stats for website/revenue tracker
            $query->where('affiliate_id', $params['affiliate_id']);
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

        if (isset($params['order_col'])) {
            $query->orderBy($params['order_col'], $params['order_dir']);
        }

        $query->groupBy('created_at');

        $query->select(DB::RAW('created_at, SUM(lead_count) as count, SUM(payout) as payout, SUM(received) as received'));

    }
}
