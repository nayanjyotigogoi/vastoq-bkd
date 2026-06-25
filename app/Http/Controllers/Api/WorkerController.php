<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\User;
use Illuminate\Http\Request;

class WorkerController extends Controller
{
    /**
     * GET /workers — public list with filters
     */
    public function index(Request $request)
    {
        $query = Worker::with('user:id,name,phone,profile_photo_url,is_verified')
            ->where('is_active', true);

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('category', 'like', $s)
                  ->orWhere('bio', 'like', $s)
                  ->orWhere('city', 'like', $s)
                  ->orWhere('locality', 'like', $s)
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $s));
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('city')) {
            $this->applyCityFilter($query, $request->city);
        }

        if ($request->boolean('available_today')) {
            $query->where('available_today', true);
        }

        if ($request->boolean('verified_only')) {
            $query->where('is_verified', true);
        }

        $perPage = min((int) $request->get('per_page', 20), 500);
        $workers = $query->orderByDesc('rating')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => [
                'data'         => array_map([$this, 'format'], $workers->items()),
                'total'        => $workers->total(),
                'current_page' => $workers->currentPage(),
                'per_page'     => $workers->perPage(),
                'last_page'    => $workers->lastPage(),
            ],
        ]);
    }

    /**
     * GET /workers/{id} — single worker (public)
     */
    public function show($id)
    {
        $worker = Worker::with('user:id,name,phone,profile_photo_url,is_verified')
            ->findOrFail($id);

        $worker->increment('view_count');

        return response()->json([
            'success' => true,
            'data'    => ['worker' => $this->format($worker)],
        ]);
    }

    /**
     * PATCH /workers/{id} — admin actions
     */
    public function adminAction(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:verify,deactivate,activate,reject_aadhaar',
        ]);

        $worker = Worker::findOrFail($id);

        match ($request->action) {
            'verify'         => $worker->update(['is_verified' => true,  'aadhaar_status' => 'verified']),
            'reject_aadhaar' => $worker->update(['is_verified' => false, 'aadhaar_status' => 'rejected']),
            'deactivate'     => $worker->update(['is_active' => false]),
            'activate'       => $worker->update(['is_active' => true]),
            default          => null,
        };

        $worker->load('user:id,name,phone,profile_photo_url,is_verified');

        return response()->json([
            'success' => true,
            'message' => 'Worker updated successfully.',
            'data'    => ['worker' => $this->format($worker)],
        ]);
    }

    /**
     * GET /worker/profile?user_id=X
     * Returns the authenticated worker's own profile.
     */
    public function profile(Request $request)
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
                'error'   => ['message' => 'Worker profile not found. Please complete your profile setup.'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => ['worker' => $this->format($worker)],
        ]);
    }

    /**
     * POST /worker/profile
     * Create a worker profile for a user with role "worker".
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id'   => 'required|exists:users,id',
            'category'  => 'required|string|max:100',
            'bio'       => 'nullable|string|max:1000',
            'city'      => 'nullable|string|max:100',
            'locality'  => 'nullable|string|max:255',
            'rate_per_day' => 'nullable|integer|min:0',
            'skills'    => 'nullable|array',
            'skills.*'  => 'string|max:100',
            'service_areas' => 'nullable|array',
        ]);

        $existing = Worker::where('user_id', $request->user_id)->first();
        if ($existing) {
            return response()->json([
                'success' => false,
                'error'   => ['message' => 'Worker profile already exists.'],
            ], 409);
        }

        $worker = Worker::create([
            'user_id'      => $request->user_id,
            'category'     => $request->category,
            'bio'          => $request->bio,
            'city'         => $request->city ?? 'Guwahati',
            'locality'     => $request->locality,
            'rate_per_day' => $request->rate_per_day,
            'skills'       => $request->skills ?? [],
            'service_areas' => $request->service_areas ?? [],
        ]);

        $worker->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Worker profile created successfully.',
            'data'    => ['worker' => $this->format($worker)],
        ], 201);
    }

    /**
     * PUT /worker/profile
     * Update the worker's own profile fields.
     */
    public function update(Request $request)
    {
        $request->validate([
            'user_id'      => 'required|exists:users,id',
            'category'     => 'nullable|string|max:100',
            'bio'          => 'nullable|string|max:1000',
            'city'         => 'nullable|string|max:100',
            'locality'     => 'nullable|string|max:255',
            'rate_per_day' => 'nullable|integer|min:0',
            'skills'       => 'nullable|array',
            'skills.*'     => 'string|max:100',
            'service_areas' => 'nullable|array',
            'available_today' => 'nullable|boolean',
        ]);

        $worker = Worker::where('user_id', $request->user_id)->first();

        if (!$worker) {
            return response()->json([
                'success' => false,
                'error'   => ['message' => 'Worker profile not found.'],
            ], 404);
        }

        $worker->update($request->only([
            'category', 'bio', 'city', 'locality',
            'rate_per_day', 'skills', 'service_areas', 'available_today',
        ]));

        $worker->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data'    => ['worker' => $this->format($worker)],
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Apply a progressive city filter: try the full term first, then drop the
     * last word one at a time until at least one worker matches.
     * Also searches service_areas JSON so suburb-level terms still find workers.
     *
     * e.g. "Dibrugarh West" → tries "Dibrugarh West" → 0 hits → tries "Dibrugarh" → hits found ✓
     */
    private function applyCityFilter($query, string $rawCity): void
    {
        $words = array_values(array_filter(explode(' ', trim($rawCity))));

        while (count($words) > 0) {
            $term  = implode(' ', $words);
            $like  = '%' . $term . '%';

            $count = (clone $query)->where(function ($q) use ($like) {
                $q->where('city', 'like', $like)
                  ->orWhere('locality', 'like', $like)
                  ->orWhereRaw("JSON_SEARCH(service_areas, 'one', ?) IS NOT NULL", [$like]);
            })->count();

            if ($count > 0 || count($words) === 1) {
                $query->where(function ($q) use ($like) {
                    $q->where('city', 'like', $like)
                      ->orWhere('locality', 'like', $like)
                      ->orWhereRaw("JSON_SEARCH(service_areas, 'one', ?) IS NOT NULL", [$like]);
                });
                return;
            }

            array_pop($words);
        }
    }

    private function format(Worker $worker): array
    {
        return [
            'id'              => $worker->id,
            'user_id'         => $worker->user_id,
            'name'            => $worker->user->name ?? '',
            'phone'           => $worker->user->phone ?? '',
            'category'        => $worker->category,
            'skills'          => $worker->skills ?? [],
            'bio'             => $worker->bio,
            'city'            => $worker->city,
            'locality'        => $worker->locality,
            'rate_per_day'    => $worker->rate_per_day,
            'photo_url'       => $worker->photo_url ?? $worker->user->profile_photo_url ?? null,
            'rating'          => $worker->rating,
            'review_count'    => $worker->review_count,
            'view_count'      => $worker->view_count,
            'contact_unlocks' => $worker->contact_unlocks,
            'jobs_completed'  => $worker->jobs_completed,
            'is_verified'     => $worker->is_verified,
            'aadhaar_status'  => $worker->aadhaar_status,
            'is_active'       => $worker->is_active,
            'available_today' => $worker->available_today,
            'service_areas'   => $worker->service_areas ?? [],
            'created_at'      => $worker->created_at,
        ];
    }
}
