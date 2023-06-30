<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\CssSelector\Node\FunctionNode;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserCollection;

use Laravel\Sanctum\PersonalAccessToken;

use App\Models\User;
use Illuminate\Support\Carbon;

use Laravel\Passport\Client;
use Illuminate\Support\Facades\Http;

// We construct an authentication for using API => 'auth:api'
// We use Sanctum or Passport (OAuth 2.0)

class AuthController extends Controller
{
    /************************************** SANCTUM ******************************************************/

    /*
    // Login using Sanctum. When an user log in, he will have a Token to use API resources
    // Token for passing through 'auth:api' middleware of API Routes

    public function login(Request $request)
    {   
        
        $validatedUser = $request->validate([           // tra ve array phan tu da dc validated
            'email'     => ['required', 'email'],
            'password'  => ['required', 'min:6']
        ]);

        $checkLogin = Auth::attempt($validatedUser);   // empty or 1
        
        if ($checkLogin) {

            /* @var \App\Models\User $user
            $user = Auth::user();               // tra ve 1 array cac details

            $token = $user->createToken('auth_token')->plainTextToken;

            // return $user->tokens;            // tra ve 1 Array chua tat ca Tokens cua $user

            $user = collect([$user]);           // chu y syntaxe :  $collection = collect(['first', 'second']);
                                                //$user chuyen tu Array sang Collection de su dung UserCollection

            return new UserCollection($user, 200, 'Login successful', $token);
            
        } else {
            return [
                'status'    => 401,
                'message'   => 'Failed to log in.'
            ];
        }
    }
    */

    // Delete token(s). It will delete the access tokens in the table

    public function tokenHandle(Request $request)
    {
        // return $authUser = $request->user()->tokens()->delete();     // Xoa tat ca tokens cua $user. tra ve so items bi xoa.
        // return $request->user()->currentAccessToken()->delete();     // xoa mat token dang su dung
        return $request->user()->tokens()->where('id', 9)->delete();    // xoa token co id la 9

    }

    // Refresh token when actual token is expired. Using Sanctum

    public function refreshToken(Request $request)
    {
        if (empty(config('sanctum.expiration'))) {      // Doi voi truong hop expiration la null hay 0
            return [                                    // thi token ko bao gio het han
                'status'    => 406,
                'message'   => 'Token is always valid. No new token has been created.'
            ];
        }

        // Get token from Header of request : authorization
        $hashToken = $request->header('authorization'); // Bearer 11|PgnDJGcFGZ5O4dzSPqSah5EREclLCG1ltSaeskLt
        $hashToken = str_replace('Bearer', '', $hashToken);
        $hashToken = trim($hashToken);                  // 11|PgnDJGcFGZ5O4dzSPqSah5EREclLCG1ltSaeskLt  trong header dua vao

        // Get Details of this hashToken in the table "personal_access_token"
        $token = PersonalAccessToken::findToken($hashToken);


        if (!empty($token)) {                           // neu tim thay thong tin token trong table
            
            // Kiem tra xem token co het han chua ?

            $tokenCreatedAt = $token->created_at;       // lay ngay tao cua Token

            // $expire = new Carbon($tokenCreatedAt);   // Tao instance Carbon  => "2023-06-27T18:41:14.000000Z"  === Carbon::parse()
            $expire = Carbon::parse($tokenCreatedAt);    // ket qua nhu tren => "2023-06-27T18:41:14.000000Z"
            $expire = $expire->addMinutes(config('sanctum.expiration'));   // "2023-06-27T18:42:14.000000Z"


            // if expired
            if (Carbon::now(config('app.timezone')) >= $expire) {
                $userID = $token->tokenable_id;             // day cung la id cua $user
                User::find($userID)->tokens()->delete();    // xoa toan bo token cua $user

                $newToken = User::find($userID)->createToken('auth_token')->plainTextToken; // tao token moi

                return [
                    'status'    => 200,
                    'token'     => $newToken
                ];

            } else {
                return [
                    'status'    => 406,
                    'message'   => 'Token is not expired. No new token has been created.'
                ];
            }
        }
       
        return [
            'status'    => 404,
            'message'   => 'Invalid token'
        ];        
    }


    /************************************** PASSPORT ******************************************************/

    // Use Passport instead of Sanctum for API Authentication : Auth + API token

    public function loginWithPassport(Request $request)
    {   
        $validatedUser = $request->validate([           // tra ve array phan tu da dc validated
            'email'     => ['required', 'email'],
            'password'  => ['required', 'min:6']
        ]);

        $checkLogin = Auth::attempt($validatedUser);   // empty or 1
        
        if ($checkLogin) {

            /** @var \App\Models\User $user **/
            $user = Auth::user();               // tra ve 1 array cac details
            
            // Create api token for $user using the method createToken of the trait HasApiTokens of Passport class
            // return access Token + his token infos in the table
            $tokenResult = $user->createToken('auth_api_passport');

            $token = $tokenResult->token;                           // get token details

            // Sau nay thi expires_at se dc dinh nghia trong AuthServiceProvider
            /*
                    Passport::tokensExpireIn(now()->addMinutes(5));
                    Passport::refreshTokensExpireIn(now()->addMinutes(60));
            */
            $token->expires_at = Carbon::now()->addMinutes(60);     // set up token's expires_at. 
                                                                    // Nhung ko thay doi trong table !!!
            
            return [
                'status'    => 200,
                'access_token' => $tokenResult->accessToken,
                'expires_at'   => $token->expires_at->toDateTimeString()    // default : hien thi Carbon trong postman
            ];                                                              // dung method nay thi hien thi nhu bthuong

        } else {
            return [
                'status'    => 401,
                'message'   => 'Failed to log in.'
            ];
        }
    }

