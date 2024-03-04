<?php

namespace App\Http\Services\Campaigns\Providers;

class LimitApi extends Limit
{
    /**
     * Instantiate.
     */
    public function __construct(
        \Illuminate\Foundation\Application $app,
        array $limit
    ) {
        $this->app = $app;
        $this->limit = $limit;
        $this->stackType = $app->request->get('stack_type');

        $this->execute();
    }

    /**
     * Static function.
     */
    public static function bind(array ...$args)
    {
        new static($args[0], $args[1]);
    }

    /**
     * Bind the class to use.
     */
    protected function execute()
    {
        // binding limit class
        $this->app->bind($this->contract, $this->limitType[$this->stackType]);

        $this->addDAta2Request();
    }

    /**
     * Add some data to request
     */
    protected function addDAta2Request()
    {
        $this->app->request->request->add(
            [
                'limit' => iterator_to_array(
                    $this->applyLimit(
                        array_keys(config('constants.MIXED_COREG_TYPE_FOR_ORDERING')),
                        $this->limit
                    )
                ),
            ]
        );
    }
}
