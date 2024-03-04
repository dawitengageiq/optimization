<?php

namespace Database\Seeders;

use App\AffiliateWebsite;
use Illuminate\Database\Seeder;

class AffiliateWebsitesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AffiliateWebsite::factory()->count(5)->create([
            'affiliate_id' => 7750,
        ]);
    }
}
