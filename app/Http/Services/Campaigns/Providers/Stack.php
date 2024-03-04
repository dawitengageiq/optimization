<?php

namespace App\Http\Services\Campaigns\Providers;

use App\Http\Services\Campaigns\Utils\Lists\Stacking\ByMixCoregPages;
use App\Http\Services\Campaigns\Utils\Lists\Stacking\ByPerCampaignType;
use App\Http\Services\Campaigns\Utils\Lists\Stacking\ByPriority;

class Stack
{
    /**
     * Application container, to be supplemented.
     */
    protected $app;

    /**
     * Current path.
     */
    protected $path = '';

    /**
     * default.
     */
    protected $type = 0;

    protected $stackType = 'default';

    protected $pathTypeLimit = [];

    protected $pathOrderType = [];

    /**
     * Path name of campaign list interface.
     */
    protected $contract = \App\Http\Services\Campaigns\Utils\Lists\Contracts\StackContract::class;

    /**
     * Stack type list with class name equivalent.
     */
    protected $stackTypeClass = [
        'campaign_type' => ByPerCampaignType::class,
        'mixed_coreg_type' => ByMixCoregPages::class,
        'default' => ByPriority::class,
    ];

    /**
     * Instantiate.
     *
     *  @param  Illuminate\Foundation\Application  $app
     */
    public function __construct(
        \Illuminate\Foundation\Application $app,
        string $orderType
    ) {
        $this->app = $app;
        $this->path = $app->request->path();
        $this->type = $orderType;
        $this->pathOrderType = config('constants.PATH_ORDER_TYPE');

        $this->execute();
    }

    /**
     * Static function.
     *
     *  @param  Illuminate\Foundation\Application  $app
     */
    public static function bind(
        \Illuminate\Foundation\Application $app,
        string $orderType
    ) {
        new static($app, $orderType);
    }

    /**
     * Bind the class to use.
     */
    protected function execute()
    {
        $this->app->bind($this->contract, $this->stackTypeClass[$this->resolveStackType()]);

        // Add some data to request, it will be used later by controller
        $this->addDAta2Request();
    }

    /**
     * Resolve what limit type to use.
     */
    protected function resolveStackType(): string
    {
        if ('test/get_campaign_list_by_api' != $this->path
        && in_array($this->type, $this->pathOrderType)) {
            $this->stackType = str_ireplace(' ', '_', strtolower($this->type));
        }

        return $this->stackType;
    }

    /**
     * Add some data to request
     */
    protected function addDAta2Request()
    {
        $this->app->request->request->add(['order_type' => $this->stackType]);
        $this->app->request->request->add(['stack_type' => $this->stackType]);
    }
}
