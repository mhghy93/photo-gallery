<?php

namespace App\Http\Controllers;

use App\Http\Resources\PhotoResource;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PhotoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return PhotoResource::collection(Photo::paginate(6));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'pic' => 'required|mimes:jpg,png,jpeg|max:5048',
        ]);

        $imagePath = $request->file('pic')->store('images');
        $request->file('pic')->move(public_path('images'), $imagePath);

        $photo = Photo::create([
            'title' => $request->input('title'),
            'pic' => $imagePath, 
            'user_id' => auth()->user()->id
        ]);

        $response = [
            'message' => 'Photo Uploaded Successfully',
            'data' => new PhotoResource($photo)
        ];

        return response($response, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $photo = Photo::findOrFail($id);
        $response = [
            'data' => new PhotoResource($photo)
        ];
        return response($response, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateTitle(Request $request, $id)
    {
        $photo = Photo::findOrFail($id);

        if ($photo->user_id == auth()->user()->id) {
            $request->validate([
                'title' => 'required|max:255'
            ]);

            $photo->update([
                'title' => $request->input('title')
            ]);

            return response([
                'message' => 'Photo Title Updated',
                'data' => new PhotoResource($photo)
            ], 200);
        } else {
            return response([
                'message' => 'Unauthorized'
            ], 401);
        }
    }

     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateImage(Request $request, $id)
    {
        $photo = Photo::findOrFail($id);

        if ($photo->user_id == auth()->user()->id) {
            $request->validate([
                'pic' => 'required|mimes:jpg,png,jpeg|max:5048',
            ]);

            $oldImagePath = $photo->pic;

            if (File::exists($oldImagePath)) {
                File::delete($oldImagePath);
            }

            $imagePath = $request->file('pic')->store('images');
            $request->file('pic')->move(public_path('images'), $imagePath);

            $photo->update([
                'pic' => $imagePath
            ]);

            return response([
                'message' => 'Image Updated',
                'data' => new PhotoResource($photo)
            ], 200);
        } else {
            return response([
                'message' => 'Unauthorized'
            ], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
