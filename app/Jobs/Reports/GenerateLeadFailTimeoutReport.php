<?php

namespace App\Jobs\Reports;

use App\Helpers\Repositories\Settings;
use App\Jobs\Job;
use App\Setting;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class GenerateLeadFailTimeoutReport extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(Settings $settings): void
    {
        if ($this->attempts() > 1) {
            return;
        }

        $dateNow = Carbon::now()->toDateTimeString();

        $campaigns = DB::table('campaigns')->select(
            'campaigns.id',
            'campaigns.name',
            DB::raw("(SELECT count(lds.id) FROM leads lds WHERE DATE(lds.created_at) = DATE('$dateNow') AND lds.lead_status=0 AND lds.campaign_id = campaigns.id GROUP BY lds.campaign_id) AS fail_count"),
            DB::raw("(SELECT count(lds.id) FROM leads lds WHERE DATE(lds.created_at) = DATE('$dateNow') AND lds.lead_status=6 AND lds.campaign_id = campaigns.id GROUP BY lds.campaign_id) AS timeout_count"))
            ->join('leads', 'leads.campaign_id', '=', 'campaigns.id')
            ->havingRaw('(fail_count IS NOT NULL OR timeout_count IS NOT NULL) OR (fail_count!=0 OR timeout_count!=0)')
            ->distinct()
            ->get();
        //provide the recipients especially the admin email
        $defaultAdminEmail = $settings->getValue('default_admin_email');

        //Get Recipients
        $qa_recipients = [];
        $setting = Setting::where('code', 'qa_recipient')->first();
        $rec_emails = explode(';', $setting->description);
        foreach ($rec_emails as $re) {
            $re = trim($re);
            if (filter_var($re, FILTER_VALIDATE_EMAIL)) {
                $qa_recipients[] = $re;
            }
        }

        $emailNotificationRecipient = env('REPORTS_EMAIL_NOTIFICATION_RECIPIENT', 'marwilburton@hotmail.com');

        //$recipients=[$defaultAdminEmail => 'EngageIQ Admin'];
        $recipients = [$defaultAdminEmail, $emailNotificationRecipient];

        // $ccs=[
        //     'ariel@engageiq.com',
        //     'karla@engageiq.com'
        // ];

        Mail::send('emails.fail_timeout', ['campaigns' => $campaigns, 'dateNow' => $dateNow], function ($message) use ($recipients, $qa_recipients) {
            $message->from('ariel@engageiq.com', 'Ariel Magbanua');

            /*
            foreach($recipients as $email => $name)
            {
                $message->to($email,$name);
            }
            */
            $message->to($recipients);
            $message->cc($qa_recipients);

            $message->subject('Current FAIL and TIMEOUT LEADS of the day');
        });
    }
}
