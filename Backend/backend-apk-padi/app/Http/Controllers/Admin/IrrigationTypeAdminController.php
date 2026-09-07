<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreIrrigationTypeRequest;
use App\Http\Requests\Admin\UpdateIrrigationTypeRequest;
use App\Models\IrrigationType;
use App\Services\Admin\AdminAuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class IrrigationTypeAdminController extends Controller
{
    public function __construct(
        private AdminAuditLogger $auditLogger
    ) {}

    /**
     * Get list of irrigation types as JSON
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => IrrigationType::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a new master irrigation type via AJAX
     */
    public function store(StoreIrrigationTypeRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $name = trim($validated['name']);
            $code = ! empty($validated['code'])
                ? Str::slug($validated['code'], '_')
                : Str::slug($name, '_');

            if (IrrigationType::where('code', $code)->exists() && empty($validated['code'])) {
                $code .= '_' . Str::lower(Str::random(4));
            }

            $irrigationType = IrrigationType::create([
                'name' => $name,
                'code' => $code,
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'created_by' => auth()->id(),
            ]);

            $this->auditLogger->write('admin_irrigation_type_created', $irrigationType, null, $irrigationType->toArray(), $request);

            return response()->json([
                'success' => true,
                'message' => "Tipe irigasi '{$irrigationType->name}' berhasil ditambahkan ke master data.",
                'data' => $irrigationType,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan tipe irigasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing irrigation type via AJAX
     */
    public function update(UpdateIrrigationTypeRequest $request, IrrigationType $irrigationType): JsonResponse
    {
        try {
            $oldValues = $irrigationType->toArray();
            $validated = $request->validated();

            if (isset($validated['name'])) {
                $validated['name'] = trim($validated['name']);
            }
            if (isset($validated['code'])) {
                $validated['code'] = Str::slug($validated['code'], '_');
            }

            $irrigationType->update($validated);

            $this->auditLogger->write('admin_irrigation_type_updated', $irrigationType, $oldValues, $irrigationType->toArray(), $request);

            return response()->json([
                'success' => true,
                'message' => "Tipe irigasi '{$irrigationType->name}' berhasil diperbarui.",
                'data' => $irrigationType,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui tipe irigasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle active status of an irrigation type
     */
    public function toggleStatus(IrrigationType $irrigationType): JsonResponse
    {
        try {
            $oldValues = $irrigationType->toArray();
            $irrigationType->update(['is_active' => ! $irrigationType->is_active]);

            $statusText = $irrigationType->is_active ? 'diaktifkan' : 'dinonaktifkan';
            $this->auditLogger->write('admin_irrigation_type_status_toggled', $irrigationType, $oldValues, $irrigationType->toArray(), request());

            return response()->json([
                'success' => true,
                'message' => "Tipe irigasi '{$irrigationType->name}' berhasil {$statusText}.",
                'data' => $irrigationType,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status tipe irigasi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
