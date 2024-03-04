<?php

namespace App\Jobs\Reports;

use App\HandPAffiliateReport;
use App\Jobs\Job;
use App\Lead;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Log;

class HandPReports extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $dateFromStr;

    protected $dateToStr;

    /**
     * Create a new job instance.
     */
    public function __construct($dateFromStr, $dateToStr)
    {
        $this->dateFromStr = $dateFromStr;
        $this->dateToStr = $dateToStr;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->attempts() > 1) {
            return;
        }

        $startLog = Carbon::now();
        Log::info(config('app.type'));
        Log::info('Generating Host and Post Affiliate Reports...');
        Log::info('Date: '.$this->dateFromStr.' - '.$this->dateToStr);
        //2 = internal type
        $params['type'] = 2;

        $dateFromStr = $this->dateFromStr;
        $dateToStr = $this->dateToStr;

        //get the leads revenue per affiliate
        $handpRevenues = Lead::select('leads.affiliate_id', 'leads.campaign_id', 's1', 's2', 's3', 's4', 's5', DB::raw('COUNT(leads.id) as lead_count'), DB::raw('SUM(leads.received) as total_received'), DB::raw('SUM(leads.payout) as total_payout'), 'leads.created_at')
            ->join('affiliates', 'leads.affiliate_id', '=', 'affiliates.id')
            ->where('leads.lead_status', '=', 1)
            ->where('leads.s5', '!=', 'EXTPATH')
            ->where('leads.s5', '!=', 'EIQPingTreeLead2605')
            ->where('affiliates.type', '=', 2)
            ->where(function ($query) use ($dateFromStr, $dateToStr) {
                $query->whereRaw('DATE(leads.created_at) >= DATE(?) and DATE(leads.created_at) <= DATE(?)',
                    [
                        $dateFromStr,
                        $dateToStr,
                    ]);
            })
            ->groupBy('leads.affiliate_id', 'leads.campaign_id', 'leads.s1', 'leads.s2', 'leads.s3', 'leads.s3', 'leads.s4', 'leads.s5', DB::raw('DATE(leads.created_at)'))->get();
        // Log::info($handpRevenues);
        foreach ($handpRevenues as $handpRevenue) {
            $handpAffiliateReport = HandPAffiliateReport::firstOrNew([
                'affiliate_id' => $handpRevenue->affiliate_id,
                'campaign_id' => $handpRevenue->campaign_id,
                's1' => $handpRevenue->s1,
                's2' => $handpRevenue->s2,
                's3' => $handpRevenue->s3,
                's4' => $handpRevenue->s4,
                's5' => $handpRevenue->s5,
                'created_at' => Carbon::parse($handpRevenue->created_at)->toDateString(),
            ]);

            $handpAffiliateReport->lead_count = $handpRevenue->lead_count;
            $handpAffiliateReport->received = $handpRevenue->total_received;
            $handpAffiliateReport->payout = $handpRevenue->total_payout;
            $handpAffiliateReport->save();

        }

        $endLog = Carbon::now();
        $diffInHours = $startLog->diffInHours($endLog).' hours';

        $emailNotificationRecipient = env('REPORTS_EMAIL_NOTIFICATION_RECIPIENT', 'marwilburton@hotmail.com');

        //send email to Burt to notify that Internal Iframe Affiliate Report Queue was successfully finished
        Mail::send('emails.affiliate_report_execution_email',
            ['startDate' => $this->dateFromStr, 'endDate' => $this->dateToStr, 'executionDuration' => $diffInHours],
            function ($m) use ($emailNotificationRecipient) {
                $m->from('ariel@engageiq.com', 'Ariel Magbanua');
                $m->to($emailNotificationRecipient, 'Marwil Burton')->subject('Hosted and Posted Affiliate Reports Job Queue Successfully Executed!');
            });
        Log::info('Generating Host and Post Affiliate Reports DONE');
    }
}
