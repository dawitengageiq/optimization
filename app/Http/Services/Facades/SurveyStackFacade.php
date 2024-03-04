<?php

namespace App\Http\Services\Facades;

use Illuminate\Support\Facades\Facade;

class SurveyStackFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'survey_stack';
    }
}
