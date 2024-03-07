<?php

namespace App\Console;

// use App\Helpers\AsyncScheduleExecutor;
// use App\Jobs\SendBugReports;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    protected $outputRecipients = [
        'ariel@engageiq.com',
        'burt@engageiq.com',
        'karla@engageiq.com',
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        /*
        $schedule->command('inspire')
                 ->hourly();
        */

        if (config('settings.execute_reorder_campaigns')) {
            $schedule->command('reorder-campaigns')
                ->withoutOverlapping()
                ->everyMinute();
        }

        if (config('settings.execute_transfer_finished_cron_jobs')) {
            $schedule->command('transfer:finished-cron-jobs')
                ->withoutOverlapping()
                ->hourly()
                ->appendOutputTo(storage_path('logs').'/TransferFinishedCronJobs.log');
        }

        if (config('settings.execute_send_pending_leads_with_job_queue')) {
            $schedule->command('send:pending-leads-job-queue')
                ->everyMinute();
        }

        if (config('settings.execute_send_pending_leads')) {
            $schedule->command('send:pending-leads')
                ->everyMinute();
        }

        if (config('settings.execute_get_cake_conversions')) {
            $schedule->command('get:cake-conversions')
                ->hourly()
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/GetCakeConversions.log');
        }

        if (config('settings.execute_bug_reports')) {
            $schedule->command('send:bug-reports')
                ->withoutOverlapping()
                ->hourly()
                ->appendOutputTo(storage_path('logs').'/SendBugReports.log');
        }

        if (config('settings.execute_all_inbox')) {
            $schedule->command('all-inbox')
                ->hourly()
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/AllInbox.log');
        }

        if (config('settings.execute_rev_tracker_update_subid_breakdown')) {
            $schedule->command('update:rev-tracker-subid-breakdown-status')
                ->withoutOverlapping()
                     // ->daily();
                ->dailyAt('23:58');
        }

        if (config('settings.execute_reorder_mixed_coreg_campaigns')) {
            $schedule->command('reorder-mixed-coreg-campaigns')
                ->withoutOverlapping()
                     // ->hourly();
                ->daily();
        }

        if (config('settings.execute_daily_reorder_mixed_coreg_campaigns')) {
            // Daily reordering
            $schedule->command('daily-reordering:mixed-coreg-campaigns')
                ->daily()
                ->appendOutputTo(storage_path('logs').'/DailyMixCoregOrderingCronJobs.log');
        }

        if (config('settings.execute_reset_lead_counters')) {
            $schedule->command('reset:lead-counters')
                ->daily()
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/ResetLeadCounts.log');
        }

        if (config('settings.execute_archive_cake_conversions')) {
            $schedule->command('archive:cake-conversions')
                ->daily()
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/ArchiveCakeConversions.log');
        }

        if (config('settings.execute_reset_campaign_type_views')) {
            $schedule->command('reset:campaign_type_views')
                ->withoutOverlapping()
                ->daily();
        }

        if (config('settings.execute_lead_fail_timeout_report')) {
            $schedule->command('generate:leads-fail-timeout-report')
                ->withoutOverlapping()
                ->daily();
        }

        if (config('settings.execute_ftp_lead_feed_csv')) {
            $schedule->command('generate:ftp-lead-feed-csv')
                ->withoutOverlapping()
                ->daily();
        }

        if (config('settings.execute_lead_advertiser_data_csv')) {
            $schedule->command("generate:lead-advertiser-data-csv --campaign_name='childsafekit'")
                ->withoutOverlapping()
                ->daily();
        }

        if (config('settings.execute_clean_dashboard_stats')) {
            $schedule->command('clean:dashboard-stats')
                ->daily()
                ->withoutOverlapping();
        }

        if (config('settings.execute_update_cpawall_status')) {
            $schedule->command('update:cpawall-status')
                ->withoutOverlapping()
                ->daily();
        }

        if (config('settings.execute_nocpl_reminder')) {
            $schedule->command('cpl-check')
                ->withoutOverlapping()
                ->weekly()->fridays()->at('13:00');
        }

        if (config('settings.execute_archive_leads')) {
            $schedule->command('archive:leads')
                     //->daily()
                ->dailyAt('01:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/ArchiveLeads.log');
        }

        if (config('settings.execute_generate_affiliate_reports_hourly')) {
            $currentHour = Carbon::now()->hour;

            if (! ($currentHour >= 0 && $currentHour <= 3)) {
                $fromDateStr = Carbon::now()->toDateString();
                $toDateStr = Carbon::now()->addDay()->toDateString();

                //execute current date every hour for updates
                $schedule->command('generate:affiliate-reports --from="'.$fromDateStr.'" --to="'.$toDateStr.'"')
                    ->hourly()
                    ->withoutOverlapping()
                    ->appendOutputTo(storage_path('logs').'/GenerateAffiliateReportsHourly.log');
            }
        }

        if (config('settings.execute_generate_clicks_vs_registration_statistics')) {
            // $schedule->command('generate:clicks-registration-stats')
            //->dailyAt('1:00')
            //->dailyAt('18:30')
            $schedule->command('generate:clicks-registration-stats-v2')
                // ->dailyAt('01:30')
                ->dailyAt('02:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/GenerateClicksVsRegistrationStats.log');
        }

        if (config('settings.execute_clean_queued_leads')) {
            // $schedule->command('generate:clicks-registration-stats')
            //->dailyAt('1:00')
            //->dailyAt('18:30')
            $schedule->command('clean:queued-leads')
                ->dailyAt('02:00')
                ->withoutOverlapping();
        }

        if (config('settings.execute_generate_affiliate_reports')) {
            $schedule->command('generate:affiliate-reports')
                ->dailyAt('3:00')
                     //->dailyAt('18:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/GenerateAffiliateReports.log');

            // $schedule->command('generate:affiliate-reports --restrict=no_revenue')
            //          ->dailyAt('8:30')
            //          //->dailyAt('18:00')
            //          ->withoutOverlapping()
            //          ->appendOutputTo(storage_path('logs').'/GenerateAffiliateReports.log');
        }

        if (config('settings.execute_generate_page_view_statistics')) {
            $schedule->command('generate:page-view-statistics')
                //->dailyAt('1:00')
                // ->dailyAt('19:00')
                // ->dailyAt('05:00')
                ->daily()
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/GeneratePageViewStatistics.log');

            $schedule->command('clean:page-views')
                //->dailyAt('1:00')
                // ->dailyAt('19:00')
                // ->dailyAt('05:00')
                ->dailyAt('13:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/GeneratePageViewStatistics.log');
        }

        if (config('settings.execute_generate_affiliate_website_report')) {
            $schedule->command('generate:affiliate-website-report')
                ->dailyAt('00:05')
                ->withoutOverlapping();

            $schedule->command('clean:website-view-tracker')
                ->dailyAt('13:05')
                ->withoutOverlapping();
        }

        if (config('settings.execute_send_affiliate_reg_revenue_report')) {
            $schedule->command('send:affiliate-reg-path-revenue-report')
                ->dailyAt('00:30')
                ->withoutOverlapping();
        }

        // if(env('EXECUTE_GENERATE_CAKE_REVENUES',true))
        // {
        //     $schedule->command('generate:cake-revenues')
        //              //->dailyAt('1:00')
        //              // ->dailyAt('19:30')
        //              ->dailyAt('02:30')
        //              ->withoutOverlapping()
        //              ->appendOutputTo(storage_path('logs').'/GenerateCakeRevenues.log');
        // }

        if (config('settings.execute_generate_handp_reports')) {
            $schedule->command('generate:hosted-and-posted-affiliate-reports')
                     //->dailyAt('1:00')
                     // ->dailyAt('20:00')
                ->dailyAt('05:30')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/GenerateHandPAffiliateReports.log');
        }

        if (config('settings.execute_generate_iframe_reports')) {
            $schedule->command('generate:iframe-affiliate-reports')
                     //->dailyAt('1:00')
                     //->dailyAt('20:30')
                ->dailyAt('06:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/GenerateIframeAffiliateReports.log');
        }

        if (config('settings.execute_generate_external_path_reports')) {
            $schedule->command('generate:external-path-affiliate-reports')
                     //->dailyAt('1:00')
                     //->dailyAt('20:30')
                ->dailyAt('06:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/GenerateExternalPathAffiliateReports.log');
        }

        if (config('settings.execute_email_lead_csv_data_feed')) {
            $dateNowStr = Carbon::now()->subDay()->toDateString();
            $schedule->command('email:lead-csv-data-feed --campaign=255 --from="'.$dateNowStr.'" --to="'.$dateNowStr.'" --email="burt@engageiq.com" --name="marwil burton"')
                     //->dailyAt('03:00')
                ->dailyAt('06:15')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/EmailLeadCSVDataFeed.log');
        }

        if (config('settings.execute_coreg_performance_reports')) {
            $schedule->command('generate:coreg-performance-report')
                     //->dailyAt('03:00')
                ->dailyAt('06:30')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/CoregPerformanceReports.log');
        }

        if (config('settings.execute_generate_high_rejection_alert_report')) {
            $schedule->command('generate:high-rejection-alert-report')
                     //->dailyAt('03:00')
                ->dailyAt('07:30')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/GenerateHighRejectionAlertReport.log');
        }

        if (config('settings.execute_creative_revenue_report')) {
            $schedule->command('generate:creative-revenue-report')
                ->withoutOverlapping()
                ->dailyAt('08:00')
                ->appendOutputTo(storage_path('logs').'/GenerateCreativeRevenueReport.log');
        }

        if (config('settings.execute_rev_tracker_update_subid_breakdown')) {
            $schedule->command('update:rev-tracker-report-subid-breakdown-status')
                ->withoutOverlapping()
                ->dailyAt('08:15');
        }

        //consolidated job here
        if (config('settings.execute_generate_consolidated_graph')) {
            // $schedule->command('consolidated-graph:data-generator')
            //     ->dailyAt('7:00')
            //     ->withoutOverlapping()
            //     ->appendOutputTo(storage_path('logs').'/GenerateConsolidatedGraph.log');

            $schedule->command('generate:consolidated-graph')
                ->dailyAt('11:00')
                ->withoutOverlapping();
        }

        if (config('settings.execute_campaign_revenue_breakdown')) {
            $all_inbox = config('settings.all_inbox_campaign_id');
            $schedule->command('generate:campaign-revenue-breakdown --campaign='.$all_inbox)
                ->dailyAt('12:00')
                ->withoutOverlapping();
            $push_pro = config('settings.push_pro_campaign_id');
            $schedule->command('generate:campaign-revenue-breakdown --campaign='.$push_pro)
                ->dailyAt('12:30')
                ->withoutOverlapping();
        }

        if (config('settings.execute_all_inbox')) {
            $schedule->command('all-inbox-cleaner')
                ->withoutOverlapping()
                ->dailyAt('23:00');

        }

        if (config('settings.execute_get_one_trust_email')) {
            $schedule->command('get:one-trust-emails')
                ->daily();
        }

        if (config('settings.execute_get_subscribed_campaigns')) {
            $schedule->command('get:user-subscribed-campaigns')
                    // ->dailyAt('01:00');
                ->weekly()->sundays()->at('01:00');
        }

        if (config('settings.execute_send_user_one_trust_email')) {
            $schedule->command('send:user-one-trust-request-email')
                ->weekly()->sundays()->at('01:00');
        }

        if (config('settings.execute_send_publisher_remove_user')) {
            $schedule->command('send:publisher-remove-user --state=CA,NV')
                ->weekly()->sundays()->at('03:00');
        }

        if (config('settings.execute_delete_opt_out_user')) {
            $schedule->command('delete:opt-out-users')
                ->weekly()->sundays()->at('04:30');
        }

        if (config('settings.execute_send_opt_out_report')) {
            $schedule->command('send:opt-out-report')
                ->weekly()->sundays()->at('08:00');
        }

        // if(env('EXECUTE_GENERATE_PREPOP_STATISTICS',true))
        // {
        //     $schedule->command('generate:prepop-statistics')
        //              ->withoutOverlapping()
        //              //->dailyAt('04:00')
        //              ->dailyAt('05:00')
        //              ->appendOutputTo(storage_path('logs').'/GeneratePrepopStatistics.log');
        // }

        // if(env('EXECUTE_UPDATE_REVTRACKER_LANDINGURL',true))
        // {
        //     $schedule->command('update:rev-tracker-landing-url')
        //              ->withoutOverlapping()
        //              ->dailyAt('07:30');
        // }

        // if(env('EXECUTE_COMSOLIDATED_GRAPH_DATA_GENERATOR', true))
        // {
        //     $schedule->command('consolidated-graph:data-generator')
        //              ->dailyAt('6:00');
        // }

        /*
        $schedule->command('command:test-command')
                 ->everyMinute()
                 ->appendOutputTo(storage_path('logs').'/TestCommand.log');
        */
    }

    /**
     * Register the Closure based commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
