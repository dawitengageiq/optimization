<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    */

    'name' => 'My Application',

    'env' => env('APP_ENV', 'production'),

    'build_number' => '9.8',

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services your application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    'type' => env('APP_TYPE', 'main'),

    'drive' => env('APP_DRIVE', 'master'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', true),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => 'http://localhost',
    'main_url' => env('APP_URL', 'http://leadreactor.test/'),
    'reports_url' => env('REPORTS_URL', 'http://leadreactor.test/'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'America/Los_Angeles',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY', 'SomeRandomString'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log settings for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Settings: "single", "daily", "syslog", "errorlog"
    |
    */

    'log' => 'daily',

    'log_level' => env('APP_LOG_LEVEL', 'debug'),

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => ServiceProvider::defaultProviders()->merge([
        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\ChartServiceProvider::class,
        App\Providers\SendLeadsServiceProvider::class,
        App\Providers\CampaignListServiceProvider::class,

        // Illuminate\Html\HtmlServiceProvider::class,

        //maatwebsite/excel
        Maatwebsite\Excel\ExcelServiceProvider::class,
        Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class,
       // Spatie\ServerMonitor\ServerMonitorServiceProvider::class,
        //Collective\Remote\RemoteServiceProvider::class,
        Collective\Html\HtmlServiceProvider::class,
        Barryvdh\Debugbar\ServiceProvider::class,
    ])->toArray(),

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => Facade::defaultAliases()->merge([
        'AffiliateEarningsByDateFilter' => App\Commands\AffiliateEarningsByDateFilter::class,
        'Carbon' => \Carbon\Carbon::class,
        'Debugbar' => Barryvdh\Debugbar\Facade::class,
        'Excel' => Maatwebsite\Excel\Facades\Excel::class,
        'Form' => Collective\Html\FormFacade::class,
        'GetAdvertisersCompanyIDPair' => App\Commands\GetAdvertisersCompanyIDPair::class,
        'GetAffiliatesCompanyIDPair' => App\Commands\GetAffiliatesCompanyIDPair::class,
        'GetAvailableUsers' => App\Commands\GetAvailableUsers::class,
        'GetCampaignListAndIDsPair' => App\Commands\GetCampaignListAndIDsPair::class,
        'GetInternalAffiliatesCompanyIDPair' => App\Commands\GetInternalAffiliatesCompanyIDPair::class,
        'GetUserActionPermission' => App\Commands\GetUserActionPermission::class,
        'Html' => Collective\Html\HtmlFacade::class,
        'Inspiring' => Illuminate\Foundation\Inspiring::class,
        'RandomProbability' => App\Commands\RandomProbability::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'SSH' => Collective\Remote\RemoteFacade::class,
    ])->toArray(),
];
