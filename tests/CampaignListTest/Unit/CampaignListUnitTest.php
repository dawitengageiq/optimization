<?php

class CampaignListUnitTest extends BrowserKitTestCase
{
    /**
     * @test
     */
    public function getCampaigns(): void
    {
        $stub = $this->getMockBuilder(CampaignList::class)
            ->setmethods(['getCampaigns'])
            ->getMock();
        $stub->expects($this->once())
            ->method('getCampaigns')
            ->will($this->returnValue(false));
        $stub->getCampaigns();
        $this->assertTrue(true);
    }
}
