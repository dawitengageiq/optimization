<?php

namespace App\Http\Services\Charts\Providers;

use App\Exceptions\ChartResolverException;
use App\Http\Services\Charts\NLRChart;
use App\Http\Services\Charts\OLRChart;

class Interfaces
{
    /**
     * Application container, to be supplemented.
     */
    protected $app;

    /**
     * Chart type.
     */
    protected $type = '';

    /**
     * Path name of campaign list interface.
     */
    protected $contract = \App\Http\Services\Contracts\ChartContract::class;

    /**
     * Path name list with class name equivalent.
     */
    protected $className = [
        'olr' => OLRChart::class,
        'nlr' => NLRChart::class,
    ];

    /**
     * Instantiate.
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->type = $this->app->request->segment(3);

        if ($app->request->segment(1) == 'admin'
        && $app->request->segment(2) == 'chart'
        ) {
            $this->execute();

        }
    }

    /**
     * Static function.
     */
    public static function bind($app)
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
                return $this->app->bind($this->contract, $className);
            }
        } else {
            throw new ChartResolverException('no_graph');
        }
    }

    /**
     * Resolve what class to use.
     */
    protected function resolveClassName(): object
    {
        if (array_key_exists($this->type, $this->className)) {
            return $this->className[$this->type];
        }

        return '';
    }
}
