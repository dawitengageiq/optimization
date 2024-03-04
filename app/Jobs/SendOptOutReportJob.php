<?php

namespace App\Jobs;

use App\LeadUserRequest;
use App\Setting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Log;
use Maatwebsite\Excel\Facades\Excel;

class SendOptOutReportJob extends Job implements ShouldQueue
{
    protected $to;

    protected $from;

    protected $users;

    protected $emails;

    protected $ids;

    protected $recipients;

    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to = $to;
        $this->emails = [];
        $this->users = LeadUserRequest::whereBetween('created_at', [$this->from.' 00:00:00', $this->to.' 23:59:59'])->get()->toArray();
        // \Log::info($this->users);
        foreach ($this->users as $user) {
            $this->ids[] = $user['id'];
            if (! in_array($user['email'], $this->emails)) {
                $this->emails[] = $user['email'];
            }
        }

        $setting = Setting::where('code', 'optoutreport_recipient')->first();
        $this->recipients = explode(';', $setting->description);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Opt out report');
        if (count($this->users) == 0) {
            Log::info('No users to report');

            return;
        }

        $reportTitle = 'OptOutReport_'.$this->from.' - '.$this->to;
        $sheetTitle = $this->from.' - '.$this->to;
        $users = $this->users;
        $date = $this->from.' - '.$this->to;
        Excel::create($reportTitle, function ($excel) use ($users, $date, $sheetTitle) {
            $excel->sheet($sheetTitle, function ($sheet) use ($users, $date) {
                $rowNumber = 1;

                //Set auto size for sheet
                $sheet->setAutoSize(true);

                //set up the title header row
                $sheet->appendRow([
                    'OPT OUT Report '.$date,
                ]);
                //style the headers
                $sheet->cells("A$rowNumber:C$rowNumber", function ($cells) {
                    // Set font
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('left');
                    $cells->setValignment('center');
                });
                $sheet->mergeCells("A$rowNumber:C$rowNumber");
                $rowNumber++;

                $sheet->appendRow([
                    '',
                ]);
                $rowNumber++;

                //set up the title header row
                $sheet->appendRow([
                    'ID',
                    'Request Type',
                    'Email',
                    'First Name',
                    'Last Name',
                    'State',
                    'City',
                    'Zip',
                    'Address',
                    'Request Date',
                    'Subscribed Campaign',
                    'One Trust Double OptIN',
                    'Get Opt Out User Subscribed Campaigns',
                    'Send Publisher Opt Out Email',
                    'Delete Opt Out User in NLR',
                ]);

                //style the headers
                $sheet->cells("A$rowNumber:O$rowNumber", function ($cells) {
                    // Set font
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                    $cells->setBackground('#000000');
                    $cells->setFontColor('#ffffff');
                });

                $stat = true;

                foreach ($users as $user) {
                    $rowNumber++;

                    if ($user['is_sent'] == 0 || $user['is_deleted'] == 0 || $user['subscribed_campaigns'] === null) {
                        $stat = false;
                    }

                    //set up the title header row
                    $sheet->appendRow([
                        $user['id'],
                        $user['request_type'],
                        $user['email'],
                        $user['first_name'],
                        $user['last_name'],
                        $user['state'],
                        $user['city'],
                        $user['zip'],
                        $user['address'],
                        $user['request_date'],
                        $user['subscribed_campaigns'],
                        1,
                        $user['subscribed_campaigns'] === null ? 0 : 1,
                        $user['is_sent'],
                        $user['is_deleted'],
                    ]);
                }

                $lastRowNumber = $rowNumber;

                $rowNumber++;

                $sheet->appendRow([
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    'Total',
                    "=SUM(L3:L$lastRowNumber)",
                    "=SUM(M3:M$lastRowNumber)",
                    "=SUM(N3:N$lastRowNumber)",
                    "=SUM(O3:O$lastRowNumber)",
                ]);

                $sheet->cells("K$rowNumber:O$rowNumber", function ($cells) {
                    // Set font
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('right');
                    $cells->setValignment('center');
                });

                $sheet->cells("A$rowNumber:O$rowNumber", function ($cells) use ($stat) {
                    if ($stat) {
                        $color = '#FFFF00';
                    } else {
                        $color = '#FF0000';
                    }
                    $cells->setBackground($color);
                });

                $sheet->cells("K4:K$lastRowNumber", function ($cells) {
                    $cells->setAlignment('right');
                    $cells->setValignment('center');
                });

                if (! $stat) {
                    $rowNumber++;

                    $sheet->appendRow([
                        '',
                    ]);

                    $rowNumber++;

                    $sheet->appendRow([
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        'DISCREPANCY DETECTED PLEASE CHECK ABOVE REPORT FOR DETAILS',
                    ]);
                    $sheet->mergeCells("L$rowNumber:O$rowNumber");
                    $sheet->cells("L$rowNumber:O$rowNumber", function ($cells) {
                        // Set font
                        $cells->setFont([
                            'size' => '12',
                            'bold' => true,
                        ]);

                        $cells->setAlignment('center');
                        $cells->setValignment('center');
                    });
                }
            });
        })->store('xlsx', storage_path('downloads'));

        $file_path = storage_path('downloads').'/'.$reportTitle.'.xlsx';

        $excelAttachment = $file_path;

        $report_recipients = $this->recipients;

        $lr_build = env('APP_BUILD', 'NLR');
        Mail::send('emails.opt_out_report', ['date' => $date],
            function ($message) use ($excelAttachment, $lr_build, $report_recipients) {

                $message->from('admin@engageiq.com', 'Automated Report by '.$lr_build);

                if (count($report_recipients) > 0) {
                    foreach ($report_recipients as $recipient) {
                        $message->to($recipient);
                    }
                }

                $message->subject('NLR Opt-Out Report');
                $message->attach($excelAttachment);
            });

        LeadUserRequest::whereIn('id', $this->ids)->update(['is_removed' => 3, 'is_reported' => 1]);
        Log::info('Done');
    }
}
