<?php

namespace Database\Seeders;

use App\Advertiser;
use App\Affiliate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = App\User::firstOrCreate([
            'email' => 'ariel@engageiq.com',
        ]);

        $admin->title = 'Mr.';
        $admin->first_name = 'Ariel';
        $admin->middle_name = 'Cacanog';
        $admin->last_name = 'Magbanua';
        $admin->gender = 'M';
        $admin->position = 'Administrator/Developer';
        $admin->password = Hash::make(12345);
        $admin->role_id = 1;
        $admin->account_type = 2;
        $admin->save();

        $faker = Faker\Factory::create();

        $affiliateIDs = Affiliate::pluck('id')->toArray();
        $advertiserIDs = Advertiser::pluck('id')->toArray();

        $affiliate = App\User::firstOrCreate([
            'email' => 'karla@engageiq.com',
        ]);

        $affiliate->title = 'Mr.';
        $affiliate->first_name = 'Karla';
        $affiliate->middle_name = 'Librero';
        $affiliate->last_name = 'Librero';
        $affiliate->gender = 'F';
        $affiliate->position = 'Affiliate Master of Coin';
        $affiliate->password = Hash::make(12345);
        $affiliate->account_type = 1;
        $affiliate->affiliate_id = $faker->randomElement($affiliateIDs);
        $affiliate->save();

        $advertiser = App\User::firstOrCreate([
            'email' => 'monty@engageiq.com',
        ]);

        $advertiser->title = 'Mr.';
        $advertiser->first_name = 'Monty';
        $advertiser->middle_name = 'Magbanua';
        $advertiser->last_name = 'Magbanua';
        $advertiser->gender = 'M';
        $advertiser->position = 'Advertiser Master of TV Shows';
        $advertiser->password = Hash::make(12345);
        $advertiser->account_type = 1;
        $advertiser->advertiser_id = $faker->randomElement($advertiserIDs);
        $advertiser->save();

        $advertiser = App\User::firstOrCreate([
            'email' => 'francis@engageiq.com',
        ]);

        $advertiser->title = 'Sir.';
        $advertiser->first_name = 'Francis';
        $advertiser->middle_name = 'Dumili';
        $advertiser->last_name = 'Magallen';
        $advertiser->gender = 'M';
        $advertiser->position = 'Nothing';
        $advertiser->password = Hash::make(12345678);
        $advertiser->account_type = 1;
        $advertiser->advertiser_id = $faker->randomElement($advertiserIDs);
        $advertiser->save();
    }
}
