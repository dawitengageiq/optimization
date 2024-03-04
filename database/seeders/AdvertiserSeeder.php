<?php

namespace Database\Seeders;

use App\Advertiser;
use Illuminate\Database\Seeder;

class AdvertiserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Advertiser::factory()->count(20)->create();
    }
}
