<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AffiliateCampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        App\AffiliateCampaign::firstOrCreate([
            'campaign_id' => 1,
            'affiliate_id' => 1,
            'lead_cap_type' => 1,
            'lead_cap_value' => 1,
        ]);
    }
}
