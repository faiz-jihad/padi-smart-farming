<?php

namespace App\Http\Controllers;

use App\Events\AdminNotificationCreated;
use App\Http\Resources\NotificationResource;
use App\Models\DeviceToken;
use App\Models\Notification;
use App\Services\Api\ApiResourceIndexService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(ApiResourceIndexService $resources)
    {
        return NotificationResource::collection($resources->notifications());
    }

    /**
     * Dispatch a notification payload to device service workers / database.
     */
    public function sendPush(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'title'   => ['required', 'string', 'max:255'],
            'body'    => ['required', 'string', 'max:1000'],
            'type'    => ['nullable', 'string', 'max:50'],
            'url'     => ['nullable', 'string', 'max:500'],
            'icon'    => ['nullable', 'string', 'max:500'],
            'data'    => ['nullable', 'array'],
        ]);

        $userId = $validated['user_id'] ?? $request->user()?->id;

        // 1. Create database notification
        $notification = Notification::create([
            'user_id' => $userId,
            'title'   => $validated['title'],
            'body'    => $validated['body'],
            'type'    => $validated['type'] ?? 'system',
            'data'    => array_merge($validated['data'] ?? [], [
                'url'  => $validated['url'] ?? url('/'),
                'icon' => $validated['icon'] ?? asset('images/padi-logo.png'),
            ]),
        ]);

        // 2. Dispatch realtime broadcast event for web/service worker push
        event(new AdminNotificationCreated($notification));

        // 3. Count target device tokens
        $tokensCount = $userId
            ? DeviceToken::where('user_id', $userId)->count()
            : DeviceToken::count();

        return response()->json([
            'status'  => 'success',
            'message' => 'Notifikasi berhasil dikirim ke antrean perangkat.',
            'data'    => [
                'notification'   => $notification,
                'devices_target' => $tokensCount,
                'payload'        => [
                    'title' => $notification->title,
                    'body'  => $notification->body,
                    'url'   => $validated['url'] ?? url('/'),
                    'icon'  => $validated['icon'] ?? asset('images/padi-logo.png'),
                ],
            ],
        ], 201);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        $notification->update(['read_at' => now()]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Notifikasi ditandai telah dibaca.',
        ]);
    }
}
