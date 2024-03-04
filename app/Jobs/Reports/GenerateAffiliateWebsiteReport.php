<?php

namespace App\Jobs\Reports;

use App\AffiliateWebsite;
use App\Jobs\Job;
use App\LeadUser;
use App\WebsitesViewTracker;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class GenerateAffiliateWebsiteReport extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    public $date;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($date)
    {
        $this->date = $date;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Affiliate Website Report Job Queue Start:');
        Log::info('Date: '.$this->date);
        // DB::enableQueryLog();
        // DB::connection('secondary')->enableQueryLog();

        // $rows = WebsitesViewTracker::whereBetween('created_at', [$this->date.' 00:00:00', $this->date.' 23:59:59'])->groupBy(['website_id', 'affiliate_id', 'revenue_tracker_id', 's1', 's2', 's3', 's4', 's5'])->select('website_id', DB::RAW('DATE(created_at) as date'), DB::RAW('SUM(payout) as payout') , DB::RAW('count(*) as count'), 'affiliate_id', 'revenue_tracker_id', 's1', 's2', 's3', 's4', 's5')->get()->toArray();

        $affiliate_websites = AffiliateWebsite::where('revenue_tracker_id', '!=', '')->whereNotNull('revenue_tracker_id')->get();
        // Log::info($affiliate_websites);

        $revenue_tracker_ids = $affiliate_websites->map(function ($st) {
            return $st->revenue_tracker_id;
        });
        // Log::info($revenue_tracker_ids);

        $aws = [];
        foreach ($affiliate_websites as $aw) {
            $aws[$aw->revenue_tracker_id] = $aw;
        }

        $data = LeadUser::whereBetween('created_at', [$this->date.' 00:00:00', $this->date.' 23:59:59'])
            ->where('email', '!=', '')
            ->whereNotNull('email')
            ->whereIn('revenue_tracker_id', $revenue_tracker_ids)
            // ->groupBy([DB::RAW('date'),'affiliate_id', 'revenue_tracker_id', 's1', 's2', 's3', 's4', 's5'])
            // ->select(DB::RAW('DATE(created_at) as date'), DB::RAW('count(*) as count'), 'affiliate_id', 'revenue_tracker_id', 's1', 's2', 's3', 's4', 's5')
            // ->selectRaw('lead_users.*')
            ->get();

        $lead_users = [];
        $email_checker = [];
        foreach ($data as $d) {
            if (! isset($email_checker[$d->revenue_tracker_id])) {
                $email_checker[$d->revenue_tracker_id] = [];
            }

            if (! in_array($d->email, $email_checker[$d->revenue_tracker_id])) {
                $email_checker[$d->revenue_tracker_id][] = $d->email;
                if (! isset($lead_users[$d->affiliate_id][$d->revenue_tracker_id][$d->s1][$d->s2][$d->s3][$d->s4][$d->s5])) {
                    $lead_users[$d->affiliate_id][$d->revenue_tracker_id][$d->s1][$d->s2][$d->s3][$d->s4][$d->s5] = 0;
                }

                $lead_users[$d->affiliate_id][$d->revenue_tracker_id][$d->s1][$d->s2][$d->s3][$d->s4][$d->s5] += 1;
            }
        }
        // Log::info($lead_users);

        $rows = [];
        foreach ($lead_users as $affiliate_id => $afd) {
            foreach ($afd as $revenue_tracker_id => $rvd) {
                foreach ($rvd as $s1 => $s1d) {
                    foreach ($s1d as $s2 => $s2d) {
                        foreach ($s2d as $s3 => $s3d) {
                            foreach ($s3d as $s4 => $s4d) {
                                foreach ($s4d as $s5 => $count) {
                                    $website_id = $aws[$revenue_tracker_id]->id;

                                    $payout = $aws[$revenue_tracker_id]->payout;
                                    $totalPayout = $payout * $count;

                                    $rows[] = [
                                        'website_id' => $website_id,
                                        'affiliate_id' => $affiliate_id,
                                        'revenue_tracker_id' => $revenue_tracker_id,
                                        's1' => $s1,
                                        's2' => $s2,
                                        's3' => $s3,
                                        's4' => $s4,
                                        's5' => $s5,
                                        'date' => $this->date,
                                        'count' => $count,
                                        'payout' => $totalPayout,
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        // Log::info(DB::getQueryLog());
        // Log::info(DB::connection('secondary')->getQueryLog());

        // $rows = [];
        // foreach($data as $row) {
        //     $website_id = $aws[$row->revenue_tracker_id]->id;

        //     $payout = $aws[$row->revenue_tracker_id]->payout;
        //     $totalPayout = $payout * $row->count;

        //     $rows[] = [
        //         'website_id' => $website_id,
        //         'affiliate_id' => $row->affiliate_id,
        //         'revenue_tracker_id' => $row->revenue_tracker_id,
        //         's1' => $row->s1,
        //         's2' => $row->s2,
        //         's3' => $row->s3,
        //         's4' => $row->s4,
        //         's5' => $row->s5,
        //         'date' => $row->date,
        //         'count' => $row->count,
        //         'payout' => $totalPayout
        //     ];
        // }

        // Log::info($rows);

        if (count($rows) > 0) {
            $conn = config('app.type') != 'reports' ? 'secondary' : 'mysql';
            $clean = DB::connection($conn)->table('affiliate_website_reports')->where('date', $this->date);
            $clean->delete();

            $chunks = array_chunk($rows, 1000);
            foreach ($chunks as $chunk) {
                try {
                    DB::connection($conn)->table('affiliate_website_reports')->insert($chunk);
                } catch (QueryException $e) {
                    Log::info('db table stats error');
                    Log::info($e->getMessage());
                }
            }
        } else {
            Log::info('No views found.');
        }

        Log::info('Affiliate Website Report Job Queue Done.');
    }
}
