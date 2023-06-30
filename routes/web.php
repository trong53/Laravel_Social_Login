<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

use App\Http\Controllers\SocialAuthController;

// use Laravel\Socialite\Two\FacebookProvider; // Day la file define scope=['email'] cua Socialite. Do vay luc nao cung co scope nay tren URL

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


// Social login with facebook, google, github and linkedin

Route::prefix('/auth')->name('auth.')->middleware('guest')->group(function(){
    Route::get('facebook', [SocialAuthController::class, 'facebookRedirect'])->name('facebook');
    Route::get('facebook/callback', [SocialAuthController::class, 'facebookLoginHandle']);

    Route::get('google', [SocialAuthController::class, 'googleRedirect'])->name('google');
    Route::get('google/callback', [SocialAuthController::class, 'googleLoginHandle']);

    Route::get('github', [SocialAuthController::class, 'githubRedirect'])->name('github');
    Route::get('github/callback', [SocialAuthController::class, 'githubLoginHandle']);

    Route::get('linkedin', [SocialAuthController::class, 'linkedinRedirect'])->name('linkedin');
    Route::get('linkedin/callback', [SocialAuthController::class, 'linkedinLoginHandle']);
});


