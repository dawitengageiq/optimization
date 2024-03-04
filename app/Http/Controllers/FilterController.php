<?php

namespace App\Http\Controllers;

use App\AffiliateCampaign;
use App\AffiliateRevenueTracker;
use App\CakeConversion;
use App\Campaign;
use App\CampaignContent;
use App\CampaignCreative;
use App\CampaignFilterGroup;
use App\CampaignJsonContent;
use App\CampaignTypeReport;
use App\CampaignTypeView;
// use App\CampaignView;
use App\CampaignView;
use App\CampaignViewReport;
use App\FilterType;
use App\Jobs\CampaignNoTracking;
use App\Jobs\SaveCampaignTypeView;
use App\Jobs\SaveCampaignView;
use App\Jobs\SaveLeadUser;
use App\Lead;
use App\LeadCount;
use App\LeadUser;
use App\Path;
use App\Setting;
use App\ZipCode;
use App\ZipMaster;
use Bus;
use Carbon\Carbon;
use Curl\Curl;
use DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Log;
use RandomProbability;
use Session;

class FilterController extends Controller
{
    public function xsession(Request $request)
    {
        //simulate a user
        $user = LeadUser::find(1)->toArray();

        //$request->session()->pull('user', null);
        $this_user = $user;
        $this_user['filter_questions'][3] = 1;

        $now = Carbon::now();
        echo $now->format('l');
        $request->session()->put('user', $this_user);
        $request->session()->put('affiliate', 1);

        return $data = $request->session()->all();
    }

    private function countLeadCapForCampaign($campaign, $start, $end)
    {
        //check if only count success in lead_status
        $lead = Lead::where('campaign_id', $campaign)
            ->whereBetween('created_at', [$start, $end])
            ->where('lead_status', 1)
            ->count();
        // Log::info('LEAD CAP:');
        // Log::info(DB::getQueryLog());
        return $lead;
    }

    public function getFromToDate($value)
    {
        switch ($value) {
            case 'Daily' :
                $date['from'] = Carbon::now()->startOfDay();
                $date['to'] = Carbon::now()->endOfDay();
                break;
            case 'Weekly' :
                $date['from'] = Carbon::now()->startOfWeek();
                $date['to'] = Carbon::now()->endOfWeek();
                break;
            case 'Monthly':
                $date['from'] = Carbon::now()->startOfMonth();
                $date['to'] = Carbon::now()->endOfMonth();
                break;
            case 'Yearly' :
                $date['from'] = Carbon::now()->startOfYear();
                $date['to'] = Carbon::now()->endOfYear();
                break;
            default:
                $date = null;
                break;
        }

        return $date;
    }

    public function sample()
    {
        $user = [
            'affiliate' => 1,
            'dobyear' => 1994,
            'dobmonth' => 10,
            'dobday' => 5,
            'ethnicity' => 2,
            'email' => 'karla@gmail.com',
        ];

        return $this->getCampaignList($user);
    }

    /* LONG PATH */
    public function getCampaignList($user)
    {
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
            ->where('s1', $user['s1'])
            ->where('s2', $user['s2'])
            ->where('s3', $user['s3'])
            ->where('s4', $user['s4'])
            ->where('s5', $user['s5'])
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
                                    if ($filter->filter_type->type == 'profile' && $filter->filter_type->name == 'age') {
                                        //$age = Carbon::parse($user['birthdate'])->age;
                                        $age = Carbon::createFromDate($user['dobyear'], $user['dobmonth'], $user['dobday'])->age;

                                        if ($this->findCampaignFilterValue($filter, $age) == false) {
                                            $filterGroupError++;
                                            break;
                                        }
                                    } elseif ($filter->filter_type->type == 'profile' && $filter->filter_type->name == 'ethnicity') {
                                        $ethnicity = array_search($user['ethnicity'], $ethnic_groups);
                                        // $ethnicity = $ethnic_groups[$user['ethnicity']];
                                        if ($user['ethnicity'] != 0) {
                                            if ($this->findCampaignFilterValue($filter, $ethnicity) == false) {
                                                $filterGroupError++;
                                                break;
                                            }
                                        }
                                    } elseif ($filter->filter_type->type == 'profile' && $filter->filter_type->name == 'gender') {
                                        //Male - M
                                        //Female - F
                                        $gender = $user['gender'] == 'M' ? 'Male' : 'Female';
                                        if ($this->findCampaignFilterValue($filter, $gender) == false) {
                                            $filterGroupError++;
                                            break;
                                        }
                                    } elseif ($filter->filter_type->type == 'profile' && $filter->filter_type->name == 'email') {
                                        $email_split = explode('@', $user['email']);
                                        $domain = explode('.', $email_split[1]);

                                        if ($this->findCampaignFilterValue($filter, $domain[0]) == false) {
                                            $filterGroupError++;
                                            break;
                                        }
                                    } elseif ($filter->filter_type->type == 'profile' && array_key_exists($filter->filter_type->name, $user)) {
                                        if ($this->findCampaignFilterValue($filter, $user[$filter->filter_type->name]) == false) {
                                            $filterGroupError++;
                                            break;
                                        }
                                    } elseif ($filter->filter_type->type == 'question') {
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
                                    } elseif ($filter->filter_type->type == 'custom' && $filter->filter_type->name == 'show_date') {
                                        $date_today = Carbon::now()->format('l');
                                        // Log::info($date_today);
                                        if ($this->findCampaignFilterValue($filter, $date_today) == false) {
                                            $filterGroupError++;
                                            break;
                                        }
                                    } elseif ($filter->filter_type->type == 'custom' && $filter->filter_type->name == 'show_time') {
                                        $time_today = Carbon::now()->format('H:i');
                                        if ($this->findCampaignFilterValue($filter, $time_today) == false) {
                                            $filterGroupError++;
                                            break;
                                        }
                                    } elseif ($filter->filter_type->type == 'custom' && $filter->filter_type->name == 'mobile_view') {
                                        if ($user['screen_view'] == 2) {
                                            $mobile = true;
                                        } else {
                                            $mobile = false;
                                        }
                                        if ($this->findCampaignFilterValue($filter, $mobile) == false) {
                                            $filterGroupError++;
                                            break;
                                        }
                                    } elseif ($filter->filter_type->type == 'custom' && $filter->filter_type->name == 'desktop_view') {
                                        if ($user['screen_view'] == 1) {
                                            $desktop = true;
                                        } else {
                                            $desktop = false;
                                        }
                                        if ($this->findCampaignFilterValue($filter, $desktop) == false) {
                                            $filterGroupError++;
                                            break;
                                        }
                                    } elseif ($filter->filter_type->type == 'custom' && $filter->filter_type->name == 'tablet_view') {
                                        if ($user['screen_view'] == 3) {
                                            $tablet = true;
                                        } else {
                                            $tablet = false;
                                        }
                                        if ($this->findCampaignFilterValue($filter, $tablet) == false) {
                                            $filterGroupError++;
                                            break;
                                        }
                                    } elseif ($filter->filter_type->type == 'custom' && $filter->filter_type->name == 'check_ping') {
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

                    // Link Out Cake click checker
                    if ($campaign->campaign_type == 5 && $filterError == 0) {
                        if (in_array($campaign->linkout_offer_id, $cake_clicks)) {
                            $filterError = 1;
                        }
                    }
                }
                if ($filterError == 0) {

                    /* NOTES */
                    // $coreg_limit = $revenue_tracker->crg_limit;
                    // $external_limit = $revenue_tracker->ext_limit;
                    // $linkout_limit = $revenue_tracker->lnk_limit;
                    // $coreg_counter = 0;
                    // $external_counter = 0;
                    // $linkout_counter = 0;

                    // 'CAMPAIGN_TYPES' => array(
                    //     1  => 'Mixed Co-reg (1st Grp)',
                    //     2  => 'Mixed Co-reg (2nd Grp)',
                    //     3  => 'Long Form Co-reg (1st Grp)',
                    //     4  => 'External Path',
                    //     5  => 'Link Out',
                    //     6  => 'Exit Page',
                    //     7  => 'Long Form Co-reg (2nd Grp)',
                    //     8  => 'Mixed Co-reg (3rd Grp)',
                    //     9  => 'Simple Co-reg',
                    //     10 => 'Custom Co-reg'
                    // )

                    if ($campaign->campaign_type == 4) { //External Path
                        if ($external_limit == null) {
                            $passedCampaigns[] = $campaign->id;
                        } elseif ($external_counter < $external_limit) {
                            $passedCampaigns[] = $campaign->id;
                            $external_counter++;
                        }
                    } elseif ($campaign->campaign_type == 5) { //Link Out
                        if ($linkout_limit == null) {
                            $passedCampaigns[] = $campaign->id;
                        } elseif ($linkout_counter < $linkout_limit) {
                            $passedCampaigns[] = $campaign->id;
                            $linkout_counter++;
                        }
                    } elseif ($campaign->campaign_type == 6) { //Exit Page
                        if ($exit_counter < $exit_limit) {
                            $passedCampaigns[] = $campaign->id;
                            $exit_counter++;
                        }
                    } else {
                        if ($coreg_limit == null) { //Co-reg
                            $passedCampaigns[] = $campaign->id;
                        } elseif ($coreg_counter < $coreg_limit) {
                            $passedCampaigns[] = $campaign->id;
                            $coreg_counter++;
                        }
                    }
                }
            }

