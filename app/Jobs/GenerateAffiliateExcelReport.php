<?php

namespace App\Jobs;

use App\Helpers\AffiliateReportExcelGeneratorHelper;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Log;

class GenerateAffiliateExcelReport extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $date_from;

    protected $date_to;

    protected $type;

    protected $startLog;

    protected $endLog;

    protected $excel;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($dateFrom, $dateTo, $type)
    {
        $this->startLog = Carbon::now();
        $this->date_from = $dateFrom;
        $this->date_to = $dateTo;
        $this->type = $type;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Generating Affiliate Excel Report for $this->date_from $this->date_to");
        $this->excel = new AffiliateReportExcelGeneratorHelper($this->date_from, $this->date_to, $this->type);
        $this->excel->generate();

        $this->endLog = Carbon::now();
        $this->mail();
    }

    public function mail()
    {

        $emailNotificationRecipient = env('REPORTS_EMAIL_NOTIFICATION_RECIPIENT', 'marwilburton@hotmail.com');

        $diffInHours = $this->startLog->diffInMinutes($this->endLog).' minute/s';
        $path = $this->excel->getPathToFile();
        $status = $this->excel->getStatus();
        Log::info($path);
        //send email to Burt to notify that Affiliate Report Queue was successfully finished
        Mail::send('emails.affiliate_report_excel', ['startDate' => $this->date_from, 'endDate' => $this->date_to, 'executionDuration' => $diffInHours, 'status' => $status],
            function ($m) use ($emailNotificationRecipient, $path, $status) {
                if ($status) {
                    $m->attach($path);
                }
                $m->from('ariel@engageiq.com', 'Ariel Magbanua');
                $m->to($emailNotificationRecipient, 'Marwil Burton')
                    ->to('admin@engageiq.com', 'EngageIQ Admin')
                    ->subject('Affiliate Reports Generate Excel Successfully Executed!');
            });
    }
}
