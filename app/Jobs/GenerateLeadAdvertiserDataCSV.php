<?php

namespace App\Jobs;

use App\CampaignConfig;
use App\Lead;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use SSH;

class GenerateLeadAdvertiserDataCSV extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $dateFrom;

    protected $dateTo;

    protected $campaignID;

    protected $campaignName;

    protected $ftpHost;

    protected $ftpUser;

    protected $ftpPassword;

    /**
     * GenerateLeadAdvertiserDataCSV constructor.
     */
    public function __construct($dateFrom, $dateTo, $campaignID, $campaignName)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->campaignID = $campaignID;
        $this->campaignName = $campaignName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->attempts() > 1) {
            return;
        }

        Log::info('Generating leads advertiser data feed.');
        Log::info("Campaign ID: $this->campaignID");
        Log::info("date_from: $this->dateFrom");
        Log::info("date_to: $this->dateTo");

        // Get all successful leads from  a certain campaign
        $leads = Lead::searchLeads([
            'lead_status' => 1,
            'campaign_id' => $this->campaignID,
            'lead_date_from' => $this->dateFrom,
            'lead_date_to' => $this->dateTo,
        ])->with('leadDataADV')->get();

        Log::info('Leads to process: '.count($leads));

        // Create the CSV file
        if (count($leads) > 0) {
            $fileTitle = $this->campaignName.$this->campaignID."_$this->dateFrom";
            $sheetTitle = "$this->dateFrom - $this->dateTo";

            Excel::create($fileTitle, function ($excel) use ($sheetTitle, $leads) {
                $excel->sheet($sheetTitle, function ($sheet) use ($leads) {
                    $header = [];

                    foreach ($leads as $lead) {
                        if ($lead->leadDataADV->value != null) {
                            $parts = parse_url($lead->leadDataADV->value);
                            $query = [];

                            if (isset($parts['query'])) {
                                parse_str($parts['query'], $query);
                            }

                            if (count($query) > 0) {
                                // Header
                                if (count($header) == 0) {
                                    foreach ($query as $key => $value) {
                                        array_push($header, $key);
                                    }

                                    $sheet->appendRow($header);
                                }

                                $row = [];

                                foreach ($query as $value) {
                                    array_push($row, $value);
                                }

                                $sheet->appendRow($row);

                                /*
                                // Insert the data to the row
                                $sheet->appendRow([$sheet->appendRow([
                                    isset($query['sourceid']) ? $query['sourceid'] : '',
                                    isset($query['email']) ? $query['email'] : '',
                                    isset($query['firstname']) ? $query['firstname'] : '',
                                    isset($query['lastname']) ? $query['lastname'] : '',
                                    isset($query['zip']) ? $query['zip'] : '',
                                    isset($query['gender']) ? $query['gender'] : '',
                                    isset($query['dob']) ? $query['dob'] : '',
                                    isset($query['CountryID']) ? $query['CountryID'] : '',
                                    isset($query['Language']) ? $query['Language'] : ''
                                ]);
                                */
                            }
                        }
                    }
                });
            })->store('csv', storage_path('ftp_feeds'));

            // get the ftp config from campaign config
            $campaignConfig = CampaignConfig::find($this->campaignID);

            if ($campaignConfig->ftp_sent == 1) {
                Log::info("Uploading the $fileTitle.csv via ftp.");
                Log::info($campaignConfig);

                if ($campaignConfig->ftp_protocol == 1) { // FTP
                    $file = Storage::disk('ftp_feeds')->get("$fileTitle.csv");
                    $ftp = Storage::createFtpDriver([
                        'host' => $campaignConfig->ftp_host,
                        'username' => $campaignConfig->ftp_username,
                        'password' => $campaignConfig->ftp_password,
                        'port' => $campaignConfig->ftp_port,
                        'root' => $campaignConfig->ftp_directory,
                        'timeout' => $campaignConfig->ftp_timeout,
                    ]);

                    $ftp->put("$fileTitle.csv", $file);
                    Log::info("$fileTitle.csv is uploaded!");

                } elseif ($campaignConfig->ftp_protocol == 2) { // SFTP
                    $filePath = storage_path('ftp_feeds')."/$fileTitle.csv";

                    $sftpConnectionConfig = [
                        'host' => $campaignConfig->ftp_host,
                        'username' => $campaignConfig->ftp_username,
                        'password' => $campaignConfig->ftp_password,
                        'key' => '',
                        'keytext' => '',
                        'keyphrase' => '',
                        'agent' => '',
                        'timeout' => $campaignConfig->ftp_timeout,
                    ];

                    config(['remote.connections.production' => $sftpConnectionConfig]);

                    $remoteDirectory = Str::endsWith($campaignConfig->ftp_directory, '/') ? $campaignConfig->ftp_directory : $campaignConfig->ftp_directory.'/';
                    SSH::into('production')->put($filePath, $remoteDirectory."$fileTitle.csv");

                    // Switch back to the default
                    $sftpConnectionConfig = [
                        'host' => '',
                        'username' => '',
                        'password' => '',
                        'key' => '',
                        'keytext' => '',
                        'keyphrase' => '',
                        'agent' => '',
                        'timeout' => 10,
                    ];

                    config(['remote.connections.production' => $sftpConnectionConfig]);
                    Log::info("$fileTitle.csv is uploaded!");
                } else {
                    Log::info('Unknown FTP Protocol');
                }
            }

            if ($campaignConfig->email_sent == 1) {
                $subject = $campaignConfig->email_title;
                $body = $campaignConfig->email_body;
                $to = explode(';', $campaignConfig->email_to);
                $file = storage_path('ftp_feeds').'/'."$fileTitle.csv";
                Mail::raw($body, function ($m) use ($to, $subject, $file) {
                    $m->attach($file);
                    $m->from('ariel@engageiq.com', 'Ariel Magbanua');
                    foreach ($to as $t) {
                        $m->to($t);
                    }
                    $m->subject($subject);
                });
            }
        }
    }
}
