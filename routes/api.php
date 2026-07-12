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
    Route::post('send-email-otp',  [AuthController::class, 'sendEmailOtp']);
    // FUTURE — MOBILE OTP: un-comment when SMS verification is ready
    Route::post('send-phone-otp',  [AuthController::class, 'sendPhoneOtp']);
    Route::get('me',               [AuthController::class, 'me']);
    Route::post('logout',          [AuthController::class, 'logout']);
    Route::post('update-profile',  [AuthController::class, 'updateProfile']);
    Route::post('change-password', [AuthController::class, 'changePassword']);

    // Google OAuth (wrapped with session middleware to support role state)
    Route::middleware([\Illuminate\Session\Middleware\StartSession::class])->group(function () {
        Route::get('google',          [SocialAuthController::class, 'redirectToGoogle']);
        Route::get('google/callback', [SocialAuthController::class, 'handleGoogleCallback']);
    });

    // POST /auth/google/exchange
    // Accepts the self-contained signed token and optionally updates the role/phone.
    // Token format: base64(json_user_data) . "." . hmac_sha256_hex
    Route::post('google/exchange', function (\Illuminate\Http\Request $request) {
        $token = $request->token;
        if (!$token) {
            return response()->json(['success' => false, 'error' => ['message' => 'Token is required.']], 400);
        }

        $dotPos = strrpos($token, '.');
        if ($dotPos === false) {
            return response()->json(['success' => false, 'error' => ['message' => 'Malformed token']], 400);
        }

        $payloadB64 = substr($token, 0, $dotPos);
        $sig        = substr($token, $dotPos + 1);

        // Verify HMAC
        $expected = hash_hmac('sha256', $payloadB64, config('app.key'));
        if (!hash_equals($expected, $sig)) {
            return response()->json(['success' => false, 'error' => ['message' => 'Invalid token signature']], 401);
        }

        $data = json_decode(base64_decode($payloadB64), true);
        if (!$data || !isset($data['exp']) || time() > $data['exp']) {
            return response()->json(['success' => false, 'error' => ['message' => 'Token expired']], 401);
        }

        $user = \App\Models\User::find($data['id']);
        if (!$user) {
            return response()->json(['success' => false, 'error' => ['message' => 'User not found']], 404);
        }

        // Validate updated role and phone number
        $role = $request->input('role', $user->role);
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'role'  => 'nullable|in:tenant,owner,worker',
            'phone' => [
                'nullable',
                'digits:10',
                'unique:users,phone,' . $user->id,
                function ($attribute, $value, $fail) use ($role) {
                    if (in_array($role, ['owner', 'worker']) && empty($value)) {
                        $fail('The mobile number is required for Property Owners and Local Workers.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Apply updates
        $updateData = [];
        if ($request->role) {
            $updateData['role'] = $request->role;
        }
        if ($request->has('phone')) {
            $updateData['phone'] = $request->phone;
        }

        if (!empty($updateData)) {
            $user->update($updateData);
            $user->refresh();
        }

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