<?php

use App\Http\Services\Leads\LeadStore;
use App\Http\Services\SendLeadsValidator;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class StoreLeadsTest extends BrowserKitTestCase
{
    //use DatabaseMigrations;
    use SendLeadsTrait;

    private $leadStore;

    private $leadRepository;

    private $SUT;

    private $validator;

    private $rules = [];

    private $responseData = [];

    public function __construct()
    {

        $this->validator = new SendLeadsValidator;
        $this->getLeadsForValidator();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->leadRepository = $this->getMock('App\Helpers\Repositories\LeadInterface');
        $this->leadData = $this->getMock(\App\Helpers\Repositories\LeadData::class);
        $this->SUT = new LeadStore($this->leadData, $this->leadRepository);
    }

    /**
     * @test
     *
     * @expectedException Exception
     */
    public function check_if_leadCreate_method_is_called_when_calling_proceedtosave_method(): void
    {
        $leadData = $this->getMock(\App\Helpers\Repositories\LeadData::class);
        $leadIn = $this->getMockbuilder('App\Helpers\Repositories\LeadInterface')
            ->setMethods(['leadCreate'])
            ->getMock();
        $leadIn->expects($this->any())
            ->method('leadCreate')
            ->will($this->returnValue(1));
        $leadIn->leadCreate($this->results, 1, 2);
        $re = $this->SUT->proceedtosave($this->results, 1, 2);
    }

    /**
     * test
     */
    public function check_save_lead_csv_data()
    {

    }
}
