<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if ($id == auth()->user()->id) {
            $profile = User::findOrFail($id);
            return response([
                'data' => new UserResource($profile)
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
    public function update(Request $request, $id)
    {
        if ($id == auth()->user()->id) {
            $profile = User::findOrFail($id);
            
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|string|email',
            ]);

            $name = $request->input('name');
            $email = $request->input('email');

            /** 
             * Fetches all the users whose email is not equal to logged in user
             * Ensures that the logged in user does not use an email to update its profile 
             * that belongs to some other user 
            */
            $user = User::where('email', $email)->where('email', '!=', auth()->user()->email)->get();
            if (count($user) == 0) {
                $profile->update([
                    'name' => $name,
                    'email' => $email
                ]);
                return response([
                    'message' => 'Profile Updated',
                    'data' => new UserResource($profile)
                ], 200);
            } else {
                return response([
                    'message' => 'This email has already been taken'
                ], 409);
            }
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
