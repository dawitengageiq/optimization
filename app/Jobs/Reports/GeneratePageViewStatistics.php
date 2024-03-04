<?php

namespace App\Jobs\Reports;

use App\Jobs\Job;
use App\PageView;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class GeneratePageViewStatistics extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    public $date;

    /**
     * Create a new job instance.
     *
     * @param $dateFromStr
     * @param $dateToStr
     */
    public function __construct($date)
    {
        $this->date = $date;
    }

    public function handle(): void
    {
        Log::info('Page Views Statistics Job Queue Start:');

        $list_types = array_values(config('constants.PAGE_VIEW_TABLE_COLUMN_MAPPING'));
        //DB::enableQueryLog();
        $views = PageView::where('created_at', $this->date)->groupBy('affiliate_id', 'revenue_tracker_id', 's1', 's2', 's3', 's4', 's5', 'type', 'created_at')->select('affiliate_id', 'revenue_tracker_id', 's1', 's2', 's3', 's4', 's5', 'type', 'created_at', DB::RAW('count(*) as total'))->get();
        //Log::info(DB::getQueryLog());
        //Log::info($views);

        $statistics = [];
        foreach ($views as $v) {
            $statistics[$v->affiliate_id][$v->revenue_tracker_id][$v->s1][$v->s2][$v->s3][$v->s4][$v->s5][$v->type] = $v->total;
        }

        $rows = [];
        foreach ($statistics as $affiliate_id => $statistic) {
            foreach ($statistic as $revenue_tracker_id => $statisti) {
                foreach ($statisti as $s1 => $statist) {
                    foreach ($statist as $s2 => $statis) {
                        foreach ($statis as $s3 => $stati) {
                            foreach ($stati as $s4 => $stat) {
                                foreach ($stat as $s5 => $s) {
                                    $row = [];
                                    $row['affiliate_id'] = $affiliate_id;
                                    $row['revenue_tracker_id'] = $revenue_tracker_id;
                                    $row['s1'] = $s1;
                                    $row['s2'] = $s2;
                                    $row['s3'] = $s3;
                                    $row['s4'] = $s4;
                                    $row['s5'] = $s5;
                                    $row['created_at'] = $this->date;

                                    $s = array_change_key_case($s);
                                    foreach ($list_types as $type) {
                                        $row[$type] = isset($s[$type]) ? $s[$type] : 0;
                                    }
                                    $rows[] = $row;
                                }
                            }
                        }
                    }
                }
            }
        }

        // Log::info($rows);

        if (count($rows) > 0) {
            $conn = config('app.type') != 'reports' ? 'secondary' : 'mysql';
            $clean = DB::connection($conn)->table('page_view_statistics')->where('created_at', $this->date);
            $clean->delete();

            $chunks = array_chunk($rows, 1000);
            foreach ($chunks as $chunk) {
                try {
                    DB::connection($conn)->table('page_view_statistics')->insert($chunk);
                } catch (QueryException $e) {
                    Log::info('Page View Stats Error');
                    Log::info($e->getMessage());
                }
            }
        }

        Log::info('Page Views Statistics Job Queue Successfully Executed!');
    }

    public function touch()
    {
        if (method_exists($this->job, 'getPheanstalk')) {
            $this->job->getPheanstalk()->touch($this->job->getPheanstalkJob());

            Log::info('Current job timer refresh!');
        }
    }
}
