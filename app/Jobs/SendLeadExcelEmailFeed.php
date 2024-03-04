<?php

namespace App\Jobs;

use App\Campaign;
use App\Lead;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Mail;

class SendLeadExcelEmailFeed extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $campaignID;

    protected $fromDate;

    protected $toDate;

    protected $recipient;

    protected $recipientName;

    /**
     * Create a new job instance.
     */
    public function __construct($campaignID, $fromDate, $toDate, $recipient, $recipientName = '')
    {
        $this->campaignID = $campaignID;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->recipient = $recipient;
        $this->recipientName = $recipientName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->attempts() > 1) {
            return;
        }

        //query the leads
        $params = [
            'campaign_id' => $this->campaignID,
            'lead_date_from' => $this->fromDate,
            'lead_date_to' => $this->toDate,
        ];

        $leads = Lead::searchLeads($params)->with('leadDataCSV')->get();

        $rows = [];

        foreach ($leads as $lead) {
            //parse the csv json string to array
            $parsedCSV = json_decode($lead->leadDataCSV->value, true);

            $row = [];

            foreach ($parsedCSV as $key => $value) {

                if ($key != 'callback' &&
                    $key != 'eiq_campaign_id' &&
                    $key != 'eiq_affiliate_id' &&
                    $key != 'yesnoto' &&
                    $key != 'toluna-campaign' &&
                    $key != 'xxTrustedFormToken' &&
                    $key != 'xxTrustedFormCertUrl' &&
                    $key != '_') {
                    $row[$key] = $value;
                }
            }

            //add the row
            array_push($rows, $row);
        }

        $title = 'leads_email_feed_'.$this->campaignID;
        $sheetTitle = $title;

        Excel::create($title, function ($excel) use ($sheetTitle, $rows) {
            $excel->sheet($sheetTitle, function ($sheet) use ($rows) {
                $rowNumber = 1;
                $firstDataRowNumber = 1;

                //Set auto size for sheet
                $sheet->setAutoSize(true);

                //style the headers
                $sheet->cells("A$rowNumber:G$rowNumber", function ($cells) {
                    // Set font
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                    $cells->setBackground('#337ab7');
                });

                foreach ($rows as $csvData) {

                    //set up the title header row
                    $headers = [];
                    $leadRow = [];

                    foreach ($csvData as $key => $value) {
                        array_push($headers, $key);
                        array_push($leadRow, $value);
                    }

                    //append the header only once
                    if ($rowNumber <= 1) {
                        $sheet->appendRow($headers);
                        $rowNumber++;
                        $firstDataRowNumber++;
                    }

                    //append the lead data
                    $sheet->appendRow($leadRow);

                    $rowNumber++;
                    $firstDataRowNumber++;
                }

            });

        })->store('xls', storage_path('app'));

        $filePath = storage_path('app').'/'.$title.'.xls';
        $campaign = Campaign::find($this->campaignID);
        $mailAdditionalData = [
            'campaign' => $campaign->name,
            'date_range' => " from $this->fromDate to $this->toDate ",
        ];

        $recipient = $this->recipient;
        $recipientName = $this->recipientName;
        $excelAttachment = $filePath;

        Mail::send('emails.lead_csv_data_email', $mailAdditionalData, function ($message) use ($recipient, $recipientName, $excelAttachment, $campaign) {
            $message->from('burt@engageiq.com', 'Marwil Burton');
            $message->to($recipient, $recipientName)->subject('Excel Lead Data CSV Feed for '.$campaign->name);
            $message->attach($excelAttachment);
        });
    }
}
