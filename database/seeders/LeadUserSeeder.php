<?php

namespace Database\Seeders;

use App\LeadUser;
use Illuminate\Database\Seeder;

class LeadUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LeadUser::factory()->count(50)->create();
    }
}
