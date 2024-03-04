<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Model::unguard();
        //$this->call(FilterTypeSeeder::class);
        //$this->call(AffiliateSeeder::class);
        //$this->call(AdvertiserSeeder::class);
        //$this->call(CampaignSeeder::class);
        //$this->call(LeadSeeder::class);
        //$this->call(CampaignFilterSeeder::class);
        //$this->call(AffiliateCampaignSeeder::class);
        //$this->call(LeadMessageSeeder::class);
        //$this->call(LeadSentResultSeeder::class);
        //$this->call(LeadDataCsvSeeder::class);
        //$this->call(LeadDataAdvSeeder::class);
        //$this->call(CampaignPayoutSeeder::class);
        //$this->call(CampaignConfigSeeder::class);
        //$this->call(LeadUserSeeder::class);
        $this->call(SettingSeeder::class);
        //$this->call(ZipMasterSeeder::class);
        //$this->call(ActionSeeder::class);
        //$this->call(RoleSeeder::class);
        //$this->call(UserSeeder::class);
        //$this->call(AffiliateCampaignRequestSeeder::class);
        //$this->call(CategorySeeder::class);
        //$this->call(LeadCountSeeder::class);
        //$this->call(CampaignFilterGroupSeeder::class);
        //$this->call(ZipCodeSeeder::class);
        //$this->call(RevTrackerNamerSeeder::class);
        //$this->call(AffiliateReportSeeder::class);
        //$this->call(ProgramIdExtractorSeeder::class);
        //$this->call(LeadsSeeder::class);
        //$this->call(CampaignTypeReportsTableSeeder::class);
        //$this->call(AffiliateWebsitesTableSeeder::class);
        //$this->call(WebsitesViewTrackerTableSeeder::class);
        // $this->call(MixedCoregCampaignOrdersSeeder::class);
        Model::reguard();
    }
}
