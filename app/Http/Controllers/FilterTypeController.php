<?php

namespace App\Http\Controllers;

use App\Commands\GetUserActionPermission;
use App\FilterType;
use Bus;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FilterTypeController extends Controller
{
    protected $canEdit = false;

    protected $canDelete = false;

    public function __construct()
    {
        $this->middleware('auth', ['except' => ['getFilterTypeQuestion']]);
        $this->middleware('admin', ['except' => ['getFilterTypeQuestion']]);

        //get the user
        $user = auth()->user();

        $this->canEdit = Bus::dispatch(new GetUserActionPermission($user, 'use_edit_filter_type'));
        $this->canDelete = Bus::dispatch(new GetUserActionPermission($user, 'use_delete_filter_type'));
    }

    public function index(Request $request): JsonResponse
    {
        $inputs = $request->all();
        $totalFiltered = FilterType::count();
        $filterTypesData = [];

        $param = $inputs['search']['value'];
        $start = $inputs['start'];
        $length = $inputs['length'];

        $columns = [
            // datatable column index  => database column name
            0 => 'type',
            1 => 'name',
            2 => 'status',
        ];

        $selectSQL = 'SELECT id, type, name, status, image FROM filter_types ';
        $whereSQL = '';

        //where
        if (! empty($param) || $param != '') {
            $whereSQL = 'WHERE ';

            $whereSQL = $whereSQL."type LIKE '%".$param."%'";
            $whereSQL = $whereSQL." OR name LIKE '%".$param."%'";

            $status = -1;

            //determine the status param
            if (strcasecmp($param, 'active') == 0 || stripos('active', $param) !== false) {
                $status = 1;
            } elseif (strcasecmp($param, 'inactive') == 0 || stripos('inactive', $param) !== false) {
                $status = 0;
            }

            if ($status >= 0) {
                $whereSQL = $whereSQL.' OR status='.$status;
            }
        }

        //$orderSQL = " ORDER BY affiliates.id ASC";
        $orderSQL = ' ORDER BY '.$columns[$inputs['order'][0]['column']].' '.$inputs['order'][0]['dir'];

        $limitSQL = '';
        if ($length > 1) {
            $limitSQL = ' LIMIT '.$start.','.$length;
        }

        $sqlWithLimit = $selectSQL.$whereSQL.$orderSQL.$limitSQL;
        $sqlWithoutLimit = $selectSQL.$whereSQL.$orderSQL;

        $filterTypes = DB::select($sqlWithLimit);

        foreach ($filterTypes as $filterType) {
            $data = [];

            //create the type html
            $typeHTML = '<span id="filtertype-'.$filterType->id.'-type">'.$filterType->type.'</span>';
            array_push($data, $typeHTML);

            //create the name html
            $nameHTML = '<span id="filtertype-'.$filterType->id.'-name">'.$filterType->name.'</span>';
            array_push($data, $nameHTML);

            //determine the status string
            $statusString = $filterType->status == 1 ? 'Active' : 'Inactive';

            //create the status html
            $statusHTML = '<span id="filtertype-'.$filterType->id.'-status" data-status="'.$filterType->status.'">'.$statusString.'</span>';
            array_push($data, $statusHTML);

            //determine if current user do have edit and delete permissions for contacts
            $editButton = '';
            $deleteButton = '';
            $image = '<textarea style="display:none" id="filtertype-'.$filterType->id.'-image">'.$filterType->image.'</textarea>';
            // if($filterType->type == 'custom') {
            //     $editButton = '';
            //     $deleteButton = '';
            // }else {
            if ($this->canEdit) {
                $editButton = '<button class="edit-filtertype btn-actions btn btn-primary" title="Edit" data-id="'.$filterType->id.'"><span class="glyphicon glyphicon-pencil"></span></button>';
            }

            if ($this->canDelete) {
                $deleteButton = '<button class="delete-filtertype btn-actions btn btn-danger" title="Delete" data-id="'.$filterType->id.'"><span class="glyphicon glyphicon-trash"></span></button>';
            }
            // }
            array_push($data, $image.$editButton.$deleteButton);

            array_push($filterTypesData, $data);
        }

        if (! empty($param) || $param != '') {
            //$totalFiltered = count($affiliatesData);
            $totalFiltered = count(DB::select($sqlWithoutLimit));
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => FilterType::count(),  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $filterTypesData,   // total data array
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
     * Store a newly created resource in storage.
     */
    public function store(
        Request $request,
        \App\Http\Services\UserActionLogger $userAction
    ): JsonResponse {
        $fileName = null;

        if ($request->hasFile('icon')) {

            $file = $request->file('icon');

            $ext = $file->getClientOriginalExtension();
            $filename = md5(time()).".$ext";

            $destinationPath = 'images/filter_types/';
            //Storage::disk('local')->put($destinationPath.$fileName, File::get($file));

            //$filename = $file->getClientOriginalName();
            $uploadSuccess = $file->move($destinationPath, $filename);
            $fileName = url().'/'.$destinationPath.$filename;
        } else {
            if ($request->input('icon') != '' || ! is_null($request->input('icon'))) {
                $url = $request->input('icon');
                $ext = pathinfo($url, PATHINFO_EXTENSION);
                if ($ext == '') {
                    $ext = 'jpg';
                }
                $filename = md5(time()).".$ext";
                $file = file_get_contents($url);
                $save = file_put_contents('images/filter_types/'.$filename, $file);
                $fileName = url().'/images/filter_types/'.$filename;
            }
        }

        $filterType = new FilterType;
        $filterType->type = $request->input('type');
        $filterType->name = $request->input('name');
        $filterType->status = $request->input('status');
        $filterType->image = $fileName;
        $filterType->save();

        //Action Logger
        $userAction->logger(5, null, $filterType->id, null, $filterType->toArray(), 'Add filter type: '.$filterType->name);

        $responseData = [
            'id' => $filterType->id,
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        Request $request,
        \App\Http\Services\UserActionLogger $userAction,
        int $id
    ): JsonResponse {
        $filterType = FilterType::find($id);

        $current_state = $filterType->toArray(); //For Logging

        $filterType->type = $request->input('type');
        $filterType->name = $request->input('name');
        $filterType->status = $request->input('status');

        $fileName = '';
        if ($request->hasFile('icon')) {
            //remove existing image
            if ($filterType->image != '' || ! is_null($filterType->image)) {
                $path_parts = pathinfo($filterType->image);
                $img_path = public_path('images\filter_types\\'.$path_parts['basename']);
                if (file_exists($img_path)) {
                    unlink($img_path);
                }
            }

            $file = $request->file('icon');

            $ext = $file->getClientOriginalExtension();
            $filename = md5(time()).".$ext";

            $destinationPath = 'images/filter_types/';
            $uploadSuccess = $file->move($destinationPath, $filename);
            $fileName = url().'/'.$destinationPath.$filename;
        } else {
            if ($request->input('icon') != '' || ! is_null($request->input('icon'))) {
                //remove existing image
                if ($filterType->image != '' || ! is_null($filterType->image)) {
                    $path_parts = pathinfo($filterType->image);
                    $img_path = public_path('images\filter_types\\'.$path_parts['basename']);
                    if (file_exists($img_path)) {
                        unlink($img_path);
                    }
                }

                $url = $request->input('icon');
                $ext = pathinfo($url, PATHINFO_EXTENSION);
                if ($ext == '') {
                    $ext = 'jpg';
                }
                $filename = md5(time()).".$ext";
                $file = file_get_contents($url);
                $save = file_put_contents('images/filter_types/'.$filename, $file);
                $fileName = url().'/images/filter_types/'.$filename;
            }
        }

        if ($fileName != '') {
            $filterType->image = $fileName;
        }
        $filterType->save();

        //Action Logger
        $userAction->logger(5, null, $filterType->id, $current_state, $filterType->toArray(), 'Update filter type: '.$filterType->name);

        return response()->json(['status' => 200, 'id' => $filterType->id, 'image' => $filterType->image]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        \App\Http\Services\UserActionLogger $userAction,
        int $id
    ): JsonResponse {

        $responseMessage = [
            'status' => 200,
            'delete_status' => true,
            'message' => 'filter type successfully deleted!',
        ];

        $filterType = FilterType::find($id);

        if ($filterType->image != '' || ! is_null($filterType->image)) {
            $path_parts = pathinfo($filterType->image);
            $img_path = public_path('images\filter_types\\'.$path_parts['basename']);
            if (file_exists($img_path)) {
                unlink($img_path);
            }
        }

        if (! $filterType->exists()) {
            $responseMessage['delete_status'] = false;
            $responseMessage['message'] = 'Filter type not found!';
        } elseif (! $filterType->delete()) {
            $responseMessage['delete_status'] = false;
            $responseMessage['message'] = 'Filter type not found!';
        } else {
            //Action Logger
            $userAction->logger(5, null, $id, $filterType->toArray(), null, 'Delete filter type: '.$filterType->name);
        }

        return response()->json($responseMessage);
    }

    public function getFilterTypeQuestion(Request $request)
    {
        $questions = FilterType::where('status', 1)->where('type', 'question')->get()->toArray();

        return $request->input('callback').'('.json_encode($questions).');';
    }
}
