<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Events\AdminNotificationCreated;
use App\Http\Resources\NotificationResource;
use App\Models\DeviceToken;
use App\Models\Notification;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Notification::query();

        if ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereNull('user_id');
            });
        }

        // Auto-seed initial notifications if completely empty for good UX
        if ($query->count() === 0) {
            $this->seedInitialNotifications($user?->id);
        }

        $notifications = $query->latest('created_at')->paginate(30);

        return NotificationResource::collection($notifications);
    }

    private function seedInitialNotifications(?int $userId): void
    {
        $now = now();
        $defaults = [
            [
                'user_id' => $userId,
                'type' => 'crop_alert',
                'title' => 'Pengingat Pemupukan Susulan I (HST 14)',
                'body' => 'Waktunya pemupukan NPK Phonska dan Urea untuk merangsang anakan produktif padi.',
                'data' => ['url' => '/farms'],
                'created_at' => $now->subMinutes(15),
            ],
            [
                'user_id' => $userId,
                'type' => 'warning',
                'title' => 'Peringatan Hama: Waspada Blas Daun',
                'body' => 'Kelembaban tinggi terdeteksi di wilayah sekitar. Pantau bercak cokelat belah ketupat pada daun.',
                'data' => ['url' => '/community-alert'],
                'created_at' => $now->subHours(2),
            ],
            [
                'user_id' => $userId,
                'type' => 'marketplace_deal',
                'title' => 'Tren Harga Gabah Hari Ini',
                'body' => 'Harga GKP rata-rata Rp 6.800/kg dan GKG Rp 7.900/kg. Cek penawaran pembeli di Toko PADI.',
                'data' => ['url' => '/marketplace'],
                'created_at' => $now->subHours(5),
            ],
            [
                'user_id' => $userId,
                'type' => 'system',
                'title' => 'Diagnosa Gemini AI Siap Digunakan',
                'body' => 'Gunakan kamera untuk memindai daun padi Anda dan dapatkan resep obat serta racikan nabati otomatis.',
                'data' => ['url' => '/plant-check'],
                'created_at' => $now->subDays(1),
            ],
        ];

        foreach ($defaults as $d) {
            Notification::create($d);
        }
    }

    /**
     * Dispatch a notification payload to device service workers / database per role or target user.
     */
    public function sendPush(Request $request, AdminNotificationService $notificationService): JsonResponse
    {
        $validated = $request->validate([
            'user_id'     => ['nullable', 'integer', 'exists:users,id'],
            'target_role' => ['nullable', 'string', 'in:farmer,extension_officer,buyer,admin,all'],
            'title'       => ['required', 'string', 'max:255'],
            'body'        => ['required', 'string', 'max:1000'],
            'type'        => ['nullable', 'string', 'max:50'],
            'url'         => ['nullable', 'string', 'max:500'],
            'icon'        => ['nullable', 'string', 'max:500'],
            'data'        => ['nullable', 'array'],
        ]);

        $title = $validated['title'];
        $body = $validated['body'];
        $type = $validated['type'] ?? 'system';
        $data = array_merge($validated['data'] ?? [], [
            'url'  => $validated['url'] ?? url('/'),
            'icon' => $validated['icon'] ?? asset('images/padi-logo.png'),
        ]);

        $dispatchedCount = 0;

        if (!empty($validated['target_role'])) {
            $role = $validated['target_role'];
            if ($role === 'all') {
                $notificationService->notifyAll($title, $body, $type, $data);
                $dispatchedCount = 1;
            } elseif ($role === UserRole::Farmer->value) {
                $dispatchedCount = $notificationService->notifyFarmers($title, $body, $type, $data);
            } elseif ($role === UserRole::ExtensionOfficer->value) {
                $dispatchedCount = $notificationService->notifyExtensionOfficers($title, $body, $type, $data);
            } elseif ($role === UserRole::Buyer->value) {
                $dispatchedCount = $notificationService->notifyBuyers($title, $body, $type, $data);
            } elseif ($role === UserRole::Admin->value) {
                $notificationService->notifyAdmins($title, $body, $type, $data);
                $dispatchedCount = 1;
            }
        } elseif (!empty($validated['user_id'])) {
            $notification = $notificationService->notifyUser($validated['user_id'], $title, $body, $type, $data);
            $dispatchedCount = $notification ? 1 : 0;
        } else {
            $userId = $request->user()?->id;
            if ($userId) {
                $notificationService->notifyUser($userId, $title, $body, $type, $data);
                $dispatchedCount = 1;
            } else {
                $notificationService->notifyAll($title, $body, $type, $data);
                $dispatchedCount = 1;
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => "Notifikasi berhasil dikirimkan ke {$dispatchedCount} penerima/perangkat.",
            'data'    => [
                'dispatched_count' => $dispatchedCount,
                'target_role'      => $validated['target_role'] ?? null,
                'payload'          => [
                    'title' => $title,
                    'body'  => $body,
                    'type'  => $type,
                    'url'   => $data['url'],
                ],
            ],
        ], 201);
    }

    /**
     * Store or update device push token for the authenticated user.
     */
    public function registerDeviceToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token'    => ['required', 'string', 'max:500'],
            'platform' => ['nullable', 'string', 'in:android,ios,web_sw,flutter'],
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $deviceToken = DeviceToken::updateOrCreate(
            ['token' => $validated['token']],
            [
                'user_id'      => $user->id,
                'platform'     => $validated['platform'] ?? 'flutter',
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Token perangkat berhasil didaftarkan.',
            'data'    => $deviceToken,
        ]);
    }

    /**
     * Mark single notification as read.
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        $user = $request->user();

        $isAdmin = $user && ($user->role === 'admin' || (method_exists($user, 'hasRole') && $user->hasRole('admin')));

        if (! $isAdmin && $notification->user_id !== null && (! $user || $notification->user_id !== $user->id)) {
            abort(403, 'Anda tidak memiliki akses ke notifikasi ini.');
        }

        $notification->update(['read_at' => now()]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Notifikasi ditandai telah dibaca.',
        ]);
    }

    /**
     * Mark all notifications for the authenticated user as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Semua notifikasi berhasil ditandai telah dibaca.',
        ]);
    }
}
