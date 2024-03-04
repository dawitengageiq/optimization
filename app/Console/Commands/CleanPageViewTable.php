<?php

namespace App\Console\Commands;

use App\PageView;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanPageViewTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:page-views
    {--date= : The start date of the query query for all the leads (YYYY-MM-DD).}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean page views table';

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
        $this->info('Initiating Page Views cleanup');
        \Log::info('Initiating Page Views cleanup');
        $date = $this->option('date');

        //NEW VERSION
        if (empty($date)) {
            $date = Carbon::now()->subDay(3)->toDateString();
        } else {
            $date = Carbon::parse($date)->toDateString();
        }
        $this->info($date);

        $views = PageView::where('created_at', '<=', $date)->delete();
        $this->info('Page Views Cleanup Done!');
        \Log::info('Page Views Cleanup Done!');
    }
}
