<?php

namespace App\Jobs;

use App\LeadUser;
use App\LeadUserRequest;
use App\Setting;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Log;
use Maatwebsite\Excel\Facades\Excel;

class SendUserOneTrustEmailJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $cc_emails = [];

    protected $reply_tos = [];

    protected $end_date;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->end_date = Carbon::now()->endOfDay();
        $settings = Setting::whereIn('code', ['ccpaadminemail_recipient', 'optoutexternal_recipient', 'optoutemail_replyto'])->get();
        foreach ($settings as $setting) {
            if ($setting->code == 'ccpaadminemail_recipient') {
                $this->cc_emails = explode(';', trim($setting->description));
            } elseif ($setting->code == 'optoutemail_replyto') {
                $this->reply_tos = explode(';', trim($setting->description));
            }
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $done = [];
        $cc_emails = $this->cc_emails;
        $reply_tos = $this->reply_tos;

        Log::info('Get requests.');
        $requests = LeadUserRequest::where('is_sent', 0)->where(function ($q) {
            $q->where('request_type', 'like', '%portability%')
                ->orWhere('request_type', 'like', '%disclosure%');
        })->groupBy('email')->groupBy('request_type')->get();

        if (count($requests) == 0) {
            Log::info('No request found.');

            return;
        }

        $request_emails = [];
        foreach ($requests as $user) {
            $request_emails[] = $user->email;
        }

        Log::info('get lead user data');
        $lead_users = LeadUser::whereIn('email', $request_emails)->whereBetween('created_at', ['2018-01-01 00:00:00', $this->end_date])->get();
        Log::info('get lead user done');
        $survey_takers = [];
        foreach ($lead_users as $user) {
            $survey_takers[$user->email] = $user;
        }

        //Log::info($requests);
        Log::info('Start sending...');
        foreach ($requests as $request) {
            Log::info($request->id.' - '.$request->request_type.' - '.$request->email);
            if (stripos($request->request_type, 'disclosure') !== false) {
                $subject = 'Epic Demand | Information Disclosure Request';
                $blade = 'user_information_disclosure_request';
            } else {
                $subject = 'Epic Demand | Data Portability Request';
                $blade = 'user_data_portability_request';
            }

            try {
                $hasInfo = false;
                $email = $request->email;
                $leadUser = $request;
                if (isset($survey_takers[$email])) {
                    $hasInfo = true;
                    $leadUser = $survey_takers[$email];
                }

                $feedTitle = 'user_data';
                \Config::set('excel.csv.enclosure', '');
                Excel::create($feedTitle, function ($excel) use ($leadUser, $hasInfo) {
                    $excel->sheet('data', function ($sheet) use ($leadUser, $hasInfo) {
                        //set up the title header row
                        $sheet->appendRow([
                            'First Name',
                            'Last Name',
                            'Email',
                            'Address',
                            'Zip',
                            'City',
                            'State',
                            'DOB',
                            'Gender',
                            'Phone',
                            'IP Address',
                            'Browser Info',
                        ]);
                        $sheet->appendRow([
                            $leadUser->first_name,
                            $leadUser->last_name,
                            $leadUser->email,
                            $hasInfo ? $leadUser->address1 : $leadUser->address,
                            $leadUser->zip,
                            $leadUser->city,
                            $leadUser->state,
                            $hasInfo ? $leadUser->birthdate : '',
                            $hasInfo ? $leadUser->gender : '',
                            $hasInfo ? $leadUser->phone : $leadUser->phone_number,
                            $hasInfo ? $leadUser->ip : '',
                            $hasInfo ? $leadUser->address2 : '',
                        ]);
                    });
                })->store('csv', storage_path('downloads'));
                $file_path = storage_path('downloads').'/'.$feedTitle.'.csv';

                Mail::send('emails.'.$blade,
                    ['request' => $request, 'user' => $user],
                    function ($m) use ($request, $cc_emails, $reply_tos, $file_path, $subject) {
                        $m->from('admin@engageiq.com', 'Epic Demand');
                        if (count($cc_emails) > 0) {
                            foreach ($cc_emails as $cc) {
                                if ($cc != '') {
                                    $m->cc(trim($cc));
                                }
                            }
                        }

                        if (count($reply_tos) > 0) {
                            foreach ($reply_tos as $to) {
                                if ($to != '') {
                                    $m->replyTo(trim($to));
                                }
                            }
                        }

                        $m->attach($file_path);

                        $m->to(trim($request['email']))->subject('Epic Demand | '.$subject);
                    });

                $done[] = $request->id;

                if ($hasInfo) {
                    Storage::disk('downloads')->delete($feedTitle.'.csv');
                }
            } catch (Exception $e) {
                $this->status = 0;
                Log::info('Sending mail error');
                Log::info($e);
            }
            sleep(15);
        }

        Log::info('Update requests');
        LeadUserRequest::whereIn('id', $done)->update(['is_removed' => 2, 'is_sent' => 1]);
        Log::info('Send User One Trust Email Job Done');
    }
}
