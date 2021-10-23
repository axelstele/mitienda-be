<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required:phone|string|email|max:255|unique:users',
            // 'phone' => 'required:email|string|max:255|unique:users',
            'password' => 'required|string|min:8'
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            // 'phone' => $validatedData['phone'],
            'password' => Hash::make($validatedData['password']),
            'generated_url' => strtolower(str_replace(' ', '-', $validatedData['name'])),
        ]);

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'accessToken' => $token,
            'tokenType' => 'Bearer'
        ], 201);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login'
            ], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'accessToken' => $token,
            'tokenType' => 'Bearer'
        ]);
    }

    public function get($companyUrl)
    {
        $user = User::where('generated_url', $companyUrl)->firstOrFail();

        return response()->json([
            'user' => $user
        ]);
    }
}
