<?php

namespace App\Commands;

use App\Campaign;
use Illuminate\Support\Facades\Cache;

class GetCampaignListAndIDsPair extends Command
{
    protected $activeOnly;

    /**
     * Create a new command instance.
     */
    public function __construct($activeOnly = null)
    {
        $this->activeOnly = $activeOnly;
    }

    /**
     * Execute the command.
     */
    public function handle(): int
    {
        // \Log::info('Here');
        // \Log::info($this->status);
        // \DB::enableQueryLog();
        if ($this->activeOnly != '') {
            $activeOnly = $this->activeOnly;
            $campaignList = Cache::remember('activeCampaignList-', 300, function () {
                return Campaign::where('status', '!=', 0)->orderBy('name')->pluck('name', 'id')->toArray();
            });
        } else {
            $campaignList = Cache::remember('campaignList', 300, function () {
                return Campaign::orderBy('name')->pluck('name', 'id')->toArray();
            });
        }
        // \Log::info(\DB::getQueryLog());

        $pair[''] = 'ALL';

        foreach ($campaignList as $key => $campaign) {
            $pair[$key] = $campaign.'('.$key.')';
        }

        return $pair;
    }
}
