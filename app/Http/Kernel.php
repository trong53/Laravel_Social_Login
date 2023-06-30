<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,            // csrf token for Form.
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,                                 // authenticated , if not =>login page
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,       // provides a quick way to authenticate users of your application without setting up a dedicated "login" page
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,              // Laravel includes a cache.headers middleware, which may be used to quickly set the Cache-Control header for a group of routes. https://laravel.com/docs/10.x/responses#cache-control-middleware
        'can' => \Illuminate\Auth\Middleware\Authorize::class,                              // realize the authorization (Gate ou Policy)
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,                     // access for only 'guest' (not-authenticated)       
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,           // While building your application, you may occasionally have actions that should require the user to confirm their password before the action is performed. Typically, these routes are protected by Laravel's built-in password.confirm middleware.
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,                // already clicked on confirm-password link in email  
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,               // By default, the user will not be able to login for one minute if they fail to provide the correct credentials after several attempts. The throttling is unique to the user's username / email address and their IP address.
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,             // required verification user (after verification by email - email_verified_at column) 
    ];
}
