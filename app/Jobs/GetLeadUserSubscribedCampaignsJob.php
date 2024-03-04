<?php

namespace App\Jobs;

use App\LeadUserRequest;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class GetLeadUserSubscribedCampaignsJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $connection = config('app.type') == 'reports' ? 'secondary' : 'mysql';
        // DB::connection($connection)->enableQueryLog();

        Log::info('Get Subscribed Campaigns for OptOut');
        //$start_date = '2018-01-01 00:00:00';
        $start_date = Carbon::now()->subYear(1)->startOfYear();
        $end_date = Carbon::now()->endOfDay();
        $users = LeadUserRequest::whereNull('subscribed_campaigns')->where('request_type', 'like', '%Delet%')->distinct('email')->pluck('email')->toArray();

        if (count($users) == 0) {
            Log::info('No new opt out users');

            return;
        }

        // \Log::info($users);
        $user_emails = array_map(function ($data) {
            return "'".$data."'";
        }, $users);
        $email_str = implode(',', $user_emails);

        $get_campaigns = DB::connection($connection)->select("SELECT lead_email, GROUP_CONCAT(DISTINCT campaign_id ORDER BY campaign_id ASC SEPARATOR ',') as campaigns FROM (
        SELECT lead_email, campaign_id FROM leads_archive 
        WHERE lead_email IN ($email_str) 
        AND lead_status = 1 AND created_at BETWEEN '$start_date' AND '$end_date'
        UNION 
        SELECT lead_email, campaign_id FROM leads WHERE lead_email IN ($email_str) 
        AND lead_status = 1) as a GROUP BY lead_email");

        $campaigns = [];
        foreach ($get_campaigns as $c) {
            $campaigns[$c->lead_email] = $c->campaigns;
        }

        $no_campaigns = [];
        foreach ($users as $user) {
            if (! array_key_exists($user, $campaigns)) {
                $no_campaigns[] = $user;
            } else {
                LeadUserRequest::where('email', $user)->whereNull('subscribed_campaigns')->update(['subscribed_campaigns' => $campaigns[$user]]);
                // $req = LeadUserRequest::where('email', $user)->first();
                // $req->subscribed_campaigns = $campaigns[$user];
                // $req->save();
            }
        }

        if (count($no_campaigns) > 0) {
            LeadUserRequest::whereIn('email', $no_campaigns)->update([
                'subscribed_campaigns' => 0,
            ]);
        }

        // Log::info(DB::connection($connection)->getQueryLog());
        Log::info('Subscribed Campaigns done');
    }
}
