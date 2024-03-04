<?php

namespace App\Console\Commands;

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
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Log;

class DeleteOptOutUsersNotJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:opt-out-users-not-job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete opt out users';

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
        $this->info('Deleting Opt out users.....');
        Log::info('Deleting Opt out users');
        $status = 1;
        $end_date = Carbon::now()->endOfDay();
        $users = LeadUserRequest::where('is_sent', '=', 1)->where('is_deleted', '=', 0)->where('request_type', 'like', '%Delet%')->pluck('email', 'id')->toArray();

        if (count($users) == 0) {
            $this->info('No requests found.');
            Log::info('No requests found.');

            return;
        }

        $emails = array_values($users);
        $ids = array_keys($users);
        // \Log::info($users);
        // \Log::info($emails);
        // \Log::info($ids);
        $this->info('Deleting Leads.');
        Log::info('Deleting Leads');
        try {
            //Lead
            $leads = Lead::whereIn('lead_email', $emails)->whereBetween('created_at', ['2018-01-01 00:00:00', $end_date])->pluck('id')->toArray();
            LeadDataAdv::destroy($leads);
            LeadDataCsv::destroy($leads);
            LeadMessage::destroy($leads);
            LeadSentResult::destroy($leads);
            Lead::destroy($leads);

            $this->info('Deleting Archived Leads');
            Log::info('Deleting Archived Leads');
            //LeadArchive
            $archived = LeadArchive::whereIn('lead_email', $emails)->whereBetween('created_at', ['2018-01-01 00:00:00', $end_date])->pluck('id')->toArray();
            LeadDataAdvArchive::destroy($archived);
            LeadDataCsvArchive::destroy($archived);
            LeadMessageArchive::destroy($archived);
            LeadSentResultArchive::destroy($archived);
            LeadArchive::destroy($archived);

            $this->info('Deleting Survey Takers');
            Log::info('Deleting Survey Takers');
            //Lead User
            LeadUser::whereIn('email', $emails)->whereBetween('created_at', ['2018-01-01 00:00:00', $end_date])->delete();
        } catch (ErrorException $e) {
            $this->info('Error encountered.');
            Log::info('Error encounterd');
            Log::info($e->getMessage());
            Log::info($e->getCode());
            $status = 0;
        } catch (QueryException $e) {
            $this->info('Error encountered.');
            Log::info('Error encountered');
            Log::info($e->getMessage());
            Log::info($e->getCode());
            $status = 0;
        }

        $this->info('Updating Requests.');
        Log::info('Updating Request');
        //Update Status
        LeadUserRequest::whereIn('id', $ids)->update(['is_removed' => 2, 'is_deleted' => $status]);
        Log::info('Deleting opt out users DONE');
        $this->info('Deleting opt out users done.');
    }
}
