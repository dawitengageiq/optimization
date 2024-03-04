<?php

namespace App\Http\Controllers;

use App\Cron;
use App\CronHistory;
use Carbon\Carbon;
use DateTime;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;

class CronController extends Controller
{
    /**
     * Transfer jobs to cron history table
     */
    public function transferFinishedCronJobs(): JsonResponse
    {
        $finishedJobs = Cron::getOldFinishedJobs()->get();
        $jobCount = 0;

        //transfer these jobs to cron history table
        foreach ($finishedJobs as $job) {
            $cronHistory = new CronHistory;
            $cronHistory->id = $job->id;
            $cronHistory->leads_queued = $job->leads_queued;
            $cronHistory->leads_processed = $job->leads_processed;
            $cronHistory->leads_waiting = $job->leads_waiting;
            $cronHistory->time_started = $job->time_started;
            $cronHistory->time_ended = $job->time_ended;
            $cronHistory->status = $job->status;
            $cronHistory->lead_ids = $job->lead_ids;

            //save the job to the cron history table
            $cronHistory->save();
            //delete the job from crons table
            $job->delete();

            $jobCount++;
        }

        return response()->json(['message' => 'Jobs transfered to cron history', 'total' => $jobCount], 200);
    }

    public function getCronJob(Request $request): JsonResponse
    {

        $inputs = $request->all();
        // Log::info($inputs);
        $cronData = [];
        $columns = [ //for ordering
            'id',
            'leads_queued',
            'leads_processed',
            'leads_waiting',
            'time_started',
            'time_ended',
            'time_interval',
            'status',
            'lead_ids',
        ];

        $order_col = $columns[$inputs['order'][0]['column']];
        $order_dir = $inputs['order'][0]['dir'];

        // $cron_count = count(Cron::all());
        $cron_count = Cron::count();

        $crons = Cron::select('crons.*', DB::RAW('DATEDIFF(time_started,time_ended) as time_interval'));

        /* FOR SEARCHING DATATABLE */
        if ($inputs['search']['value'] != '') {

            $search = $inputs['search']['value'];

            if (is_numeric($search)) {
                //$crons->whereRaw('id = '.$search.' OR leads_queued = '.$search.' OR leads_processed = '.$search.' OR leads_waiting = '.$search.' OR time_started = '.$search.' OR time_ended = '.$search.' OR lead_ids LIKE %'.$search.'%');
                $crons->where('id', '=', intval($search))
                    ->orWhere('leads_queued', '=', intval($search))
                    ->orWhere('leads_processed', '=', intval($search))
                    ->orWhere('leads_waiting', '=', intval($search))
                    ->orWhere('lead_ids', 'like', "%$search%");
            } else {
                if (strtolower($search) == 'on going' || strtolower($search) == 'done') {
                    if (strtolower($search) == 'on going') {
                        $crons->where('status', 1);
                    } else {
                        $crons->where('status', 0);
                    }
                } else {
                    $crons->where('lead_ids', 'like', '%'.$search.'%')
                        ->orWhere('time_started', '=', $search)
                        ->orWhere('time_ended', '=', $search);
                }
            }

            /*
            if(strtolower($search) == 'on going' || strtolower($search) == 'done')
            {
                if(strtolower($search) == 'on going')
                {
                    $crons->where('status',1);
                }
                else
                {
                    $crons->where('status',0);
                }

            }
            else
            {
                if(is_int($search))
                {
                    $crons->whereRaw('id = '.$search.' OR leads_queued = '.$search.' OR leads_processed = '.$search.' OR leads_waiting = '.$search.' OR time_started = '.$search.' OR time_ended = '.$search.' OR lead_ids LIKE %'.$search.'%');
                }
                else
                {
                    $crons->where('lead_ids','like','%'.$search.'%');
                }
            }
            */
        }

        $crons = $crons->orderBy($order_col, $order_dir)->skip($inputs['start'])->take($inputs['length'])->get();

        foreach ($crons as $cron) {

            if ($cron->status == 0) {
                $time_started = Carbon::parse($cron->time_started);
                $time_ended = Carbon::parse($cron->time_ended);
                $interval = $time_started->diff($time_ended)->format('%H:%I:%S');
                $time_ended = $cron->time_ended;
            } else {
                $time_ended = '-';
                $interval = '-';
            }
            $status = $cron->status == 0 ? 'Done' : 'On Going';

            $actionCell = '<textarea id="crn-'.$cron->id.'-leadids" hidden>'.$cron->lead_ids.'</textarea>';
            $actionCell .= '<button data-id="'.$cron->id.'" data-lq="'.$cron->leads_queued.'" data-lp="'.$cron->leads_processed.'" data-lw="'.$cron->leads_waiting.'" data-ts="'.$cron->time_started.'" data-te="'.$time_ended.'" data-ti="'.$interval.'" data-status="'.$status.'" data-s = "'.$cron->status.'" class="more-details btn btn-default">Show</button>';

            $data = [
                $cron->id,
                $cron->leads_queued,
                $cron->leads_processed,
                $cron->leads_waiting,
                $cron->time_started,
                $time_ended,
                $interval,
                $status,
                $actionCell,
            ];

            array_push($cronData, $data);
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $cron_count,  // total number of records
            'recordsFiltered' => $cron_count, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $cronData,
        ];

        return response()->json($responseData, 200);
    }

