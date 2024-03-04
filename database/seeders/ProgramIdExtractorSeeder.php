<?php

namespace Database\Seeders;

use App\Campaign;
use Illuminate\Database\Seeder;

class ProgramIdExtractorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campaigns = Campaign::whereIn('campaign_type', [1, 2, 3, 7, 8, 9, 10, 11, 12])->pluck('name', 'id');

        foreach ($campaigns as $id => $name) {
            // echo $id.' - '.$name.'<br>';
            preg_match('#\((.*?)\)#', $name, $match);
            if ($match) {
                $prg_id = $match[1];
                if (is_numeric($prg_id) && $prg_id > 0) {
                    $campaign = Campaign::find($id);
                    $campaign->olr_program_id = $prg_id;
                    $campaign->save();
                }
            }
            // print_r($match);
            // echo '<br>';
        }
    }
}
