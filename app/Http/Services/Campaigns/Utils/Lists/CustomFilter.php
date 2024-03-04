<?php

namespace App\Http\Services\Campaigns\Utils\Lists;

use Carbon\Carbon;

final class CustomFilter
{
    /**
     * Check for filter error
     *
     * @param  collection  $campaign
     * @param  array  $userDetails
     * @param  int  $filterStatus
     * @param  collection  $filterTypes
     * @return int
     */
    public function passed($campaign, $userDetails, $filterStatus, $filterTypes)
    {
        $filterPassed = true;

        if ($filterStatus == 1) {
            $filterGroupsChecker = [];
            $campaignFilterPassed = false;
            $filterPassed = true; //no filter groups = no errors
            if (count($campaign->filter_groups) > 0) { //check if campaign has filter groups
                foreach ($campaign->filter_groups as $group) { //go through each filter group
                    $filterGroupError = 0; //filter group checker
                    if ($group->status == 1 && $group->filters) { //check if filter group has filters
                        foreach ($group->filters as $filter) { //go through each filter
                            if ($this->check($filter, $filterTypes, $userDetails, $campaign->config) == false) {
                                $filterGroupError++;
                                break;
                            }
                        }
                        if ($filterGroupError == 0) {
                            $campaignFilterPassed = true;
                        }
                    }
                    $filterGroupsChecker[] = $filterGroupError == 0 ? 1 : 0;

                    if ($campaignFilterPassed) {
                        break;
                    }
                }

                if ($campaignFilterPassed
                    || count($filterGroupsChecker) == 0
                    || in_array(1, $filterGroupsChecker)
                ) {
                    $filterPassed = true;
                } else {
                    $filterPassed = false;
                }
            }
        }

        return $filterPassed;
    }

    /**
     * Check if campaign passed the filters
     *
     * @param  collection  $filter
     * @param  array  $filterTypes
     * @param  collection  $user
     * @param  collection  $config
     * @return bolean
     */
    protected function check($filter, $filterTypes, $user, $config)
    {
        $answer = '';
        $filterID = $filter->filter_type_id;
        $filterType = strtolower($filterTypes[$filterID]['type']); //get filter type
        $filterName = strtolower($filterTypes[$filterID]['name']); //get filter name
        if ($filterType == 'profile') {
            switch ($filterName) {
                case 'age' :
                    $answer = Carbon::createFromDate($user['dobyear'], $user['dobmonth'], $user['dobday'])->age;
                    break;
                case 'ethnicity':
                    $ethnicGroups = config('constants.ETHNIC_GROUPS');
                    $ethnicity = array_search($user['ethnicity'], $ethnicGroups);
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
                    $emailsplit = explode('@', $user['email']);
                    $domain = explode('.', $emailsplit[1]);
                    $answer = $domain[0];
                    break;
                case 'email_ext':
                    $emailsplit = explode('@', $user['email']);
                    $domain = explode('.', $emailsplit[1]);
                    $answer = $domain[1];
                    break;
                default:
                    if (array_key_exists($filterName, $user)) { //check if filter name exists in user details
                        $answer = $user[$filterName];
                    }
                    break;
            }
        } elseif ($filterType == 'custom') {
            switch ($filterName) {
                case 'show_date' :
                    $answer = Carbon::now()->format('l');
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
                    $answer = $this->checkPing($user, $config);
                    break;
                default:
                    $answer = '';
                    break;
            }
        } elseif ($filterType == 'question') {
            if (! isset($user['filter_questions']) || ! array_key_exists($filterID, $user['filter_questions'])) {
                return false;
            } else {
                $answer = $user['filter_questions'][$filterID];
            }
        }

        if ($this->findCampaignFilterValue($filter, $answer) == false) {
            return false;
        }

        return true;
    }

    /**
     * Find campaign filter value
     *
     * @param  collection  $filter
     * @param  string  $answer
     * @return bolean
     */
    protected function findCampaignFilterValue($filter, $answer)
    {
        $value = false;
        if (! is_null($filter->value_text) || $filter->value_text != '') {
            $answer = strtolower($answer);
            if (strpos($filter->value_text, '[NOT]') !== false) {
                $noStr = explode('[NOT]', $filter->value_text);
                $filterValue = trim(strtolower($noStr[1]));
                if ($answer != $filterValue) {
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
                $noStr = explode('[NOT]', $filter->value_array);
                $filterValue = trim(strtolower($noStr[1]));

                $filterValue = preg_replace('/\s*,\s*/', ',', $filterValue);

                $array = explode(',', $filterValue);
                if (! in_array($answer, $array)) {
                    $value = true;
                }
            } else {
                $filterValue = trim(strtolower($filter->value_array));

                $filterValue = preg_replace('/\s*,\s*/', ',', $filterValue);

                $array = explode(',', $filterValue);
                if (in_array($answer, $array)) {
                    $value = true;
                }
            }
        } else {
            $value = true;
        }

        return $value;
    }

    /**
     * Ping the target url
     *
     * @param  collection  $filter
     * @param  collection  $user
     * @param  collection  $config
     * @return bolean
     */
    protected function checkPing($user, $config)
    {
        if (! $config) {
            return true;
        }
        if ($config && ! $config->ping_url) {
            return true;
        }

        $url = $config->ping_url;

        if (strpos($url, '[VALUE_EMAIL]') !== false) {
            $url = str_replace('[VALUE_EMAIL]', $user['email'], $url);
        }

        $curl = new Curl();
        $curl->setopt(CURLOPT_RETURNTRANSFER, true);
        $curl->setopt(CURLOPT_TIMEOUT, 5);
        $curl->get($url);
        $content = $curl->response;
        $curl->close();

        if (strpos($content, $config->ping_success) !== false) {
            return true;
        }

        return false;
    }
}
