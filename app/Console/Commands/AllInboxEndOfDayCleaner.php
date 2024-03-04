<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AllInboxEndOfDayCleaner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'all-inbox-cleaner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ALL INBOX Status QUEUE Cleaner';

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
        $this->info('Executing AllInbox CLEANER CURL...');

        $status = 2;
        $job = (new \App\Jobs\AllInbox($status))->delay(3);
        dispatch($job);

        $this->info('AllInbox CLEANER Job already pushed and will be executed soon!');
    }
}
