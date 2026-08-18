<?php

namespace App\Http\Controllers;

use App\Http\Resources\DeviceTokenResource;
use App\Models\DeviceToken;
use App\Services\Api\ApiResourceIndexService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function index(ApiResourceIndexService $resources)
    {
        return DeviceTokenResource::collection($resources->deviceTokens());
    }

    /**
     * Register or update a device push token from Service Worker / Mobile app.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token'    => ['required', 'string', 'max:1000'],
            'platform' => ['nullable', 'string', 'in:web,android,ios', 'max:20'],
        ]);

        $user = $request->user();

        $deviceToken = DeviceToken::updateOrCreate(
            [
                'token' => $validated['token'],
            ],
            [
                'user_id'      => $user?->id,
                'platform'     => $validated['platform'] ?? 'web',
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Token perangkat berhasil didaftarkan ke sistem notifikasi.',
            'data'    => [
                'id'           => $deviceToken->id,
                'platform'     => $deviceToken->platform,
                'last_used_at' => $deviceToken->last_used_at?->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Remove a device token when user logs out or revokes permission.
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);

        DeviceToken::where('token', $validated['token'])->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Token perangkat berhasil dihapus.',
        ]);
    }
}
