<?php

namespace App\Http\Services\Helpers;

class SurveyStack
{
    /**
     * Initial/Default Data
     */
    public $pathID;

    public $userDetails;

    public $redirectUrl = '';

    public $queryString = [];

    public $reloadParentFrame = false;

    public $targetUrl = 'http://leadreactor.engageiq.com/';

    /**
     * Intantiate
     */
    public function __construct()
    {
    }

    public function setPathID($pathID)
    {
        $this->pathID = $pathID;

        return $this;
    }

    public function pathID()
    {
        return $this->pathID;
    }

    public function setUserDetails($userDetails)
    {
        $userDetails['target_url'] = $this->targetUrl;
        $this->userDetails = $userDetails;

        return $this;
    }

    public function userDetails()
    {
        return $this->userDetails;
    }

    public function setQueryString($queryString)
    {
        $this->queryString = $queryString;

        return $this;
    }

    public function queryString()
    {
        return $this->queryString;
    }

    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    public function settargetUrl($targetUrl)
    {
        $this->targetUrl = preg_replace('{//$}', '/', $targetUrl.'/');

        return $this;
    }

    public function targetUrl($removeTrailingSlash = false)
    {
        if ($removeTrailingSlash) {
            return preg_replace('{/$}', '', $this->targetUrl);
        }

        return $this->targetUrl;
    }

    public function redirectUrl()
    {
        return $this->redirectUrl;
    }

    public function setReloadParentFrame($reloadParentFrame)
    {
        $this->reloadParentFrame = $reloadParentFrame;

        return $this;
    }

    public function reloadParentFrame()
    {
        return $this->reloadParentFrame;
    }

    public function iframeContentJS()
    {
        if ($this->targetUrl(true) == 'http://leadreactor.engageiq.com') {
            return 'http://path2.paidforresearch.com/assets/js/api/iframe-content.min.js?t'.time();
        }

        return 'http://path2.paidforresearch.com/assets/js/api/iframe-content-test.min.js?t'.time();
    }
}
