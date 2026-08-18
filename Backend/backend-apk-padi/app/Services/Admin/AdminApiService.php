<?php

namespace App\Services\Admin;

use App\Helpers\ApiResponse;
use App\Http\Resources\AdminBroadcastResource;
use App\Http\Resources\AuditLogResource;
use App\Http\Resources\UserResource;
use App\Models\AdminBroadcast;
use App\Models\AlertSubscription;
use App\Models\AuditLog;
use App\Models\CommunityReport;
use App\Models\CropSeason;
use App\Models\Farm;
use App\Models\MarketListing;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class AdminApiService
{
    public function handle(Request $request, ?string $resource = null, ?string $id = null): JsonResponse
    {
        return match ($resource) {
            null => $this->overview(),
            'users' => $this->users($request, $id),
            'broadcasts' => $this->broadcasts($request, $id),
            'audit-logs' => $this->auditLogs($request, $id),
            'farms', 'agriculture' => $this->farms($request, $id),
            'weather' => $this->weather($request),
            'soil' => $this->soil($request),
            default => ApiResponse::error('Fitur admin tidak ditemukan.', 404),
        };
    }

    private function overview(): JsonResponse
    {
        return ApiResponse::success('Data admin berhasil diambil.', [
            'summary' => [
                'users_total' => User::query()->count(),
                'users_active' => User::query()->where('status', 'active')->count(),
                'farmers_total' => User::query()->where('role', 'farmer')->count(),
                'buyers_total' => User::query()->where('role', $this->legacyRoleValue('buyer'))->count(),
                'farms_total' => Farm::query()->count(),
                'crop_seasons_total' => CropSeason::query()->count(),
                'market_listings_total' => MarketListing::query()->count(),
                'community_reports_total' => CommunityReport::query()->count(),
                'alert_subscriptions_total' => AlertSubscription::query()->count(),
                'broadcasts_total' => AdminBroadcast::query()->count(),
                'audit_logs_total' => AuditLog::query()->count(),
            ],
            'users' => UserResource::collection(User::query()->latest('id')->limit(8)->get()),
            'broadcasts' => AdminBroadcastResource::collection(
                AdminBroadcast::query()->with('admin')->latest('id')->limit(5)->get(),
            ),
            'audit_logs' => AuditLogResource::collection(
                AuditLog::query()->with('user')->latest('id')->limit(8)->get(),
            ),
        ]);
    }

    private function users(Request $request, ?string $id): JsonResponse
    {
        if ($request->isMethod('get') && $id === null) {
            $users = User::query()
                ->when($request->query('role'), fn ($query, string $role) => $query->where('role', $this->legacyRoleValue($role)))
                ->when($request->query('status'), fn ($query, string $status) => $query->where('status', $status))
                ->when($request->query('q'), function ($query, string $search): void {
                    $query->where(function ($query) use ($search): void {
                        $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
                })
                ->latest('id')
                ->limit($this->limit($request))
                ->get();

            return ApiResponse::success('Data pengguna berhasil diambil.', [
                'users' => UserResource::collection($users),
            ]);
        }

        if ($request->isMethod('patch') && $id !== null) {
            return $this->updateUser($request, $id);
        }

        return ApiResponse::error('Metode admin pengguna tidak didukung.', 405);
    }

    private function updateUser(Request $request, string $id): JsonResponse
    {
        $target = $this->findUser($id);
        if (! $target instanceof User) {
            return $target;
        }

        $data = $request->validate([
            'role' => ['sometimes', 'required', Rule::in(['farmer', 'buyer', 'extension_officer', 'admin'])],
            'status' => ['sometimes', 'required', Rule::in(['active', 'inactive', 'suspended'])],
        ]);

        if ($data === []) {
            return ApiResponse::error('Tidak ada perubahan pengguna yang dikirim.', 422);
        }

        if ($target->is($request->user()) && ($data['status'] ?? 'active') !== 'active') {
            return ApiResponse::error('Admin tidak dapat menonaktifkan akun sendiri.', 422);
        }

        $oldValues = $target->only(['role', 'status']);

        if (array_key_exists('role', $data)) {
            Role::findOrCreate($data['role']);
            $target->role = $this->legacyRoleValue($data['role']);
            $target->syncRoles([$data['role']]);
        }

        if (array_key_exists('status', $data)) {
            $target->status = $data['status'];
        }

        $target->save();
        $target->refresh();
        $this->audit($request, 'admin_user_updated', $target, $oldValues, $target->only(['role', 'status']));

        return ApiResponse::success('Pengguna berhasil diperbarui.', [
            'user' => UserResource::make($target),
        ]);
    }

    private function broadcasts(Request $request, ?string $id): JsonResponse
    {
        if ($request->isMethod('get') && $id === null) {
            $broadcasts = AdminBroadcast::query()
                ->with('admin')
                ->when($request->query('status'), fn ($query, string $status) => $query->where('status', $status))
                ->latest('id')
                ->limit($this->limit($request))
                ->get();

            return ApiResponse::success('Data broadcast berhasil diambil.', [
                'broadcasts' => AdminBroadcastResource::collection($broadcasts),
            ]);
        }

        if ($request->isMethod('post') && $id === null) {
            return $this->createBroadcast($request);
        }

        if ($request->isMethod('patch') && $id !== null) {
            return $this->updateBroadcast($request, $id);
        }

        if ($request->isMethod('delete') && $id !== null) {
            return $this->deleteBroadcast($request, $id);
        }

        return ApiResponse::error('Metode admin broadcast tidak didukung.', 405);
    }

    private function createBroadcast(Request $request): JsonResponse
    {
        $data = $this->validateBroadcast($request, true);
        $data['admin_id'] = $request->user()->id;

        if (($data['status'] ?? 'draft') === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $broadcast = AdminBroadcast::query()->create($data)->load('admin');
        $this->audit($request, 'admin_broadcast_created', $broadcast, null, $broadcast->toArray());

        return ApiResponse::success('Broadcast berhasil dibuat.', [
            'broadcast' => AdminBroadcastResource::make($broadcast),
        ], 201);
    }

    private function updateBroadcast(Request $request, string $id): JsonResponse
    {
        $broadcast = $this->findBroadcast($id);
        if (! $broadcast instanceof AdminBroadcast) {
            return $broadcast;
        }

        $data = $this->validateBroadcast($request, false);
        if ($data === []) {
            return ApiResponse::error('Tidak ada perubahan broadcast yang dikirim.', 422);
        }

        if (($data['status'] ?? null) === 'published' && empty($data['published_at']) && $broadcast->published_at === null) {
            $data['published_at'] = now();
        }

        $oldValues = $broadcast->toArray();
        $broadcast->fill($data);
        $broadcast->save();
        $broadcast->load('admin');
        $this->audit($request, 'admin_broadcast_updated', $broadcast, $oldValues, $broadcast->toArray());

        return ApiResponse::success('Broadcast berhasil diperbarui.', [
            'broadcast' => AdminBroadcastResource::make($broadcast),
        ]);
    }

    private function deleteBroadcast(Request $request, string $id): JsonResponse
    {
        $broadcast = $this->findBroadcast($id);
        if (! $broadcast instanceof AdminBroadcast) {
            return $broadcast;
        }

        $oldValues = $broadcast->toArray();
        $broadcast->delete();
        $this->audit($request, 'admin_broadcast_deleted', AdminBroadcast::class, $oldValues, null, (int) $id);

        return ApiResponse::success('Broadcast berhasil dihapus.');
    }

    private function auditLogs(Request $request, ?string $id): JsonResponse
    {
        if (! $request->isMethod('get') || $id !== null) {
            return ApiResponse::error('Metode admin audit log tidak didukung.', 405);
        }

        $logs = AuditLog::query()
            ->with('user')
            ->when($request->query('action'), fn ($query, string $action) => $query->where('action', $action))
            ->latest('id')
            ->limit($this->limit($request))
            ->get();

        return ApiResponse::success('Data audit log berhasil diambil.', [
            'audit_logs' => AuditLogResource::collection($logs),
        ]);
    }

    private function farms(Request $request, ?string $id): JsonResponse
    {
        $farms = Farm::query()
            ->with(['farmer', 'weatherSnapshots' => fn ($q) => $q->latest('observed_at')])
            ->when($request->query('q'), function ($query, string $search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhereHas('farmer', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            })
            ->latest('id')
            ->limit($this->limit($request))
            ->get();

        return ApiResponse::success('Data lahan pertanian admin berhasil diambil.', [
            'farms' => $farms,
        ]);
    }

    private function weather(Request $request): JsonResponse
    {
        $stats = [
            'total_farms' => Farm::query()->count(),
            'farms_with_weather' => \App\Models\WeatherSnapshot::query()->distinct('farm_id')->count('farm_id'),
            'total_snapshots' => \App\Models\WeatherSnapshot::query()->count(),
            'expired_snapshots' => \App\Models\WeatherSnapshot::query()->where('expires_at', '<', now())->count(),
        ];

        $latestSnapshots = \App\Models\WeatherSnapshot::query()
            ->with('farm.farmer')
            ->latest('observed_at')
            ->limit($this->limit($request))
            ->get();

        return ApiResponse::success('Data pemantauan cuaca admin berhasil diambil.', [
            'stats' => $stats,
            'latest_snapshots' => $latestSnapshots,
        ]);
    }

    private function soil(Request $request): JsonResponse
    {
        $stats = [
            'total_samples' => \App\Models\SoilDetection::count(),
            'avg_ph' => round(\App\Models\SoilDetection::avg('ph_level') ?? 6.5, 2),
            'optimal_count' => \App\Models\SoilDetection::where('soil_status', 'optimal')->count(),
            'critical_count' => \App\Models\SoilDetection::where('soil_status', 'critical')->count(),
        ];

        $detections = \App\Models\SoilDetection::query()
            ->with('farm.farmer')
            ->latest('tested_at')
            ->limit($this->limit($request))
            ->get();

        return ApiResponse::success('Data analisis tanah admin berhasil diambil.', [
            'stats' => $stats,
            'detections' => $detections,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateBroadcast(Request $request, bool $creating): array
    {
        $required = $creating ? 'required' : 'sometimes';

        return $request->validate([
            'title' => [$required, 'string', 'max:150'],
            'message' => [$required, 'string', 'max:5000'],
            'type' => ['sometimes', 'required', Rule::in(['info', 'warning', 'announcement', 'system'])],
            'status' => ['sometimes', 'required', Rule::in(['draft', 'published', 'expired'])],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:published_at'],
        ]);
    }

    private function findUser(string $id): User|JsonResponse
    {
        if (! ctype_digit($id)) {
            return ApiResponse::error('ID pengguna tidak valid.', 422);
        }

        return User::query()->find((int) $id) ?? ApiResponse::error('Pengguna tidak ditemukan.', 404);
    }

    private function findBroadcast(string $id): AdminBroadcast|JsonResponse
    {
        if (! ctype_digit($id)) {
            return ApiResponse::error('ID broadcast tidak valid.', 422);
        }

        return AdminBroadcast::query()->find((int) $id) ?? ApiResponse::error('Broadcast tidak ditemukan.', 404);
    }

    private function limit(Request $request): int
    {
        return min(max((int) $request->query('limit', 25), 1), 50);
    }

    private function legacyRoleValue(string $role): string
    {
        return match ($role) {
            'buyer' => 'partner',
            'extension_officer' => 'ppl',
            default => $role,
        };
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    private function audit(
        Request $request,
        string $action,
        User|AdminBroadcast|string $entity,
        ?array $oldValues,
        ?array $newValues,
        ?int $entityId = null,
    ): void {
        AuditLog::query()->create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'entity_type' => is_string($entity) ? $entity : $entity::class,
            'entity_id' => $entityId ?? (is_string($entity) ? null : $entity->getKey()),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);
    }
}
