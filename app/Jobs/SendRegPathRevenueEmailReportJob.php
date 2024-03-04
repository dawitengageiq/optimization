<?php

namespace App\Jobs;

use App\Affiliate;
use App\AffiliateWebsite;
use App\AffiliateWebsiteReport;
use App\Setting;
use App\User;
use App\UserMeta;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Mail;

class SendRegPathRevenueEmailReportJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $from;

    protected $to;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Affilaite Reg Path Email Report');
        Log::info($this->from.' - '.$this->to);

        $websites = AffiliateWebsite::pluck('website_name', 'id')->toArray();
        $payouts = AffiliateWebsite::pluck('payout', 'id')->toArray();
        // DB::enableQueryLog();
        // DB::connection('secondary')->enableQueryLog();
        // $user_ids = UserMeta::where('key', 'reg_path_revenue_email_report')->where('value',1)->get();
        // $uids = $user_ids->map(function($st) {
        //     return $st->user_id;
        // });
        // Log::info($user_ids);
        // Log::info($uids);
        // $users = User::whereIn('id', $uids)->get();
        // Log::info($users);
        // $affiliates = [];
        // $affiliate_ids = [];
        // foreach($users as $user) {
        //     $affiliate_ids = $user->affiliate_id;
        //     $affiliates[$user->affiliate_id] = $user;
        // }
        // Log::info($affiliates);

        $user_ids = UserMeta::where('key', 'reg_path_revenue_email_report')->where('value', 1)->with('user')->get();
        // Log::info($user_ids);
        $affiliate_ids = $user_ids->map(function ($st) {
            return $st->user->affiliate_id;
        });
        $affiliates = [];
        foreach ($user_ids as $user) {
            $affiliates[$user->user->affiliate_id] = $user->user;
        }

        $coworker_emails = [];
        $cws = UserMeta::where('key', 'coworker_email')->with('user')->get();
        foreach ($cws as $cw) {
            $coworker_emails[$cw->user->affiliate_id] = explode(';', $cw->value);
        }

        // Log::info($affiliates);
        // Log::info(DB::getQueryLog());
        // Log::info(DB::connection('secondary')->getQueryLog());
        // Log::info($user_ids);
        // Log::info($affiliate_ids);

        $params = [
            'start_date' => $this->from,
            'end_date' => $this->to,
            'order_col' => 'date',
            'order_dir' => 'asc',
            'affiliate_ids' => $affiliate_ids,
        ];

        // DB::connection('secondary')->enableQueryLog();
        $items = AffiliateWebsiteReport::externalPathRevenue($params)
            ->select(DB::RAW('date, SUM(payout) as payout, SUM(count) as count, website_id, affiliate_id'))
            ->groupBy('affiliate_id')->groupBy('date')->groupBy('website_id')->get();
        // Log::info(DB::connection('secondary')->getQueryLog());
        // Log::info($items);

        $rows = [];
        $aff_ids = [];
        foreach ($items as $i) {
            if (! in_array($i->affiliate_id, $aff_ids)) {
                $aff_ids[] = $i->affiliate_id;
            }
            $rows[$i->affiliate_id][$i->website_id][$i->date] = $i;
        }
        // Log::info($rows);

        $ss = Setting::where('code', 'js_midpath_email_report')->first();
        $cc_emails = explode(';', $ss->description);

        $affiliates_names = Affiliate::whereIn('id', $aff_ids)->pluck('company', 'id')->toArray();
        // Log::info($affiliates_names);
        $date = Carbon::yesterday()->format('m/d/Y');
        $display_date = Carbon::parse($this->from)->format('F Y');
        foreach ($rows as $affiliate_id => $row) {
            $the_affiliate = $affiliates[$affiliate_id];
            $the_coworker = isset($coworker_emails[$affiliate_id]) ? $coworker_emails[$affiliate_id] : [];

            // Log::info($the_affiliate);
            Mail::send('emails.affiliate_reg_path_revenue',
                ['from' => $this->from, 'to' => $this->to, 'items' => $row, 'websites' => $websites, 'payouts' => $payouts, 'date' => $display_date],
                function ($m) use ($affiliate_id, $the_affiliate, $cc_emails, $affiliates_names, $date, $the_coworker) {

                    foreach ($cc_emails as $cc) {
                        $m->cc($cc);
                    }

                    if (count($the_coworker) > 0) {
                        foreach ($the_coworker as $cw) {
                            $m->cc($cw);
                        }
                    }

                    $affiliate_name = isset($affiliates_names[$affiliate_id]) ? $affiliates_names[$affiliate_id] : '';
                    $title = $affiliate_id.' - '.$affiliate_name.' Js Midpath Revenue Report '.$date;

                    $m->to($the_affiliate->email, $the_affiliate->first_name.' '.$the_affiliate->last_name)
                        ->subject($title);
                });
        }
    }
}
