<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PingController extends Controller
{
    /* 139 - Shop Your Way (1127) */
    public function shopYourWayPing(Request $request)
    {
        $email = $request->input('email');
        $url = 'https://preping.permissiondata.com/exists/?campaign=5612&email='.$email;
        // must set $url first....
        $http = curl_init($url);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, true);
        // do your curl thing here
        $result = curl_exec($http);
        $http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);
        curl_close($http);
        $http_status = (string) $http_status;

        return $http_status;
    }

    /* 4 - GlobalTestMarket co-reg (913) */
    public function globalTestMarketPing(Request $request)
    {
        $email = $request->input('email');
        $UserName = 'ENGAGE_IQ';
        $DateandHour = gmdate('Y-m-d H');
        $HashedPassword = substr(md5('QOSRD16#pmq2dEbl'), 0, 10);
        $Key = 'sk5734d80d6ad1d';
        $sharedSecret = md5($UserName.':'.$Key.':'.$DateandHour.':'.$HashedPassword);
        $emailhashvalue = md5(strtolower($email));
        $myurl = "https://panelmanager.lightspeedgmiservices.com/PanelManager/Panel.ashx?method=checkRecruitEligibility&username=$UserName&sharedSecret=$sharedSecret&PanelBrand=gtm&Email=$emailhashvalue";

        return $content = file_get_contents($myurl);
    }

    /* 92 - Survey and Quizzes (1094) */
    public function surveyAndQuizzesPing(Request $request)
    {
        $email = $request->input('email');
        $emailhashvalue = md5($email);
        $myurl = "http://puzz.biglist.com/api/1.1/lists/news/subscriptions/$emailhashvalue?apikey=bbmwhaw9n43awadfgh&format=xml";

        return $content = file_get_contents($myurl);
    }
}
