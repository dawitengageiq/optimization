<?php

namespace App\Jobs;

use App\CakeConversion;
use App\CakeConversionArchive;
use App\Helpers\Repositories\Settings;
use ErrorException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Log;

class ArchiveCakeConversions extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(Settings $settings): void
    {
        if ($this->attempts() > 1) {
            return;
        }

        $days = $settings->getValue('cake_conversions_archiving_age_in_days');
        $counter = 0;

        //Log::info("cake_conversions_archiving_age_in_days: $days");
        //Log::info(CakeConversion::withDaysOld($days)->toSql());

        $cakeConversions = CakeConversion::withDaysOld($days)->get();

        $firstConversionID = 0;
        $lastConversionID = 0;

        //first lead id
        $firstConversion = CakeConversion::withDaysOld($days)->first();

        if (isset($firstConversion)) {
            $firstConversionID = $firstConversion->id;
        }

        $conversionsToArchive = count($cakeConversions);

        foreach ($cakeConversions as $conversion) {
            //Log::info("Archiving lead with lead_id = $lead->id");

            $cakeConversionArchive = new CakeConversionArchive;

            try {
                $cakeConversionArchive->id = $conversion->id;
                $cakeConversionArchive->visitor_id = $conversion->visitor_id;
                $cakeConversionArchive->click_request_session_id = $conversion->click_request_session_id;
                $cakeConversionArchive->click_id = $conversion->click_id;
                $cakeConversionArchive->conversion_date = $conversion->conversion_date;
                $cakeConversionArchive->last_updated = $conversion->last_updated;
                $cakeConversionArchive->click_date = $conversion->click_date;
                $cakeConversionArchive->event_id = $conversion->event_id;
                $cakeConversionArchive->affiliate_id = $conversion->affiliate_id;
                $cakeConversionArchive->advertiser_id = $conversion->advertiser_id;
                $cakeConversionArchive->offer_id = $conversion->offer_id;
                $cakeConversionArchive->offer_name = $conversion->offer_name;
                $cakeConversionArchive->campaign_id = $conversion->campaign_id;
                $cakeConversionArchive->creative_id = $conversion->creative_id;
                $cakeConversionArchive->sub_id_1 = $conversion->sub_id_1;
                $cakeConversionArchive->sub_id_2 = $conversion->sub_id_2;
                $cakeConversionArchive->sub_id_3 = $conversion->sub_id_3;
                $cakeConversionArchive->sub_id_4 = $conversion->sub_id_4;
                $cakeConversionArchive->sub_id_5 = $conversion->sub_id_5;
                $cakeConversionArchive->conversion_ip_address = $conversion->conversion_ip_address;
                $cakeConversionArchive->click_ip_address = $conversion->click_ip_address;
                $cakeConversionArchive->received_amount = $conversion->received_amount;
                $cakeConversionArchive->test = $conversion->test;
                $cakeConversionArchive->transaction_id = $conversion->transaction_id;

                $cakeConversionArchive->save();

                //remove the conversion
                $conversion->delete();
            } catch (ErrorException $e) {
                Log::info($e->getMessage());
                Log::info($e->getCode());
            } catch (QueryException $e) {
                Log::info($e->getMessage());
                Log::info($e->getCode());

                if ($e->getCode() == 23000) {
                    //remove the conversion
                    $conversion->delete();
                }
            }

            $lastConversionID = $conversion->id;
            $counter++;
        }

        $emailNotificationRecipient = env('REPORTS_EMAIL_NOTIFICATION_RECIPIENT', 'marwilburton@hotmail.com');

        //send email to Burt to notify that Atchive Leads Queue was successfully finished
        Mail::send('emails.archive_cake_conversions',
            ['days' => $days, 'conversionsToArchive' => $conversionsToArchive, 'firstConversionID' => $firstConversionID, 'lastConversionID' => $lastConversionID],
            function ($m) use ($emailNotificationRecipient) {
                $m->from('ariel@engageiq.com', 'Ariel Magbanua');
                $m->to($emailNotificationRecipient, 'Marwil Burton');
                // $m->cc('ariel@engageiq.com', 'Ariel Magbanua');
                $m->subject('Archive Cake Conversions Job Queue Successfully Executed!');
            });

        Log::info("There are $counter cake conversions are archived!");
    }
}
