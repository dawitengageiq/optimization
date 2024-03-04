<?php

namespace Database\Seeders;

use App\AffiliateRevenueTracker;
use App\Campaign;
use App\Lead;
use App\LeadSentResult;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LeadsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coregs = Campaign::whereNotIn('campaign_type', [4, 5, 6])->where('status', '!=', 0)->pluck('id')->toArray();
        $rev_trackers = AffiliateRevenueTracker::pluck('revenue_tracker_id')->toArray();

        // $coregs = [12,1,7,4,129,92,20,218,30,211];
        $rev_trackers = [7819, 7789, 7876, 7875, 7881, 7874, 7899, 7898, 7900, 7882];

        $coregs = [12, 1, 7, 4, 129, 92, 20, 218, 30, 211];
        // $rev_trackers = [7819, 7789, 7876];

        $rejected_results = ['duplicate', 'no blank found', 'already found', 'existed', 'invalid value', 'does not have value', 'error'];

        $faker = Faker\Factory::create();
        $numberOfLeads = 15000;

        $date_yesterday = Carbon::yesterday()->toDateString();
        // $date = $date_yesterday.' '.$faker->numberBetween(10,23).':'.$faker->numberBetween(10,59).':'.$faker->numberBetween(10,59);
        for ($x = 0; $x < $numberOfLeads; $x++) {
            $date = $date_yesterday.' '.$faker->numberBetween(10, 23).':'.$faker->numberBetween(10, 59).':'.$faker->numberBetween(10, 59);

            $lead = Lead::firstOrNew([
                'campaign_id' => $faker->randomElement($coregs),
                'lead_email' => $faker->email(),
            ]);

            $lead->affiliate_id = $faker->randomElement($rev_trackers);
            // $lead->lead_status = $faker->randomElement([0,1,2,5]);
            $lead->lead_status = $faker->randomElement([1, 2]);
            // $lead->lead_status = 2;
            $lead->payout = 1;
            $lead->received = 1;
            $lead->created_at = $date;
            $lead->updated_at = $date;
            $lead->save();

            $result = LeadSentResult::firstOrNew([
                'id' => $lead->id,
            ]);
            if ($lead->lead_status == 2) {
                $result->value = $faker->randomElement($rejected_results);
            } elseif ($lead->lead_status == 1) {
                $result->value = 'Success';
            } else {
                $result->value = '';
            }

            $result->save();
        }
    }
}
