<?php

namespace App\Console\Commands;

use App\CampaignCreative;
use App\Commands\RandomProbability;
use App\Helpers\Repositories\LeadData;
use App\Jobs\TestJob;
use App\Lead;
use Bus;
use Illuminate\Console\Command;
use Log;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:test-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Artisan test';

    private $leadData;

    /**
     * Create a new command instance.
     */
    public function __construct(LeadData $leadData)
    {
        $this->leadData = $leadData;

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /*
        //probability test
        $set = [
            9 => .5,
            14 => .3,
            15 => .2,
        ];

        // $set = CampaignCreative::where('campaign_id',1)->lists('weight','id')->toArray();

        $result = [];
        foreach($set as $id => $weight)
        {
            $result[$id] = 0;
        }

        $this->info('Executing random probability with sample size of 4000.');

        for($i = 0; $i < 4000; $i++)
        {
            $id = Bus::dispatch(new RandomProbability($set));
            ++$result[$id];
        }

        foreach($result as $id => $count)
        {
            $this->info("$id - $count");
        }
        */

        //$job = (new TestJob('Test Job!'))->delay(1);
        //dispatch($job);

        /*
        $lead = Lead::find(5178014);
        $this->leadData->updateOrCreateLeadMessage($lead,'awts grrrr!');
        $this->info('done!');
        */

        Log::info('Schedule Test!!!');
    }
}
