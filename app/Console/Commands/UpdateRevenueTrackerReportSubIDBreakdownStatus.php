<?php

namespace App\Console\Commands;

use App\AffiliateRevenueTracker;
use DB;
use Illuminate\Console\Command;

class UpdateRevenueTrackerReportSubIDBreakdownStatus extends Command
{
    protected $logger;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:rev-tracker-report-subid-breakdown-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Revenue Tracker Report Sub ID Breakdown Status';

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
        $rev_trackers = AffiliateRevenueTracker::where('subid_breakdown', '!=', DB::RAW('report_subid_breakdown_status'))->orWhere('rsib_s1', '!=', DB::RAW('sib_s1'))->orWhere('rsib_s2', '!=', DB::RAW('sib_s2'))
            ->orWhere('rsib_s3', '!=', DB::RAW('sib_s3'))->orWhere('rsib_s4', '!=', DB::RAW('sib_s4'))->get();
        foreach ($rev_trackers as $rt) {
            $rt->report_subid_breakdown_status = $rt->subid_breakdown;
            $rt->rsib_s1 = $rt->sib_s1;
            $rt->rsib_s2 = $rt->sib_s2;
            $rt->rsib_s3 = $rt->sib_s3;
            $rt->rsib_s4 = $rt->sib_s4;
            $rt->save();
        }
    }
}
