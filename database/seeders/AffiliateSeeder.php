<?php

namespace Database\Seeders;

use App\Affiliate;
use Illuminate\Database\Seeder;

class AffiliateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Affiliate::factory()->count(10000)->create();
    }
}
