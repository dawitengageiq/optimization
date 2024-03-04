<?php

namespace App\Console\Commands;

use App\LeadUser;
use Carbon\Carbon;
use Curl\Curl;
use Illuminate\Console\Command;

class ResendCVDFeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resend:cvd-feed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resend CVD Feed';

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
        $this->info('Executing.....');
        $users = LeadUser::where('status', 1)->where('response', 'like', '%| CVD:%')
            ->where('response', 'not like', '%| CVDR:%')->get();
        foreach ($users as $user) {
            $cvd_curl = new Curl();
            $fields = http_build_query([
                'pid' => 7,
                'sid' => 36,
                'cid' => 646,
                'ip' => $user->ip,
                'subid1' => 'CD'.$user->revenue_tracker_id,
                'subid2' => $user->revenue_tracker_id,
                'externalid' => 1,
                'First' => $user->first_name,
                'Last' => $user->last_name,
                'Email' => $user->email,
                'Address1' => $user->address1,
                'Address2' => $user->address2,
                'City' => $user->city,
                'State' => $user->state,
                'Zip' => $user->zip,
                'Phone' => $user->phone,
                'dob' => Carbon::parse($user->birthdate)->format('m/d/Y'),
            ]);
            $url = 'https://sbg.api.twyne.io/lead/submit?'.$fields;
            $cvd_curl->get($url);
            $cvd_curl->setHeader('content-type', 'application/json; charset=utf-8');
            $cvd_feed_response = ' | CVDR: '.$cvd_curl->response;
            $cvd_curl->close();
            $user->response .= $cvd_feed_response;
            $user->save();
        }
        $this->info('Resent Done.');
    }
}
