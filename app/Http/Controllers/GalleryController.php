<?php

namespace App\Http\Controllers;

use App\Http\Requests\GalleryRequest;
use Illuminate\Http\Request;
use Storage;

class GalleryController extends Controller
{
    public function show()
    {
        $images = Storage::disk('public')->files('images/gallery');
        $col_num = 4;
        $counter = 0;
        $row = 0;
        foreach ($images as $image) {
            $gallery[$row][] = $image;
            $counter++;
            if ($counter % $col_num == 0 && $counter != 0) {
                $row++;
            }
        }

        foreach ($gallery as $key => $row) {
            if (count($row) < $col_num) {
                $n = $col_num - count($row);
                for ($x = 0; $x < $n; $x++) {
                    $gallery[$key][] = '';
                }
            }
        }

        return $gallery;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return mixed
     */
    public function store(GalleryRequest $request)
    {
        $destinationPath = 'images/gallery/';

        if ($request->hasFile('image')) {
            $file = $request->file('image');

            $ext = $file->getClientOriginalExtension();
            $filename = $request->input('name').".$ext";
            $uploadSuccess = $file->move($destinationPath, $filename);
        } else {
            if ($request->input('image') != '' || ! is_null($request->input('image'))) {
                $url = $request->input('image');
                $ext = pathinfo($url, PATHINFO_EXTENSION);
                $filename = $request->input('name').".$ext";
                $file = file_get_contents($url);
                $save = file_put_contents('images/gallery/'.$filename, $file);
            }
        }

        return $this->show();
        //return url($destinationPath.$filename);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return mixed
     */
    public function destroy(Request $request)
    {
        $img = $request->input('image');
        // $img_path = public_path('images\gallery\\'.$img);
        $img_path = public_path($img);

        if (file_exists($img_path)) { //check if file exists
            unlink($img_path);
        }

        return $this->show();
    }
}
