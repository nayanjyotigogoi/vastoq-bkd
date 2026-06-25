<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * POST /auth/login
     * Authenticate with phone + password.
     */
    public function login(Request $request)
    {
        $request->validate([
            'phone'    => 'required|digits:10',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error'   => ['message' => 'No account found with this phone number.'],
            ], 401);
        }

        if ($user->is_blocked) {
            return response()->json([
                'success' => false,
                'error'   => ['message' => 'Your account has been blocked. Please contact support.'],
            ], 403);
        }

        if (!$user->password || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'error'   => ['message' => 'Incorrect password.'],
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user'        => $this->formatUser($user),
                'redirect_to' => $this->getRedirectPath($user->role),
            ],
        ]);
    }

    /**
     * POST /auth/register
     * Create a new account with name, phone, password, and role.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'required|digits:10|unique:users,phone',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:tenant,owner,worker',
        ]);

        $user = User::create([
            'name'        => $request->name,
            'phone'       => $request->phone,
            'password'    => Hash::make($request->password),
            'role'        => $request->role,
            'is_verified' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully.',
            'data' => [
                'user'        => $this->formatUser($user),
                'redirect_to' => $this->getRedirectPath($user->role),
            ],
        ], 201);
    }

    /**
     * GET /auth/me?user_id=X
     * Returns the current user's full profile.
     */
    public function me(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $this->formatUser($user),
            ],
        ]);
    }

    /**
     * POST /auth/logout
     */
    public function logout()
    {
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * POST /auth/update-profile
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|max:255',
        ]);

        $user = User::findOrFail($request->user_id);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data'    => ['user' => $this->formatUser($user)],
        ]);
    }

    /**
     * POST /auth/change-password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'user_id'      => 'required|exists:users,id',
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        $user = User::findOrFail($request->user_id);

        if ($user->password && !Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'success' => false,
                'error'   => ['message' => 'Current password is incorrect.'],
            ], 422);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function formatUser(User $user): array
    {
        return [
            'id'                => $user->id,
            'name'              => $user->name,
            'phone'             => $user->phone,
            'email'             => $user->email,
            'role'              => $user->role,
            'credit_balance'    => $user->credit_balance ?? 0,
            'is_verified'       => $user->is_verified,
            'profile_photo_url' => $user->profile_photo_url,
        ];
    }

    private function getRedirectPath(?string $role): string
    {
        return match ($role) {
            'owner'  => '/owner/dashboard',
            'worker' => '/worker/dashboard',
            'admin'  => '/admin',
            default  => '/dashboard',
        };
    }
}
