<?php

namespace Database\Seeders;

use App\AffiliateRevenueTracker;
use Illuminate\Database\Seeder;

class UpdateRevenueTrackerSubIDBreakdownReportStatus extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rev_trackers = AffiliateRevenueTracker::where('subid_breakdown', '=', 1)
            ->update(['report_subid_breakdown_status' => 1]);
    }
}
