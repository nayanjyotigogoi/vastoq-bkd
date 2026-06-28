<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

            $api         = new Api(env('RAZORPAY_KEY_ID'), env('RAZORPAY_KEY_SECRET'));
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
                'key_id'   => env('RAZORPAY_KEY_ID'),
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

            $priceConfig = config('prices.listing_unlock');
            ListingUnlock::create([
                'listing_id'  => $listing->id,
                'user_id'     => $user->id,
                'amount_paid' => $priceConfig['amount'],
                'expires_at'  => now()->addDays(30),
            ]);

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

    public function createWorkerUnlockOrder(Request $request, $id)
    {
        Log::info('[WORKER:PAYMENT] Create Order Attempt', ['worker_id' => $id]);
        try {
            $request->validate(['user_id' => 'required|exists:users,id']);
            $worker = Worker::with('user:id,phone,email')->findOrFail($id);
            $user   = User::findOrFail($request->user_id);

            $existing = WorkerUnlock::where('worker_id', $worker->id)->where('user_id', $user->id)->first();
            if ($existing) return response()->json(['success' => false, 'message' => 'Already unlocked'], 400);

            $api         = new Api(env('RAZORPAY_KEY_ID'), env('RAZORPAY_KEY_SECRET'));
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
                'key_id'   => env('RAZORPAY_KEY_ID'),
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

            $priceConfig = config('prices.worker_unlock');
            WorkerUnlock::create([
                'worker_id'   => $worker->id,
                'user_id'     => $user->id,
                'amount_paid' => $priceConfig['amount'],
                'expires_at'  => now()->addDays(30),
            ]);

            return response()->json(['success' => true, 'message' => 'Payment successful! Details unlocked.',
                'data' => ['phone' => $worker->user?->phone, 'service_area' => $worker->locality]]);
        } catch (\Throwable $e) {
            Log::error('[WORKER:PAYMENT] Verification Failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
