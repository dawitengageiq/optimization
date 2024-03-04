<?php

namespace App;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $connection;

    protected $table = 'leads';

    protected $fillable = [
        'campaign_id',
        'affiliate_id',
        'creative_id',
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
        'path_id',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
            $this->connection = 'secondary';
        }
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
        return $this->hasOne(LeadMessage::class, 'id');
    }

    public function leadDataADV()
    {
        return $this->hasOne(LeadDataAdv::class, 'id');
    }

    public function leadDataCSV()
    {
        return $this->hasOne(LeadDataCsv::class, 'id');
    }

    public function leadSentResult()
    {
        return $this->hasOne(LeadSentResult::class, 'id');
    }

    public function campaignCreative()
    {
        return $this->belongsTo(CampaignCreative::class);
    }

    public function campaignViewReport()
    {
        return $this->belongsTo(CampaignViewReport::class, 'campaign_id', 'campaign_id');
    }

    public function setCreativeIdAttribute($value)
    {
        $this->attributes['creative_id'] = $value ?: null;
    }

    public function setPathIdAttribute($value)
    {
        $this->attributes['path_id'] = $value ?: null;
    }

    public function scopeGetRevenuePerRevenueTrackerAndCampaign($query, $params)
    {
        $query->select(DB::raw('COUNT(*) AS leads'), DB::raw('SUM(received) AS revenue'));

        if (isset($params['affiliate_id'])) {
            $query->where('affiliate_id', '=', $params['affiliate_id']);
        }

        if (isset($params['campaign_id'])) {
            $query->where('campaign_id', '=', $params['campaign_id']);
        }

        /*
        if((isset($params['lead_date_from']) && $params['lead_date_from']!=='') && (isset($params['lead_date_to']) && $params['lead_date_to']!==''))
        {
            $query->whereRaw('DATE(created_at) >= DATE(?) and DATE(created_at) <= DATE(?)',
                [
                    $params['lead_date_from'],
                    $params['lead_date_to']
                ]);
        }
        else if((isset($params['lead_date_from']) && $params['lead_date_from']!=='') && (!isset($params['lead_date_to']) || $params['lead_date_to']===''))
        {
            $query->whereRaw('DATE(created_at) = DATE(?)',[$params['lead_date_from']]);
        }
        else if((!isset($params['lead_date_from']) || $params['lead_date_from']==='') && (isset($params['lead_date_to']) && $params['lead_date_to']!==''))
        {
            $query->whereRaw('DATE(created_at) = date(?)',[$params['lead_date_to']]);
        }
        */

        if ((isset($params['lead_date_from']) && $params['lead_date_from'] !== '') && (isset($params['lead_date_to']) && $params['lead_date_to'] !== '')) {
            $query->whereRaw('UNIX_TIMESTAMP(created_at) >= UNIX_TIMESTAMP(?) and UNIX_TIMESTAMP(created_at) <= UNIX_TIMESTAMP(?)',
                [
                    $params['lead_date_from'],
                    $params['lead_date_to'],
                ]);
        } elseif ((isset($params['lead_date_from']) && $params['lead_date_from'] !== '') && (! isset($params['lead_date_to']) || $params['lead_date_to'] === '')) {
            $query->whereRaw('UNIX_TIMESTAMP(created_at) = UNIX_TIMESTAMP(?)', [$params['lead_date_from']]);
        } elseif ((! isset($params['lead_date_from']) || $params['lead_date_from'] === '') && (isset($params['lead_date_to']) && $params['lead_date_to'] !== '')) {
            $query->whereRaw('UNIX_TIMESTAMP(created_at) = UNIX_TIMESTAMP(?)', [$params['lead_date_to']]);
        }

        return $query->groupBy('campaign_id', 'affiliate_id');
    }

    public function scopeSearchLeads($query, $params)
    {
        // if(isset($params['containing_data']) && $params['containing_data']!=='')
        // {
        //     $query->join('lead_messages','leads.id','=','lead_messages.id')
        //           ->join('lead_sent_results','leads.id','=','lead_sent_results.id')
        //           ->join('lead_data_csvs','leads.id','=','lead_data_csvs.id')
        //           ->join('lead_data_advs','leads.id','=','lead_data_advs.id')
        //           ->whereRaw("lead_sent_results.value LIKE '%".$params['containing_data']."%' OR lead_messages.value LIKE '%".$params['containing_data']."%' OR lead_data_csvs.value LIKE '%".$params['containing_data']."%' OR lead_data_advs.value LIKE '%".$params['containing_data']."%'");
        // }

        if (isset($params['affiliate_type']) && $params['affiliate_type'] != '') {
            $query->join('affiliates', 'leads.affiliate_id', '=', 'affiliates.id');
            $query->where('affiliates.type', $params['affiliate_type']);
        }

        if (isset($params['lead_id']) && $params['lead_id'] !== '') {
            $query->where('leads.id', '=', $params['lead_id']);
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
            $query->whereRaw('date(leads.created_at) >= date(?) and date(leads.created_at) <= date(?)',
                [
                    $params['lead_date_from'],
                    $params['lead_date_to'],
                ]);
        } elseif ((isset($params['lead_date_from']) && $params['lead_date_from'] !== '') &&
                (! isset($params['lead_date_to']) || $params['lead_date_to'] === '')) {
            $query->whereRaw('date(leads.created_at) = date(?)', [$params['lead_date_from']]);
        } elseif ((! isset($params['lead_date_from']) || $params['lead_date_from'] === '') &&
                (isset($params['lead_date_to']) && $params['lead_date_to'] !== '')) {
            $query->whereRaw('date(leads.created_at) = date(?)', [$params['lead_date_to']]);
        }

        if (isset($params['duplicate']) && $params['duplicate'] !== '') {
            if ($params['duplicate'] == 1) {
                $query->having(DB::raw('COUNT(*)'), '=', 1);
            } else {
                $query->having(DB::raw('COUNT(*)'), '>', 1);
            }
        }

        // order by create date
        $query->orderBy('leads.created_at', 'desc');

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
            $query->whereRaw('date(leads.created_at) >= date(?) and date(leads.created_at) <= date(?)',
                [
                    $params['lead_date_from'],
                    $params['lead_date_to'],
                ]);
        } elseif ((isset($params['lead_date_from']) && $params['lead_date_from'] !== '') &&
                (! isset($params['lead_date_to']) || $params['lead_date_to'] === '')) {
            $query->whereRaw('date(leads.created_at) = date(?)', [$params['lead_date_from']]);
        } elseif ((! isset($params['lead_date_from']) || $params['lead_date_from'] === '') &&
                (isset($params['lead_date_to']) && $params['lead_date_to'] !== '')) {
            $query->whereRaw('date(leads.created_at) = date(?)', [$params['lead_date_to']]);
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

        // $query->selectRaw('s1,s2,s3,s4,s5,leads.created_at,campaign_id,creative_id,affiliate_id,lead_status,COUNT(leads.id) as lead_count, campaigns.name as campaign_name, campaigns.rate as campaign_rate,
        //     CASE WHEN lead_status = 1 THEN SUM(received) - SUM(payout) ELSE 0 END as profit,
        //     CASE WHEN lead_status = 1 THEN SUM(payout) ELSE 0 END as cost,
        //     CASE WHEN lead_status = 1 THEN SUM(received) ELSE 0 END as revenue,
        //     advertisers.company as advertiser_name');

        $query->select('s1', 's2', 's3', 's4', 's5', 'leads.created_at', 'campaign_id', 'creative_id', 'affiliate_id', 'lead_status', DB::RAW('COUNT(leads.id) as lead_count'), 'campaigns.name as campaign_name', 'campaigns.rate as campaign_rate', 'campaigns.campaign_type', 'advertisers.company as advertiser_name');

        $query->addSelect(DB::RAW('CASE WHEN lead_status = 1 THEN SUM(received) - SUM(payout) ELSE 0 END as profit'));

        $query->addSelect(DB::RAW('CASE WHEN lead_status = 1 THEN SUM(payout) ELSE 0 END as cost'));

        $query->addSelect(DB::RAW('CASE WHEN lead_status = 1 THEN SUM(received) ELSE 0 END as revenue'));

        $query->join('campaigns', 'leads.campaign_id', '=', 'campaigns.id');
        $query->join('advertisers', 'campaigns.advertiser_id', '=', 'advertisers.id');

        if (isset($params['affiliate_type']) && $params['affiliate_type'] != '') {
            $query->join('affiliates', 'leads.affiliate_id', '=', 'affiliates.id');
            $query->where('affiliates.type', $params['affiliate_type']);
        }

        // if(isset($params['containing_data']) && $params['containing_data']!=='')
        // {
        //     $query->join('lead_messages','leads.id','=','lead_messages.id')
        //         ->join('lead_sent_results','leads.id','=','lead_sent_results.id')
        //         ->join('lead_data_csvs','leads.id','=','lead_data_csvs.id')
        //         ->join('lead_data_advs','leads.id','=','lead_data_advs.id')
        //         ->whereRaw("lead_sent_results.value LIKE '%".$params['containing_data']."%' OR lead_messages.value LIKE '%".$params['containing_data']."%' OR lead_data_csvs.value LIKE '%".$params['containing_data']."%' OR lead_data_advs.value LIKE '%".$params['containing_data']."%'");
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
                case 'yesterday' :
                    $date['from'] = Carbon::yesterday()->startOfDay();
                    $date['to'] = Carbon::yesterday()->endOfDay();
                    break;
                case 'week' :
                    $date['from'] = Carbon::now()->startOfWeek();
                    $date['to'] = Carbon::now()->endOfWeek();
                    break;
                case 'month':
                    $date['from'] = Carbon::now()->startOfMonth();
                    $date['to'] = Carbon::now()->endOfMonth();
                    break;
                case 'last_month':
                    $date['from'] = Carbon::now()->subMonth()->startOfMonth();
                    $date['to'] = Carbon::now()->subMonth()->endOfMonth();
                    break;
                case 'year' :
                    $date['from'] = Carbon::now()->startOfYear();
                    $date['to'] = Carbon::now()->endOfYear();
                    break;
                default:
                    $date = null;
                    break;
            }
            $query->whereRaw('date(leads.created_at) >= date(?) and date(leads.created_at) <= date(?)',
                [
                    $date['from'],
                    $date['to'],
                ]);
        } elseif ((isset($params['lead_date_from']) && $params['lead_date_from'] !== '') &&
            (isset($params['lead_date_to']) && $params['lead_date_to'] !== '')) {
            $query->whereRaw('date(leads.created_at) >= date(?) and date(leads.created_at) <= date(?)',
                [
                    $params['lead_date_from'],
                    $params['lead_date_to'],
                ]);
        } elseif ((isset($params['lead_date_from']) && $params['lead_date_from'] !== '') &&
            (! isset($params['lead_date_to']) || $params['lead_date_to'] === '')) {
            $query->whereRaw('date(leads.created_at) = date(?)', [$params['lead_date_from']]);
        } elseif ((! isset($params['lead_date_from']) || $params['lead_date_from'] === '') &&
            (isset($params['lead_date_to']) && $params['lead_date_to'] !== '')) {
            $query->whereRaw('date(leads.created_at) = date(?)', [$params['lead_date_to']]);
        }

        if (isset($params['group_by']) && $params['group_by'] !== '') {
            switch ($params['group_by']) {
                case 'week':
                    // $query->selectRaw('DATE_FORMAT(leads.created_at,"%Y-%U") as lead_date');
                    $query->addSelect(DB::RAW('DATE_FORMAT(leads.created_at,"%Y-%U") as lead_date'));
                    // $query->selectRaw('CONCAT(DATE_FORMAT(created_at,"%Y"),"-", week(created_at)) as lead_date');
                    $query->groupBy(DB::raw('week(leads.created_at)'));
                    break;
                case 'month':
                    // $query->selectRaw('DATE_FORMAT(leads.created_at,"%Y-%m") as lead_date');
                    $query->addSelect(DB::RAW('DATE_FORMAT(leads.created_at,"%Y-%m") as lead_date'));
                    // $query->selectRaw('month(created_at) as lead_date');
                    $query->groupBy(DB::raw('month(leads.created_at)'));
                    break;
                case 'year':
                    // $query->selectRaw('year(leads.created_at) as lead_date');
                    $query->addSelect(DB::RAW('year(leads.created_at) as lead_date'));
                    $query->groupBy(DB::raw('year(leads.created_at)'));
                    break;
                default:
                    // $query->selectRaw('date(leads.created_at) as lead_date');
                    $query->addSelect(DB::RAW('date(leads.created_at) as lead_date'));
                    $query->groupBy(DB::raw('date(leads.created_at)'));
                    break;
            }
        } else {
            $query->selectRaw('date(leads.created_at) as lead_date');
            $query->groupBy(DB::raw('date(leads.created_at)'));
        }

        if (isset($params['group_by_column']) && $params['group_by_column'] !== '') {
            switch ($params['group_by_column']) {
                case 'campaign':
                    $query->groupBy('campaign_id', 'lead_status');
                    break;
                case 'creative_id':
                    $query->groupBy('creative_id', 'lead_status');
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

        if (isset($params['order'])) {
            $order_col = $columns[$params['order'][0]['column']];
            $order_dir = $params['order'][0]['dir'];
            $query->orderBy($order_col, $order_dir);
        } else {
            // $query->orderBy('leads.created_at','desc');
            $query->orderBy('lead_date', 'desc');
        }

        return $query;
    }

    public function scopeGetRevenueStatsTotal($query, $params)
    {
        // $query->selectRaw('COUNT(*)');

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
                case 'yesterday' :
                    $date['from'] = Carbon::yesterday()->startOfDay();
                    $date['to'] = Carbon::yesterday()->endOfDay();
                    break;
                case 'week' :
                    $date['from'] = Carbon::now()->startOfWeek();
                    $date['to'] = Carbon::now()->endOfWeek();
                    break;
                case 'month':
                    $date['from'] = Carbon::now()->startOfMonth();
                    $date['to'] = Carbon::now()->endOfMonth();
                    break;
                case 'last_month':
                    $date['from'] = Carbon::now()->subMonth()->startOfMonth();
                    $date['to'] = Carbon::now()->subMonth()->endOfMonth();
                    break;
                case 'year' :
                    $date['from'] = Carbon::now()->startOfYear();
                    $date['to'] = Carbon::now()->endOfYear();
                    break;
                default:
                    $date = null;
                    break;
            }
            $query->whereRaw('date(leads.created_at) >= date(?) and date(leads.created_at) <= date(?)',
                [
                    $date['from'],
                    $date['to'],
                ]);
        } elseif ((isset($params['lead_date_from']) && $params['lead_date_from'] !== '') &&
            (isset($params['lead_date_to']) && $params['lead_date_to'] !== '')) {
            $query->whereRaw('date(leads.created_at) >= date(?) and date(leads.created_at) <= date(?)',
                [
                    $params['lead_date_from'],
                    $params['lead_date_to'],
                ]);
        } elseif ((isset($params['lead_date_from']) && $params['lead_date_from'] !== '') &&
            (! isset($params['lead_date_to']) || $params['lead_date_to'] === '')) {
            $query->whereRaw('date(leads.created_at) = date(?)', [$params['lead_date_from']]);
        } elseif ((! isset($params['lead_date_from']) || $params['lead_date_from'] === '') &&
            (isset($params['lead_date_to']) && $params['lead_date_to'] !== '')) {
            $query->whereRaw('date(leads.created_at) = date(?)', [$params['lead_date_to']]);
        }

        if (isset($params['group_by']) && $params['group_by'] !== '') {
            switch ($params['group_by']) {
                case 'week':
                    $query->selectRaw('DATE_FORMAT(leads.created_at,"%Y-%U") as lead_date');
                    // $query->selectRaw('CONCAT(DATE_FORMAT(created_at,"%Y"),"-", week(created_at)) as lead_date');
                    $query->groupBy(DB::raw('week(leads.created_at)'));
                    break;
                case 'month':
                    $query->selectRaw('DATE_FORMAT(leads.created_at,"%Y-%m") as lead_date');
                    // $query->selectRaw('month(created_at) as lead_date');
                    $query->groupBy(DB::raw('month(leads.created_at)'));
                    break;
                case 'year':
                    $query->selectRaw('year(leads.created_at) as lead_date');
                    $query->groupBy(DB::raw('year(leads.created_at)'));
                    break;
                default:
                    $query->selectRaw('date(leads.created_at) as lead_date');
                    $query->groupBy(DB::raw('date(leads.created_at)'));
                    break;
            }
        } else {
            $query->selectRaw('date(leads.created_at) as lead_date');
            $query->groupBy(DB::raw('date(leads.created_at)'));
        }

        if (isset($params['group_by_column']) && $params['group_by_column'] !== '') {
            switch ($params['group_by_column']) {
                case 'campaign':
                    $query->groupBy('campaign_id', 'lead_status');
                    break;
                case 'creative_id':
                    $query->groupBy('creative_id', 'lead_status');
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
                    $query->groupBy('campaign_id', 'creative_id', 'affiliate_id', 'lead_status', 's1', 's2', 's3', 's4', 's5');
                    break;
            }
        } else {
            $query->groupBy('campaign_id', 'creative_id', 'affiliate_id', 'lead_status', 's1', 's2', 's3', 's4', 's5');
        }

        return $query;
    }

    public function scopeGetLeadIDByEmail($query, $campaignID, $email)
    {
        return $query->select('id')
            ->where('campaign_id', '=', $campaignID)
            ->where('lead_email', '=', $email);
    }

    public function scopeGetSuccessLeads($query, $campaignID)
    {
        return $query->select('id')
            ->where('campaign_id', '=', $campaignID)
            ->where('lead_status', '=', 1);
    }

    public function scopeGetDailyCampaignSuccessLeads($query, $campaignID, $date)
    {
        $query->where('lead_status', '=', 1);

        if ($campaignID !== null && $campaignID > 0) {
            $query->where('campaign_id', '=', $campaignID);
        }

        if ($date !== null && $date > 0) {
            $query->whereRaw('date(created_at) = date(?)', [$date]);
        }

        return $query;
    }

    public function scopeGetDailyLeads($query, $campaignID, $affiliateID, $date)
    {
        if ($campaignID !== null && $campaignID > 0) {
            $query->where('campaign_id', '=', $campaignID);
        }

        if ($affiliateID !== null && $affiliateID > 0) {
            $query->where('affiliate_id', '=', $affiliateID);
        }

        if ($date !== null && $date > 0) {
            $query->whereRaw('date(created_at) = date(?)', [$date]);
        }

        return $query;
    }

    public function scopeGetWeeklyLeads($query, $campaignID, $affiliateID, $date)
    {
        if ($campaignID !== null && $campaignID > 0) {
            $query->where('campaign_id', '=', $campaignID);
        }

        if ($affiliateID !== null && $affiliateID > 0) {
            $query->where('affiliate_id', '=', $affiliateID);
        }

        if ($date !== null && $date > 0) {
            $query->whereRaw('yearweek(created_at) = yearweek(?)', [$date]);
        }

        return $query;
    }

    public function scopeGetMonthlyLeads($query, $campaignID, $affiliateID, $date)
    {
        if ($campaignID !== null && $campaignID > 0) {
            $query->where('campaign_id', '=', $campaignID);
        }

        if ($affiliateID !== null && $affiliateID > 0) {
            $query->where('affiliate_id', '=', $affiliateID);
        }

        $carbonDate = Carbon::parse($date);

        if ($date !== null && $date > 0) {
            $query->whereRaw('year(created_at) = year(?) AND month(created_at) = month(?)', [$carbonDate, $carbonDate]);
        }

        return $query;
    }

    public function scopeGetYearlyLeads($query, $campaignID, $affiliateID, $date)
    {
        if ($campaignID !== null && $campaignID > 0) {
            $query->where('campaign_id', '=', $campaignID);
        }

        if ($affiliateID !== null && $affiliateID > 0) {
            $query->where('affiliate_id', '=', $affiliateID);
        }

        $carbonDate = Carbon::parse($date);

        if ($date !== null && $date > 0) {
            $query->whereRaw('year(created_at) = year(?)', [$carbonDate]);
        }

        return $query;
    }

    public function scopeSuccessLeads($query)
    {
        return $query->where('lead_status', '=', 1);
    }

    public function scopeRejectedLeads($query)
    {
        return $query->where('lead_status', '=', 2);
    }

    public function scopeFailedLeads($query)
    {
        return $query->where('lead_status', '=', 0);
    }

    public function scopePendingLeads($query)
    {
        return $query->where('lead_status', '=', 3);
    }

    public function scopeCampaignEmailLeads($query, $params)
    {
        return $query->select('id', 'campaign_id', 'lead_email', 'lead_status')
            ->where('campaign_id', '=', $params['campaign_id'])
            ->where('lead_email', '=', $params['lead_email'])
            ->orderBy('id', 'ASC');
    }

    public function scopeMonthOld($query)
    {
        $dateNow = Carbon::now()->toDateString();

        return $query->where(DB::raw('MONTH(created_at)'), '<', DB::raw("MONTH('$dateNow')"))
            ->where(DB::raw('YEAR(created_at)'), '=', DB::raw("YEAR('$dateNow')"));
    }

    public function scopeWithDaysOld($query, $numberOfDays)
    {
        $dateNow = Carbon::now()->toDateString();

        $query->where('lead_status', '!=', 4);
        $query->whereRaw("DATEDIFF(DATE('$dateNow'),DATE(created_at)) > $numberOfDays");

        return $query;
    }

    public function scopeAffiliateRevenueStats($query, $params)
    {
        $query->select('affiliate_id', DB::raw('COUNT(affiliate_id) AS leads'), DB::raw('SUM(received) as received'), DB::raw('SUM(payout) AS payout'));

        if (isset($params['affiliate_id'])) {
            $query->where('affiliate_id', '=', $params['affiliate_id']);
        }

        if (isset($params['date'])) {
            $query->where(DB::raw('DATE(created_at)'), '=', DB::raw("DATE('".$params['date']."')"));
        }

        $query->where('lead_status', '=', 1);

        $query->groupBy('affiliate_id');

        return $query;
    }

    public function scopeRevenueTrackerAffiliateReport($query, $params)
    {
        $query->select('affiliate_id', 'campaign_id', DB::raw('COUNT(id) AS lead_count'), DB::raw('SUM(received) AS revenue'));

        if (isset($params['lead_status'])) {
            $query->where('lead_status', '=', $params['lead_status']);
        } else {
            $query->where('lead_status', '=', 1);
        }

        if (isset($params['revenue_tracker_id'])) {
            $query->where('affiliate_id', '=', $params['revenue_tracker_id']);
        }

        if (isset($params['created_at'])) {
            $query->whereRaw('DATE(created_at) = DATE(?)', [$params['created_at']]);
        }

        $query->groupBy('affiliate_id', 'campaign_id', DB::raw('DATE(created_at)'))
            ->orderBy('affiliate_id');
    }

    public function scopeRevenueTrackerAffiliateReportWithStatuses($query, $params)
    {
        $query->select('affiliate_id', 'campaign_id', 'lead_status', DB::raw('COUNT(id) AS lead_count'), DB::raw('SUM(received) AS revenue'));

        if (isset($params['lead_statuses'])) {
            $query->whereIn('lead_status', $params['lead_statuses']);
        }

        if (isset($params['revenue_tracker_id'])) {
            $query->where('affiliate_id', '=', $params['revenue_tracker_id']);
        }

        if (isset($params['created_at'])) {
            $query->whereRaw('DATE(created_at) = DATE(?)', [$params['created_at']]);
        }

        $query->groupBy('affiliate_id', 'campaign_id', 'lead_status', DB::raw('DATE(created_at)'))
            ->orderBy('affiliate_id', 'campaign_id');
    }

    public function scopeCreativeRevenueReport($query, $params)
    {

        if (isset($params['campaign_id']) && $params['campaign_id'] !== '') {
            $query->where('campaign_id', '=', $params['campaign_id']);
        }

        if (isset($params['campaign_ids']) && count($params['campaign_ids']) > 0) {
            $query->whereIn('campaign_id', $params['campaign_ids']);
        }

        if (isset($params['affiliate_id']) && $params['affiliate_id'] !== '') {
            $query->where('affiliate_id', '=', $params['affiliate_id']);
        }

        if ((isset($params['lead_date_from']) && $params['lead_date_from'] !== '') &&
             (isset($params['lead_date_to']) && $params['lead_date_to'] !== '')) {
            $query->whereRaw('date(leads.created_at) >= date(?) and date(leads.created_at) <= date(?)',
                [
                    $params['lead_date_from'],
                    $params['lead_date_to'],
                ]);
        } elseif ((isset($params['lead_date_from']) && $params['lead_date_from'] !== '') &&
                (! isset($params['lead_date_to']) || $params['lead_date_to'] === '')) {
            $query->whereRaw('date(leads.created_at) = date(?)', [$params['lead_date_from']]);
        } elseif ((! isset($params['lead_date_from']) || $params['lead_date_from'] === '') &&
                (isset($params['lead_date_to']) && $params['lead_date_to'] !== '')) {
            $query->whereRaw('date(leads.created_at) = date(?)', [$params['lead_date_to']]);
        } else {
            $query->whereRaw('date(leads.created_at) = ?', [$params['lead_date_today']]);
        }

        // order by create date
        $query->orderBy('leads.created_at', 'desc');

        // if(isset($params['limit_rows']) && $params['limit_rows']!=='')
        // {
        //     $query->take($params['limit_rows']);
        // }

        $query->groupBy('campaign_id', 'creative_id', 'path_id', 'lead_status', 'affiliate_id');

        return $query;
    }

    public function scopePageOptInRateReport($query, $fullBreakdown, $params)
    {
        $selectQry = 'leads.campaign_id, leads.affiliate_id as revenue_tracker_id, COUNT(*) as total, date(leads.created_at) as date'; //affiliate_revenue_trackers.affiliate_id, affiliates.company as name,

        if ($fullBreakdown) {
            $selectQry .= ', lead_status';
        }
        $query->selectRaw($selectQry);

        // $query->join('campaigns','campaigns.id','=','leads.campaign_id');

        $query->whereRaw('date(leads.created_at) BETWEEN "'.$params['date_from'].'" AND "'.$params['date_to'].'"');

        $query->whereIn('lead_status', [1, 2]);

        if ($params['affiliate_id'] != '') {
            $query->join('affiliate_revenue_trackers', 'affiliate_revenue_trackers.revenue_tracker_id', '=', 'leads.affiliate_id');

            $query->whereRaw('leads.affiliate_id = '.$params['affiliate_id'].' OR affiliate_revenue_trackers.affiliate_id = '.$params['affiliate_id']);
        }

        if (isset($params['benchmarks'])) {
            $query->whereIn('leads.campaign_id', $params['benchmarks']);
        }

        if ($fullBreakdown) {
            $query->groupBy('leads.campaign_id', 'leads.affiliate_id', 'lead_status');
        } else {
            $query->groupBy('leads.campaign_id', 'leads.affiliate_id');
        }

        if ($params['group_by'] == 'day') {
            $query->groupBy('date');
        }

        return $query;
    }

    public function scopeFilterByRejection($query, $type)
    {
        $setting = Setting::where('code', 'high_rejection_keywords')->first();
        $keywords = json_decode($setting->description, true);
        $duplicate_keywords = array_filter(array_map('trim', array_map('strtolower', explode(',', $keywords['d']))));
        $prepop_keywords = array_filter(array_map('trim', array_map('strtolower', explode(',', $keywords['p']))));
        $filter_keywords = array_filter(array_map('trim', array_map('strtolower', explode(',', $keywords['f']))));
        $accepted_keywords = array_filter(array_map('trim', array_map('strtolower', explode(',', $keywords['a']))));
        $not_others = array_merge($duplicate_keywords, $prepop_keywords, $filter_keywords, $accepted_keywords);

        $query->join('lead_sent_results', function ($q) {
            $q->on('leads.id', '=', 'lead_sent_results.id');
        });

        if ($type == 'acceptable') {
            $query->where(function ($q) use ($accepted_keywords) {
                $c = 0;
                foreach ($accepted_keywords as $key) {
                    if ($c == 0) {
                        $q->where('lead_sent_results.value', 'LIKE', "%$key%");
                    } else {
                        $q->orWhere('lead_sent_results.value', 'LIKE', "%$key%");
                    }
                    $c++;
                }
            });
        } elseif ($type == 'duplicates') {
            $already = $accepted_keywords;
            foreach ($already as $key) {
                $query->where('lead_sent_results.value', 'NOT LIKE', "%$key%");
            }

            $query->where(function ($q) use ($duplicate_keywords) {
                $c = 0;
                foreach ($duplicate_keywords as $key) {
                    if ($c == 0) {
                        $q->where('lead_sent_results.value', 'LIKE', "%$key%");
                    } else {
                        $q->orWhere('lead_sent_results.value', 'LIKE', "%$key%");
                    }
                    $c++;
                }
            });
        } elseif ($type == 'pre_pop_issues') {
            $already = array_merge($duplicate_keywords, $accepted_keywords);
            foreach ($already as $key) {
                $query->where('lead_sent_results.value', 'NOT LIKE', "%$key%");
            }

            $query->where(function ($q) use ($prepop_keywords) {
                $c = 0;
                foreach ($prepop_keywords as $key) {
                    if ($c == 0) {
                        $q->where('lead_sent_results.value', 'LIKE', "%$key%");
                    } else {
                        $q->orWhere('lead_sent_results.value', 'LIKE', "%$key%");
                    }
                    $c++;
                }
            });
        } elseif ($type == 'filter_issues') {
            $already = array_merge($duplicate_keywords, $accepted_keywords, $prepop_keywords);
            foreach ($already as $key) {
                $query->where('lead_sent_results.value', 'NOT LIKE', "%$key%");
            }

            $query->where(function ($q) use ($filter_keywords) {
                $c = 0;
                foreach ($filter_keywords as $key) {
                    if ($c == 0) {
                        $q->where('lead_sent_results.value', 'LIKE', "%$key%");
                    } else {
                        $q->orWhere('lead_sent_results.value', 'LIKE', "%$key%");
                    }
                    $c++;
                }
            });
        } elseif ($type == 'others') {
            $already = $not_others;
            foreach ($already as $key) {
                $query->where('lead_sent_results.value', 'NOT LIKE', "%$key%");
            }
        }

        return $query;
    }
}
