<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    //register
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6|max:10'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json(['message' => 'User created successfully'], 201);
    }

    //login
    public function login(Request $request)
    {

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        if ($user->role == null) {
            return response()->json(['error' => 'User belum divalidasi, hubungi kepala sekolah'], 401);
        }

        $credetentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credetentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could not create token'], 500);
        }

        return response()->json([
            'token' => $token,
            // 'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }

    //get all data
    public function getAllData()
    {
        return response()->json([
            User::all()
        ]);
    }

    //logout
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not log out'], 500);
        }

        return response()->json(['message' => 'Successfully logged out']);
    }
}
