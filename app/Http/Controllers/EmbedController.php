<?php

namespace App\Http\Controllers;

use App\Campaign;
use App\CampaignConfig;
use App\CampaignCreative;
use App\CampaignJsonContent;
use Bus;
use Curl\Curl;
use Illuminate\Http\Request;
use RandomProbability;

class EmbedController extends Controller
{
    public function embed(Request $request)
    {
        $id = $request->id;
        $params = $request->all();

        $campaign = Campaign::find($id);
        if (! $campaign) {
            return 'No campaign found.';
        }

        $creative_id = $request->creative_id;
        if ($creative_id == '') {
            $campCreatives = CampaignCreative::where('campaign_id', $id)->where('weight', '!=', 0)->pluck('weight', 'id')->toArray();
            if ($campCreatives) {
                $creative_id = Bus::dispatch(new RandomProbability($campCreatives));
            }
        }
        $params['eiq_creative_id'] = $creative_id;
        $creative = CampaignCreative::find($creative_id);
        $config = CampaignConfig::find($id);
        $content = CampaignJsonContent::find($id);
        // $json = json_decode($this->getTrueVal($content->json, $request->all()), true);
        $json = $this->getTrueVal(json_decode($content->json, true), $params);
        if ($request->test == 'yes') {
            return $json;
        }

        $redirect_url = $request->redirect_url;

        $script = $content->script;

        return view('embed', compact('campaign', 'creative', 'config', 'json', 'redirect_url', 'script'));
    }

    public function getValues($html, $values)
    {
        foreach ($values as $short_code => $value) {
            if (strpos($html, $short_code) !== false) {
                $html = str_replace($short_code, $value, $html);
            }
        }

        return $html;
    }

    public function getTrueVal($json, $values)
    {
        // \Log::info($values);
        foreach ($json['fields'] as &$field) {

            if ($field['name'] == 'eiq_campaign_id' || $field['type'] == 'article') {
                //KEEP
            } elseif ($field['name'] == 'eiq_affiliate_id') {
                $affiliate_id = isset($values['affiliate_id']) && $values['affiliate_id'] != '' ? $values['affiliate_id'] : 1;
                $field['value'] = $affiliate_id;
            } elseif ($field['name'] == 'eiq_email') {
                $email = isset($values['email']) && $values['email'] != '' ? $values['email'] : '';
                $field['value'] = $email;
            } elseif ($field['name'] == 'eiq_path_id') {
                $val = isset($values['path_id']) && $values['path_id'] != '' ? $values['path_id'] : '';
                $field['value'] = $val;
            } elseif ($field['value'] == '[VALUE_REV_TRACKER]') {
                $val = isset($values['affiliate_id']) && $values['affiliate_id'] != '' ? $values['affiliate_id'] : 1;
                $field['value'] = $val;
            } elseif ($field['value'] == 'CD[VALUE_REV_TRACKER]') {
                $val = isset($values['affiliate_id']) && $values['affiliate_id'] != '' ? 'CD'.$values['affiliate_id'] : 1;
                $field['value'] = $val;
            } else {
                $val = isset($values[$field['name']]) && $values[$field['name']] != '' ? $values[$field['name']] : '';
                $field['value'] = $val;
            }
        }

        //\Log::info($json);
        return $json;
    }

    public function receive(Request $request)
    {
        $curl = new Curl();
        $curl->get($_SERVER['QUERY_STRING']);
        if ($curl->error) {
            $output = $curl->error_code;
        } else {
            $output = $curl->response;
        }
        $curl->close();

        return $output;
    }
}
