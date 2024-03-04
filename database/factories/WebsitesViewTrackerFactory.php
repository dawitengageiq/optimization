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
$factory->define(App\WebsitesViewTracker::class, function (Faker\Generator $faker) {

    $affiliateWebsites = App\AffiliateWebsite::whereIn('affiliate_id', [1, 2, 3, 3432])->pluck('id')->toArray();

    //$dateStr = Carbon\Carbon::now()->subDay()->toDateTimeString();
    $dateStr = '2017-10-01';

    return [
        'website_id' => $faker->randomElement($affiliateWebsites),
        'email' => $faker->email,
        'payout' => $faker->randomFloat(3, 1, 30),
        'created_at' => $dateStr,
        'updated_at' => $dateStr,
    ];
});

$factory->define(App\WebsitesViewTracker::class, function (Faker\Generator $faker) {

    $affiliateWebsites = App\AffiliateWebsite::whereIn('affiliate_id', [1, 2, 3, 3432])->pluck('id')->toArray();

    //$dateStr = Carbon\Carbon::now()->subDay()->toDateTimeString();
    $dateStr = '2017-10-01';

    return [
        'website_id' => $faker->randomElement($affiliateWebsites),
        'email' => $faker->email,
        'payout' => $faker->randomFloat(3, 1, 30),
        'created_at' => $dateStr,
        'updated_at' => $dateStr,
    ];
});