    public function getCronHistory(Request $request): JsonResponse
    {

        $inputs = $request->all();
        // Log::info($inputs);
        $cronData = [];
        $columns = [ //for ordering
            'id',
            'leads_queued',
            'leads_processed',
            'leads_waiting',
            'time_started',
            'time_ended',
            'time_interval',
            'status',
            'lead_ids',
        ];

        $order_col = $columns[$inputs['order'][0]['column']];
        $order_dir = $inputs['order'][0]['dir'];

        $cron_count = CronHistory::count();

        $crons = CronHistory::select('cron_histories.*', DB::RAW('DATEDIFF(time_started,time_ended) as time_interval'));

        /* FOR SEARCHING DATATABLE */
        if ($inputs['search']['value'] != '') {
            $search = $inputs['search']['value'];

            if (is_numeric($search)) {
                $crons->where('id', '=', intval($search))
                    ->orWhere('leads_queued', '=', intval($search))
                    ->orWhere('leads_processed', '=', intval($search))
                    ->orWhere('leads_waiting', '=', intval($search))
                    ->orWhere('lead_ids', 'like', "%$search%");
            } else {
                if (strtolower($search) == 'on going' || strtolower($search) == 'done') {
                    if (strtolower($search) == 'on going') {
                        $crons->where('status', 1);
                    } else {
                        $crons->where('status', 0);
                    }
                } else {
                    $crons->where('lead_ids', 'like', '%'.$search.'%')
                        ->orWhere('time_started', '=', $search)
                        ->orWhere('time_ended', '=', $search);
                }
            }

            /*
            if(strtolower($search) == 'on going' || strtolower($search) == 'done')
            {
                if(strtolower($search) == 'on going')
                {
                    $crons->where('status',1);
                }
                else
                {
                    $crons->where('status',0);
                }
            }
            else
            {
                if(is_int($search))
                {
                    $crons->whereRaw('id = '.$search.' OR leads_queued = '.$search.' OR leads_processed = '.$search.' OR leads_waiting = '.$search.' OR time_started = '.$search.' OR time_ended = '.$search.' OR lead_ids LIKE %'.$search.'%');
                }
                else
                {
                    $crons->where('lead_ids','like','%'.$search.'%');
                }
            }
            */
        }

        $crons = $crons->orderBy($order_col, $order_dir)->skip($inputs['start'])->take($inputs['length'])->get();

        foreach ($crons as $cron) {

            if ($cron->status == 0) {
                $time_started = Carbon::parse($cron->time_started);
                $time_ended = Carbon::parse($cron->time_ended);
                $interval = $time_started->diff($time_ended)->format('%H:%I:%S');
                $time_ended = $cron->time_ended;
            } else {
                $time_ended = '-';
                $interval = '-';
            }
            $status = $cron->status == 0 ? 'Done' : 'On Going';

            $actionCell = '<textarea id="crn-'.$cron->id.'-leadids" hidden>'.$cron->lead_ids.'</textarea>';
            $actionCell .= '<button data-id="'.$cron->id.'" data-lq="'.$cron->leads_queued.'" data-lp="'.$cron->leads_processed.'" data-lw="'.$cron->leads_waiting.'" data-ts="'.$cron->time_started.'" data-te="'.$time_ended.'" data-ti="'.$interval.'" data-status="'.$status.'" data-s = "'.$cron->status.'" class="more-details btn btn-default">Show</button>';

            $data = [
                $cron->id,
                $cron->leads_queued,
                $cron->leads_processed,
                $cron->leads_waiting,
                $cron->time_started,
                $time_ended,
                $interval,
                $status,
                $actionCell,
            ];

            array_push($cronData, $data);
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $cron_count,  // total number of records
            'recordsFiltered' => $cron_count, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $cronData,
        ];

        return response()->json($responseData, 200);

    }

    public function validateDate($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);

        return $d && $d->format('Y-m-d') == $date;
    }
}
