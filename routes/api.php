<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{UserController, AuthController};
use App\Models\User;
use Illuminate\Support\Carbon;

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


// API resource : CRUD
Route::middleware('auth:api')->group(function(){            // sanctum : 'auth:sanctum  // passport: 'auth:api'
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'detail']);
    Route::post('/create', [UserController::class, 'create']);
    Route::put('/update/{id}', [UserController::class, 'update']);
    Route::patch('/update/{id}', [UserController::class, 'update']);
    Route::delete('/delete/{id}', [UserController::class, 'delete']);
});


// learn sanctum => authentication
Route::post('login', [AuthController::class, 'login']);
Route::get('token', [AuthController::class, 'tokenHandle'])->middleware('auth:sanctum');
Route::get('refresh-token', [AuthController::class, 'refreshToken']);


// Test : learn Passport
Route::get('passport-token', function(){
    $user = User::find(5);

    // day la method createToken cua trait HasApiTokens ben trong User class.
    $tokenResult = $user->createToken('auth_api_passport');

    $accessToken = $tokenResult->accessToken;
    $token = $tokenResult->token;
    $will_expires_at = $token->expires_at;      // "2024-06-28T08:46:10.000000Z"  default = 1 year

    // set up expires_at
    $token->expires_at = Carbon::parse($token->created_at)->addMinutes(60);
    $token->expires_at = $token->expires_at->toDateTimeString();   // 2023-06-28 11:58:01

    return [
        'accessToken'   => $accessToken,
        'expires_at'    => $token->expires_at
    ];
});


// Learn Passport
Route::post('login-passport', [AuthController::class, 'loginWithPassport']);
Route::get('logout-passport', [AuthController::class, 'logoutWithPassport'])->middleware('auth:api');


// Learn Passport Grant Tokens
Route::post('login-passportGrant', [AuthController::class, 'loginWithPassportGrantTokens']);
Route::post('refresh-passportGrantToken', [AuthController::class, 'refreshPassportGrantToken']);