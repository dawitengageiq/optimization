<?php

namespace App\Http\Services\Campaigns\Providers;

class Facades
{
    /**
     * Application container, to be supplemented.
     */
    protected $app;

    /**
     * Current path.
     */
    protected $path = '';

    protected $segment1 = '';

    /**
     * Path name list where facade is required.
     */
    protected $requiringFacades = [
        'test/get_campaign_list',
        'api/get_campaign_list',
        'winston/get_campaign_list',
        'frame/get_campaign_list_by_api',
        'test/survey_stack_curl',
        'frame/survey_stack_curl',
    ];

    /**
     * Instantiate.
     */
    public function __construct(
        \Illuminate\Foundation\Application $app,
        Aliases $alias
    ) {
        $this->app = $app;
        $this->path = $app->request->path();
        $this->segment1 = $app->request->segment(1);
        $this->alias = $alias;

        if (in_array($this->path, $this->requiringFacades)
        || in_array($this->segment1, $this->requiringFacades)
        ) {
            $this->execute();
        }

    }

    /**
     * Static function.
     */
    public static function bind(\Illuminate\Foundation\Application $app)
    {
        new static($app ,new Aliases);
    }

    /**
     * Register the facade services.
     */
    protected function execute(): void
    {
        $this->app->bind('survey_stack', function () {
            return new \App\Http\Services\Helpers\SurveyStack;
        });

        // Then register aliases for above binding
        $this->registerAlias();
    }

    /**
     * Set the aliases with facade name equivalent, then
     * Register the aliases
     *
     * @method registerAlias
     */
    protected function registerAlias(): void
    {
        // Set the aliases
        $this->alias->set([
            'SurveyStack' => \App\Http\Services\Facades\SurveyStackFacade::class,
        ]);

        // Run register
        $this->alias->registers();
    }
}
