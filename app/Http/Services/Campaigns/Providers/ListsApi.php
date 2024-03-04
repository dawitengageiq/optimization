<?php

namespace App\Http\Services\Campaigns\Providers;

use App\Exceptions\CampaignListsResolverException;
use App\Http\Services\Campaigns\ListsApiMultiplePage;
use App\Http\Services\Campaigns\ListsApiOnePage;

class ListsApi
{
    /**
     * Application container, to be supplemented.
     */
    protected $app;

    /**
     * default.
     */
    protected $page = 'one_page';

    /**
     * Path name of campaign list interface.
     */
    protected $contract = \App\Http\Services\Contracts\CampaignListContract::class;

    /**
     * Path name list with class name equivalent.
     */
    protected $className = [
        'one_page' => ListsApiOnePage::class,
        'multi_page' => ListsApiMultiplePage::class,
    ];

    /**
     * Instantiate.
     */
    public function __construct(
        \Illuminate\Foundation\Application $app,
        \App\Http\Services\Campaigns\Repos\ApiConfig $apiConfig,
        \App\Http\Services\Campaigns\Repos\RevenueTracker $revenueTracker,
        \App\Http\Services\Campaigns\Repos\AffiliateWebsites $affiliateWebsites
    ) {
        $this->app = $app;
        $this->apiConfig = $apiConfig;
        $this->revenueTracker = $revenueTracker;
        $this->affiliateWebsites = $affiliateWebsites;

        /* If affiliate id is not number */
        if (! is_numeric($this->app->request->get('affiliate_id'))) {
            throw new CampaignListsResolverException('invalid_affiliate_id');
        }
        // Check for affiliate id for it is required
        if (! $this->app->request->has('affiliate_id')) {
            throw new CampaignListsResolverException('provide_affiliate_id');
        }
        // Check for website id for it is required
        if (! $this->app->request->has('website_id')) {
            throw new CampaignListsResolverException('provide_website_id');
        }

        /* REVENUE_TRACKER */
        $this->revenueTracker->external($this->app->request->all());
        $this->app->request->request->add(['revenue_tracker' => $this->revenueTracker->details()]);

        /* AFFILIATE API CONFIGS */
        $this->apiConfig->get($this->app->request->get('affiliate_id'));
        $this->app->request->request->add(['api_config' => $this->apiConfig->details()]);
        if ($this->apiConfig->isMultiPage()) {
            $this->page = 'multi_page';
        }

        /* AFFILIATE WEBSITES */
        $this->affiliateWebsites->pluck(
            $this->app->request->get('website_id'),
            $this->app->request->get('affiliate_id'),
            $this->app->request->get('email'),
            $this->apiConfig->timeInterval()
        );

        $this->app->request->request->add(['affiliate_websites' => [
            'is_registered' => $this->affiliateWebsites->isRegistered(),
            'is_disabled' => $this->affiliateWebsites->isDisabled(),
            'is_email_unique' => $this->affiliateWebsites->isEmailUnique(),
        ],
        ]);

        $this->execute();
    }

    /**
     * Static function.
     */
    public static function boot(\Illuminate\Foundation\Application $app)
    {
        // make sure the page is for iframe api
        if ('frame/get_campaign_list_by_api' == $app->request->path()
        ) {
            new static(
                $app,
                new \App\Http\Services\Campaigns\Repos\ApiConfig(new \App\AffiliateApiConfigs),
                new \App\Http\Services\Campaigns\Repos\RevenueTracker(new \App\AffiliateRevenueTracker),
                new \App\Http\Services\Campaigns\Repos\AffiliateWebsites(new \App\AffiliateWebsite, new \App\WebsitesViewTrackerDuplicate)
            );
        }
    }

    /**
     * Bootstrap any application for campaign listing by api.
     */
    protected function execute()
    {
        // Campaign list interface
        $this->app->bind($this->contract, $this->className[$this->page]);

        // Facade
        Stack::bind($this->app, $this->revenueTracker->orderType());

        // Limit interface
        LimitApi::bind($this->app, $this->apiConfig->displayLimit());
    }
}
