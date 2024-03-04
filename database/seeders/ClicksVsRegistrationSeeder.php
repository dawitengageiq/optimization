<?php

namespace Database\Seeders;

use App\ClicksVsRegistrationStatistics;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ClicksVsRegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $revenueTrackers = App\AffiliateRevenueTracker::where('offer_id', '!=', 1)->get();
        $date = Carbon::yesterday()->toDateString();

        foreach ($revenueTrackers as $tracker) {
            try {
                ClicksVsRegistrationStatistics::factory()->count(1)->create([
                    'affiliate_id' => $tracker->affiliate_id,
                    'revenue_tracker_id' => $tracker->revenue_tracker_id,
                    'created_at' => $date,
                ]);
            } catch (Exception $e) {
                //do nothing
            }
        }
    }
}
