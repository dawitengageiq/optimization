<?php

namespace Database\Seeders;

use App\Campaign;
use App\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $send_pending_lead_cron_expiration = Setting::firstOrNew([
            'code' => 'send_pending_lead_cron_expiration',
        ]);
        if (! $send_pending_lead_cron_expiration->exists) {
            $send_pending_lead_cron_expiration->name = 'Sending Expiration of Pending Leads';
            $send_pending_lead_cron_expiration->description = 'How many minutes does the sending job expires.';
            $send_pending_lead_cron_expiration->integer_value = 3;
            $send_pending_lead_cron_expiration->save();
        }

        $num_campaign_per_stack_page = Setting::firstOrNew([
            'code' => 'num_campaign_per_stack_page',
        ]);
        if (! $num_campaign_per_stack_page->exists) {
            $num_campaign_per_stack_page->name = 'No. of Campaign per Stack Page';
            $num_campaign_per_stack_page->description = 'How many campaigns to show per page.';
            $num_campaign_per_stack_page->integer_value = 65;
            $num_campaign_per_stack_page->save();
        }

        $stack_path_campaign_type_order = Setting::firstOrNew([
            'code' => 'stack_path_campaign_type_order',
        ]);
        if (! $stack_path_campaign_type_order->exists) {
            $stack_path_campaign_type_order->name = 'Stack Page Order';
            $stack_path_campaign_type_order->description = 'Order of campaign type to show in stack path.';
            // $stack_path_campaign_type_order->string_value = '{1:1,2:3,3:2,4:7,5:4,6:5,7:6,8:8,9:9,10:10,11:11,12:12}';
            $stack_path_campaign_type_order->string_value = '[11,12,1,2,8,13,3,7,4,5,6,9,10]';
            $stack_path_campaign_type_order->save();
        }

        $campaign_filter_process_status = Setting::firstOrNew([
            'code' => 'campaign_filter_process_status',
        ]);
        if (! $campaign_filter_process_status->exists) {
            $campaign_filter_process_status->name = 'Filter Process Type';
            $campaign_filter_process_status->description = 'Campaign filter status, whether to use the filters in the filter tab.';
            $campaign_filter_process_status->integer_value = 1;
            $campaign_filter_process_status->save();
        }

        $leads_archiving_age_in_days = Setting::firstOrNew([
            'code' => 'leads_archiving_age_in_days',
        ]);
        if (! $leads_archiving_age_in_days->exists) {
            $leads_archiving_age_in_days->name = 'Leads Archiving Age (in days)';
            $leads_archiving_age_in_days->description = 'Archive the leads that has an age specified in days.';
            $leads_archiving_age_in_days->integer_value = 60;
            $leads_archiving_age_in_days->save();
        }

        $high_critical_rejection_rate = Setting::firstOrNew([
            'code' => 'high_critical_rejection_rate',
        ]);
        if (! $high_critical_rejection_rate->exists) {
            $high_critical_rejection_rate->name = 'High Critical Rejection Rate';
            $high_critical_rejection_rate->description = '[minOfHigh, maxOfHigh]. minOfCritical = maxOfHigh + 0.1, maxOfCritical = 100';
            $high_critical_rejection_rate->string_value = '[60,89.9]';
            $high_critical_rejection_rate->save();
        }

        $send_pending_queue = Setting::firstOrNew([
            'code' => 'num_leads_to_process_for_send_pending_leads',
        ]);
        if (! $send_pending_queue->exists) {
            $send_pending_queue->name = 'No. of Leads per Send Leads Process';
            $send_pending_queue->description = 'Number of leads to process for send pending leads';
            $send_pending_queue->integer_value = 200;
            $send_pending_queue->save();
        }

        $campaign_type_view_reordering_status = Setting::firstOrNew([
            'code' => 'campaign_reordering_status',
        ]);
        if (! $campaign_type_view_reordering_status->exists) {
            $campaign_type_view_reordering_status->name = 'Campaign Reordering';
            $campaign_type_view_reordering_status->description = 'Global setting status for campaign reordering.';
            $campaign_type_view_reordering_status->integer_value = 0;
            $campaign_type_view_reordering_status->save();
        }

        $campaign_type_view_count = Setting::firstOrNew([
            'code' => 'campaign_type_view_count',
        ]);
        if (! $campaign_type_view_count->exists) {
            $campaign_type_view_count->name = 'Campaign Type View Count';
            $campaign_type_view_count->description = 'Global setting view count for campaign types.';
            $campaign_type_view_count->integer_value = 0;
            $campaign_type_view_count->save();
        }

        $campaign_type_view_reference_date = Setting::firstOrNew([
            'code' => 'campaign_type_view_reference_date',
        ]);
        if (! $campaign_type_view_reference_date->exists) {
            $campaign_type_view_reference_date->name = 'Campaign Type View Reference Date';
            $campaign_type_view_reference_date->description = 'Global setting reference date for campaign types.';
            $campaign_type_view_reference_date->date_value = \Carbon\Carbon::now()->toDateString();
            $campaign_type_view_reference_date->save();
        }

        $campaign_nos_before_not_displaying = Setting::firstOrNew([
            'code' => 'user_nos_before_not_displaying_campaign',
        ]);
        if (! $campaign_nos_before_not_displaying->exists) {
            $campaign_nos_before_not_displaying->name = 'User "Nos" before disabling a campaign for user';
            $campaign_nos_before_not_displaying->description = 'How many times user nos before not displaying a campaign';
            $campaign_nos_before_not_displaying->integer_value = 5;
            $campaign_nos_before_not_displaying->save();
        }

        $default_admin_email = Setting::firstOrNew([
            'code' => 'default_admin_email',
        ]);
        if (! $default_admin_email->exists) {
            $default_admin_email->name = 'Default Admin Email';
            $default_admin_email->description = 'email to be used for receiving cron reports';
            $default_admin_email->string_value = 'admin@engageiq.com';
            $default_admin_email->save();
        }

        $campaign_type_path_limit = Setting::firstOrNew([
            'code' => 'campaign_type_path_limit',
        ]);
        if (! $campaign_type_path_limit->exists) {
            $campaign_type_path_limit->name = 'No. of Campaigns per Campaign Type Stack Page';
            $campaign_type_path_limit->description = '{"1":"35","2":"35","8":"35","13":"35","3":"1","7":"1","4":"1","5":"35","6":"1","9":"","10":"","11":"","12":""}';
            $campaign_type_path_limit->string_value = null;
            $campaign_type_path_limit->save();
        }

        $mixed_coreg_campaign_reordering_status = Setting::firstOrNew([
            'code' => 'mixed_coreg_campaign_reordering_status',
        ]);
        if (! $mixed_coreg_campaign_reordering_status->exists) {
            $mixed_coreg_campaign_reordering_status->name = 'Mixed Co-Reg Campaign Reordering';
            $mixed_coreg_campaign_reordering_status->description = 'Global setting status for mixed co-reg campaign reordering.';
            $mixed_coreg_campaign_reordering_status->integer_value = 0;
            $mixed_coreg_campaign_reordering_status->save();
        }

        $mixed_coreg_campaign_reordering_reference_date = Setting::firstOrNew([
            'code' => 'mixed_coreg_campaign_reordering_reference_date',
        ]);
        if (! $mixed_coreg_campaign_reordering_reference_date->exists) {
            $mixed_coreg_campaign_reordering_reference_date->name = 'Mixed Coreg Campaign Reordering Reference Date';
            $mixed_coreg_campaign_reordering_reference_date->description = 'Global setting reference date for mixed campaign reordering.';
            $mixed_coreg_campaign_reordering_reference_date->date_value = \Carbon\Carbon::now()->toDateString();
            $mixed_coreg_campaign_reordering_reference_date->save();
        }

        $cake_conversions_age_in_days = Setting::firstOrNew([
            'code' => 'cake_conversions_archiving_age_in_days',
        ]);
        if (! $cake_conversions_age_in_days->exists) {
            $cake_conversions_age_in_days->name = 'Cake Conversions Archiving Age (in days)';
            $cake_conversions_age_in_days->description = 'Archive the cake conversions that has an age specified in days.';
            $cake_conversions_age_in_days->integer_value = 1;
            $cake_conversions_age_in_days->save();
        }

        $campaign_type_benchmarks = Setting::firstOrNew([
            'code' => 'campaign_type_benchmarks',
        ]);
        if (! $campaign_type_benchmarks->exists) {
            $campaigns = Campaign::where('status', 2)->groupBy('campaign_type')->pluck('id', 'campaign_type');
            $campaigns[4] = env('EXTERNAL_PATH_IFFICIENT_CAMPAIGN_ID', '287');
            $campaign_type_benchmarks->name = 'Campaign Type ';
            $campaign_type_benchmarks->description = json_encode($campaigns);
            $campaign_type_benchmarks->save();
        }

        $address_feed_affiliates = Setting::firstOrNew([
            'code' => 'address_feed_affiliates',
        ]);
        if (! $address_feed_affiliates->exists) {
            $address_feed_affiliates->name = 'Address Feed Affiliates';
            $address_feed_affiliates->string_value = null;
            $address_feed_affiliates->save();
        }

        $report_recipients = Setting::firstOrNew([
            'code' => 'report_recipient',
        ]);
        if (! $report_recipients->exists) {
            $report_recipients->name = 'Report Recipient';
            $report_recipients->description = 'karla@engageiq.com;ariel@engageiq.com;burt@engageiq.com;monty@engageiq.com;dexter@engageiq.com;rian@engageiq.com;delicia@engageiq.com;jackie@engageiq.com;johneil@engageiq.com;janhavi@engageiq.com;angela@engageiq.com';
            $report_recipients->save();
        }

        $qa_recipients = Setting::firstOrNew([
            'code' => 'qa_recipient',
        ]);
        if (! $qa_recipients->exists) {
            $qa_recipients->name = 'QA Recipient';
            $qa_recipients->description = 'karla@engageiq.com;ariel@engageiq.com;monty@engageiq.com;dexter@engageiq.com';
            $qa_recipients->save();
        }

        $header_pixel = Setting::firstOrNew([
            'code' => 'header_pixel',
        ]);
        if (! $header_pixel->exists) {
            $header_pixel->name = 'Header Pixel';
            $header_pixel->description = '';
            $header_pixel->save();
        }

        $body_pixel = Setting::firstOrNew([
            'code' => 'body_pixel',
        ]);
        if (! $body_pixel->exists) {
            $body_pixel->name = 'Body Pixel';
            $body_pixel->description = '';
            $body_pixel->save();
        }

        $footer_pixel = Setting::firstOrNew([
            'code' => 'footer_pixel',
        ]);
        if (! $footer_pixel->exists) {
            $footer_pixel->name = 'Footer Pixel';
            $footer_pixel->description = '';
            $footer_pixel->save();
        }

        $cvd_feed_affiliates = Setting::firstOrNew([
            'code' => 'cvd_feed_affiliates',
        ]);
        if (! $cvd_feed_affiliates->exists) {
            $cvd_feed_affiliates->name = 'CVD Feed Affiliates';
            $cvd_feed_affiliates->string_value = null;
            $cvd_feed_affiliates->save();
        }

        $email_media_buy_affiliates = Setting::firstOrNew([
            'code' => 'email_media_buy_affiliates',
        ]);
        if (! $email_media_buy_affiliates->exists) {
            $email_media_buy_affiliates->name = 'Email Media Buy Affiliates';
            $email_media_buy_affiliates->string_value = null;
            $email_media_buy_affiliates->save();
        }

        $permission_data_codes = Setting::firstOrNew([
            'code' => 'permission_data_pub_codes',
        ]);
        if (! $permission_data_codes->exists) {
            $permission_data_codes->name = 'Permission Data Pub Code';
            $permission_data_codes->description = '["AOP","EG2","EG5","EGI","EG6","EG4","EG3","EG7"]';
            $permission_data_codes->save();
        }

        $monitis_path_ids = Setting::firstOrNew([
            'code' => 'monitis_path_ids',
        ]);
        if (! $monitis_path_ids->exists) {
            $monitis_path_ids->name = 'Monitis Path Codes';
            $monitis_path_ids->description = '{"Path6":"148774","Path11":"150578","Path12":"148778","Path17":"149908"}';
            $monitis_path_ids->save();
        }

        $high_rejection_keywords = Setting::firstOrNew([
            'code' => 'high_rejection_keywords',
        ]);
        if (! $high_rejection_keywords->exists) {
            $high_rejection_keywords->name = 'Lead High Rejection Keywords';
            $high_rejection_keywords->description = '{"d":"duplicate,dupe,already,exist","p":"invalid,does not,doesn","f":"no","a":"no match"}';
            $high_rejection_keywords->save();
        }

        $nocpl_recipient = Setting::firstOrNew([
            'code' => 'nocpl_recipient',
        ]);
        if (! $nocpl_recipient->exists) {
            $nocpl_recipient->name = 'No CPL Recipient';
            $nocpl_recipient->description = 'karla@engageiq.com;burt@engageiq.com;janhavi@engageiq.com;dell@engageiq.com;billing@engageiq.com';
            $nocpl_recipient->save();
        }

        $cplchecker_excluded_campaigns = Setting::firstOrNew([
            'code' => 'cplchecker_excluded_campaigns',
        ]);
        if (! $cplchecker_excluded_campaigns->exists) {
            $cplchecker_excluded_campaigns->name = 'Campaigns Excluded from CPL Checker';
            $cplchecker_excluded_campaigns->description = '';
            $cplchecker_excluded_campaigns->save();
        }

        $optoutreport_recipient = Setting::firstOrNew([
            'code' => 'optoutreport_recipient',
        ]);
        if (! $optoutreport_recipient->exists) {
            $optoutreport_recipient->name = 'Opt Out Report Recipient';
            $optoutreport_recipient->description = 'karla@engageiq.com;burt@engageiq.com;dell@engageiq.com';
            $optoutreport_recipient->save();
        }

        $ccpaadminemail_recipient = Setting::firstOrNew([
            'code' => 'ccpaadminemail_recipient',
        ]);
        if (! $ccpaadminemail_recipient->exists) {
            $ccpaadminemail_recipient->name = 'CCPA Admin Email';
            $ccpaadminemail_recipient->description = 'burt@engageiq.com';
            $ccpaadminemail_recipient->save();
        }

        $optoutexternal_recipient = Setting::firstOrNew([
            'code' => 'optoutexternal_recipient',
        ]);
        if (! $optoutexternal_recipient->exists) {
            $optoutexternal_recipient->name = 'Opt Out External Recipient';
            $optoutexternal_recipient->description = '';
            $optoutexternal_recipient->save();
        }

        $optoutemail_replyto = Setting::firstOrNew([
            'code' => 'optoutemail_replyto',
        ]);
        if (! $optoutemail_replyto->exists) {
            $optoutemail_replyto->name = 'Opt Out Reply To Email';
            $optoutemail_replyto->description = '';
            $optoutemail_replyto->save();
        }

        $epic_demand_all_inbox_feed_affiliates = Setting::firstOrNew([
            'code' => 'epic_demand_all_inbox_feed_affiliates',
        ]);
        if (! $epic_demand_all_inbox_feed_affiliates->exists) {
            $epic_demand_all_inbox_feed_affiliates->name = 'Epic Demand All Inbox Affiliates';
            $epic_demand_all_inbox_feed_affiliates->save();
        }

        $epicdemand_tcpa = Setting::firstOrNew([
            'code' => 'epicdemand_tcpa',
        ]);
        if (! $epicdemand_tcpa->exists) {
            $epicdemand_tcpa->name = 'Epic Demand TCPA';
            $epicdemand_tcpa->save();
        }

        $pfr_tcpa = Setting::firstOrNew([
            'code' => 'pfr_tcpa',
        ]);
        if (! $pfr_tcpa->exists) {
            $pfr_tcpa->name = 'Paid for Research TCPA';
            $pfr_tcpa->save();
        }

        $admired_tcpa = Setting::firstOrNew([
            'code' => 'admired_tcpa',
        ]);
        if (! $admired_tcpa->exists) {
            $admired_tcpa->name = 'Admired Opinion TCPA';
            $admired_tcpa->save();
        }

        $clinical_trial_tcpa = Setting::firstOrNew([
            'code' => 'clinical_trial_tcpa',
        ]);
        if (! $clinical_trial_tcpa->exists) {
            $clinical_trial_tcpa->name = 'Clinical TCPA';
            $clinical_trial_tcpa->save();
        }

        $error_email_recipient = Setting::firstOrNew([
            'code' => 'error_email_recipient',
        ]);
        if (! $error_email_recipient->exists) {
            $error_email_recipient->name = 'Error Email Recipient';
            $error_email_recipient->description = 'karla@engageiq.com;burt@engageiq.com';
            $error_email_recipient->save();
        }

        $publisher_percentage_revenue = Setting::firstOrNew([
            'code' => 'publisher_percentage_revenue',
        ]);
        if (! $publisher_percentage_revenue->exists) {
            $publisher_percentage_revenue->name = 'Publisher Given Percentage Revenue';
            $publisher_percentage_revenue->description = '70';
            $publisher_percentage_revenue->save();
        }

        $data_feed_excluded_affiliates = Setting::firstOrNew([
            'code' => 'data_feed_excluded_affiliates',
        ]);
        if (! $data_feed_excluded_affiliates->exists) {
            $data_feed_excluded_affiliates->name = 'DataFeed Excluded Affiliates';
            $data_feed_excluded_affiliates->save();
        }

        $js_midpath_email_report = Setting::firstOrNew([
            'code' => 'js_midpath_email_report',
        ]);
        if (! $js_midpath_email_report->exists) {
            $js_midpath_email_report->name = 'JS Midpath Email Report';
            $js_midpath_email_report->save();
        }

        $excessive_affiliate_subids = Setting::firstOrNew([
            'code' => 'excessive_affiliate_subids',
        ]);
        if (! $excessive_affiliate_subids->exists) {
            $excessive_affiliate_subids->name = 'Excessive Affilaite Subids';
            $excessive_affiliate_subids->save();
        }

        $full_rejection_alert_status = Setting::firstOrNew([
            'code' => 'full_rejection_alert_status',
        ]);
        if (! $full_rejection_alert_status->exists) {
            $full_rejection_alert_status->name = 'Enable/Disable 100% Rejection Report?';
            $full_rejection_alert_status->integer_value = 1;
            $full_rejection_alert_status->save();
        }

        $full_rejection_alert_min_leads = Setting::firstOrNew([
            'code' => 'full_rejection_alert_min_leads',
        ]);
        if (! $full_rejection_alert_min_leads->exists) {
            $full_rejection_alert_min_leads->name = 'Minimum number of leads to check';
            $full_rejection_alert_min_leads->description = 30;
            $full_rejection_alert_min_leads->save();
        }

        $full_rejection_alert_check_days = Setting::firstOrNew([
            'code' => 'full_rejection_alert_check_days',
        ]);
        if (! $full_rejection_alert_check_days->exists) {
            $full_rejection_alert_check_days->name = 'Number of days to look back for leads';
            $full_rejection_alert_check_days->description = 2;
            $full_rejection_alert_check_days->save();
        }

        $full_rejection_advertiser_email_status = Setting::firstOrNew([
            'code' => 'full_rejection_advertiser_email_status',
        ]);
        if (! $full_rejection_advertiser_email_status->exists) {
            $full_rejection_advertiser_email_status->name = 'Send email to advertiser for those campaigns that have 100% rejection rate.';
            $full_rejection_advertiser_email_status->integer_value = 1;
            $full_rejection_advertiser_email_status->save();
        }

        $full_rejection_excluded_campaigns = Setting::firstOrNew([
            'code' => 'full_rejection_excluded_campaigns',
        ]);
        if (! $full_rejection_excluded_campaigns->exists) {
            $full_rejection_excluded_campaigns->name = 'Exclude the following Campaign from Automatically Shutting Off if it has 100% Rejection';
            $full_rejection_excluded_campaigns->description = '';
            $full_rejection_excluded_campaigns->save();
        }

        $full_rejection_deactivate_campaign_status = Setting::firstOrNew([
            'code' => 'full_rejection_deactivate_campaign_status',
        ]);
        if (! $full_rejection_deactivate_campaign_status->exists) {
            $full_rejection_deactivate_campaign_status->name = 'Shut OFF automaticall those campaigns that have 100% rejection rate.';
            $full_rejection_deactivate_campaign_status->integer_value = 1;
            $full_rejection_deactivate_campaign_status->save();
        }

        $click_log_num_days = Setting::firstOrNew([
            'code' => 'click_log_num_days',
        ]);
        if (! $click_log_num_days->exists) {
            $click_log_num_days->name = 'Days coverage for the trace';
            $click_log_num_days->integer_value = 30;
            $click_log_num_days->save();
        }

        $clic_logs_apply_all_affiliates = Setting::firstOrNew([
            'code' => 'clic_logs_apply_all_affiliates',
        ]);
        if (! $clic_logs_apply_all_affiliates->exists) {
            $clic_logs_apply_all_affiliates->name = 'Apply click log tracing to all Affiliates.';
            $clic_logs_apply_all_affiliates->integer_value = 0;
            $clic_logs_apply_all_affiliates->save();
        }
    }
}
