<?php

namespace App\Console\Commands;

use App\Lead;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanQueuedLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:queued-leads {--date= : The start date of the query query for all the leads (YYYY-MM-DD).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean queued leads';

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
        $this->info('Initiating Queued Leads Cleanup');
        \Log::info('Initiating Queued Leads Cleanup');
        $date = $this->option('date');

        //NEW VERSION
        if (empty($date)) {
            $from = Carbon::yesterday()->startOfDay();
            $to = Carbon::yesterday()->endOfDay();
        } else {
            $from = Carbon::parse($date)->startOfDay();
            $to = Carbon::parse($date)->endOfDay();
        }
        $this->info('Date: '.$from.' to '.$to);
        \Log::info('Date: '.$from.' to '.$to);

        $from = Carbon::yesterday()->startOfDay();
        $to = Carbon::yesterday()->endOfDay();
        $total = Lead::where('lead_status', 4)->whereBetween('created_at', [$from, $to])->update(['lead_status' => 3]);
        $this->info('Total leads returned to pending: '.$total);
        \Log::info('Total leads returned to pending: '.$total);

        $this->info('Queued Leads Cleanup Done!');
        \Log::info('Queued Leads Cleanup Done!');
    }
}
