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

        if (env('EXECUTE_REORDER_CAMPAIGNS', true)) {
            $schedule->command('reorder-campaigns')
                ->withoutOverlapping()
                ->everyMinute();
        }

        if (env('EXECUTE_TRANSFER_FINISHED_CRON_JOBS', true)) {
            $schedule->command('transfer:finished-cron-jobs')
                ->withoutOverlapping()
                ->hourly()
                ->appendOutputTo(storage_path('logs').'/TransferFinishedCronJobs.log');
        }

        if (env('EXECUTE_SEND_PENDING_LEADS_WITH_JOB_QUEUE', true)) {
            $schedule->command('send:pending-leads-job-queue')
                ->everyMinute();
        }

        if (env('EXECUTE_SEND_PENDING_LEADS', true)) {
            $schedule->command('send:pending-leads')
                ->everyMinute();
        }

        if (env('EXECUTE_GET_CAKE_CONVERSIONS', true)) {
            $schedule->command('get:cake-conversions')
                ->hourly()
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/GetCakeConversions.log');
        }

        if (env('EXECUTE_BUG_REPORTS', true)) {
            $schedule->command('send:bug-reports')
                ->withoutOverlapping()
                ->hourly()
                ->appendOutputTo(storage_path('logs').'/SendBugReports.log');
        }

        if (env('EXECUTE_ALL_INBOX', true)) {
            $schedule->command('all-inbox')
                ->hourly()
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/AllInbox.log');
        }

        if (env('EXECUTE_REV_TRACKER_UPDATE_SUBID_BREAKDOWN', true)) {
            $schedule->command('update:rev-tracker-subid-breakdown-status')
                ->withoutOverlapping()
                     // ->daily();
                ->dailyAt('23:58');
        }

        if (env('EXECUTE_REORDER_MIXED_COREG_CAMPAIGNS', true)) {
            $schedule->command('reorder-mixed-coreg-campaigns')
                ->withoutOverlapping()
                     // ->hourly();
                ->daily();
        }

        if (env('EXECUTE_DAILY_REORDER_MIXED_COREG_CAMPAIGNS', true)) {
            // Daily reordering
            $schedule->command('daily-reordering:mixed-coreg-campaigns')
                ->daily()
                ->appendOutputTo(storage_path('logs').'/DailyMixCoregOrderingCronJobs.log');
        }

        if (env('EXECUTE_RESET_LEAD_COUNTERS', true)) {
            $schedule->command('reset:lead-counters')
                ->daily()
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/ResetLeadCounts.log');
        }

        if (env('EXECUTE_ARCHIVE_CAKE_CONVERSIONS', true)) {
            $schedule->command('archive:cake-conversions')
                ->daily()
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/ArchiveCakeConversions.log');
        }

        if (env('EXECUTE_RESET_CAMPAIGN_TYPE_VIEWS', true)) {
            $schedule->command('reset:campaign_type_views')
                ->withoutOverlapping()
                ->daily();
        }

        if (env('EXECUTE_LEAD_FAIL_TIMEOUT_REPORT', true)) {
            $schedule->command('generate:leads-fail-timeout-report')
                ->withoutOverlapping()
                ->daily();
        }

        if (env('EXECUTE_FTP_LEAD_FEED_CSV', true)) {
            $schedule->command('generate:ftp-lead-feed-csv')
                ->withoutOverlapping()
                ->daily();
        }

        if (env('EXECUTE_LEAD_ADVERTISER_DATA_CSV', true)) {
            $schedule->command("generate:lead-advertiser-data-csv --campaign_name='childsafekit'")
                ->withoutOverlapping()
                ->daily();
        }

        if (env('EXECUTE_CLEAN_DASHBOARD_STATS', true)) {
            $schedule->command('clean:dashboard-stats')
                ->daily()
                ->withoutOverlapping();
        }

        if (env('EXECUTE_UPDATE_CPAWALL_STATUS', true)) {
            $schedule->command('update:cpawall-status')
                ->withoutOverlapping()
                ->daily();
        }

        if (env('EXECUTE_NOCPL_REMINDER', true)) {
            $schedule->command('cpl-check')
                ->withoutOverlapping()
                ->weekly()->fridays()->at('13:00');
        }

        if (env('EXECUTE_ARCHIVE_LEADS', true)) {
            $schedule->command('archive:leads')
                     //->daily()
                ->dailyAt('01:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/ArchiveLeads.log');
        }

        if (env('EXECUTE_GENERATE_AFFILIATE_REPORTS_HOURLY', true)) {
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

        if (env('EXECUTE_GENERATE_CLICKS_VS_REGISTRATION_STATISTICS', true)) {
            // $schedule->command('generate:clicks-registration-stats')
            //->dailyAt('1:00')
            //->dailyAt('18:30')
            $schedule->command('generate:clicks-registration-stats-v2')
                // ->dailyAt('01:30')
                ->dailyAt('02:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/GenerateClicksVsRegistrationStats.log');
        }

        if (env('EXECUTE_CLEAN_QUEUED_LEADS', true)) {
            // $schedule->command('generate:clicks-registration-stats')
            //->dailyAt('1:00')
            //->dailyAt('18:30')
            $schedule->command('clean:queued-leads')
                ->dailyAt('02:00')
                ->withoutOverlapping();
        }

        if (env('EXECUTE_GENERATE_AFFILIATE_REPORTS', true)) {
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

        if (env('EXECUTE_GENERATE_PAGE_VIEW_STATISTICS', true)) {
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

        if (env('EXECUTE_GENERATE_AFFILIATE_WEBSITE_REPORT', true)) {
            $schedule->command('generate:affiliate-website-report')
                ->dailyAt('00:05')
                ->withoutOverlapping();

            $schedule->command('clean:website-view-tracker')
                ->dailyAt('13:05')
                ->withoutOverlapping();
        }

        if (env('EXECUTE_SEND_AFFILIATE_REG_REVENUE_REPORT', true)) {
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

        if (env('EXECUTE_GENERATE_HANDP_REPORTS', true)) {
            $schedule->command('generate:hosted-and-posted-affiliate-reports')
                     //->dailyAt('1:00')
                     // ->dailyAt('20:00')
                ->dailyAt('05:30')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/GenerateHandPAffiliateReports.log');
        }

        if (env('EXECUTE_GENERATE_IFRAME_REPORTS', true)) {
            $schedule->command('generate:iframe-affiliate-reports')
                     //->dailyAt('1:00')
                     //->dailyAt('20:30')
                ->dailyAt('06:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/GenerateIframeAffiliateReports.log');
        }

        if (env('EXECUTE_GENERATE_EXTERNAL_PATH_REPORTS', true)) {
            $schedule->command('generate:external-path-affiliate-reports')
                     //->dailyAt('1:00')
                     //->dailyAt('20:30')
                ->dailyAt('06:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/GenerateExternalPathAffiliateReports.log');
        }

        if (env('EXECUTE_EMAIL_LEAD_CSV_DATA_FEED', true)) {
            $dateNowStr = Carbon::now()->subDay()->toDateString();
            $schedule->command('email:lead-csv-data-feed --campaign=255 --from="'.$dateNowStr.'" --to="'.$dateNowStr.'" --email="burt@engageiq.com" --name="marwil burton"')
                     //->dailyAt('03:00')
                ->dailyAt('06:15')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/EmailLeadCSVDataFeed.log');
        }

        if (env('EXECUTE_COREG_PERFORMANCE_REPORTS', true)) {
            $schedule->command('generate:coreg-performance-report')
                     //->dailyAt('03:00')
                ->dailyAt('06:30')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/CoregPerformanceReports.log');
        }

        if (env('EXECUTE_GENERATE_HIGH_REJECTION_ALERT_REPORT', true)) {
            $schedule->command('generate:high-rejection-alert-report')
                     //->dailyAt('03:00')
                ->dailyAt('07:30')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs').'/GenerateHighRejectionAlertReport.log');
        }

        if (env('EXECUTE_CREATIVE_REVENUE_REPORT', true)) {
            $schedule->command('generate:creative-revenue-report')
                ->withoutOverlapping()
                ->dailyAt('08:00')
                ->appendOutputTo(storage_path('logs').'/GenerateCreativeRevenueReport.log');
        }

        if (env('EXECUTE_REV_TRACKER_UPDATE_SUBID_BREAKDOWN', true)) {
            $schedule->command('update:rev-tracker-report-subid-breakdown-status')
                ->withoutOverlapping()
                ->dailyAt('08:15');
        }

        //consolidated job here
        if (env('EXECUTE_GENERATE_CONSOLIDATED_GRAPH', true)) {
            // $schedule->command('consolidated-graph:data-generator')
            //     ->dailyAt('7:00')
            //     ->withoutOverlapping()
            //     ->appendOutputTo(storage_path('logs').'/GenerateConsolidatedGraph.log');

            $schedule->command('generate:consolidated-graph')
                ->dailyAt('11:00')
                ->withoutOverlapping();
        }

        if (env('EXECUTE_CAMPAIGN_REVENUE_BREAKDOWN', true)) {
            $all_inbox = env('ALL_INBOX_CAMPAIGN_ID', 286);
            $schedule->command('generate:campaign-revenue-breakdown --campaign='.$all_inbox)
                ->dailyAt('12:00')
                ->withoutOverlapping();
            $push_pro = env('PUSH_PRO_CAMPAIGN_ID', 1672);
            $schedule->command('generate:campaign-revenue-breakdown --campaign='.$push_pro)
                ->dailyAt('12:30')
                ->withoutOverlapping();
        }

        if (env('EXECUTE_ALL_INBOX', true)) {
            $schedule->command('all-inbox-cleaner')
                ->withoutOverlapping()
                ->dailyAt('23:00');

        }

        if (env('EXECUTE_GET_ONE_TRUST_EMAIL', true)) {
            $schedule->command('get:one-trust-emails')
                ->daily();
        }

        if (env('EXECUTE_GET_SUBSCRIBED_CAMPAIGNS', true)) {
            $schedule->command('get:user-subscribed-campaigns')
                    // ->dailyAt('01:00');
                ->weekly()->sundays()->at('01:00');
        }

        if (env('EXECUTE_SEND_USER_ONE_TRUST_EMAIL', true)) {
            $schedule->command('send:user-one-trust-request-email')
                ->weekly()->sundays()->at('01:00');
        }

        if (env('EXECUTE_SEND_PUBLISHER_REMOVE_USER', true)) {
            $schedule->command('send:publisher-remove-user --state=CA,NV')
                ->weekly()->sundays()->at('03:00');
        }

        if (env('EXECUTE_DELETE_OPT_OUT_USER', true)) {
            $schedule->command('delete:opt-out-users')
                ->weekly()->sundays()->at('04:30');
        }

        if (env('EXECUTE_SEND_OPT_OUT_REPORT', true)) {
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
