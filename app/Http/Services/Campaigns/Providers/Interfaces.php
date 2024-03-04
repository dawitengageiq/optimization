<?php

namespace App\Http\Services\Campaigns\Providers;

use App\Http\Services\Campaigns\Lists;

class Interfaces
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
     * Path name of campaign list interface.
     */
    protected $contract = \App\Http\Services\Contracts\CampaignListContract::class;

    /**
     * Path name list with class name equivalent.
     */
    protected $className = [
        'test/get_campaign_list' => Lists::class,
        'api/get_campaign_list' => Lists::class,
    ];

    /**
     * Instantiate.
     */
    public function __construct(\Illuminate\Foundation\Application $app)
    {
        $this->app = $app;
        $this->path = $app->request->path();

        $this->execute();
    }

    /**
     * Static function.
     */
    public static function bind(\Illuminate\Foundation\Application $app)
    {
        new static($app);
    }

    /**
     * Bind the class to use.
     */
    protected function execute()
    {
        if ($className = $this->resolveClassName()) {
            if (class_exists($className)) {
                $this->app->bind($this->contract, $className);
            }
        }
    }

    /**
     * Resolve what class to use.
     */
    protected function resolveClassName()
    {
        if (array_key_exists($this->path, $this->className)) {
            return $this->className[$this->path];
        }
       //$oVal = (object)[];
       return '';
    }
}
