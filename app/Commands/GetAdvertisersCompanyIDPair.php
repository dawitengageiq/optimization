<?php

namespace App\Commands;

use App\Advertiser;

class GetAdvertisersCompanyIDPair extends Command
{
    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the command.
     */
    public function handle(): int
    {
        //return Advertiser::orderBy('company')->lists('company','id');

        $advertisersList = [];
        $advertisers = Advertiser::select('id', 'company')->where('status', 1)->orderBy('company')->get();

        foreach ($advertisers as $advertiser) {
            $advertisersList[$advertiser->id] = $advertiser->company.' ('.$advertiser->id.')';
        }

        return $advertisersList;
    }
}
