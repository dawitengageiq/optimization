<?php

namespace App\Console\Commands;

use App\Jobs\Reports\GeneratePrepopStatisticsV2;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GeneratePrePopStatisticsVer2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:prepop-statistics-v2
                            {--date= : (YYYY-MM-DD).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will call cake APIs for clicks for prepop statistics';

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
        $this->info('Initiating generating of prepop statistics...');

        $date = $this->option('date');

        if (empty($date)) {
            $date = Carbon::now()->subDay()->toDateString();
        }

        $this->info("$date");

        $job = (new GeneratePrepopStatisticsV2($date))->delay(3);
        dispatch($job);

        $this->info('Generate Prepop Statistics are being processed, results will be available soon!');
    }
}
