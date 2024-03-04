<?php

namespace App\Http\Controllers;

use App\LeadUser;
use App\Setting;
use Carbon\Carbon;
use Curl\Curl;
use DateTime;
use DB;
use Excel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LeadUsersController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $inputs = $request->all();
        session()->put('survey_takers_input', $inputs);
        $totalFiltered = LeadUser::searchUsers($inputs)->pluck('id')->count();
        $leadUsersData = [];

        $start = $inputs['start'];
        $length = $inputs['length'];

        $ethnic_groups = config('constants.ETHNIC_GROUPS');

        $columns = [
            0 => 'id',
            1 => 'affiliate_id',
            2 => 'revenue_tracker_id',
            3 => 's1',
            4 => 's2',
            5 => 's3',
            6 => 's4',
            7 => 's5',
            8 => 'first_name',
            9 => 'last_name',
            10 => 'email',
            11 => 'zip',
            12 => 'state',
            13 => 'source_url',
            14 => 'created_at',
        ];
        $leadUsers = LeadUser::searchUsers($inputs)
            ->orderBy($columns[$inputs['order'][0]['column']], $inputs['order'][0]['dir'])
            ->offset($start)->limit($length)
            ->get();

        foreach ($leadUsers as $user) {
            $data = [];

            //for id html
            $idHTML = '<span id="lead_user-'.$user->id.'-id">'.$user->id.'</span>';
            array_push($data, $idHTML);

            //for affiliate id html
            $affiliateIDHTML = '<span id="lead_user-'.$user->id.'-affiliate_id">'.$user->affiliate_id.'</span>';
            array_push($data, $affiliateIDHTML);

            //for revenue tracker id html
            $revenueTrackerIDHTML = '<span id="lead_user-'.$user->id.'-revenue_tracker_id">'.$user->revenue_tracker_id.'</span>';
            array_push($data, $revenueTrackerIDHTML);

            $s1HTML = '<span id="lead_user-'.$user->id.'-s1">'.$user->s1.'</span>';
            array_push($data, $s1HTML);

            $s2HTML = '<span id="lead_user-'.$user->id.'-s2">'.$user->s2.'</span>';
            array_push($data, $s2HTML);

            $s3HTML = '<span id="lead_user-'.$user->id.'-s3">'.$user->s3.'</span>';
            array_push($data, $s3HTML);

            $s4HTML = '<span id="lead_user-'.$user->id.'-s4">'.$user->s4.'</span>';
            array_push($data, $s4HTML);

            $s5HTML = '<span id="lead_user-'.$user->id.'-s5">'.$user->s5.'</span>';
            array_push($data, $s5HTML);

            //for first name html
            $firstNameHTML = '<span id="lead_user-'.$user->id.'-first_name">'.$user->first_name.'</span>';
            array_push($data, $firstNameHTML);

            //for last name HTML
            $lastNameHTML = '<span id="lead_user-'.$user->id.'-last_name">'.$user->last_name.'</span>';
            array_push($data, $lastNameHTML);

            //for email HTML
            $emailHTML = '<span id="lead_user-'.$user->id.'-email">'.$user->email.'</span>';
            array_push($data, $emailHTML);

            //for zip HTML
            $zipHTML = '<span id="lead_user-'.$user->id.'-zip">'.$user->zip.'</span>';
            array_push($data, $zipHTML);

            //for state HTML
            $stateHTML = '<span id="lead_user-'.$user->id.'-state">'.$user->state.'</span>';
            array_push($data, $stateHTML);

            //for source url HTML
            $sourceURLHTML = '<div style="word-wrap: break-word; width: 190px"><span id="lead_user-'.$user->id.'-source_url">'.$user->source_url.'</span></div>';
            array_push($data, $sourceURLHTML);

            //for created at HTML
            $createdAtHTML = '<span id="lead_user-'.$user->id.'-created_at">'.$user->created_at.'</span>';
            array_push($data, $createdAtHTML);

            //for the additional details
            // $dataBirthDate = 'data-birthdate="'.$user->birthdate.'"';
            // $dataGender = 'data-gender="'.$user->gender.'"';
            // $dataCity = 'data-city="'.$user->city.'"';
            // $dataAddress1 = 'data-address1="'.$user->address1.'"';
            // $dataAddress2 = 'data-address2="'.$user->address2.'"';
            // $dataEthnicity = 'data-ethnicity="'.$ethnic_groups[$user->ethnicity].'"';
            // $dataPhone = 'data-phone="'.$user->phone.'"';
            // $dataIP = 'data-ip="'.$user->ip.'"';
            // $dataIsMobile = 'data-ismobile="false"';
            // if($user->is_mobile==1){
            //     $dataIsMobile = 'data-ismobile="true"';
            // }
            // $dataStatus = 'data-status="'.$user->status.'"';
            // //escape double quotes in data response
            // $escapedDataResponse = str_replace('"', '&quot;', $user->response);
            // $dataResponse = 'data-response="'.$escapedDataResponse.'"';
            // $dataUpdatedAt = 'data-updatedat="'.$user->updated_at.'"';
            // $additionalData = $dataBirthDate.' '.$dataGender.' '.$dataCity.' '.$dataAddress1.' '.$dataAddress2.' '.$dataEthnicity.' '.$dataPhone.' '.$dataIP.' '.$dataIsMobile.' '.$dataStatus.' '.$dataResponse.' '.$dataUpdatedAt.' ';

            $user->ethnic_group = isset($ethnic_groups[$user->ethnicity]) ? $ethnic_groups[$user->ethnicity] : '';

            $moreDetailHTML = '<button data-id="'.$user->id.'" class="more-details btn btn-primary"><span class="glyphicon glyphicon-list-alt"></span></button>';
            $moreDetailHTML .= '<textarea id="st-'.$user->id.'-md" class="hide">'.json_encode($user).'</textarea>';
            array_push($data, $moreDetailHTML);

            array_push($leadUsersData, $data);
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => LeadUser::count(),  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $leadUsersData,   // total data array,
        ];

        return response()->json($responseData, 200);
    }

    public function downloadSurveyTakers(): BinaryFileResponse
    {
        $inputs = session()->get('survey_takers_input');

        $columns = [
            'id',
            'affiliate_id',
            'revenue_tracker_id',
            's1',
            's2',
            's3',
            's4',
            's5',
            'first_name',
            'last_name',
            'email',
            'zip',
            'state',
            'source_url',
            'created_at',
        ];

        $title = 'SurveyTakers_'.Carbon::now()->toDateString();

        $leadUsers = LeadUser::searchUsers($inputs)
            ->orderBy($columns[$inputs['order'][0]['column']], $inputs['order'][0]['dir'])
            ->get();

        Excel::create($title, function ($excel) use ($title, $leadUsers) {
            $excel->sheet($title, function ($sheet) use ($leadUsers) {

                // Set auto size for sheet
                $sheet->setAutoSize(true);

                $headers = ['ID', 'Affiliate ID', 'Revenue Tracker ID', 'S1', 'S2', 'S3', 'S4', 'S5', 'First Name', 'Last Name', 'Email', 'Birthdate', 'Gender', 'State', 'City', 'Zip', 'Address1', 'Address2', 'Phone', 'IP Address', 'Source Url', 'Is Mobile', 'All Inbox Status', 'Response', 'Created At', 'Updated At'];

                //set up the title header row
                $sheet->appendRow($headers);

                //style the headers
                $sheet->cells('A1:Z1', function ($cells) {

                    // Set font
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                $ethnic_groups = config('constants.ETHNIC_GROUPS');

                foreach ($leadUsers as $user) {
                    if ($user->status == 1) {
                        $stat = 'Sent';
                    } elseif ($user->status == 0) {
                        $stat = 'Pending';
                    } else {
                        $stat = 'Processing';
                    }

                    $sheet->appendRow([
                        $user->id,
                        $user->affiliate_id,
                        $user->revenue_tracker_id,
                        $user->s1,
                        $user->s2,
                        $user->s3,
                        $user->s4,
                        $user->s5,
                        $user->first_name,
                        $user->last_name,
                        $user->email,
                        $user->birthdate,
                        $user->gender,
                        $user->state,
                        $user->city,
                        $user->zip,
                        $user->address1,
                        $user->address2,
                        $user->phone,
                        $user->ip,
                        $user->source_url,
                        $user->is_mobile == 1 ? 'True' : 'False',
                        $stat,
                        $user->response,
                        $user->created_at,
                        $user->updated_at,
                    ]);
                }
            });
        })->store('xls', storage_path('downloads'));

        $file_path = storage_path('downloads').'/'.$title.'.xls';

        if (file_exists($file_path)) {
            return response()->download($file_path, $title.'.xls', [
                'Content-Length: '.filesize($file_path),
            ]);
        } else {
            exit('Requested file does not exist on our server!');
        }
    }

    public function validateDate($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);

        return $d && $d->format('Y-m-d') == $date;
    }

    public function allInboxEmailFeed(Request $request, $status = 0)
    {
        if ($status != 0 && $status != 2) {
            return 'status should only be either 0 or 2';
        }
        $allInboxFeedStatus = env('ALL_INBOX_FEED_STATUS', 'false');
        $phoneFeedStatus = env('PHONE_FEED_STATUS', 'false');
        $addFeedStatus = env('ADDRESS_FEED_STATUS', 'false');
        $cvdFeedStatus = env('CVD_FEED_STATUS', 'false');
        $embFeedStatus = env('EMAIL_MEDIA_BUY_FEED_STATUS', 'false');
        $aoFeedStatus = env('ADMIRED_OPINION_FEED_STATUS', 'false');
        $allowLog = env('ALL_INBOX_LOGGER', 'true');
        $edAllInboxFeedStatus = env('EP_ALL_INBOX_FEED_STATUS', 'false');

        //get affiliates
        $settings = Setting::whereIn('code', ['phone_feed_affiliates', 'address_feed_affiliates', 'cvd_feed_affiliates', 'email_media_buy_affiliates', 'all_inbox_feed_affiliates', 'epic_demand_all_inbox_feed_affiliates', 'data_feed_excluded_affiliates'])->get();

        $affiliates = [
            'phone_feed_affiliates' => [],
            'address_feed_affiliates' => [],
            'cvd_feed_affiliates' => [],
            'email_media_buy_affiliates' => [],
            'all_inbox_feed_affiliates' => [],
            'epic_demand_all_inbox_feed_affiliates' => [],
            'data_feed_excluded_affiliates' => [],
        ];
        foreach ($settings as $setting) {
            // Log::info('Enter: '.$setting->code);
            $affiliates[$setting->code] = json_decode($setting->description, true);

            if ($setting->code == 'phone_feed_affiliates') {
                $phoneFeedType = $setting->integer_value;
            } elseif ($setting->code == 'address_feed_affiliates') {
                $addFeedType = $setting->integer_value;
            } elseif ($setting->code == 'cvd_feed_affiliates') {
                $cvdFeedType = $setting->integer_value;
            } elseif ($setting->code == 'email_media_buy_affiliates') {
                $embFeedType = $setting->integer_value;
            } elseif ($setting->code == 'all_inbox_feed_affiliates') {
                $aibFeedType = $setting->integer_value;
            } elseif ($setting->code == 'epic_demand_all_inbox_feed_affiliates') {
                $edaibFeedType = $setting->integer_value;
            } elseif ($setting->code == 'data_feed_excluded_affiliates') {
                $exAffList = [];
                if ($setting->description != '') {
                    $exAffList = explode(',', trim($setting->description));
                }
            }
        }
        // Log::info($affiliates);

        if ($status == 0) {
            $from = Carbon::now()->subDay(15)->startOfDay();
            $to = Carbon::now();
        } elseif ($status == 2) {
            $from = Carbon::yesterday()->subDay(15)->startOfDay();
            $to = Carbon::yesterday()->endOfDay();
        }
        $users = LeadUser::where('status', '=', $status)->whereBetween('created_at', [$from, $to])->get();

        $uids = $users->map(function ($st) {
            return $st->id;
        });

        //UPDATE STATUS = 2
        LeadUser::whereIn('id', $uids)->whereBetween('created_at', [$from, $to])->update(['status' => 2]);

        if ($allowLog) {
            Log::info('ALL INBOX CRON Status: '.$status);
        }
        foreach ($users as $user) {

            $source_url = '';
            if ($user->source_url != '') {
                $parse = parse_url($user->source_url);
                $host_names = explode('.', $parse['host']);
                if (count($host_names) > 2) {
                    $source_url = $host_names[count($host_names) - 2].'.'.$host_names[count($host_names) - 1];
                } else {
                    $source_url = $parse['host'];
                }
            }

            // Log::info($user);
            // Log::info($source_url);

            $all_inbox_response = '';
            $emb_feed_response = '';
            $phone_feed_response = '';
            $add_feed_response = '';
            $cvd_feed_response = '';
            $admired_opinion_response = '';

            if ($allowLog) {
                $time1 = microtime(true);
            }

            /* ALL INBOX Feed */
            if ($allInboxFeedStatus == 'true') {
                $canSendAIB = true;
                if (count($affiliates['all_inbox_feed_affiliates']) > 0) { //if no affiliates meaning send to all
                    //check type: if type = 1: affiliates are exemptions; else: affiliates are allowed
                    if ($aibFeedType == 1) {
                        if (in_array($user->revenue_tracker_id, $affiliates['all_inbox_feed_affiliates'])) {
                            $canSendAIB = false;
                        }
                    } else {
                        if (! in_array($user->revenue_tracker_id, $affiliates['all_inbox_feed_affiliates'])) {
                            $canSendAIB = false;
                        }
                    }
                }

                if (count($exAffList) > 0) {
                    if (in_array($user->revenue_tracker_id, $exAffList)) {
                        $canSendAIB = false;
                    }
                }

                if ($canSendAIB) {

                    if (strpos($source_url, 'paidforresearch') !== false) {
                        $source_id = 620;
                    } elseif (strpos($source_url, 'epicdemand') !== false) {
                        $source_id = 1232;
                    } elseif (strpos($source_url, 'admiredopinions') !== false) {
                        $source_id = 1104;
                    } elseif (strpos($source_url, 'clinicaltrialshelp') !== false) {
                        $source_id = 620;
                    } else {
                        $source_id = 620;
                    }

                    if ($allowLog) {
                        Log::info('AllInbox: '.$user->id.' : '.$source_id.' - '.$source_url);
                    }
                    $all_inbox_curl = new Curl();
                    $all_inbox_curl->post('https://api3.all-inbox.com/ClientDataAPI-V2/?resource=contacts', [
                        'api_key' => 'd41fe959_8cfb_48de_98db_e86bb601820e',
                        'source_id' => $source_id,
                        'email' => $user->email,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'source_url' => 'https://'.$source_url,
                        'sign_up_date' => Carbon::parse($user->created_at)->format('m/d/Y'),
                        'ip_address' => $user->ip,
                        'address1' => $user->address1,
                        'user_agent' => $user->address2,
                        'city' => $user->city,
                        'state' => $user->state,
                        'zip' => $user->zip,
                        'country' => 'US',
                        'phone' => $user->phone,
                        'gender' => $user->gender,
                        'dob' => $user->birthdate,
                        'source_sub_id' => 'CD'.$user->revenue_tracker_id,
                        'is_mobile' => $user->is_mobile == 1 ? 'true' : 'false',
                    ]);
                    $all_inbox_curl->setopt(CURLOPT_POST, 1);
                    $all_inbox_curl->setopt(CURLOPT_RETURNTRANSFER, true);
                    $all_inbox_curl->setopt(CURLOPT_HEADER, false);
                    $all_inbox_curl->setopt(CURLOPT_FOLLOWLOCATION, true);
                    $all_inbox_curl->setopt(CURLOPT_VERBOSE, false);
                    $all_inbox_curl->setopt(CURLOPT_SSL_VERIFYHOST, 2);
                    $all_inbox_curl->setopt(CURLOPT_SSL_VERIFYPEER, true);
                    $all_inbox_response = $all_inbox_curl->response.' SourceID - '.$source_id.'.';
                    $all_inbox_curl->close();
                    if ($allowLog) {
                        Log::info($all_inbox_curl->response);
                    }
                }
            }

            /* SEPARATE ADMIRED OPINION ALL INBOX Feed */
            // if($aoFeedStatus == 'true') {
            //     if(strpos($user->source_url, 'admiredopinions.com') !== false) {
            //     $canSend = true;
            //     if(count($exAffList) > 0) {
            //         if(in_array($user->revenue_tracker_id, $exAffList)) {
            //             $canSend = false;
            //         }
            //     }
            //     if($canSend) {
            //         $admired_opinion_curl = new Curl();
            //         $admired_opinion_curl->post('https://api3.all-inbox.com/ClientDataAPI-V2/?resource=contacts',array(
            //             "api_key" => "d41fe959_8cfb_48de_98db_e86bb601820e",
            //             "source_id" => 1104,
            //             "email" => $user->email,
            //             "first_name" => $user->first_name,
            //             "last_name" => $user->last_name,
            //             "source_urlsource_url" => "https://" . $source_url,
            //             "sign_up_date" => Carbon::parse($user->created_at)->format('m/d/Y'),
            //             "ip_address" => $user->ip,
            //             "address1" => $user->address1,
            //             "user_agent" => $user->address2,
            //             "city" => $user->city,
            //             "state" => $user->state,
            //             "zip" => $user->zip,
            //             "country" => 'US',
            //             "phone" => $user->phone,
            //             "gender" => $user->gender,
            //             "dob" => $user->birthdate,
            //             "source_sub_id" => 'CD'.$user->revenue_tracker_id
            //         ));
            //         $admired_opinion_curl->setopt(CURLOPT_POST, 1);
            //         $admired_opinion_curl->setopt(CURLOPT_RETURNTRANSFER, TRUE);
            //         $admired_opinion_curl->setopt(CURLOPT_HEADER, FALSE);
            //         $admired_opinion_curl->setopt(CURLOPT_FOLLOWLOCATION, TRUE);
            //         $admired_opinion_curl->setopt(CURLOPT_VERBOSE, FALSE);
            //         $admired_opinion_curl->setopt(CURLOPT_SSL_VERIFYHOST, 2);
            //         $admired_opinion_curl->setopt(CURLOPT_SSL_VERIFYPEER, TRUE);
            //         $admired_opinion_response = ' | AOD: '.$admired_opinion_curl->response;
            //         $admired_opinion_curl->close();
            //     }
            //     }
            // }

            /* EMAIL MEDIA BUY */
            if ($embFeedStatus == 'true') {
                $canSendEMB = true;
                if (count($affiliates['email_media_buy_affiliates']) > 0) { //if no affiliates meaning send to all
                    //check type: if type = 1: affiliates are exemptions; else: affiliates are allowed
                    if ($embFeedType == 1) {
                        if (in_array($user->revenue_tracker_id, $affiliates['email_media_buy_affiliates'])) {
                            $canSendEMB = false;
                        }
                    } else {
                        if (! in_array($user->revenue_tracker_id, $affiliates['email_media_buy_affiliates'])) {
                            $canSendEMB = false;
                        }
                    }
                }

                if (count($exAffList) > 0) {
                    if (in_array($user->revenue_tracker_id, $exAffList)) {
                        $canSendEMB = false;
                    }
                }

                if ($canSendEMB) {
                    $emb_curl = new Curl();
                    $emb_curl->post('https://api3.all-inbox.com/ClientDataAPI-V2/?resource=contacts', [
                        'api_key' => 'd41fe959_8cfb_48de_98db_e86bb601820e',
                        'source_id' => 985,
                        'email' => $user->email,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'source_url' => 'https://'.$source_url,
                        'sign_up_date' => Carbon::parse($user->created_at)->format('m/d/Y'),
                        'ip_address' => $user->ip,
                        'address1' => $user->address1,
                        'user_agent' => $user->address2,
                        'city' => $user->city,
                        'state' => $user->state,
                        'zip' => $user->zip,
                        'country' => 'US',
                        'phone' => $user->phone,
                        'gender' => $user->gender,
                        'dob' => $user->birthdate,
                        'source_sub_id' => 'CD'.$user->revenue_tracker_id,
                        'is_mobile' => $user->is_mobile == 1 ? 'true' : 'false',
                    ]);
                    $emb_curl->setopt(CURLOPT_POST, 1);
                    $emb_curl->setopt(CURLOPT_RETURNTRANSFER, true);
                    $emb_curl->setopt(CURLOPT_HEADER, false);
                    $emb_curl->setopt(CURLOPT_FOLLOWLOCATION, true);
                    $emb_curl->setopt(CURLOPT_VERBOSE, false);
                    $emb_curl->setopt(CURLOPT_SSL_VERIFYHOST, 2);
                    $emb_curl->setopt(CURLOPT_SSL_VERIFYPEER, true);
                    $emb_feed_response = ' | EMB: '.$emb_curl->response.' SourceID - 985.';
                    $emb_curl->close();
                }
            }

            /* PHONE FEED */
            if ($phoneFeedStatus == 'true') {
                if ($user->phone != '') {
                    $canSend = true;

                    if (count($affiliates['phone_feed_affiliates']) > 0) {
                        //check type: if type = 1: affiliates are exemptions; else: affiliates are allowed
                        if ($phoneFeedType == 1) {
                            if (in_array($user->revenue_tracker_id, $affiliates['phone_feed_affiliates'])) {
                                $canSend = false;
                            }
                        } else {
                            if (! in_array($user->revenue_tracker_id, $affiliates['phone_feed_affiliates'])) {
                                $canSend = false;
                            }
                        }
                    }

                    if (count($exAffList) > 0) {
                        if (in_array($user->revenue_tracker_id, $exAffList)) {
                            $canSend = false;
                        }
                    }

                    if ($canSend) {
                        $phone_curl = new Curl();
                        $phone_curl->post('http://edemodata.liquid-data.com/listener.php', [
                            'token' => 'blbMnAMn7qGODODEdgdZuUX2',
                            'email' => $user->email,
                            'capture_date' => Carbon::parse($user->created_at)->format('Y-m-d H:i:s'),
                            'ip' => $user->ip,
                            'url' => 'www.'.$source_url,
                            'address1' => $user->address1,
                            'address2' => '',
                            'city' => $user->city,
                            'state' => $user->state,
                            'zip' => $user->zip,
                            'dob' => $user->birthdate,
                            'first_name' => $user->first_name,
                            'last_name' => $user->last_name,
                            'gender' => $user->gender,
                            'list' => 1,
                            'phone' => $user->phone,
                            'reference' => 'CD'.$user->revenue_tracker_id,
                            // 'flexid' => 'CD'.$user->revenue_tracker_id
                        ]);
                        $phone_curl->setopt(CURLOPT_RETURNTRANSFER, true);
                        $phone_curl->setopt(CURLOPT_HEADER, false);
                        $phone_curl->setopt(CURLOPT_POST, 1);
                        $phone_feed_response = ' | P: '.$phone_curl->response;
                        $phone_curl->close();
                    }
                }
            }

            /* ADDRESS FEED */
            if ($addFeedStatus == 'true') {
                if ($user->address1 != '') {
                    $add_canSend = true;

                    if (count($affiliates['address_feed_affiliates']) > 0) { //
                        //check type: if type = 1: affiliates are exemptions; else: affiliates are allowed
                        if ($addFeedType == 1) {
                            if (in_array($user->revenue_tracker_id, $affiliates['address_feed_affiliates'])) {
                                $add_canSend = false;
                            }
                        } else {
                            if (! in_array($user->revenue_tracker_id, $affiliates['address_feed_affiliates'])) {
                                $add_canSend = false;
                            }
                        }
                    }

                    if (count($exAffList) > 0) {
                        if (in_array($user->revenue_tracker_id, $exAffList)) {
                            $add_canSend = false;
                        }
                    }

                    if ($add_canSend) {
                        $add_curl = new Curl();
                        $add_curl->post('http://edemodata.liquid-data.com/listener.php', [
                            'token' => 'oY01RDtp7laMbeqmzH2HJr1p',
                            'email' => $user->email,
                            'capture_date' => Carbon::parse($user->created_at)->format('Y-m-d H:i:s'),
                            'ip' => $user->ip,
                            'address1' => $user->address1,
                            'reference' => 'CD'.$user->revenue_tracker_id,
                            // 'flexid' => 'CD'.$user->revenue_tracker_id
                        ]);
                        $add_curl->setopt(CURLOPT_RETURNTRANSFER, true);
                        $add_curl->setopt(CURLOPT_HEADER, false);
                        $add_curl->setopt(CURLOPT_POST, 1);
                        $add_feed_response = ' | A: '.$add_curl->response;
                        $add_curl->close();
                    }
                }
            }

            /* CVD Feed */
            if ($cvdFeedStatus == 'true') {
                $canSend = true;
                if (count($affiliates['cvd_feed_affiliates']) > 0) {
                    //check type: if type = 1: affiliates are exemptions; else: affiliates are allowed
                    if ($cvdFeedType == 1) {
                        if (in_array($user->revenue_tracker_id, $affiliates['cvd_feed_affiliates'])) {
                            $canSend = false;
                        }
                    } else {
                        if (! in_array($user->revenue_tracker_id, $affiliates['cvd_feed_affiliates'])) {
                            $canSend = false;
                        }
                    }
                }//else able to send to all

                if (count($exAffList) > 0) {
                    if (in_array($user->revenue_tracker_id, $exAffList)) {
                        $canSend = false;
                    }
                }

                if ($canSend) {
                    $accepted_states = ['AL', 'AR', 'CO', 'GA', 'IA', 'ID', 'IL', 'IN', 'KS', 'LA', 'MD', 'MI', 'MN', 'MO', 'MS', 'NC', 'NE', 'OH', 'OK', 'SC', 'SD', 'TN', 'TX', 'VA', 'WI', 'WY'];
                    $user_age = Carbon::parse($user->birthdate)->age;
                    // Log::info($user_age);
                    if (in_array($user->state, $accepted_states) && ($user_age >= 50 && $user_age <= 80) && $user->address1 != '' && $user->phone != '') {
                        $cvd_curl = new Curl();
                        $fields = http_build_query([
                            // 'IsTest' => 'N',
                            'pid' => 7,
                            'sid' => 36,
                            'cid' => 646,
                            'ip' => $user->ip,
                            'subid1' => 'CD'.$user->revenue_tracker_id,
                            'subid2' => $user->revenue_tracker_id,
                            'externalid' => 1,
                            'First' => $user->first_name,
                            'Last' => $user->last_name,
                            'Email' => $user->email,
                            'Address1' => $user->address1,
                            'Address2' => $user->address2,
                            'City' => $user->city,
                            'State' => $user->state,
                            'Zip' => $user->zip,
                            'Phone' => $user->phone,
                            'dob' => Carbon::parse($user->birthdate)->format('m/d/Y'),
                        ]);
                        $url = 'https://sbg.api.twyne.io/lead/submit?'.$fields;
                        $cvd_curl->get($url);
                        $cvd_curl->setHeader('content-type', 'application/json; charset=utf-8');
                        $cvd_feed_response = ' | CVD: '.$cvd_curl->response;
                        //var_dump($cvd_curl);
                        $cvd_curl->close();
                    }
                }
            }

            /* EPIC DEMAND ALL INBOX */
            // $ep_all_inbox_response = '';
            // if($edAllInboxFeedStatus == 'true') {
            //     $canSendEDAIB = true;
            //     if(count($affiliates['epic_demand_all_inbox_feed_affiliates']) > 0) //if no affiliates meaning send to all
            //     {
            //         //check type: if type = 1: affiliates are exemptions; else: affiliates are allowed
            //         if($edaibFeedType == 1) {
            //             if(in_array($user->revenue_tracker_id, $affiliates['epic_demand_all_inbox_feed_affiliates'])) {
            //                 $canSendEDAIB = false;
            //             }
            //         }else{
            //             if(! in_array($user->revenue_tracker_id, $affiliates['epic_demand_all_inbox_feed_affiliates'])) {
            //                 $canSendEDAIB = false;
            //             }
            //         }
            //     }

            //     if($canSendEDAIB) {
            //         $ed_all_inbox_curl = new Curl();
            //         $ed_all_inbox_curl->post('https://api3.all-inbox.com/ClientDataAPI-V2/?resource=contacts',array(
            //             "api_key" => "d41fe959_8cfb_48de_98db_e86bb601820e",
            //             "source_id" => 1232,
            //             "email" => $user->email,
            //             "first_name" => $user->first_name,
            //             "last_name" => $user->last_name,
            //             "source_url" => "https://" . $source_url,
            //             "sign_up_date" => Carbon::parse($user->created_at)->format('m/d/Y'),
            //             "ip_address" => $user->ip,
            //             "address1" => $user->address1,
            //             "user_agent" => $user->address2,
            //             "city" => $user->city,
            //             "state" => $user->state,
            //             "zip" => $user->zip,
            //             "country" => 'US',
            //             "phone" => $user->phone,
            //             "gender" => $user->gender,
            //             "dob" => $user->birthdate,
            //             "source_sub_id" => 'CD'.$user->revenue_tracker_id,
            //             "is_mobile" => $user->is_mobile == 1 ? 'true' : 'false'
            //         ));
            //         $ed_all_inbox_curl->setopt(CURLOPT_POST, 1);
            //         $ed_all_inbox_curl->setopt(CURLOPT_RETURNTRANSFER, TRUE);
            //         $ed_all_inbox_curl->setopt(CURLOPT_HEADER, FALSE);
            //         $ed_all_inbox_curl->setopt(CURLOPT_FOLLOWLOCATION, TRUE);
            //         $ed_all_inbox_curl->setopt(CURLOPT_VERBOSE, FALSE);
            //         $ed_all_inbox_curl->setopt(CURLOPT_SSL_VERIFYPEER, TRUE);
            //         $ed_all_inbox_curl->setopt(CURLOPT_SSL_VERIFYHOST, 2);
            //         $ep_all_inbox_response = '| EDAI: '. $ed_all_inbox_curl->response;
            //         $ed_all_inbox_curl->close();
            //     }
            // }

            $user->response = $all_inbox_response.$emb_feed_response.$phone_feed_response.$add_feed_response.$cvd_feed_response.$admired_opinion_response;
            $user->status = 1;
            $user->save();
            if ($allowLog) {
                $time2 = microtime(true);
                $time = $time2 - $time1;
                Log::info($user->id.': '.$time);
            }
        }
        Log::info('ALL INBOX DONE');

        return 'Done!';
    }

    public function karla()
    {

        $curl = new Curl();
        $curl->get('http://reports.tmginteractive.com/NS/TMG_ReportAPI.asmx/GetReport?username=EngageIq&password=EngageIq@45&start_date_time=2018-12-16+00%3A00%3A00&end_date_time=2018-12-17+23%3A59%3A59');
        $xml = simplexml_load_string($curl->response, 'SimpleXMLElement', LIBXML_NOWARNING);
        foreach ($xml->placements->placement as $placement) {
            foreach ($placement->transactions->transaction as $transaction) {
                echo $transaction->affId.' - '.$transaction->subId;
                echo '<br>';
            }
            echo '<hr>';
        }
        dd();
        $json = json_encode($xml);
        $response = json_decode($json, true);

        return $response['placement']['placement'][1];

        dd();
        $campaign_type = 4;
        $report = \App\CampaignViewReport::firstOrNew([
            'campaign_id' => 845,
            'revenue_tracker_id' => 1,
            's1' => 'Test',
            's2' => 'Karla',
            's3' => 'subid',
            's4' => '',
            's5' => '',
        ]);

        if ($report->exists) {
            $report->total_view_count = $report->total_view_count + 1;
            $report->current_view_count = $report->current_view_count + 1;
            $report->campaign_type_id = $campaign_type;
        } else {
            $report->total_view_count = 1;
            $report->current_view_count = 1;
            $report->campaign_type_id = $campaign_type;
        }

        $report->campaign_type_id = $campaign_type;
        $report->save();
        dd();
        $curl = new Curl();
        $curl->get('http://affiliates.fireclickmedia.com/affiliates/api/11/reports.asmx/Clicks?api_key=fr6vkUlDGPWDgiajccYZA&affiliate_id=12577&offer_id=0&start_at_row=0&row_limit=0&campaign_id=0&include_duplicates=true&start_date=2018-12-10&end_date=2018-12-11');
        $xml = simplexml_load_string($curl->response);
        $json = json_encode($xml);
        $array = json_decode($json, true);
        if ($array['row_count'] <= 1) {
            $clicks[] = $array['clicks']['click'];
        } else {
            $clicks = $array['clicks']['click'];
        }
        foreach ($clicks as $click) {
            $rev_tracker = ! is_array($click['subid_1']) ? $click['subid_1'] : '';
            $revenue_tracker_id = str_replace('CD', '', $rev_tracker);
            $revenue_tracker_id = $revenue_tracker_id == '' ? 1 : $revenue_tracker_id;
            $s1s2 = ! is_array($click['subid_2']) ? $click['subid_2'] : '';
            $s3 = ! is_array($click['subid_3']) ? $click['subid_3'] : '';
            $s4 = ! is_array($click['subid_4']) ? $click['subid_4'] : '';
            $s5 = ! is_array($click['subid_5']) ? $click['subid_5'] : '';
            $s12 = explode('__', $s1s2);
            $s1 = isset($s12[0]) ? $s12[0] : '';
            $s2 = isset($s12[1]) ? $s12[1] : '';

            if (! isset($data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5])) {
                $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] = 0;
                $rev_trackers[] = $revenue_tracker_id;
            }
            $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] += isset($click['price']) && ! is_array($click['price']) ? $click['price'] : 0;
        }

        return $data;

        dd();
        DB::enableQueryLog();
        $campaignRevenueViews = \App\AffiliateReport::select('affiliate_reports.revenue_tracker_id', 'affiliate_reports.campaign_id', DB::raw('SUM(affiliate_reports.revenue) AS revenue'), DB::raw("(SELECT COUNT(campaign_views.id) FROM campaign_views WHERE campaign_views.campaign_id = affiliate_reports.campaign_id AND campaign_views.affiliate_id = affiliate_reports.revenue_tracker_id AND DATE(campaign_views.created_at) = DATE('2018-12-04')) AS campaign_views "))
            ->whereRaw("DATE(affiliate_reports.created_at) = DATE('2018-12-04')")
            ->groupBy('affiliate_reports.revenue_tracker_id', 'affiliate_reports.campaign_id')->get();

        return DB::getQueryLog();
        dd();
        $curl = new Curl();
        $curl->get('http://engageiq.performance-stats.com/api/12/reports.asmx/Clicks?api_key=zDv2V1bMTwk3my5bUAV3vkue1BJRTQ&advertiser_id=0&offer_id=0&creative_id=0&price_format_id=0&include_tests=false&start_at_row=0&row_limit=2500&include_duplicates=false&affiliate_id=18632&campaign_id=0&start_date=2018-12-04&end_date=2018-12-05');
        $xml = simplexml_load_string($curl->response, 'SimpleXMLElement', LIBXML_NOWARNING);
        $json = json_encode($xml);
        $array = json_decode($json, true);
        $data = [];
        // return $array;
        $clicks = $array['clicks']['click'];
        foreach ($clicks as $click) {
            $rev_tracker = ! is_array($click['sub_id_1']) ? $click['sub_id_1'] : '';
            $revenue_tracker_id = str_replace('CD', '', $rev_tracker);
            $revenue_tracker_id = $revenue_tracker_id == '' ? 1 : $revenue_tracker_id;
            $s1s2 = ! is_array($click['sub_id_2']) ? $click['sub_id_2'] : '';
            $s3 = ! is_array($click['sub_id_3']) ? $click['sub_id_3'] : '';
            $s4 = ! is_array($click['sub_id_4']) ? $click['sub_id_4'] : '';
            $s5 = ! is_array($click['sub_id_5']) ? $click['sub_id_5'] : '';
            $s12 = explode('__', $s1s2);
            $s1 = isset($s12[0]) ? $s12[0] : '';
            $s2 = isset($s12[1]) ? $s12[1] : '';

            if (! isset($data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5])) {
                $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] = 0;
            }

            $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] += isset($click['received']['amount']) ? $click['received']['amount'] : 0;
        }

        $curl->get('http://engageiq.performance-stats.com/api/15/reports.asmx/EventConversions?api_key=zDv2V1bMTwk3my5bUAV3vkue1BJRTQ&brand_advertiser_id=0&site_offer_id=0&campaign_id=0&creative_id=0&event_type=all&event_id=0&channel_id=0&site_offer_contract_id=0&source_affiliate_tag_id=0&brand_advertiser_tag_id=0&site_offer_tag_id=0&price_format_id=0&disposition_type=all&disposition_id=0&source_affiliate_billing_status=all&brand_advertiser_billing_status=all&test_filter=non_tests&start_at_row=0&row_limit=100&sort_field=visitor_id&sort_descending=false&source_affiliate_id=18632&start_date=2018-12-04&end_date=2018-12-05');
        $xml = simplexml_load_string($curl->response, 'SimpleXMLElement', LIBXML_NOWARNING);
        $json = json_encode($xml);
        $array = json_decode($json, true);
        $clicks = $array['event_conversions']['event_conversion'];
        foreach ($clicks as $click) {
            $rev_tracker = ! is_array($click['sub_id_1']) ? $click['sub_id_1'] : '';
            $revenue_tracker_id = str_replace('CD', '', $rev_tracker);
            $revenue_tracker_id = $revenue_tracker_id == '' ? 1 : $revenue_tracker_id;
            $s1s2 = ! is_array($click['sub_id_2']) ? $click['sub_id_2'] : '';
            $s3 = ! is_array($click['sub_id_3']) ? $click['sub_id_3'] : '';
            $s4 = ! is_array($click['sub_id_4']) ? $click['sub_id_4'] : '';
            $s5 = ! is_array($click['sub_id_5']) ? $click['sub_id_5'] : '';
            $s12 = explode('__', $s1s2);
            $s1 = isset($s12[0]) ? $s12[0] : '';
            $s2 = isset($s12[1]) ? $s12[1] : '';

            if (! isset($data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5])) {
                $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] = 0;
            }

            $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] += isset($click['received']['amount']) ? $click['received']['amount'] : 0;
        }

        return $data;

        dd();
        // $curl = new Curl();
        // $curl->get('https://epollsurveys.api.hasoffers.com/Apiv3/json?api_key=ab5f781d44740da55b17c0d8bafd193d1cae0e9b88543771582bcb47eb44f447&Target=Affiliate_Report&Method=getStats&fields[]=Stat.payout&fields[]=Stat.affiliate_info5&fields[]=Stat.affiliate_info4&fields[]=Stat.affiliate_info3&fields[]=Stat.affiliate_info2&fields[]=Stat.affiliate_info1&fields[]=Stat.date&data_start=2018-11-29&data_end=2018-11-29');
        // $array = json_decode($curl->response,TRUE);
        // return $array;
        // return $array['response']['data']['data'];

        $curl = new Curl();
        $curl->get('http://api.pdreporting.com/API/aerAPI/v1/?output=XML&method=getSourceTrackingByDateReport&date=2018-12-03,2018-12-03&pub_code=EG3&sessionUUID=B489E1AC-0B51-8587-81CBCB8CBF7F273D');
        $xml = simplexml_load_string($curl->response);
        foreach ($xml->result->report->groups->group->groupRecords->record as $value) {
            echo $value->sourceId;
            echo ' --- ';
            echo $value->publisherRevenueShare;
            echo '<br>';
            // $publisherRevenueShare = doubleval($record['publisherRevenueShare']);
            // $publisherRevenueShare = round($publisherRevenueShare, 2, PHP_ROUND_HALF_DOWN);
        }
        // return $xml['result']['report']['groups']['group']['groupRecords']['record'];
        // return $array;
        dd();

        $curl = new Curl();
        $curl->get('http://rexadz.com/api/?key=UGMzdVBlU3FkMWhBOUtiR2pwUjFCUT09&startdate=4/5/2018&enddate=4/5/2018');
        $xml = simplexml_load_string($curl->response);
        // return $xml->websites[0]->subid1;
        foreach ($xml->websites as $website) {
            foreach ($website->subid1->subid as $value) {
                echo $value['id'];
                echo '---';
                echo $value;
                echo '<br>';
            }
            echo 'karla';
        }

        // foreach($xml->websites[0]->subid1->subid as $key => $value){
        //    echo $key['id'].' - '.$value;
        // }
        dd();
        $json = json_encode($xml);
        $array = json_decode($json, true);

        // return $array['websites'];
        return $array;
        dd();

        $url = 'https://api.ifficient.com/inquiry/pubrevenue';
        $dateStr = '12/03/2018';

        $curl = new Curl();
        $curl->setBasicAuthentication('engageiq', 'engage26#2');
        $curl->get($url, [
            'sourceids' => 'all',
            'subids' => 'all',
            'startdate' => $dateStr,
            'enddate' => $dateStr,
        ]);
        $response = json_decode($curl->response, true);
        // return count($response['Sources']);
        // return $response['Sources'][0]['Subids'];
        return $response;

        dd();

        $curl = new Curl();
        $curl->get('http://reports.tmginteractive.com/NS/TMG_ReportAPI.asmx/GetReport?username=EngageIq&password=EngageIq@45&start_date_time=2018-12-03+00%3A00%3A00&end_date_time=2018-12-03+23%3A59%3A59');
        $xml = simplexml_load_string($curl->response, 'SimpleXMLElement', LIBXML_NOWARNING);
        $json = json_encode($xml);
        $array = json_decode($json, true);
        // return $array;
        foreach ($array['placements']['placement'] as $placement) {
            foreach ($placement['transactions']['transaction'] as $transaction) {
                echo $transaction['revenue'];
                echo '<br>';
            }
            echo '<hr>';
        }
        //return $array['placements'];
        // return $array['clicks']['click'][0]['received']['amount'];
    }
}
