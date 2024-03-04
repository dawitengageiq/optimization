<?php

namespace App;

use Carbon\Carbon;
use DateTime;
use DB;
use Illuminate\Database\Eloquent\Model;

class CakeConversion extends Model
{
    protected $table = 'cake_conversions';

    protected $fillable = [
        'id',
        'visitor_id',
        'request_session_id',
        'click_request_session_id',
        'click_id',
        'conversion_date',
        'last_updated',
        'click_date',
        'event_id',
        'affiliate_id',
        'advertiser_id',
        'offer_id',
        'offer_name',
        'campaign_id',
        'creative_id',
        'sub_id_1',
        'sub_id_2',
        'sub_id_3',
        'sub_id_4',
        'sub_id_5',
        'conversion_ip_address',
        'click_ip_address',
        'received_amount',
        'test',
        'transaction_id',
    ];

    public function scopeWithDaysOld($query, $numberOfDays)
    {
        $dateNow = Carbon::now()->toDateString();

        return $query->whereRaw("DATEDIFF(DATE('$dateNow'),DATE(conversion_date)) > $numberOfDays");
    }

    public function scopeFindConversionsEmailOfferID($query, $inputs)
    {
        //$query->whereRaw("offer_id=$offerID AND sub_id_5='".$email."'");

        if (isset($inputs['offer_id']) && $inputs['offer_id'] != '') {
            $query->where('offer_id', '=', $inputs['offer_id']);
        }

        if (isset($inputs['email']) && $inputs['email'] != '') {
            $query->where('sub_id_5', '=', $inputs['email']);
        }

        return $query;
    }

    public function scopeFindConversionsAffiliateOfferS4($query, $inputs)
    {
        if (isset($inputs['affiliate_id']) && $inputs['affiliate_id'] != '') {
            $query->where('affiliate_id', '=', $inputs['affiliate_id']);
        }

        if (isset($inputs['offer_id']) && $inputs['offer_id'] != '') {
            $query->where('offer_id', '=', $inputs['offer_id']);
        }

        if (isset($inputs['sub_id_4']) && $inputs['sub_id_4'] != '') {
            $query->where('sub_id_4', '=', $inputs['sub_id_4']);
        }

        /*
        $query->where(function($query)
        {
            $s1 = isset($inputs['sub_id_1']) ? "'".$inputs['sub_id_1']."'" : '';
            $s2 = isset($inputs['sub_id_2']) ? "'".$inputs['sub_id_2']."'" : '';
            $s3 = isset($inputs['sub_id_3']) ? "'".$inputs['sub_id_3']."'" : '';
            $s4 = isset($inputs['sub_id_4']) ? "'".$inputs['sub_id_4']."'" : '';
            $s5 = isset($inputs['sub_id_5']) ? "'".$inputs['sub_id_5']."'" : '';

            $query->where('sub_id_1','=',$s1);
            $query->orWhere('sub_id_2','=',$s2);
            $query->orWhere('sub_id_3','=',$s3);
            $query->orWhere('sub_id_4','=',$s4);
            $query->orWhere('sub_id_5','=',$s5);
        });
        */
        return $query;
    }

    public function scopeSearchCakeConversions($query, $inputs)
    {
        $param = $inputs['search']['value'];

        $columns = [
            // datatable column index  => database column name
            0 => 'id',
            1 => 'offer_id',
            2 => 'offer_name',
            3 => 'campaign_id',
            4 => 'sub_id_5',
            5 => 'conversion_date',
        ];

        if (! empty($param) || $param != '') {
            $query->where('offer_name', 'LIKE', "%$param%")
                ->orWhere('offer_name', 'LIKE', "%$param%")
                ->orWhere('sub_id_5', 'LIKE', "%$param%");

            if (is_numeric($param)) {
                $paramInt = intval($param);
                $query->orWhere('id', '=', $paramInt)
                    ->orWhere('offer_id', '=', $paramInt)
                    ->orWhere('campaign_id', '=', $paramInt);
            }

            //for conversion_date
            if ($this->validateDate($param)) {
                $query->orWhere(DB::raw('DATE(conversion_date)'), '=', DB::raw("DATE('$param')"));
            }

        }

        $query->orderBy($columns[$inputs['order'][0]['column']], $inputs['order'][0]['dir']);

        return $query;
    }

    /**
     * Date validation
     */
    protected function validateDate($date): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);

        return $d && $d->format('Y-m-d') == $date;
    }
}
