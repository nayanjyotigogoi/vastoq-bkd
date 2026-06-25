<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
   public function tenant(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail(
            $request->user_id
        );

        $listingUnlocks = $user
            ->listingUnlocks()
            ->with(['listing.owner'])
            ->latest()
            ->get();

        $workerUnlocks = $user
            ->workerUnlocks()
            ->with(['worker.user'])
            ->latest()
            ->get();

        $savedListings = $user
            ->savedListings()
            ->with(['listing.owner'])
            ->latest()
            ->get();

        $formattedWorkerUnlocks = $workerUnlocks->map(function ($wu) {
            $worker = $wu->worker;
            return [
                'id'         => $wu->id,
                'unlocked_at'=> $wu->created_at,
                'expires_at' => $wu->expires_at,
                'worker'     => $worker ? [
                    'id'           => $worker->id,
                    'name'         => $worker->user->name ?? '',
                    'phone'        => $worker->user->phone ?? '',
                    'category'     => $worker->category,
                    'locality'     => $worker->locality,
                    'city'         => $worker->city,
                    'is_verified'  => $worker->is_verified,
                    'service_areas'=> $worker->service_areas ?? [],
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'unlocks_used'   => $listingUnlocks->count() + $workerUnlocks->count(),
                    'saved_listings' => $savedListings->count(),
                    'unlock_credits' => $user->credit_balance ?? 0,
                ],
                'unlocks'             => $listingUnlocks,
                'worker_unlocks'      => $formattedWorkerUnlocks,
                'saved_listings_data' => $savedListings,
            ],
        ]);
    }

    public function worker(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $worker = Worker::where('user_id', $request->user_id)
            ->with('user')
            ->first();

        if (!$worker) {
            return response()->json([
                'success' => false,
                'error'   => ['message' => 'Worker profile not found.'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'worker' => [
                    'id'              => $worker->id,
                    'name'            => $worker->user->name ?? '',
                    'phone'           => $worker->user->phone ?? '',
                    'category'        => $worker->category,
                    'skills'          => $worker->skills ?? [],
                    'bio'             => $worker->bio,
                    'locality'        => $worker->locality,
                    'city'            => $worker->city,
                    'rate_per_day'    => $worker->rate_per_day,
                    'is_verified'     => $worker->is_verified,
                    'aadhaar_status'  => $worker->aadhaar_status,
                    'available_today' => $worker->available_today,
                    'rating'          => $worker->rating,
                    'review_count'    => $worker->review_count,
                ],
                'stats' => [
                    'profile_views'   => $worker->view_count,
                    'contact_unlocks' => $worker->contact_unlocks,
                    'rating'          => $worker->rating,
                    'jobs_completed'  => $worker->jobs_completed,
                    'review_count'    => $worker->review_count,
                ],
                'reviews' => [],
            ],
        ]);
    }
}