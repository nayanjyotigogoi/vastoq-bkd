<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\FurnitureController;
use App\Http\Controllers\Api\FurnitureEnquiryController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ListingUnlockController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SavedListingController;
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
| Listings
|--------------------------------------------------------------------------
*/

Route::prefix('listings')->group(function () {

    Route::get('/', [ListingController::class, 'index']);

    Route::post('/', [ListingController::class, 'store']);

    Route::get('/my-listings', [ListingController::class, 'myListings']);

    Route::get('/{id}', [ListingController::class, 'show']);

    Route::post(
        '/{id}/unlock',
        [ListingUnlockController::class, 'unlock']
    );

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

    Route::post(
        'send-otp',
        [AuthController::class, 'sendOtp']
    );

    Route::post(
        'verify-otp',
        [AuthController::class, 'verifyOtp']
    );

    Route::post(
        'select-role',
        [AuthController::class, 'selectRole']
    );

    Route::get(
        'me',
        [AuthController::class, 'me']
    );

    Route::post(
        'logout',
        [AuthController::class, 'logout']
    );

    Route::post(
        'update-profile',
        [AuthController::class, 'updateProfile']
    );
});

Route::prefix('dashboard')->group(function () {

    Route::get(
        '/tenant',
        [DashboardController::class, 'tenant']
    );

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