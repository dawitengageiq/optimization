<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Will return all category names
     *
     * @return mixed
     */
    public function categoryNames()
    {
        return Category::getAllActiveNames()->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(
        Request $request,
        \App\Http\Services\UserActionLogger $userAction
    ) {
        $category = new Category;
        $category->name = $request->input('name');
        $category->description = $request->input('description');
        $category->status = $request->input('status');
        $category->save();

        //Action Logger
        $userAction->logger(8, null, $category->id, null, $category->toArray(), 'Add category: '.$category->name);

        return $category->id;
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
     * Update the specified resource in storage.
     *
     * @return array|string
     */
    public function update(
        Request $request,
        \App\Http\Services\UserActionLogger $userAction
    ) {
        $id = $request->input('id');
        $category = Category::find($id);

        $current_state = $category->toArray(); //For Logging

        $category->name = $request->input('name');
        $category->description = $request->input('description');
        $category->status = $request->input('status');
        $category->save();

        //Action Logger
        $userAction->logger(8, null, $category->id, $current_state, $category->toArray(), 'Update category: '.$category->name);

        return $id;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        Request $request,
        \App\Http\Services\UserActionLogger $userAction
    ) {
        $id = $request->input('id');
        $category = Category::find($id);
        $category->delete();

        //Action Logger
        $userAction->logger(8, null, $id, $category->toArray(), null, 'Delete category: '.$category->name);
    }

    /**
     * This will determine if certain advertiser is active.
     */
    public function status($id): JsonResponse
    {
        $category = Category::find($id);

        $response = [];

        if ($category->exists()) {
            $response['category_id'] = $category->id;
            $response['name'] = $category->name;
            $response['active'] = $category->status == 1;
        }

        return response()->json($response, 200);
    }

    /**
     * Will return all categories id -> name
     *
     * @return mixed
     */
    public function getCategories()
    {
        return Category::where('status', '=', 1)->select('name', 'id')->orderBy('name', 'asc')->get()->toArray();
    }
}
