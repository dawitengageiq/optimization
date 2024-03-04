<?php

namespace App;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;

class LeadArchive extends Model
{
    protected $table = 'leads_archive';

    protected $fillable = [
        'id',
        'campaign_id',
        'affiliate_id',
        's1',
        's2',
        's3',
        's4',
        's5',
        'lead_status',
        'lead_email',
        'received',
        'payout',
        'retry_count',
        'last_retry_date',
        'created_at',
        'updated_at',
        'creative_id',
        'path_id',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
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

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function leadMessage()
    {
        return $this->hasOne(LeadMessageArchive::class, 'id');
    }

    public function leadDataADV()
    {
        return $this->hasOne(LeadDataAdvArchive::class, 'id');
    }

    public function leadDataCSV()
    {
        return $this->hasOne(LeadDataCsvArchive::class, 'id');
    }

    public function leadSentResult()
    {
        return $this->hasOne(LeadSentResultArchive::class, 'id');
    }

    public function scopeSearchLeads($query, $params)
    {
        if (isset($params['containing_data']) && $params['containing_data'] !== '') {
            $query->join('lead_messages_archive', 'leads_archive.id', '=', 'lead_messages_archive.id')
                ->join('lead_sent_results_archive', 'leads_archive.id', '=', 'lead_sent_results_archive.id')
                ->join('lead_data_csvs_archive', 'leads_archive.id', '=', 'lead_data_csvs_archive.id')
                ->join('lead_data_advs_archive', 'leads_archive.id', '=', 'lead_data_advs_archive.id')
                ->whereRaw("lead_sent_results_archive.value LIKE '%".$params['containing_data']."%' OR lead_messages_archive.value LIKE '%".$params['containing_data']."%' OR lead_data_csvs_archive.value LIKE '%".$params['containing_data']."%' OR lead_data_advs_archive.value LIKE '%".$params['containing_data']."%'");
        }

        if (isset($params['affiliate_type']) && $params['affiliate_type'] != '') {
            $query->join('affiliates', 'leads_archive.affiliate_id', '=', 'affiliates.id');
            $query->where('affiliates.type', $params['affiliate_type']);
        }

        if (isset($params['lead_id']) && $params['lead_id'] !== '') {
            $query->where('leads_archive.id', '=', $params['lead_id']);
        }

        if (isset($params['campaign_id']) && $params['campaign_id'] !== '') {
            $query->where('campaign_id', '=', $params['campaign_id']);
        }

        if (isset($params['campaign_ids']) && count($params['campaign_ids']) > 0) {
            $query->whereIn('campaign_id', $params['campaign_ids']);
        }

        if (isset($params['affiliate_id']) && $params['affiliate_id'] !== '') {
            $query->where('affiliate_id', '=', $params['affiliate_id']);
        }

        if (isset($params['lead_email']) && $params['lead_email'] !== '') {
            $query->where('lead_email', '=', $params['lead_email']);
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

        if (isset($params['lead_status']) && $params['lead_status'] !== '') {
            $query->where('lead_status', '=', $params['lead_status']);
        }

        if ((isset($params['lead_date_from']) && $params['lead_date_from'] !== '') &&
            (isset($params['lead_date_to']) && $params['lead_date_to'] !== '')) {
            $query->whereRaw('leads_archive.created_at >= ? and leads_archive.created_at <= ?',
                [
                    $params['lead_date_from'].' 00:00:00',
                    $params['lead_date_to'].' 23:59:59',
                ]);
        } elseif ((isset($params['lead_date_from']) && $params['lead_date_from'] !== '') &&
            (! isset($params['lead_date_to']) || $params['lead_date_to'] == '')) {
            $query->whereRaw('leads_archive.created_at >= ? and leads_archive.created_at <= ?',
                [
                    $params['lead_date_from'].' 00:00:00',
                    $params['lead_date_from'].' 23:59:59',
                ]);
        } elseif ((! isset($params['lead_date_from']) || $params['lead_date_from'] == '') &&
            (isset($params['lead_date_to']) && $params['lead_date_to'] !== '')) {
            $query->whereRaw('leads_archive.created_at >= ? and leads_archive.created_at <= ?',
                [
                    $params['lead_date_to'].' 00:00:00',
                    $params['lead_date_to'].' 23:59:59',
                ]);
        }

        if (isset($params['duplicate']) && $params['duplicate'] !== '') {
            if ($params['duplicate'] == 1) {
                $query->having(DB::raw('COUNT(*)'), '=', 1);
            } else {
                $query->having(DB::raw('COUNT(*)'), '>', 1);
            }
        }

        //order by create date
        $query->orderBy('leads_archive.created_at', 'desc');

        if (isset($params['limit_rows']) && $params['limit_rows'] !== '') {
            $query->take($params['limit_rows']);
        }

        return $query;
    }

    public function scopeGetSearchLeads($query, $params, $start, $length, $order_col, $order_dir)
    {
        if (isset($params['lead_id']) && $params['lead_id'] !== '') {
            $query->where('id', '=', $params['lead_id']);
        }

        if (isset($params['campaign_id']) && $params['campaign_id'] !== '') {
            $query->where('campaign_id', '=', $params['campaign_id']);
        }

        if (isset($params['affiliate_id']) && $params['affiliate_id'] !== '') {
            $query->where('affiliate_id', '=', $params['affiliate_id']);
        }

        if (isset($params['lead_email']) && $params['lead_email'] !== '') {
            $query->where('lead_email', '=', $params['lead_email']);
        }

        if (isset($params['lead_status']) && $params['lead_status'] !== '') {
            $query->where('lead_status', '=', $params['lead_status']);
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

        if ((isset($params['lead_date_from']) && $params['lead_date_from'] !== '') &&
            (isset($params['lead_date_to']) && $params['lead_date_to'] !== '')) {
            $query->whereRaw('date(leads_archive.created_at) >= date(?) and date(leads_archive.created_at) <= date(?)',
                [
                    $params['lead_date_from'],
                    $params['lead_date_to'],
                ]);
        } elseif ((isset($params['lead_date_from']) && $params['lead_date_from'] !== '') &&
                (! isset($params['lead_date_to']) || $params['lead_date_to'] === '')) {
            $query->whereRaw('date(leads_archive.created_at) = date(?)', [$params['lead_date_from']]);
        } elseif ((! isset($params['lead_date_from']) || $params['lead_date_from'] === '') &&
                (isset($params['lead_date_to']) && $params['lead_date_to'] !== '')) {
            $query->whereRaw('date(leads_archive.created_at) = date(?)', [$params['lead_date_to']]);
        }

        if (isset($params['duplicate']) && $params['duplicate'] !== '') {
            if ($params['duplicate'] == 1) {
                $query->having(DB::raw('COUNT(*)'), '=', 1);
            } else {
                $query->having(DB::raw('COUNT(*)'), '>', 1);
            }
        }

        if ($order_col != null && $order_dir != null) {
            if ($order_col > -1) {
                $query->orderBy($order_col, $order_dir);
            }
        }

        if ($start != null) {
            $query->skip($start);
        }

        if ($length != null) {
            $query->take($length);
        }

        return $query;
    }

    public function scopeGetRevenueStats($query, $params)
    {
        $columns = [ //for ordering
            'lead_date',
            'campaign_name',
            'campaign_type',
            'creative_id',
            'affiliate_id',
            'affiliate_id',
            'campaign_rate',
            'advertiser_name',
            's1',
            's2',
            's3',
            's4',
            's5',
            'lead_status',
            'lead_count',
            'cost',
            'revenue',
            'profit',
        ];

        $query->selectRaw('s1,s2,s3,s4,s5,leads_archive.created_at,campaign_id,creative_id,affiliate_id,lead_status,COUNT(leads_archive.id) as lead_count, campaigns.name as campaign_name, campaigns.rate as campaign_rate, 
            CASE WHEN lead_status = 1 THEN SUM(received) - SUM(payout) ELSE 0 END as profit,
            CASE WHEN lead_status = 1 THEN SUM(payout) ELSE 0 END as cost,
            CASE WHEN lead_status = 1 THEN SUM(received) ELSE 0 END as revenue,
            advertisers.company as advertiser_name,campaigns.campaign_type');

        $query->join('campaigns', 'leads_archive.campaign_id', '=', 'campaigns.id');
        $query->join('advertisers', 'campaigns.advertiser_id', '=', 'advertisers.id');

        if (isset($params['affiliate_type']) && $params['affiliate_type'] != '') {
            $query->join('affiliates', 'leads_archive.affiliate_id', '=', 'affiliates.id');
            $query->where('affiliates.type', $params['affiliate_type']);
        }

        // if(isset($params['containing_data']) && $params['containing_data']!=='')
        // {
        //     $query->join('lead_messages_archive','leads_archive.id','=','lead_messages_archive.id')
        //         ->join('lead_sent_results_archive','leads_archive.id','=','lead_sent_results_archive.id')
        //         ->join('lead_data_csvs_archive','leads_archive.id','=','lead_data_csvs_archive.id')
        //         ->join('lead_data_advs_archive','leads_archive.id','=','lead_data_advs_archive.id')
        //         ->whereRaw("lead_sent_results_archive.value LIKE '%".$params['containing_data']."%' OR lead_messages_archive.value LIKE '%".$params['containing_data']."%' OR lead_data_csvs_archive.value LIKE '%".$params['containing_data']."%' OR lead_data_advs_archive.value LIKE '%".$params['containing_data']."%'");
        // }

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

        if (isset($params['lead_id']) && $params['lead_id'] !== '') {
            $query->where('id', '=', $params['lead_id']);
        }

        if (isset($params['campaign_id']) && $params['campaign_id'] !== '') {
            $query->where('campaign_id', '=', $params['campaign_id']);
        }

        if (isset($params['campaign_ids']) && count($params['campaign_ids']) > 0) {
            $query->whereIn('campaign_id', $params['campaign_ids']);
        }

        if (isset($params['affiliate_id']) && $params['affiliate_id'] !== '') {
            $query->where('affiliate_id', '=', $params['affiliate_id']);
        }

        if (isset($params['lead_status']) && $params['lead_status'] !== '') {
            $query->where('lead_status', '=', $params['lead_status']);
        }

        //DATE RANGE
        if ((isset($params['date_range']) && $params['date_range'] !== '')) {
            switch ($params['date_range']) {
                case 'today' :

                    $date['from'] = Carbon::now()->startOfDay();
                    $date['to'] = Carbon::now()->endOfDay();
                    break;

                case 'week' :

                    $date['from'] = Carbon::now()->startOfWeek();
                    $date['to'] = Carbon::now()->endOfWeek();
                    break;

                case 'month':

                    $date['from'] = Carbon::now()->startOfMonth();
                    $date['to'] = Carbon::now()->endOfMonth();
                    break;

                case 'year' :

                    $date['from'] = Carbon::now()->startOfYear();
                    $date['to'] = Carbon::now()->endOfYear();
                    break;

                case 'yesterday' :

                    $date['from'] = Carbon::now()->subDay()->startOfDay();
                    $date['to'] = Carbon::now()->subDay()->endOfDay();
                    break;

                case 'last_month':

                    $date['from'] = Carbon::now()->subMonth()->startOfMonth();
                    $date['to'] = Carbon::now()->subMonth()->endOfMonth();
                    break;

                default:

                    $date = null;

                    break;
            }

            $query->whereRaw('leads_archive.created_at >= ? and leads_archive.created_at <= ?',
                [
                    $date['from'],
                    $date['to'],
                ]);

        } elseif ((isset($params['lead_date_from']) && $params['lead_date_from'] !== '') &&
                (isset($params['lead_date_to']) && $params['lead_date_to'] !== '')) {
            $query->whereRaw('leads_archive.created_at >= ? and leads_archive.created_at <= ?',
                [
                    $params['lead_date_from'].' 00:00:00',
                    $params['lead_date_to'].' 23:59:59',
                ]);
        } elseif ((isset($params['lead_date_from']) && $params['lead_date_from'] !== '') &&
            (! isset($params['lead_date_to']) || $params['lead_date_to'] === '')) {
            $query->whereRaw('leads_archive.created_at >= ? and leads_archive.created_at <= ?',
                [
                    $params['lead_date_from'].' 00:00:00',
                    $params['lead_date_from'].' 23:59:59',
                ]);
        } elseif ((! isset($params['lead_date_from']) || $params['lead_date_from'] === '') &&
            (isset($params['lead_date_to']) && $params['lead_date_to'] !== '')) {
            $query->whereRaw('leads_archive.created_at >= ? and leads_archive.created_at <= ?',
                [
                    $params['lead_date_to'].' 00:00:00',
                    $params['lead_date_to'].' 23:59:59',
                ]);
        }

        if (isset($params['order'])) {
            $order_col = $columns[$params['order'][0]['column']];
            $order_dir = $params['order'][0]['dir'];
            $query->orderBy($order_col, $order_dir);
        } else {
            $query->orderBy('lead_date', 'desc');
        }

        if (isset($params['group_by']) && $params['group_by'] !== '') {
            switch ($params['group_by']) {
                case 'week':
                    $query->selectRaw('DATE_FORMAT(leads_archive.created_at,"%Y-%U") as lead_date');
                    // $query->selectRaw('CONCAT(DATE_FORMAT(created_at,"%Y"),"-", week(created_at)) as lead_date');
                    $query->groupBy(DB::raw('week(leads_archive.created_at)'));
                    break;
                case 'month':
                    $query->selectRaw('DATE_FORMAT(leads_archive.created_at,"%Y-%m") as lead_date');
                    // $query->selectRaw('month(created_at) as lead_date');
                    $query->groupBy(DB::raw('month(leads_archive.created_at)'));
                    break;
                case 'year':
                    $query->selectRaw('year(leads_archive.created_at) as lead_date');
                    $query->groupBy(DB::raw('year(leads_archive.created_at)'));
                    break;
                default:
                    $query->selectRaw('date(leads_archive.created_at) as lead_date');
                    $query->groupBy(DB::raw('date(leads_archive.created_at)'));
                    break;
            }
        } else {
            $query->selectRaw('date(leads_archive.created_at) as lead_date');
            $query->groupBy(DB::raw('date(leads_archive.created_at)'));
        }

        if (isset($params['group_by_column']) && $params['group_by_column'] !== '') {
            switch ($params['group_by_column']) {
                case 'campaign':
                    $query->groupBy('campaign_id', 'lead_status');
                    break;
                case 'affiliate':
                    $query->groupBy('affiliate_id', 'lead_status');
                    break;
                case 's1':
                    $query->groupBy('s1', 'lead_status');
                    break;
                case 's1':
                    $query->groupBy('s1', 'lead_status');
                    break;
                case 's2':
                    $query->groupBy('s2', 'lead_status');
                    break;
                case 's3':
                    $query->groupBy('s3', 'lead_status');
                    break;
                case 's4':
                    $query->groupBy('s4', 'lead_status');
                    break;
                case 's5':
                    $query->groupBy('s5', 'lead_status');
                    break;
                default:
                    $query->groupBy('campaign_id', 'creative_id', 'affiliate_id', 'lead_status');
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
                    // $query->groupBy('campaign_id','creative_id','affiliate_id','lead_status','s1','s2','s3','s4','s5');
                    break;
            }
        } else {
            // $query->groupBy('campaign_id','creative_id','affiliate_id','lead_status','s1','s2','s3','s4','s5');
            $query->groupBy('campaign_id', 'creative_id', 'affiliate_id', 'lead_status');
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

        return $query;
    }

    public function scopePageOptInRateReport($query, $fullBreakdown, $params)
    {
        $selectQry = 'leads_archive.campaign_id, leads_archive.affiliate_id as revenue_tracker_id, COUNT(*) as total, date(leads_archive.created_at) as date'; //affiliate_revenue_trackers.affiliate_id, affiliates.company as name,

        if ($fullBreakdown) {
            $selectQry .= ', lead_status';
        }
        $query->selectRaw($selectQry);

        $query->whereRaw('date(leads_archive.created_at) BETWEEN "'.$params['date_from'].'" AND "'.$params['date_to'].'"');

        $query->whereIn('lead_status', [1, 2]);

        if ($params['affiliate_id'] != '') {
            $query->join('affiliate_revenue_trackers', 'affiliate_revenue_trackers.revenue_tracker_id', '=', 'leads_archive.affiliate_id');

            $query->whereRaw('leads_archive.affiliate_id = '.$params['affiliate_id'].' OR affiliate_revenue_trackers.affiliate_id = '.$params['affiliate_id']);
        }

        if (isset($params['benchmarks'])) {
            $query->whereIn('leads_archive.campaign_id', $params['benchmarks']);
        }

        if ($fullBreakdown) {
            $query->groupBy('leads_archive.campaign_id', 'leads_archive.affiliate_id', 'lead_status');
        } else {
            $query->groupBy('leads_archive.campaign_id', 'leads_archive.affiliate_id');
        }

        if ($params['group_by'] == 'day') {
            $query->groupBy('date');
        }

        return $query;
    }
}
