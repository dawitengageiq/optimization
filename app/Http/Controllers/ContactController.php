<?php

namespace App\Http\Controllers;

use App\Commands\GetUserActionPermission;
use App\Http\Requests\AddContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\User;
use Bus;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Log;

class ContactController extends Controller
{
    protected $canEdit = false;

    protected $canDelete = false;

    public function __construct()
    {
        $this->middleware('auth');

        //get the user
        $user = auth()->user();

        $this->canEdit = Bus::dispatch(new GetUserActionPermission($user, 'use_edit_contact'));
        $this->canDelete = Bus::dispatch(new GetUserActionPermission($user, 'use_delete_contact'));
    }

    /**
     * Server side processing for contacts data table
     */
    public function index(Request $request): JsonResponse
    {
        $inputs = $request->all();
        $allNonAdminCount = User::allNonAdmin()->count();

        $totalFiltered = $allNonAdminCount;
        $contactsData = [];

        $param = $inputs['search']['value'];
        $start = $inputs['start'];
        $length = $inputs['length'];

        $columns = [
            // datatable column index  => database column name
            0 => 'name',
            1 => 'position',
            2 => 'company',
            3 => 'email',
            4 => 'mobile_number',
            5 => 'phone_number',
        ];

        // $selectSQL = "SELECT id, affiliate_id, advertiser_id, CONCAT(title,' ',first_name,' ',middle_name,' ',last_name) AS name, position, email, mobile_number, phone_number, gender, address, instant_messaging FROM users ";
        $selectSQL = "SELECT users.id, affiliate_id, advertiser_id, CONCAT(title,' ',first_name,' ',middle_name,' ',last_name) AS name,
            position, email, mobile_number, phone_number, gender, users.address, instant_messaging,
            CASE WHEN affiliate_id IS NOT NULL AND advertiser_id IS NULL THEN aff.company
            WHEN advertiser_id IS NOT NULL AND affiliate_id IS NULL THEN adv.company
            WHEN advertiser_id IS NOT NULL AND affiliate_id IS NOT NULL THEN CONCAT(aff.company,', ',adv.company)
            ELSE NULL END AS company
            FROM users
            LEFT JOIN affiliates aff ON affiliate_id = aff.id
            LEFT JOIN advertisers adv ON advertiser_id = adv.id ";
        $whereSQL = 'WHERE account_type=1 ADVERTISER_AFFILIATE_CRITERIA SEARCH_CRITERIA';

        $advertiserAffiliateCriteria = '';
        //determine if user is advertiser or affiliate and get the id from the request
        if (isset($inputs['awesome_advertiser_id'])) {
            $advertiserAffiliateCriteria = 'AND advertiser_id='.$inputs['awesome_advertiser_id'];
        } elseif (isset($inputs['awesome_affiliate_id'])) {
            $advertiserAffiliateCriteria = 'AND affiliate_id='.$inputs['awesome_affiliate_id'];
        }

        //apply the ADVERTISER_AFFILIATE_CRITERIA
        $whereSQL = str_replace('ADVERTISER_AFFILIATE_CRITERIA', $advertiserAffiliateCriteria, $whereSQL);

        $searchCriteria = '';

        //where
        if (! empty($param) || $param != '') {

            $searchCriteria = $searchCriteria."AND (title LIKE '%".$param."%'";

            $searchCriteria = $searchCriteria." OR position LIKE '%".$param."%'";
            $searchCriteria = $searchCriteria." OR first_name LIKE '%".$param."%'";
            $searchCriteria = $searchCriteria." OR middle_name LIKE '%".$param."%'";
            $searchCriteria = $searchCriteria." OR last_name LIKE '%".$param."%'";
            $searchCriteria = $searchCriteria." OR email LIKE '%".$param."%'";
            $searchCriteria = $searchCriteria." OR mobile_number LIKE '%".$param."%'";
            $searchCriteria = $searchCriteria." OR adv.company LIKE '%".$param."%'";
            $searchCriteria = $searchCriteria." OR aff.company LIKE '%".$param."%')";
        }

        //apply the SEARCH_CRITERIA
        $whereSQL = str_replace('SEARCH_CRITERIA', $searchCriteria, $whereSQL);

        //$orderSQL = " ORDER BY affiliates.id ASC";
        $orderSQL = ' ORDER BY '.$columns[$inputs['order'][0]['column']].' '.$inputs['order'][0]['dir'];

        $limitSQL = '';
        if ($length > 1) {
            $limitSQL = ' LIMIT '.$start.','.$length;
        }

        $sqlWithLimit = $selectSQL.$whereSQL.$orderSQL.$limitSQL;
        $sqlWithoutLimit = $selectSQL.$whereSQL.$orderSQL;

        // Log::info($sqlWithLimit);

        $contacts = DB::select($sqlWithLimit);

        foreach ($contacts as $contact) {
            $data = [];

            //create full name html
            $fullNameHTML = '<span id="contact-'.$contact->id.'-full_name"">'.$contact->name.'</span>';
            array_push($data, $fullNameHTML);

            //create position html
            $positionHTML = '<span id="contact-'.$contact->id.'-position">'.$contact->position.'</span>';
            array_push($data, $positionHTML);

            //create position html
            $companyHTML = '<span id="contact-'.$contact->id.'-company">'.$contact->company.'</span>';
            array_push($data, $companyHTML);

            //create email html
            $emailHTML = '<span id="contact-'.$contact->id.'-email">'.$contact->email.'</span>';
            array_push($data, $emailHTML);

            //create mobile html
            $mobileHTML = '<span id="contact-'.$contact->id.'-mobile_number">'.$contact->mobile_number.'</span>';
            array_push($data, $mobileHTML);

            //create phone html
            $phoneHTML = '<span id="contact-'.$contact->id.'-phone_number">'.$contact->phone_number.'</span>';
            array_push($data, $phoneHTML);

            //construct the action buttons
            $hiddenAffiliateID = '<input type="hidden" id="contact-'.$contact->id.'-affiliate_id" value="'.$contact->affiliate_id.'"/>';
            $hiddenAdvertiserID = '<input type="hidden" id="contact-'.$contact->id.'-advertiser_id" value="'.$contact->advertiser_id.'"/>';
            $gender = '<input type="hidden" id="contact-'.$contact->id.'-gender" value="'.$contact->gender.'"/>';
            $address = '<input type="hidden" id="contact-'.$contact->id.'-address" value="'.$contact->address.'"/>';
            $instantMessaging = '<input type="hidden" id="contact-'.$contact->id.'-instant_messaging" value="'.$contact->instant_messaging.'"/>';

            //determine if current user do have edit and delete permissions for contacts
            $editButton = '';
            $deleteButton = '';

            if ($this->canEdit) {
                $editButton = '<button class="edit-contact btn-actions btn btn-primary" title="Edit" data-id="'.$contact->id.'"><span class="glyphicon glyphicon-pencil"></span></button>';
            }

            if ($this->canDelete) {
                $deleteButton = '<button class="delete-contact btn-actions btn btn-danger" data-id="'.$contact->id.'"><span class="glyphicon glyphicon-trash"></span></button>';
            }

            array_push($data, $hiddenAffiliateID.$hiddenAdvertiserID.$gender.$address.$instantMessaging.$editButton.$deleteButton);

            array_push($contactsData, $data);
        }

        if (! empty($param) || $param != '') {
            $totalFiltered = count(DB::select($sqlWithoutLimit));
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $allNonAdminCount,  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $contactsData,   // total data array
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        //
    }

    /**
     * Store a newly created contact in storage. This is for user type only and not admin
     */
    public function store(
        AddContactRequest $request,
        \App\Http\Services\UserActionLogger $userAction
    ): JsonResponse {
        $inputs = $request->all();
        //encrypt password
        $inputs['password'] = Hash::make($inputs['password']);
        $inputs['account_type'] = 1; // 1 value indicates that the user is not a engageiq admin user

        //remove affiliate_id if it is 0
        if (isset($inputs['affiliate_id']) && $inputs['affiliate_id'] == 0) {
            // Log::info('affiliate_id: '.$inputs['affiliate_id']);
            unset($inputs['affiliate_id']);
        }

        //remove advertiser_id if it is 0
        if (isset($inputs['advertiser_id']) && $inputs['advertiser_id'] == 0) {
            // Log::info('advertiser_id: '.$inputs['advertiser_id']);
            unset($inputs['advertiser_id']);
        }

        $user = User::create($inputs);

        //Action Logger
        $userAction->logger(1, null, $user->id, null, $user->toArray(), 'Add contact: '.$user->email);

        $responseData = [
            'id' => $user->id,
            'canEdit' => $this->canEdit,
            'canDelete' => $this->canDelete,
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): Response
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateContactRequest $request,
        \App\Http\Services\UserActionLogger $userAction,
        $id
    ): JsonResponse {
        $inputs = $request->all();

        $title = $inputs['title'];

        $affiliate_id = null;
        $advertiser_id = null;

        if (isset($inputs['affiliate_id']) && $inputs['affiliate_id'] != 0) {
            $affiliate_id = $inputs['affiliate_id'];
        }

        if (isset($inputs['advertiser_id']) && $inputs['advertiser_id'] != 0) {
            $advertiser_id = $inputs['advertiser_id'];
        }

        $first_name = $inputs['first_name'];
        $middle_name = $inputs['middle_name'];
        $last_name = $inputs['last_name'];
        $gender = $inputs['gender'];
        $position = $inputs['position'];
        //$password = $inputs['password'];
        $address = $inputs['address'];
        $email = $inputs['email'];
        $mobile_number = $inputs['mobile_number'];
        $phone_number = $inputs['phone_number'];
        $instant_messaging = $inputs['instant_messaging'];

        $user = User::find($id);

        $current_state = $user->toArray(); //For Logging

        $user->title = $title;
        $user->affiliate_id = $affiliate_id;
        $user->advertiser_id = $advertiser_id;
        $user->first_name = $first_name;
        $user->middle_name = $middle_name;
        $user->last_name = $last_name;
        $user->gender = $gender;
        $user->position = $position;
        //$user->password = Hash::make($password);
        $user->address = $address;
        $user->email = $email;
        $user->mobile_number = $mobile_number;
        $user->phone_number = $phone_number;
        $user->instant_messaging = $instant_messaging;

        $user->save();

        //Action Logger
        $userAction->logger(1, null, $user->id, $current_state, $user->toArray(), 'Update contact: '.$user->email);

        return response()->json(['id' => $user->id], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        \App\Http\Services\UserActionLogger $userAction,
        $id
    ): JsonResponse {
        $responseMessage = [
            'status' => 200,
            'delete_status' => true,
            'message' => 'user successfully deleted!',
        ];

        $user = User::find($id);

        if (! $user->exists()) {
            $responseMessage['delete_status'] = false;
            $responseMessage['message'] = 'User not found!';
        } elseif (! $user->delete()) {
            $responseMessage['delete_status'] = false;
            $responseMessage['message'] = 'User not found!';
        } else {
            //Action Logger
            $userAction->logger(1, null, $id, $user->toArray(), null, 'Delete contact: '.$user->email);
        }

        return response()->json($responseMessage, 200);
    }
}
