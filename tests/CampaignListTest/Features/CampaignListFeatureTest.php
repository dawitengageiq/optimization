<?php

namespace tests\CampaignListFeatureTest\Features;

use App\Jobs\SaveLeadUser;

class CampaignListFeatureTest extends BrowserKitTestCase
{
    use SendLeadsTrait;

    /**
     * @test
     */
    public function test_campaign_test_controller_class_exist(): void
    {
        $this->assertTrue(class_exists('\\App\\Http\\Controllers\\Test\\CampaignListTestController'));
    }

    /**
     * @test
     */
    public function test_if_save_lead_user_job_is_running(): void
    {
        $this->getLeadsForValidator();
        $this->expectsJobs(SaveLeadUser::class);
        dispatch(new SaveLeadUser($this->results));
    }

    /**
     * @test
     */
    public function test_it_will_return_path_type(): void
    {
        $response = new stdClass;
        $campaign = $this->getMockBuilder(CampaignListTestController::class)
            ->disableOriginalConstructor()
            ->setMethods(['registerUserAndGetCampaigns'])
            ->getMock();
        $campaign->expects($this->once())
            ->method('registerUserAndGetCampaigns')
            ->will($this->returnValue($response));
        $campaign->registerUserAndGetCampaigns();
        $revenueTracker = RevenueTracker::pathType();
        $this->assertSame(2, $revenueTracker);
    }

    /**
     * @test
     */
    public function filter_campaign(): void
    {
        $response = new stdClass();
        $this->getLeadsForValidator();
        $campaign = $this->getMockBuilder(CampaignListTestController::class)
            ->disableOriginalConstructor()
            ->setMethods(['filterCampaigns'])
            ->getMock();
        $campaign->expects($this->once())
            ->method('filterCampaigns')
            ->will($this->returnValue($response));
        $campaign->filterCampaigns($this->results);
    }
}
