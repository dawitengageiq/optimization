<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FilterTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        App\FilterType::firstOrCreate([
            'type' => 'profile',
            'name' => 'email',
        ]);

        App\FilterType::firstOrCreate([
            'type' => 'profile',
            'name' => 'age',
        ]);

        App\FilterType::firstOrCreate([
            'type' => 'profile',
            'name' => 'gender',
        ]);

        App\FilterType::firstOrCreate([
            'type' => 'profile',
            'name' => 'state',
        ]);

        App\FilterType::firstOrCreate([
            'type' => 'profile',
            'name' => 'zip',
        ]);

        App\FilterType::firstOrCreate([
            'type' => 'question',
            'name' => 'Do you have diabetes?',
        ]);

        App\FilterType::firstOrCreate([
            'type' => 'question',
            'name' => 'Do you smoke?',
        ]);

        App\FilterType::firstOrCreate([
            'type' => 'custom',
            'name' => 'show_date',
        ]);

        App\FilterType::firstOrCreate([
            'type' => 'custom',
            'name' => 'show_time',
        ]);

        App\FilterType::firstOrCreate([
            'type' => 'custom',
            'name' => 'tablet_view',
        ]);

        App\FilterType::firstOrCreate([
            'type' => 'custom',
            'name' => 'mobile_view',
        ]);

        App\FilterType::firstOrCreate([
            'type' => 'custom',
            'name' => 'desktop_view',
        ]);

        App\FilterType::firstOrCreate([
            'type' => 'custom',
            'name' => 'check_ping',
        ]);
    }
}
