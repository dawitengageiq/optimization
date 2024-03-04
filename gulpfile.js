var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir.config.sourcemaps = true;

elixir(function(mix) {

    /**
     * Advertiser portal styles and js
     */

    mix.sass(
        ['advertiser/style.scss'],
        'public/css/advertiser/style.min.css'
    );

    /**
     * Admin portal styles and js
     */

    //advertisers
    mix.scripts(
        ['admin/advertisers.js'],
        'public/js/admin/advertisers.min.js'
    );

    //affiliate reports
    mix.sass(
        ['admin/affiliate_reports/style.scss'],
        'public/css/admin/affiliatereports.min.css'
    );
    mix.scripts(
        ['admin/affiliate_reports.js'],
        'public/js/admin/affiliate_reports.min.js'
    );

    //affiliates
    mix.scripts(
        ['admin/affiliates.js'],
        'public/js/admin/affiliates.min.js'
    );

    //apply to run
    mix.sass(
        ['admin/apply_to_run/style.scss'],
        'public/css/admin/affiliaterequest.min.css'
    );
    mix.scripts(
        ['admin/affiliaterequest.js'],
        'public/js/admin/affiliaterequest.min.js'
    );

    //cake conversions
    mix.scripts(
        ['admin/cake_conversions.js'],
        'public/js/admin/cake_conversions.min.js'
    );

    //campaigns
    mix.scripts(
        ['admin/campaigns.js'],
        'public/js/admin/campaigns.min.js'
    );

    //campaigns categories
    mix.scripts(
        ['admin/campaign_categories.js'],
        'public/js/admin/campaign_categories.min.js'
    );

    //charts
    mix.sass(
        ['admin/charts/style.scss'],
        'public/css/admin/charts.min.css'
    );
    mix.scripts(
        ['admin/highcharts.js'],
        'public/js/admin/highcharts.min.js'
    );

    //contacts
    mix.scripts(
        ['admin/contacts.js'],
        'public/js/admin/contacts.min.js'
    );

    //consolidated graph
    mix.sass(
        ['admin/charts/consolidated_chart.scss'],
        'public/css/admin/consolidated_chart.min.css'
    );
    mix.scripts(
        ['admin/consolidated_chart.js'],
        'public/js/admin/consolidated_chart.min.js'
    );
    mix.scripts(
        ['admin/consolidated_common.js'],
        'public/js/admin/consolidated_common.min.js'
    );
    mix.scripts(
        ['admin/consolidated_graph/date_range_multiple.js'],
        'public/js/admin/consolidated_graph/date_range_multiple.min.js'
    );

    //coregreports
    mix.sass(
        ['admin/coregreports/style.scss'],
        'public/css/admin/coregreports.min.css'
    );
    mix.scripts(
        ['admin/coreg_reports.js'],
        'public/js/admin/coreg_reports.min.js'
    );

    //creative stats
    mix.scripts(
        ['admin/creative_stats.js'],
        'public/js/admin/creative_stats.min.js'
    );

    //cron history
    mix.scripts(
        ['admin/cronhistory.js'],
        'public/js/admin/cronhistory.min.js'
    );

    //cron job
    mix.scripts(
        ['admin/cron_jobs.js'],
        'public/js/admin/cron_jobs.min.js'
    );

    //dashboard
    mix.sass(
        ['admin/dashboard/style.scss'],
        'public/css/admin/dashboard.min.css'
    );
    mix.scripts(
        ['admin/dashboard.js'],
        'public/js/admin/dashboard.min.js'
    );

    //duplicate leads
    mix.scripts(
        ['admin/duplicate_leads.js'],
        'public/js/admin/duplicate_leads.min.js'
    );

    //filter types
    mix.scripts(
        ['admin/filtertypes.js'],
        'public/js/admin/filtertypes.min.js'
    );

    //galley
    mix.scripts(
        ['admin/gallery.js'],
        'public/js/admin/gallery.min.js'
    );

    //revenue stats
    mix.scripts(
        ['admin/revenue_statistics.js'],
        'public/js/admin/revenue_statistics.min.js'
    );

    //revenue trackers
    mix.scripts(
        ['admin/revenue_trackers.js'],
        'public/js/admin/revenue_trackers.min.js'
    );

    //search leads
    mix.scripts(
        ['admin/search_leads.js'],
        'public/js/admin/search_leads.min.js'
    );

    //clicks vs registration
    mix.sass(
        ['admin/clicks_vs_registration/style.scss'],
        'public/css/admin/clicks-vs-registration.min.css'
    );
    mix.scripts(
        ['admin/clicks-vs-registration.js'],
        'public/js/admin/clicks-vs-registration.min.js'
    );

    //page view stats resources/assets/sass/
    mix.sass(
        ['admin/page_view_stats/style.scss'],
        'public/css/admin/page-view-statistics.min.css'
    );
    mix.scripts(
        ['admin/page-view-statistics.js'],
        'public/js/admin/page-view-statistics.min.js'
    );

    //settings
    mix.scripts(
        ['admin/settings.js'],
        'public/js/admin/settings.min.js'
    );

    //survey takers
    mix.sass(
        ['admin/survey_takers/style.scss'],
        'public/css/admin/survey_takers.min.css'
    );
    mix.scripts(
        ['admin/survey_takers.js'],
        'public/js/admin/survey_takers.min.js'
    );

    //survey paths
    mix.scripts(
        ['admin/survey_paths.js'],
        'public/js/admin/survey_paths.min.js'
    );

    //zip codes
    mix.scripts(
        ['admin/zip_codes.js'],
        'public/js/admin/zip_codes.min.js'
    );

    //zip master
    mix.scripts(
        ['admin/zip_master.js'],
        'public/js/admin/zip_master.min.js'
    );

    //app template
    mix.styles([
        'sb-admin-2.css',
        'style.css',
        'timeline.css'],
        'public/css/app.min.css'
    );
    mix.scripts(
        ['sb-admin-2.js','commons.js'],
        'public/js/app.min.js'
    );

    //user profile
    mix.scripts(
        ['admin/user_profile.js'],
        'public/js/admin/user_profile.min.js'
    );
    mix.styles([
        'admin/user_profile.css'],
        'public/css/admin/user_profile.min.css'
    );

    // diff lib
    mix.styles([
        'admin/diffview.css'],
        'public/css/admin/diffview.min.css'
    );

    //roles management
    mix.scripts(
        ['admin/roles.js'],
        'public/js/admin/roles.min.js'
    );

    //user management
    mix.scripts(
        ['admin/users.js'],
        'public/js/admin/users.min.js'
    );

    //prepop statistics
    mix.scripts(
        ['admin/prepopstatistics.js'],
        'public/js/admin/prepopstatistics.min.js'
    );

    //user action history statistics
    mix.scripts(
        ['admin/user-action-history.js'],
        'public/js/admin/user-action-history.min.js'
    );

    // diff lib
    mix.scripts(
        ['admin/difflib.js'],
        'public/js/admin/difflib.min.js'
    );

    mix.scripts(
        ['admin/diffview.js'],
        'public/js/admin/diffview.min.js'
    );

    /**
     * Affiliate portal styles and js
     */

    //campaigns
    mix.scripts(
        ['affiliate/campaigns.js'],
        'public/js/affiliate/campaigns.min.js'
    );

    //statistics
    mix.scripts(
        ['affiliate/statistics.js'],
        'public/js/affiliate/statistics.min.js'
    );

    //edit account
    mix.scripts(
        ['commons.js'],
        'public/js/affiliate/commons.min.js'
    );

    //websites
    mix.scripts(
        ['affiliate/websites.js'],
        'public/js/affiliate/websites.min.js'
    );

    //master template
    mix.styles([
            'affiliate/style.css'],
            'public/css/affiliate/style.min.css'
    );

    /**
     * Campaign Api js
     */

    // Campaign loader
    mix.scripts([
            'api/campaign_loader.js'],
            'public/js/api/campaign_loader.min.js'
    );

    // Campaign custom script
    mix.scripts([
            'api/campaigns_custom_script.js'],
            'public/js/api/campaigns_custom_script.min.js'
    );

    //Page OptIn Rates
    mix.scripts([
            'admin/pageoptinratestats.js'],
            'public/js/admin/pageoptinratestats.min.js'
    );

    /**
     * User Action Log
     */
    mix.styles(
        ['cpm/style.css'],
        'public/cpm/style.min.css'
    );
    mix.scripts([
        'cpm/script.js'],
        'public/cpm/script.min.js'
    );
    mix.scripts([
        'cpm/campaign.js'],
        'public/cpm/campaign.min.js'
    );
    mix.scripts([
        'cpm/campaigns.js'],
        'public/cpm/campaigns.min.js'
    );

    /**
     * Embeded Campaign
    */
    mix.styles(
        ['embed/style.css'],
        'public/css/embed/style.min.css'
    );
    mix.scripts([
            'embed/script.js'],
            'public/js/embed/script.min.js'
    );
});
