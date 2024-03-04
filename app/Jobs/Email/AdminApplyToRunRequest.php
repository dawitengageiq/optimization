<?php

namespace App\Jobs\Email;

use App\Jobs\Job;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AdminApplyToRunRequest extends Job implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $email_data)
    {
        $this->email_data = $email_data;
    }

    /**
     * Execute the job.
     */
    public function handle(Mailer $mailer): void
    {
        if ($this->attempts() > 1) {
            return;
        }

        extract($this->email_data);

        $mailer->send('emails.admin_apply_to_run_request',
            ['admin' => $admin, 'campaign' => $campaign, 'affiliate' => $affiliate],
            function ($mail) use ($admin) {

                $mail->from('admin@engageiq.com', 'Engage IQ: Lead Reactor');

                $mail->to($admin->email, $admin->first_name.' '.$admin->last_name)->subject('Lead Reactor: Affiliate Apply to Run Request');
            });

        echo 'Dispatching email: admin apply to run request.';
    }

    /**
     * Handle a job failure.
     */
    public function failed(): void
    {
        // Called when the job is failing...
    }
}
