<?php

namespace App\Jobs;

use App\LeadUser;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SaveLeadUser extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->attempts() > 1) {
            return;
        }

        $user = $this->user;

        $isBot = false;

        if (isset($user['email2'])) {
            if ($user['email2'] != '') {
                $isBot = true;
            }
        }

        // $user_details['s1']

        $lead_user = new LeadUser;
        $lead_user->first_name = $user['first_name'];
        $lead_user->last_name = $user['last_name'];
        $lead_user->email = $user['email'];
        $lead_user->birthdate = Carbon::createFromDate($user['dobyear'], $user['dobmonth'], $user['dobday'])->format('Y-m-d');
        $lead_user->gender = $user['gender'];
        $lead_user->zip = $user['zip'];
        $lead_user->city = $user['city'];
        $lead_user->state = $user['state'];
        $lead_user->ethnicity = 0;
        $lead_user->address1 = $user['address'];
        $lead_user->phone = $user['phone1'].$user['phone2'].$user['phone3'];
        $lead_user->source_url = $user['source_url'];
        $lead_user->affiliate_id = $user['affiliate_id'];
        $lead_user->revenue_tracker_id = $user['revenue_tracker_id'];
        $lead_user->ip = $user['ip'];
        $lead_user->is_mobile = $user['screen_view'] == 1 ? false : true;
        $lead_user->status = $isBot ? 3 : 0;
        $lead_user->s1 = $user['s1'];
        $lead_user->s2 = $user['s2'];
        $lead_user->s3 = $user['s3'];
        $lead_user->s4 = $user['s4'];
        $lead_user->s5 = $user['s5'];
        $lead_user->save();

        //echo 'USER SAVED IN DB.';
    }
}
