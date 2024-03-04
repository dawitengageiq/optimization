<?php

namespace App\Commands;

use App\Affiliate;
use DB;

class GetInternalAffiliatesCompanyIDPair extends Command
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
        $affiliates = Affiliate::select('id', DB::raw('CONCAT(company," (",id,") ") AS affiliate_name'))->where('status', 1)->where('type', 1)->orderBy('company')->pluck('affiliate_name', 'id')->toArray();

        return $affiliates;
    }
}
