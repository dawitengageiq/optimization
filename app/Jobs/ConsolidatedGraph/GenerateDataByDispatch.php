<?php

namespace App\Jobs\ConsolidatedGraph;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class GenerateDataByDispatch implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * Instantiated needed class
     */
    protected $affiliates;

    protected $revenueAndViews;

    /**
     * Carbon / Date
     */
    protected $carbon;

    protected $date;

    protected $suppliedDate;

    /**
     * Listds of revenue tracker ids that are updated.
     *
     * @var array
     */
    protected $updatedRevTracker = [];

    /**
     * Email recipient
     *
     * @var string
     */
    protected $adminEmail;

    protected $campaigns = [];

    /**
     * Inintialization.
     */
    public function __construct($date = '')
    {
        // Instantiate needed class
        $this->carbon = new Carbon;
        $this->affiliates = new Utils\AffiliatesRevenueTracker(new \App\Affiliate);
        $this->revenueAndViews = new Utils\RevenueAndViews(
            new \App\ConsolidatedGraph,
            $this->carbon
        );
        $this->settings = new Utils\Settings(new \App\Setting);
        $this->campaign = new \App\Campaign;

        // Date use to query
        if ($date) {
            $this->suppliedDate = $date;
            $this->date = $this->carbon->parse($date);
        } else {
            $this->date = $this->carbon->yesterday();
        }

        // Provide benchmarks
        $this->affiliates->setBenchmarks($this->settings->campaignTypeBenchmarks());
        $this->revenueAndViews->setBenchmarks($this->settings->campaignTypeBenchmarks());

        // Fetch all nixcoreg campaign
        $campaigns = $this->campaign->whereIn('campaign_type', [1, 2, 8, 13])
            ->get(['id', 'campaign_type'])
            ->groupBy('campaign_type')
            ->toArray();

        // Regroup for easy access
        $this->campaigns[1] = array_column($campaigns[1], 'id');
        $this->campaigns[2] = array_column($campaigns[2], 'id');
        $this->campaigns[8] = array_column($campaigns[8], 'id');
        $this->campaigns[13] = array_column($campaigns[13], 'id');

        // Exit page offer id as default for every affilaites
        // If the revenue tracker have its own exit page id, a new query will occur, see: App\Jobs\ConsolidatedGraph\Utils\Traits\CakeRevenue::class
        $this->affiliates->setOfferID($this->campaign->where('id', $this->settings->benchmark('Exit Page'))->first(['linkout_offer_id']));

        // Set the campaign types, we need the campaign types of mix coreg 1 and 2
        $this->revenueAndViews->setCampaignTypes(config('constants.CAMPAIGN_TYPES'));

        // Set the admin email as recipient
        $this->adminEmail = $this->settings->adminEmail();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Time execution starts
        $this->startLog = $this->carbon->now();

        // Provide the current date.
        $this->affiliates->setDate($this->date->format('Y-m-d'));

        // Fetch affilates with coresponding relationship in reference to current date
        $this->affiliates->pluck();

        // go through all the revenue trackers the affilate has.
        foreach ($this->getAffiliateRevenueTracker() as $revenueTracker) {
            // Discontinue if affilate dont have revenue trackers.
            if (! $revenueTracker) {
                continue;
            }
            // Discontinue if no records for survey takers and clicks
            if (! $revenueTracker->clicksVsRegistrationStatistics) {
                continue;
            }
            if ($revenueTracker->clicksVsRegistrationStatistics['registration_count'] <= 0) {
                continue;
            }
            if ($revenueTracker->clicksVsRegistrationStatistics['clicks'] <= 0) {
                continue;
            }

            // Id will be included in email
            $this->updatedRevTracker[] = $revenueTracker->revenue_tracker_id;

            // Extract the data from other tables for each revenue tracker
            $revData = $this->revenueAndViews->extractDataFromOtherTables($revenueTracker, $this->campaigns, $this->date);

            // Set the parameters needed to generate records
            // The extracted data will be used to fill the needed data to be save.
            $this->revenueAndViews->setParams($revData);

            // Check if revenue tracker records is existing in reference to supplied date,
            // if not create new model instance
            // $this->suppliedDate was produced by running the script manually, means there is possibly a record exists, we dont need to insert the records as new.
            // Running the script by task scheduler, means the date is yesterday and we're sure that there's no record exists.
            $this->revenueAndViews->checkRevenueTrackerExist($this->suppliedDate);

            // This time generate records for parameters was set.
            $this->revenueAndViews->generateRecords();
            // Save the generated records
            $this->revenueAndViews->save($this->date);
            // Reset parameters to default for next loop.
            $this->revenueAndViews->setParams([]);
        }

        // Execute email notification
        $this->sendEmail();
    }

    /**
     * Get the revenue tracker of affilate by generator.
     *
     * @return yield|generator
     */
    protected function getRevenueTracker()
    {
        foreach ($this->affiliates->get() as $affiliate) {
            for ($i = 0; $i < count($affiliate->revenueTracker); $i++) {
                yield $affiliate->revenueTracker[$i];
            }
        }
    }

    protected function getAffiliateRevenueTracker()
    {
        foreach ($this->affiliates->get() as $affiliate) {
            yield $affiliate->affiliateRevenueTracker;
        }
    }

    /**
     * Send notification email
     */
    protected function sendEmail()
    {
        // Do the Email
        Mail::send(
            'emails.consolidated_graph',
            [
                'revTrackerIDs' => $this->updatedRevTracker,
                'dateExecuted' => $this->carbon->parse($this->date),
                'executionDuration' => $this->startLog->diffInSeconds($this->carbon->now()).' seconds',
            ],
            function ($mail) {
                $mail->from('noreply@engageiq.com')
                    ->to('burt@engageiq.com', 'Marwil Burton')
                    ->subject('Job: Consolidated Graph Data');
            }
        );
    }
}
