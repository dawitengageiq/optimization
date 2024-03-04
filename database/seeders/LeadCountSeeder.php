<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LeadCountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campaigns = App\Campaign::pluck('id')->toArray();

        $date_now = Carbon\Carbon::now()->toDateString();
        foreach ($campaigns as $campaign) {

            App\LeadCount::firstOrCreate([
                'campaign_id' => $campaign,
                'affiliate_id' => null,
                'count' => 0,
                'reference_date' => $date_now,
            ]);

            $affiliates = App\AffiliateCampaign::where('campaign_id', $campaign)->pluck('affiliate_id')->toArray();
            foreach ($affiliates as $affiliate) {
                App\LeadCount::firstOrCreate([
                    'campaign_id' => $campaign,
                    'affiliate_id' => $affiliate,
                    'count' => 0,
                    'reference_date' => $date_now,
                ]);
            }
        }
    }
}
