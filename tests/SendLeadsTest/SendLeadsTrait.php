<?php

/**
 * part of SendLeadTesting Suite
 * this Trait is reusable
 */
trait SendLeadsTrait
{
    /**
     * example query string from Paid To Research to Become VeryRichInYourDreams.com
     *
     * @var string
     */
    // protected $leads ="eiq_campaign_id=238&eiq_affiliate_id=1&eiq_email=meme@yahoo.com&rev_tracker=CD1&eiq_path_id=21&ip=122.3.181.235&first_name=testname&last_name=testlast&zip=38105&date_of_birth=05%2F03%2F1998&gender=M&createDate=03%2F17%2F2017+05%3A16%3A00&yesnoto=&vindale-campaign=YES&xxTrustedFormToken=https%3A%2F%2Fcert.trustedform.com%2Faae71fe582c0e0614d922fd4c056b6bebd4fc498&xxTrustedFormCertUrl=https%3A%2F%2Fcert.trustedform.com%2Faae71fe582c0e0614d922fd4c056b6bebd4fc498&ignore=1";

    // protected $leads="submit=engageiq_post_data&eiq_affiliate_id=1&offer_id=&s1=&s2=&s3=&s4=&s5=&address=&phone1=&phone2=&phone3=&phone=0956m674324234&source_url=http%3A%2F%2Fwww.paidforresearch.local%2Flazy_loading%2Fregistration.php%3Faffiliate_id%3D%26offer_id%3D%26eiq_campaign_id%1D%26s1%3D%26s2%3D%26s3%3D%26s4%3D%26s5%3D%26address%3D%26phone1%3D%26phone2%3D%26phone3%3D%26firstname%3D%26lastname%3D%26dobmonth%3D%26dobday%3D%26dobyear%3D%26email%3D%26gender%3D%26ethnicity%3D%26zip%3D&screen_view=1&ip=127.0.0.1&image=&first_name=jeremie&last_name=yunsay&eiq_email=meme@yahoo.com&zip=38105&birthdate=1977-07-23&dobmonth=07&dobday=23&dobyear=1977&gender=M&chk_agree=&submitBtn=Submit&eiq_campaign_id=2&eiq_realtime=1&eiq_redirect_url=";
    protected $leads = 'submit=engageiq_post_data&eiq_affiliate_id=1&offer_id=1&s1=&s2=&s3=&s4=&s5=&address=&phone1=&phone2=&phone3=&phone=0956m674324234&source_url=http%3A%2F%2Fwww.paidforresearch.local%2Flazy_loading%2Fregistration.php%3Faffiliate_id%3D%26offer_id%3D%26eiq_campaign_id%1D%26s1%3D%26s2%3D%26s3%3D%26s4%3D%26s5%3D%26address%3D%26phone1%3D%26phone2%3D%26phone3%3D%26firstname%3D%26lastname%3D%26dobmonth%3D%26dobday%3D%26dobyear%3D%26email%3D%26gender%3D%26ethnicity%3D%26zip%3D&screen_view=1&ip=127.0.0.1&image=&first_name=&last_name=yunsay&eiq_email=memememoouuoa@yahoo.com&zip=38105&birthdate=1977-07-23&dobmonth=07&dobday=23&dobyear=1977&gender=M&chk_agree=&submitBtn=Submit&eiq_campaign_id=2&eiq_realtime=1&eiq_redirect_url=';

    /**
     * array of query string converted to key value(array of course)
     *
     * @var array
     */
    protected $results = [];

    /**
     * leads
     *
     * @var array
     */
    private $qry = [];

    /**
     * transform query string into array
     */
    protected function getLeadsForValidator()
    {
        $this->qry = $this->leads;
        //string must contain at least one = and cannot be in first position
        if (strpos($this->qry, '=')) {
            if (strpos($this->qry, '?') !== false) {
                $q = parse_url($this->qry);
                $this->qry = $q['query'];
            }
        } else {
            return false;
        }

        foreach (explode('&', $this->qry) as $couple) {
            [$key, $val] = explode('=', $couple);
            $this->results[$key] = $val;
        }
    }
}
