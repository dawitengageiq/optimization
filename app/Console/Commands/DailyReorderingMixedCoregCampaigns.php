<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DailyReorderingMixedCoregCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    //protected $signature = 'daily-reordering:mixed-coreg-campaigns {revenueTRrackerID}';
    protected $signature = 'daily-reordering:mixed-coreg-campaigns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mixed coreg campaigns reordering daily.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(\App\Jobs\Reordering\DailyMixCoreg $dailyOrdering)
    {
        $this->dailyOrdering = $dailyOrdering;
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // if($revenueTRrackerID = $this->argument('revenueTRrackerID')) {
        $this->info('Executing daily campaign reordering.');
        //
        //     $this->dailyOrdering->setRevenueTRrackerID($revenueTRrackerID);
        $this->dailyOrdering->execute();
        // }
    }
}
