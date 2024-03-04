<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CampaignFilterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        App\CampaignFilter::firstOrCreate([
            'campaign_id' => 1,
            'filter_type_id' => 1,
        ]);
    }
}
