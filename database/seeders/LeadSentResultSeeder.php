<?php

namespace Database\Seeders;

use App\Lead;
use App\LeadSentResult;
use Illuminate\Database\Seeder;

class LeadSentResultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // use the factory to create a Faker\Generator instance
        $faker = Faker\Factory::create();

        // $leadIDs = Lead::pluck('id')->toArray();
        // $leadIDs = Lead::where('lead_status',2)->where(DB::RAW('date(created_at)'),'2016-06-10')->pluck('id')->toArray();
        // $results = ['Successful','failed','not found'];

        $leads = Lead::leftJoin('lead_sent_results', 'leads.id', '=', 'lead_sent_results.id')->whereNull('lead_sent_results.value')->pluck('leads.lead_status', 'leads.id');
        $rejected_results = ['duplicate', 'no blank found', 'already found', 'existed', 'invalid value', 'does not have value', 'error'];
        // $this->command->info('Creating sample leads...');
        foreach ($leads as $id => $status) {
            $data = LeadSentResult::firstOrNew(['id' => $id]);
            if ($status == 1) {
                $data->value = 'Success';
            } elseif ($status == 2) {
                $data->value = $faker->randomElement($rejected_results);
            }
            $data->save();
        }
    }
}
