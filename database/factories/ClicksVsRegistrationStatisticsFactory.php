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
$factory->define(App\ClicksVsRegistrationStatistics::class, function (Faker\Generator $faker) {

    $registrationCount = $faker->numberBetween(0, 5000);
    $clicks = $registrationCount + $faker->numberBetween(0, 100);

    return [
        'registration_count' => $registrationCount,
        'clicks' => $clicks,
        'percentage' => floatval($registrationCount / $clicks),
    ];
});
