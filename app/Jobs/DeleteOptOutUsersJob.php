<?php

namespace App\Jobs;

use App\Lead;
use App\LeadArchive;
use App\LeadDataAdv;
use App\LeadDataAdvArchive;
use App\LeadDataCsv;
use App\LeadDataCsvArchive;
use App\LeadMessage;
use App\LeadMessageArchive;
use App\LeadSentResult;
use App\LeadSentResultArchive;
use App\LeadUser;
use App\LeadUserRequest;
use Carbon\Carbon;
use ErrorException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class DeleteOptOutUsersJob extends Job implements ShouldQueue
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
        Log::info('Deleting Opt out users');
        $status = 1;
        $end_date = Carbon::now()->endOfDay();
        $users = LeadUserRequest::where('is_sent', '=', 1)->where('is_deleted', '=', 0)->where('request_type', 'like', '%Delet%')->pluck('email', 'id')->toArray();

        if (count($users) == 0) {
            Log::info('No new users');

            return;
        }

        $emails = array_values($users);
        $ids = array_keys($users);
        // \Log::info($users);
        // \Log::info($emails);
        // \Log::info($ids);
        Log::info('Deleting Leads');
        try {
            //Lead
            $leads = Lead::whereIn('lead_email', $emails)->whereBetween('created_at', ['2018-01-01 00:00:00', $end_date])->pluck('id')->toArray();
            LeadDataAdv::destroy($leads);
            LeadDataCsv::destroy($leads);
            LeadMessage::destroy($leads);
            LeadSentResult::destroy($leads);
            Lead::destroy($leads);

            Log::info('Deleting Archived Leads');
            //LeadArchive
            $archived = LeadArchive::whereIn('lead_email', $emails)->whereBetween('created_at', ['2018-01-01 00:00:00', $end_date])->pluck('id')->toArray();
            LeadDataAdvArchive::destroy($archived);
            LeadDataCsvArchive::destroy($archived);
            LeadMessageArchive::destroy($archived);
            LeadSentResultArchive::destroy($archived);
            LeadArchive::destroy($archived);

            Log::info('Deleting Survey Takers');
            //Lead User
            LeadUser::whereIn('email', $emails)->whereBetween('created_at', ['2018-01-01 00:00:00', $end_date])->delete();
        } catch (ErrorException $e) {
            Log::info('Error encounterd');
            Log::info($e->getMessage());
            Log::info($e->getCode());
            $status = 0;
        } catch (QueryException $e) {
            Log::info('Error encountered');
            Log::info($e->getMessage());
            Log::info($e->getCode());
            $status = 0;
        }

        Log::info('Updating Request');
        //Update Status
        LeadUserRequest::whereIn('id', $ids)->update(['is_removed' => 2, 'is_deleted' => $status]);
        Log::info('Deleting opt out users DONE');

    }
}
