<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        App\Category::firstOrCreate([
            'name' => 'Regular',
            'status' => 1,
        ]);

        App\Category::firstOrCreate([
            'name' => 'Market Research',
            'status' => 1,
        ]);

        App\Category::firstOrCreate([
            'name' => 'Work At Home',
            'status' => 1,
        ]);

        App\Category::firstOrCreate([
            'name' => 'Sweepstakes',
            'status' => 1,
        ]);
    }
}
