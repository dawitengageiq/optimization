<?php

namespace App\Console\Commands;

use App\AffiliateRevenueTracker;
use Illuminate\Console\Command;

class UpdateRevenueTrackerSubIDBreakdownStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:rev-tracker-subid-breakdown-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Revenue Tracker Sub ID Breakdown Status';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $rev_trackers = AffiliateRevenueTracker::whereNotNull('new_subid_breakdown_status')->get();
        foreach ($rev_trackers as $rt) {
            $rt->subid_breakdown = $rt->new_subid_breakdown_status;
            $rt->new_subid_breakdown_status = null;

            $rt->sib_s1 = $rt->nsib_s1;
            $rt->nsib_s1 = null;

            $rt->sib_s2 = $rt->nsib_s2;
            $rt->nsib_s2 = null;

            $rt->sib_s3 = $rt->nsib_s3;
            $rt->nsib_s3 = null;

            $rt->sib_s4 = $rt->nsib_s4;
            $rt->nsib_s4 = null;
            $rt->save();
        }
    }
}
