<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CampaignPayoutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        App\CampaignPayout::firstOrCreate([
            'campaign_id' => 1,
            'affiliate_id' => 1,
            'received' => 2,
            'payout' => 1,
        ]);
    }
}
