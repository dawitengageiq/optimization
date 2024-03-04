<?php

namespace App\Http\Services\Campaigns\Utils\Content;

final class ContentReplaceable
{
    /**
     * Default variables
     */
    protected $dataToReplace;

    protected $html;

    /**
     * Flag/container for testing process
     */
    protected static $forTesting = false;

    /**
     * Initialize
     *
     * @param  array  $userDetails
     * @param  string  $html
     */
    public function __construct($dataToReplace, $html)
    {
        $this->dataToReplace = $dataToReplace;
        $this->html = $html;
    }

    /**
     * Process the replacing of content codes
     *
     * @param  array  $userDetails
     * @param  string  $html
     * @return string
     */
    public static function process($dataToReplace, $html)
    {
        if (! $dataToReplace) {
            return $html;
        }

        $replace = new self($dataToReplace, $html);

        return $replace->execute()->html;
    }

    /**
     * Execute the replacing of content codes
     *
     * @return Class::
     */
    public function executeThroughLoop()
    {

        // Go through all replaceables and replace html content with user details
        collect($this->replaceable())->each(function ($val, $key) {
            if (is_callable($val)) {
                $val($key);
            } else {
                $this->replace($key, $this->dataToReplace[$val]);
            }
        });

        /*FOR TESTING*/
        //if($this->$forTesting) $this->replaceSendLeadsUrl();
        $this->replaceSendLeadsUrl($this->dataToReplace['target_url']);

        return $this;
    }

    /**
     * Execute the replacing of content codes
     *
     * @return Class::
     */
    public function execute()
    {

        $this->replace('VALUE_REV_TRACKER', $this->dataToReplace['revenue_tracker_id']);
        $this->replace('VALUE_AFFILIATE_ID', $this->dataToReplace['affiliate_id']);
        $this->replace('VALUE_DOBMONTH', $this->dataToReplace['dobmonth']);
        $this->replace('VALUE_DOBDAY', $this->dataToReplace['dobday']);
        $this->replace('VALUE_DOBYEAR', $this->dataToReplace['dobyear']);
        $this->replace('VALUE_EMAIL', $this->dataToReplace['email']);
        $this->replace('VALUE_FIRST_NAME', $this->dataToReplace['first_name']);
        $this->replace('VALUE_LAST_NAME', $this->dataToReplace['last_name']);
        $this->replace('VALUE_ZIP', $this->dataToReplace['zip']);
        $this->replace('VALUE_CITY', $this->dataToReplace['city']);
        $this->replace('VALUE_STATE', $this->dataToReplace['state']);
        $this->replace('VALUE_BIRTHDATE', $this->dataToReplace['birthdate']);
        $this->replace('VALUE_IP', $this->dataToReplace['ip']);
        $this->replace('VALUE_ADDRESS1', $this->dataToReplace['address']);
        $this->replace('VALUE_PHONE', $this->dataToReplace['phone']);
        $this->replace('VALUE_PHONE1', $this->dataToReplace['phone1']);
        $this->replace('VALUE_PHONE2', $this->dataToReplace['phone2']);
        $this->replace('VALUE_PHONE3', $this->dataToReplace['phone3']);
        $this->replace('VALUE_GENDER', $this->dataToReplace['gender']);
        $this->replace('VALUE_GENDER_FULL', ($this->dataToReplace['gender'] == 'F') ? 'Female' : 'Male');
        $this->replace('VALUE_TITLE', ($this->dataToReplace['gender'] == 'M') ? 'Mr.' : 'Ms.');
        $this->replace('VALUE_PUB_TIME', date('Y-m-d H:i:s'));
        $this->replace('VALUE_DATE_TIME', date('Y-m-d H:i:s'));
        $this->replace('VALUE_TODAY', date('m/d/Y'));
        $this->replace('VALUE_TODAY_MONTH', date('m'));
        $this->replace('VALUE_TODAY_DAY', date('d'));
        $this->replace('VALUE_TODAY_YEAR', date('Y'));
        $this->replace('VALUE_TODAY_HOUR', date('H'));
        $this->replace('VALUE_TODAY_MIN', date('i'));
        $this->replace('VALUE_TODAY_SEC', date('s'));
        $this->replace('VALUE_AGE', date_diff(date_create($this->dataToReplace['birthdate']), date_create('today'))->y);
        $this->replace('VALUE_ETHNICITY', (array_key_exists('ethnicity', $this->dataToReplace)) ? $this->dataToReplace['ethnicity'] : '');
        $this->replace('VALUE_PATH_ID', $this->dataToReplace['path_id']);
        $this->replace('VALUE_NEXT_CAMPAIGN_PRIORITY', 1);
        $this->replace('VALUE_NEXT_CAMPAIGN_PRIORITY', 2);
        $this->replace('VALUE_URL_SURVEY_PAGE', '');
        $this->replace('VALUE_URL_REDIRECT_PAGE', '');
        $this->replace('VALUE_URL_REDIRECT_STACK_PAGE', '');
        //  BROWSER DETECT
        $this->replace('DETECT_OS', $this->dataToReplace['os']);
        $this->replace('DETECT_OS_VER', $this->dataToReplace['os_version']);
        $this->replace('DETECT_BROWSER', $this->dataToReplace['browser']);
        $this->replace('DETECT_BROWSER_VER', $this->dataToReplace['browser_version']);
        $this->replace('DETECT_USER_AGENT', $this->dataToReplace['user_agent']);
        //  MOBILE DETECT
        $this->replace('DETECT_DEVICE', $this->dataToReplace['type']);
        $this->replace('DETECT_ISMOBILE', $this->dataToReplace['isMobile']);
        $this->replace('DETECT_ISTABLET', $this->dataToReplace['isTablet']);
        $this->replace('DETECT_ISDESKTOP', $this->dataToReplace['isDesktop']);

        // This is not good fix
        $this->html = str_replace('$prepopulation+=', '$prepopulation .= ', $this->html);
        $this->html = str_replace('if (gender==', 'if ($gender==', $this->html);

        /*FOR TESTING*/
        $this->replaceSendLeadsUrl($this->dataToReplace['target_url']);

        return $this;
    }

