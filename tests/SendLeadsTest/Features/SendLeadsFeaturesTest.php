<?php

use App\Http\Services\SendLeadsValidator;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SendLeadsFeaturesTest extends BrowserKitTestCase
{
    use DatabaseTransactions;
    use SendLeadsTrait;

    /**
     * @group failing
     * Tests
     */
    private $errors;

    private $rules = [];

    private $validator;

    public function __construct()
    {
        $this->validator = new SendLeadsValidator;
    }

    /**
     * @test
     * first of all, check if is string.
     */
    public function test_if_leads_variables_is_type_of_string(): void
    {
        $leads = is_string($this->leads);
        $this->assertTrue($leads);
    }

    public function test_if_query_string_contains_data(): void
    {
        $query = explode('&', $this->leads);
        foreach ($query as $param => $val) {
            [$param, $value] = explode('=', $val, 2);
            $ids[] = urldecode($val);
        }
        $this->assertContains('eiq_campaign_id=2', $ids);
        $this->assertNotContains('eiq_affiliate_id=2', $ids);
    }

    public function test_if_no_email_throws_the_error(): void
    {
        /**
         * eiq_email has no value, serves the purpose here
         *
         * @var string
         */
        $this->leads = str_replace('eiq_email', '', $this->leads);
        $this->rules = [
            'eiq_email' => 'required',
        ];

        $this->getLeadsForValidator();
        if ($this->validator->validateEmailAffiliateCampaign($this->results, $this->rules) === false) {
            $this->errors = $this->validator->getErrors();
            /**
             * let's show the evidence
             */
            echo 'from ....'.' '.__METHOD__."\n";
            var_dump($this->errors);
            echo "..................................\n";
        } else {
        }
        $status = $this->errors['status'];
        $this->assertArrayHasKey('status', $this->errors);
        $this->assertContains('eiq_email_empty', $this->errors);
        $this->assertArrayHasKey('message', $this->errors);
        $this->assertContains(' Eiq Email Empty', $this->errors);
    }

    public function test_if_no_affiliate_id_throws_the_error(): void
    {
        /**
         * @test
         * no affiliate id here, that will serve the purpose here
         */
        $this->leads = str_replace('eiq_affiliate_id', '', $this->leads);
        /**
         * the rules the validator should check
         *
         * @var array
         */
        $this->rules = [
            'eiq_affiliate_id' => 'required',
        ];

        $this->getLeadsForValidator();
        if ($this->validator->validateEmailAffiliateCampaign($this->results, $this->rules) === false) {
            $this->errors = $this->validator->getErrors();
            /**
             * let's show the evidence,otherwise you are subject to firing squad for not telling the truth
             */
            echo 'from ....'.' '.__METHOD__."\n";
            var_dump($this->errors);
            echo "..................................\n";
        } else {
        }
        $this->assertArrayHasKey('status', $this->errors);
        $this->assertContains('eiq_affiliate_id_empty', $this->errors);
        $this->assertArrayHasKey('message', $this->errors);
        $this->assertContains(' Eiq Affiliate Id Empty', $this->errors);
    }

    public function test_if_no_campaign_id_throws_the_error(): void
    {
        /**
         * @test
         * no campaign_id here, that will serve the purpose
         */
        $this->leads = str_replace('eiq_campaign_id', '', $this->leads);
        /**
         * the rules the validator should check
         *
         * @var array
         */
        $this->rules = [
            'eiq_campaign_id' => 'required',
        ];

        $this->getLeadsForValidator();
        if ($this->validator->validateEmailAffiliateCampaign($this->results, $this->rules) === false) {
            $this->errors = $this->validator->getErrors();
            /**
             * let's show the evidence
             */
            echo 'from ....'.' '.__METHOD__."\n";
            var_dump($this->errors);
            echo "..................................\n";

        } else {
        }//this should never be called
        $this->assertArrayHasKey('status', $this->errors);
        $this->assertContains('eiq_campaign_id_empty', $this->errors);
        $this->assertArrayHasKey('message', $this->errors);
        $this->assertContains(' Eiq Campaign Id Empty', $this->errors);
    }

    /**
     * @test
     */
    public function test_redirect_to_some_url_if_eiq_redirect_url_is_in_the_query_string(): void
    {
        $this->leads = $this->leads.'http://www.google.com';
        $this->getLeadsForValidator();
        $this->assertContains('http://www.google.com', $this->results['eiq_redirect_url']);
    }

    public function kabaw_case($var)
    {
        return preg_replace('/(?<!\ )[A-Z]/', ' $0', $var);
    }
}