            /*** TIMER ***/ $time9 = microtime(true);
            /*** OVERALL TIMER: CAMPAIGN FILTER ***/ $campaign_filter_total += $time9 - $time8;

        }

        /*** EXEC TIME ***/
        $exec_time['get_active_campaign'] = $time2 - $time1;
        $exec_time['get_duplicates'] = $time3 - $time2;
        $exec_time['check_duplicate'] = $lead_duplicate_total;
        $exec_time['check_cap'] = $lead_cap_total;
        $exec_time['check_campaign_filter'] = $campaign_filter_total;
        Log::info('/*** EXEC TIME: LONG PATH ***/', $exec_time);

        return $passedCampaigns;
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

    public function checkPing($filter, $user)
    {
        $url = $filter->filter_group->campaign->config->ping_url;
        $success = $filter->filter_group->campaign->config->ping_success;

        if (strpos($url, '[VALUE_EMAIL]') !== false) {
            $url = str_replace('[VALUE_EMAIL]', $user['email'], $url);
        }

        $curl = new Curl();
        $curl->setopt(CURLOPT_RETURNTRANSFER, true);
        $curl->setopt(CURLOPT_TIMEOUT, 5);
        $curl->get($url);
        $content = $curl->response;
        $curl->close();

        $pos = strpos($content, $success);

        // Log::info('CHECK PING: '.$url.' - '.$success.' - Email:'. $user['email'].' - '.$content.' - Position:'.$pos);

        if ($pos !== false) {
            return true;
        } else {
            return false;
        }
    }

    public function returnCampaignById($id)
    {
        $campaign = Campaign::find($id);
        if ($campaign->content) {
            $content = $campaign->content->long;
        } else {
            $content = 'NONE';
        }
        $data['content'] = $content;

        return $data;
    }

    public function getRevenueTrackerID($affiliate, $campaign, $offer, $s1, $s2, $s3, $s4, $s5)
    {
        $tracker = AffiliateRevenueTracker::select('revenue_tracker_id')
            ->where('affiliate_id', $affiliate)
            ->where('campaign_id', $campaign)
            ->where('offer_id', $offer)
            // ->where('s1',$s1)
            // ->where('s2',$s2)
            // ->where('s3',$s3)
            // ->where('s4',$s4)
            // ->where('s5',$s5)
            ->first();

        return $tracker ? $tracker->revenue_tracker_id : 1;
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

    /**
     * AJAX Version of getting and displaying of Campaign Content
     *
     * @return string
     */
    public function campaignProcessor(Request $request)
    {
        $id = $request->input('id');
        $campaign = $this->returnCampaignById($id);

        return $request->input('callback').'('.json_encode($campaign).');';
    }

    /**
     * Curl Version of getting and displaying of Campaign Content
     *
     * @return string
     */
    public function campaignProcessorPhp(Request $request)
    {
        $id = $request->input('id');

        $campaign = Campaign::find($id);

        if (isset($campaign) && $campaign->content) {
            $content = $campaign->content->long;
            // $open_pos = strpos($content, '<form');
            // $close_pos = strpos($content, '>', $open_pos);
            // $content = substr_replace($content, $required_string, $close_pos + 1, 0);
        } else {
            $content = ['message' => 'Campaign does not exists.'];
        }

        return $content;
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

    //Curl Version of getting and displaying of STACK Campaign Content
    public function campaignStackProcessorPhp(Request $request)
    {
        // DB::enableQueryLog();
        $inputs = $request->all();
        // Log::info($inputs);
        $campaigns = isset($inputs['campaigns']) ? $inputs['campaigns'] : [];
        $affiliate_id = isset($inputs['affiliate_id']) ? $inputs['affiliate_id'] : '';
        $session = isset($inputs['session']) ? $inputs['session'] : '';
        $path = isset($inputs['path']) ? $inputs['path'] : '';
        $s1 = isset($inputs['s1']) ? $inputs['s1'] : '';
        $s2 = isset($inputs['s2']) ? $inputs['s2'] : '';
        $s3 = isset($inputs['s3']) ? $inputs['s3'] : '';
        $s4 = isset($inputs['s4']) ? $inputs['s4'] : '';
        $s5 = isset($inputs['s5']) ? $inputs['s5'] : '';
        $return_str = '';
        $cntr = 0;

        if (empty($campaigns) || is_null($campaigns)) {
            return ['message' => 'Campaign does not exists.'];
        }

        /* GET CAMPAIGN TYPE */
        $campaign_type = null;
        foreach ($campaigns as $c) {
            $cmp = Campaign::select('campaign_type')->where('id', '=', $c)->first();
            if ($cmp != null) {
                $campaign_type = $cmp->campaign_type;
                break;
            }
        }

        if ($campaign_type == null) {
            return ['message' => 'Campaign does not exists.'];
        }
        // Log::info($campaign_type . ' - TYPE');

        /* Campaign Reordering View Tracking */
        $rs = Setting::whereIn('code', ['campaign_reordering_status', 'mixed_coreg_campaign_reordering_status'])->pluck('integer_value', 'code');
        $rtSetting = AffiliateRevenueTracker::where('revenue_tracker_id', $affiliate_id)->select(['mixed_coreg_order_status', 'order_status', 'subid_breakdown', 'sib_s1', 'sib_s2', 'sib_s3', 'sib_s4'])->first();

        if ($rtSetting && $rtSetting->subid_breakdown == 0) { //Check if rev tracker allowed for subid breakdown
            $s1 = '';
            $s2 = '';
            $s3 = '';
            $s4 = '';
            $s5 = '';
        } else {
            if ($rtSetting && $rtSetting->sib_s1 == 0) {
                $s1 = '';
            }
            if ($rtSetting && $rtSetting->sib_s2 == 0) {
                $s2 = '';
            }
            if ($rtSetting && $rtSetting->sib_s3 == 0) {
                $s3 = '';
            }
            if ($rtSetting && $rtSetting->sib_s4 == 0) {
                $s4 = '';
            }
            $s5 = '';
        }

        //Check Mixed Coreg Reorder Global Settings
        $mcReorder = false;
        if (env('EXECUTE_REORDER_MIXED_COREG_CAMPAIGNS', false) || env('EXECUTE_DAILY_REORDER_MIXED_COREG_CAMPAIGNS', false)) {
            if (isset($rs['mixed_coreg_campaign_reordering_status']) && $rs['mixed_coreg_campaign_reordering_status'] == 1) {
                $mcReorder = true;
            }
        }
        //Check Campaign Type Reorder Global Settings
        $ctReorder = false;
        if (env('EXECUTE_REORDER_CAMPAIGNS', false)) {
            if (isset($rs['campaign_reordering_status']) && $rs['campaign_reordering_status'] == 1) {
                $ctReorder = true;
            }
        }
        //Check Rev Tracker Settings Level
        if ($mcReorder || $ctReorder) {
            if ($rtSetting) {
                if ($rtSetting->mixed_coreg_order_status != 1) {
                    $mcReorder = false;
                }
                if ($rtSetting->order_status != 1) {
                    $ctReorder = false;
                }
            }
        }

        /* For Creatives */
        $creative_ids = [];
        $campaign_creatives = [];
        if (isset($inputs['creatives']) && count($inputs['creatives']) > 0) {
            $creative_ids = $inputs['creatives'];
            $stack_creatives = CampaignCreative::whereIn('id', $creative_ids)->select('id', 'image', 'description')->get();
            foreach ($stack_creatives as $c) {
                $campaign_creatives[$c->id]['image'] = $c->image;
                $campaign_creatives[$c->id]['description'] = $c->description;
            }
        }

        $campaigns_types_that_stacked = [1, 2, 5, 8, 9, 10, 11, 12, 13];
        $not_coreg_campaigns = [4, 5, 6];

        // Log::info($campaigns);
        $stack_contents = CampaignContent::whereIn('id', $campaigns)->pluck('stack', 'id');
        foreach ($campaigns as $id) {
            $content = isset($stack_contents[$id]) ? $stack_contents[$id] : '';
            $return_str .= '<!-------------- Campaign: '.$id.'-------------->';
            $creative_id = null;

            /* For Creative Rotation */
            if (isset($creative_ids[$id])) { //Sent a creative id. Probably for previewing a content
                $creative_id = $creative_ids[$id]; //get campaign's creative
                $this_creative = isset($campaign_creatives[$creative_id]) ? $campaign_creatives[$creative_id] : null; //Get campaign creative's image and description
                $stack_content = $this->getCampaignCreativeContent($content, $creative_id, $this_creative);
            } else {
                //check if has [VALUE_CREATIVE_DESCRIPTION] OR [VALUE_CREATIVE_IMAGE]
                if (strpos($content, '[VALUE_CREATIVE_DESCRIPTION]') !== false || strpos($content, '[VALUE_CREATIVE_IMAGE]') !== false) {
                    $stack_content = '';
                } else {
                    $stack_content = $content;
                }
            }

            /* For Path ID: ADD PATH ID*/
            if (! in_array($campaign_type, $not_coreg_campaigns) && $stack_content != '') { //if campaign is coreg
                $stack_content = $this->addPathIdFieldInForm($stack_content);
            }

            $return_str .= $stack_content;
            if (in_array($campaign_type, $campaigns_types_that_stacked) && $stack_content != '') {
                $return_str .= '<div class="separator" style=""></div>';
            }

            // Count Campaign View
            if ($stack_content != '' && $affiliate_id != '' && $session != '') {

                $view_details = [
                    'campaign_id' => $id,
                    'affiliate_id' => $affiliate_id,
                    'session' => $session,
                    'path_id' => $path,
                    'creative_id' => $creative_id,
                    's1' => $s1,
                    's2' => $s2,
                    's3' => $s3,
                    's4' => $s4,
                    's5' => $s5,
                    // 'campaign_type_id'  => $campaign_type
                ];
                // $job = (new SaveCampaignView($view_details))->delay(5);
                // $this->dispatch($job);

                try {
                    CampaignView::create($view_details);

                    if ($mcReorder) {
                        $report = CampaignViewReport::firstOrNew([
                            'campaign_id' => $view_details['campaign_id'],
                            'revenue_tracker_id' => $view_details['affiliate_id'],
                            's1' => $s1,
                            's2' => $s2,
                            's3' => $s3,
                            's4' => $s4,
                            's5' => $s5,
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
                    }

                } catch (QueryException $exception) {
                    // $errorCode = $exception->errorInfo[1];
                    // Log::info('Campaign View insert error code: '.$errorCode);
                    // Log::info('Error message: '.$exception->getMessage());
                }

                $cntr++;
            }
        }

        // $campaign_type_job = (new SaveCampaignTypeView($campaign_type, $affiliate_id, $session, Carbon::now()))->delay(5);
        // $this->dispatch($campaign_type_job);

        if ($ctReorder) {
            try {
                CampaignTypeView::create([
                    'campaign_type_id' => $campaign_type,
                    'revenue_tracker_id' => $affiliate_id,
                    'session' => $session,
                    'timestamp' => Carbon::now(),
                ]);

                $report = CampaignTypeReport::firstOrNew([
                    'campaign_type_id' => $campaign_type,
                    'revenue_tracker_id' => $affiliate_id,
                    's1' => $s1,
                    's2' => $s2,
                    's3' => $s3,
                    's4' => $s4,
                    's5' => $s5,
                ]);

                if ($report->exists) {
                    $report->views = $report->views + 1;
                } else {
                    $report->views = 1;
                }

                $report->save();
            } catch (QueryException $e) {
                // Log::info($e->getMessage());
            }
        }

        if (in_array($campaign_type, $campaigns_types_that_stacked)) {
            $return_str .= '<div align="center"><button id="submit_stack_button" type="button" class="submit_button_form" align="absmiddle">Submit</button></div>';
        }

        // Log::info(DB::getQueryLog());
        return $return_str;
    }

    /*
    ** Replace Stack Creative with Creative Content
    */
    public function getCampaignCreativeContent($content, $creative_id, $creative)
    {
        if ($creative) {
            $html = $content;
            //Replace with creative description
            if (strpos($html, '[VALUE_CREATIVE_DESCRIPTION]') !== false) {
                $html = str_replace('[VALUE_CREATIVE_DESCRIPTION]', $creative['description'], $html);
            }
            //Replace with creative image
            if (strpos($html, '[VALUE_CREATIVE_IMAGE]') !== false) {
                $html = str_replace('[VALUE_CREATIVE_IMAGE]', $creative['image'], $html);
            }
            //Replace with creative id
            if (strpos($html, '[VALUE_CREATIVE_ID]') !== false) {
                $html = str_replace('[VALUE_CREATIVE_ID]', $creative_id, $html);
            } else { //If no creative id, add
                if (strpos($html, '<!-- Standard Required Data END-->') !== false) {
                    $form_array = explode('<!-- Standard Required Data END-->', $html);
                    $form_array[0] .= '<input type="hidden" name="eiq_creative_id" value="'.$creative_id.'"/>';
                    $form_array[0] .= '<!-- Standard Required Data END-->';
                    $html = implode(' ', $form_array);
                } else {
                    $form_array = explode('</form>', $html);
                    $form_array[0] .= '<input type="hidden" name="eiq_creative_id" value="'.$creative_id.'"/>';
                    $form_array[0] .= '</form>';
                    $html = implode(' ', $form_array);
                }
            }
        } else {
            $html = '';
        }

        return $html;
    }

    /*
    ** Replace Stack Creative with Creative Content by $id -> creative id
    */
    public function getStackCreativeById($stack, $campaign_id, $creative_id)
    {
        // $creative = CampaignCreative::find($id);
        $creative = CampaignCreative::where('id', $creative_id)->where('campaign_id', $campaign_id)->first();
        if ($creative) {
            $html = $stack;
            //Replace with creative description
            if (strpos($html, '[VALUE_CREATIVE_DESCRIPTION]') !== false) {
                $html = str_replace('[VALUE_CREATIVE_DESCRIPTION]', $creative->description, $html);
            }
            //Replace with creative image
            if (strpos($html, '[VALUE_CREATIVE_IMAGE]') !== false) {
                $html = str_replace('[VALUE_CREATIVE_IMAGE]', $creative->image, $html);
            }
            //Replace with creative id
            if (strpos($html, '[VALUE_CREATIVE_ID]') !== false) {
                $html = str_replace('[VALUE_CREATIVE_ID]', $creative_id, $html);
            } else { //If no creative id, add
                if (strpos($html, '<!-- Standard Required Data END-->') !== false) {
                    $form_array = explode('<!-- Standard Required Data END-->', $html);
                    $form_array[0] .= '<input type="hidden" name="eiq_creative_id" value="'.$creative_id.'"/>';
                    $form_array[0] .= '<!-- Standard Required Data END-->';
                    $html = implode(' ', $form_array);
                } else {
                    $form_array = explode('</form>', $html);
                    $form_array[0] .= '<input type="hidden" name="eiq_creative_id" value="'.$creative_id.'"/>';
                    $form_array[0] .= '</form>';
                    $html = implode(' ', $form_array);
                }
            }

            // /* LOAD STACK CONTENT */
            // $doc = new \DOMDocument();
            // libxml_use_internal_errors(true);
            // $doc->loadHTML($stack);
            // $finder = new \DomXPath($doc);

            // /* GET AND REPLACE CREATIVE IMAGE */
            // $classname="content-creative-image";
            // $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
            // foreach ($nodes as $tag) {
            //     $tag->setAttribute('src',$creative->image);
            // }

            // /*GET AND REPLACE CREATIVE CONTENT */
            // $classname="content-creative-description";
            // $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
            // foreach ($nodes as $tag) {
            //     $tag->nodeValue = '[VALUE_CREATIVE_DESCRIPTION]';
            // }
            // $html = $doc->saveHTML();
            // if(strpos($html,'[VALUE_CREATIVE_DESCRIPTION]') !== false) $html = str_replace('[VALUE_CREATIVE_DESCRIPTION]', $creative->description , $html);
        } else {
            $html = '';
            // $html = $stack;
        }

        return $html;
    }

    /*
    ** ADD PATH ID IN CAMPAIGN
    */
    public function addPathIdFieldInForm($html)
    {
        if (strpos($html, '[VALUE_PATH_ID]') !== false) {
            //Not do anything
        } else { //If no path id shortcode, add
            if (strpos($html, '<!-- Standard Required Data END-->') !== false) {
                $form_array = explode('<!-- Standard Required Data END-->', $html);
                $form_array[0] .= '<input type="hidden" name="eiq_path_id" value="[VALUE_PATH_ID]"/>';
                $form_array[0] .= '<!-- Standard Required Data END-->';
                $html = implode(' ', $form_array);
            } else {
                $form_array = explode('</form>', $html);
                $form_array[0] .= '<input type="hidden" name="eiq_path_id" value="[VALUE_PATH_ID]"/>';
                $form_array[0] .= '</form>';
                $html = implode(' ', $form_array);
            }
        }

        return $html;
    }

    /* HPCS */
    public function highPayingCampaignListProcessor(Request $request)
    {
        $affiliate_id = $request->input('affiliate_id');

        $revenue_tracker = AffiliateRevenueTracker::select('revenue_tracker_id', 'path_type')
            ->where('affiliate_id', $affiliate_id)
            ->where('campaign_id', $request->input('campaign_id'))
            ->where('offer_id', $request->input('offer_id'))
            ->where('s1', $request->input('s1'))
            ->where('s2', $request->input('s2'))
            ->where('s3', $request->input('s3'))
            ->where('s4', $request->input('s4'))
            ->where('s5', $request->input('s5'))
            ->first();
        $revenue_tracker_id = $revenue_tracker ? $revenue_tracker->revenue_tracker_id : 1;

        //Path Types
        // 1 => 'Long Path',
        // 2 => 'Stack Path',
        $path_type = $revenue_tracker ? $revenue_tracker->path_type : 1;

        //INSERT USER
        $ethnic_groups = config('constants.ETHNIC_GROUPS');
        $dob = Carbon::createFromDate($request->input('dobyear'), $request->input('dobmonth'), $request->input('dobday'))->format('Y-m-d');

        $zip = $request->input('zip');
        //get city and state by zip
        // $zip_details = ZipMaster::select('city','state')->where('zip',$zip)->first();
        $zip_details = ZipCode::select('city', 'state')->where('zip', $zip)->first();

        $user = new LeadUser();
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

        //RETURN CAMPAIGN LIST
        $usr_data = $_GET;
        $usr_data['state'] = $zip_details->state;
        $usr_data['city'] = $zip_details->city;

        if ($path_type == 1) {
            $return['campaigns'] = $this->getCampaignList($usr_data);
        } else {
            $return['campaigns'] = $this->getCampaignStackList($usr_data);
        }

        $return['id'] = $user_id;
        $return['user'] = $_GET;
        $return['user']['city'] = $zip_details->city;
        $return['user']['state'] = $zip_details->state;
        $return['affiliate_id'] = $usr_data['affiliate_id'];
        $return['offer_id'] = $usr_data['offer_id'];
        $return['campaign_id'] = $usr_data['campaign_id'];
        $return['revenue_tracker_id'] = $revenue_tracker_id;

        return $request->input('callback').'('.json_encode($return).');';
    }

    protected function getHighPayingContent($id)
    {
        $campaign = Campaign::find($id);

        if ($campaign->content) {
            $content = $campaign->content->high_paying;
        } else {
            $content = 'NONE';
        }

        return $content;
    }

    /**
     * Curl Version
     *
     * @return string
     */
    public function getHighPayingContentCurl(Request $request)
    {
        $id = $request->input('id');
        $campaign = $this->getHighPayingContent($id);

        return $campaign;
    }

    /**
     * HighPayingContent AJAX Version
     *
     * @return string
     */
    public function getHighPayingContentAjax(Request $request)
    {
        $id = $request->input('id');
        $campaign = $this->getHighPayingContent($id);

        return $request->input('callback').'('.json_encode($campaign).');';
    }

    /**
     * Zip
     *
     * @return string
     */
    public function zipValidationChecker(Request $request)
    {
        $zip = $request->input('zip');

        // $details = ZipMaster::select('city','state')->where('zip',$zip)->first();
        $details = ZipCode::select('city', 'state')->where('zip', $zip)->first();

        if (is_null($details)) {
            $checker = 'Please provide a valid US Zip Code';
        } else {
            $checker = 'true';
        }

        // Log::info($zip .' - '. $checker);
        // return $checker;

        return $request->input('callback').'('.json_encode($checker).');';
    }

    /**
     * Zip Details
     *
     * @return string
     */
    public function getZipDetails(Request $request)
    {
        $zip = $request->input('zip');

        // $details = ZipMaster::select('city','state')->where('zip',$zip)->first();
        $details = ZipCode::select('city', 'state')->where('zip', $zip)->first();

        return $request->input('callback').'('.json_encode($details).');';
    }

    /**
     * Register survey taker and return campaign list
     *
     * @return array
     */
    public function registerUserAndGetCampaigns(Request $request)
    {
        $user_details = $request->all();
        $return = [];

        /*IDENTIFY REVENUE_TRACKER_ID*/
        $affiliate_id = $user_details['affiliate_id'];
        $revenue_tracker = AffiliateRevenueTracker::select('revenue_tracker_id', 'path_type')
            ->where('affiliate_id', $affiliate_id)
            ->where('campaign_id', $user_details['campaign_id'])
            ->where('offer_id', $user_details['offer_id'])
            ->where('s1', $user_details['s1'])
            ->where('s2', $user_details['s2'])
            ->where('s3', $user_details['s3'])
            ->where('s4', $user_details['s4'])
            ->where('s5', $user_details['s5'])
            ->first();
        //If revenue tracker not exists, set to 1
        $revenue_tracker_id = $revenue_tracker ? $revenue_tracker->revenue_tracker_id : 1;
        $user_details['revenue_tracker_id'] = $revenue_tracker_id;

        /*IDENTIFY PATH TYPE*/ //Path Types: 1 => Long Path, 2 => Stack Path
        //If revenue tracker not exists, get default rev tracker path type
        if ($revenue_tracker) {
            $path_type = $revenue_tracker->path_type;
        } else {
            $default_rev_tracker = AffiliateRevenueTracker::select('path_type')->where('revenue_tracker_id', 1)->first();
            //If default rev tracker not exists, set to 2
            $path_type = $default_rev_tracker ? $default_rev_tracker->path_type : 2;
        }

        /*GET ZIP DETAILS*/
        $zip = $user_details['zip'];
        $zip_details = ZipCode::select('city', 'state')->where('zip', $zip)->first();
        $user_details['state'] = $zip_details->state;
        $user_details['city'] = $zip_details->city;

        /*INSERT USER*/
        $job = (new SaveLeadUser($user_details))->delay(5);
        $this->dispatch($job);
        // $user = new LeadUser;
        // $user->first_name = $user_details['first_name'];
        // $user->last_name = $user_details['last_name'];
        // $user->email = $user_details['email'];
        // $user->birthdate = Carbon::createFromDate($user_details['dobyear'], $user_details['dobmonth'], $user_details['dobday'])->format('Y-m-d');
        // $user->gender = $user_details['gender'];
        // $user->zip = $zip;
        // $user->city = $zip_details->city;
        // $user->state = $zip_details->state;
        // $user->ethnicity = 0;
        // $user->address1 = $user_details['address'];
        // $user->phone = $user_details['phone1'].$user_details['phone2'].$user_details['phone3'];
        // $user->source_url = $user_details['source_url'];
        // $user->affiliate_id = $affiliate_id;
        // $user->revenue_tracker_id = $revenue_tracker_id;
        // $user->ip = $user_details['ip'];
        // $user->is_mobile = $user_details['screen_view'] == 1 ? false : true;
        // $user->status = 0;
        // $user->save();
        // $user_id = $user->id;

        /*PREPARE RETURN DATA*/
        // $return['id'] = $user_id;
        $return['user'] = $user_details;
        $return['revenue_tracker_id'] = $revenue_tracker_id;
        $return['path_type'] = $path_type;

        /*GET CAMPAIGN LIST*/
        // DB::enableQueryLog();
        $qualified_campaigns = $this->getQualifiedCampaigns($user_details, $revenue_tracker, $path_type);
        $return['campaigns'] = $qualified_campaigns;
        // Log::info(DB::getQueryLog());
        /*GET CREATIVES*/
        $creatives = [];
        foreach ($qualified_campaigns as $page) {
            foreach ($page as $cid) {
                $creative_id = null;
                //Get Campaign Creatives if any and get Creative ID by random probability
                $campCreatives = CampaignCreative::where('campaign_id', $cid)->where('weight', '!=', 0)->pluck('weight', 'id')->toArray();
                if ($campCreatives) {
                    $creative_id = Bus::dispatch(new RandomProbability($campCreatives));
                }
                if ($creative_id != null) {
                    $creatives[$cid] = $creative_id;
                } //Save creative id of campaign
            }
        }
        $return['creatives'] = $creatives;

        return $return;
    }

    /**
     * Return Qualified Campaigns
     *
     * @param  Request  $request
     * @return array
     */
    public function getQualifiedCampaigns($user, $revenue_tracker, $path_type)
    {
        /*** For Testing ***/ /*** OVERALL TIME ***/
        /*** For Testing ***/ /*** TIMER ***/ $time = microtime(true);
        /*** For Testing ***/ $lead_duplicate_total = 0;
        $lead_cap_total = 0;
        $campaign_filter_total = 0;
        $cake_checker_total = 0;
        $final = 0;

        /* INITIALIZE VARIABLES */
        $revenue_tracker_id = $revenue_tracker ? $revenue_tracker->revenue_tracker_id : 1;
        $coreg_limit = $revenue_tracker ? $revenue_tracker->crg_limit : null;
        $external_limit = $revenue_tracker ? $revenue_tracker->ext_limit : null;
        $linkout_limit = $revenue_tracker ? $revenue_tracker->lnk_limit : null;
        $exit_limit = 1;
        $coreg_counter = 0;
        $external_counter = 0;
        $linkout_counter = 0;
        $exit_counter = 0;
        $filter_types = $this->getFilterTypes();
        $lead_cap_types = config('constants.LEAD_CAP_TYPES');
        $filter_process_status = Setting::select('integer_value')->where('code', 'campaign_filter_process_status')->first()->integer_value;
        $qualifiedCampaigns = [];
        if ($path_type == 2) {
            /* For Stack Path */
            $stack_num_campaign = Setting::where('code', 'num_campaign_per_stack_page')->first()->integer_value;
            $stack_type_order = Setting::where('code', 'stack_path_campaign_type_order')->first()->string_value;
            $stack_type_order = json_decode($stack_type_order, true);
            $stack = [];
        } else {
            $passedCampaigns = [];
        }

        /*** For Testing ***/ /*** TIMER ***/ $time1 = microtime(true);

        ///* GET ACTIVE CAMPAIGNS */
        // $campaigns = Campaign::where('status','!=',0)->where('status','!=',3)->orderBy('priority','asc')->get();
        /* GET ACTIVE CAMPAIGNS AND ITS LEAD COUNT */
        $campaigns = Campaign::where('status', '!=', 0)->where('status', '!=', 3)
            ->select('id', 'name', 'advertiser_id', 'status', 'lead_cap_type', 'lead_cap_value', 'default_received', 'default_payout', 'priority', 'campaign_type', 'linkout_offer_id',
                // DB::raw('(SELECT count FROM lead_counts WHERE campaign_id = campaigns.id AND affiliate_id IS NULL) AS lead_count'))
                DB::raw('(CASE WHEN campaign_type = 5 THEN (SELECT count FROM link_out_counts WHERE campaign_id = campaigns.id AND affiliate_id IS NULL) ELSE (SELECT count FROM lead_counts WHERE campaign_id = campaigns.id AND affiliate_id IS NULL) END ) AS lead_count'))
            ->orderBy('priority', 'ASC')
            ->get();

        /*** For Testing ***/ /*** TIMER ***/ $time2 = microtime(true);

        /* GET ALL LEADS ANSWERED BY EMAIL */
        $lead_campaigns = Lead::where('lead_email', $user['email'])->whereIn('lead_status', [1, 2, 3, 4, 5])->pluck('campaign_id')->toArray();

        /*** For Testing ***/ /*** TIMER ***/ $time3 = microtime(true);

        /* GET CAKE CONVERSIONS CLICKED BY EMAIL */
        $cake_clicks = CakeConversion::where('sub_id_5', $user['email'])->pluck('offer_id')->toArray();

        /*** For Testing ***/ /*** TIMER ***/ $time4 = microtime(true);

        /* GO THROUGH EACH CAMPAIGN TO CHECK IF THEY QUALIFY */
        foreach ($campaigns as $campaign) {
            /*** For Testing ***/ /*** TIMER ***/ $time5 = microtime(true);

            /* CHECK IF USER ALREADY ANSWERED CAMPAIGN */
            if (in_array($campaign->id, $lead_campaigns)) {
                continue;
            } //If already answered, skip campaign

            /*** For Testing ***/ /*** TIMER ***/ $time6 = microtime(true);
            /*** For Testing ***/ /*** OVERALL TIMER: LEAD DUPLICATE ***/ $lead_duplicate_total += $time6 - $time5;

            /* CHECK EACH CAMPAIGN CAP IF REACHED */
            $uniCapPassed = true;
            $affCapPassed = true;

            /* CHECK DEFAULT CAMPAIGN CAP */
            if ($campaign->lead_cap_type != 0) { //if default cap is not unlimited or 0, check campaign lead counts
                $totalCampaignLeadCount = $campaign->lead_count == null ? 0 : $campaign->lead_count;
                if ($totalCampaignLeadCount) {
                    //if default lead cap value less than or equal to lead count, FAILED
                    if ($campaign->lead_cap_value <= $totalCampaignLeadCount) {
                        $uniCapPassed = false;
                    }
                }/* else {
                    // lead count doesn't exists therefore, campaign has no count. PASSED
                    $uniCapPassed = true;
                }*/
            }
            if ($uniCapPassed == false) {
                continue;
            } //If default campaign cap failed, skip campaign

            /* CHECK AFFILIATE CAMPAIGN CAP */
            if ($campaign->campaign_type == 5) {
                $leadCountQry = '(SELECT count FROM link_out_counts WHERE campaign_id = affiliate_campaign.campaign_id AND affiliate_id = affiliate_campaign.affiliate_id) AS lead_count';
            } else {
                $leadCountQry = '(SELECT count FROM lead_counts WHERE campaign_id = affiliate_campaign.campaign_id AND affiliate_id = affiliate_campaign.affiliate_id) AS lead_count';
            }

            $affiliateCampaignCap = AffiliateCampaign::where('campaign_id', $campaign->id)->where('affiliate_id', $revenue_tracker_id)
                ->select('lead_cap_type', 'lead_cap_value', DB::raw($leadCountQry))
                ->first();

            if ($campaign->status == 1 || $affiliateCampaignCap) { //If campaign is private
                if ($affiliateCampaignCap && $uniCapPassed) { //check if affiliate campaign exists and default campaign cap is passed
                    if ($affiliateCampaignCap->lead_cap_type != 0) { // if cap != unlimited, then check
                        //get affiliate lead count for campaign
                        // $totalAffCmpLeadCount = LeadCount::where('campaign_id',$campaign->id)->where('affiliate_id',$revenue_tracker_id)->first();
                        $totalAffCmpLeadCount = $affiliateCampaignCap->lead_count == null ? 0 : $affiliateCampaignCap->lead_count;
                        if ($totalAffCmpLeadCount > 0) {
                            if ($affiliateCampaignCap->lead_cap_value <= $totalAffCmpLeadCount) {
                                $affCapPassed = false;
                            }
                        } else {
                            // lead count doesn't exists therefore, campaign has no count. PASSED
                            $affCapPassed = true;
                        }
                    }
                } else {
                    $affCapPassed = false;
                }
            }

            if ($affCapPassed == false) {
                continue;
            } //If affiliate campaign cap failed, skip campaign

            /*** For Testing ***/ /*** TIMER ***/ $time7 = microtime(true);
            /*** For Testing ***/ /*** OVERALL TIMER: LEAD CAP ***/ $lead_cap_total += $time7 - $time6;

            /* CHECK IF FILTERS ARE MET */
            $filterError = 0;
            if ($uniCapPassed && $affCapPassed) {
                if ($filter_process_status == 1) { // Check campaign filter process' status; If Active(1) proceed with filter, else disregard filters.
                    $filter_groups = $campaign->filter_groups;
                    $filter_groups_pass_checker = [];
                    $campaign_filter_passed = false;

                    if (count($filter_groups) > 0) { //check if campaign has filter groups
                        foreach ($filter_groups as $group) { //go through each filter group
                            $filterGroupError = 0; //filter group checker
                            // if($campaign_filter_passed) break;

                            if ($group->status == 1) { //check if filter group is active
                                $filters = $group->filters;
                                if ($filters) { //check if filter group has filters
                                    foreach ($filters as $filter) { //go through each filter
                                        if ($this->ifFilterPassed($filter, $filter_types, $user) == false) {
                                            $filterGroupError++;
                                            break;
                                        }
                                    }

                                    if ($filterGroupError == 0) {
                                        $campaign_filter_passed = true;
                                    }
                                }
                            }
                            $filter_groups_pass_checker[] = $filterGroupError == 0 ? 1 : 0;

                            if ($campaign_filter_passed) {
                                break;
                            }
                        }

                        if ($campaign_filter_passed || count($filter_groups_pass_checker) == 0 || in_array(1, $filter_groups_pass_checker)) {
                            $filterError = 0;
                        } else {
                            $filterError = 1;
                        }
                    } else {
                        $filterError = 0; //no filter groups = no errors
                    }
                }
            } else {
                continue; //Go to next campaign
            }

            /*** For Testing ***/ /*** TIMER ***/ $time8 = microtime(true);
            /*** For Testing ***/ /*** OVERALL TIMER: CAMPAIGN FILTER ***/ $campaign_filter_total += $time8 - $time7;

            /*LINK OUT CAKE CLICK CHECKER*/
            if ($campaign->campaign_type == 5 && $filterError == 0) {
                if (in_array($campaign->linkout_offer_id, $cake_clicks)) {
                    $filterError = 1;
                }
            }

            /*** For Testing ***/ /*** TIMER ***/ $time9 = microtime(true);
            /*** For Testing ***/ /*** OVERALL TIMER: CAKE CONVERSION ***/ $cake_checker_total += $time9 - $time8;

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

                if ($limitChecker) {
                    if ($path_type == 1) { //Long Path
                        $passedCampaigns[] = $campaign->id;
                    } else { //stack path
                        //Check if stack is not empty
                        //Check Campaign type
                        //Find array with same type of campaign type
                        $campaign_type = $campaign->campaign_type;
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
            } else {
                continue;
            }
            /*** For Testing ***/ /*** TIMER ***/ $time10 = microtime(true);
            /*** For Testing ***/ /*** OVERALL TIMER: CAKE CONVERSION ***/ $final += $time10 - $time9;
        }

        /*** For Testing ***/ /*** TIMER ***/ $time11 = microtime(true);
        if ($path_type == 1) {
            $qualifiedCampaigns = $passedCampaigns;
        } else {
            $path_count = 0;
            foreach ($stack_type_order as $value) {
                //$stack_path[$value] = $stack[$value];
                if (isset($stack[$value])) {
                    $path_count += count($stack[$value]);
                    foreach ($stack[$value] as $path) {
                        $qualifiedCampaigns[] = $path;
                    }
                }
            }
        }
        /*** For Testing ***/ /*** TIMER ***/ $time12 = microtime(true);
        /*** For Testing ***/ /*** OVERALL TIMER: CAKE CONVERSION ***/ $final += $time12 - $time10;

        /*** For Testing ***/ /*** EXEC TIME ***/
        /*** For Testing ***/ $exec_time['init'] = $time1 - $time;
        /*** For Testing ***/ $exec_time['get_active_campaign'] = $time2 - $time1;
        /*** For Testing ***/ $exec_time['get_duplicates'] = $time3 - $time2;
        /*** For Testing ***/ $exec_time['check_duplicate'] = $lead_duplicate_total;
        /*** For Testing ***/ $exec_time['get_cake'] = $time4 - $time3;
        /*** For Testing ***/ $exec_time['check_cake_conversions'] = $cake_checker_total;
        /*** For Testing ***/ $exec_time['check_cap'] = $lead_cap_total;
        /*** For Testing ***/ $exec_time['check_campaign_filter'] = $campaign_filter_total;
        /*** For Testing ***/ $exec_time['finalizations'] = $final;
        /*** For Testing ***/ $exec_time['email'] = $user['email'];
        /*** For Testing ***/ Log::info('/*** EXEC TIME ***/', $exec_time);

        return $qualifiedCampaigns;

    }

    private function getFilterTypes()
    {
        $filter_types = FilterType::select('id', 'type', 'name')->get();
        $filters = [];
        foreach ($filter_types as $type) {
            $filters[$type->id]['type'] = $type->type;
            $filters[$type->id]['name'] = $type->name;
        }

        return $filters;
    }

    private function ifFilterPassed($filter, $filter_types, $user)
    {
        $filter_id = $filter->filter_type_id;
        $filter_type = strtolower($filter_types[$filter_id]['type']); //get filter type
        $filter_name = strtolower($filter_types[$filter_id]['name']); //get filter name

        $return_value = true;
        $answer = '';

        if ($filter_type == 'profile') {
            switch ($filter_name) {
                case 'age' :
                    $answer = Carbon::createFromDate($user['dobyear'], $user['dobmonth'], $user['dobday'])->age;
                    break;
                case 'ethnicity':
                    $ethnic_groups = config('constants.ETHNIC_GROUPS');
                    $ethnicity = array_search($user['ethnicity'], $ethnic_groups);
                    if ($user['ethnicity'] != 0) {
                        $answer = $ethnicity;
                    } // if ethnicity is not 0, if 0; answer = ''
                    break;
                case 'gender':
                    //Male - M
                    //Female - F
                    $answer = $user['gender'] == 'M' ? 'Male' : 'Female';
                    break;
                case 'email':
                    $email_split = explode('@', $user['email']);
                    $domain = explode('.', $email_split[1]);
                    $answer = $domain[0];
                    break;
                default:
                    if (array_key_exists($filter_name, $user)) { //check if filter name exists in user details
                        $answer = $user[$filter_name];
                    }
                    break;
            }
        } elseif ($filter_type == 'custom') {
            switch ($filter_name) {
                case 'show_date' :
                    $answer = $date_today = Carbon::now()->format('l');
                    break;
                case 'show_time':
                    $answer = Carbon::now()->format('H:i');
                    break;
                case 'mobile_view':
                    $answer = $user['screen_view'] == 2 ? true : false;
                    break;
                case 'desktop_view':
                    $answer = $user['screen_view'] == 1 ? true : false;
                    break;
                case 'tablet_view':
                    $answer = $user['screen_view'] == 3 ? true : false;
                    break;
                case 'check_ping':
                    $answer = $this->checkPing($filter, $user);
                    break;
                default:
                    $answer = '';
                    break;
            }
        } elseif ($filter_type == 'question') {
            if (! isset($user['filter_questions']) || ! array_key_exists($filter_id, $user['filter_questions'])) {
                return false;
            } else {
                $answer = $user['filter_questions'][$filter_id];
            }
        }

        if ($this->findCampaignFilterValue($filter, $answer) == false) {
            $return_value = false;
        }

        return $return_value;
    }

    //gets campaign content and returns it as an array
    public function getCampaignContentByID(Request $request)
    {
        $campaigns = $request->input('campaigns');
        if (empty($campaigns) || is_null($campaigns)) {
            return ['message' => 'Campaign does not exists.'];
        }

        /* GET CAMPAIGN TYPE */
        $campaign_type = null;
        foreach ($campaigns as $c) {
            $campaign_type = Campaign::find($c);
            if ($campaign_type != null) {
                $campaign_type = $campaign_type->campaign_type;
                break;
            }
        }
        if ($campaign_type == null) {
            return ['message' => 'Campaign does not exists.'];
        }

        /* For Creatives */
        $creatives = [];
        if ($request->has('creatives')) {
            $creatives = $request->input('creatives');
        }

        $campaigns_types_that_stacked = [1, 2, 5, 8, 9, 10, 11, 12];

        $return = [];
        foreach ($campaigns as $id) {
            $campaign = CampaignContent::find($id);
            $stack_content = '';
            if (isset($campaign)) {
                /* For Creative Rotation */
                if (isset($creatives[$id])) { //Sent a creative id. Probably for previewing a content
                    $stack_content = $this->getStackCreativeById($campaign->stack, $id, $creatives[$id]);
                } else {
                    //check if has [VALUE_CREATIVE_DESCRIPTION] OR [VALUE_CREATIVE_IMAGE]
                    if (strpos($campaign->stack, '[VALUE_CREATIVE_DESCRIPTION]') !== false || strpos($campaign->stack, '[VALUE_CREATIVE_IMAGE]') !== false) {
                        $campCreatives = CampaignCreative::where('campaign_id', $id)->where('weight', '!=', 0)->pluck('weight', 'id')->toArray();
                        if ($campCreatives) {
                            $creative_id = Bus::dispatch(new RandomProbability($campCreatives));
                            $stack_content = $this->getStackCreativeById($campaign->stack, $id, $creative_id);
                        } else {
                            $stack_content = '';
                        }
                    } else {
                        $stack_content = $campaign->stack;
                    }
                }
            }

            $return[$id] = $stack_content;
        }

        if (in_array($campaign_type, $campaigns_types_that_stacked)) {
            $return['end'] = '<div align="center"><button id="submit_stack_button" type="button" class="submit_button_form" align="absmiddle">Submit</button></div>';
        } else {
            $return['end'] = '';
        }

        return $return;
    }

    public function getPathAddDetails(Request $request)
    {
        $inputs = $request->all();
        // Log::info($request->fullUrl());
        // Log::info($inputs);

        //Get Path ID
        $path_url = isset($inputs['path']) ? $inputs['path'] : null;
        $path_id = null;
        if ($path_url != null) {
            $path = Path::where('url', $path_url)->first();
            if ($path) {
                $path_id = $path->id;
            } else {
                $path_name = parse_url($path_url, PHP_URL_PATH);
                $path_name = str_replace('/', '', $path_name);
                $path_name = str_replace('_', ' ', $path_name);
                $path_name = ucwords($path_name);
                $path_name = str_replace('/', '', $path_name);
                $new_path = new Path;
                $new_path->name = $path_name;
                $new_path->url = $path_url;
                $new_path->save();
                $path_id = $new_path->id;
            }

        }
        $details['path_id'] = $path_id;
        // Log::info($path_url);
        //Get TCPA
        if (stripos($path_url, 'admiredopinion') !== false) {
            $tcpa = 'admired_tcpa';
        } elseif (stripos($path_url, 'paidforresearch') !== false) {
            $tcpa = 'pfr_tcpa';
        } elseif (stripos($path_url, 'clinicaltrialshelp') !== false) {
            $tcpa = 'clinical_trial_tcpa';
        } else {
            $tcpa = 'epicdemand_tcpa';
        }

        //Get Filter Questions to be displayed in the registration page
        $details['filters'] = FilterType::where('status', 1)->where('type', 'question')->get()->toArray();

        //Get Rev Tracker ID and Pixel
        $affiliate_id = isset($inputs['affiliate_id']) ? $inputs['affiliate_id'] : null;
        $campaign_id = isset($inputs['campaign_id']) ? $inputs['campaign_id'] : null;
        $offer_id = isset($inputs['offer_id']) ? $inputs['offer_id'] : null;

        $revenue_tracker = AffiliateRevenueTracker::select('revenue_tracker_id', 'path_type', 'fire_at', 'pixel', 'pixel_header', 'pixel_body', 'pixel_footer', 'subid_breakdown', 'sib_s1', 'sib_s2', 'sib_s3', 'sib_s4')
            ->where('affiliate_id', $affiliate_id)
            ->where('campaign_id', $campaign_id)
            ->where('offer_id', $offer_id)
            // ->where('s1',$s1)
            // ->where('s2',$s2)
            // ->where('s3',$s3)
            // ->where('s4',$s4)
            // ->where('s5',$s5)
            ->first();

        $allowBreakdown = $revenue_tracker ? $revenue_tracker->subid_breakdown : 0;
        $s1Breakdown = $revenue_tracker ? $revenue_tracker->sib_s1 : 0;
        $s2Breakdown = $revenue_tracker ? $revenue_tracker->sib_s2 : 0;
        $s3Breakdown = $revenue_tracker ? $revenue_tracker->sib_s3 : 0;
        $s4Breakdown = $revenue_tracker ? $revenue_tracker->sib_s4 : 0;
        $s5Breakdown = 0;

        $s1 = isset($inputs['s1']) && $allowBreakdown && $s1Breakdown ? $inputs['s1'] : '';
        $s2 = isset($inputs['s2']) && $allowBreakdown && $s2Breakdown ? $inputs['s2'] : '';
        $s3 = isset($inputs['s3']) && $allowBreakdown && $s3Breakdown ? $inputs['s3'] : '';
        $s4 = isset($inputs['s4']) && $allowBreakdown && $s4Breakdown ? $inputs['s4'] : '';
        $s5 = isset($inputs['s5']) && $allowBreakdown && $s5Breakdown ? $inputs['s5'] : '';

        $revenue_tracker_id = $revenue_tracker ? $revenue_tracker->revenue_tracker_id : 1;
        $fire_at = $revenue_tracker ? $revenue_tracker->fire_at : null;
        $pixel = $revenue_tracker ? $revenue_tracker->pixel : null;

        $details['revenue_tracker_id'] = $revenue_tracker_id;
        $details['s1'] = $s1;
        $details['s2'] = $s2;
        $details['s3'] = $s3;
        $details['s4'] = $s4;
        $details['s5'] = $s5;
        $details['subid_breakdown'] = $allowBreakdown;
        $details['s1_breakdown'] = $s1Breakdown;
        $details['s2_breakdown'] = $s2Breakdown;
        $details['s3_breakdown'] = $s3Breakdown;
        $details['s4_breakdown'] = $s4Breakdown;
        $details['s5_breakdown'] = $s5Breakdown;
        $details['fire_at'] = $fire_at;
        $details['pixel'] = $pixel;
        $details['pixel_header'] = $revenue_tracker ? $revenue_tracker->pixel_header : null;
        $details['pixel_body'] = $revenue_tracker ? $revenue_tracker->pixel_body : null;
        $details['pixel_footer'] = $revenue_tracker ? $revenue_tracker->pixel_footer : null;

        //Get Universal Pixel details
        $settings = Setting::whereIn('code', ['header_pixel', 'body_pixel', 'footer_pixel', $tcpa])->get();
        foreach ($settings as $setting) {
            if ($setting->code == $tcpa) {
                $details['tcpa'] = $setting->description;
            } else {
                $details[$setting->code] = $setting->description;
            }
        }

        //Get Filter-Free Campaigns
        if (isset($inputs['get_filter_free_campaigns']) && $inputs['get_filter_free_campaigns'] == 1) {
            // Log::info('FILTER FREE CAMPAIGNS');

            if (isset($inputs['the_filter_free_campaigns'])
                && count($inputs['the_filter_free_campaigns']) > 0) {

                $campaigns = Campaign::leftJoin('lead_counts', function ($join) {
                    $join->on('campaigns.id', '=', 'lead_counts.campaign_id')
                        ->whereNull('lead_counts.affiliate_id');
                })
                    ->whereIn('campaigns.id', $inputs['the_filter_free_campaigns'])
                    ->orderBy('priority', 'ASC')
                    ->select(['campaigns.id', 'campaigns.name', 'campaigns.lead_cap_type', 'campaigns.lead_cap_value', 'lead_counts.count'])
                    ->get();
            } else {
                $default_limit = 15;
                $limit = isset($inputs['limit']) ? $inputs['limit'] : $default_limit;

                // DB::enableQueryLog();
                $campaigns = Campaign::leftJoin('campaign_filter_groups', 'campaigns.id', '=', 'campaign_filter_groups.campaign_id')
                    ->leftJoin('lead_counts', function ($join) {
                        $join->on('campaigns.id', '=', 'lead_counts.campaign_id')
                            ->whereNull('lead_counts.affiliate_id');
                    })
                    ->whereNull('campaign_filter_groups.campaign_id')->where('campaigns.status', 2)
                    ->whereIn('campaigns.campaign_type', [1, 2, 8, 13])
                    ->orderBy('priority', 'ASC')
                    ->limit($limit)
                    ->select(['campaigns.id', 'campaigns.name', 'campaigns.lead_cap_type', 'campaigns.lead_cap_value', 'lead_counts.count'])
                    ->get();
                // ->pluck('campaigns.id');
            }
            // Log::info(DB::getQueryLog());
            // Log::info($campaigns);
            $creatives = [];
            $campaign_ids = [];
            foreach ($campaigns as $campaign) {
                $capCheck = true;
                $creative_id = null;

                //Cap Checker
                if ($campaign->lead_cap_type > 0) {
                    if ($campaign->lead_cap_value < $campaign->count) {
                        $capCheck = false;
                    }
                }

                if ($capCheck) {
                    $campaign_ids[] = $campaign->id;

                    //Get Campaign Creatives if any and get Creative ID by random probability
                    $campCreatives = CampaignCreative::where('campaign_id', $campaign->id)->where('weight', '!=', 0)->pluck('weight', 'id')->toArray();
                    if ($campCreatives) {
                        $creative_id = Bus::dispatch(new RandomProbability($campCreatives));
                    }

                    if ($creative_id != null) {
                        $creatives[$campaign->id] = $creative_id;
                    } //Save creative id of campaign
                }

            }

            $details['filter_free_campaigns'] = $campaign_ids;
            $details['filter_free_creatives'] = $creatives;
            // Log::info($campaign_ids);
        }

        //Get Filter-Free Campaigns w/ Status and Cap Checker
        if (isset($inputs['get_pre_reg_campaigns']) && $inputs['get_pre_reg_campaigns'] != null) {

            $campaign_type = $inputs['get_pre_reg_campaigns'];
            $limit = isset($inputs['get_pre_reg_campaigns_limit']) ? $inputs['get_pre_reg_campaigns_limit'] : null;

            $details['get_pre_reg_campaigns'] = Campaign::getFilterFreeCampaigns($campaign_type, $revenue_tracker_id, $limit)->pluck('creative_id', 'id')->toArray();
        }

        return $details;
    }

    /**
     * Get a creative for a campaign
     *
     * @return null
     */
    public function getCampaignCreativeID(Request $request)
    {

        $id = $request->input('id');
        $campaign = CampaignContent::find($id);
        // Log::info("campaign_content_id: $id");

        $creative_id = null;

        if ($campaign == null) {
            return $creative_id;
        }

        // check if has [VALUE_CREATIVE_DESCRIPTION] OR [VALUE_CREATIVE_IMAGE]
        if (strpos($campaign->stack, '[VALUE_CREATIVE_DESCRIPTION]') !== false || strpos($campaign->stack, '[VALUE_CREATIVE_IMAGE]') !== false) {
            $campCreatives = CampaignCreative::where('campaign_id', $id)->where('weight', '!=', 0)->pluck('weight', 'id')->toArray();
            if ($campCreatives) {
                $creative_id = Bus::dispatch(new RandomProbability($campCreatives));
            }
        }

        return $creative_id;
    }

    public function getRandomCampaignCreativeID(Request $request)
    {

        $id = $request->input('id');
        $creative_id = null;

        $campCreatives = CampaignCreative::where('campaign_id', $id)->where('weight', '!=', 0)->pluck('weight', 'id')->toArray();
        if ($campCreatives) {
            $creative_id = Bus::dispatch(new RandomProbability($campCreatives));
        }

        return $creative_id;
    }

    public function trackCampaignNos(Request $request)
    {
        // Log::info($request->all());
        $inputs = $request->all();
        if (isset($inputs['email'],$inputs['campaigns'], $inputs['session'])) {
            $tracking = (new CampaignNoTracking($inputs['email'], $inputs['campaigns'], $inputs['session']))->delay(5);
            $this->dispatch($tracking);
        }
    }

    public function getFilterFreeCampaigns(Request $request)
    {
        $inputs = $request->all();

        $default_limit = 15;
        $limit = isset($inputs['limit']) ? $inputs['limit'] : $default_limit;

        $campaigns = \App\Campaign::leftJoin('campaign_filter_groups', 'campaigns.id', '=', 'campaign_filter_groups.campaign_id')
            ->whereNull('campaign_filter_groups.campaign_id')->where('campaigns.status', 2)
            ->whereIn('campaigns.campaign_type', [1, 2, 8, 13])
            ->orderBy('priority', 'ASC')
            ->limit($limit)
            ->pluck('campaigns.id');
    }

    //gets all public and private campaigns from campaign type given
    public function getAllCampaignsFromCampaignType(Request $request)
    {
        $ct_id = $request->input('id');

        return $campaigns = Campaign::leftJoin('campaign_creatives', 'campaign_creatives.id', '=', DB::RAW('(SELECT MIN(campaign_creatives.id) FROM campaign_creatives WHERE campaign_creatives.campaign_id = campaigns.id AND weight != 0)'))
            ->where('status', '!=', 0)->where('status', '!=', 3)->where('campaign_type', $ct_id)
            ->orderBy('priority', 'ASC')
            ->selectRaw('campaign_creatives.id as creative_id, campaigns.id')
            ->pluck('creative_id', 'campaigns.id');
    }

    public function getAllCampaignsForQA(Request $request)
    {
        $campaigns = Campaign::leftJoin('campaign_creatives', 'campaign_creatives.id', '=', DB::RAW('(SELECT MIN(campaign_creatives.id) FROM campaign_creatives WHERE campaign_creatives.campaign_id = campaigns.id AND weight != 0)'))
            ->orderBy('priority', 'ASC')
            ->selectRaw('campaigns.id, status, campaign_type, campaign_creatives.id as creative_id, campaigns.name')
            ->get();
        $statuses = config('constants.CAMPAIGN_STATUS');
        $response = [
            // 'campaign_types' => [],
            'creatives' => [],
            // 'names' => [],
            'active' => [],
            'campaigns' => [],
        ];
        foreach ($campaigns as $c) {
            // $response['campaign_types'][$c->campaign_type][] = $c->id;
            $response['campaigns'][$c->campaign_type][$c->id] = $c->id.' - '.$c->name.' - '.$statuses[$c->status];
            // $response['campaigns'][$c->id] = $c->name.' - '.$statuses[$c->status];
            if ($c->creative_id != null) {
                $response['creatives'][$c->campaign_type][$c->id] = $c->creative_id;
            }
            if (in_array($c->status, [1, 2])) {
                $response['active'][$c->campaign_type][] = $c->id;
            }
        }

        return $response;
    }

    public function getUserInfoByEmail(Request $request)
    {
        $email = $request->input('email');
        $info = LeadUser::where('email', $email)->first();

        return $info;
    }

    //Curl Version of getting and displaying of STACK Campaign Content
    public function campaignStackProcessorJSON(Request $request)
    {
        // DB::enableQueryLog();
        $inputs = $request->all();
        // Log::info($inputs);
        $campaigns = isset($inputs['campaigns']) ? $inputs['campaigns'] : [];
        $affiliate_id = isset($inputs['affiliate_id']) ? $inputs['affiliate_id'] : '';
        $session = isset($inputs['session']) ? $inputs['session'] : '';
        $path = isset($inputs['path']) ? $inputs['path'] : '';
        $s1 = isset($inputs['s1']) ? $inputs['s1'] : '';
        $s2 = isset($inputs['s2']) ? $inputs['s2'] : '';
        $s3 = isset($inputs['s3']) ? $inputs['s3'] : '';
        $s4 = isset($inputs['s4']) ? $inputs['s4'] : '';
        $s5 = isset($inputs['s5']) ? $inputs['s5'] : '';
        $campaign_type = isset($inputs['campaign_type']) ? $inputs['campaign_type'] : '';
        $return_str = '';
        $cntr = 0;

        if (empty($campaigns) || is_null($campaigns)) {
            return ['message' => 'Campaign does not exists.'];
        }

        /* Campaign Reordering View Tracking */
        $rs = Setting::whereIn('code', ['campaign_reordering_status', 'mixed_coreg_campaign_reordering_status'])->pluck('integer_value', 'code');

        $rtSetting = AffiliateRevenueTracker::where('revenue_tracker_id', $affiliate_id)->select(['mixed_coreg_order_status', 'order_status', 'subid_breakdown', 'sib_s1', 'sib_s2', 'sib_s3', 'sib_s4'])->first();
        if ($rtSetting && $rtSetting->subid_breakdown == 0) { //Check if rev tracker allowed for subid breakdown
            $s1 = '';
            $s2 = '';
            $s3 = '';
            $s4 = '';
            $s5 = '';
        } else {
            if ($rtSetting && $rtSetting->sib_s1 == 0) {
                $s1 = '';
            }
            if ($rtSetting && $rtSetting->sib_s2 == 0) {
                $s2 = '';
            }
            if ($rtSetting && $rtSetting->sib_s3 == 0) {
                $s3 = '';
            }
            if ($rtSetting && $rtSetting->sib_s4 == 0) {
                $s4 = '';
            }
            $s5 = '';
        }

        //Check Mixed Coreg Reorder Global Settings
        $mcReorder = false;
        if (env('EXECUTE_REORDER_MIXED_COREG_CAMPAIGNS', false) || env('EXECUTE_DAILY_REORDER_MIXED_COREG_CAMPAIGNS', false)) {
            if (isset($rs['mixed_coreg_campaign_reordering_status']) && $rs['mixed_coreg_campaign_reordering_status'] == 1) {
                $mcReorder = true;
            }
        }
        //Check Campaign Type Reorder Global Settings
        $ctReorder = false;
        if (env('EXECUTE_REORDER_CAMPAIGNS', false)) {
            if (isset($rs['campaign_reordering_status']) && $rs['campaign_reordering_status'] == 1) {
                $ctReorder = true;
            }
        }
        //Check Rev Tracker Settings Level
        if ($mcReorder || $ctReorder) {
            if ($rtSetting) {
                if ($rtSetting->mixed_coreg_order_status != 1) {
                    $mcReorder = false;
                }
                if ($rtSetting->order_status != 1) {
                    $ctReorder = false;
                }
            }
        }

        /* For Creatives */
        $creative_ids = [];
        $campaign_creatives = [];
        if (isset($inputs['creatives']) && count($inputs['creatives']) > 0) {
            $creative_ids = $inputs['creatives'];
            $stack_creatives = CampaignCreative::whereIn('id', $creative_ids)->select('id', 'image', 'description')->get();
            foreach ($stack_creatives as $c) {
                $campaign_creatives[$c->id]['id'] = $c->id;
                $campaign_creatives[$c->id]['image'] = $c->image;
                $campaign_creatives[$c->id]['description'] = $c->description;
            }
        }

        /*Filters*/
        $filters = [];
        if ($request->filter == 'yes') {
            $filter_groups = CampaignFilterGroup::whereIn('campaign_id', $campaigns)->with('filters.filter_type')->where('status', 1)->get();
            foreach ($filter_groups as $fg) {
                $fs = [];
                foreach ($fg->filters as $f) {
                    $fs[] = [
                        'type' => $f->filter_type->type,
                        'name' => $f->filter_type->name,
                        'value' => $this->getCampaignFilterValue($f),
                    ];
                }
                $filters[$fg->campaign_id][] = $fs;
            }
        }

        $data = [];
        $cc = CampaignJsonContent::whereIn('id', $campaigns)->where(function ($query) {
            $query->where('json', '!=', '')
                ->orWhere('script', '!=', '');
        })->select(['id', 'json', 'script'])->get();
        $campaign_contents = [];
        foreach ($cc as $c) {
            $campaign_contents[$c->id] = $c;
        }

        foreach ($campaigns as $id) {
            $creative_id = isset($creative_ids[$id]) ? $creative_ids[$id] : 0; //get campaign's creative
            $content = isset($campaign_contents[$id]) ? $campaign_contents[$id] : null;
            $data[] = [
                'id' => $id,
                'json' => $content != null ? json_decode($content->json, true) : '',
                'script' => $content != null ? $content->script : '',
                'creative' => isset($campaign_creatives[$creative_id]) ? $campaign_creatives[$creative_id] : null,
                'filters' => isset($filters[$id]) ? $filters[$id] : [],
            ];

            // Count Campaign View
            if ($affiliate_id != '' && $session != '') {
                $view_details = [
                    'campaign_id' => $id,
                    'affiliate_id' => $affiliate_id,
                    'session' => $session,
                    'path_id' => $path,
                    'creative_id' => $creative_id,
                    's1' => $s1,
                    's2' => $s2,
                    's3' => $s3,
                    's4' => $s4,
                    's5' => $s5,
                ];

                try {
                    CampaignView::create($view_details);
                    if ($mcReorder) {
                        $report = CampaignViewReport::firstOrNew([
                            'campaign_id' => $view_details['campaign_id'],
                            'revenue_tracker_id' => $view_details['affiliate_id'],
                            's1' => $s1,
                            's2' => $s2,
                            's3' => $s3,
                            's4' => $s4,
                            's5' => $s5,
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
                    }

                } catch (QueryException $exception) {
                    // $errorCode = $exception->errorInfo[1];
                    // Log::info('Campaign View insert error code: '.$errorCode);
                    // Log::info('Error message: '.$exception->getMessage());
                }

                $cntr++;
            }
        }

        if ($ctReorder) {
            try {
                CampaignTypeView::create([
                    'campaign_type_id' => $campaign_type,
                    'revenue_tracker_id' => $affiliate_id,
                    'session' => $session,
                    'timestamp' => Carbon::now(),
                ]);

                $report = CampaignTypeReport::firstOrNew([
                    'campaign_type_id' => $campaign_type,
                    'revenue_tracker_id' => $affiliate_id,
                    's1' => $s1,
                    's2' => $s2,
                    's3' => $s3,
                    's4' => $s4,
                    's5' => $s5,
                ]);

                if ($report->exists) {
                    $report->views = $report->views + 1;
                } else {
                    $report->views = 1;
                }

                $report->save();
            } catch (QueryException $e) {
                // Log::info($e->getMessage());
            }
        }

        return response()->json($data, 200);
    }

    public function updateLeadUserDetails(Request $request, $id)
    {
        $saved_fields = [
            'first_name',
            'last_name',
            'email',
            'birthdate',
            'gender',
            'zip',
            'city',
            'state',
            'address1',
            'address2',
            'ethnicity',
            'phone',
        ];
        $fields = $request->all();
        $updated = [];
        foreach ($fields as $key => $val) {
            if (in_array($key, $saved_fields)) {
                $updated[$key] = $val;
            }
        }
        if (count($updated) > 0) {
            return $user = LeadUser::where('id', $id)->update($updated);
        } else {
            return 'User lead not updated';
        }

    }

    /**
     * Get campaign type
     *
     * @return null
     */
    public function getCampaignType(Request $request)
    {

        $id = $request->input('id');
        $campaign = Campaign::find($id);

        return $campaign->campaign_type;
    }

    public function campaignStackProcessorArray(Request $request)
    {
        // DB::enableQueryLog();
        $inputs = $request->all();
        // Log::info($inputs);
        $campaigns = isset($inputs['campaigns']) ? $inputs['campaigns'] : [];
        $affiliate_id = isset($inputs['affiliate_id']) ? $inputs['affiliate_id'] : '';
        $session = isset($inputs['session']) ? $inputs['session'] : '';
        $path = isset($inputs['path']) ? $inputs['path'] : '';
        $s1 = isset($inputs['s1']) ? $inputs['s1'] : '';
        $s2 = isset($inputs['s2']) ? $inputs['s2'] : '';
        $s3 = isset($inputs['s3']) ? $inputs['s3'] : '';
        $s4 = isset($inputs['s4']) ? $inputs['s4'] : '';
        $s5 = isset($inputs['s5']) ? $inputs['s5'] : '';
        $contents = [];
        $cntr = 0;
        $filters = [];

        if (empty($campaigns) || is_null($campaigns)) {
            return ['message' => 'Campaign does not exists.'];
        }

        /* GET CAMPAIGN TYPE */
        $campaign_type = null;
        foreach ($campaigns as $c) {
            $cmp = Campaign::select('campaign_type')->where('id', '=', $c)->first();
            if ($cmp != null) {
                $campaign_type = $cmp->campaign_type;
                break;
            }
        }

        if ($campaign_type == null) {
            return ['message' => 'Campaign does not exists.'];
        }
        // Log::info($campaign_type . ' - TYPE');

        /* Campaign Reordering View Tracking */
        $rs = Setting::whereIn('code', ['campaign_reordering_status', 'mixed_coreg_campaign_reordering_status'])->pluck('integer_value', 'code');
        $rtSetting = AffiliateRevenueTracker::where('revenue_tracker_id', $affiliate_id)->select(['mixed_coreg_order_status', 'order_status', 'subid_breakdown', 'sib_s1', 'sib_s2', 'sib_s3', 'sib_s4'])->first();

        if ($rtSetting && $rtSetting->subid_breakdown == 0) { //Check if rev tracker allowed for subid breakdown
            $s1 = '';
            $s2 = '';
            $s3 = '';
            $s4 = '';
            $s5 = '';
        } else {
            if ($rtSetting && $rtSetting->sib_s1 == 0) {
                $s1 = '';
            }
            if ($rtSetting && $rtSetting->sib_s2 == 0) {
                $s2 = '';
            }
            if ($rtSetting && $rtSetting->sib_s3 == 0) {
                $s3 = '';
            }
            if ($rtSetting && $rtSetting->sib_s4 == 0) {
                $s4 = '';
            }
            $s5 = '';
        }

        //Check Mixed Coreg Reorder Global Settings
        $mcReorder = false;
        if (env('EXECUTE_REORDER_MIXED_COREG_CAMPAIGNS', false) || env('EXECUTE_DAILY_REORDER_MIXED_COREG_CAMPAIGNS', false)) {
            if (isset($rs['mixed_coreg_campaign_reordering_status']) && $rs['mixed_coreg_campaign_reordering_status'] == 1) {
                $mcReorder = true;
            }
        }
        //Check Campaign Type Reorder Global Settings
        $ctReorder = false;
        if (env('EXECUTE_REORDER_CAMPAIGNS', false)) {
            if (isset($rs['campaign_reordering_status']) && $rs['campaign_reordering_status'] == 1) {
                $ctReorder = true;
            }
        }
        //Check Rev Tracker Settings Level
        if ($mcReorder || $ctReorder) {
            if ($rtSetting) {
                if ($rtSetting->mixed_coreg_order_status != 1) {
                    $mcReorder = false;
                }
                if ($rtSetting->order_status != 1) {
                    $ctReorder = false;
                }
            }
        }

        /*Filters*/
        if ($request->filter == 'yes') {
            $filter_groups = CampaignFilterGroup::whereIn('campaign_id', $campaigns)->with('filters.filter_type')->where('status', 1)->get();
            foreach ($filter_groups as $fg) {
                $fs = [];
                foreach ($fg->filters as $f) {
                    $fs[] = [
                        'type' => $f->filter_type->type,
                        'name' => $f->filter_type->name,
                        'value' => $this->getCampaignFilterValue($f),
                    ];
                }
                $filters[$fg->campaign_id][] = $fs;
            }
        }

        /* For Creatives */
        $creative_ids = [];
        $campaign_creatives = [];
        if (isset($inputs['creatives']) && count($inputs['creatives']) > 0) {
            $creative_ids = $inputs['creatives'];
            $stack_creatives = CampaignCreative::whereIn('id', $creative_ids)->select('id', 'image', 'description')->get();
            foreach ($stack_creatives as $c) {
                $campaign_creatives[$c->id]['image'] = $c->image;
                $campaign_creatives[$c->id]['description'] = $c->description;
            }
        }

        $not_coreg_campaigns = [4, 5, 6];

        // Log::info($campaigns);
        $stack_contents = CampaignContent::whereIn('id', $campaigns)->pluck('stack', 'id');
        foreach ($campaigns as $id) {
            $content = isset($stack_contents[$id]) ? $stack_contents[$id] : '';
            $creative_id = null;

            /* For Creative Rotation */
            if (isset($creative_ids[$id])) { //Sent a creative id. Probably for previewing a content
                $creative_id = $creative_ids[$id]; //get campaign's creative
                $this_creative = isset($campaign_creatives[$creative_id]) ? $campaign_creatives[$creative_id] : null; //Get campaign creative's image and description
                $stack_content = $this->getCampaignCreativeContent($content, $creative_id, $this_creative);
            } else {
                //check if has [VALUE_CREATIVE_DESCRIPTION] OR [VALUE_CREATIVE_IMAGE]
                if (strpos($content, '[VALUE_CREATIVE_DESCRIPTION]') !== false || strpos($content, '[VALUE_CREATIVE_IMAGE]') !== false) {
                    $stack_content = '';
                } else {
                    $stack_content = $content;
                }
            }

            /* For Path ID: ADD PATH ID*/
            if (! in_array($campaign_type, $not_coreg_campaigns) && $stack_content != '') { //if campaign is coreg
                $stack_content = $this->addPathIdFieldInForm($stack_content);
            }

            $contents[$id] = $stack_content;

            // Count Campaign View
            if ($stack_content != '' && $affiliate_id != '' && $session != '') {

                $view_details = [
                    'campaign_id' => $id,
                    'affiliate_id' => $affiliate_id,
                    'session' => $session,
                    'path_id' => $path,
                    'creative_id' => $creative_id,
                    's1' => $s1,
                    's2' => $s2,
                    's3' => $s3,
                    's4' => $s4,
                    's5' => $s5,
                    // 'campaign_type_id'  => $campaign_type
                ];
                // $job = (new SaveCampaignView($view_details))->delay(5);
                // $this->dispatch($job);

                try {
                    CampaignView::create($view_details);

                    if ($mcReorder) {
                        $report = CampaignViewReport::firstOrNew([
                            'campaign_id' => $view_details['campaign_id'],
                            'revenue_tracker_id' => $view_details['affiliate_id'],
                            's1' => $s1,
                            's2' => $s2,
                            's3' => $s3,
                            's4' => $s4,
                            's5' => $s5,
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
                    }

                } catch (QueryException $exception) {
                    // $errorCode = $exception->errorInfo[1];
                    // Log::info('Campaign View insert error code: '.$errorCode);
                    // Log::info('Error message: '.$exception->getMessage());
                }

                $cntr++;
            }
        }

        // $campaign_type_job = (new SaveCampaignTypeView($campaign_type, $affiliate_id, $session, Carbon::now()))->delay(5);
        // $this->dispatch($campaign_type_job);

        if ($ctReorder) {
            try {
                CampaignTypeView::create([
                    'campaign_type_id' => $campaign_type,
                    'revenue_tracker_id' => $affiliate_id,
                    'session' => $session,
                    'timestamp' => Carbon::now(),
                ]);

                $report = CampaignTypeReport::firstOrNew([
                    'campaign_type_id' => $campaign_type,
                    'revenue_tracker_id' => $affiliate_id,
                    's1' => $s1,
                    's2' => $s2,
                    's3' => $s3,
                    's4' => $s4,
                    's5' => $s5,
                ]);

                if ($report->exists) {
                    $report->views = $report->views + 1;
                } else {
                    $report->views = 1;
                }

                $report->save();
            } catch (QueryException $e) {
                // Log::info($e->getMessage());
            }
        }

        if ($request->filter == 'yes') {
            $response = ['contents' => $contents, 'filters' => $filters];
        } else {
            $response = $contents;
        }
        // Log::info(DB::getQueryLog());
        return response()->json($response);
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
}
