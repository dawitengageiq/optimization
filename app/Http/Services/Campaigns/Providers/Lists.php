<?php

namespace App\Http\Services\Campaigns\Providers;

use App\Exceptions\CampaignListsResolverException;
use RevenueTracker;

class Lists
{
    /**
     * Application container, to be supplemented.
     */
    protected $app;

    /**
     * Current path.
     */
    protected $path = '';

    protected $acessTester = 'Smp59WaDzmrsceVRBtBrf2NXy5uYgfb3M4Dyfdebw7DP7Evf9NhaBA4fQNT8gTLtxb3uS6krmnygnqXjVZShE';

    /**
     * Instantiate.
     *
     * @return void;
     */
    public function __construct(
        \Illuminate\Foundation\Application $app,
        \App\Http\Services\Campaigns\Repos\RevenueTracker $revenueTracker
    ) {
        $this->revenueTracker = $revenueTracker;
        $this->app = $app;
        $this->path = $app->request->path();

        // Tester access
        if ($this->path == 'test/get_campaign_list'
        && $this->app->request->get('access_tester') != $this->acessTester) {
            throw new CampaignListsResolverException('forbidden');
        }

        // Check affiliate id,
        // If not available assign with empty value for it will be resolve in RevenueTracker::class
        if (! $this->app->request->has('affiliate_id')) {
            $this->app->request->request->add(['affiliate_id' => '']);
        }

        /* REVENUE_TRACKER */
        $this->revenueTracker->get($this->app->request->all());
        $this->app->request->request->add(['revenue_tracker' => $this->revenueTracker->details()]);

        $this->execute();

    }

    /**
     * Static function.
     */
    public static function boot(\Illuminate\Foundation\Application $app)
    {
        if ($app->request->path() == 'test/get_campaign_list'
        || $app->request->path() == 'api/get_campaign_list'
        ) {
            new static(
                $app,
                new \App\Http\Services\Campaigns\Repos\RevenueTracker(new \App\AffiliateRevenueTracker)
            );
        }
    }

    /**
     * Bootstrap any application for campaign listing.
     */
    protected function execute(): void
    {
        Stack::bind($this->app, $this->revenueTracker->orderType());

        Limit::bind($this->app, $this->revenueTracker->mixedCoregLimit());
    }
}
