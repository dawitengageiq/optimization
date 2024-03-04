<?php

namespace App\Http\Controllers;

use App\Action;
use App\Role;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Log;

class RoleController extends Controller
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
     */
    public function index(): View
    {
        $actions = Action::all();
        $actionsData = [];

        //restructure to a desired associative array
        foreach ($actions as $action) {
            $actionsData[$action->code] = $action;
        }

        return view('management.role', compact('actionsData'));
    }

    /**
     * Sever side data provider for roles page
     */
    public function roles(Request $request): JsonResponse
    {
        $inputs = $request->all();
        $totalFiltered = Role::count();
        $rolesData = [];

        $param = $inputs['search']['value'];
        $start = $inputs['start'];
        $length = $inputs['length'];

        $columns = [
            0 => 'id',
            1 => 'name',
            2 => 'description',
        ];

        //$rolesWithoutLimit = Role::select('id','name');
        $rolesWithoutLimit = Role::where('name', 'LIKE', "%$param%")
            ->orWhere('description', 'LIKE', "%$param%");

        if (is_numeric($param)) {
            $rolesWithoutLimit->orWhere('id', '=', $param);
        }

        $rolesWithoutLimit->orderBy($columns[$inputs['order'][0]['column']], $inputs['order'][0]['dir']);

        /*
        $selectSQL = "SELECT id, name, description FROM roles ";
        $whereSQL = "";
        $idAdded = false;

        //where
        if(!empty($param))
        {
            //id
            if(is_numeric($param))
            {
                //for id
                $whereSQL = $whereSQL."WHERE id=".(integer)$param;
                $idAdded = true;
            }

            if($idAdded)
            {
                $whereSQL = $whereSQL." OR name LIKE '%".$param."%'";
            }
            else
            {
                $whereSQL = $whereSQL."WHERE name LIKE '%".$param."%'";
            }

            $whereSQL = $whereSQL." OR description LIKE '%".$param."%'";
        }

        $orderSQL = " ORDER BY ".$columns[$inputs['order'][0]['column']]." ".$inputs['order'][0]['dir'];

        $limitSQL = '';
        if($length>1)
        {
            $limitSQL = " LIMIT ".$start.",".$length;
        }

        $sqlWithLimit = $selectSQL.$whereSQL.$orderSQL.$limitSQL;
        $sqlWithoutLimit = $selectSQL.$whereSQL.$orderSQL;

        Log::info('SQL with limit: '.$sqlWithLimit);
        Log::info('SQL without limit: '.$sqlWithoutLimit);

        $roles = DB::select($sqlWithLimit);
        */

        $roles = $rolesWithoutLimit;

        if ($length > 1) {
            $roles->take($length)->skip($start);
        }

        $roles = $roles->get();

        foreach ($roles as $role) {
            $data = [];

            //for id html
            $idHTML = '<span id="roles-'.$role->id.'-id">'.$role->id.'</span>';
            array_push($data, $idHTML);

            //for name html
            $nameHTML = '<span id="roles-'.$role->id.'-name">'.$role->name.'</span>';
            array_push($data, $nameHTML);

            //for name html
            $descriptionHTML = '<span id="roles-'.$role->id.'-description">'.$role->description.'</span>';
            array_push($data, $descriptionHTML);

            //action buttons
            $editButton = '<button class="editRole btn-actions btn btn-primary" title="Edit" data-id="'.$role->id.'"><span class="glyphicon glyphicon-pencil"></span></button>';
            $deleteButton = '<button class="deleteRole btn-actions btn btn-danger" title="Delete" data-id="'.$role->id.'"><span class="glyphicon glyphicon-trash"></span></button>';

            array_push($data, $editButton.$deleteButton);

            array_push($rolesData, $data);
        }

        if (! empty($param) || $param != '') {
            //$totalFiltered = count($affiliatesData);
            //$totalFiltered = count(DB::select($sqlWithoutLimit));
            $totalFiltered = $rolesWithoutLimit->count();
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => Role::count(),  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $rolesData,   // total data array
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created role in storage.
     *
     * notice: dev protect this route with basic authentication
     */
    public function store(Request $request): JsonResponse
    {
        $inputs = $request->all();

        $permissions = json_decode($inputs['permissions']);
        $roleName = $inputs['name'];
        $roleDescription = $inputs['description'];

        //get all actions
        $actions = Action::all();
        $actionsArray = [];

        //create a simple array version
        foreach ($actions as $action) {
            $data = ['id' => $action->id, 'value' => false];
            $actionsArray[$action->code] = $data;
        }

        //override permissions provided from the dialog modal
        foreach ($permissions as $permission) {
            $actionsArray[$permission->code]['value'] = $permission->value;
        }

        //create the role
        $role = Role::create([
            'name' => $roleName,
            'description' => $roleDescription,
        ]);

        foreach ($actionsArray as $actionData) {
            $role->actions()->attach($actionData['id'], ['permitted' => $actionData['value']]);
        }

        $responseData = [
            'message' => 'Role successfully added!',
            'success' => true,
            'role' => ['id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
            ],
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        //get the role
        $role = Role::find($id);

        //get all the data from url
        $inputs = $request->all();
        $permissions = json_decode($inputs['permissions']);

        //get all actions of the role
        $userActions = $role->actions;
        $actionsArray = [];

        //create a simple array version
        foreach ($userActions as $action) {
            $data = [
                'pivot_id' => $action->pivot->id,
                'action_id' => $action->id,
                'value' => $action->pivot->permitted == 1,
            ];

            $actionsArray[$action->code] = $data;
        }

        //override permissions provided from the dialog modal
        foreach ($permissions as $permission) {
            $actionsArray[$permission->code]['action_id'] = $permission->action_id;
            $actionsArray[$permission->code]['value'] = $permission->value;
        }

        // Log::info($actionsArray);

        foreach ($actionsArray as $actionData) {
            //$role->actions()->attach($actionData['id'],['permitted' => $actionData['value']]);

            //check if there is no pivot id if there is no pivot id attach it as a new action for the role
            if (! isset($actionData['pivot_id'])) {
                $role->actions()->attach($actionData['action_id'], ['permitted' => $actionData['value']]);
            } else {
                //if existing just update it
                $role->actions()->updateExistingPivot($actionData['action_id'], ['permitted' => $actionData['value']]);
            }
        }

        //update the role name and description
        $role->name = $inputs['name'];
        $role->description = $inputs['description'];

        $role->save();

        $responseData = [
            'message' => 'Role successfully updated!',
            'success' => true,
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
            ],
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(int $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        $role = Role::find($id);

        $responseData = [
            'message' => 'Role delete failed!',
            'success' => false,
        ];

        if ($role->delete()) {
            $responseData = [
                'message' => 'Role successfully deleted!',
                'success' => true,
            ];
        }

        return response()->json($responseData, 200);
    }

    /**
     * get all actions of a particular role.
     *
     * @return mixed
     */
    public function getActions($id)
    {
        $role = Role::find($id);

        return $role->actions;
    }
}
