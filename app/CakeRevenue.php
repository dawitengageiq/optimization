<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CakeRevenue extends Model
{
    protected $connection;

    protected $table = 'cake_revenues';

    public $timestamps = false;

    protected $fillable = [
        'affiliate_id',
        'revenue_tracker_id',
        'offer_id',
        'revenue',
        'created_at',
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

    public function scopePageOptInRateReport($query, $campaign_ids, $params)
    {
        if ($params['date_from'] != $params['date_to']) {
            $query->selectRaw('revenue_tracker_id, offer_id, SUM(revenue) as revenue, cake_revenues.created_at, s1, s2, s3, s4, s5');
            if ($params['group_by'] == 'day') {
                $query->groupBy('cake_revenues.created_at');
            }
        } else {
            $query->selectRaw('revenue_tracker_id, offer_id, SUM(revenue) as revenue, cake_revenues.created_at, s1, s2, s3, s4, s5');
        }

        $query->groupBy(['revenue_tracker_id', 'offer_id']);

        $query->whereIn('offer_id', $campaign_ids);
        $query->whereRaw('cake_revenues.created_at BETWEEN "'.$params['date_from'].'" AND "'.$params['date_to'].'"');

        if ($params['affiliate_id'] != '') {
            // $query->whereRaw('affiliate_id = '.$params['affiliate_id'].' OR revenue_tracker_id = '. $params['affiliate_id']);

            if (is_array($params['affiliate_id'])) {
                $affs = implode(',', $params['affiliate_id']);
                $query->whereRaw('(affiliate_id IN ('.$affs.') OR revenue_tracker_id IN ('.$affs.'))');
            } else {
                $query->whereRaw('(affiliate_id = '.$params['affiliate_id'].' OR revenue_tracker_id = '.$params['affiliate_id'].')');
            }
        }

        if ($params['group_by'] == 'day') {
            $query->groupBy('cake_revenues.created_at');
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
}
