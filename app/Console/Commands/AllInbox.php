<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AllInbox extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'all-inbox';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'all Inbox';

    /**
     * Create a new command instance.
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
        $this->info('Executing AllInbox CURL...');

        $job = (new \App\Jobs\AllInbox())->delay(3);
        dispatch($job);

        $this->info('AllInbox Job already pushed and will be executed soon!');
    }
}
