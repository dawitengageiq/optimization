<?php

namespace App\Jobs;

use App\LeadUser;
use App\LeadUserRequest;
use Carbon\Carbon;
use Curl\Curl;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class GetOneTrustEmailJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $date;

    protected $end_date;

    protected $size;

    protected $states;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($date)
    {
        $this->date = date('Ymd', strtotime($date));
        $this->end_date = Carbon::now()->endOfDay();
        $this->size = 100;
        $this->states = array_map('strtolower', config('constants.US_STATES_ABBR'));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Get One Trust Date: '.$this->date);
        $page = 0;
        $get = true;
        $emails = [];
        $users = [];
        while ($get) {
            $output = $this->curler('https://app.onetrust.com/api/datasubject/v2/requestqueues/en-us?size='.$this->size.'&sort=createdDate,asc&modifieddate='.$this->date.'&page='.$page);

            foreach ($output['content'] as $req) {
                if ($req['status'] != 'UNVERIFIED') {
                    if (! in_array($req['email'], $emails)) {
                        $emails[] = $req['email'];
                    }
                    $requestQueueRefId = $req['requestQueueRefId'];
                    $user_additional_data = $this->curler('https://app.onetrust.com/api/datasubject/v2/requestqueues/'.$requestQueueRefId.'/language/en-us');
                    $additional_data = json_decode($user_additional_data['additionalData'], true);

                    $state = '';
                    if (isset($additional_data['State'])) {
                        $state = trim($additional_data['State']);
                        if (strlen($state) > 2) {
                            $state = array_search(strtolower($state), $this->states) ? array_search(strtolower($state), $this->states) : $state;
                        }
                    }

                    $users[] = [
                        'email' => $req['email'],
                        'status' => $req['status'],
                        'request' => $req['requestTypes'][0],
                        'first_name' => $req['firstName'],
                        'last_name' => $req['lastName'],
                        'request_date' => date('Y-m-d H:i:s', strtotime($req['dateCreated'])),
                        'state' => $state,
                        'zip' => isset($additional_data['Zip']) ? $additional_data['Zip'] : '',
                        'city' => isset($additional_data['City']) ? $additional_data['City'] : '',
                        'address' => isset($additional_data['Address']) ? $additional_data['Address'] : '',
                        'phone_number' => isset($additional_data['Phone Number']) ? $additional_data['Phone Number'] : '',
                    ];
                }
            }

            $get = $output['last'] ? false : true;
            $page++;
        }

        // $exist = LeadUserRequest::lists('email')->toArray();
        // $new = array_diff($emails,$exists);

        // $lead_users = LeadUser::whereIn('email',$emails)->whereBetween('created_at', ['2018-01-01 00:00:00', $this->end_date])->groupBy('email')
        //     ->select(['email', 'state', 'city', 'zip', 'address1'])->get()->toArray();

        // $lead_data = array();
        // foreach($lead_users as $lu) {
        //     $lead_data[$lu['email']] = array(
        //         'state' => $lu['state'],
        //         'city' => $lu['city'],
        //         'zip' => $lu['zip'],
        //         'address' => $lu['address1']
        //     );
        // }

        if (count($users) > 0) {
            Log::info('User Count: '.count($users));
            foreach ($users as $key => $user) {
                $request_type = $user['request'];
                $email = $user['email'];
                $first_name = $user['first_name'];
                $last_name = $user['last_name'];
                $state = $user['state'];
                $city = $user['city'];
                $zip = $user['zip'];
                $address = $user['address'];
                $request_date = $user['request_date'];
                $phone_number = $user['phone_number'];

                $uu = LeadUserRequest::firstOrNew([
                    'email' => $email,
                    'request_date' => $request_date,
                ]);

                $uu->request_type = $request_type;
                $uu->email = $email;
                $uu->first_name = $first_name;
                $uu->last_name = $last_name;
                $uu->state = $state;
                $uu->city = $city;
                $uu->zip = $zip;
                $uu->address = $address;
                $uu->request_date = $request_date;
                $uu->phone_number = $phone_number;
                $uu->save();
            }
        } else {
            Log::info('No users');
        }

        Log::info('One Trust Process Done');

    }

    public function curler($url)
    {
        Log::info($url);

        $callCounter = 0;
        $response = null;
        do {
            $curl = new Curl();
            $curl->setOpt(CURLOPT_HTTPHEADER, [
                'APIKey: 5058555e7eaf1cd20f4d7394659ac2b7',
            ]);
            $curl->get($url);

            if ($curl->error) {
                Log::info('OneTrust API Error!');
                Log::info($curl->error_message);
                $callCounter++;
            } else {
                $response = json_decode($curl->response, true);
            }

            //stop the process when budget breached
            if ($callCounter == 10) {
                Log::info('ONE TRUST API call limit reached!');
                $curl->close();
                break;
            }

        } while ($curl->error);

        $curl->close();

        return $response;
    }
}
