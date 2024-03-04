<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PageViewStatisticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker\Factory::create();
        $date = Carbon\Carbon::yesterday()->toDateString();

        $revenue_trackers = [1, 7612, 7820, 8245, 7819, 8094, 8093, 7789, 8095, 8128, 8294, 8095, 8053, 8106, 18657, 7932, 7790, 7861, 8206];
        // $revenue_trackers = App\AffiliateRevenueTracker::pluck('revenue_tracker_id')->limit('50')->toArray();
        // DB::enableQueryLog();
        $aff_rev = App\AffiliateRevenueTracker::whereIn('revenue_tracker_id', $revenue_trackers)->pluck('affiliate_id', 'revenue_tracker_id');
        // Log::info(DB::getQueryLog());
        // Log::info($aff_rev);
        foreach ($aff_rev as $rev => $aff) {
            $stat = App\PageViewStatistics::firstOrNew([
                'revenue_tracker_id' => $rev,
                'affiliate_id' => $aff == null ? $rev : $aff,
                'created_at' => $date,
            ]);

            $stat->lp = $faker->numberBetween(50, 100);
            $stat->rp = $faker->numberBetween(50, $stat->lp);
            $stat->to1 = $faker->numberBetween(50, $stat->rp);
            $stat->to2 = $faker->numberBetween(45, $stat->to1);
            $stat->mo1 = $faker->numberBetween(45, $stat->to2);
            $stat->mo2 = $faker->numberBetween(45, $stat->mo1);
            $stat->mo3 = $faker->numberBetween(45, $stat->mo2);
            $stat->mo4 = $faker->numberBetween(40, $stat->mo3);
            $stat->lfc1 = $faker->numberBetween(40, $stat->mo4);
            $stat->lfc2 = $faker->numberBetween(40, $stat->lfc1);
            $stat->tbr1 = $faker->numberBetween(40, $stat->lfc2);
            $stat->pd = $faker->numberBetween(40, $stat->tbr1);
            $stat->tbr2 = $faker->numberBetween(35, $stat->pd);
            $stat->iff = $faker->numberBetween(35, $stat->tbr2);
            $stat->rex = $faker->numberBetween(35, $stat->iff);
            $stat->cpawall = $faker->numberBetween(35, $stat->rex);
            $stat->exitpage = $faker->numberBetween(35, $stat->cpawall);
            $stat->save();
        }

        $campaigns = [4, 23, 221, 74, 51, 107, 144, 28, 204, 37];
        $campaigns = [4, 23, 221, 74, 51, 107, 144, 28, 204, 37, 544, 431, 33, 40, 80, 114, 21, 287, 51, 71];
        $settings = \App\Setting::where('code', 'campaign_type_benchmarks')->first();
        $campaigns = json_decode($settings->description, true);
        $affiliates = [1, 7612, 7820, 8245, 7819, 8094, 8093, 7789, 8095, 8128, 8294, 8095, 8053, 8106, 18657, 7932, 7790, 7861, 8206];

        $count = 1000;
        while ($count > 0) {
            $campaignID = $faker->randomElement($campaigns);
            $affiliateID = $faker->randomElement($affiliates);
            $dateStr = Carbon\Carbon::yesterday()->toDateTimeString();

            App\Lead::create([
                'campaign_id' => $campaignID,
                'affiliate_id' => $affiliateID,
                'lead_status' => $faker->numberBetween(1, 2),
                'lead_email' => $count.$faker->email(),
                'retry_count' => 0,
                'payout' => 1,
                'received' => 1,
                'last_retry_date' => '',
                'created_at' => $dateStr,
                'updated_at' => $dateStr,
            ]);
            $count--;
        }

        $revenue_trackers = [1, 7612, 7820, 8245, 7819, 8094, 8093, 7789, 8095, 8128, 8294, 8095, 8053, 8106, 18657, 7932, 7790, 7861, 8206];
        $aff_rev = App\AffiliateRevenueTracker::whereIn('revenue_tracker_id', $revenue_trackers)->pluck('affiliate_id', 'revenue_tracker_id');
        $offer_ids = App\Campaign::whereNotNull('linkout_offer_id')->where('linkout_offer_id', '!=', 0)->pluck('linkout_offer_id')->toArray();

        foreach ($aff_rev as $rev => $aff) {
            $count = 10;
            while ($count != 0) {
                $stat = App\CakeRevenue::firstOrNew([
                    'revenue_tracker_id' => $rev,
                    'affiliate_id' => $aff == null ? $rev : $aff,
                    'offer_id' => $faker->randomElement($offer_ids),
                    'created_at' => $date,
                ]);

                $stat->revenue = $faker->numberBetween(1.00, 100.00);
                $stat->save();
                $count--;
            }
        }

        // foreach($aff_rev as $rev => $aff) {
        //     $stat = App\CakeRevenue::firstOrNew([
        //         'revenue_tracker_id' => $rev,
        //         'affiliate_id' => $aff == null ? $rev : $aff,
        //         'offer_id' => 123456,
        //         'created_at' => $date
        //     ]);

        //     $stat->revenue = $faker->numberBetween(30.00,100.00);
        //     $stat->save();
        // }
    }
}
