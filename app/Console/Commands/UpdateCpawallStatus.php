<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateCpawallStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:cpawall-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Link out campaign status based on cake status';

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
        $this->info('Updated CPAWALL STATUS starting...');
        $job = (new \App\Jobs\UpdateCpawallStatusJob());
        dispatch($job);
    }
}
