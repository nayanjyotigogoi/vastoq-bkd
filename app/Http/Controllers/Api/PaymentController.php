<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ListingBoostedMail;
use App\Mail\ListingUnlockedOwnerMail;
use App\Mail\ListingUnlockedUserMail;
use App\Mail\WorkerUnlockedMail;
use App\Notifications\ListingUnlockedOwnerNotification;
use App\Notifications\PaymentSuccessNotification;
use App\Models\Listing;
use App\Models\ListingUnlock;
use App\Models\Worker;
use App\Models\WorkerUnlock;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    public function createListingUnlockOrder(Request $request, $id)
    {
        Log::info('[LISTING:PAYMENT] Create Order Attempt', ['listing_id' => $id]);
        try {
            $request->validate(['user_id' => 'required|exists:users,id']);
            $listing = Listing::with('owner:id,name,phone,is_verified')->findOrFail($id);
            $user    = User::findOrFail($request->user_id);

            $existing = ListingUnlock::where('listing_id', $listing->id)->where('user_id', $user->id)->first();
            if ($existing) return response()->json(['success' => false, 'message' => 'Already unlocked'], 400);

            $api         = new Api(config('services.razorpay.key_id'), config('services.razorpay.key_secret'));
            $priceConfig = config('prices.listing_unlock');
            $amount      = $priceConfig['amount'] * 100;

            $razorpayOrder = $api->order->create([
                'receipt'  => (string) Str::uuid(),
                'amount'   => $amount,
                'currency' => $priceConfig['currency'],
                'notes'    => ['listing_id' => $listing->id, 'user_id' => $user->id, 'type' => 'listing_unlock'],
            ]);

            Transaction::create([
                'id'                => (string) Str::uuid(),
                'user_id'           => $user->id,
                'listing_id'        => $listing->id,
                'amount_cents'      => $amount,
                'currency'          => $priceConfig['currency'],
                'razorpay_order_id' => $razorpayOrder['id'],
                'status'            => 'created',
            ]);

            return response()->json([
                'success'  => true,
                'order_id' => $razorpayOrder['id'],
                'amount'   => $amount,
                'currency' => 'INR',
                'key_id'   => config('services.razorpay.key_id'),
                'contact'  => $user->email,
            ]);
        } catch (\Throwable $e) {
            Log::error('[LISTING:PAYMENT] Order Creation Failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function verifyListingUnlockPayment(Request $request, $id)
    {
        Log::info('[LISTING:PAYMENT] Verification Attempt', ['listing_id' => $id]);
        try {
            $request->validate([
                'user_id'             => 'required|exists:users,id',
                'razorpay_payment_id' => 'required|string',
                'razorpay_order_id'   => 'required|string',
                'razorpay_signature'  => 'required|string',
            ]);
            $listing = Listing::with('owner:id,name,phone,is_verified')->findOrFail($id);
            $user    = User::findOrFail($request->user_id);

            $existing = ListingUnlock::where('listing_id', $listing->id)->where('user_id', $user->id)->first();
            if ($existing) {
                return response()->json(['success' => true, 'message' => 'Already unlocked', 'data' => [
                    'phone' => $listing->owner?->phone, 'address' => $listing->address,
                    'latitude' => $listing->latitude, 'longitude' => $listing->longitude,
                ]]);
            }

            $api = new Api(config('services.razorpay.key_id'), config('services.razorpay.key_secret'));
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id'   => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature'  => $request->razorpay_signature,
            ]);

            $txn = Transaction::where('razorpay_order_id', $request->razorpay_order_id)->first();
            if ($txn) {
                $txn->update([
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'razorpay_signature'  => $request->razorpay_signature,
                    'status'              => 'paid',
                ]);
            }

            $priceConfig = config('prices.listing_unlock');
            ListingUnlock::create([
                'listing_id'  => $listing->id,
                'user_id'     => $user->id,
                'amount_paid' => $priceConfig['amount'],
                'expires_at'  => now()->addDays(30),
            ]);

            $listing->increment('unlock_count');

            // Email + notification: tenant
            if ($user->email) {
                try { Mail::to($user->email)->send(new ListingUnlockedUserMail($user, $listing->load('owner'))); }
                catch (\Throwable $e) { Log::error('[LISTING:UNLOCK] User email failed', ['error' => $e->getMessage()]); }
            }
            try { $user->notify(new PaymentSuccessNotification("You unlocked contact details for \"{$listing->title}\".", "/rentals/{$listing->id}")); }
            catch (\Throwable $e) { Log::error('[LISTING:UNLOCK] User notification failed', ['error' => $e->getMessage()]); }

            // Email + notification: owner
            $owner = $listing->owner;
            if ($owner && $owner->email) {
                try { Mail::to($owner->email)->send(new ListingUnlockedOwnerMail($owner, $listing, $user)); }
                catch (\Throwable $e) { Log::error('[LISTING:UNLOCK] Owner email failed', ['error' => $e->getMessage()]); }
            }
            if ($owner) {
                try { $owner->notify(new ListingUnlockedOwnerNotification($listing)); }
                catch (\Throwable $e) { Log::error('[LISTING:UNLOCK] Owner notification failed', ['error' => $e->getMessage()]); }
            }

            // Notification: admin(s)
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                try {
                    $admin->notify(new PaymentSuccessNotification(
                        "{$user->name} unlocked \"{$listing->title}\" · ₹{$priceConfig['amount']}",
                        "/admin"
                    ));
                } catch (\Throwable $e) {
                    Log::error('[LISTING:UNLOCK] Admin notification failed', ['error' => $e->getMessage()]);
                }
            }

            return response()->json(['success' => true, 'message' => 'Payment successful! Details unlocked.', 'data' => [
                'phone'     => $listing->owner?->phone,
                'address'   => $listing->address,
                'latitude'  => $listing->latitude,
                'longitude' => $listing->longitude,
            ]]);
        } catch (\Throwable $e) {
            Log::error('[LISTING:PAYMENT] Verification Failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function createListingBoostOrder(Request $request, $id)
    {
        Log::info('[LISTING:BOOST] Create Order Attempt', ['listing_id' => $id]);
        try {
            $request->validate(['user_id' => 'required|exists:users,id']);
            $listing = Listing::findOrFail($id);
            $user    = User::findOrFail($request->user_id);

            if ((int) $listing->owner_id !== (int) $user->id) {
                return response()->json(['success' => false, 'message' => 'Only the listing owner can boost this listing.'], 403);
            }

            if ($listing->is_featured && $listing->featured_until && $listing->featured_until->isFuture()) {
                return response()->json(['success' => false, 'message' => 'This listing is already boosted.'], 400);
            }

            $api         = new Api(config('services.razorpay.key_id'), config('services.razorpay.key_secret'));
            $priceConfig = config('prices.listing_boost');
            $amount      = $priceConfig['amount'] * 100;

            $razorpayOrder = $api->order->create([
                'receipt'  => (string) Str::uuid(),
                'amount'   => $amount,
                'currency' => $priceConfig['currency'],
                'notes'    => ['listing_id' => $listing->id, 'user_id' => $user->id, 'type' => 'listing_boost'],
            ]);

            Transaction::create([
                'id'                => (string) Str::uuid(),
                'user_id'           => $user->id,
                'listing_id'        => $listing->id,
                'amount_cents'      => $amount,
                'currency'          => $priceConfig['currency'],
                'razorpay_order_id' => $razorpayOrder['id'],
                'status'            => 'created',
            ]);

            return response()->json([
                'success'  => true,
                'order_id' => $razorpayOrder['id'],
                'amount'   => $amount,
                'currency' => 'INR',
                'key_id'   => config('services.razorpay.key_id'),
                'contact'  => $user->email,
            ]);
        } catch (\Throwable $e) {
            Log::error('[LISTING:BOOST] Order Creation Failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function verifyListingBoostPayment(Request $request, $id)
    {
        Log::info('[LISTING:BOOST] Verification Attempt', ['listing_id' => $id]);
        try {
            $request->validate([
                'user_id'             => 'required|exists:users,id',
                'razorpay_payment_id' => 'required|string',
                'razorpay_order_id'   => 'required|string',
                'razorpay_signature'  => 'required|string',
            ]);
            $listing = Listing::findOrFail($id);
            $user    = User::findOrFail($request->user_id);

            if ((int) $listing->owner_id !== (int) $user->id) {
                return response()->json(['success' => false, 'message' => 'Only the listing owner can boost this listing.'], 403);
            }

            $api = new Api(config('services.razorpay.key_id'), config('services.razorpay.key_secret'));
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id'   => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature'  => $request->razorpay_signature,
            ]);

            $txn = Transaction::where('razorpay_order_id', $request->razorpay_order_id)->first();
            if ($txn) {
                $txn->update([
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'razorpay_signature'  => $request->razorpay_signature,
                    'status'              => 'paid',
                ]);
            }

            $priceConfig = config('prices.listing_boost');
            $listing->update([
                'is_featured'    => true,
                'featured_until' => now()->addDays($priceConfig['duration_days']),
            ]);

            // Email + notification: confirm boost to owner
            if ($user->email) {
                try { Mail::to($user->email)->send(new ListingBoostedMail($user, $listing->fresh(), $priceConfig['duration_days'])); }
                catch (\Throwable $e) { Log::error('[LISTING:BOOST] Email failed', ['error' => $e->getMessage()]); }
            }
            try { $user->notify(new PaymentSuccessNotification("Your listing \"{$listing->title}\" is now featured for {$priceConfig['duration_days']} days.", "/owner/dashboard")); }
            catch (\Throwable $e) { Log::error('[LISTING:BOOST] Notification failed', ['error' => $e->getMessage()]); }

            return response()->json(['success' => true, 'message' => 'Listing boosted! It will be featured for ' . $priceConfig['duration_days'] . ' days.', 'data' => [
                'is_featured'    => true,
                'featured_until' => $listing->featured_until,
            ]]);
        } catch (\Throwable $e) {
            Log::error('[LISTING:BOOST] Verification Failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function createWorkerUnlockOrder(Request $request, $id)
    {
        Log::info('[WORKER:PAYMENT] Create Order Attempt', ['worker_id' => $id]);
        try {
            $request->validate(['user_id' => 'required|exists:users,id']);
            $worker = Worker::with('user:id,phone,email')->findOrFail($id);
            $user   = User::findOrFail($request->user_id);

            $existing = WorkerUnlock::where('worker_id', $worker->id)->where('user_id', $user->id)->first();
            if ($existing) return response()->json(['success' => false, 'message' => 'Already unlocked'], 400);

            $api         = new Api(config('services.razorpay.key_id'), config('services.razorpay.key_secret'));
            $priceConfig = config('prices.worker_unlock');
            $amount      = $priceConfig['amount'] * 100;

            $razorpayOrder = $api->order->create([
                'receipt'  => (string) Str::uuid(),
                'amount'   => $amount,
                'currency' => $priceConfig['currency'],
                'notes'    => ['worker_id' => $worker->id, 'user_id' => $user->id, 'type' => 'worker_unlock'],
            ]);

            Transaction::create([
                'id'                => (string) Str::uuid(),
                'user_id'           => $user->id,
                'worker_id'         => $worker->id,
                'amount_cents'      => $amount,
                'currency'          => $priceConfig['currency'],
                'razorpay_order_id' => $razorpayOrder['id'],
                'status'            => 'created',
            ]);

            return response()->json([
                'success'  => true,
                'order_id' => $razorpayOrder['id'],
                'amount'   => $amount,
                'currency' => 'INR',
                'key_id'   => config('services.razorpay.key_id'),
                'contact'  => $user->email,
            ]);
        } catch (\Throwable $e) {
            Log::error('[WORKER:PAYMENT] Order Creation Failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function verifyWorkerUnlockPayment(Request $request, $id)
    {
        Log::info('[WORKER:PAYMENT] Verification Attempt', ['worker_id' => $id]);
        try {
            $request->validate([
                'user_id'             => 'required|exists:users,id',
                'razorpay_payment_id' => 'required|string',
                'razorpay_order_id'   => 'required|string',
                'razorpay_signature'  => 'required|string',
            ]);
            $worker = Worker::with('user:id,phone')->findOrFail($id);
            $user   = User::findOrFail($request->user_id);

            $existing = WorkerUnlock::where('worker_id', $worker->id)->where('user_id', $user->id)->first();
            if ($existing) {
                return response()->json(['success' => true, 'message' => 'Already unlocked',
                    'data' => ['phone' => $worker->user?->phone, 'service_area' => $worker->locality]]);
            }

            $api = new Api(config('services.razorpay.key_id'), config('services.razorpay.key_secret'));
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id'   => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature'  => $request->razorpay_signature,
            ]);

            $txn = Transaction::where('razorpay_order_id', $request->razorpay_order_id)->first();
            if ($txn) {
                $txn->update([
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'razorpay_signature'  => $request->razorpay_signature,
                    'status'              => 'paid',
                ]);
            }

            $priceConfig = config('prices.worker_unlock');
            WorkerUnlock::create([
                'worker_id'   => $worker->id,
                'user_id'     => $user->id,
                'amount_paid' => $priceConfig['amount'],
                'expires_at'  => now()->addDays(30),
            ]);

            $worker->increment('contact_unlocks');

            // Email: notify worker that someone viewed their contact
            $workerUser = $worker->user;
            if ($workerUser && $workerUser->email) {
                try { Mail::to($workerUser->email)->send(new WorkerUnlockedMail($workerUser, $worker)); }
                catch (\Throwable $e) { Log::error('[WORKER:UNLOCK] Email failed', ['error' => $e->getMessage()]); }
            }

            return response()->json(['success' => true, 'message' => 'Payment successful! Details unlocked.',
                'data' => ['phone' => $worker->user?->phone, 'service_area' => $worker->locality]]);
        } catch (\Throwable $e) {
            Log::error('[WORKER:PAYMENT] Verification Failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function createUnlockPackageOrder(Request $request)
    {
        Log::info('[UNLOCK_PACKAGE:PAYMENT] Create Order Attempt');
        try {
            $request->validate(['user_id' => 'required|exists:users,id']);
            $user = User::findOrFail($request->user_id);

            $api         = new Api(env('RAZORPAY_KEY_ID'), env('RAZORPAY_KEY_SECRET'));
            $priceConfig = config('prices.premium_unlock_package');
            $amount      = $priceConfig['amount'] * 100;

            $razorpayOrder = $api->order->create([
                'receipt'  => (string) Str::uuid(),
                'amount'   => $amount,
                'currency' => $priceConfig['currency'],
                'notes'    => ['user_id' => $user->id, 'type' => 'unlock_package'],
            ]);

            Transaction::create([
                'id'                => (string) Str::uuid(),
                'user_id'           => $user->id,
                'amount_cents'      => $amount,
                'currency'          => $priceConfig['currency'],
                'razorpay_order_id' => $razorpayOrder['id'],
                'status'            => 'created',
            ]);

            return response()->json([
                'success'  => true,
                'order_id' => $razorpayOrder['id'],
                'amount'   => $amount,
                'currency' => 'INR',
                'key_id'   => env('RAZORPAY_KEY_ID'),
                'contact'  => $user->email,
            ]);
        } catch (\Throwable $e) {
            Log::error('[UNLOCK_PACKAGE:PAYMENT] Order Creation Failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function verifyUnlockPackagePayment(Request $request)
    {
        Log::info('[UNLOCK_PACKAGE:PAYMENT] Verification Attempt');
        try {
            $request->validate([
                'user_id'             => 'required|exists:users,id',
                'razorpay_payment_id' => 'required|string',
                'razorpay_order_id'   => 'required|string',
                'razorpay_signature'  => 'required|string',
            ]);
            $user = User::findOrFail($request->user_id);

            $api = new Api(env('RAZORPAY_KEY_ID'), env('RAZORPAY_KEY_SECRET'));
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id'   => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature'  => $request->razorpay_signature,
            ]);

            $txn = Transaction::where('razorpay_order_id', $request->razorpay_order_id)->first();
            if ($txn) {
                $txn->update([
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'razorpay_signature'  => $request->razorpay_signature,
                    'status'              => 'paid',
                ]);
            }

            $priceConfig = config('prices.premium_unlock_package');
            
            // Credit Vastoq Points
            $pointsToCredit = $priceConfig['points'] ?? 100;
            $user->increment('vastoq_points', $pointsToCredit);

            return response()->json([
                'success' => true,
                'message' => 'Pack purchased! ' . $pointsToCredit . ' Vastoq Points credited to your wallet.',
                'data'    => [
                    'vastoq_points'          => $user->fresh()->vastoq_points ?? 0,
                    'free_unlocks_remaining' => $user->free_unlocks_remaining ?? 0,
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('[UNLOCK_PACKAGE:PAYMENT] Verification Failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
