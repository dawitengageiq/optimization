<?php

namespace App\Console\Commands;

use App\Jobs\DeleteOptOutUsersJob;
use Illuminate\Console\Command;

class DeleteOptOutUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:opt-out-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete opt out users';

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
        $this->info('Deleting Opt out users.....');

        $job = (new DeleteOptOutUsersJob());
        dispatch($job);

        $this->info('Deleting opt out users is being processed, results will be available soon!');
    }
}
