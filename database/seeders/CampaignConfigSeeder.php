<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CampaignConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campaigns = App\Campaign::pluck('id')->toArray();
        // use the factory to create a Faker\Generator instance
        $faker = Faker\Factory::create();

        foreach ($campaigns as $campaign) {
            App\CampaignConfig::firstOrCreate([
                'id' => $campaign,
                'post_url' => $faker->url(),
                'post_header' => 'no header requried',
                'post_data' => '{"email":"email","fname":"firstname","lname":"lastname","ipaddress":"ip","city":"city","state":"state","zip":"postal_code","dobday":"date_of_birthday","dobmonth":"date_of_birthmonth","dobyear":"date_of_birthyear"}',
                'post_data_fixed_value' => '{"sr":"600"}',
                'post_data_map' => 'no post data map required',
                'post_method' => 'GET',
                'post_success' => 'OK',
            ]);
        }
    }
}
