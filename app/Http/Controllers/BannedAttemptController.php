<?php

namespace App\Http\Controllers;

use App\BannedAttempt;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BannedAttemptController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $inputs = $request->all();
        // \Log::info($inputs);
        session()->put('banned_attempt_input', $inputs);
        $totalFiltered = BannedAttempt::searchUsers($inputs)->pluck('id')->count();
        $leadUsersData = [];

        $start = $inputs['start'];
        $length = $inputs['length'];

        $ethnic_groups = config('constants.ETHNIC_GROUPS');

        $columns = [
            0 => 'id',
            1 => 'affiliate_id',
            2 => 'revenue_tracker_id',
            3 => 's1',
            4 => 's2',
            5 => 's3',
            6 => 's4',
            7 => 's5',
            8 => 'first_name',
            9 => 'last_name',
            10 => 'email',
            11 => 'zip',
            12 => 'state',
            13 => 'source_url',
            14 => 'created_at',
        ];
        $leadUsers = BannedAttempt::searchUsers($inputs)
            ->orderBy($columns[$inputs['order'][0]['column']], $inputs['order'][0]['dir'])
            ->offset($start)->limit($length)
            ->get();

        foreach ($leadUsers as $user) {
            $data = [];

            //for id html
            $idHTML = '<span id="lead_user-'.$user->id.'-id">'.$user->id.'</span>';
            array_push($data, $idHTML);

            //for affiliate id html
            $affiliateIDHTML = '<span id="lead_user-'.$user->id.'-affiliate_id">'.$user->affiliate_id.'</span>';
            array_push($data, $affiliateIDHTML);

            //for revenue tracker id html
            $revenueTrackerIDHTML = '<span id="lead_user-'.$user->id.'-revenue_tracker_id">'.$user->revenue_tracker_id.'</span>';
            array_push($data, $revenueTrackerIDHTML);

            $s1HTML = '<span id="lead_user-'.$user->id.'-s1">'.$user->s1.'</span>';
            array_push($data, $s1HTML);

            $s2HTML = '<span id="lead_user-'.$user->id.'-s2">'.$user->s2.'</span>';
            array_push($data, $s2HTML);

            $s3HTML = '<span id="lead_user-'.$user->id.'-s3">'.$user->s3.'</span>';
            array_push($data, $s3HTML);

            $s4HTML = '<span id="lead_user-'.$user->id.'-s4">'.$user->s4.'</span>';
            array_push($data, $s4HTML);

            $s5HTML = '<span id="lead_user-'.$user->id.'-s5">'.$user->s5.'</span>';
            array_push($data, $s5HTML);

            //for first name html
            $firstNameHTML = '<span id="lead_user-'.$user->id.'-first_name">'.$user->first_name.'</span>';
            array_push($data, $firstNameHTML);

            //for last name HTML
            $lastNameHTML = '<span id="lead_user-'.$user->id.'-last_name">'.$user->last_name.'</span>';
            array_push($data, $lastNameHTML);

            //for email HTML
            $emailHTML = '<span id="lead_user-'.$user->id.'-email">'.$user->email.'</span>';
            array_push($data, $emailHTML);

            //for zip HTML
            $zipHTML = '<span id="lead_user-'.$user->id.'-zip">'.$user->zip.'</span>';
            array_push($data, $zipHTML);

            //for state HTML
            $stateHTML = '<span id="lead_user-'.$user->id.'-state">'.$user->state.'</span>';
            array_push($data, $stateHTML);

            //for source url HTML
            $sourceURLHTML = '<div style="word-wrap: break-word; width: 190px"><span id="lead_user-'.$user->id.'-source_url">'.$user->source_url.'</span></div>';
            array_push($data, $sourceURLHTML);

            //for created at HTML
            $createdAtHTML = '<span id="lead_user-'.$user->id.'-created_at">'.$user->created_at.'</span>';
            array_push($data, $createdAtHTML);

            //for the additional details
            // $dataBirthDate = 'data-birthdate="'.$user->birthdate.'"';
            // $dataGender = 'data-gender="'.$user->gender.'"';
            // $dataCity = 'data-city="'.$user->city.'"';
            // $dataAddress1 = 'data-address1="'.$user->address1.'"';
            // $dataAddress2 = 'data-address2="'.$user->address2.'"';
            // $dataEthnicity = 'data-ethnicity="'.$ethnic_groups[$user->ethnicity].'"';
            // $dataPhone = 'data-phone="'.$user->phone.'"';
            // $dataIP = 'data-ip="'.$user->ip.'"';
            // $dataIsMobile = 'data-ismobile="false"';
            // if($user->is_mobile==1){
            //     $dataIsMobile = 'data-ismobile="true"';
            // }
            // $dataStatus = 'data-status="'.$user->status.'"';
            // //escape double quotes in data response
            // $escapedDataResponse = str_replace('"', '&quot;', $user->response);
            // $dataResponse = 'data-response="'.$escapedDataResponse.'"';
            // $dataUpdatedAt = 'data-updatedat="'.$user->updated_at.'"';
            // $additionalData = $dataBirthDate.' '.$dataGender.' '.$dataCity.' '.$dataAddress1.' '.$dataAddress2.' '.$dataEthnicity.' '.$dataPhone.' '.$dataIP.' '.$dataIsMobile.' '.$dataStatus.' '.$dataResponse.' '.$dataUpdatedAt.' ';

            $user->ethnic_group = isset($ethnic_groups[$user->ethnicity]) ? $ethnic_groups[$user->ethnicity] : '';

            $moreDetailHTML = '<button data-id="'.$user->id.'" class="more-details btn btn-primary"><span class="glyphicon glyphicon-list-alt"></span></button>';
            $moreDetailHTML .= '<textarea id="st-'.$user->id.'-md" class="hide">'.json_encode($user).'</textarea>';
            array_push($data, $moreDetailHTML);

            array_push($leadUsersData, $data);
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => BannedAttempt::count(),  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $leadUsersData,   // total data array,
        ];

        return response()->json($responseData, 200);
    }

    public function download(): BinaryFileResponse
    {
        $inputs = session()->get('banned_attempt_input');

        $columns = [
            'id',
            'affiliate_id',
            'revenue_tracker_id',
            's1',
            's2',
            's3',
            's4',
            's5',
            'first_name',
            'last_name',
            'email',
            'zip',
            'state',
            'source_url',
            'created_at',
        ];

        $title = 'BannedAttempt_'.Carbon::now()->toDateString();

        $leadUsers = BannedAttempt::searchUsers($inputs)
            ->orderBy($columns[$inputs['order'][0]['column']], $inputs['order'][0]['dir'])
            ->get();

        Excel::create($title, function ($excel) use ($title, $leadUsers) {
            $excel->sheet($title, function ($sheet) use ($leadUsers) {

                // Set auto size for sheet
                $sheet->setAutoSize(true);

                $headers = ['ID', 'Affiliate ID', 'Revenue Tracker ID', 'S1', 'S2', 'S3', 'S4', 'S5', 'First Name', 'Last Name', 'Email', 'Birthdate', 'Gender', 'State', 'City', 'Zip', 'Address1', 'Address2', 'Phone', 'IP Address', 'Ethnicity', 'Is Mobile', 'All Inbox Status', 'Response', 'Created At', 'Updated At'];

                //set up the title header row
                $sheet->appendRow($headers);

                //style the headers
                $sheet->cells('A1:Z1', function ($cells) {

                    // Set font
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                $ethnic_groups = config('constants.ETHNIC_GROUPS');

                foreach ($leadUsers as $user) {
                    if ($user->status == 1) {
                        $stat = 'Sent';
                    } elseif ($user->status == 0) {
                        $stat = 'Pending';
                    } else {
                        $stat = 'Invalid';
                    }

                    $sheet->appendRow([
                        $user->id,
                        $user->affiliate_id,
                        $user->revenue_tracker_id,
                        $user->s1,
                        $user->s2,
                        $user->s3,
                        $user->s4,
                        $user->s5,
                        $user->first_name,
                        $user->last_name,
                        $user->email,
                        $user->birthdate,
                        $user->gender,
                        $user->state,
                        $user->city,
                        $user->zip,
                        $user->address1,
                        $user->address2,
                        $user->phone,
                        $user->ip,
                        $ethnic_groups[$user->ethnicity],
                        $user->is_mobile == 1 ? 'True' : 'False',
                        $stat,
                        $user->response,
                        $user->created_at,
                        $user->updated_at,
                    ]);
                }
            });
        })->store('xls', storage_path('downloads'));

        $file_path = storage_path('downloads').'/'.$title.'.xls';

        if (file_exists($file_path)) {
            return response()->download($file_path, $title.'.xls', [
                'Content-Length: '.filesize($file_path),
            ]);
        } else {
            exit('Requested file does not exist on our server!');
        }
    }
}
