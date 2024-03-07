<?php

namespace App\Http\Services\Campaigns\Providers;

use App\Http\Services\Campaigns\Utils\Lists\Limit\StackType\ByMixCoreg;
use App\Http\Services\Campaigns\Utils\Lists\Limit\StackType\ByPathType;

class Limit
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
     * limit.
     */
    protected $pathTypeLimit = [];

    /**
     * Path name of limit interface.
     */
    protected $contract = \App\Http\Services\Campaigns\Utils\Lists\Contracts\LimitContract::class;

    /**
     * Limit type list with class name equivalent.
     */
    protected $limitType = [
        'campaign_type' => ByPathType::class,
        'mixed_coreg_type' => ByMixCoreg::class,
        'default' => ByPathType::class,
    ];

    /**
     * Instantiate.
     *
     * @param  Illuminate\Foundation\Application  $app
     */
    public function __construct(
        \Illuminate\Foundation\Application $app,
        string $mixedCoregLimit,
        \App\Http\Services\Campaigns\Repos\Settings $settings
    ) {
        $this->app = $app;
        $this->settings = $settings;
        $this->mixedCoregLimit = $mixedCoregLimit;

        $this->settings->get();
        $this->app->request->request->add(['campaign_settings' => $this->settings->details()]);

        $this->stackType = $app->request->get('stack_type');

        $this->execute();
    }

    /**
     * Static function.
     *
     * @param  array  $args
     */
    public static function bind(...$args)
    {
        new static(
            $args[0],
            $args[1],
            new \App\Http\Services\Campaigns\Repos\Settings(new \App\Setting)
        );
    }

    /**
     * Bind the class to use.
     */
    protected function execute()
    {
        // binding limit class
        $this->app->bind($this->contract, $this->limitType[$this->stackType]);

        // Resolve what are limit data will be used.
        $this->resolveLimitData();
    }

    /**
     * Resolve what limit type to use.
     *
     * @return string
     */
    protected function resolveLimitData()
    {
        $pathTypeLimit = $this->settings->pathTypeLimit();

        if ($pathTypeLimit) {
            $this->pathTypeLimit = json_decode($pathTypeLimit, true);
        } else {
            $this->pathTypeLimit = [];
        }

        // Add some data to request, it will be used later by controller
        $this->addDAta2Request();
    }

    /**
     * Add some data to request
     */
    protected function addDAta2Request()
    {
        $limitType = [
            'campaign_type' => function () {
                $this->app->request->request->add(['limit' => $this->pathTypeLimit]);
                $this->app->request->request->add(['limit_type' => ($this->pathTypeLimit) ? 'Campaign type' : 'First level']);
            },
            'mixed_coreg_type' => function () {
                $this->mixedCoregLimit = $this->mixedCoregLimit;
                $this->app->request->request->add(['limit' => $this->limit()]);
                $this->app->request->request->add(['limit_type' => ($this->mixedCoregLimit) ? 'Mixed coreg type' : 'First level']);
            },
            'default' => function () {
                $this->app->request->request->add(['limit' => $this->pathTypeLimit]);
                $this->app->request->request->add(['limit_type' => ($this->pathTypeLimit) ? 'Campaign type' : 'First level']);
            },
        ];

        // Run callable
        $limitType[$this->stackType]();
    }

    /**
     * Set limits per campaign type
     */
    protected function limit(): string
    {
        $coregTypes = array_keys(config('constants.MIXED_COREG_TYPE_FOR_ORDERING'));
        if (count($this->pathTypeLimit)) {
            foreach ($coregTypes as $coregType) {
                $this->pathTypeLimit[$coregType] = $this->mixedCoregLimit;
            }

            return $this->pathTypeLimit;
        }

        return iterator_to_array(
            $this->applyLimit(
                $coregTypes,
                $this->mixedCoregLimit
            )
        );
    }

    /**
     * Iterate and apply limit
     *
     * @param  array  $mixedCoregs
     * @return yield
     */
    protected function applyLimit($coregTypes, string $limit)
    {
        // foreach($mixedCoregs as $mixedCoreg) {
        for ($i = 0; $i < count($coregTypes); $i++) {
            yield $coregTypes[$i] => $limit;
        }
    }
}
