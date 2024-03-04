<?php

namespace Database\Seeders;

use App\Campaign;
use App\CampaignRevenueViewStatistic;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CampaignRevenueViewStatisticsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $campaigns = Campaign::where('status','=',1)->lists('id')->toArray();
        $revTrackerIDs = [1, 74, 169, 7612, 7614, 7672, 7673, 7690, 7750, 7754, 7755, 7770, 7789, 7790, 7816, 7819, 7820, 7845, 7861, 7874, 7875, 7876, 7881, 7882, 786, 7913, 7914,  7915, 7929, 7932];
        $campaignIDs = [94, 96, 283, 285, 287, 290, 304, 535];

        $startDate = Carbon::parse('2018-08-31');
        $numberOfDays = 31;

        $faker = Faker\Factory::create();

        for ($i = 0; $i < $numberOfDays; $i++) {

            $date = $startDate->addDay()->toDateString();

            foreach ($campaignIDs as $campaignID) {
                foreach ($revTrackerIDs as $revTrackerID) {
                    $stat = CampaignRevenueViewStatistic::firstOrNew([
                        'revenue_tracker_id' => $revTrackerID,
                        'campaign_id' => $campaignID,
                        'created_at' => $date,
                    ]);

                    $stat->revenue = $faker->randomNumber(3);
                    $stat->views = $faker->randomNumber(4);
                    $stat->created_at = $date;

                    $stat->save();
                }
            }
        }
    }
}
