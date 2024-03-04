<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Log;

class CleanUpDashboardStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:dashboard-stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean Up 61 day old data';

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
        $date = Carbon::yesterday()->subDay(60)->toDateString();
        //DB::enableQueryLog();
        $offer_con = config('app.type') == 'reports' ? 'secondary' : '';
        DB::connection($offer_con)->table('offer_goes_downs')->where('date', '<=', $date)->delete();

        $path_speed_con = config('app.type') != 'reports' ? 'secondary' : '';
        DB::connection($path_speed_con)->table('path_speeds')->where('created_at', '<=', $date)->delete();

        $path_speed_con = config('app.type') != 'reports' ? 'secondary' : '';
        DB::connection($path_speed_con)->table('campaign_revenue_breakdowns')->where('created_at', '<=', $date)->delete();
        //Log::info(DB::getQueryLog());
    }
}
