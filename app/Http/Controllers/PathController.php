<?php

namespace App\Http\Controllers;

use App\Path;
use Illuminate\Http\Request;

class PathController extends Controller
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:paths|max:100',
            'url' => 'required|unique:paths|max:255|url',
        ]);

        $path = new Path;
        $path->name = $request->input('name');
        $path->url = $request->input('url');
        $path->save();

        return $path->id;
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {

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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id = $request->input('id');

        $this->validate($request, [
            'name' => 'required|unique:paths,name,'.$id.'|max:100',
            'url' => 'required|unique:paths,url,'.$id.'|max:255|url',
        ]);

        $path = Path::find($id);
        $path->name = $request->input('name');
        $path->url = $request->input('url');
        $path->save();

        return $id;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $path = Path::find($id);
        $path->delete();
    }
}
