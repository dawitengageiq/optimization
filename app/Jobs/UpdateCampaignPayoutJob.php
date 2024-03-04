<?php

namespace App\Jobs;

use App\Campaign;
use App\Http\Services\UserActionLogger;
use App\Setting;
use Carbon\Carbon;
use Curl\Curl;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Log;
use Maatwebsite\Excel\Facades\Excel;

class UpdateCampaignPayoutJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $logger;

    protected $file;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($file)
    {
        $this->file = $file;
        $this->logger = new UserActionLogger;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Update Campaign Payout Job Process');
        Log::info($this->file);

        $sheetNames = Excel::load($this->file)->getSheetNames();
        $results = Excel::load($this->file)->get();

        $coreg_notes = [];
        $cpa_notes = [];

        // Log::info($sheetNames);
        // Log::info($results);

        foreach ($sheetNames as $key => $sheetTitle) {
            if (stripos($sheetTitle, 'cpa') !== false) {
                Log::info('CPA Sheet: '.$sheetTitle);
                $sheet = $results[$key];

                $cake_url = 'https://engageiq.performance-stats.com/api/5/addedit.asmx/Offer?api_key=zDv2V1bMTwk3my5bUAV3vkue1BJRTQ&advertiser_id=0&vertical_id=0&offer_name=&third_party_name=&hidden=no_change&offer_status_id=0&offer_type_id=0&currency_id=0&ssl=no_change&click_cookie_days=-1&impression_cookie_days=-1&redirect_offer_contract_id=0&redirect_404=no_change&enable_view_thru_conversions=no_change&click_trumps_impression=no_change&disable_click_deduplication=no_change&last_touch=no_change&enable_transaction_id_deduplication=no_change&postbacks_only=no_change&pixel_html=&postback_url=&postback_url_ms_delay=-1&fire_global_pixel=no_change&static_suppression=-1&conversion_cap_behavior=-1&conversion_behavior_on_redirect=-1&expiration_date=12/31/2014+13:59:59&expiration_date_modification_type=do_not_change&offer_contract_name=&offer_contract_hidden=no_change&price_format_id=0&payout=0&received_percentage=no_change&offer_link=&thankyou_link=&preview_link=&thumbnail_file_import_url=&offer_description=&restrictions=&advertiser_extended_terms=&testing_instructions=&tags=&allow_affiliates_to_create_creatives=no_change&unsubscribe_link=&from_lines=&subject_lines=&conversions_from_whitelist_only=no_change&allowed_media_type_modification_type=do_not_change&allowed_media_type_ids=0&redirect_domain=&cookie_domain=&track_search_terms_from_non_supported_search_engines=no_change&auto_disposition_type=no_change&auto_disposition_delay_hours=0&session_regeneration_seconds=-1&session_regeneration_type_id=0&payout_modification_type=do_not_change&recieved_modification_type=do_not_change&tags_modification_type=do_not_change&fire_pixel_on_non_paid_conversions=no_change&received_modification_type=change';

                foreach ($sheet as $row) {

                    if ($row->offer_id != null && is_numeric($row->new_cpa_cpc)) {
                        // \Log::info($row);
                        $received = number_format($row->new_cpa_cpc, 2);
                        \Log::info("offer_id: $row->offer_id");
                        \Log::info("new received: $received");

                        $note = [
                            'offer_id' => $row->offer_id,
                            'received' => $received,
                        ];

                        $url = $cake_url.'&offer_id='.$row->offer_id.'&received='.$received;
                        $note['cake_url'] = $url;
                        $curl = new Curl();
                        $curl->get($url);
                        if ($curl->error) {
                            $note['curl_status'] = 'curl error';
                            $note['curl_response'] = $curl->error_message;
                            Log::info('error encountered. offer not updated.');
                            Log::info($curl->error_message);
                        } else {
                            $note['curl_status'] = 'success';
                            $note['curl_response'] = $curl->response;
                            $xml = simplexml_load_string($curl->response);
                            $json = json_encode($xml);
                            $response = json_decode($json, true);
                            if ($response['success'] == 'true') {
                                Log::info('offer updated');
                                Log::info($response);
                            }
                        }
                        $curl->close();

                        $cpa_notes[] = $note;
                    }
                }
            } elseif (stripos($sheetTitle, 'coreg') !== false) {
                Log::info('Coreg Sheet: '.$sheetTitle);
                $sheet = $results[$key];
                // Log::info($sheet);
                // Log::info('start');
                foreach ($sheet as $row) {
                    // Log::info($row);
                    // Log::info($row->campaign);
                    $campaign_id = null;
                    if (isset($row->id) && $row->id != null && $row->id != '') {
                        $campaign_id = $row->id;
                    } elseif (isset($row->campaign) && $row->campaign != null && $row->campaign != '') {
                        // preg_match_all('!\d+!', $row->campaign, $matches);
                        preg_match_all('#\((.*?)\)#', $row->campaign, $match);
                        // \Log::info($match);
                        // \Log::info($match[1][count($match[1]) - 1]);
                        if (isset($match[1]) && count($match[1]) > 0) {
                            $campaign_id = $match[1][count($match[1]) - 1];
                        }
                    }
                    // Log::info($campaign_id);

                    if ($campaign_id != null && is_numeric($row->cpl)) {
                        $payout = number_format($row->cpl, 2);
                        $note = [
                            'campaign_id' => $campaign_id,
                            'payout' => $payout,
                        ];

                        $campaign = Campaign::find($campaign_id);
                        if ($campaign) {
                            $note['nlr_status'] = 'success';
                            $current_state = $campaign->toArray();
                            $campaign->default_payout = $payout;
                            $campaign->default_received = $payout;
                            $campaign->save();
                            \Log::info("campaign_id: $campaign_id");
                            \Log::info("cpl: $payout");
                            \Log::info('campaign updated');
                            //Action Logger: Update Campaign Payout/Received
                            $this->logger->log(3, 31, $campaign->id, 'Update info via payout upload', $current_state, $campaign->toArray());
                        } else {
                            $note['nlr_status'] = 'error. campaign not found in lead reactor.';
                            Log::info($row);
                            Log::info('No campaign id found in lead reactor');
                        }

                        $coreg_notes[] = $note;
                    } else {
                        Log::info($row);
                        Log::info('No campaign id found in excel');
                    }
                    Log::info('-----');
                }
            }
        }

        $settings = Setting::where('code', 'report_recipient')->first();
        $emails = explode(';', str_replace(' ', '', $settings->description));
        $date = Carbon::now()->toDateTimeString();
        $notes = [
            'cpa' => $cpa_notes,
            'coreg' => $coreg_notes,
        ];
        // Log::info($notes);

        Mail::send('emails.update_campaign_payout',
            ['notes' => $notes, 'date' => $date],
            function ($m) use ($date, $emails) {

                foreach ($emails as $cc) {
                    $m->to($cc);
                }

                $m->subject('Update Campaign Payout Job Process - '.$date);
            });

        // Excel::load($this->file, function($reader) {
        //     $reader->each(function($sheet) {
        //         // get sheet title
        //         $sheetTitle = $sheet->getTitle();
        //         // Log::info($sheetTitle);
        //         // Log::info('----');
        //         // $sheet->each(function($row) {
        //         //     \Log::info($row);
        //         // });
        //         if(stripos($sheetTitle, 'cpa') !== false) {
        //             Log::info('CPA Sheet: '.$sheetTitle);
        //             $sheet->each(function($row) {
        //                 \Log::info($row);
        //             });
        //         }else if(stripos($sheetTitle, 'coreg') !== false){
        //             Log::info('Coreg Sheet: '.$sheetTitle);

        //             // Loop through all rows
        //             $sheet->each(function($row) {
        //                 // Log::info($row);
        //                 $campaign_id = null;
        //                 if(isset($row->id) && $row->id != null && $row->id != ''){
        //                     $campaign_id = $row->id;
        //                 }else if(isset($row->campaign) && $row->campaign != null && $row->campaign != '') {
        //                     preg_match_all('#\((.*?)\)#', $row->campaign, $match);
        //                     if(isset($match[1])) {
        //                         $campaign_id = $match[1][count($match[1]) - 1];
        //                     }
        //                 }

        //                 if($campaign_id != null) {
        //                     $campaign = Campaign::find($campaign_id);
        //                     if($campaign) {
        //                         $payout = number_format($row->cpl, 2);
        //                         $current_state = $campaign->toArray();
        //                         $campaign->default_payout = $payout;
        //                         $campaign->default_received = $payout;
        //                         $campaign->save();
        //                         \Log::info("campaign_id: $campaign_id");
        //                         \Log::info("cpl: $payout");
        //                         \Log::info("campaign updated");
        //                         //Action Logger: Update Campaign Payout/Received
        //                         $this->logger->log(3, 31, $campaign->id, 'Update info via payout upload', $current_state, $campaign->toArray());
        //                     }

        //                 }
        //             });
        //         }
        //     });
        // });
    }
}
