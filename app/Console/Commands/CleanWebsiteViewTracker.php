<?php

namespace App\Console\Commands;

use App\WebsitesViewTracker;
use App\WebsitesViewTrackerDuplicate;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanWebsiteViewTracker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:website-view-tracker
    {--date= : The start date of the query query for all the leads (YYYY-MM-DD).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean website view tracker and duplicates table.';

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
        $this->info('Initiating website view cleanup');
        \Log::info('Initiating website view cleanup');
        $date = $this->option('date');

        //NEW VERSION
        if (empty($date)) {
            $date = Carbon::now()->subDay(7)->toDateString();
        } else {
            $date = Carbon::parse($date)->toDateString();
        }
        $this->info('Removing data older than '.$date);

        $views = WebsitesViewTracker::where('created_at', '<=', $date.' 23:59:59')->delete();
        $views = WebsitesViewTrackerDuplicate::where('created_at', '<=', $date.' 23:59:59')->delete();

        $this->info('Website view Cleanup Done!');
        \Log::info('Website view Cleanup Done!');
    }
}
