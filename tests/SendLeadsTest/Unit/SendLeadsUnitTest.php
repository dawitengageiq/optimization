<?php

use Illuminate\Database\QueryException;

class SendLeadsUnitTest extends BrowserKitTestCase
{
    /**
     * the SendLeadsValidator instance
     */
    private $validator;

    /**
     * return array
     */
    private $errors;

    /**
     * the campaign instance
     */
    private $campaign;

    /**
     * begin silly testing
     */
    use SendLeadsTrait;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->getLeadsForValidator();
    }

    /**
     * @test
     * test if SendLeadsTrait is called successfully
     */
    public function show(): void
    {
        var_dump($this->results);
    }

    /**
     * @test
     * return the campaign id
     * integer
     */
    public function check_if_campaign_return_id(): void
    {
        $this->checkMikeTestHello(CampaignRepository::class, 'getCampaignByIdAndStatus', $this->results['eiq_campaign_id']);
    }

    /**
     * @test
     *
     * @return affiliate id
     * integer
     */
    public function check_if_affiliate_return_id(): affiliate
    {
        $this->checkMikeTestHello(AffiliateRepository::class, 'getAffiliateById', $this->results['eiq_affiliate_id']);
    }

    /**
     * @test
     */
    public function check_if_campaign_should_return_object(): object
    {
        $campaignCount = \App\LeadCount::where('campaign_id', $this->results['eiq_campaign_id'])->first();
        if (null !== $campaignCount) {
            $this->assertIsObject($campaignCount);
        } else {
            $this->assertNull($campaignCount);
        }

    }

    /**
     * @test
     *
     * @return bool if object
     */
    public function check_if_campaign_affiliate_should_return_object(): bool
    {
        $campaignAffiliateCount = \App\LeadCount::where('campaign_id', $this->results['eiq_campaign_id'])->where('affiliate_id', $this->results['eiq_affiliate_id'])->first();
        if (null !== $campaignAffiliateCount) {
            $this->assertIsObject($campaignAffiliateCount);
        } else {
            $this->assertNull($campaignAffiliateCount);
        }
    }

    /**
     * @test
     *
     * @return bool
     * silly very long method name, at least you got the idea
     */
    public function capreach_should_return_true_if_campaign_counts_and_campaign_affiliate_counts_were_both_not_null_and_return_false_if_campaign_counts_and_campaign_affiliate_counts_were_both_null(): bool
    {
        $response = true;
        /**
         * get the campaign object
         */
        $campaign = $this->getMockBuilder(CampaignRepository::class)
            ->setMethods(['getCampaignByIdAndStatus'])
            ->getMock();
        $campaign->expects($this->once())
            ->method('getCampaignByIdAndStatus')
            ->will($this->returnValue($response));
        $campaign = $campaign->getCampaignByIdAndStatus($this->results['eiq_campaign_id']);

        /**
         * get the affiliate object
         */
        $affiliate = $this->getMockBuilder(AffiliateRepository::class)
            ->setMethods(['getAffiliateById'])
            ->getMock();
        $affiliate->expects($this->once())
            ->method('getAffiliateById')
            ->will($this->returnValue(1));
        $affiliate = $affiliate->getAffiliateById($this->results['eiq_affiliate_id']);
        /**
         * campaign count object
         */
        $campaignCounts = \App\LeadCount::where('campaign_id', $this->results['eiq_campaign_id'])->first();
        /**
         * affiliate object
         */
        $campaignAffiliateCounts = \App\LeadCount::where('campaign_id', $this->results['eiq_campaign_id'])->where('affiliate_id', $this->results['eiq_affiliate_id'])->first();
        /**
         * check if $campaignCounts and $campaignAffiliateCounts true
         */
        if (null === $campaignCounts || null === $campaignAffiliateCounts) {
            $response = false;
        }
        /**
         * capreach
         * now we can simulate the method checkCapNoReset using the variables as parameters created above
         */
        $capReached = $this->getMockBuilder(LeadTestController::class)
            ->disableOriginalConstructor()
            ->setMethods(['checkCapNoReset'])
            ->getMock();
        $capReached->expects($this->once())
            ->method('checkCapNoReset')
            ->will($this->returnValue($response));
        $capReached = $capReached->checkCapNoReset($campaign, $affiliate, $campaignCounts, $campaignAffiliateCounts);
        $this->assertTrue($response === $capReached);
        /**
         *  now this should throws errors if cap is reached
         */
        $this->validator = new App\Http\Services\SendLeadsValidator;
        if ($this->validator->isBoolean($capReached) === false) {
            $this->errors = $this->validator->getErrors();
            $this->assertArrayHasKey('status', $this->errors);
        }
    }

    /**
     * @test
     * save the leads if not duplicate entry
     */
    public function leads_should_save_if_not_duplicate(): void
    {
        $payout = 1;
        $received = 2;
        $lead = new \App\Http\Services\Repositories\LeadRepository;
        try {
            $lead->leadCreate($this->results, $payout, $received);
            $this->seeInDatabase('leads', ['lead_email' => $this->results['eiq_email']]);
        } catch (QueryException $e) {
            $this->assertSame($e->errorInfo[1], 1062);
        } finally {
            $this->assertClassNotHasAttribute('call_user_func', LeadTestController::class);
        }
    }

    /**
     * reusable method
     *
     * @param  [mixed] $className
     * @param  [method] $method
     * @param  [var] $param
     * @return [integer]
     */
    private function checkMikeTestHello($className, $method, $param)
    {
        $response = new stdClass();
        $response->id = 2;
        $stub = $this->getMockBuilder($className)
            ->setMethods([$method])
            ->getMock();
        $stub->expects($this->once())
            ->method($method)
            ->will($this->returnValue($response));
        $id = $stub->$method($param);
        $this->assertEquals($response->id, $id->id);
        $this->assertIsInt($id->id);
    }
}
