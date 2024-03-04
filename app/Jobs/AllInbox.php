<?php

namespace App\Jobs;

use Curl\Curl;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Log;

class AllInbox extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $status;

    /**
     * Create a new job instance.
     */
    public function __construct($status = 0)
    {
        $this->status = $status;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->attempts() > 1) {
            return;
        }

        $curl = new Curl();
        //$curl->get('http://leadreactor.app/cron/all_inbox'); //development only
        //$curl->get('http://testleadreactor.engageiq.com/cron/all_inbox');
        $curl->get(config('constants.APP_BASE_URL').'/cron/all_inbox/'.$this->status);
        //$curl->get('http://leadreactor.engageiq.com/cron/all_inbox');

        // Log::info("AllInbox response: $curl->response");

        $emailNotificationRecipient = env('REPORTS_EMAIL_NOTIFICATION_RECIPIENT', 'marwilburton@hotmail.com');

        Mail::send('emails.all_inbox', [], function ($m) use ($emailNotificationRecipient) {
            $m->from('ariel@engageiq.com', 'Ariel Magbanua');
            $m->to($emailNotificationRecipient, 'Marwil Burton');
            // $m->cc('ariel@engageiq.com', 'Ariel Magbanua');
            $m->subject('All Inbox Job Queue Successfully Executed!');
        });
    }
}
