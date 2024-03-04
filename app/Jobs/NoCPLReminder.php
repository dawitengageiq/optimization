<?php

namespace App\Jobs;

use App\Campaign;
use App\CplChecker;
use App\Note;
use App\NoteCategory;
use App\Setting;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Log;

class NoCPLReminder extends Job implements ShouldQueue
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
        Log::info('NO CPL Reminder....');

        $updated_campaigns = [];
        $new_campaigns = [];

        $date = Carbon::now()->toDateString();
        $sixty_days = Carbon::now()->subDay(60)->toDateString();
        //Get Existing Campaigns with no CPL
        $existing_campaigns = CplChecker::pluck('campaign_id')->toArray();

        //Get Campaigns with no CPL
        $campaigns = Campaign::where(function ($q) {
            $q->where('default_payout', '=', 0)->orWhere('default_received', '=', 0);
        })->where('status', '!=', 0)->select('id', 'status', 'campaign_type')->get();
        // ->where('status', '!=', 0)->whereNotIn('campaign_type', [4,5,6])->pluck('id')->toArray();

        //Get Excluded Campaign Ids
        $setting = Setting::where('code', 'cplchecker_excluded_campaigns')->first();
        $excluded_campaigns = explode(',', $setting->description);
        $excluded_campaigns = array_map('trim', $excluded_campaigns);

        $campaign_ids = [];
        foreach ($campaigns as $c) {
            $campaign_ids[] = $c->id;
        }

        //Check if Existing Campaigns has CPL already or excluded campaign
        foreach ($existing_campaigns as $cid) {
            if (! in_array($cid, $campaign_ids)) {
                $updated_campaigns[] = $cid;
            }

            if (in_array($cid, $excluded_campaigns)) {
                $updated_campaigns[] = $cid;
            }
        }
        //delete campaigns with cpl
        CplChecker::whereIn('campaign_id', $updated_campaigns)->delete();

        //Go through new campaigns with no cpl
        foreach ($campaigns as $campaign) {
            if ($campaign->status != 0 && ! in_array($campaign->campaign_type, [4, 5, 6]) && ! in_array($campaign->id, $excluded_campaigns)) {
                if (! in_array($campaign->id, $existing_campaigns)) {
                    $new_campaigns[] = [
                        'campaign_id' => $campaign->id,
                        'created_at' => $date,
                    ];
                }
            }
        }
        //Save new campaigns
        if (count($new_campaigns) > 0) {
            CplChecker::insert($new_campaigns);
        }

        //Get No CPL 60 days old
        $alert = CplChecker::leftJoin('campaigns', 'campaigns.id', '=', 'cpl_checkers.campaign_id')
            ->where('cpl_checkers.created_at', '<=', $sixty_days)->pluck('campaigns.name', 'campaign_id');

        if (count($alert) > 0) {
            $setting = Setting::where('code', 'nocpl_recipient')->first();
            $recipients = explode(';', $setting->description);
            $cpl_category = NoteCategory::where('name', 'CPL')->first();
            $cpl_category_id = $cpl_category->id;
            $notes = [];
            $timestamp = Carbon::now();
            foreach ($alert as $id => $name) {
                $notes[] = [
                    'category_id' => $cpl_category_id,
                    'subject' => 'No CPL Reminder: '.$name,
                    'content' => '<p>This message was automatically generated.</p>',
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];

                Mail::send('emails.campaign_no_cpl_reminder',
                    ['campaign' => $name, 'url' => env('APP_BASE_URL', 'https://leadreactor.engageiq.com').'/admin/campaigns'],
                    function ($m) use ($name, $recipients) {
                        foreach ($recipients as $recipient) {
                            $m->to($recipient);
                        }
                        $m->subject('No CPL Reminder:'.$name);
                    });
            }

            Note::insert($notes);
        }
    }
}
