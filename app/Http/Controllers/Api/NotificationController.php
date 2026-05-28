<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function unread(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Use toBase() to convert Eloquent\Collection → Support\Collection before
        // mapping to plain arrays. Without this, map() returns an Eloquent\Collection
        // whose merge() implementation calls getKey() on each item, which fails for arrays.
        $notifications = $user->unreadNotifications()
            ->latest()
            ->take(10)
            ->get()
            ->toBase()
            ->map(fn($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'data'       => $n->data,
                'read_at'    => $n->read_at,
                'created_at' => $n->created_at->diffForHumans(),
            ]);

        $recent = $user->readNotifications()
            ->latest()
            ->take(5)
            ->get()
            ->toBase()
            ->map(fn($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'data'       => $n->data,
                'read_at'    => $n->read_at,
                'created_at' => $n->created_at->diffForHumans(),
            ]);

        $all = $notifications->merge($recent)->sortByDesc('created_at')->values();

        return response()->json([
            'notifications' => $all,
            'unread_count'  => $user->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user         = Auth::user();
        $notification = $user->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    public function markAllRead(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->unreadNotifications()->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }
}
