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
$factory->define(App\Campaign::class, function (Faker\Generator $faker) {

    $advertisers = \App\Advertiser::pluck('id')->toArray();
    $campaign_types = config('constants.CAMPAIGN_TYPES');
    $campaign_types = array_keys($campaign_types);
    $campaign_count = \App\Campaign::count();
    $new_campaign_priority = $campaign_count + 1;

    return [
        'name' => 'Campaign-'.$faker->numberBetween(1, 1000),
        'advertiser_id' => $faker->randomElement($advertisers),
        'status' => $faker->numberBetween(0, 2),
        'description' => $faker->sentence(),
        'lead_cap_type' => $faker->numberBetween(1, 3),
        'lead_cap_value' => $faker->numberBetween(0, 10000),
        'priority' => $new_campaign_priority,
        'campaign_type' => $faker->randomElement($campaign_types),
        'default_received' => 2.50,
        'default_payout' => 1,
    ];
});
