<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CampaignTypeReportsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campaignTypes = config('constants.CAMPAIGN_TYPES');

        foreach ($campaignTypes as $key => $type) {
            $campaignTypeReport = \App\CampaignTypeReport::firstOrNew([
                'revenue_tracker_id' => 1,
                'campaign_type_id' => $key,
            ]);

            $campaignTypeReport->views = 1000;
            $campaignTypeReport->save();
        }
    }
}
