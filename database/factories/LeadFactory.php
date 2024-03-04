<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;


/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/
/*
$factory->define(App\Lead::class, function (Faker\Generator $faker) {

    //$campaigns = App\Campaign::whereIn('campaign_type',[4,255,32,209,208,139,247,92,129,83,210,20,23,5,168,147,200,186,101,90,169,206,265,213,135,6,134,192,292,266,279,264,38,259,220,33,28,159,40,19,199,46,296,112,189,22,47,128,239,240,276,277,278,291,254,122,256,232,126,251,175,80,27,253,35,182,120,119,153,82,152,29,109,234,233,102,149,103,219,174,167,217,180,214,154,173,183,177,142,138,121,117,132,113,155,156,133,79,166,110,111,18,137,36,91,44,78,45,43,11,87,181,165,260,295,299,300])->pluck('id')->toArray();
    //$campaigns = [4,255,32,209,208,139,247,92,129,83,210,20,23,5,168,147,200,186,101,90,169,206,265,213,135,6,134,192,292,266,279,264,38,259,220,33,28,159,40,19,199,46,296,112,189,22,47,128,239,240,276,277,278,291,254,122,256,232,126,251,175,80,27,253,35,182,120,119,153,82,152,29,109,234,233,102,149,103,219,174,167,217,180,214,154,173,183,177,142,138,121,117,132,113,155,156,133,79,166,110,111,18,137,36,91,44,78,45,43,11,87,181,165,260,295,299,300];
    // $campaigns = App\Campaign::where('status','=',1)->pluck('id')->toArray();
    $campaigns = [32, 65];
    // $campaigns = App\Campaign::pluck('id')->toArray();
    //$affiliates = App\Affiliate::where('type','=',2)->pluck('id')->toArray();
    //$affiliates = App\AffiliateRevenueTracker::pluck('revenue_tracker_id')->toArray();
    //$affiliates = App\AffiliateRevenueTracker::where('offer_id',1)->pluck('revenue_tracker_id')->toArray();
    //$affiliates = App\AffiliateRevenueTracker::where('offer_id', '!=', 1)->pluck('revenue_tracker_id')->toArray();
    $affiliates = [1, 7612, 7820, 8245, 7819, 8094, 8093, 7789, 8095, 8128];

    /*
    $affiliates = App\AffiliateRevenueTracker::where('offer_id', '=',1)
                                                ->where('campaign_id','=',1)
                                                ->where('revenue_tracker_id','=',1)
                                                ->pluck('revenue_tracker_id')->toArray();
    */

    $campaignID = $faker->randomElement($campaigns);
    $affiliateID = $faker->randomElement($affiliates);
    //$affiliateID = 2;
    $campaignPayout = App\CampaignPayout::getCampaignAffiliatePayout($campaignID, $affiliateID)->first();

    $subs = ['abc', 'def'];

    $payout = $faker->numberBetween(1.00, 2.00);
    $received = $faker->numberBetween(3.00, 6.00);

    if (isset($campaignPayout->payout)) {
        $payout = $campaignPayout->payout;
    }

    if (isset($campaignPayout->received)) {
        $received = $campaignPayout->received;
    }

    $dateStr = Carbon\Carbon::now()->subDays(30)->toDateTimeString();
    //$dateStr = \Carbon\Carbon::now()->subDays(1)->toDateTimeString();
    // $dateStr = Carbon\Carbon::parse('2017-10-01')->toDateTimeString();

    return [
        'campaign_id' => $campaignID,
        'affiliate_id' => $affiliateID,
        's1' => $faker->randomElement($subs),
        's2' => $faker->randomElement($subs),
        's3' => $faker->randomElement($subs),
        's4' => $faker->randomElement($subs),
        's5' => $faker->randomElement($subs),
        'lead_status' => 6,
        //'lead_status' => 1,
        'lead_email' => $faker->email(),
        'retry_count' => 0,
        'payout' => $payout,
        'received' => $received,
        //'last_retry_date' => $faker->dateTimeBetween('2016-07-28','2016-08-04'),
        'last_retry_date' => $dateStr,
        'created_at' => $dateStr,
        'updated_at' => $dateStr,
    ];
});
