<?php

namespace Database\Seeders;

use App\ZipMaster;
use Curl\Curl;
use Illuminate\Database\Seeder;

class ZipMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $states = config('constants.US_STATES_ABBR');
        foreach ($states as $abbr => $full) {
            $us_states[] = $abbr;
        }
        // return $us_states;

        foreach ($us_states as $state) {
            $curl = new Curl();
            $curl->get('http://www.webservicex.net/uszip.asmx/GetInfoByState?USState='.$state);
            $myXMLData = $curl->response;
            $curl->close();
            $xml = simplexml_load_string($myXMLData);
            foreach ((array) $xml as $index => $node) {
                $out[$index] = (is_object($node)) ? xml2array($node) : $node;
            }
            $zips = $out['Table'];

            foreach ($zips as $zip) {
                ZipMaster::firstOrCreate([
                    'zip' => $zip->ZIP,
                    'city' => $zip->CITY,
                    'state' => $zip->STATE,
                    'area_code' => $zip->AREA_CODE,
                    'time_zone' => $zip->TIME_ZONE,
                ]);
            }
        }
    }
}
