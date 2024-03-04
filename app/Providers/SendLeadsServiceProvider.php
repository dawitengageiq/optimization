<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SendLeadsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            'App\Http\Services\Contracts\LeadInterface',
            'App\Http\Services\Repositories\LeadRepository');

        $this->app->bind(
            'App\Http\Services\Contracts\LeadCSVDataInterface',
            'App\Http\Services\Repositories\LeadCSVDataRepository');
    }
}
