<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\FurnitureController;
use App\Http\Controllers\Api\FurnitureEnquiryController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ListingUnlockController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\WorkerController;
use App\Http\Controllers\Api\WorkerUnlockController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\SavedListingController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\UploadController;
/*
|--------------------------------------------------------------------------
| API Health Check
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'Vastoq API is running.'
    ]);
});

/*
|--------------------------------------------------------------------------
| Prices (public)
|--------------------------------------------------------------------------
*/

Route::get('/prices', function () {
    return response()->json([
        'success' => true,
        'data' => [
            'listing_unlock' => config('prices.listing_unlock.amount'),
            'worker_unlock'  => config('prices.worker_unlock.amount'),
            'listing_boost'  => config('prices.listing_boost.amount'),
            'listing_boost_duration_days' => config('prices.listing_boost.duration_days'),
        ],
    ]);
});

/*
|--------------------------------------------------------------------------
| Listings
|--------------------------------------------------------------------------
*/

Route::prefix('listings')->group(function () {

    Route::get('/', [ListingController::class, 'index']);

    Route::post('/', [ListingController::class, 'store']);

    Route::get('/my-listings', [ListingController::class, 'myListings']);

    // Razorpay payment routes — MUST come before {id} routes
    Route::post('/{id}/create-unlock-order', [PaymentController::class, 'createListingUnlockOrder']);
    Route::post('/{id}/verify-unlock-payment', [PaymentController::class, 'verifyListingUnlockPayment']);
    Route::post('/{id}/create-boost-order', [PaymentController::class, 'createListingBoostOrder']);
    Route::post('/{id}/verify-boost-payment', [PaymentController::class, 'verifyListingBoostPayment']);

    Route::get('/{id}/unlock-status', [ListingUnlockController::class, 'status']);

    Route::post('/{id}/unlock',       [ListingUnlockController::class, 'unlock']);

    Route::get('/{id}', [ListingController::class, 'show']);

    Route::put('/{id}', [ListingController::class, 'update']);

    Route::patch('/{id}', [ListingController::class, 'update']);

    Route::delete('/{id}', [ListingController::class, 'destroy']);
});
/*
|--------------------------------------------------------------------------
| Furniture
|--------------------------------------------------------------------------
*/

Route::prefix('furniture')->group(function () {

    // List furniture
    Route::get('/', [FurnitureController::class, 'index']);

    // Create furniture
    Route::post('/', [FurnitureController::class, 'store']);

    // Single furniture
    Route::get('/{id}', [FurnitureController::class, 'show']);

    // Update furniture
    Route::put('/{id}', [FurnitureController::class, 'update']);

    Route::patch('/{id}', [FurnitureController::class, 'update']);

    // Delete furniture
    Route::delete('/{id}', [FurnitureController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Furniture Enquiries
|--------------------------------------------------------------------------
*/

Route::prefix('furniture-enquiries')->group(function () {

    // Create enquiry
    Route::post('/', [FurnitureEnquiryController::class, 'store']);

    // List enquiries
    Route::get('/', [FurnitureEnquiryController::class, 'index']);

    // Single enquiry
    Route::get('/{id}', [FurnitureEnquiryController::class, 'show']);

    // Update status
    Route::put('/{id}/status', [FurnitureEnquiryController::class, 'updateStatus']);

    // Delete enquiry
    Route::delete('/{id}', [FurnitureEnquiryController::class, 'destroy']);
});


/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {

    Route::post('login',           [AuthController::class, 'login']);
    Route::post('register',        [AuthController::class, 'register']);
    Route::get('me',               [AuthController::class, 'me']);
    Route::post('logout',          [AuthController::class, 'logout']);
    Route::post('update-profile',  [AuthController::class, 'updateProfile']);
    Route::post('change-password', [AuthController::class, 'changePassword']);

    // Google OAuth (wrapped with session middleware to support role state)
    Route::middleware([\Illuminate\Session\Middleware\StartSession::class])->group(function () {
        Route::get('google',          [SocialAuthController::class, 'redirectToGoogle']);
        Route::get('google/callback', [SocialAuthController::class, 'handleGoogleCallback']);
    });

    // Get authenticated user profile and manage role via Sanctum token
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('user', function (\Illuminate\Http\Request $request) {
            $user = $request->user();
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id'                => $user->id,
                        'name'              => $user->name,
                        'phone'             => $user->phone,
                        'email'             => $user->email,
                        'role'              => $user->role,
                        'credit_balance'    => $user->credit_balance ?? 0,
                        'is_verified'       => $user->is_verified,
                        'profile_photo_url' => $user->profile_photo_url,
                    ]
                ]
            ]);
        });

        Route::post('update-role', function (\Illuminate\Http\Request $request) {
            $request->validate([
                'role' => 'required|in:tenant,owner,worker',
            ]);
            $user = $request->user();
            $user->update(['role' => $request->role]);
            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully.'
            ]);
        });
    });
});

Route::prefix('dashboard')->group(function () {

    Route::get(
        '/tenant',
        [DashboardController::class, 'tenant']
    );

    Route::get(
        '/worker',
        [DashboardController::class, 'worker']
    );
});

/*
|--------------------------------------------------------------------------
| Workers — public list & admin actions
|--------------------------------------------------------------------------
*/

Route::prefix('workers')->group(function () {
    Route::get('/', [WorkerController::class, 'index']);

    // Razorpay payment routes — MUST come before {id} routes
    Route::post('/{id}/create-unlock-order', [PaymentController::class, 'createWorkerUnlockOrder']);
    Route::post('/{id}/verify-unlock-payment', [PaymentController::class, 'verifyWorkerUnlockPayment']);

    Route::get('/{id}/unlock-status', [WorkerUnlockController::class, 'status']);
    Route::post('/{id}/unlock', [WorkerUnlockController::class, 'unlock']);
    Route::get('/{id}', [WorkerController::class, 'show']);
    Route::patch('/{id}', [WorkerController::class, 'adminAction']);
});

/*
|--------------------------------------------------------------------------
| Worker — own profile management
|--------------------------------------------------------------------------
*/

Route::prefix('worker')->group(function () {

    Route::get(
        '/profile',
        [WorkerController::class, 'profile']
    );

    Route::post(
        '/profile',
        [WorkerController::class, 'store']
    );

    Route::put(
        '/profile',
        [WorkerController::class, 'update']
    );

    Route::post(
        '/aadhaar',
        [WorkerController::class, 'submitAadhaar']
    );
});

/*
|--------------------------------------------------------------------------
| Coupons
|--------------------------------------------------------------------------
*/

Route::prefix('coupons')->group(function () {
    Route::post('/validate', [CouponController::class, 'check']);
});

/*
|--------------------------------------------------------------------------
| Uploads — listing photos & profile photos
|--------------------------------------------------------------------------
*/

Route::prefix('uploads')->group(function () {
    Route::post('/listing-photos', [UploadController::class, 'listingPhotos']);
    Route::post('/profile-photo',  [UploadController::class, 'profilePhoto']);
});

Route::prefix('saved-listings')->group(function () {

    Route::get(
        '/',
        [SavedListingController::class, 'index']
    );

    Route::post(
        '/toggle',
        [SavedListingController::class, 'toggle']
    );
});