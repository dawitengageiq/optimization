<?php

namespace Database\Seeders;

use App\ZipCode;
use Illuminate\Database\Seeder;

class ZipCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $file_path = storage_path('app/zip_codes.xls');
        $excel = Excel::load($file_path, function ($reader) {
        })->all();
        $zips = $excel[0];
        foreach ($zips as $zip) {
            $code = sprintf('%05s', $zip['zip']);
            ZipCode::firstOrCreate(['zip' => $code, 'city' => $zip['city'], 'state' => $zip['state']]);
        }
    }
}
