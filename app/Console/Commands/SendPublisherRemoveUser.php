<?php

namespace App\Console\Commands;

use App\Jobs\SendPublisherRemoveUserJob;
use Illuminate\Console\Command;

class SendPublisherRemoveUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:publisher-remove-user {--state= : comma separate the states.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send publisher to remove user';

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
        $this->info('Send publisher remove users.....');

        $state = $this->option('state');

        $job = (new SendPublisherRemoveUserJob($state));
        dispatch($job);

        $this->info('Process results will be available soon!');
    }
}
