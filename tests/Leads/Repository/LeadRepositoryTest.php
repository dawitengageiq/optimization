<?php

use App\Http\Services\SendLeadsValidator;

class LeadRepositoryTest extends BrowserKitTestCase
{
    use SendLeadsTrait;

    private $leadStore;

    private $leadRepository;

    private $SUT;

    private $validator;

    private $rules = [];

    private $responseData = [];

    public function __construct()
    {
        $this->getLeadsForValidator();
        $this->validator = new SendLeadsValidator;
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function save_leads_to_leads_table_if_not_duplicate(): void
    {
        $response = new stdClass;
        $response->id = 1;
        $leadRepository = $this->getMockBuilder('App\Helpers\Repositories\LeadRepository')
            ->setMethods(['leadCreate'])
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->getMock();
        $leadRepository->expects($this->once())
            ->method('leadCreate')
            ->will($this->returnValue($response->id));
        $leadRepository->leadCreate($this->results, 1, 2);
        $this->assertIsObject($leadRepository);
    }
}
