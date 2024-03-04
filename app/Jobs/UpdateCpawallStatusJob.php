<?php

namespace App\Jobs;

use App\Campaign;
use App\Setting;
use Carbon\Carbon;
use Curl\Curl;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Mail;

class UpdateCpawallStatusJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
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
        Log::info('Updating CPAWALL Status ...');
        $dateYesterday = Carbon::yesterday()->format('m/d/Y');
        Log::info('Date: '.$dateYesterday);
        $dateToStr = urlencode($dateYesterday.' 00:00:00');
        $dateFromStr = urlencode($dateYesterday.' 23:59:59');
        $cakeBaseClicksURL = "http://engageiq.performance-stats.com/api/4/reports.asmx/Caps?api_key=zDv2V1bMTwk3my5bUAV3vkue1BJRTQ&advertiser_id=0&offer_id=0&affiliate_id=0&advertiser_tag_id=0&offer_tag_id=0&affiliate_tag_id=0&advertiser_manager_id=0&affiliate_manager_id=0&advertiser_billing_cycle_id=0&affiliate_billing_cycle_id=0&cap_type_id=0&cap_entity=all_entity_types&cap_interval_id=0&traffic_only=FALSE&start_at_row=0&row_limit=0&sort_field=percent_reached&sort_descending=FALSE&search=&start_date=$dateToStr&end_date=$dateFromStr";

        $cDetails = Campaign::where('campaign_type', 5)->where('linkout_offer_id', '!=', 0)->where('linkout_cake_status', 1)->select(['id', 'status', 'linkout_offer_id', 'status_dupe', 'name'])->get();
        //->lists('linkout_offer_id', 'id')->toArray();
        // $offer_ids = array_values($campaigns);
        $offer_ids = [];
        $campaigns = [];
        $statuses = [];
        foreach ($cDetails as $c) {
            if (! in_array($c->linkout_offer_id, $offer_ids)) {
                $offer_ids[] = $c->linkout_offer_id;
            }
            $campaigns[$c->id] = $c->linkout_offer_id;
            $statuses[$c->id] = [
                'id' => $c->id,
                'offer' => $c->linkout_offer_id,
                'name' => $c->name,
                'status' => $c->status,
                'status_dupe' => $c->status_dupe,
            ];
        }

        $data = [];

        $rowStart = 1;
        $rowLimit = 2500;
        $totalRows = 0;

        while (true) {
            $cakeurl = $cakeBaseClicksURL;
            $cakeurl = str_replace('start_at_row=0', 'start_at_row='.$rowStart, $cakeurl);
            $cakeurl = str_replace('row_limit=0', "row_limit=$rowLimit", $cakeurl);

            $response = $this->getCurlResponse($cakeurl);
            $caps = [];

            $totalRows = $response['row_count'];

            if ($totalRows == 0) {
                break;
            } else {
                $x = $rowStart - 1;
                $currentRows = $totalRows - $x;
                if ($currentRows <= 1) {
                    $caps[] = $response['caps']['cap'];
                } else {
                    $caps = $response['caps']['cap'];
                }

                // Log::info($caps);

                foreach ($caps as $cap) {
                    $offer_id = $cap['offer']['offer_id'];
                    if (in_array($offer_id, $offer_ids)) {
                        $campaign_id = array_search($offer_id, $campaigns);
                        $data[$campaign_id] = $statuses[$campaign_id];
                        $data[$campaign_id]['r'] = ! is_array($cap['percent_reached']) ? 1 : 0;
                    }
                }
            }

            $rowStart += $rowLimit;

            if ($rowStart > $totalRows) {
                break;
            }
        }

        // Log::info($data);

        $changed = [];
        foreach ($data as $cid => $d) {
            if (($d['r'] == 1 && $d['status'] != 0) || ($d['r'] == 0 && $d['status'] == 0)) {
                if ($d['r'] == 1 && $d['status'] != 0) {
                    $new_status = 0;
                } elseif ($d['r'] == 0 && $d['status'] == 0) {
                    $new_status = $d['status_dupe'];
                }
                $d['new_status'] = $new_status;
                $changed[] = $d;

                $ch = Campaign::find($d['id']);
                $ch->status = $new_status;
                $ch->save();
            }
        }
        Log::info($changed);

        if (count($changed) > 0) {
            $settings = Setting::where('code', 'report_recipient')->first();
            $emails = explode(';', str_replace(' ', '', $settings->description));
            $date = Carbon::now()->toDateString();
            $statuses = config('constants.CAMPAIGN_STATUS');
            Mail::send('emails.cpawall_update_status_report',
                ['items' => $changed, 'date' => $date, 'statuses' => $statuses],
                function ($m) use ($date, $emails) {

                    foreach ($emails as $cc) {
                        $m->to($cc);
                    }

                    $title = 'Link out campaings status update - '.$date;

                    $m->subject($title);
                });
        }
        Log::info('Updating CPAWALL Done.');
    }

    public function getCurlResponse($url, $response_type = 'xml')
    {
        Log::info("curl: $url");

        $callCounter = 0;
        $response = null;

        do {
            $curl = new Curl();
            $curl->get($url);

            if ($curl->error) {
                Log::info('getCakeClicks API Error!');
                Log::info($curl->error_message);
                $callCounter++;
            } else {
                try {

                    if ($response_type == 'json') {
                        $json = $curl->response;
                    } elseif ($response_type == 'LIBXML_NOWARNING') {
                        $xml = simplexml_load_string($curl->response, 'SimpleXMLElement', LIBXML_NOWARNING);
                        $json = json_encode($xml);
                    } else {
                        $xml = simplexml_load_string($curl->response);
                        $json = json_encode($xml);
                    }
                    $response = json_decode($json, true);
                } catch (Exception $e) {
                    Log::info($e->getCode());
                    Log::info($e->getMessage());
                    $callCounter++;

                    //stop the process when budget breached
                    if ($callCounter == 10) {
                        Log::info('getCakeClicks API call limit reached!');
                        $this->errors[] = [
                            'Area' => 'CURL API call limit reached!',
                            'Info' => $url,
                            'MSG' => $e->getMessage(),
                        ];
                        $curl->close();
                        break;
                    }

                    continue;
                }
            }

            //stop the process when budget breached
            if ($callCounter == 10) {
                Log::info('getCakeClicks API call limit reached!');
                $this->errors[] = [
                    'Area' => 'CURL API call limit reached!',
                    'Info' => $url,
                    'MSG' => '',
                ];
                $curl->close();
                break;
            }

        } while ($curl->error);

        $curl->close();

        return $response;
    }
}
