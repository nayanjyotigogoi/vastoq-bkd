<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        // General API — 120 requests per minute per user/IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        // Login — 10 attempts per 5 minutes per IP
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinutes(5, 10)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'error'   => ['message' => 'Too many login attempts. Please wait 5 minutes and try again.'],
                    ], 429);
                });
        });

        // Register — 5 accounts per hour per IP
        RateLimiter::for('register', function (Request $request) {
            return Limit::perHour(5)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'error'   => ['message' => 'Too many registration attempts. Please try again in an hour.'],
                    ], 429);
                });
        });

        // Contact form — 3 submissions per hour per IP
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perHour(3)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'error'   => ['message' => 'You have sent too many messages. Please wait an hour before trying again.'],
                    ], 429);
                });
        });

        // Google OAuth — 15 attempts per minute per IP (prevents redirect loop abuse)
        RateLimiter::for('google_auth', function (Request $request) {
            return Limit::perMinute(15)->by($request->ip());
        });
    }
}
