<?php

namespace App\Jobs;

use App\LeadUser;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use SSH;

class FTPLeadFeed extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $date;

    /**
     * FTPLeadFeed constructor.
     */
    public function __construct($date)
    {
        $this->date = $date;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->attempts() > 1) {
            return;
        }

        // Query all lead users that have all required information
        Log::info('Generating lead users feed...');

        $feedTitle = "ftp_leads_file_$this->date";
        $leadUsers = LeadUser::surveyTakersFeed()->get();

        if (count($leadUsers) > 0) {
            // Create the csv file
            Excel::create($feedTitle, function ($excel) use ($leadUsers) {
                $excel->sheet('leads', function ($sheet) use ($leadUsers) {
                    //set up the title header row
                    $sheet->appendRow([
                        'First_Name',
                        'Last_Name',
                        'leadreasAddress',
                        'City',
                        'State',
                        'Zip',
                        'DOB',
                        'Entry Date',
                        'Phone',
                        'Email_Address',
                    ]);

                    foreach ($leadUsers as $leadUser) {

                        // Format the dates
                        $dob = Carbon::parse($leadUser->birthdate);
                        $formattedDOB = $dob->format('mdY');

                        $sheet->appendRow([
                            $leadUser->first_name,
                            $leadUser->last_name,
                            $leadUser->address1,
                            $leadUser->city,
                            $leadUser->state,
                            $leadUser->zip,
                            $formattedDOB,
                            $leadUser->created_at->format('mdY'),
                            $leadUser->phone,
                            $leadUser->email,
                        ]);
                    }
                });
            })->store('csv', storage_path('ftp_feeds'));

            // Upload the csv file via ftp
            Log::info("Uploading the $feedTitle.csv via ftp.");
            // $file = Storage::disk('ftp_feeds')->get("$feedTitle.csv");
            // Storage::disk('touch_mark_corp_ftp')->put("$feedTitle.csv", $file);
            $filePath = storage_path('ftp_feeds')."/$feedTitle.csv";
            SSH::into('touchmark')->put($filePath, "$feedTitle.csv");

            Log::info("$feedTitle.csv is uploaded!");
        }
    }
}
