<?php

namespace App\Http\Controllers;

use App\BugReport;
use App\Jobs\Jira\CreateJIRAIssueTicket;
use Carbon\Carbon;
use File;
use Illuminate\Http\Request;
use Log;
use Mail;
use Storage;

// use Validator;

class BugReportController extends Controller
{
    /**
     * Receiving of report and sending to developers
     *
     * @return int
     */
    public function receive(Request $request)
    {
        $this->validate($request, [
            'bug_summary' => 'required',
            'bug_description' => 'required',
        ]);

        // Log::info('Hello');
        // Log::info($request->all());
        $sender = auth()->user();
        $inputs = $request->all();
        $summary = $inputs['bug_summary'];
        $description = $inputs['bug_description'];
        $attached_files = $request->file('bug_evidence_files');

        $dateNow = Carbon::now();
        $uploadedFileNames = '';

        //save all evidence files
        if (! empty($attached_files)) {
            foreach ($attached_files as $file) {
                if ($file != null) {
                    //prepend the timestamp
                    $fullFilename = $dateNow->timestamp.'_'.$file->getClientOriginalName();
                    $bugUploadsPath = storage_path('app').'/uploads/bugs/';
                    $file->move($bugUploadsPath, $fullFilename);

                    $uploadedFileNames = $fullFilename.','.$uploadedFileNames;
                }
            }
        }

        $uploadedFileNames = trim($uploadedFileNames);
        $uploadedFileNames = trim($uploadedFileNames, ',');

        BugReport::create([
            'reporter_name' => $sender->first_name.' '.$sender->last_name.'('.$sender->email.')',
            'reporter_email' => $sender->email,
            'bug_summary' => $summary,
            'bug_description' => $description,
            'evidences' => $uploadedFileNames,
        ]);

        /*
        if(($key = array_search('', $inputs['bug_evidence_files'])) !== false) {
            unset($inputs['bug_evidence_files'][$key]);
        }

        // Log::info($inputs['bug_evidence_files']);
        // Log::info('Mail CHECKER');

        Mail::send('emails.bug_report',
            ['inputs' => $inputs,'sender' => $sender],
            function ($mail) use ($inputs,$sender,$attached_files) {

                $mail->from($sender->email, $sender->first_name.' '.$sender->last_name);

                $mail->to('karla@engageiq.com', 'Karla Librero')
                    ->to('ariel@engageiq.com', 'Ariel Magbanua')
                    ->to('burt@engageiq.com', 'Marwil Burton')
                    ->to('rohit@engageiq.com', 'Rohit Gambhir')
                    ->to('monty@engageiq.com', 'Monty Magbanua')
                    ->subject('Engage IQ: Admin Bug Report ');

                $list_of_files = json_decode($inputs['list_of_files']);
                // Log::info('--------');
                // Log::info($list_of_files);
                // Log::info('--------');
                // Log::info('Mail 2');
                foreach($inputs['bug_evidence_files'] as $file) {
                    if(in_array($file->getClientOriginalName(),$list_of_files)) {
                        // Log::info('--------');
                        // Log::info($file->getPathname());
                        // Log::info($file->getClientOriginalExtension());
                        // Log::info($file->getClientOriginalName());
                        // Log::info(filesize($file));
                        // Log::info('--------');
                        $mail->attach($file->getPathname(), ['as' => $file->getClientOriginalName()]);
                    }
                }
        });

        $attachedFilePaths = [];

        //get all file paths
        $list_of_files = json_decode($inputs['list_of_files']);
        foreach($inputs['bug_evidence_files'] as $file) {
            if(in_array($file->getClientOriginalName(),$list_of_files))
            {
                //$mail->attach($file->getPathname(), ['as' => $file->getClientOriginalName()]);
                //$attachedFilePaths[$file->getClientOriginalName()] = $file->getPathname();

                $fullFilename = $file->getClientOriginalName();

                if(Storage::exists('uploads/bugs/'.$fullFilename))
                {
                    //delete the old and replace with new
                    Storage::delete('uploads/bugs/'.$fullFilename);
                }

                //save the file to local storage
                Storage::disk('local')->put($fullFilename,  File::get($file));
                //copy the uploaded file to uploads
                Storage::move($fullFilename, 'uploads/bugs/'.$fullFilename);

                $filePath = storage_path('app/uploads/bugs').'/'.$fullFilename;
                Log::info("Bug file path: $filePath");

                array_push($attachedFilePaths,$filePath);
            }
        }

        Log::info($attachedFilePaths);

        $projectKey = env('JIRA_PROJECT_KEY','NLR');
        $issueTypeName = 'Bug';
        $assigneeUsername = env('JIRA_ISSUE_ASSIGNEE_USERNAME','monty'); //default is monty
        $jiraUserName = env('JIRA_USERNAME','ariel@engageiq.com');
        $jiraUserPassword = env('JIRA_USER_PASSWORD','ariel@engageiq');

        //execute the job that will create the ticket
        $job = (new CreateJIRAIssueTicket($projectKey,$summary,$description,$attachedFilePaths,$issueTypeName,$assigneeUsername,$jiraUserName,$jiraUserPassword))->delay(3);
        $this->dispatch($job);

        return 1;
        */
    }
}
