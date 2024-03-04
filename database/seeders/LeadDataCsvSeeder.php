<?php

namespace Database\Seeders;

use App\Lead;
use App\LeadDataCsv;
use Illuminate\Database\Seeder;

class LeadDataCsvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // use the factory to create a Faker\Generator instance
        $faker = Faker\Factory::create();
        //$leads = Lead::lists('id','lead_email')->toArray();
        //$leads = Lead::select('id','lead_email')->get();
        $leads = Lead::select('id', 'lead_email')->whereRaw('id NOT IN (SELECT lead_data_csvs.id FROM lead_data_csvs)')->get();

        foreach ($leads as $lead) {
            $data = LeadDataCsv::firstOrNew([
                'id' => $lead->id,
            ]);

            //$data->value = '{"email":"'.$lead->lead_email.'","fname":"'.$faker->firstName().'","lname":"'.$faker->lastName().'","ipaddress":"'.$faker->ipv4().'","city":"'.$faker->city().'","state":"CAL","zip":"'.$faker->postcode().'","dobday":"23","dobmonth":"04","dobyear":"1988"}';
            $data->value = '{"callback":"jQuery11110645003034519518_1477287179382","eiq_campaign_id":"'.$lead->campaign_id.'", "eiq_affiliate_id":"'.$lead->affiliate_id.'", "eiq_email":"'.$lead->lead_email.'", "rev_tracker":"CD7747", "first_name":"'.$faker->firstName().'", "last_name":"'.$faker->lastName().'", "zip":"90003", "gender":"M", "birth_date":"1985-07-29", "toluna-campaign":"YES"}';
            $data->save();
        }
    }
}