    // Log out = revoke the token (turn into TRUE the column revoke of the token's table)
    // or we can delete this token
    public function logoutWithPassport(Request $request)
    {
        $user = $request->user();

        // giong nhu Sanctum, Passport cung co nhung thuoc tinh tokens, token. Xem trait HasApiTokens
        // return $user->tokens;    // liet ke toan bo tokens cua $user
        // return $user->tokens();  // method hasMany trong model Token.php => bat dau query dc nhu sanctum  ex: ->delete()

        // return $user->tokens()->delete();      // chi hien thi detail cua token dang su dung trong table
        
        $status = $user->token()->revoke();  // vo hieu luc token nay cua $user. column revoke = 1

        if ($status) {

            // Log out cho gaurd 'web'
            // Auth::guard('web')->logout();         // This will remove the authentication information from the user's session 
                                                // so that subsequent requests are not authenticated.
            // $request->session()->invalidate();      // invalidate the user's session
         
            // $request->session()->regenerateToken(); // regenerate their CSRF token

            // return Auth::guard('web')->user();   // tra ve nothing = $user la empty
            // return Auth::user()                  // tra ve $user hien tai. Nhu vay la guard dang lam viec la 'api', ko phai la 'web'

            return [
                'status'    => 200,
                'message'   => 'Successful logout'
            ];

        } else {
            return [
                'status'    => 405,
                'message'   => 'Logout not allowed'
            ];
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////

    // Passport Grant - Login. This one is recommended because it generates the token and refresh token

    public function loginWithPassportGrantTokens(Request $request)
    {   
        $validatedUser = $request->validate([           // tra ve array phan tu da dc validated
            'email'     => ['required', 'email'],
            'password'  => ['required', 'min:6']
        ]);

        $checkLogin = Auth::attempt($validatedUser);   // empty or 1
        
        if ($checkLogin) {

            /** @var \App\Models\User $user **/
            $user = Auth::user();               // tra ve 1 array cac details
            
            // to be able to create Passport Grant Tokens, we need a Passport Grant Client
            $client = Client::where('password_client', 1)->first();
            

            if ($client) {
                $clientID =$client->id;
                $clientSecret = $client->secret;

                // submit form de tao response. IMPORTANT : siteweb cua user dang chay tren cong 8000. === site of User
                // Ta phai tao port khac = 8080 de API service chay   === Site of API service (facebook, Google, ...)
                // path /oauth/token co san trong Passport
                // this is the Token creation step of API Service

                $response = Http::asForm()->post('http://127.0.0.1:8080/oauth/token', [  
                    'grant_type'    => 'password',
                    'client_id'     => $clientID,
                    'client_secret' => $clientSecret,
                    'username'      => $validatedUser['email'],
                    'password'      => $validatedUser['password'],
                    'scope'         => '',
                ]);

                return $response;
            }        

        } else {
            return [
                'status'    => 401,
                'message'   => 'Failed to log in.'
            ];
        }
    }

    // refresh Passport grant Token

    public function refreshPassportGrantToken(Request $request)
    {
        // Get refresh token from Post request
        $refreshToken = $request->refresh;

        // Get Passport grant client
        $client = Client::where('password_client', 1)->first();
            
            if ($client) {
                $clientID =$client->id;
                $clientSecret = $client->secret;

                // create a new access token when having refresh token
                $response = Http::asForm()->post('http://127.0.0.1:8080/oauth/token', [
                    'grant_type'        => 'refresh_token',
                    'refresh_token'     => $refreshToken,
                    'client_id'         => $clientID,
                    'client_secret'     => $clientSecret,
                    'scope' => '',
                ]);

                return $response;
            }   

    }

}




/*
model Client in Laravel/Paspport

protected $table = 'oauth_clients';

$client = Client::where('password_client', 1)->first();
{
    "id": 2,
    "user_id": null,
    "name": "Laravel Social Login Password Grant Client",
    "provider": "users",
    "redirect": "http://localhost",
    "personal_access_client": false,
    "password_client": true,
    "revoked": false,
    "created_at": "2023-06-28T05:53:36.000000Z",
    "updated_at": "2023-06-28T05:53:36.000000Z"
}
*/


/*
https://carbon.nesbot.com/docs/#api-instantiation

The string passed to Carbon::parse or to new Carbon can represent 
a relative time (next sunday, tomorrow, first day of next month, last year) 
or an absolute time (first day of December 2008, 2017-01-06). 

https://github.com/mohamedgaber-intake40/sanctum-refresh-token/wiki/V2.x


*/