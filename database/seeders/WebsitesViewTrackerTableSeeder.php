<?php

namespace Database\Seeders;

use App\WebsitesViewTracker;
use Illuminate\Database\Seeder;

class WebsitesViewTrackerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WebsitesViewTracker::factory()->count(150)->create();
    }
}
