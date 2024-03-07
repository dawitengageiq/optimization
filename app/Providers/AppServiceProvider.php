<?php

namespace App\Providers;

use App\Http\Services\Consolidated\Providers\Graph;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use League\Flysystem\Sftp\SftpAdapter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        /*Queue::after(function ($connection, $job, $data) {

            //job was a success
            if ($connection == 'beanstalkd') {

                switch (get_class($job)) {
                    case 'AffiliateReportsV2':
                        Log::info('Job Successful!');
                        Log::info($connection);
                        Log::info('AffiliateReportsV2');
                        Log::info($data);
                        break;
                }

            }

        });*/
        /*
        Queue::failing(function ($connection, $job, $data) {

            // Notify team of failing job...
            Log::info('AppServiceProvider - Job Failed!');
            Log::info($connection);
            //Log::info($job);
            Log::info($data);

        });
        */

        // Consolidated graph
        Graph::boot($this->app);

        Storage::extend('sftp', function ($app, $config) {
            return new Filesystem(new SftpAdapter($config));
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Helpers\Repositories\LeadDataInterface::class,
            \App\Helpers\Repositories\LeadData::class);

        $this->app->bind(
            \App\Helpers\Repositories\SettingsInterface::class,
            \App\Helpers\Repositories\Settings::class);

        if (env('APP_DEBUG') == true && env('APP_BUILD') == 'JTLR') {
            $this->app->register(\Rap2hpoutre\LaravelLogViewer\LaravelLogViewerServiceProvider::class);
            $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);

            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Debugbar', \Barryvdh\Debugbar\Facade::class);
        }

        $this->app->bind(
            \App\Helpers\Repositories\AffiliateReportCurlInterface::class,
            \App\Helpers\Repositories\AffiliateReportCurl::class);

        $this->app->bind(
            \App\Helpers\Repositories\LeadCountsInterface::class,
            \App\Helpers\Repositories\LeadCounts::class);
        $this->app->bind(
            \App\Http\Services\Contracts\CampaignListContract::class,
            \App\Http\Services\Campaigns\Lists::class);
        $this->app->bind(
            \App\Http\Services\Campaigns\Utils\Lists\Contracts\LimitContract::class,
            \App\Http\Services\Campaigns\Utils\Lists\Limit\StackType\ByPerStack::class);
        $this->app->bind(
            \App\Http\Services\Campaigns\Utils\Lists\Contracts\StackContract::class,
            \App\Http\Services\Campaigns\Utils\Lists\Stacking\ByPriority::class);
    }
}
