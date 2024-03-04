<?php

namespace App\Jobs;

use App\BugReport;
use App\Helpers\Repositories\Settings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Mail;

class SendBugReportsV2 extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $bugReports;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->bugReports = BugReport::all();
    }

    /**
     * Execute the job.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(Settings $settings): void
    {
        if ($this->attempts() > 1) {
            return;
        }

        //Get Recipients
        $qa_recipients = [];
        $setting = $settings->getValue('qa_recipient');

        if (isset($setting)) {
            $rec_emails = explode(';', $setting->description);
            foreach ($rec_emails as $re) {
                $re = trim($re);
                if (filter_var($re, FILTER_VALIDATE_EMAIL)) {
                    $qa_recipients[] = $re;
                }
            }
        }

        if (count($qa_recipients) == 0) {
            $qa_recipients[] = env('REPORTS_EMAIL_NOTIFICATION_RECIPIENT', 'marwilburton@hotmail.com');
        }

        $projectKey = env('JIRA_PROJECT_KEY', 'NLR');
        $issueTypeName = 'Bug';
        $assigneeUsername = env('JIRA_ISSUE_ASSIGNEE_USERNAME', 'Ariel'); //default is Ariel
        $jiraUserName = env('JIRA_USERNAME', 'monty');
        $jiraUserPassword = env('JIRA_USER_PASSWORD', 'magbanua2016');

        //this will return the engageiq atlassian base url if it is not specified in env
        $baseURI = env('JIRA_API_BASE_URL', 'https://engageiq.atlassian.net');

        $inputs = [];

        foreach ($this->bugReports as $report) {
            $jiraDescriptionTicket = '';
            $inputs['bug_summary'] = $report->bug_summary;
            $inputs['bug_description'] = $report->bug_description;
            $inputs['jira_description'] = $jiraDescriptionTicket;

            $senderNameWithEmail = $report->reporter_name;
            $senderEmail = $report->reporter_email;

            //attach all attachments
            $attachments = explode(',', $report->evidences);

            /*
            //create JIRA ticket via API
            Log::info('JIRA Create Issue API: ');

            $this->latesReport = $report;

            $requestBody['fields'] = [];

            //specify the project key
            $requestBody['fields']['project'] = ['key' => $projectKey];

            //the summary
            $requestBody['fields']['summary'] = $report->bug_summary;

            //the description
            $requestBody['fields']['description'] = $report->bug_description;

            //the issuetype
            $requestBody['fields']['issuetype'] = ['name' => $issueTypeName];

            //the assignee
            $requestBody['fields']['assignee'] = ['name' => $assigneeUsername];

            Log::info('JIRA_API_BASE_URL: '.$baseURI);
            Log::info('JIRA_USERNAME: '.$jiraUserName);
            Log::info('JIRA_USER_PASSWORD: '.$jiraUserPassword);
            Log::info('JIRA_ISSUE_ASSIGNEE_USERNAME: '.$assigneeUsername);
            Log::info('JIRA_PROJECT_KEY: '.$projectKey);

            $client = new Client([
                'base_uri' => $baseURI,
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'auth' => [$jiraUserName, $jiraUserPassword],
            ]);

            try
            {

                $response = $client->request('POST', '/rest/api/2/issue/', [
                    'json' => $requestBody,
                ]);

                //get the status code
                $statusCode = $response->getStatusCode();
                Log::info("Create Ticket HTTP status code: $statusCode");

                //extract the response content
                $responseBody = $response->getBody()->getContents();
                $responseBodyArray = \GuzzleHttp\json_decode($responseBody);

                Log::info("JIRA ticket created! - $responseBodyArray->key");
                $jiraDescriptionTicket = "JIRA ticket ($responseBodyArray->key) was successfully created.";

                //do not execute attachment API if evidence is empty
                if(!empty($report->evidences))
                {
                    $attachmentClient = new Client([
                        'base_uri' => $baseURI,
                        'headers' => [
                            'X-Atlassian-Token' => 'no-check'
                        ],
                        'auth' => [$jiraUserName, $jiraUserPassword],
                    ]);

                    $url = "/rest/api/2/issue/$responseBodyArray->key/attachments";
                    Log::info("attachment url: $url");

                    $payload = [
                        'multipart' => []
                    ];

                    $attachmentsAllComplete = true;

                    //send them one by one
                    foreach($attachments as $attachment)
                    {
                        $attachmentPath = storage_path('app').'/uploads/bugs/'.$attachment;

                        Log::info("attachment path: $attachmentPath");

                        try
                        {
                            //$contents = File::get($attachmentPath);

                            $data = [
                                'name' => 'file',
                                'contents' => fopen($attachmentPath,'r')
                            ];

                            array_push($payload['multipart'],$data);
                        }
                        catch (\ErrorException $exception)
                        {
                            Log::info("$attachment does not exist!");
                            $attachmentsAllComplete = false;
                        }
                    }

                    $attachmentResponse = $attachmentClient->request('POST',$url,$payload);

                    $statusCode = $attachmentResponse->getStatusCode();
                    $attachmentResponseBody = $attachmentResponse->getBody()->getContents();

                    Log::info("JIRA Add Attachment Response Status Code: $statusCode");
                    Log::info("JIRA Add Attachment Response: $attachmentResponseBody");

                    $isEverythingOK = ($statusCode==200 || $statusCode==201) ? true : false;

                    if($attachmentsAllComplete && $isEverythingOK)
                    {
                        $jiraDescriptionTicket = "JIRA ticket ($responseBodyArray->key) was successfully created and all bug attachments are attached.";

                        if(count($attachments)==0)
                        {
                            $jiraDescriptionTicket = "JIRA ticket ($responseBodyArray->key) was successfully created and there are no bug attachments.";
                        }
                    }
                    else
                    {
                        $jiraDescriptionTicket = "JIRA ticket ($responseBodyArray->key) was successfully created but there is problem attaching some bug attachments kindly manually attach the files to JIRA.";
                    }
                }

                $inputs['jira_description'] = $jiraDescriptionTicket;

                //soft delete the report
                //$report->delete();
            }
            catch(ClientException $e)
            {
                $statusCode = $e->getCode();
                Log::info("HTTP status code: $statusCode");
                Log::info($e->getMessage());
            }

            Log::info($inputs);
            Log::info("senderEmail: $senderEmail");
            Log::info("senderNameWithEmail: $senderNameWithEmail");
            */

            if (count($inputs) > 0 && (isset($senderEmail) && isset($senderNameWithEmail))) {
                $evidences = $report->evidences;

                Mail::send('emails.bug_report',
                    ['inputs' => $inputs, 'sender' => $report->reporter_name],
                    function ($mail) use ($senderEmail, $senderNameWithEmail, $evidences, $attachments, $qa_recipients) {

                        $mail->from($senderEmail, $senderNameWithEmail);

                        $mail->to($qa_recipients);

                        $mail->subject('Engage IQ: Admin Bug Report ');

                        if (! empty($evidences)) {
                            foreach ($attachments as $attachment) {
                                $attachmentPath = storage_path('app').'/uploads/bugs/'.$attachment;

                                if (file_exists($attachmentPath)) {
                                    $mail->attach($attachmentPath);
                                }
                            }
                        }
                    });

                Log::info('Bug Report email notification sent for '.$inputs['bug_summary']);
            }

            //soft delete the report
            $report->delete();
        }
    }
}
