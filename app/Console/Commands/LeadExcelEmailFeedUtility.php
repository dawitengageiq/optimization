<?php

namespace App\Console\Commands;

use App\Campaign;
use App\Jobs\SendLeadExcelEmailFeed;
use App\Lead;
use Carbon\Carbon;
use DateTime;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Mail;
use Validator;

class LeadExcelEmailFeedUtility extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:lead-csv-data-feed
                            {--campaign= : The ID of the campaign.}
                            {--from= : The start date of the query query for all the leads (YYYY-MM-DD).}
                            {--to= : The end date of the query for all the leads (YYYY-MM-DD).}
                            {--email= : The email address of the recipient.}
                            {--name= : The name of the recipient.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will query certain leads and extract the csv data and email it.';

    /**
     * Create a new command instance.
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
        $this->info('Initiating lead csv data excel feed tasks...');

        $campaignID = $this->option('campaign');
        $fromDate = $this->option('from');
        $toDate = $this->option('to');
        $recipient = $this->option('email');
        $recipientName = $this->option('name');

        if (empty($campaignID)) {
            $this->info('Please provide the campaign ID!');
        } elseif (empty($fromDate) || empty($toDate)) {
            $this->info('Please provide the date range!');
        } elseif ((! $this->validateDate($fromDate) || ! $this->validateDate($toDate)) || ! $this->isRangeValid($fromDate, $toDate)) {
            $this->info('Invalid date range!');
        } elseif (empty($recipient)) {
            $this->info('Please provide the recipient email!');
        } elseif (! $this->isEmailValid($recipient)) {
            $this->info('Invalid recipient email!');
        } else {
            $this->info("Processing email feed for $recipient.");

            //execute the queue for sending the excel feed with 5 seconds delay before execution
            $job = (new SendLeadExcelEmailFeed($campaignID, $fromDate, $toDate, $recipient, $recipientName))->delay(3);
            dispatch($job);

            //temporarily put the processing here since no decision yet when to use queues.
            //$this->sendLeadExcelEmailFeed($campaignID,$fromDate,$toDate,$recipient,$recipientName);

            $this->info("Excel Email Feed is processed and will be sent later via email to $recipient");
        }
    }

    /*
    private function sendLeadExcelEmailFeed($campaignID,$fromDate,$toDate,$recipient,$recipientName)
    {
        $params = [
            'campaign_id' => $campaignID,
            'lead_date_from' => $fromDate,
            'lead_date_to' => $toDate
        ];

        $this->info("Querying the needed leads...");
        $leads = Lead::searchLeads($params)->with('leadDataCSV')->get();
        $campaign = Campaign::find($campaignID);

        $rows = [];

        foreach($leads as $lead)
        {
            //parse the csv json string to array
            $parsedCSV = json_decode($lead->leadDataCSV->value,true);

            $row = [];

            foreach($parsedCSV as $key => $value)
            {

                if( $key!='callback' &&
                    $key!='eiq_campaign_id' &&
                    $key!='eiq_affiliate_id' &&
                    $key!='yesnoto' &&
                    $key!='toluna-campaign' &&
                    $key!='xxTrustedFormToken' &&
                    $key!='xxTrustedFormCertUrl' &&
                    $key!='_')
                {
                    $row[$key] = $value;
                }
            }

            //add the row
            array_push($rows,$row);
        }

        $title = 'email_feed_cp_'.$campaign->id;
        $sheetTitle = $fromDate.'_'.$toDate;

        Excel::create($title,function($excel) use($title,$sheetTitle,$rows)
        {
            $excel->sheet($sheetTitle,function($sheet) use($title,$rows)
            {
                $rowNumber = 1;
                $firstDataRowNumber = 1;

                //Set auto size for sheet
                $sheet->setAutoSize(true);

                //style the headers
                $sheet->cells("A$rowNumber:G$rowNumber", function($cells)
                {
                    // Set font
                    $cells->setFont([
                        'size'       => '12',
                        'bold'       =>  true
                    ]);

                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                    $cells->setBackground('#337ab7');
                });

                foreach($rows as $csvData)
                {

                    //set up the title header row
                    $headers = [];
                    $leadRow = [];

                    foreach($csvData as $key => $value)
                    {
                        array_push($headers,$key);
                        array_push($leadRow,$value);
                    }

                    //append the header only once
                    if($rowNumber<=1)
                    {
                        $sheet->appendRow($headers);
                        ++$rowNumber;
                        ++$firstDataRowNumber;
                    }

                    //append the lead data
                    $sheet->appendRow($leadRow);

                    ++$rowNumber;
                    ++$firstDataRowNumber;
                }

            });

        })->store('xls', storage_path('app'));

        $filePath = storage_path('app').'/'.$title.'.xls';

        $mailAdditionalData = [
            'campaign' => $campaign->name,
            'date_range' => " from $fromDate to $toDate "
        ];

        $excelAttachment = $filePath;

        Mail::send('emails.lead_csv_data_email', $mailAdditionalData, function ($message) use ($recipient,$recipientName,$excelAttachment,$campaign) {
            $message->from('burt@engageiq.com', 'Marwil Burton');
            $message->to($recipient,$recipientName)->subject('Excel Lead Data CSV Feed for '.$campaign->name);
            $message->attach($excelAttachment);
        });
    }
    */
    private function validateDate($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);

        return $d && $d->format('Y-m-d') === $date;
    }

    private function isRangeValid($fromDate, $toDate)
    {
        $from = Carbon::parse($fromDate);
        $to = Carbon::parse($toDate);

        $days = $from->diffInDays($to, false);

        if ($days < 0) {
            return false;
        }

        return true;
    }

    private function isEmailValid($email)
    {
        $inputs = [
            'recipient_email' => $email,
        ];

        $validator = Validator::make($inputs, [
            'recipient_email' => 'email',
        ]);

        if ($validator->fails()) {
            return false;
        }

        return true;
    }
}
