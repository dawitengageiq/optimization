<?php

namespace App\Jobs;

use App\Campaign;
use App\LeadUserRequest;
use App\Setting;
use Carbon\Carbon;
use Curl\Curl;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Log;
use Phar;
use PharData;
use SSH;
use Storage;

class SendPublisherRemoveUserJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $campaigns;

    protected $users;

    protected $emails;

    protected $ids;

    protected $status;

    protected $states = [];

    protected $cc_emails = [];

    protected $unique = [];

    protected $external_emails = [];

    protected $reply_tos = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($state)
    {
        if ($state != '') {
            $this->states = explode(',', $state);
        }

        $this->status = 1;
        $campaigns = Campaign::whereNotNull('advertiser_email')
            ->where('advertiser_email', '!=', '')
            ->with(['advertiser' => function ($q) {
                $q->select(['id', 'company']);

            }])
            ->select(['id', 'advertiser_email', 'name', 'advertiser_id'])->get()->toArray();
        foreach ($campaigns as $c) {
            $this->campaigns[$c['id']] = $c;
        }
        // \Log::info($this->campaigns);
        // \DB::connection('secondary')->enableQueryLog();
        $this->emails = [];
        $reqQry = LeadUserRequest::where('is_sent', 0)->whereNotNull('subscribed_campaigns')->where('request_type', 'like', '%Delet%');
        if (count($this->states) > 0) {
            $reqQry = $reqQry->whereIn('state', $this->states);
        }
        $this->users = $reqQry->get()->toArray();
        // \Log::info(\DB::connection('secondary')->getQueryLog());
        foreach ($this->users as $user) {
            $this->ids[] = $user['id'];
            if (! in_array($user['email'], $this->emails)) {
                $this->emails[] = $user['email'];
                $this->unique[] = $user;
            }
        }
        // \Log::info($this->users);

        // \Log::info(count($this->users));
        // \Log::info(count($this->unique));

        $settings = Setting::whereIn('code', ['ccpaadminemail_recipient', 'optoutexternal_recipient', 'optoutemail_replyto'])->get();
        foreach ($settings as $setting) {
            if ($setting->code == 'ccpaadminemail_recipient') {
                $this->cc_emails = explode(';', trim($setting->description));
            } elseif ($setting->code == 'optoutexternal_recipient') {
                $this->external_emails = explode(';', trim($setting->description));
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
        if (count($this->users) == 0) {
            Log::info('No emails for opt out found.');

            return;
        }

        Log::info('Sending Opt Out Emails...');

        $list = [];
        foreach ($this->unique as $u) {
            $subscribed = $u['subscribed_campaigns'] == 0 ? [] : explode(',', $u['subscribed_campaigns']);
            foreach ($subscribed as $s) {
                if (array_key_exists($s, $this->campaigns)) {
                    $list[$s][] = $u;
                }
            }
        }

        // \Log::info($list);
        //Send Camapaign Advertiser
        $cc_emails = $this->cc_emails;
        $reply_tos = $this->reply_tos;

        foreach ($list as $campaign_id => $users) {
            if (count($users) > 0) {
                $campaign = $this->campaigns[$campaign_id];
                Log::info('Campaign: '.$campaign['name']);
                try {
                    Mail::send('emails.send_publisher_remove_user',
                        ['campaign' => $campaign, 'users' => $users],
                        function ($m) use ($campaign, $cc_emails, $reply_tos) {
                            Log::info($campaign['advertiser_email']);
                            $m->from('admin@engageiq.com', 'CCPA Request');
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

                            $m->to(trim($campaign['advertiser_email']))->subject('EngageIQ Removal of Users CCPA');
                        });
                } catch (Exception $e) {
                    $this->status = 0;
                    Log::info('Sending mail error');
                    Log::info($e);
                }
                sleep(15);
            }
        }

        //External Paths Email
        if (count($this->external_emails) > 0) {
            foreach ($this->external_emails as $external_email) {
                $email = trim($external_email);
                $campaign = false;
                $users = $this->unique;
                try {
                    Mail::send('emails.send_publisher_remove_user',
                        ['campaign' => $campaign, 'users' => $users],
                        function ($m) use ($cc_emails, $email, $reply_tos) {
                            $m->from('admin@engageiq.com', 'CCPA Request');
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

                            $m->to($email)->subject('EngageIQ Removal of Users CCPA');
                        });
                } catch (Exception $e) {
                    $this->status = 0;
                    Log::info('Sending mail error');
                    Log::info($e);
                }
                sleep(15);
            }
        }

        //ALL Inbox
        Log::info('All Inbox');
        foreach ($this->emails as $email) {
            Log::info('Email: '.$email);
            $curl = new Curl();
            $curl->post('https://ao7ulnwxj5.execute-api.us-east-1.amazonaws.com/prod/ccpa?request=datadeletion', [
                'email' => $email,
                'source_id' => '620',
                'api_key' => 'd41fe959_8cfb_48de_98db_e86bb601820e',
            ]);

            if ($curl->error) {
                $this->status = 0;
                Log::info($curl->error_code);
            } else {
                Log::info('All Inbox Error');
                Log::info($curl->response);
            }
        }

        //Live Ramp
        Log::info('Live Ramp');
        $lr = '';
        $date = Carbon::now()->toDateString();
        $time = Carbon::now()->format('h:m:s');
        // $time = Carbon::now()->format('hms'); //Local Testing
        $lr_name = 'web_match_'.$date.'T'.$time.'.psv';
        Log::info('File Name: '.$lr_name);
        foreach ($this->emails as $email) {
            $email = trim(strtolower($email));
            $lr .= md5($email).'|'.sha1($email).'|'.hash('sha256', $email).'|1'."\n";
        }
        Storage::disk('downloads')->put($lr_name, $lr);
        Log::info('.psv created');

        try {
            // $a = new PharData(storage_path('downloads/'.$lr_name.'.tar'), Phar::CURRENT_AS_FILEINFO | Phar::KEY_AS_FILENAME);
            // $a->addFile(storage_path('downloads/'.$lr_name), $lr_name);
            // $a->compress(Phar::GZ);
            // unset($a);
            // Log::info('compressed file created');

            // unlink(storage_path('downloads/'.$lr_name));
            // unlink(storage_path('downloads/'.$lr_name.'.tar'));

            // $filePath = storage_path('downloads/'.$lr_name.'.tar.gz');

            $filePath = storage_path('downloads/'.$lr_name);

            $dest = $filePath.'.gz';
            $error = false;
            if ($fp_out = gzopen($dest, 'wb9')) {
                if ($fp_in = fopen($filePath, 'rb')) {
                    while (! feof($fp_in)) {
                        gzwrite($fp_out, fread($fp_in, 1024 * 512));
                    }
                    fclose($fp_in);
                } else {
                    $this->status = 0;
                }
                gzclose($fp_out);
                Log::info('compressed file created');
                unlink(storage_path('downloads/'.$lr_name));
            } else {
                $this->status = 0;
            }

            Log::info('Sending file to SSH');
            $sftpConnectionConfig = [
                'host' => 'sftp.pippio.com:4222',
                'username' => 'engage_iq_optout',
                'password' => '0TiUgFa2AwavhC1',
                'key' => '',
                'keytext' => '',
                'keyphrase' => '',
                'agent' => '',
            ];

            config(['remote.connections.production' => $sftpConnectionConfig]);

            $filePath = storage_path('downloads/'.$lr_name.'.gz');
            SSH::into('production')->put($filePath, "$lr_name.gz");

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

            Log::info('Sent.');
        } catch (Exception $e) {
            Log::info('Live Ramp Error Encountered');
            Log::info($e);
            $this->status = 0;
        }

        //Update ALL USERS
        LeadUserRequest::whereIn('id', $this->ids)->update(['is_removed' => 1, 'is_sent' => $this->status]);
    }
}
