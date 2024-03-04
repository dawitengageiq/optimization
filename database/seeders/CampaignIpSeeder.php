<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CampaignIpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        App\CampaignIp::firstOrCreate([
            'campaign_id' => 1,
            'ip' => '192.168.1.8',
        ]);
    }
}
