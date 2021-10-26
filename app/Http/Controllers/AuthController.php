<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use Artisan;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required:phone|string|email|max:255|unique:users',
            // 'phone' => 'required:email|string|max:255|unique:users',
            'url' => 'required|string|unique:users',
            'password' => 'required|string|min:8'
        ]);

        $token = DB::transaction(function () use ($validatedData) {
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                // 'phone' => $validatedData['phone'],
                'password' => Hash::make($validatedData['password']),
                'url' => strtolower(str_replace(' ', '-', $validatedData['url'])),
            ]);

            $token = $user->createToken('auth')->plainTextToken;

            $newSchemaName = "mitienda{$user->id}";

            DB::unprepared("
                SET search_path to public;
                CREATE SCHEMA {$newSchemaName};
                SET search_path to {$newSchemaName};
            ");

            Schema::create('migrations', function (Blueprint $table) {
                $table->id();
                $table->string('migration');
                $table->integer('batch');
            });

            Artisan::call('migrate', array('--path' => 'database/migrations/company', '--force' => true));

            return $token;
        });

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

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->noContent();
    }

    public function refreshToken(Request $request)
    {
        $token = $request->bearerToken();

        if (!empty($token)) {
            $personalAccessToken = PersonalAccessToken::findToken($token);
            if (!empty($personalAccessToken)) {
                $user = $personalAccessToken->tokenable;
                DB::table('personal_access_tokens')->where('token', $personalAccessToken->token)->delete();
                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'accessToken' => $token,
                    'tokenType' => 'Bearer'
                ]);
            }
        }
    }

    public function get($companyUrl)
    {
        $user = User::where('url', $companyUrl)->firstOrFail();

        return response()->json([
            'user' => $user
        ]);
    }
}
