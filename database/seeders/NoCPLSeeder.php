<?php

namespace Database\Seeders;

use App\NoteCategory;
use Illuminate\Database\Seeder;

class NoCPLSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        NoteCategory::firstOrCreate([
            'name' => 'CPL',
        ]);
    }
}
