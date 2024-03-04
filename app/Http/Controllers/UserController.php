<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Requests\AddUserRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Role;
use App\User;
use DB;
use File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Log;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('restrict_access', ['only' => [
            'index',
        ]]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(): View
    {
        //get all roles
        $roles = Role::pluck('name', 'id')->toArray();

        return view('management.user', compact('roles'));
    }

    public function user($id): View
    {
        $user = User::find($id);

        return view('users.profile', compact('user'));
    }

    /**
     * Server side process for users datatable
     */
    public function users(Request $request): JsonResponse
    {
        $currentUser = $request->user();

        //exclude the current user
        $allAdminCount = User::allAdmin()->count() - 1;

        $inputs = $request->all();
        $totalFiltered = $allAdminCount;
        $usersData = [];

        $param = $inputs['search']['value'];
        $start = $inputs['start'];
        $length = $inputs['length'];

        $columns = [
            0 => 'users.id',
            1 => 'name',
            2 => 'users.email',
            3 => 'users.mobile_number',
            4 => 'users.phone_number',
            5 => 'role_name',
        ];

        $selectSQL = "SELECT users.id, CONCAT(users.title,' ',users.first_name,' ',users.middle_name,' ',users.last_name) AS name, users.email, users.mobile_number, users.phone_number, users.gender, users.position, users.address, users.instant_messaging, users.role_id, roles.name AS role_name FROM users ";
        //do not include the current user's account
        $whereSQL = 'LEFT JOIN roles ON users.role_id = roles.id WHERE users.account_type = 2 AND users.id != '.$currentUser->id;
        $userIDAdded = false;

        //where
        if (! empty($param)) {
            $whereSQL = $whereSQL.' AND ';

            //id
            if (is_numeric($param)) {
                //for id
                $whereSQL = $whereSQL.'(users.id='.intval($param);
                $userIDAdded = true;
            }

            if ($userIDAdded) {
                $whereSQL = $whereSQL." OR users.title LIKE '%".$param."%'";
            } else {
                $whereSQL = $whereSQL."(users.title LIKE '%".$param."%'";
            }

            $whereSQL = $whereSQL." OR users.first_name LIKE '%".$param."%'";
            $whereSQL = $whereSQL." OR users.middle_name LIKE '%".$param."%'";
            $whereSQL = $whereSQL." OR users.last_name LIKE '%".$param."%'";
            $whereSQL = $whereSQL." OR users.email LIKE '%".$param."%'";
            $whereSQL = $whereSQL." OR users.mobile_number LIKE '%".$param."%'";
            $whereSQL = $whereSQL." OR users.phone_number LIKE '%".$param."%'";
            $whereSQL = $whereSQL." OR roles.name LIKE '%".$param."%')";
        }

        $orderSQL = ' ORDER BY '.$columns[$inputs['order'][0]['column']].' '.$inputs['order'][0]['dir'];

        $limitSQL = '';
        if ($length > 1) {
            $limitSQL = ' LIMIT '.$start.','.$length;
        }

        $sqlWithLimit = $selectSQL.$whereSQL.$orderSQL.$limitSQL;
        $sqlWithoutLimit = $selectSQL.$whereSQL.$orderSQL;

        // Log::info('SQL with limit: '.$sqlWithLimit);
        // Log::info('SQL without limit: '.$sqlWithoutLimit);

        $users = DB::select($sqlWithLimit);

        foreach ($users as $user) {
            $data = [];

            //for id html
            $idHTML = '<span id="user-'.$user->id.'-id">'.$user->id.'</span>';
            array_push($data, $idHTML);

            //for name html
            $nameHTML = '<span id="user-'.$user->id.'-full_name">'.$user->name.'</span>';
            array_push($data, $nameHTML);

            //for email HTML
            $emailHTML = '<span id="user-'.$user->id.'-email">'.$user->email.'</span>';
            array_push($data, $emailHTML);

            //for mobile HTML
            $mobileHTML = '<span id="user-'.$user->id.'-mobile_number">'.$user->mobile_number.'</span>';
            array_push($data, $mobileHTML);

            //for phone HTML
            $phoneHTML = '<span id="user-'.$user->id.'-phone_number">'.$user->phone_number.'</span>';
            array_push($data, $phoneHTML);

            //for phone HTML
            $roleNameHTML = '<span id="user-'.$user->id.'-role_name">'.$user->role_name.'</span>';
            array_push($data, $roleNameHTML);

            //action buttons
            $hiddenUserID = '<input type="hidden" id="user-'.$user->id.'-id" value="'.$user->id.'"/>';
            $gender = '<input type="hidden" id="user-'.$user->id.'-gender" value="'.$user->gender.'"/>';
            $position = '<input type="hidden" id="user-'.$user->id.'-position" value="'.$user->position.'"/>';
            $roleID = '<input type="hidden" id="user-'.$user->id.'-role_id" value="'.$user->role_id.'"/>';
            $address = '<input type="hidden" id="user-'.$user->id.'-address" value="'.$user->address.'"/>';
            $instantMessaging = '<input type="hidden" id="user-'.$user->id.'-instant_messaging" value="'.$user->instant_messaging.'"/>';

            $editButton = '<button class="editUser btn-actions btn btn-primary" title="Edit" data-id="'.$user->id.'"><span class="glyphicon glyphicon-pencil"></span></button>';
            $deleteButton = '<button class="deleteUser btn-actions btn btn-danger" title="Delete" data-id="'.$user->id.'"><span class="glyphicon glyphicon-trash"></span></button>';

            array_push($data, $hiddenUserID.$gender.$position.$roleID.$address.$instantMessaging.$editButton.$deleteButton);

            array_push($usersData, $data);
        }

        if (! empty($param) || $param != '') {
            //$totalFiltered = count($affiliatesData);
            $totalFiltered = count(DB::select($sqlWithoutLimit));
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $allAdminCount,  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $usersData,   // total data array
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
     * Store a newly created contact in storage.
     */
    public function store(AddUserRequest $request): JsonResponse
    {
        $inputs = $request->all();
        //encrypt password
        $inputs['password'] = Hash::make($inputs['password']);
        $inputs['account_type'] = 2;

        // Log::info($inputs);

        $user = User::create($inputs);

        return response()->json(['status' => 200, 'id' => $user->id]);
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
        //
    }

    /**
     * Update the specified user resource in storage.
     */
    public function update(UpdateUserRequest $request, $id): JsonResponse
    {
        $inputs = $request->all();

        $title = $inputs['title'];

        $first_name = $inputs['first_name'];
        $middle_name = $inputs['middle_name'];
        $last_name = $inputs['last_name'];
        $gender = $inputs['gender'];
        $position = $inputs['position'];

        $password = '';

        if (isset($inputs['password']) && ! empty($inputs['password'])) {
            $password = $inputs['password'];
        }

        $address = $inputs['address'];
        $email = $inputs['email'];
        $mobile_number = $inputs['mobile_number'];
        $phone_number = $inputs['phone_number'];
        $instant_messaging = $inputs['instant_messaging'];
        $role_id = $inputs['role_id'];

        $user = User::find($id);

        $user->title = $title;
        $user->first_name = $first_name;
        $user->middle_name = $middle_name;
        $user->last_name = $last_name;
        $user->gender = $gender;
        $user->position = $position;

        if (! empty($password)) {
            $user->password = Hash::make($password);
        }

        $user->address = $address;
        $user->email = $email;
        $user->mobile_number = $mobile_number;
        $user->phone_number = $phone_number;
        $user->instant_messaging = $instant_messaging;
        $user->role_id = $role_id;

        $user->save();

        return response()->json(['status' => 200, 'id' => $user->id]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Requests\UpdateUserProfileRequest $request): JsonResponse
    {
        $inputs = $request->all();

        //find the user
        $user = User::find($inputs['id']);

        $title = $inputs['title'];
        $first_name = $inputs['first_name'];
        $middle_name = $inputs['middle_name'];
        $last_name = $inputs['last_name'];
        $gender = $inputs['gender'];
        $position = $inputs['position'];
        $address = $inputs['address'];
        $email = $inputs['email'];
        $mobile_number = $inputs['mobile_number'];
        $phone_number = $inputs['phone_number'];
        $instant_messaging = $inputs['instant_messaging'];

        $user->title = $title;
        $user->first_name = $first_name;
        $user->middle_name = $middle_name;
        $user->last_name = $last_name;
        $user->gender = $gender;
        $user->position = $position;
        $user->address = $address;
        $user->email = $email;
        $user->mobile_number = $mobile_number;
        $user->phone_number = $phone_number;
        $user->instant_messaging = $instant_messaging;

        $user->save();

        return response()->json(['status' => 200, 'id' => $user->id]);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $inputs = $request->all();
        $newPassword = $inputs['password'];
        $user = $request->user();

        $user->password = Hash::make($newPassword);
        $user->save();

        return response()->json(['status' => 200, 'message' => 'password was successfully changed!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
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
        }

        return response()->json($responseMessage);
    }

    /**
     * user profile image upload
     */
    public function profile_image_upload(Request $request): JsonResponse
    {
        $user = $request->user();
        $inputs = $request->all();

        $currentProfileImage = $inputs['current_profile_image'];
        // Log::info('current profile image: '.$currentProfileImage);

        $responseMessage = [
            'message' => 'upload successful!',
            'filename' => '',
        ];

        if ($request->hasFile('profileImageInput')) {
            $file = $request->file('profileImageInput');
            $ext = $file->getClientOriginalExtension();
            $filename = md5(time()).'.'.$ext;

            $oldProfileName = basename($user->profile_image);
            $oldProfileImagePath = public_path().'\images\profile\\'.$oldProfileName;

            //delete the previous image
            if (! empty($oldProfileName) && file_exists($oldProfileImagePath)) {
                unlink($oldProfileImagePath);
            }

            $destinationPath = config('constants.PROFILE_IMAGE_DIR');
            $file->move($destinationPath, $filename);
            $completeLRImagePath = url().'/'.$destinationPath.$filename;

            $user->profile_image = $completeLRImagePath;
            $user->save();

            $responseMessage['filename'] = $filename;
        } elseif (! $request->hasFile('profileImageInput') && ! empty($currentProfileImage)) {
            $responseMessage = [
                'message' => 'Please choose a new image if you want to change your profile image!',
            ];
        } else {
            $oldProfileName = basename($user->profile_image);
            $oldProfileImagePath = public_path().'\images\profile\\'.$oldProfileName;
            // Log::info('oldProfileImagePath: '.$oldProfileImagePath);
            // Log::info('fileExist: '.file_exists($oldProfileImagePath));

            //delete the previous image
            if (! empty(basename($user->profile_image)) && file_exists($oldProfileImagePath)) {
                unlink($oldProfileImagePath);
            }

            $user->profile_image = '';
            $user->save();

            $responseMessage = [
                'message' => 'Your profile was removed!',
            ];
        }

        return response()->json($responseMessage, 200);
    }
}
