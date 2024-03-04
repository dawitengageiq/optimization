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
$factory->define(App\LeadUser::class, function (Faker\Generator $faker) {
    return [
        'first_name' => $faker->firstName(null),
        'last_name' => $faker->lastName,
        'email' => $faker->email,
        'birthdate' => $faker->date($format = 'Y-m-d', $max = '-13 years'),
        'gender' => 'M',
        'city' => $faker->city,
        'state' => $faker->stateAbbr,
        'zip' => $faker->postcode,
        'address1' => $faker->address,
        'address2' => $faker->address,
        'ethnicity' => $faker->numberBetween(1, 6),
        'phone' => $faker->phoneNumber,
        'source_url' => $faker->url,
        'affiliate_id' => 1,
        'revenue_tracker_id' => 1,
        's1' => $faker->randomElement([1, 2, 3, 4, 5]),
        's2' => $faker->randomElement([1, 2, 3, 4, 5]),
        's3' => $faker->randomElement([1, 2, 3, 4, 5]),
        's4' => $faker->randomElement([1, 2, 3, 4, 5]),
        's5' => $faker->randomElement([1, 2, 3, 4, 5]),
    ];
});