    /**
     * Set the valu and callback for replacable
     *
     * @return array
     */
    protected function replaceable()
    {
        return [
            'VALUE_REV_TRACKER' => 'revenue_tracker_id',
            'VALUE_AFFILIATE_ID' => 'affiliate_id',
            'VALUE_DOBMONTH' => 'dobmonth',
            'VALUE_DOBDAY' => 'dobday',
            'VALUE_DOBYEAR' => 'dobyear',
            'VALUE_EMAIL' => 'email',
            'VALUE_FIRST_NAME' => 'first_name',
            'VALUE_LAST_NAME' => 'last_name',
            'VALUE_ZIP' => 'zip',
            'VALUE_CITY' => 'city',
            'VALUE_STATE' => 'state',
            'VALUE_BIRTHDATE' => 'birthdate',
            'VALUE_IP' => 'ip',
            'VALUE_ADDRESS1' => 'address',
            'VALUE_PHONE' => 'phone',
            'VALUE_PHONE1' => 'phone1',
            'VALUE_PHONE2' => 'phone2',
            'VALUE_PHONE3' => 'phone3',
            'VALUE_GENDER' => 'gender',
            'VALUE_PATH_ID' => 'path_id',
            //  BROWSER DETECT
            'DETECT_OS' => 'os',
            'DETECT_OS_VER' => 'os_version',
            'DETECT_BROWSER' => 'browser',
            'DETECT_BROWSER_VER' => 'browser_version',
            'DETECT_USER_AGENT' => 'user_agent',
            //  MOBILE DETECT
            'DETECT_DEVICE' => 'type',
            'DETECT_ISMOBILE' => 'isMobile',
            'DETECT_ISTABLET' => 'isTablet',
            'DETECT_ISDESKTOP' => 'isDesktop',
            // Callable
            'VALUE_GENDER_FULL' => function ($key) {
                $this->replace($key, ($this->dataToReplace['gender'] == 'F') ? 'Female' : 'Male');
            },
            'VALUE_TITLE' => function ($key) {
                $this->replace($key, ($this->dataToReplace['gender'] == 'M') ? 'Mr.' : 'Ms.');
            },
            'VALUE_PUB_TIME' => function ($key) {
                $this->replace($key, date('Y-m-d H:i:s'));
            },
            'VALUE_DATE_TIME' => function ($key) {
                $this->replace($key, date('Y-m-d H:i:s'));
            },
            'VALUE_TODAY' => function ($key) {
                $this->replace($key, date('m/d/Y'));
            },
            'VALUE_TODAY_MONTH' => function ($key) {
                $this->replace($key, date('m'));
            },
            'VALUE_TODAY_DAY' => function ($key) {
                $this->replace($key, date('d'));
            },
            'VALUE_TODAY_YEAR' => function ($key) {
                $this->replace($key, date('Y'));
            },
            'VALUE_TODAY_HOUR' => function ($key) {
                $this->replace($key, date('H'));
            },
            'VALUE_TODAY_MIN' => function ($key) {
                $this->replace($key, date('i'));
            },
            'VALUE_TODAY_SEC' => function ($key) {
                $this->replace($key, date('s'));
            },
            'VALUE_AGE' => function ($key) {
                $this->replace($key, date_diff(date_create($this->dataToReplace['birthdate']), date_create('today'))->y);
            },
            // EMP
            'VALUE_ETHNICITY' => function ($key) {
                if (array_key_exists('ethnicity', $this->dataToReplace)) {
                    $ethnicity = $this->dataToReplace['ethnicity'];
                } else {
                    $ethnicity = '';
                }
                $this->replace($key, $ethnicity);
            },
            'VALUE_CURRENT_CAMPAIGN_PRIORITY' => function ($key) {
                $this->replace($key, 1);
            },
            'VALUE_NEXT_CAMPAIGN_PRIORITY' => function ($key) {
                $this->replace($key, 2);
            },
            'VALUE_URL_SURVEY_PAGE' => function ($key) {
                $this->replace($key, '');
            },
            'VALUE_URL_REDIRECT_PAGE' => function ($key) {
                $this->replace($key, '');
            },
            'VALUE_URL_REDIRECT_STACK_PAGE' => function ($key) {
                $this->replace($key, '');
            },
        ];
    }

    /**
     * Callback function for replacing @var $key with @var $val
     *
     * @param  string  $key
     * @param  string  $val
     */
    protected function replace($key, $val)
    {
        if (strpos($this->html, '['.$key.']') !== false) {
            $this->html = str_replace('['.$key.']', $val, $this->html);
        }
    }

    /**
     * Replace sendleads url from NLR to TLR
     * For testing
     */
    protected function replaceSendLeadsUrl($targetUrl)
    {
        $nlrUrl = 'http://leadreactor.engageiq.com/sendLead/';
        $targetedUrl = $targetUrl.'sendLead/';

        if (strpos($this->html, $nlrUrl) !== false) {
            $this->html = str_replace($nlrUrl, $targetedUrl, $this->html);
        }
    }
}
