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
$factory->define(App\Advertiser::class, function (Faker\Generator $faker) {
    return [
        'company' => $faker->company,
        'website_url' => $faker->url,
        'phone' => $faker->phoneNumber,
        'address' => $faker->address,
        'city' => $faker->city,
        'state' => 'CA',
        'zip' => '900011',
        'description' => $faker->sentence,
        'status' => $faker->numberBetween(0, 1),
    ];
});
