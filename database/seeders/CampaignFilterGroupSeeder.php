<?php

namespace Database\Seeders;

use App\CampaignFilter;
use App\CampaignFilterGroup;
use App\CampaignFilterGroupFilter;
use Illuminate\Database\Seeder;

class CampaignFilterGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campaigns = CampaignFilter::groupBy('campaign_id')->pluck('campaign_id');

        foreach ($campaigns as $campaign) {
            $group = CampaignFilterGroup::firstOrNew([
                'campaign_id' => $campaign,
            ]);

            $group->name = 'First Filter';
            $group->description = 'Please Edit filter group name.';
            $group->status = 1;
            $group->save();

            $campaign_filters = CampaignFilter::where('campaign_id', $campaign)->get();
            foreach ($campaign_filters as $filter) {
                CampaignFilterGroupFilter::firstOrCreate([
                    'campaign_filter_group_id' => $group->id,
                    'filter_type_id' => $filter->filter_type_id,
                    'value_text' => $filter->value_text,
                    'value_min_integer' => $filter->value_min_integer,
                    'value_max_integer' => $filter->value_max_integer,
                    'value_min_date' => $filter->value_min_date,
                    'value_max_date' => $filter->value_max_date,
                    'value_min_time' => $filter->value_min_time,
                    'value_max_time' => $filter->value_max_time,
                    'value_boolean' => $filter->value_boolean,
                    'value_array' => $filter->value_array,
                ]);
            }
        }
    }
}
