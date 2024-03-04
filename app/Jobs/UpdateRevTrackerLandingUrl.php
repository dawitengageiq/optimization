<?php

namespace App\Jobs;

use App\AffiliateRevenueTracker;
use App\Setting;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Log;

class UpdateRevTrackerLandingUrl extends Job implements ShouldQueue
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
        if ($this->attempts() > 1) {
            return;
        }

        // Log::info('Update Landing URL');
        $revenue_trackers = AffiliateRevenueTracker::pluck('tracking_link', 'id');
        foreach ($revenue_trackers as $id => $tracking_link) {
            $landing_url = '';
            if ($tracking_link != '') {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $tracking_link);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $a = curl_exec($ch);
                $landing_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                // Log::info($landing_url);
                $rt = AffiliateRevenueTracker::find($id);
                $rt->landing_url = $landing_url;
                $rt->save();
            }
        }

        $settings = Setting::where('code', 'default_admin_email')->first();
        $email = $settings->string_value != '' ? $settings->string_value : 'burt@engageiq.com';
        // Log::info($email);

        //send email to Burt to notify that Affiliate Report Queue was successfully finished
        Mail::send('emails.update_rev_tracker_landing_url',
            ['now' => Carbon::now()],
            function ($m) use ($email) {
                $m->from('ariel@engageiq.com', 'Ariel Magbanua');
                $m->to($email)->subject('Update Revenue Tracker Landing URL Job Queue Successfully Executed!');
            });
        // Log::info('Update Landing URL DONE!');
    }
}
