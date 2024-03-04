<?php

namespace App\Http\Controllers;

use App\AffiliateRevenueTracker;
use App\AffiliateWebsite;
use App\BannedAttempt;
use App\Campaign;
use App\CampaignTypeOrder;
use App\Lead;
use App\LeadUser;
use App\LeadUserBanned;
use App\MixedCoregCampaignOrder;
use App\Setting;
use App\WebsitesViewTracker;
use App\ZipCode;
use Bus;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use RandomProbability;

class CampaignList2Controller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $params = $request->all();
        /* Check if user is banned */
        $phone = $request->phone;
        if ($phone == '') {
            $phone = $request->phone1.$request->phone2.$request->phone3;
        }
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $banned = LeadUserBanned::where('email', $request->email);
        if ($phone != '') {
            $banned = $banned->orWhere('phone', $phone);
        }
        $banned = $banned->get();
        if (count($banned) > 0) {
            BannedAttempt::create([
                'first_name' => $params['first_name'],
                'last_name' => $params['last_name'],
                'email' => $params['email'],
                'birthdate' => Carbon::createFromDate($params['dobyear'], $params['dobmonth'], $params['dobday'])->format('Y-m-d'),
                'gender' => $params['gender'],
                'zip' => $params['zip'],
                'city' => $params['city'],
                'state' => $params['state'],
                'ethnicity' => 0,
                'address1' => $params['address'],
                'address2' => $params['user_agent'],
                'phone' => $params['phone1'].$params['phone2'].$params['phone3'],
                'source_url' => $params['source_url'],
                'affiliate_id' => $params['affiliate_id'],
                'revenue_tracker_id' => $params['revenue_tracker_id'],
                's1' => $params['s1'],
                's2' => $params['s2'],
                's3' => $params['s3'],
                's4' => $params['s4'],
                's5' => $params['s5'],
                'ip' => $params['ip'],
                'is_mobile' => $params['screen_view'] == 1 ? false : true,
            ]);

            return response()->json([
                'status' => 'error',
                'code' => 'banned',
                'message' => 'There are no campaigns available for you at the moment.',
            ]);
            exit;
        }

        //Get Revenue Tracker
        $revenue_tracker = AffiliateRevenueTracker::where('affiliate_id', $request->affiliate_id)
            ->where('campaign_id', $request->campaign_id)
            ->where('offer_id', $request->offer_id)
            ->select(
                'revenue_tracker_id',
                'path_type',
                'order_status',
                'order_type',
                'crg_limit',
                'ext_limit',
                'lnk_limit',
                'order_type',
                'mixed_coreg_campaign_limit',
                'subid_breakdown',
                'sib_s1',
                'sib_s2',
                'sib_s3',
                'sib_s4'
            )
            ->first();
        if (! $revenue_tracker) {
            $revenue_tracker = AffiliateRevenueTracker::where('revenue_tracker_id', 1)->select(
                'revenue_tracker_id',
                'path_type',
                'order_status',
                'order_type',
                'crg_limit',
                'ext_limit',
                'lnk_limit',
                'order_type',
                'mixed_coreg_campaign_limit',
                'subid_breakdown',
                'sib_s1',
                'sib_s2',
                'sib_s3',
                'sib_s4'
            )->first();
        }
        $revenue_tracker_id = $revenue_tracker ? $revenue_tracker->revenue_tracker_id : 1;
        $coreg_limit = $revenue_tracker ? $revenue_tracker->crg_limit : null;
        $external_limit = $revenue_tracker ? $revenue_tracker->ext_limit : null;
        $linkout_limit = $revenue_tracker ? $revenue_tracker->lnk_limit : null;
        $exit_limit = 1;
        $coreg_counter = 0;
        $external_counter = 0;
        $linkout_counter = 0;
        $exit_counter = 0;
        $path_type = $revenue_tracker->path_type;
        $campaign_filters = [];
        $campaign_creatives = [];

        //Get Setting
        $stack_num_campaign = Setting::where('code', 'num_campaign_per_stack_page')->first()->integer_value;
        $stack_type_order = Setting::where('code', 'stack_path_campaign_type_order')->first()->string_value;
        $stack_type_order = json_decode($stack_type_order, true);

        //Get Zip
        $zip = $this->getZipDetails($request->zip);

        //Save User Data
        $user = new LeadUser;
        $user->first_name = $request->first_name == '' ? '' : $request->first_name;
        $user->last_name = $request->last_name == '' ? '' : $request->last_name;
        $user->email = $request->email;
        if ($request->dobyear != '' && $request->dobmonth != '' && $request->dobday != '') {
            $user->birthdate = Carbon::createFromDate($request->dobyear, $request->dobmonth, $request->dobday)->format('Y-m-d');
        } else {
            $user->birthdate = '';
        }
        $user->gender = $request->gender;
        if ($zip) {
            $user->zip = $zip->zip;
            $user->city = $zip->city;
            $user->state = $zip->state;
        }
        $user->ethnicity = 0;
        $user->address1 = $request->address1;
        $user->address2 = $request->user_agent;
        $user->phone = $request->phone1.$request->phone2.$request->phone3;
        $user->affiliate_id = $request->affiliate_id;
        $user->revenue_tracker_id = $revenue_tracker_id;
        $user->ip = $request->ip == '' ? '' : $request->ip;
        $user->is_mobile = $request->screen_view == 1 ? false : true;
        $user->status = $request->email2 == '' ? 0 : 1;
        $user->source_url = $request->source_url;
        $user->s1 = $revenue_tracker->subid_breakdown && $revenue_tracker->sib_s1 && $request->s1 != '' ? $request->s1 : '';
        $user->s2 = $revenue_tracker->subid_breakdown && $revenue_tracker->sib_s2 && $request->s2 != '' ? $request->s2 : '';
        $user->s3 = $revenue_tracker->subid_breakdown && $revenue_tracker->sib_s3 && $request->s3 != '' ? $request->s3 : '';
        $user->s4 = $revenue_tracker->subid_breakdown && $revenue_tracker->sib_s4 && $request->s4 != '' ? $request->s4 : '';
        $user->s5 = $revenue_tracker->subid_breakdown && $revenue_tracker->sib_s5 && $request->s5 != '' ? $request->s5 : '';
        $user->save();

        //Get Email Leads
        $lead_campaigns = Lead::where('lead_email', $user->email)->whereIn('lead_status', [1, 2, 3, 4, 5])->pluck('campaign_id')->toArray();

        //Campaigns
        $campaigns = Campaign::where('status', '!=', 0)->where('status', '!=', 3)
            ->with(['firstAffiliateCampaign' => function ($query) use ($revenue_tracker_id) {
                $query->where('affiliate_id', '=', $revenue_tracker_id);
            }])
            ->with(['leadCounts' => function ($query) use ($revenue_tracker_id) {
                $query->whereNull('affiliate_id')
                    ->orWhere('affiliate_id', '=', $revenue_tracker_id);
            }])
            ->with(['creatives' => function ($query) {
                $query->where('weight', '>', 0)->select('id', 'weight', 'campaign_id');
            }])
            ->with('filter_groups.filters.filter_type')
            ->select('status', 'lead_cap_type', 'lead_cap_value', 'priority', 'campaign_type', 'id')
            ->orderBy('priority', 'asc')->get();

        $accepted_campaigns = [];
        $stack = [];
        foreach ($campaigns as $campaign) {
            // \Log::info('Campaign: '. $campaign->id);

            /* CHECK IF USER ALREADY ANSWERED CAMPAIGN */
            if (in_array($campaign->id, $lead_campaigns)) {
                continue;
            }
            //\Log::info('- lead');

            /* CHECK IF AFFILIATE IS ALLOWED TO RUN CAMPAIGN */
            if ($campaign->status == 1) { //campaign is private
                if (! $campaign->firstAffiliateCampaign) { //campaign does not have affiliate_campaign
                    continue;
                }
            }
            // \Log::info('- affiliate');

            /* CAP CHECKING */
            $campaignCap = true;
            $affiliateCap = true;
            if (count($campaign->lead_counts) > 0) {
                foreach ($campaign->lead_counts as $cap) {
                    if ($cap->affiliate_id == null) { //Campaign Cap Checking
                        if ($campaign->lead_cap_type != 0 && $campaign->lead_cap_value <= $cap->count) {
                            $campaignCap = false;
                        }
                    } else { //Affiliate Cap Checking
                        if ($campaign->firstAffiliateCampaign->lead_cap_type != 0 &&
                            $campaign->firstAffiliateCampaign->lead_cap_value <= $cap->count) {
                            $affiliateCap = false;
                        }
                    }
                }
            }

            if (! $campaignCap || ! $affiliateCap) {
                continue;
            }
            // \Log::info('- cap');

            /* FILTER CHECKING */
            $filterPassed = true;
            $campaign_filter_groups = [];
            if (count($campaign->filter_groups) > 0) {
                $filterResults = [];
                foreach ($campaign->filter_groups as $filter_group) {
                    if (count($filter_group->filters) > 0) {
                        $filterGroupError = 0;
                        $filters = [];
                        foreach ($filter_group->filters as $filter) {
                            $filter_type = $filter->filter_type->type;
                            $filter_name = $filter->filter_type->name;
                            if ($filter_type == 'custom' && $filter_name == 'show_date') {
                                $date_today = Carbon::now()->format('l');
                                if ($this->findCampaignFilterValue($filter, $date_today) == false) {
                                    $filterGroupError++;
                                    break;
                                }
                            } elseif ($filter_type == 'custom' && $filter_name == 'show_time') {
                                $time_today = Carbon::now()->format('H:i');
                                if ($this->findCampaignFilterValue($filter, $time_today) == false) {
                                    $filterGroupError++;
                                    break;
                                }
                            } elseif ($filter_type == 'custom' && $filter_name == 'mobile_view') {
                                if ($user['screen_view'] == 2) {
                                    $mobile = true;
                                } else {
                                    $mobile = false;
                                }
                                if ($this->findCampaignFilterValue($filter, $mobile) == false) {
                                    $filterGroupError++;
                                    break;
                                }
                            } elseif ($filter_type == 'custom' && $filter_name == 'desktop_view') {
                                if ($user['screen_view'] == 1) {
                                    $desktop = true;
                                } else {
                                    $desktop = false;
                                }
                                if ($this->findCampaignFilterValue($filter, $desktop) == false) {
                                    $filterGroupError++;
                                    break;
                                }
                            } elseif ($filter_type == 'custom' && $filter_name == 'tablet_view') {
                                if ($user['screen_view'] == 3) {
                                    $tablet = true;
                                } else {
                                    $tablet = false;
                                }
                                if ($this->findCampaignFilterValue($filter, $tablet) == false) {
                                    $filterGroupError++;
                                    break;
                                }
                            } elseif ($filter_type == 'profile' && $filter_name == 'email') {
                                $email_split = explode('@', $user->email);
                                $domain = explode('.', $email_split[1]);

                                if ($this->findCampaignFilterValue($filter, $domain[0]) == false) {
                                    $filterGroupError++;
                                    break;
                                }
                            } else {
                                $filters[] = [
                                    'type' => $filter->filter_type->type,
                                    'name' => $filter->filter_type->name,
                                    'value' => $this->getCampaignFilterValue($filter),
                                ];
                            }
                        }
                        if (count($filters) > 0) {

                            $campaign_filter_groups[] = $filters;
                        }
                        $filterResults[] = $filterGroupError == 0 ? 1 : 0;
                    }
                }

                if (count($filterResults) == 0 || in_array(1, $filterResults)) {
                    $filterPassed = true;
                } else {
                    $filterPassed = false;
                }
            }

            if (! $filterPassed) {
                continue;
            }
            // \Log::info('- filter');

            /* LIMIT CHECKING */
            $limitChecker = false;
            if ($campaign->campaign_type == 4) { //External Path
                if ($external_limit == null) {
                    $limitChecker = true;
                } elseif ($external_counter < $external_limit) {
                    $limitChecker = true;
                    $external_counter++;
                }
            } elseif ($campaign->campaign_type == 5) { //Link Out
                if ($linkout_limit == null) {
                    $limitChecker = true;
                } elseif ($linkout_counter < $linkout_limit) {
                    $limitChecker = true;
                    $linkout_counter++;
                }
            } elseif ($campaign->campaign_type == 6) { //Exit Page
                if ($exit_counter < $exit_limit) {
                    $limitChecker = true;
                    $exit_counter++;
                }
            } else {
                if ($coreg_limit == null) { //Co-reg
                    $limitChecker = true;
                } elseif ($coreg_counter < $coreg_limit) {
                    $limitChecker = true;
                    $coreg_counter++;
                }
            }

            if ($limitChecker == true) {
                //Check if stack is not empty
                //Check Campaign type
                //Find array with same type of campaign type
                $campaign_type = $campaign->campaign_type;
                // echo $campaign->id.' - '.$campaign_type.'<br>';
                if (array_key_exists($campaign_type, $stack) && $campaign_type != 4 && $campaign_type != 3 && $campaign_type != 7) {
                    //get last array
                    $last_set = count($stack[$campaign_type]) - 1;
                    if (count($stack[$campaign_type][$last_set]) == $stack_num_campaign) {
                        $stack[$campaign_type][count($stack[$campaign_type])][0] = $campaign->id;
                    } else {
                        array_push($stack[$campaign_type][$last_set], $campaign->id);
                    }
                } elseif ($campaign_type == 4 || $campaign_type == 3 || $campaign_type == 7) {
                    //1 Display per Page: External, Long Form (1st Grp) & (2nd Grp)
                    if (isset($stack[$campaign_type])) {
                        $key = count($stack[$campaign_type]);
                    } else {
                        $key = 0;
                    }
                    $stack[$campaign_type][$key][] = $campaign->id;
                } else {
                    $stack[$campaign_type][0][0] = $campaign->id;
                }

                // if(count($campaign_filter_groups) > 0) {
                //     $campaign_filters[$campaign->id] = $campaign_filter_groups;
                // }

                $creative_id = null;
                if (count($campaign->creatives) > 0) {
                    $campCreatives = [];
                    foreach ($campaign->creatives as $cr) {
                        $campCreatives[$cr->id] = $cr->weight;
                    }
                    $creative_id = Bus::dispatch(new RandomProbability($campCreatives));
                }
                if ($creative_id != null) {
                    $campaign_creatives[$campaign->id] = $creative_id;
                } //Save creative id of campaign

                // \Log::info('- limit');
            }

            //if(!$limitChecker) continue;
            // \Log::info('-----------');
        }

        $the_path = [];
        $path_count = 0;
        $has_campaign_type = [];
        foreach ($stack_type_order as $value) {
            //$stack_path[$value] = $stack[$value];
            if (isset($stack[$value])) {
                $path_count += count($stack[$value]);
                foreach ($stack[$value] as $path) {
                    $has_campaign_type[] = $value;
                    $the_path[] = $path;
                }
            }
        }

        // 'revenue_tracker_id' => 1,
        // 'path_type' => 2,
        // 'stacking' => 'Default stacking',
        // 'ordering' => 'Proirity ordering',
        // 'limit' => 'Campaign Type limit',

        if (count($the_path) == 0) {
            $this->sendWarningEmail();
        }

        /*
            user
            campaigns
            creatives
            campaign_types
        */
        $response = [
            'campaigns' => $the_path,
            'user' => $user,
            'campaign_types' => $has_campaign_type,
            // 'filters' => $campaign_filters,
            'creatives' => $campaign_creatives,
            'path_type' => $path_type,
        ];

        return response()->json($response);
    }

    public function getZipDetails($zip)
    {
        return ZipCode::where('zip', $zip)->select([
            'zip',
            'city',
            'state',
        ])->first();
    }

    private function findCampaignFilterValue($filter, $answer)
    {

        $value = false;
        if (! is_null($filter->value_text) || $filter->value_text != '') {
            $answer = strtolower($answer);
            if (strpos($filter->value_text, '[NOT]') !== false) {
                $no_str = explode('[NOT]', $filter->value_text);
                $filter_value = trim(strtolower($no_str[1]));
                if ($answer != $filter_value) {
                    $value = true;
                }
            } elseif ($answer == strtolower($filter->value_text)) {
                $value = true;
            }
        } elseif (! is_null($filter->value_boolean) || $filter->value_boolean != '') {
            if ($answer == $filter->value_boolean) {
                $value = true;
            }
        } elseif (! is_null($filter->value_min_date) || $filter->value_min_date != '' || ! is_null($filter->value_max_date) || $filter->value_max_date != '') {
            $min = Carbon::parse($filter->value_min_date);
            $max = Carbon::parse($filter->value_min_date);
            $value = Carbon::parse($answer)->between($min, $max);
        } elseif (! is_null($filter->value_min_time) || $filter->value_min_time != '' || ! is_null($filter->value_max_time) || $filter->value_max_time != '') {
            $min = Carbon::parse($filter->value_min_time);
            $max = Carbon::parse($filter->value_max_time);
            $value = Carbon::parse($answer)->between($min, $max);
        } elseif (! is_null($filter->value_min_integer) || $filter->value_min_integer != '' || ! is_null($filter->value_max_integer) || $filter->value_max_integer != '') {
            if ($answer >= $filter->value_min_integer && $answer <= $filter->value_max_integer) {
                $value = true;
            }
        } elseif (! is_null($filter->value_array) || $filter->value_array != '') {
            $answer = strtolower($answer);
            if (strpos($filter->value_array, '[NOT]') !== false) {
                $no_str = explode('[NOT]', $filter->value_array);
                $filter_value = trim(strtolower($no_str[1]));

                $filter_value = preg_replace('/\s*,\s*/', ',', $filter_value);

                $array = explode(',', $filter_value);
                if (! in_array($answer, $array)) {
                    $value = true;
                }
            } else {
                $filter_value = trim(strtolower($filter->value_array));

                $filter_value = preg_replace('/\s*,\s*/', ',', $filter_value);

                $array = explode(',', $filter_value);
                if (in_array($answer, $array)) {
                    $value = true;
                }
            }
        } else {
            $value = true;
        }

        return $value;
    }

    private function getCampaignFilterValue($filter)
    {
        $type = '';
        if (! is_null($filter->value_text) || $filter->value_text != '') {
            $answer = strtolower($filter->value_text);
            if (strpos($filter->value_text, '[NOT]') !== false) {
                $type = 'not';
                $no_str = explode('[NOT]', $filter->value_text);
                $answer = trim(strtolower($no_str[1]));
            } else {
                $answer = trim(strtolower($filter->value_text));
            }
        } elseif (! is_null($filter->value_boolean) || $filter->value_boolean != '') {
            $answer = $filter->value_boolean;
        } elseif (! is_null($filter->value_min_date) || $filter->value_min_date != '' || ! is_null($filter->value_max_date) || $filter->value_max_date != '') {
            $answer = [
                'min' => $filter->value_min_date,
                'max' => $filter->value_max_date,
            ];
        } elseif (! is_null($filter->value_min_time) || $filter->value_min_time != '' || ! is_null($filter->value_max_time) || $filter->value_max_time != '') {
            $answer = [
                'min' => $filter->value_min_time,
                'max' => $filter->value_max_time,
            ];
        } elseif (! is_null($filter->value_min_integer) || $filter->value_min_integer != '' || ! is_null($filter->value_max_integer) || $filter->value_max_integer != '') {
            $answer = [
                'min' => $filter->value_min_integer,
                'max' => $filter->value_max_integer,
            ];
        } elseif (! is_null($filter->value_array) || $filter->value_array != '') {
            if (strpos($filter->value_array, '[NOT]') !== false) {
                $type = 'not';
                $no_str = explode('[NOT]', $filter->value_array);
                $filter_value = trim(strtolower($no_str[1]));
                $filter_value = preg_replace('/\s*,\s*/', ',', $filter_value);
                $array = explode(',', $filter_value);
                $answer = $array;
            } else {
                $filter_value = trim(strtolower($filter->value_array));
                $filter_value = preg_replace('/\s*,\s*/', ',', $filter_value);
                $array = explode(',', $filter_value);
                $answer = $array;
            }
        }

        return [
            'type' => $type,
            'value' => $answer,
        ];
    }

    /* STACK */
    public function getCampaignStackList($user)
    {
        // DB::enableQueryLog();
        /*** OVERALL TIME ***/
        $lead_duplicate_total = 0;
        $lead_cap_total = 0;
        $campaign_filter_total = 0;

        /*** TIMER ***/ $time1 = microtime(true);

        /* GET ACTIVE CAMPAIGNS */
        $campaigns = Campaign::where('status', '!=', 0)->where('status', '!=', 3)->orderBy('priority', 'asc')->get();

        /*** TIMER ***/ $time2 = microtime(true);

        /* GET ALL LEADS ANSWERED BY EMAIL */
        $lead_campaigns = Lead::where('lead_email', $user['email'])->whereIn('lead_status', [1, 2, 3, 4, 5])->pluck('campaign_id')->toArray();

        /* GET CAKE CONVERSIONS CLICKED BY EMAIL */
        $cake_clicks = CakeConversion::where('sub_id_5', $user['email'])->pluck('offer_id')->toArray();

        /*** TIMER ***/ $time3 = microtime(true);

        /* 11/20/15 - compare revenue_tracker_id instead of affiliate_id */
        //$revenue_tracker_id = $this->getRevenueTrackerID($user['affiliate_id'],$user['campaign_id'],$user['offer_id']);
        /* 01/18/16 - get coreg, external, and linkout limit instead of path limit */
        $revenue_tracker = AffiliateRevenueTracker::select('revenue_tracker_id', 'crg_limit', 'ext_limit', 'lnk_limit')
            ->where('affiliate_id', $user['affiliate_id'])
            ->where('campaign_id', $user['campaign_id'])
            ->where('offer_id', $user['offer_id'])
            // ->where('s1',$user['s1'])
            // ->where('s2',$user['s2'])
            // ->where('s3',$user['s3'])
            // ->where('s4',$user['s4'])
            // ->where('s5',$user['s5'])
            ->first();

        $revenue_tracker_id = $revenue_tracker ? $revenue_tracker->revenue_tracker_id : 1;
        $coreg_limit = $revenue_tracker ? $revenue_tracker->crg_limit : null;
        $external_limit = $revenue_tracker ? $revenue_tracker->ext_limit : null;
        $linkout_limit = $revenue_tracker ? $revenue_tracker->lnk_limit : null;
        $exit_limit = 1;
        $coreg_counter = 0;
        $external_counter = 0;
        $linkout_counter = 0;
        $exit_counter = 0;

        /* For Stack Path */
        $stack_num_campaign = Setting::where('code', 'num_campaign_per_stack_page')->first()->integer_value;
        $stack_type_order = Setting::where('code', 'stack_path_campaign_type_order')->first()->string_value;
        $stack_type_order = json_decode($stack_type_order, true);
        $stack = [];

        $lead_cap_types = config('constants.LEAD_CAP_TYPES');
        $ethnic_groups = config('constants.ETHNIC_GROUPS');
        $filter_process_status = Setting::select('integer_value')->where('code', 'campaign_filter_process_status')->first()->integer_value;

        $availableCampaign = [];
        $passedCampaigns = [];

        foreach ($campaigns as $campaign) {

            /*** TIMER ***/ $time4 = microtime(true);

            /* CHECK IF USER ALREADY ANSWERED CAMPAIGN */
            if (in_array($campaign->id, $lead_campaigns)) {
                continue;
            }

            /*** TIMER ***/ $time5 = microtime(true);
            /*** OVERALL TIMER: LEAD DUPLICATE ***/ $lead_duplicate_total += $time5 - $time4;

            /*** TIMER ***/ $time6 = microtime(true);

            /* CHECK EACH CAMPAIGN CAP IF REACHED */
            $uniCapPassed = true;
            $affCapPassed = true;

            /* CHECK DEFAULT CAMPAIGN CAP */
            if ($campaign->lead_cap_type == 0) { //unlimited
                $uniCapPassed = true;
            } else {
                $totalCampaignLeadCount = LeadCount::where('campaign_id', $campaign->id)->where('affiliate_id', null)->first();
                if ($totalCampaignLeadCount) { //Lead Count Exists
                    if ($campaign->lead_cap_value > $totalCampaignLeadCount->count) {
                        $uniCapPassed = true;
                    } else {
                        $uniCapPassed = false;
                    }
                } else {
                    // Lead Count doesn't exists therefore, campaign has no count. PASSED
                    $uniCapPassed = true;
                }
            }

            /* CHECK AFFILIATE CAMPAIGN CAP */
            $affiliateCampaignCap = AffiliateCampaign::where('campaign_id', $campaign->id)->where('affiliate_id', $revenue_tracker_id)->first();
            if ($campaign->status == 1) { //Campaign is private
                if ($affiliateCampaignCap && $uniCapPassed) { //check if affiliate campaign exists and default campaign cap is passed
                    if ($affiliateCampaignCap->lead_cap_type == 0) { // if cap = unlimited, the passed
                        $affCapPassed = true;
                    } else {
                        $totalAffCmpLeadCount = LeadCount::where('campaign_id', $campaign->id)->where('affiliate_id', $revenue_tracker_id)->first();
                        if ($totalAffCmpLeadCount) { //affiliate campaign lead count exists
                            if ($affiliateCampaignCap->lead_cap_value >= $totalAffCmpLeadCount->count) {
                                $affCapPassed = true;
                            } else {
                                $affCapPassed = false;
                            }
                        } else { //affiliate campaign lead count does not yet exists
                            $affCapPassed = true;
                        }
                    }
                } else {
                    $affCapPassed = false;
                }
            }

            /*** TIMER ***/ $time7 = microtime(true);
            /*** OVERALL TIMER: LEAD CAP ***/ $lead_cap_total += $time7 - $time6;

            /*** TIMER ***/ $time8 = microtime(true);

            /* CHECK IF FILTERS ARE MET */
            $filterError = 0;

            if ($uniCapPassed && $affCapPassed) {
                // Check campaign filter process' status; If Active(1) proceed with filter, else disregard filters.
                if ($filter_process_status == 1) {
                    $filter_groups = $campaign->filter_groups;
                    $filter_groups_pass_checker = [];

                    if (count($filter_groups) > 0) { //check if campaign has filter groups
                        foreach ($filter_groups as $group) { //get all campaign filter groups
                            if (count($group->filters) > 0 && $group->status == 1) { //check if filter group has filters and is active
                                $filterGroupError = 0; //filter group checker
                                foreach ($group->filters as $filter) { // go through each filter
                                    $filter_type = $filter->filter_type->type;
                                    $filter_name = $filter->filter_type->name;
                                    if ($filter_type == 'profile' && $filter_name == 'age') {
                                        //$age = Carbon::parse($user['birthdate'])->age;
                                        $age = Carbon::createFromDate($user['dobyear'], $user['dobmonth'], $user['dobday'])->age;

                                        if ($this->findCampaignFilterValue($filter, $age) == false) {
                                            $filterGroupError++;
                                            break;
                                        }
                                    } elseif ($filter_type == 'profile' && $filter_name == 'ethnicity') {
                                        $ethnicity = array_search($user['ethnicity'], $ethnic_groups);
                                        // $ethnicity = $ethnic_groups[$user['ethnicity']];
                                        if ($user['ethnicity'] != 0) {
                                            if ($this->findCampaignFilterValue($filter, $ethnicity) == false) {
                                                $filterGroupError++;
                                                break;
                                            }
                                        }
                                    } elseif ($filter_type == 'profile' && $filter_name == 'gender') {
                                        //Male - M
                                        //Female - F
                                        $gender = $user['gender'] == 'M' ? 'Male' : 'Female';
                                        if ($this->findCampaignFilterValue($filter, $gender) == false) {
                                            $filterGroupError++;
                                            break;
                                        }
                                    } elseif ($filter_type == 'profile' && $filter_name == 'email') {
                                        $email_split = explode('@', $user['email']);
                                        $domain = explode('.', $email_split[1]);

                                        if ($this->findCampaignFilterValue($filter, $domain[0]) == false) {
                                            $filterGroupError++;
                                            break;
                                        }
                                    } elseif ($filter_type == 'profile' && array_key_exists($filter_name, $user)) {
                                        if ($this->findCampaignFilterValue($filter, $user[$filter_name]) == false) {
                                            $filterGroupError++;
                                            break;
                                        }
                                    } elseif ($filter_type == 'question') {
                                        if (! isset($user['filter_questions'])) {
                                            $filterGroupError++;
                                            break;
                                        }
                                        if (array_key_exists($filter->filter_type_id, $user['filter_questions'])) {
                                            if ($this->findCampaignFilterValue($filter, $user['filter_questions'][$filter->filter_type_id]) == false) {
                                                $filterGroupError++;
                                                break;
                                            }
                                        } else {
                                            $filterGroupError++;
                                            break;
                                        }
                                    } elseif ($filter_type == 'custom' && $filter->filter_type->name == 'show_date') {
                                        $date_today = Carbon::now()->format('l');
                                        // Log::info($date_today);
                                        if ($this->findCampaignFilterValue($filter, $date_today) == false) {
                                            $filterGroupError++;
                                            break;
                                        }
                                    } elseif ($filter_type == 'custom' && $filter_name == 'show_time') {
                                        $time_today = Carbon::now()->format('H:i');
                                        if ($this->findCampaignFilterValue($filter, $time_today) == false) {
                                            $filterGroupError++;
                                            break;
                                        }
                                    } elseif ($filter_type == 'custom' && $filter_name == 'mobile_view') {
                                        if ($user['screen_view'] == 2) {
                                            $mobile = true;
                                        } else {
                                            $mobile = false;
                                        }
                                        if ($this->findCampaignFilterValue($filter, $mobile) == false) {
                                            $filterGroupError++;
                                            break;
                                        }
                                    } elseif ($filter_type == 'custom' && $filter_name == 'desktop_view') {
                                        if ($user['screen_view'] == 1) {
                                            $desktop = true;
                                        } else {
                                            $desktop = false;
                                        }
                                        if ($this->findCampaignFilterValue($filter, $desktop) == false) {
                                            $filterGroupError++;
                                            break;
                                        }
                                    } elseif ($filter_type == 'custom' && $filter_name == 'tablet_view') {
                                        if ($user['screen_view'] == 3) {
                                            $tablet = true;
                                        } else {
                                            $tablet = false;
                                        }
                                        if ($this->findCampaignFilterValue($filter, $tablet) == false) {
                                            $filterGroupError++;
                                            break;
                                        }
                                    } elseif ($filter_type == 'custom' && $filter_name == 'check_ping') {
                                        $result = $this->checkPing($filter, $user);
                                        if ($this->findCampaignFilterValue($filter, $result) == false) {
                                            $filterGroupError++;
                                            break;
                                        }
                                    }
                                }
                                $filter_groups_pass_checker[] = $filterGroupError == 0 ? 1 : 0;
                            }
                        }
                        if (count($filter_groups_pass_checker) == 0 || in_array(1, $filter_groups_pass_checker)) {
                            $filterError = 0;
                        } else {
                            $filterError = 1;
                        }
                    } else {
                        $filterError = 0;
                    }

                    //Link Out Cake click checker
                    if ($campaign->campaign_type == 5 && $filterError == 0) {
                        if (in_array($campaign->linkout_offer_id, $cake_clicks)) {
                            $filterError = 1;
                        }
                    }
                }

                if ($filterError == 0) {

                    $limitChecker = false;

                    if ($campaign->campaign_type == 4) { //External Path
                        if ($external_limit == null) {
                            $limitChecker = true;
                        } elseif ($external_counter < $external_limit) {
                            $limitChecker = true;
                            $external_counter++;
                        }
                    } elseif ($campaign->campaign_type == 5) { //Link Out
                        if ($linkout_limit == null) {
                            $limitChecker = true;
                        } elseif ($linkout_counter < $linkout_limit) {
                            $limitChecker = true;
                            $linkout_counter++;
                        }
                    } elseif ($campaign->campaign_type == 6) { //Exit Page
                        if ($exit_counter < $exit_limit) {
                            $limitChecker = true;
                            $exit_counter++;
                        }
                    } else {
                        if ($coreg_limit == null) { //Co-reg
                            $limitChecker = true;
                        } elseif ($coreg_counter < $coreg_limit) {
                            $limitChecker = true;
                            $coreg_counter++;
                        }
                    }

                    if ($limitChecker == true) {
                        //Check if stack is not empty
                        //Check Campaign type
                        //Find array with same type of campaign type
                        $campaign_type = $campaign->campaign_type;
                        // echo $campaign->id.' - '.$campaign_type.'<br>';
                        if (array_key_exists($campaign_type, $stack) && $campaign_type != 4 && $campaign_type != 3 && $campaign_type != 7) {
                            //get last array
                            $last_set = count($stack[$campaign_type]) - 1;
                            if (count($stack[$campaign_type][$last_set]) == $stack_num_campaign) {
                                $stack[$campaign_type][count($stack[$campaign_type])][0] = $campaign->id;
                            } else {
                                array_push($stack[$campaign_type][$last_set], $campaign->id);
                            }
                        } elseif ($campaign_type == 4 || $campaign_type == 3 || $campaign_type == 7) {
                            //1 Display per Page: External, Long Form (1st Grp) & (2nd Grp)
                            if (isset($stack[$campaign_type])) {
                                $key = count($stack[$campaign_type]);
                            } else {
                                $key = 0;
                            }
                            $stack[$campaign_type][$key][] = $campaign->id;
                        } else {
                            $stack[$campaign_type][0][0] = $campaign->id;
                        }
                    }
                }
            }

            /*** TIMER ***/ $time9 = microtime(true);
            /*** OVERALL TIMER: CAMPAIGN FILTER ***/ $campaign_filter_total += $time9 - $time8;
        }

        //$stack_path = array();
        $the_path = [];
        $path_count = 0;
        foreach ($stack_type_order as $value) {
            //$stack_path[$value] = $stack[$value];
            if (isset($stack[$value])) {
                $path_count += count($stack[$value]);
                foreach ($stack[$value] as $path) {
                    $the_path[] = $path;
                }
            }
        }

        /*** EXEC TIME ***/
        $exec_time['get_active_campaign'] = $time2 - $time1;
        $exec_time['get_duplicates'] = $time3 - $time2;
        $exec_time['check_duplicate'] = $lead_duplicate_total;
        $exec_time['check_cap'] = $lead_cap_total;
        $exec_time['check_campaign_filter'] = $campaign_filter_total;
        Log::info('/*** EXEC TIME: STACK PATH ***/', $exec_time);
        // Log::info(DB::getQueryLog());
        return $the_path;
    }

    public function campaignListProcessor(Request $request)
    {

        //IDENTIFY REVENUE_TRACKER_ID AND PATH TYPE
        $affiliate_id = $request->input('affiliate_id');

        $revenue_tracker = AffiliateRevenueTracker::select('revenue_tracker_id', 'path_type')
            ->where('affiliate_id', $affiliate_id)
            ->where('campaign_id', $request->input('campaign_id'))
            ->where('offer_id', $request->input('offer_id'))
            // ->where('s1',$request->input('s1'))
            // ->where('s2',$request->input('s2'))
            // ->where('s3',$request->input('s3'))
            // ->where('s4',$request->input('s4'))
            // ->where('s5',$request->input('s5'))
            ->first();

        $revenue_tracker_id = $revenue_tracker ? $revenue_tracker->revenue_tracker_id : 1;
        //Path Types
        // 1 => 'Long Path',
        // 2 => 'Stack Path',
        $default_rev_tracker = AffiliateRevenueTracker::select('path_type')->where('revenue_tracker_id', 1)->first();
        $path_type = $revenue_tracker ? $revenue_tracker->path_type : $default_rev_tracker->path_type;

        //INSERT USER
        $ethnic_groups = config('constants.ETHNIC_GROUPS');
        $dob = Carbon::createFromDate($request->input('dobyear'), $request->input('dobmonth'), $request->input('dobday'))->format('Y-m-d');

        $zip = $request->input('zip');
        //get city and state by zip
        // $zip_details = ZipMaster::select('city','state')->where('zip',$zip)->first();
        $zip_details = ZipCode::select('city', 'state')->where('zip', $zip)->first();

        $user = new LeadUser;
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->email = $request->input('email');
        $user->birthdate = $dob;
        $user->gender = $request->input('gender');
        $user->zip = $zip;
        $user->city = $zip_details->city;
        $user->state = $zip_details->state;
        $user->ethnicity = array_search($request->input('ethnicity'), $ethnic_groups);
        $user->address1 = $request->input('address');
        $user->phone = $request->input('phone1').$request->input('phone2').$request->input('phone3');
        $user->source_url = $request->input('source_url');
        $user->affiliate_id = $affiliate_id;
        $user->revenue_tracker_id = $revenue_tracker_id;
        $user->ip = $request->input('ip');
        $user->is_mobile = $request->input('screen_view') == 1 ? false : true;
        $user->status = 0;
        $user->save();
        $user_id = $user->id;
        // $user_id = 100;

        //RETURN CAMPAIGN LIST
        $usr_data = $_GET;
        $usr_data['state'] = $zip_details->state;
        $usr_data['city'] = $zip_details->city;

        if ($path_type == 1) {
            $return['campaigns'] = $this->getCampaignList($usr_data);
        } else {
            $return['campaigns'] = $this->getCampaignStackList($usr_data);
        }

        // Log::info(json_encode($return['campaigns'])); /* PLS ADD */

        $return['id'] = $user_id;
        $return['user'] = $_GET;
        $return['user']['city'] = $zip_details->city;
        $return['user']['state'] = $zip_details->state;
        $return['affiliate_id'] = $usr_data['affiliate_id'];
        $return['offer_id'] = $usr_data['offer_id'];
        $return['campaign_id'] = $usr_data['campaign_id'];
        $return['revenue_tracker_id'] = $revenue_tracker_id;
        $return['path_type'] = $path_type;
        // $return['revenue_tracker_id'] = $this->getRevenueTrackerID($request->input('affiliate_id'),$request->input('campaign_id'),$request->input('offer_id'));

        return $return;
        // return $request->input('callback')."(".json_encode($return).");";
    }

    protected function sendWarningEmail()
    {
        $time = Carbon::now()->toDateTimeString();
        $setting = Setting::where('code', 'error_email_recipient')->first();
        $recipients = array_map('trim', array_filter(explode(';', $setting->description)));
        Mail::send('emails.no_campaigns_returned_to_user',
            ['date' => $time],
            function ($m) use ($recipients) {
                $m->from('karla@engageiq.com');
                $m->to($recipients)
                    ->subject('CRITICAL ERROR! Lead Reactor not returning campaigns. JSON Code');
            });
    }

    public function capaignListJson(Request $request): JsonResponse
    {
        // \Log::info('Test');
        $params = $request->all();
        // \Log::info($params);

        // \Log::info($params);
        //Get Revenue Tracker
        $revenue_tracker = false;

        $revenue_tracker = AffiliateRevenueTracker::where('affiliate_id', $request->affiliate_id)
            ->where('campaign_id', $request->campaign_id)
            ->where('offer_id', $request->offer_id)
            ->select(
                'revenue_tracker_id',
                'path_type',
                'order_status',
                'order_type',
                'crg_limit',
                'ext_limit',
                'lnk_limit',
                'mixed_coreg_campaign_limit',
                'subid_breakdown',
                'sib_s1',
                'sib_s2',
                'sib_s3',
                'sib_s4'
            )
            ->first();

        if (! $revenue_tracker) {
            $revenue_tracker = AffiliateRevenueTracker::where('revenue_tracker_id', 1)->select(
                'revenue_tracker_id',
                'path_type',
                'order_status',
                'order_type',
                'crg_limit',
                'ext_limit',
                'lnk_limit',
                'mixed_coreg_campaign_limit',
                'subid_breakdown',
                'sib_s1',
                'sib_s2',
                'sib_s3',
                'sib_s4'
            )->first();
        }

        if (isset($params['set_affiliate']) && $params['set_affiliate'] == 1) {
            if (isset($params['campaign_id']) && isset($params['offer_id'])) {
                $revenue_tracker->revenue_tracker_id = $revenue_tracker->revenue_tracker_id;
            } else {
                $revenue_tracker->revenue_tracker_id = $request->affiliate_id;
            }
            $revenue_tracker->subid_breakdown = true;
            $revenue_tracker->sib_s1 = true;
            $revenue_tracker->sib_s2 = true;
            $revenue_tracker->sib_s3 = true;
            $revenue_tracker->sib_s4 = true;
            $revenue_tracker->sib_s5 = true;
        }

        // \Log::info($revenue_tracker);

        if (isset($params['affiliate_website_check'])) {
            if ((! isset($params['website_id']) || $params['website_id'] == '') && isset($params['campaign_id']) && isset($params['offer_id']) && $params['campaign_id'] != '' && $params['offer_id'] != '') {
                //Continue
            } elseif (! isset($params['website_id']) || ! isset($params['affiliate_id'])) {
                $response = [
                    'error' => 'Affiliate and Website ID are required.',
                ];

                return response()->json($response);
            } else {
                $affiliate_website = AffiliateWebsite::where('affiliate_id', $params['affiliate_id'])
                    ->where('id', $params['website_id'])->where('status', 1)->first();

                if (! $affiliate_website) {
                    $response = [
                        'error' => 'You are not allowed to run this path. Please contact EngageIQ.',
                    ];

                    return response()->json($response);
                } else {
                    // if($params['email'] != '') {
                    //     $track = WebsitesViewTracker::firstOrNew([
                    //         'website_id' => $affiliate_website->id,
                    //         'email' => $params['email']
                    //     ]);
                    //     if(!$track->exists) {
                    //         $track->affiliate_id = $request->affiliate_id;
                    //         $track->revenue_tracker_id = $revenue_tracker->revenue_tracker_id;
                    //         $track->s1 = $request->s1;
                    //         $track->s2 = $request->s2;
                    //         $track->s3 = $request->s3;
                    //         $track->s4 = $request->s4;
                    //         $track->s5 = $request->s5;
                    //         $track->payout = $affiliate_website->payout;
                    //         $track->status = 0;
                    //         $track->save();
                    //     }
                    // }
                }
            }

        }

        $revenue_tracker_id = $revenue_tracker ? $revenue_tracker->revenue_tracker_id : 1;
        $coreg_limit = $revenue_tracker ? $revenue_tracker->crg_limit : null;
        $external_limit = $revenue_tracker ? $revenue_tracker->ext_limit : null;
        $linkout_limit = $revenue_tracker ? $revenue_tracker->lnk_limit : null;
        $exit_limit = 1;
        $coreg_counter = 0;
        $external_counter = 0;
        $linkout_counter = 0;
        $exit_counter = 0;
        $path_type = $revenue_tracker->path_type;
        $campaign_filters = [];
        $campaign_creatives = [];
        $ethnic_groups = config('constants.ETHNIC_GROUPS');

        if (isset($params['allow_subid']) && $params['allow_subid'] == 1) {
            $revenue_tracker->subid_breakdown = true;
            $revenue_tracker->sib_s1 = true;
            $revenue_tracker->sib_s2 = true;
            $revenue_tracker->sib_s3 = true;
            $revenue_tracker->sib_s4 = true;
            $revenue_tracker->sib_s5 = true;
        }

        //Get Zip
        $zip = $this->getZipDetails($request->zip);

        /* Check if user is banned */
        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        $banned = LeadUserBanned::where('email', $request->email);
        if ($phone != '') {
            $banned = $banned->orWhere('phone', $phone);
        }
        $banned = $banned->get();
        // \Log::info($banned);
        if (count($banned) > 0) {
            $city = $zip ? $zip->city : '';
            $state = $zip ? $zip->state : '';
            $birthdate = $request->dobyear != '' && $request->dobmonth != '' && $request->dobday != '' ?
                Carbon::createFromDate($request->dobyear, $request->dobmonth, $request->dobday)->format('Y-m-d') : '';
            BannedAttempt::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'birthdate' => $birthdate,
                'gender' => $request->gender,
                'zip' => $request->zip,
                'city' => $city,
                'state' => $state,
                'ethnicity' => 0,
                'address1' => $request->address,
                'address2' => $request->user_agent,
                'phone' => $phone,
                'source_url' => $request->source_url,
                'affiliate_id' => $request->affiliate_id,
                'revenue_tracker_id' => $revenue_tracker_id,
                's1' => $revenue_tracker->subid_breakdown && $revenue_tracker->sib_s1 && $request->s1 != '' ? $request->s1 : '',
                's2' => $revenue_tracker->subid_breakdown && $revenue_tracker->sib_s2 && $request->s2 != '' ? $request->s2 : '',
                's3' => $revenue_tracker->subid_breakdown && $revenue_tracker->sib_s3 && $request->s3 != '' ? $request->s3 : '',
                's4' => $revenue_tracker->subid_breakdown && $revenue_tracker->sib_s4 && $request->s4 != '' ? $request->s4 : '',
                's5' => $revenue_tracker->subid_breakdown && $revenue_tracker->sib_s5 && $request->s5 != '' ? $request->s5 : '',
                'ip' => $request->ip,
                'is_mobile' => $request->screen_view == 1 ? false : true,
            ]);

            return response()->json([
                'status' => 'error',
                'code' => 'banned',
                'error' => 'There is no available survey at this point of time please try again later.',
            ]);
            exit;
        }

        //Get Setting
        $stack_num_campaign = Setting::where('code', 'num_campaign_per_stack_page')->first()->integer_value;
        $stack_type_order = Setting::where('code', 'stack_path_campaign_type_order')->first()->string_value;
        $stack_type_order = json_decode($stack_type_order, true);

        //Save User Data
        $user = new LeadUser;
        $user->first_name = $request->first_name == '' ? '' : $request->first_name;
        $user->last_name = $request->last_name == '' ? '' : $request->last_name;
        $user->email = $request->email;
        if ($request->dobyear != '' && $request->dobmonth != '' && $request->dobday != '') {
            $user->birthdate = Carbon::createFromDate($request->dobyear, $request->dobmonth, $request->dobday)->format('Y-m-d');
        } else {
            $user->birthdate = '';
        }
        $user->gender = $request->gender;
        if ($zip) {
            $user->zip = $zip->zip;
            $user->city = $zip->city;
            $user->state = $zip->state;
        }
        $user->ethnicity = 0;
        $user->address1 = $request->address1;
        $user->address2 = $request->user_agent;
        $user->phone = $request->phone1.$request->phone2.$request->phone3;
        $user->affiliate_id = $request->affiliate_id;
        $user->revenue_tracker_id = $revenue_tracker_id;
        $user->ip = $request->ip == '' ? '' : $request->ip;
        $user->is_mobile = $request->screen_view == 1 ? false : true;
        $user->status = $request->email2 == '' ? 0 : 1;
        $user->source_url = $request->source_url;
        $user->s1 = $revenue_tracker->subid_breakdown && $revenue_tracker->sib_s1 && $request->s1 != '' ? $request->s1 : '';
        $user->s2 = $revenue_tracker->subid_breakdown && $revenue_tracker->sib_s2 && $request->s2 != '' ? $request->s2 : '';
        $user->s3 = $revenue_tracker->subid_breakdown && $revenue_tracker->sib_s3 && $request->s3 != '' ? $request->s3 : '';
        $user->s4 = $revenue_tracker->subid_breakdown && $revenue_tracker->sib_s4 && $request->s4 != '' ? $request->s4 : '';
        $user->s5 = $revenue_tracker->subid_breakdown && $revenue_tracker->sib_s5 && $request->s5 != '' ? $request->s5 : '';

        if (isset($params['dont_save_user'])) {
            //do nothing
        } else {
            $user->save();
        }

        $campaign_types = array_keys(config('constants.CAMPAIGN_TYPES'));
        if (isset($params['campaign_types'])) {
            $campaign_types = $params['campaign_types'];
        }

        if (isset($params['campaign_list_limit']) && $params['campaign_list_limit'] > 0) {
            $coreg_limit = $params['campaign_list_limit'];
        }

        $groupAsOne = isset($params['campaign_grouped']) ? true : false;

        //Get Email Leads
        $lead_campaigns = Lead::where('lead_email', $user->email)->whereIn('lead_status', [1, 2, 3, 4, 5])->pluck('campaign_id')->toArray();

        //Campaigns
        // \DB::connection('secondary')->enableQueryLog();
        // \DB::connection('mysql')->enableQueryLog();

        $campaigns = Campaign::where('status', '!=', 0)
            ->where('status', '!=', 3)
            ->whereIn('campaign_type', $campaign_types);

        if (isset($params['disregard_doi']) && $params['disregard_doi'] == 1) {
            // $campaigns = $campaigns->where('default_received', '>', 0)->where('default_payout', '>', 0);
            $campaigns = $campaigns->where(function ($query) {
                $query->whereIn('campaign_type', [4, 5, 6])
                    ->orWhere(function ($q) {
                        $q->where('default_received', '>', 0)->where('default_payout', '>', 0);
                    });
            });
        }

        if (isset($params['name_like']) && $params['name_like'] != '') {
            $campaigns = $campaigns->where('name', 'like', '%'.$params['name_like'].'%');
        }

        $campaigns = $campaigns->with(['firstAffiliateCampaign' => function ($query) use ($revenue_tracker_id) {
            $query->where('affiliate_id', '=', $revenue_tracker_id);
        }])
            ->with(['leadCounts' => function ($query) use ($revenue_tracker_id) {
                $query->whereNull('affiliate_id')
                    ->orWhere('affiliate_id', '=', $revenue_tracker_id);
            }])
            ->with(['creatives' => function ($query) {
                $query->where('weight', '>', 0)->select('id', 'weight', 'campaign_id');
            }])
            ->with('filter_groups.filters.filter_type')
            ->select('status', 'lead_cap_type', 'lead_cap_value', 'priority', 'campaign_type', 'id')
            ->orderBy('priority', 'asc')->get();
        // \Log::info(\DB::connection('mysql')->getQueryLog());
        // \Log::info(\DB::connection('secondary')->getQueryLog());

        $mergeCoregs = false;
        $mixedCoregTypes = [1, 2, 8, 13];
        if ($revenue_tracker->order_type == 2) {
            $list = MixedCoregCampaignOrder::where('revenue_tracker_id', $revenue_tracker->revenue_tracker_id)->first();

            if ($list) {
                $new_order = $list->campaign_id_order != '' ? json_decode($list->campaign_id_order, true) : [];

                if (count($new_order) > 0) {
                    $kampaigns = [];

                    foreach ($campaigns as $c) {
                        if (! in_array($c->id, $new_order)) {
                            $new_order[] = $c->id;
                        }
                        $kampaigns[$c->id] = $c;
                    }

                    $new_campaigns = [];
                    foreach ($new_order as $cid) {
                        if (isset($kampaigns[$cid])) {
                            $new_campaigns[] = $kampaigns[$cid];
                        }
                    }

                    $campaigns = $new_campaigns;
                    $mergeCoregs = true;
                }
            }
        } elseif ($revenue_tracker->order_type == 1) {
            // \Log::info($stack_type_order);
            $list = CampaignTypeOrder::where('revenue_tracker_id', $revenue_tracker->revenue_tracker_id)->get();
            // \Log::info($list);
            if (count($list) > 0) {
                $klist = [];
                foreach ($list as $l) {
                    $klist[$l->campaign_type_id] = $l;
                }

                $new_order = [];
                foreach ($stack_type_order as $ct) {
                    if (isset($klist[$ct])) {
                        $cur_ct = $klist[$ct];
                        $ct_order = $cur_ct->campaign_id_order != '' ? json_decode($cur_ct->campaign_id_order, true) : [];
                        if (count($ct_order) > 0) {
                            $new_order = array_merge($new_order, $ct_order);
                        }
                    }
                }

                // \Log::info($new_order);

                if (count($new_order) > 0) {
                    $kampaigns = [];

                    foreach ($campaigns as $c) {
                        if (! in_array($c->id, $new_order)) {
                            $new_order[] = $c->id;
                        }
                        $kampaigns[$c->id] = $c;
                    }

                    $new_campaigns = [];
                    foreach ($new_order as $cid) {
                        if (isset($kampaigns[$cid])) {
                            $new_campaigns[] = $kampaigns[$cid];
                        }
                    }

                    $campaigns = $new_campaigns;
                }
            }
        }

        // \Log::info($campaigns);
        $accepted_campaigns = [];
        $stack = [];
        foreach ($campaigns as $campaign) {
            // \Log::info('Campaign: '. $campaign->id.' Type: '.$campaign->campaign_type);

            /* CHECK IF USER ALREADY ANSWERED CAMPAIGN */
            if (in_array($campaign->id, $lead_campaigns)) {
                continue;
            }
            //\Log::info('- lead');

            /* CHECK IF AFFILIATE IS ALLOWED TO RUN CAMPAIGN */
            if ($campaign->status == 1) { //campaign is private
                if (! $campaign->firstAffiliateCampaign) { //campaign does not have affiliate_campaign
                    continue;
                }
            }
            // \Log::info('- affiliate');

            /* CAP CHECKING */
            $campaignCap = true;
            $affiliateCap = true;
            if (count($campaign->lead_counts) > 0) {
                foreach ($campaign->lead_counts as $cap) {
                    if ($cap->affiliate_id == null) { //Campaign Cap Checking
                        if ($campaign->lead_cap_type != 0 && $campaign->lead_cap_value <= $cap->count) {
                            $campaignCap = false;
                        }
                    } else { //Affiliate Cap Checking
                        if ($campaign->firstAffiliateCampaign->lead_cap_type != 0 &&
                            $campaign->firstAffiliateCampaign->lead_cap_value <= $cap->count) {
                            $affiliateCap = false;
                        }
                    }
                }
            }

            if (! $campaignCap || ! $affiliateCap) {
                continue;
            }
            // \Log::info('- cap');

            /* FILTER CHECKING */
            $filterPassed = true;
            $campaign_filter_groups = [];
            if (count($campaign->filter_groups) > 0) {
                $filterResults = [];
                foreach ($campaign->filter_groups as $filter_group) {
                    if (count($filter_group->filters) > 0) {
                        $filterGroupError = 0;
                        $filters = [];
                        foreach ($filter_group->filters as $filter) {
                            $filter_type = $filter->filter_type->type;
                            $filter_name = $filter->filter_type->name;
                            if ($filter_type == 'custom' && $filter_name == 'show_date') {
                                $date_today = Carbon::now()->format('l');
                                if ($this->findCampaignFilterValue($filter, $date_today) == false) {
                                    $filterGroupError++;
                                    break;
                                }
                            } elseif ($filter_type == 'custom' && $filter_name == 'show_time') {
                                $time_today = Carbon::now()->format('H:i');
                                if ($this->findCampaignFilterValue($filter, $time_today) == false) {
                                    $filterGroupError++;
                                    break;
                                }
                            } elseif ($filter_type == 'custom' && $filter_name == 'mobile_view') {
                                if ($params['screen_view'] == 2) {
                                    $mobile = true;
                                } else {
                                    $mobile = false;
                                }
                                if ($this->findCampaignFilterValue($filter, $mobile) == false) {
                                    $filterGroupError++;
                                    break;
                                }
                            } elseif ($filter_type == 'custom' && $filter_name == 'desktop_view') {
                                if ($params['screen_view'] == 1) {
                                    $desktop = true;
                                } else {
                                    $desktop = false;
                                }
                                if ($this->findCampaignFilterValue($filter, $desktop) == false) {
                                    $filterGroupError++;
                                    break;
                                }
                            } elseif ($filter_type == 'custom' && $filter_name == 'tablet_view') {
                                if ($params['screen_view'] == 3) {
                                    $tablet = true;
                                } else {
                                    $tablet = false;
                                }
                                if ($this->findCampaignFilterValue($filter, $tablet) == false) {
                                    $filterGroupError++;
                                    break;
                                }
                            } elseif ($filter_type == 'profile' && $filter_name == 'email') {
                                $email_split = explode('@', $user->email);
                                $domain = explode('.', $email_split[1]);

                                if ($this->findCampaignFilterValue($filter, $domain[0]) == false) {
                                    $filterGroupError++;
                                    break;
                                }
                            } elseif ($request->dobyear != '' && $request->dobmonth != '' && $request->dobday != '' && $filter_type == 'profile' && $filter_name == 'age') {
                                $age = Carbon::createFromDate($params['dobyear'], $params['dobmonth'], $params['dobday'])->age;

                                if ($this->findCampaignFilterValue($filter, $age) == false) {
                                    $filterGroupError++;
                                    break;
                                }
                            } elseif ($request->ethnicity != '' && $filter_type == 'profile' && $filter_name == 'ethnicity') {
                                $ethnicity = array_search($params['ethnicity'], $ethnic_groups);
                                if ($params['ethnicity'] != 0) {
                                    if ($this->findCampaignFilterValue($filter, $ethnicity) == false) {
                                        $filterGroupError++;
                                        break;
                                    }
                                }
                            } elseif ($request->gender != '' && $filter_type == 'profile' && $filter_name == 'gender') {
                                //Male - M
                                //Female - F
                                $gender = $params['gender'] == 'M' ? 'Male' : 'Female';
                                if ($this->findCampaignFilterValue($filter, $gender) == false) {
                                    $filterGroupError++;
                                    break;
                                }
                            } elseif ($filter->filter_type->type == 'profile' && $filter_name == 'zip') {
                                if ($this->findCampaignFilterValue($filter, $params['zip']) == false) {
                                    $filterGroupError++;
                                    break;
                                }
                            } elseif ($filter->filter_type->type == 'profile' && $filter_name == 'state') {
                                if ($this->findCampaignFilterValue($filter, $user->state) == false) {
                                    $filterGroupError++;
                                    break;
                                }
                            } elseif ($filter->filter_type->type == 'profile' && $filter_name == 'city') {
                                if ($this->findCampaignFilterValue($filter, $user->city) == false) {
                                    $filterGroupError++;
                                    break;
                                }
                            }
                        }
                        if (count($filters) > 0) {

                            $campaign_filter_groups[] = $filters;
                        }
                        $filterResults[] = $filterGroupError == 0 ? 1 : 0;
                    }
                }

                if (count($filterResults) == 0 || in_array(1, $filterResults)) {
                    $filterPassed = true;
                } else {
                    $filterPassed = false;
                }
            }

            if (! $filterPassed) {
                continue;
            }
            // \Log::info('- filter');

            /* LIMIT CHECKING */
            $limitChecker = false;
            if ($campaign->campaign_type == 4) { //External Path
                if ($external_limit == null) {
                    $limitChecker = true;
                } elseif ($external_counter < $external_limit) {
                    $limitChecker = true;
                    $external_counter++;
                }
            } elseif ($campaign->campaign_type == 5) { //Link Out
                if ($linkout_limit == null) {
                    $limitChecker = true;
                } elseif ($linkout_counter < $linkout_limit) {
                    $limitChecker = true;
                    $linkout_counter++;
                }
            } elseif ($campaign->campaign_type == 6) { //Exit Page
                if ($exit_counter < $exit_limit) {
                    $limitChecker = true;
                    $exit_counter++;
                }
            } else {
                if ($coreg_limit == null) { //Co-reg
                    $limitChecker = true;
                } elseif ($coreg_counter < $coreg_limit) {
                    $limitChecker = true;
                    $coreg_counter++;
                }
            }

            if (isset($params['disregard_limit_checker'])) {
                $limitChecker = true;
            }

            if ($limitChecker == true) {
                //Check if stack is not empty
                //Check Campaign type
                //Find array with same type of campaign type
                $campaign_type = $campaign->campaign_type;
                // echo $campaign->id.' - '.$campaign_type.'<br>';

                if ($mergeCoregs && in_array($campaign_type, $mixedCoregTypes)) { //MERGE ALL MIXED COREGS
                    $campaign_type = 1;
                    if (! isset($stack[$campaign_type][0])) {
                        $stack[$campaign_type][0] = [];
                    }
                    array_push($stack[$campaign_type][0], $campaign->id);
                } elseif (array_key_exists($campaign_type, $stack) && $campaign_type != 4 && $campaign_type != 3 && $campaign_type != 7) {
                    //get last array
                    $last_set = count($stack[$campaign_type]) - 1;
                    if (count($stack[$campaign_type][$last_set]) == $stack_num_campaign) {
                        $stack[$campaign_type][count($stack[$campaign_type])][0] = $campaign->id;
                    } else {
                        array_push($stack[$campaign_type][$last_set], $campaign->id);
                    }
                } elseif ($campaign_type == 4 || $campaign_type == 3 || $campaign_type == 7) {
                    //1 Display per Page: External, Long Form (1st Grp) & (2nd Grp)
                    if (isset($stack[$campaign_type])) {
                        $key = count($stack[$campaign_type]);
                    } else {
                        $key = 0;
                    }
                    $stack[$campaign_type][$key][] = $campaign->id;
                } else {
                    $stack[$campaign_type][0][0] = $campaign->id;
                }

                // if(count($campaign_filter_groups) > 0) {
                //     $campaign_filters[$campaign->id] = $campaign_filter_groups;
                // }

                $creative_id = null;
                if (count($campaign->creatives) > 0) {
                    $campCreatives = [];
                    foreach ($campaign->creatives as $cr) {
                        $campCreatives[$cr->id] = $cr->weight;
                    }
                    $creative_id = Bus::dispatch(new RandomProbability($campCreatives));
                }
                if ($creative_id != null) {
                    $campaign_creatives[$campaign->id] = $creative_id;
                } //Save creative id of campaign

                // \Log::info('- limit');
            }

            //if(!$limitChecker) continue;
            // \Log::info('-----------');
        }

        // \Log::info($stack);
        // \Log::info('List');
        if ($mergeCoregs) { //merge mixed coregs
            $mcoregs = [];
            $mcLists = [];
            $mcLimit = $revenue_tracker->mixed_coreg_campaign_limit;
            foreach ($stack_type_order as $value) { //$value -> campaign type
                if (isset($stack[$value])) {
                    foreach ($stack[$value] as $path) {
                        if (in_array($value, $mixedCoregTypes)) {
                            if (! isset($mcLists[1][0])) {
                                $mcLists[1][0] = [];
                            }
                            $mcLists[1][0] = array_merge($mcLists[1][0], $path);
                        } else {
                            $mcLists[$value][] = $path;
                        }
                    }
                }
            }
            // \Log::info($mcLists);

            $the_path = [];
            $has_campaign_type = [];
            foreach ($stack_type_order as $value) {
                if (in_array($value, [2, 8, 13])) {
                    continue;
                } //skip cuz its already merged in mixed coreg 1;
                $type_id = in_array($value, $mixedCoregTypes) ? 1 : $value;
                if (isset($mcLists[$type_id])) {
                    if ($type_id == 1) {
                        if ($mcLimit > 0) {
                            $chunks = array_chunk($mcLists[$type_id][0], $mcLimit);
                            // \Log::info('chunk');
                            // \Log::info($mcLimit);
                            // \Log::info($mcLists[$type_id][0]);
                            // \Log::info($chunks);
                            // \Log::info(count($chunks));
                            $the_path = array_merge($the_path, $chunks);

                            for ($ctc = 0; $ctc < count($chunks); $ctc++) {
                                $has_campaign_type[] = $type_id;
                            }
                        } else {
                            $has_campaign_type[] = $type_id;
                            $the_path[] = $mcLists[1][0];
                        }
                    } else {
                        foreach ($mcLists[$type_id] as $path) {
                            $has_campaign_type[] = $type_id;
                            if ($groupAsOne) {
                                $the_path = array_merge($the_path, $path);
                            } else {
                                $the_path[] = $path;
                            }
                        }
                    }
                }
            }
            // \Log::info($has_campaign_type);
            // \Log::info($the_path);
        } else {
            $the_path = [];
            $path_count = 0;
            $has_campaign_type = [];
            foreach ($stack_type_order as $value) {
                //$stack_path[$value] = $stack[$value];
                if (isset($stack[$value])) {
                    $path_count += count($stack[$value]);
                    foreach ($stack[$value] as $path) {
                        $has_campaign_type[] = $value;
                        if ($groupAsOne) {
                            $the_path = array_merge($the_path, $path);
                        } else {
                            $the_path[] = $path;
                        }
                    }
                }
            }
        }

        // 'revenue_tracker_id' => 1,
        // 'path_type' => 2,
        // 'stacking' => 'Default stacking',
        // 'ordering' => 'Proirity ordering',
        // 'limit' => 'Campaign Type limit',

        if (count($the_path) == 0) {
            $this->sendWarningEmail();
        }

        /*
            user
            campaigns
            creatives
            campaign_types
        */
        $response = [
            'campaigns' => $the_path,
            'user' => $user,
            'campaign_types' => $has_campaign_type,
            'creatives' => $campaign_creatives,
            'path_type' => $path_type,
        ];

        return response()->json($response);
    }

    public function saveLeadUser(Request $request): JsonResponse
    {
        $params = $request->all();
        // \Log::info($params);
        //Get Revenue Tracker
        $revenue_tracker = false;

        $revenue_tracker = AffiliateRevenueTracker::where('affiliate_id', $request->affiliate_id)
            ->where('campaign_id', $request->campaign_id)
            ->where('offer_id', $request->offer_id)
            ->select(
                'revenue_tracker_id',
                'path_type',
                'order_status',
                'order_type',
                'crg_limit',
                'ext_limit',
                'lnk_limit',
                'mixed_coreg_campaign_limit',
                'subid_breakdown',
                'sib_s1',
                'sib_s2',
                'sib_s3',
                'sib_s4'
            )
            ->first();

        if (! $revenue_tracker) {
            $revenue_tracker = AffiliateRevenueTracker::where('revenue_tracker_id', 1)->select(
                'revenue_tracker_id',
                'path_type',
                'order_status',
                'order_type',
                'crg_limit',
                'ext_limit',
                'lnk_limit',
                'mixed_coreg_campaign_limit',
                'subid_breakdown',
                'sib_s1',
                'sib_s2',
                'sib_s3',
                'sib_s4'
            )->first();
        }

        if (isset($params['set_affiliate']) && $params['set_affiliate'] == 1) {
            if (isset($params['campaign_id']) && isset($params['offer_id'])) {
                $revenue_tracker->revenue_tracker_id = $revenue_tracker->revenue_tracker_id;
            } else {
                $revenue_tracker->revenue_tracker_id = $request->affiliate_id;
            }
            $revenue_tracker->subid_breakdown = true;
            $revenue_tracker->sib_s1 = true;
            $revenue_tracker->sib_s2 = true;
            $revenue_tracker->sib_s3 = true;
            $revenue_tracker->sib_s4 = true;
            $revenue_tracker->sib_s5 = true;
        }

        // \Log::info($revenue_tracker);

        $revenue_tracker_id = $revenue_tracker ? $revenue_tracker->revenue_tracker_id : 1;
        $ethnic_groups = config('constants.ETHNIC_GROUPS');

        if (isset($params['allow_subid']) && $params['allow_subid'] == 1) {
            $revenue_tracker->subid_breakdown = true;
            $revenue_tracker->sib_s1 = true;
            $revenue_tracker->sib_s2 = true;
            $revenue_tracker->sib_s3 = true;
            $revenue_tracker->sib_s4 = true;
            $revenue_tracker->sib_s5 = true;
        }

        //Get Zip
        $zip = $this->getZipDetails($request->zip);

        //Save User Data
        $user = new LeadUser;
        $user->first_name = $request->first_name == '' ? '' : $request->first_name;
        $user->last_name = $request->last_name == '' ? '' : $request->last_name;
        $user->email = $request->email;
        if ($request->dobyear != '' && $request->dobmonth != '' && $request->dobday != '') {
            $user->birthdate = Carbon::createFromDate($request->dobyear, $request->dobmonth, $request->dobday)->format('Y-m-d');
        } else {
            $user->birthdate = '';
        }
        $user->gender = $request->gender;
        if ($zip) {
            $user->zip = $zip->zip;
            $user->city = $zip->city;
            $user->state = $zip->state;
        }
        $user->ethnicity = 0;
        $user->address1 = $request->address1;
        $user->address2 = $request->user_agent;
        $user->phone = $request->phone1.$request->phone2.$request->phone3;
        $user->affiliate_id = $request->affiliate_id;
        $user->revenue_tracker_id = $revenue_tracker_id;
        $user->ip = $request->ip == '' ? '' : $request->ip;
        $user->is_mobile = $request->screen_view == 1 ? false : true;
        $user->status = $request->email2 == '' ? 0 : 1;
        $user->source_url = $request->source_url;
        $user->s1 = $revenue_tracker->subid_breakdown && $revenue_tracker->sib_s1 && $request->s1 != '' ? $request->s1 : '';
        $user->s2 = $revenue_tracker->subid_breakdown && $revenue_tracker->sib_s2 && $request->s2 != '' ? $request->s2 : '';
        $user->s3 = $revenue_tracker->subid_breakdown && $revenue_tracker->sib_s3 && $request->s3 != '' ? $request->s3 : '';
        $user->s4 = $revenue_tracker->subid_breakdown && $revenue_tracker->sib_s4 && $request->s4 != '' ? $request->s4 : '';
        $user->s5 = $revenue_tracker->subid_breakdown && $revenue_tracker->sib_s5 && $request->s5 != '' ? $request->s5 : '';
        $user->save();

        $response = [
            'user' => $user,
        ];

        return response()->json($response);
    }
}
