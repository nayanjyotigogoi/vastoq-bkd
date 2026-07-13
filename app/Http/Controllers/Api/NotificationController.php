<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $user = User::findOrFail($request->user_id);

        $notifications = $user->notifications()
            ->latest()
            ->take(30)
            ->get()
            ->map(fn ($n) => [
                'id'         => $n->id,
                'type'       => $n->data['type'] ?? 'general',
                'title'      => $n->data['title'] ?? '',
                'body'       => $n->data['body'] ?? '',
                'url'        => $n->data['url'] ?? null,
                'icon'       => $n->data['icon'] ?? 'bell',
                'read'       => !is_null($n->read_at),
                'created_at' => $n->created_at->toISOString(),
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications,
                'unread_count'  => $user->unreadNotifications()->count(),
            ],
        ]);
    }

    public function markRead(Request $request, string $id)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $user         = User::findOrFail($request->user_id);
        $notification = $user->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        User::findOrFail($request->user_id)->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
