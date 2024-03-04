<?php

namespace App\Jobs;

use App\CakeConversion;
use App\Campaign;
use App\LinkOutCount;
use Carbon\Carbon;
use Curl\Curl;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Log;
use Sabre\Xml\Reader;

class GetCakeConversions extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->attempts() > 1) {
            return;
        }

        $startDate = Carbon::now()->subHour()->format('Y/m/d H:00:00');
        $endDate = Carbon::now()->format('Y/m/d H:00:00');

        $startDateParam = $startDate;
        $endDateParam = $endDate;
        $firstConversionID = 0;
        $lastConversionID = 0;
        $conversionCount = 0;

        Log::info('Range: '.$startDate.' - '.$endDate);

        $startDate = urlencode($startDate);
        $endDate = urlencode($endDate);

        $templateURL = config('constants.CAKE_API_CONVERSION_URL');

        //for the start date
        $templateURL = str_replace('CAKE_START_DATE', $startDate, $templateURL);
        //for the end date
        $conversionURL = str_replace('CAKE_END_DATE', $endDate, $templateURL);

        //set initial start  row and row limit
        $conversionURL = str_replace('START_ROW', 0, $conversionURL);
        $conversionURL = str_replace('ROW_LIMIT', 1, $conversionURL);

        $prefix = config('constants.CAKE_SABRE_PREFIX_V11');
        $subPrefix = config('constants.CAKE_SABRE_SUB_PREFIX_V11');

        $reader = new Reader();

        $reader->elementMap = [
            $prefix.'conversion_report_response' => 'Sabre\Xml\Element\KeyValue',
            //$prefix.'conversions' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'conversion' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'event_info' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'affiliate' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'advertiser' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'offer' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'campaign' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'creative' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'received' => 'Sabre\Xml\Element\KeyValue',

        ];

        //now the URL is built. Perform the execution of API to get conversion data.
        $curl = new Curl();
        $curl->get($conversionURL);
        $reader->xml($curl->response);
        $parsedResponse = $reader->parse();

        if ($curl->error) {
            Log::info($curl->error_code);
        } else {

            $rowCount = intval($parsedResponse['value'][$prefix.'row_count']);
            $rows = $rowCount;
            //$rowCount = 1000;
            $startRow = 0;
            $rowLimit = 500;
            $count = 0;

            //FOR CPAWALL CAP
            $campaigns = Campaign::where('campaign_type', 5) //->where('lead_cap_type','!=',0)
                ->whereNotNull('linkout_offer_id')
                ->where('linkout_offer_id', '!=', 0)
                ->select('id', 'linkout_offer_id', 'lead_cap_type', 'lead_cap_value')->get();

            $campaignLinkId = [];
            $linkCampaignId = [];
            $linkOuts = [];

            foreach ($campaigns as $c) {
                $campaignLinkId[$c->id] = $c->linkout_offer_id;

                if ($c->lead_cap_type != 0) {

                    $linkCampaignId[$c->linkout_offer_id] = $c->id;
                    $linkOuts[$c->linkout_offer_id]['type'] = $c->lead_cap_type;
                    $linkOuts[$c->linkout_offer_id]['value'] = $c->lead_cap_value;
                    $linkOuts[$c->linkout_offer_id]['affiliates'] = [];

                }
            }

            $affiliates = DB::SELECT('SELECT campaign_id,affiliate_id, lead_cap_type, lead_cap_value FROM affiliate_campaign WHERE
                    campaign_id IN (SELECT id FROM campaigns WHERE campaign_type = 5 AND (linkout_offer_id IS NOT NULL AND linkout_offer_id != 0) )');

            foreach ($affiliates as $a) {
                $linkout_offer_id = $campaignLinkId[$a->campaign_id];

                if (! isset($linkOuts[$linkout_offer_id])) {
                    $linkOuts[$linkout_offer_id]['type'] = 0;
                    $linkOuts[$linkout_offer_id]['value'] = 0;
                    $linkOuts[$linkout_offer_id]['affiliates'] = [];
                }

                $linkOuts[$linkout_offer_id]['affiliates'][$a->affiliate_id]['type'] = $a->lead_cap_type;
                $linkOuts[$linkout_offer_id]['affiliates'][$a->affiliate_id]['value'] = $a->lead_cap_value;
            }

            $counter = [];

            //RESET
            $now_date = Carbon::now();
            $reset_caps = LinkOutCount::where('expiration_date', '<=', $now_date)->pluck('id');
            $not_included = [];

            foreach ($reset_caps as $rc) {

                $cap = LinkOutCount::find($rc);
                $campaign = $cap->campaign_id;
                $affiliate = $cap->affiliate_id;
                $offer = $campaignLinkId[$campaign];
                $not_included[] = $offer;

                if ($affiliate == '') {
                    $cap_type = $linkOuts[$offer]['type'];
                } else {
                    $cap_type = $linkOuts[$offer]['affiliates'][$affiliate]['type'];
                }

                switch ($cap_type) {
                    case 1 : //daily
                        $exp_date = Carbon::now()->endOfDay();
                        break;
                    case 2 : //weekly
                        $exp_date = Carbon::now()->endOfWeek();
                        break;
                    case 3 : //monthly
                        $exp_date = Carbon::now()->endOfMonth();
                        break;
                    case 4: //yearly
                        $exp_date = Carbon::now()->endOfYear();
                        break;
                    default:
                        $exp_date = null;
                        break;
                }

                $cap->expiration_date = $exp_date;
                $cap->count = 0;
                $cap->save();
            }

            //CPAWALL CAP END
            do {
                //set initial start  row and row limit
                $templateURL = config('constants.CAKE_API_CONVERSION_URL');
                $templateURL = str_replace('CAKE_START_DATE', $startDate, $templateURL);
                $conversionURL = str_replace('CAKE_END_DATE', $endDate, $templateURL);
                $conversionURL = str_replace('START_ROW', $startRow, $conversionURL);
                $conversionURL = str_replace('ROW_LIMIT', $rowLimit, $conversionURL);

                Log::info('Fetching conversions using the following URL: ');
                Log::info($conversionURL);

                $curl = new Curl();
                $curl->get($conversionURL);

                if ($curl->error) {
                    Log::info($curl->error_code);
                } else {
                    $reader = new Reader();

                    $reader->elementMap = [
                        $prefix.'conversion_report_response' => 'Sabre\Xml\Element\KeyValue',
                        //$prefix.'conversions' => 'Sabre\Xml\Element\KeyValue',
                        $prefix.'conversion' => 'Sabre\Xml\Element\KeyValue',
                        $prefix.'event_info' => 'Sabre\Xml\Element\KeyValue',
                        $prefix.'affiliate' => 'Sabre\Xml\Element\KeyValue',
                        $prefix.'advertiser' => 'Sabre\Xml\Element\KeyValue',
                        $prefix.'offer' => 'Sabre\Xml\Element\KeyValue',
                        $prefix.'campaign' => 'Sabre\Xml\Element\KeyValue',
                        $prefix.'creative' => 'Sabre\Xml\Element\KeyValue',
                        $prefix.'received' => 'Sabre\Xml\Element\KeyValue',
                    ];

                    $reader->xml($curl->response);
                    $response = $reader->parse();

                    //get the row count
                    $conversions = $response['value'][$prefix.'conversions'];

                    foreach ($conversions as $cnv) {
                        $count++;

                        $conversionData = $cnv['value'];

                        try {

                            $conversion = CakeConversion::firstOrNew(['id' => $conversionData[$prefix.'conversion_id']]);

                            //CPAWALL CAP START
                            if (! $conversion->exists && ! in_array($conversionData[$prefix.'offer'][$subPrefix.'offer_id'], $not_included)) {
                                $offer_id = $conversionData[$prefix.'offer'][$subPrefix.'offer_id'];
                                $affiliate_id = $conversionData[$prefix.'affiliate'][$subPrefix.'affiliate_id'];
                                // Log::info($conversionData[$prefix.'offer'][$subPrefix.'offer_id'].' - '.$conversionData[$prefix.'affiliate'][$subPrefix.'affiliate_id']);

                                if (in_array($offer_id, array_keys($linkOuts))) {
                                    if ($linkOuts[$offer_id]['type'] != 0) {
                                        $counter[$offer_id][null] = ! isset($counter[$offer_id][null]) ? 1 : $counter[$offer_id][null] + 1;
                                    } else {
                                        $counter[$offer_id][null] = 0;
                                    }

                                    if (in_array($affiliate_id, array_keys($linkOuts[$offer_id]['affiliates']))) {
                                        $counter[$offer_id][$affiliate_id] = ! isset($counter[$offer_id][$affiliate_id]) ? 1 : $counter[$offer_id][$affiliate_id] + 1;
                                    }
                                }
                            }

                            if ($firstConversionID == 0) {
                                $firstConversionID = $conversionData[$prefix.'conversion_id'];
                            }

                            //CPAWALL CAP END
                            $conversion->id = $conversionData[$prefix.'conversion_id']; //conversion_id
                            $conversion->visitor_id = $conversionData[$prefix.'visitor_id']; //visitor_id
                            $conversion->request_session_id = $conversionData[$prefix.'request_session_id']; //request_session_id
                            $conversion->click_request_session_id = $conversionData[$prefix.'click_request_session_id']; //click_request_session_id
                            $conversion->click_id = $conversionData[$prefix.'click_id']; //click_id
                            $conversion->conversion_date = $conversionData[$prefix.'conversion_date']; //conversion_date
                            $conversion->last_updated = $conversionData[$prefix.'last_updated']; //last_updated
                            $conversion->click_date = $conversionData[$prefix.'click_date']; //click_date
                            $conversion->event_id = $conversionData[$prefix.'event_info'][$subPrefix.'event_id']; //event_id
                            $conversion->affiliate_id = $conversionData[$prefix.'affiliate'][$subPrefix.'affiliate_id']; //affiliate_id
                            $conversion->advertiser_id = $conversionData[$prefix.'advertiser'][$subPrefix.'advertiser_id']; //advertiser_id
                            $conversion->offer_id = $conversionData[$prefix.'offer'][$subPrefix.'offer_id']; //offer_id
                            $conversion->offer_name = $conversionData[$prefix.'offer'][$subPrefix.'offer_name']; //offer_name
                            $conversion->campaign_id = $conversionData[$prefix.'campaign'][$prefix.'campaign_id']; //campaign_id
                            $conversion->creative_id = $conversionData[$prefix.'creative'][$subPrefix.'creative_id']; //creative_id
                            $conversion->sub_id_1 = $conversionData[$prefix.'sub_id_1']; //sub_id_1
                            $conversion->sub_id_2 = $conversionData[$prefix.'sub_id_2']; //sub_id_2
                            $conversion->sub_id_3 = $conversionData[$prefix.'sub_id_3']; //sub_id_3
                            $conversion->sub_id_4 = $conversionData[$prefix.'sub_id_4']; //sub_id_4
                            $conversion->sub_id_5 = $conversionData[$prefix.'sub_id_5']; //sub_id_5
                            $conversion->conversion_ip_address = $conversionData[$prefix.'conversion_ip_address']; //conversion_ip_address
                            $conversion->click_ip_address = $conversionData[$prefix.'click_ip_address']; //click_ip_address
                            $conversion->received_amount = $conversionData[$prefix.'received'][$prefix.'amount']; //received_amount
                            $conversion->test = $conversionData[$prefix.'test']; //test
                            $conversion->transaction_id = $conversionData[$prefix.'transaction_id']; //transaction_id
                            $conversion->save();

                            $lastConversionID = $conversionData[$prefix.'conversion_id'];

                            $conversionCount++;
                        } catch (QueryException $exception) {
                            //do nothing
                        }
                    }

                    if ($rows < 500) {
                        $rowLimit = $rows;
                        $rows = 0;
                    }

                    $rows -= 500;
                    $startRow += 500;
                }

            } while ($rows >= 0);

            foreach ($counter as $offer_id => $c) {
                foreach ($c as $affiliate => $new_count) {
                    $campaign_id = array_search($offer_id, $campaignLinkId); //$linkCampaignId[$offer_id];
                    $affiliate_id = $affiliate == '' ? null : $affiliate;
                    //1 -> Daily; 2 -> Weekly; 3 -> Monthly; 4 -> Yearly

                    if ($affiliate_id == '' || $affiliate_id == null) {
                        $type = $linkOuts[$offer_id]['type'];
                    } else {
                        $type = $linkOuts[$offer_id]['affiliates'][$affiliate_id]['type'];
                    }

                    if ($type != 0) {
                        $cap = LinkOutCount::firstOrNew(['campaign_id' => $campaign_id, 'affiliate_id' => $affiliate_id]);

                        if ($cap->exists) {
                            $cap->count += $new_count;
                        } else {
                            $cap->count = $new_count;
                        }

                        $cap->save();
                    }
                }
            }
        }

        $emailNotificationRecipient = env('REPORTS_EMAIL_NOTIFICATION_RECIPIENT', 'marwilburton@hotmail.com');

        //send email to Burt to notify that Atchive Leads Queue was successfully finished
        Mail::send('emails.get_cake_conversions',
            ['startDateParam' => $startDateParam, 'endDateParam' => $endDateParam, 'firstConversionID' => $firstConversionID, 'lastConversionID' => $lastConversionID, 'conversionCount' => $conversionCount],
            function ($m) use ($emailNotificationRecipient) {
                $m->from('ariel@engageiq.com', 'Ariel Magbanua');
                $m->to($emailNotificationRecipient, 'Marwil Burton');
                // $m->cc('ariel@engageiq.com', 'Ariel Magbanua');
                $m->subject('Get Cake Conversions Job Queue Successfully Executed!');
            });
    }
}
