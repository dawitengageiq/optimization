<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ArchiveCakeConversions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'archive:cake-conversions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will archive cake conversions.';

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
        $this->info('Initiating archiving of cake conversions...');

        $job = (new \App\Jobs\ArchiveCakeConversions())->delay(3);
        dispatch($job);

        $this->info('Cake conversions will be archive shortly!');
    }
}
