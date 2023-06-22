<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;

class SocialAuthController extends Controller
{
    /*************************Facebook login *********************************/

    // Click on the Login button, It will redirect to the Login page of the social network 
    public function facebookRedirect() {
        // return Socialite::driver('facebook')->setScopes([''])->redirect();  // default is scope=email,

        // I have turned on the Email Permission, and now I can get email data
        return Socialite::driver('facebook')->redirect();      
    }

    // if the login is well done, we will be redirected to our page callback to do this treatment below
    public function facebookLoginHandle() {
        try {
            $userData = Socialite::driver('facebook')->user();      // ->stateless(). Get user data from facebook
            // dd($userData);

            $provider = 'facebook';
            return $this->loginHandle($provider, $userData);

        } catch (\Exception $e) {
            echo ($e->getMessage());                        // display the errors
        }
    }

    /*************************Google login *********************************/

    public function googleRedirect() {

        return Socialite::driver('google')->redirect();
    }

    public function googleLoginHandle() {
        try {
            $userData = Socialite::driver('google')->user();      // ->stateless()
            
            $provider = 'google';
            return $this->loginHandle($provider, $userData);

        } catch (\Exception $e) {
            echo ($e->getMessage());
        }
    }

    /*************************Github login *********************************/

    public function githubRedirect() {

        return Socialite::driver('github')->redirect();
    }

    public function githubLoginHandle() {
        try {
            $userData = Socialite::driver('github')->user();      // ->stateless()
            
            $provider = 'github';
            return $this->loginHandle($provider, $userData);

        } catch (\Exception $e) {
            echo ($e->getMessage());
        }
    }

    /************************* Functions Helpers *****************************/

    public function emailHandler(string $email, string $provider) : string
    {
        return "($provider)_$email";
    }

    public function loginHandle(string $provider, $userData)
    {
        $user = User::updateOrCreate(                       // update or create an user
            [$provider.'_id'  => $userData->id],            // condition for finding the instance
                                                            // if found, update with the 2nd array
            [                                               // if not found, create with both 1er and 2nd arrays
                'name'      => $userData->name ?? $provider.'_'.$userData->id,  
                'nickname'  => $userData->nickname ?? null,
                'email'     => $this->emailHandler($userData->email, $provider)    
            ]
        );    
        
        // dd($user);
        Auth::login($user);                                     // login to our app with 'user' instance
        $redirectTo = RouteServiceProvider::HOME ?? '/home';    // setup the path = homepage
        return redirect($redirectTo);                           // redirect to Homepage of User
    }
}
