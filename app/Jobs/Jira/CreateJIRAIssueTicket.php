<?php

namespace App\Jobs\Jira;

use App\Jobs\Job;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class CreateJIRAIssueTicket extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $projectKey;

    protected $summary;

    protected $description;

    protected $fileAttachments;

    protected $issueTypeName;

    protected $assigneeUsername;

    protected $jiraUserName;

    protected $jiraUserPassword;

    /**
     * Create the job instance
     */
    public function __construct($projectKey, $summary, $description, $fileAttachments, $issueTypeName, $assigneeUsername, $jiraUserName, $jiraUserPassword)
    {
        $this->projectKey = $projectKey;
        $this->summary = $summary;
        $this->description = $description;
        $this->fileAttachments = $fileAttachments;
        $this->issueTypeName = $issueTypeName;
        $this->assigneeUsername = $assigneeUsername;
        $this->jiraUserName = $jiraUserName;
        $this->jiraUserPassword = $jiraUserPassword;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->attempts() > 1) {
            return;
        }

        Log::info('JIRA Create Issue API: ');

        $requestBody['fields'] = [];

        //specify the project key
        $requestBody['fields']['project'] = ['key' => $this->projectKey];

        //the summary
        $requestBody['fields']['summary'] = $this->summary;

        //the description
        $requestBody['fields']['description'] = $this->description;

        //the issuetype
        $requestBody['fields']['issuetype'] = ['name' => $this->issueTypeName];

        //the assignee
        $requestBody['fields']['assignee'] = ['name' => $this->assigneeUsername];

        //this will return the engageiq atlassian base url if it is not specified in env
        $baseURI = env('JIRA_API_BASE_URL', 'https://engageiq.atlassian.net');

        $reporterUsername = $this->jiraUserName;
        $reporterPassword = $this->jiraUserPassword;

        Log::info('JIRA_API_BASE_URL: '.$baseURI);
        Log::info('JIRA_USERNAME: '.$reporterUsername);
        Log::info('JIRA_USER_PASSWORD: '.$reporterPassword);
        Log::info('JIRA_ISSUE_ASSIGNEE_USERNAME: '.$this->assigneeUsername);
        Log::info('JIRA_PROJECT_KEY: '.$this->projectKey);

        $client = new Client([
            'base_uri' => $baseURI,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'auth' => [$reporterUsername, $reporterPassword],
        ]);

        try {
            $response = $client->request('POST', '/rest/api/2/issue/', [
                'json' => $requestBody,
            ]);

            //get the status code
            $statusCode = $response->getStatusCode();
            Log::info("HTTP status code: $statusCode");

            //extract the response content
            $responseBody = $response->getBody();
            $responseBodyArray = \GuzzleHttp\json_decode($responseBody);

            Log::info("JIRA ticket created! - $responseBodyArray->key");

            $attachmentClient = new Client([
                'base_uri' => $baseURI,
                'headers' => [
                    'X-Atlassian-Token' => 'no-check',
                ],
                'auth' => [$reporterUsername, $reporterPassword],
            ]);

            $url = "/rest/api/2/issue/$responseBodyArray->key/attachments";
            Log::info("attachment url: $url");

            $payload = [
                'multipart' => [],
            ];

            foreach ($this->fileAttachments as $attachment) {
                Log::info("attachment: $attachment");

                $data = [
                    'name' => 'file',
                    'contents' => fopen($attachment, 'r'),
                ];

                array_push($payload['multipart'], $data);
                /*
                $attachmentResponse = $attachmentClient->request('POST',$url,[
                    'multipart' => [
                        [
                            'name' => 'file',
                            'contents' => fopen($attachment, 'r')
                        ]
                    ]
                ]);

                $statusCode = $attachmentResponse->getStatusCode();
                $attachmentResponseBody = $attachmentResponse->getBody();

                Log::info("JIRA Add Attachment Response Status Code: $statusCode");
                Log::info("JIRA Add Attachment Response: $attachmentResponseBody");
                */
            }

            $attachmentResponse = $attachmentClient->request('POST', $url, $payload);

            $statusCode = $attachmentResponse->getStatusCode();
            $attachmentResponseBody = $attachmentResponse->getBody();

            Log::info("JIRA Add Attachment Response Status Code: $statusCode");
            Log::info("JIRA Add Attachment Response: $attachmentResponseBody");
        } catch (RequestException $e) {
            $statusCode = $e->getCode();
            Log::info("HTTP status code: $statusCode");

            Log::info($e->getRequest());

            if ($e->hasResponse()) {
                Log::info($e->getResponse());
            }
        }
    }
}
