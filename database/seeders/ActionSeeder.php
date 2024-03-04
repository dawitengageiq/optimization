<?php

namespace Database\Seeders;

use App\Action;
use Illuminate\Database\Seeder;

class ActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //main section
        $accessDashboard = Action::firstOrCreate([
            'name' => 'Access Dashboard',
            'code' => 'access_dashboard',
        ]);
        $accessDashboard->description = 'Access permission for dashboard.';
        $accessDashboard->save();

        $accessContacts = Action::firstOrCreate([
            'name' => 'Access Contacts',
            'code' => 'access_contacts',
        ]);
        $accessContacts->description = 'Access permission for contacts.';
        $accessContacts->save();

        $accessAffiliates = Action::firstOrCreate([
            'name' => 'Access Affiliates',
            'code' => 'access_affiliates',
        ]);
        $accessAffiliates->description = 'Access permission for affiliates.';
        $accessAffiliates->save();

        $accessCampaigns = Action::firstOrCreate([
            'name' => 'Access Campaigns',
            'code' => 'access_campaigns',
        ]);
        $accessCampaigns->description = 'Access permission for campaigns.';
        $accessCampaigns->save();

        $accessAdvertisers = Action::firstOrCreate([
            'name' => 'Access Advertisers',
            'code' => 'access_advertisers',
        ]);
        $accessAdvertisers->description = 'Access permission for advertisers.';
        $accessAdvertisers->save();

        $accessFilterTypes = Action::firstOrCreate([
            'name' => 'Access Filter Types',
            'code' => 'access_filter_types',
        ]);
        $accessFilterTypes->description = 'Access permission for filter types.';
        $accessFilterTypes->save();

        $accessReportsAndStatistics = Action::firstOrCreate([
            'name' => 'Access Reports and Statistics',
            'code' => 'access_reports_and_statistics',
        ]);
        $accessReportsAndStatistics->description = 'Access permission for reports and statistics.';
        $accessReportsAndStatistics->save();

        $accessSearchLeads = Action::firstOrCreate([
            'name' => 'Access Search Leads',
            'code' => 'access_search_leads',
        ]);
        $accessSearchLeads->description = 'Access permission for search leads.';
        $accessSearchLeads->save();

        $accessRevenueStatistics = Action::firstOrCreate([
            'name' => 'Access Revenue Statistics',
            'code' => 'access_revenue_statistics',
        ]);
        $accessRevenueStatistics->description = 'Access permission for revenue statistics.';
        $accessRevenueStatistics->save();

        $accessDuplicateLeads = Action::firstOrCreate([
            'name' => 'Access Duplicate Leads',
            'code' => 'access_duplicate_leads',
        ]);
        $accessDuplicateLeads->description = 'Access permission for duplicate leads.';
        $accessDuplicateLeads->save();

        $accessCoregReports = Action::firstOrCreate([
            'name' => 'Access Coreg Reports',
            'code' => 'access_coreg_reports',
        ]);
        $accessCoregReports->description = 'Access permission for access coreg reports.';
        $accessCoregReports->save();

        $accessPrepopStatistics = Action::firstOrCreate([
            'name' => 'Access Prepop Statistics',
            'code' => 'access_prepop_statistics',
        ]);
        $accessPrepopStatistics->description = 'Access permission for access prepop statistics.';
        $accessPrepopStatistics->save();

        $accessCreativeStatistics = Action::firstOrCreate([
            'name' => 'Access Creative Revenue Reports',
            'code' => 'access_creative_revenue_reports',
        ]);
        $accessCreativeStatistics->description = 'Access permission for access creative revenue reports.';
        $accessCreativeStatistics->save();

        $accessAffiliateReports = Action::firstOrCreate([
            'name' => 'Access Affiliate Reports',
            'code' => 'access_affiliate_reports',
        ]);
        $accessAffiliateReports->description = 'Access permission for affiliate reports.';
        $accessAffiliateReports->save();

        $accessRevenueTrackers = Action::firstOrCreate([
            'name' => 'Access Revenue Trackers',
            'code' => 'access_revenue_trackers',
        ]);
        $accessRevenueTrackers->description = 'Access permission for revenue trackers.';
        $accessRevenueTrackers->save();

        $accessGallery = Action::firstOrCreate([
            'name' => 'Access Gallery',
            'code' => 'access_gallery',
        ]);
        $accessGallery->description = 'Access permission for gallery.';
        $accessGallery->save();

        $accessZipMaster = Action::firstOrCreate([
            'name' => 'Accesss Zip Master',
            'code' => 'access_zip_master',
        ]);
        $accessZipMaster->description = 'Access permission for zip master.';
        $accessZipMaster->save();

        $accessApplyToRunRequest = Action::firstOrCreate([
            'name' => 'Access Apply To Run Request',
            'code' => 'access_apply_to_run_request',
        ]);
        $accessApplyToRunRequest->description = 'Access permission for apply to run request.';
        $accessApplyToRunRequest->save();

        $categoriesAccess = Action::firstOrCreate([
            'name' => 'Categories Access',
            'code' => 'access_categories',
        ]);
        $categoriesAccess->description = 'Access permission for categories.';
        $categoriesAccess->save();

        $accessSurveyTakers = Action::firstOrCreate([
            'name' => 'Access Survey Takers',
            'code' => 'access_survey_takers',
        ]);
        $accessSurveyTakers->description = 'Access permission for survey takers.';
        $accessSurveyTakers->save();

        $accessCakeConversions = Action::firstOrCreate([
            'name' => 'Access Cake Conversions',
            'code' => 'access_cake_conversions',
        ]);
        $accessCakeConversions->description = 'Access permission for cake conversions.';
        $accessCakeConversions->save();

        $accessUsersAndRoles = Action::firstOrCreate([
            'name' => 'Access Users and Roles',
            'code' => 'access_users_and_roles',
        ]);
        $accessUsersAndRoles->description = 'Access permission for users and roles.';
        $accessUsersAndRoles->save();

        $accessSettings = Action::firstOrCreate([
            'name' => 'Access Settings',
            'code' => 'access_settings',
        ]);
        $accessSettings->description = 'Access permission for settings.';
        $accessSettings->save();

        $accessUserActionHistory = Action::firstOrCreate([
            'name' => 'Access User Action History',
            'code' => 'access_user_action_history',
        ]);
        $accessUserActionHistory->description = 'Access permission for user action history.';
        $accessUserActionHistory->save();

        $accessSurveyPaths = Action::firstOrCreate([
            'name' => 'Access Survey Paths',
            'code' => 'access_survey_paths',
        ]);
        $accessSurveyPaths->description = 'Access permission for survey paths.';
        $accessSurveyPaths->save();

        $accessCronJob = Action::firstOrCreate([
            'name' => 'Access Cron Job',
            'code' => 'access_cron_job',
        ]);
        $accessCronJob->description = 'Access permission for cron job.';
        $accessCronJob->save();

        //seeder for contacts content
        $addContact = Action::firstOrCreate([
            'name' => 'Add Contact',
            'code' => 'use_add_contact',
        ]);
        $addContact->description = 'Use permission for Add contact in Contacts page.';
        $addContact->save();

        $editContact = Action::firstOrCreate([
            'name' => 'Edit Contact',
            'code' => 'use_edit_contact',
        ]);
        $editContact->description = 'Use permission for Edit contact in Contacts page.';
        $editContact->save();

        $deleteContact = Action::firstOrCreate([
            'name' => 'Delete Contact',
            'code' => 'use_delete_contact',
        ]);
        $deleteContact->description = 'Use permission for Delete contact in Contacts page.';
        $deleteContact->save();

        //seeder for affiliates content
        $addAffiliate = Action::firstOrCreate([
            'name' => 'Add Affiliate',
            'code' => 'use_add_affiliate',
        ]);
        $addAffiliate->description = 'Use permission for Add affiliate in Affiliates page.';
        $addAffiliate->save();

        $editAffiliate = Action::firstOrCreate([
            'name' => 'Edit Affiliate',
            'code' => 'use_edit_affiliate',
        ]);
        $editAffiliate->description = 'Use permission for Edit affiliate in Affiliates page.';
        $editAffiliate->save();

        $deleteAffiliate = Action::firstOrCreate([
            'name' => 'Delete Affiliate',
            'code' => 'use_delete_affiliate',
        ]);
        $deleteAffiliate->description = 'Use permission for Delete affiliate in Affiliates page.';
        $deleteAffiliate->save();

        //seeder for campaigns content
        $addCampaign = Action::firstOrCreate([
            'name' => 'Add Campaign',
            'code' => 'use_add_campaign',
        ]);
        $addCampaign->description = 'Use permission for Add campaign in Campaigns page.';
        $addCampaign->save();

        $editCampaign = Action::firstOrCreate([
            'name' => 'Edit Campaign',
            'description' => '',
            'code' => 'use_edit_campaign',
        ]);
        $editCampaign->description = 'Use permission for Edit campaign in Campaigns page.';
        $editCampaign->save();

        $deleteCampaign = Action::firstOrCreate([
            'name' => 'Delete Campaign',
            'description' => 'Use permission for Delete campaign in Campaigns page.',
            'code' => 'use_delete_campaign',
        ]);
        $deleteCampaign->description = 'Use permission for Delete campaign in Campaigns page.';
        $deleteCampaign->save();

        //seeder for edit campaign tabs
        $editCampaignInfoTab = Action::firstOrCreate([
            'name' => 'Edit Campaign Info Tab',
            'code' => 'use_edit_campaign_info_tab',
        ]);
        $editCampaignInfoTab->description = 'Use permission for Edit campaign info tab in a certain campaign.';
        $editCampaignInfoTab->save();

        $editCampaignFiltersTab = Action::firstOrCreate([
            'name' => 'Edit Campaign Filters Tab',
            'code' => 'use_edit_campaign_filters_tab',
        ]);
        $editCampaignFiltersTab->description = 'Use permission for Edit campaign filters tab in a certain campaign.';
        $editCampaignFiltersTab->save();

        $editCampaignAffiliatesTab = Action::firstOrCreate([
            'name' => 'Edit Campaign Affiliates Tab',
            'code' => 'use_edit_campaign_affiliates_tab',
        ]);
        $editCampaignAffiliatesTab->description = 'Use permission for Edit campaign affiliates tab in a certain campaign.';
        $editCampaignAffiliatesTab->save();

        $editCampaignPayoutsTab = Action::firstOrCreate([
            'name' => 'Edit Campaign Payouts Tab',
            'code' => 'use_edit_campaign_payouts_tab',
        ]);
        $editCampaignPayoutsTab->description = 'Use permission for Edit campaign payouts tab in a certain campaign.';
        $editCampaignPayoutsTab->save();

        $editCampaignConfigTab = Action::firstOrCreate([
            'name' => 'Edit Campaign Config Tab',
            'code' => 'use_edit_campaign_config_tab',
        ]);
        $editCampaignConfigTab->description = 'Use permission for Edit campaign config tab in a certain campaign.';
        $editCampaignConfigTab->save();

        $editCampaignLongContentTab = Action::firstOrCreate([
            'name' => 'Edit Campaign Long Content Tab',
            'description' => '',
            'code' => 'use_edit_campaign_long_content_tab',
        ]);
        $editCampaignLongContentTab->description = 'Use permission for Edit campaign long content tab in a certain campaign.';
        $editCampaignLongContentTab->save();

        $editCampaignStackContentTab = Action::firstOrCreate([
            'name' => 'Edit Campaign Stack Content Tab',
            'code' => 'use_edit_campaign_stack_content_tab',
        ]);
        $editCampaignStackContentTab->description = 'Use permission for Edit campaign stack content tab in a certain campaign.';
        $editCampaignStackContentTab->save();

        $editCampaignHighPayingContentTab = Action::firstOrCreate([
            'name' => 'Edit Campaign High Paying Content Tab',
            'code' => 'use_edit_campaign_high_paying_content_tab',
        ]);
        $editCampaignHighPayingContentTab->description = 'Use permission for Edit campaign high paying content tab in a certain campaign.';
        $editCampaignHighPayingContentTab->save();

        //seeder for advertisers content
        $ddAdvertiser = Action::firstOrCreate([
            'name' => 'Add Advertiser',
            'code' => 'use_add_advertiser',
        ]);
        $ddAdvertiser->description = 'Use permission for Add advertiser in Advertisers page.';
        $ddAdvertiser->save();

        $editAdvertiser = Action::firstOrCreate([
            'name' => 'Edit Advertiser',
            'code' => 'use_edit_advertiser',
        ]);
        $editAdvertiser->description = 'Use permission for Edit advertiser in Advertisers page.';
        $editAdvertiser->save();

        $deleteAdvertiser = Action::firstOrCreate([
            'name' => 'Delete Advertiser',
            'code' => 'use_delete_advertiser',
        ]);
        $deleteAdvertiser->description = 'Use permission for Delete advertiser in Advertisers page.';
        $deleteAdvertiser->save();

        //seeder for filter types content
        $addFilterType = Action::firstOrCreate([
            'name' => 'Add Filter Type',
            'description' => 'Use permission for Add filter type in Filter Types page.',
            'code' => 'use_add_filter_type',
        ]);
        $addFilterType->description = 'Use permission for Add filter type in Filter Types page.';
        $addFilterType->save();

        $editFilterType = Action::firstOrCreate([
            'name' => 'Edit Filter Type',
            'code' => 'use_edit_filter_type',
        ]);
        $editFilterType->description = 'Use permission for Edit filter type in Filter Types page.';
        $editFilterType->save();

        $deleteFilterType = Action::firstOrCreate([
            'name' => 'Delete Filter Type',
            'code' => 'use_delete_filter_type',
        ]);
        $deleteFilterType->description = 'Use permission for Delete filter type in Filter Types page.';
        $deleteFilterType->save();

        //seeder for revenue trackers content
        $addRevenueTracker = Action::firstOrCreate([
            'name' => 'Add Revenue Tracker',
            'code' => 'use_add_revenue_trackers',
        ]);
        $addRevenueTracker->description = 'Use permission for Add revenue tracker type in Revenue Trackers page.';
        $addRevenueTracker->save();

        $editRevenueTracker = Action::firstOrCreate([
            'name' => 'Edit Revenue Tracker',
            'code' => 'use_edit_revenue_trackers',
        ]);
        $editRevenueTracker->description = 'Use permission for Edit revenue tracker type in Revenue Trackers page.';
        $editRevenueTracker->save();

        $deleteRevenueTracker = Action::firstOrCreate([
            'name' => 'Delete Revenue Tracker',
            'code' => 'use_delete_revenue_trackers',
        ]);
        $deleteRevenueTracker->description = 'Use permission for Delete revenue tracker type in Revenue Trackers page.';
        $deleteRevenueTracker->save();

        //seeder for Gallery content
        $addGalleryImage = Action::firstOrCreate([
            'name' => 'Add Gallery Image',
            'code' => 'use_add_gallery_image',
        ]);
        $addGalleryImage->description = 'Use permission for Add image in Gallery page.';
        $addGalleryImage->save();

        $deleteGalleryImage = Action::firstOrCreate([
            'name' => 'Delete Gallery Image',
            'code' => 'use_delete_gallery_image',
        ]);
        $deleteGalleryImage->description = 'Use permission for Delete image in Gallery page.';
        $deleteGalleryImage->save();

        $apiAccess = Action::firstOrCreate([
            'name' => 'API Access',
            'code' => 'use_api_access',
        ]);
        $apiAccess->description = 'Use permission for available APIs.';
        $apiAccess->save();

        //seeder for categories
        $editCategory = Action::firstOrCreate([
            'name' => 'Edit Category',
            'code' => 'use_edit_category',
        ]);
        $editCategory->description = 'Use permission for Edit Category in Categories page.';
        $editCategory->save();

        $deleteCategory = Action::firstOrCreate([
            'name' => 'Delete Category',
            'code' => 'use_delete_category',
        ]);
        $deleteCategory->description = 'Use permission for Delete Category in Categories page.';
        $deleteCategory->save();

        $deleteCategory = Action::firstOrCreate([
            'name' => "Force Edit Campaign's Default Received and Default Payout",
            'code' => 'use_force_edit_default_received_payout',
        ]);
        $deleteCategory->description = 'Use permission for Force Edit Campaign\'s Default Received and Default Payout.';
        $deleteCategory->save();
    }
}
