<?php

namespace Database\Seeders;

use App\CampaignView;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class CampaignViewsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $revTrackerIDs = [1, 74, 169, 7612, 7614, 7672, 7673, 7690, 7750, 7754, 7755, 7770, 7789, 7790, 7816, 7819, 7820, 7845, 7861, 7874, 7875, 7876, 7881, 7882, 786, 7913, 7914,  7915, 7929, 7932];
        $campaignIDs = [94, 96, 283, 285, 287, 290, 304, 535];

        $startDate = Carbon::parse('2018-06-01');
        $numberOfDays = 31;

        $faker = Faker\Factory::create();

        for ($i = 0; $i < $numberOfDays; $i++) {
            $date = $startDate->addDay();

            foreach ($revTrackerIDs as $revTrackerID) {
                foreach ($campaignIDs as $campaignID) {
                    $views = $faker->numberBetween(1, 150);

                    for ($i = 0; $i < $views; $i++) {
                        $dateTime = $date->addHour($faker->numberBetween(0, 20))->addMinute($faker->numberBetween(0, 45))->toDateTimeString();

                        try {
                            $campaignView = CampaignView::firstOrNew([
                                'campaign_id' => $campaignID,
                                // 'affiliate_id' => $revTrackerID,
                                'session' => 'auto-generated-'.$faker->word().$faker->word().$faker->word().$faker->word(),
                                // 'created_at' => $dateTime
                            ]);

                            $campaignView->affiliate_id = $revTrackerID;
                            $campaignView->created_at = $dateTime;

                            $campaignView->save();
                        } catch (Exception $e) {
                            Log::info($e);
                        }
                    }
                }
            }
        }
    }
}
