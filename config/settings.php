<?php

return [
    'address_feed_status' => env('ADDRESS_FEED_STATUS', 'false'),
    'admired_opinion_feed_status' => env('ADMIRED_OPINION_FEED_STATUS', 'false'),
    'all_inbox_campaign_id' => env('ALL_INBOX_CAMPAIGN_ID', 286),
    'all_inbox_feed_status' => env('ALL_INBOX_FEED_STATUS', 'false'),
    'all_inbox_logger' => env('ALL_INBOX_LOGGER', 'true'),
    'api_key' => env('API_KEY', '00b462d748d32231540d3dee001dbbadec723a56+e67f380c13a5664c91d48f38d822b3272f2c62a2'),
    'api_user' => env('API_USER', 'api@engageiq.com'),
    'app_base_url' => env('APP_BASE_URL', 'https://leadreactor.engageiq.com'),
    'app_build' => env('APP_BUILD'),
    'app_server' => env('APP_SERVER', 'master'),
    'cpa_wall_affluent_campaign_id' => env('CPA_WALL_AFFLUENT_CAMPAIGN_ID', 0),
    'cpa_wall_campaign_ids' => env('CPA_WALL_CAMPAIGN_IDS'),
    'cpa_wall_engageiq_campaign_id' => env('CPA_WALL_ENGAGEIQ_CAMPAIGN_ID', 0),
    'cpa_wall_epoll_campaign_id' => env('CPA_WALL_EPOLL_CAMPAIGN_ID', 285),
    'cpa_wall_job_to_shop_campaign_id' => env('CPA_WALL_JOB_TO_SHOP_CAMPAIGN_ID', 297),
    'cpa_wall_sbg_campaign_id' => env('CPA_WALL_SBG_CAMPAIGN_ID', 0),
    'cpa_wall_survey_spot_campaign_id' => env('CPA_WALL_SURVEY_SPOT_CAMPAIGN_ID', 0),
    'cvd_feed_status' => env('CVD_FEED_STATUS', 'false'),
    'eiq_iframe_id' => env('EIQ_IFRAME_ID', 0),
    'email_media_buy_feed_status' => env('EMAIL_MEDIA_BUY_FEED_STATUS', 'false'),
    'encryption_applied' => env('ENCRYPTION_APPLIED', false),
    'encryption_key' => env('ENCRYPTION_KEY'),
    'ep_all_inbox_feed_status' => env('EP_ALL_INBOX_FEED_STATUS', 'false'),
    'execute_all_inbox' => env('EXECUTE_ALL_INBOX', true),
    'execute_archive_cake_conversions' => env('EXECUTE_ARCHIVE_CAKE_CONVERSIONS', true),
    'execute_archive_leads' => env('EXECUTE_ARCHIVE_LEADS', true),
    'execute_bug_reports' => env('EXECUTE_BUG_REPORTS', true),
    'execute_campaign_revenue_breakdown' => env('EXECUTE_CAMPAIGN_REVENUE_BREAKDOWN', true),
    'execute_clean_dashboard_stats' => env('EXECUTE_CLEAN_DASHBOARD_STATS', true),
    'execute_clean_queued_leads' => env('EXECUTE_CLEAN_QUEUED_LEADS', true),
    'execute_coreg_performance_reports' => env('EXECUTE_COREG_PERFORMANCE_REPORTS', true),
    'execute_creative_revenue_report' => env('EXECUTE_CREATIVE_REVENUE_REPORT', true),
    'execute_daily_reorder_mixed_coreg_campaigns' => env('EXECUTE_DAILY_REORDER_MIXED_COREG_CAMPAIGNS', false),
    'execute_delete_opt_out_user' => env('EXECUTE_DELETE_OPT_OUT_USER', true),
    'execute_email_lead_csv_data_feed' => env('EXECUTE_EMAIL_LEAD_CSV_DATA_FEED', true),
    'execute_ftp_lead_feed_csv' => env('EXECUTE_FTP_LEAD_FEED_CSV', true),
    'execute_generate_affiliate_reports' => env('EXECUTE_GENERATE_AFFILIATE_REPORTS', true),
    'execute_generate_affiliate_reports_hourly' => env('EXECUTE_GENERATE_AFFILIATE_REPORTS_HOURLY', true),
    'execute_generate_affiliate_website_report' => env('EXECUTE_GENERATE_AFFILIATE_WEBSITE_REPORT', true),
    'execute_generate_clicks_vs_registration_statistics' => env('EXECUTE_GENERATE_CLICKS_VS_REGISTRATION_STATISTICS', true),
    'execute_generate_consolidated_graph' => env('EXECUTE_GENERATE_CONSOLIDATED_GRAPH', true),
    'execute_generate_external_path_reports' => env('EXECUTE_GENERATE_EXTERNAL_PATH_REPORTS', true),
    'execute_generate_handp_reports' => env('EXECUTE_GENERATE_HANDP_REPORTS', true),
    'execute_generate_high_rejection_alert_report' => env('EXECUTE_GENERATE_HIGH_REJECTION_ALERT_REPORT', true),
    'execute_generate_iframe_reports' => env('EXECUTE_GENERATE_IFRAME_REPORTS', true),
    'execute_generate_page_view_statistics' => env('EXECUTE_GENERATE_PAGE_VIEW_STATISTICS', true),
    'execute_get_cake_conversions' => env('EXECUTE_GET_CAKE_CONVERSIONS', true),
    'execute_get_one_trust_email' => env('EXECUTE_GET_ONE_TRUST_EMAIL', true),
    'execute_get_subscribed_campaigns' => env('EXECUTE_GET_SUBSCRIBED_CAMPAIGNS', true),
    'execute_lead_advertiser_data_csv' => env('EXECUTE_LEAD_ADVERTISER_DATA_CSV', true),
    'execute_lead_fail_timeout_report' => env('EXECUTE_LEAD_FAIL_TIMEOUT_REPORT', true),
    'execute_nocpl_reminder' => env('EXECUTE_NOCPL_REMINDER', true),
    'execute_reorder_campaigns' => env('EXECUTE_REORDER_CAMPAIGNS', false),
    'execute_reorder_mixed_coreg_campaigns' => env('EXECUTE_REORDER_MIXED_COREG_CAMPAIGNS', false),
    'execute_reset_campaign_type_views' => env('EXECUTE_RESET_CAMPAIGN_TYPE_VIEWS', true),
    'execute_reset_lead_counters' => env('EXECUTE_RESET_LEAD_COUNTERS', true),
    'execute_rev_tracker_update_subid_breakdown' => env('EXECUTE_REV_TRACKER_UPDATE_SUBID_BREAKDOWN', true),
    'execute_send_affiliate_reg_revenue_report' => env('EXECUTE_SEND_AFFILIATE_REG_REVENUE_REPORT', true),
    'execute_send_opt_out_report' => env('EXECUTE_SEND_OPT_OUT_REPORT', true),
    'execute_send_pending_leads' => env('EXECUTE_SEND_PENDING_LEADS', true),
    'execute_send_pending_leads_with_job_queue' => env('EXECUTE_SEND_PENDING_LEADS_WITH_JOB_QUEUE', true),
    'execute_send_publisher_remove_user' => env('EXECUTE_SEND_PUBLISHER_REMOVE_USER', true),
    'execute_send_user_one_trust_email' => env('EXECUTE_SEND_USER_ONE_TRUST_EMAIL', true),
    'execute_transfer_finished_cron_jobs' => env('EXECUTE_TRANSFER_FINISHED_CRON_JOBS', true),
    'execute_update_cpawall_status' => env('EXECUTE_UPDATE_CPAWALL_STATUS', true),
    'external_path_adsmith_campaign_id' => env('EXTERNAL_PATH_ADSMITH_CAMPAIGN_ID', '2127'),
    'external_path_ifficient_campaign_id' => env('EXTERNAL_PATH_IFFICIENT_CAMPAIGN_ID', '287'),
    'external_path_permission_data_campaign_i' => env('EXTERNAL_PATH_PERMISSION_DATA_CAMPAIGN_I', 283),
    'external_path_permission_data_campaign_id' => env('EXTERNAL_PATH_PERMISSION_DATA_CAMPAIGN_ID', 283),
    'external_path_rexads_campaign_id' => env('EXTERNAL_PATH_REXADS_CAMPAIGN_ID', 289),
    'external_path_tiburon_campaign_id' => env('EXTERNAL_PATH_TIBURON_CAMPAIGN_ID', 290),
    'jira_api_base_url' => env('JIRA_API_BASE_URL', 'https://engageiq.atlassian.net'),
    'jira_issue_assignee_username' => env('JIRA_ISSUE_ASSIGNEE_USERNAME', 'Ariel'),
    'jira_project_key' => env('JIRA_PROJECT_KEY', 'NLR'),
    'jira_user_password' => env('JIRA_USER_PASSWORD', 'magbanua2016'),
    'jira_username' => env('JIRA_USERNAME', 'monty'),
    'mac_key' => env('MAC_KEY'),
    'phone_feed_status' => env('PHONE_FEED_STATUS', 'false'),
    'push_crew_notifications_campaign_id' => env('PUSH_CREW_NOTIFICATIONS_CAMPAIGN_ID', 304),
    'push_pro_campaign_id' => env('PUSH_PRO_CAMPAIGN_ID', 1672),
    'reports_allow_origin' => env('REPORTS_ALLOW_ORIGIN', 'http://leadreactor.engageiq.com'),
    'reports_email_notification_recipient' => env('REPORTS_EMAIL_NOTIFICATION_RECIPIENT', 'marwilburton@hotmail.com'),
];
