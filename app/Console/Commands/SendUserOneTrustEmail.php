<?php

namespace App\Console\Commands;

use App\Jobs\SendUserOneTrustEmailJob;
use Illuminate\Console\Command;

class SendUserOneTrustEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:user-one-trust-request-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends user the email for the request type they have.';

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
        $this->info('Sends user the email for the request type they have......');

        $job = (new SendUserOneTrustEmailJob());
        dispatch($job);
    }
}
