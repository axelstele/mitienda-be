<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// AuthController
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/get/{company_url}', [AuthController::class, 'get']);
Route::post('/userinfo', [AuthController::class, 'userinfo'])->middleware('auth:sanctum');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/refresh', [AuthController::class, 'refreshToken']);

// ArticleController
Route::post('/article', [ArticleController::class, 'create'])->middleware('auth:sanctum');
Route::get('/articles', [ArticleController::class, 'get'])->middleware('auth:sanctum');

// CategoryController
Route::post('/category', [CategoryController::class, 'create'])->middleware('auth:sanctum');
Route::get('/categories', [CategoryController::class, 'get'])->middleware('auth:sanctum');

