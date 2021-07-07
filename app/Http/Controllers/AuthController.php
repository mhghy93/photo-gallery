<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = $request->validate([
            'name' => 'required',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);

        $user = User::create([
            'name' => $validator['name'],
            'email' => $validator['email'],
            'password' => Hash::make($validator['password']),
        ]);

        $token = $user->createToken('token')->plainTextToken;

        $response = [
            'data' => new UserResource($user),
            'token' => $token
        ];

        return response($response, 201);
    }

    public function login(Request $request)
    {
        $validator = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        // Check Email
        $user = User::where('email', $validator['email'])->first();

        // Check Password
        if (!$user || !Hash::check($validator['password'], $user->password)) {
            return response(['Bad credentials'], 401);
        }

        $token = $user->createToken('token')->plainTextToken;

        $response = [
            'data' => new UserResource($user),
            'token' => $token
        ];

        return response($response, 201);
    }
}
