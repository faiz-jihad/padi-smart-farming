<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\CropSeason;
use App\Models\DiseaseScan;
use App\Models\Farm;
use App\Models\FarmActivity;
use App\Models\Harvest;
use App\Models\IrrigationSchedule;
use App\Models\PplValidation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FarmTimelineController extends Controller
{
    /**
     * Get aggregated multi-event timeline for a farm and active season:
     * - Cultivation activities (Activities)
     * - AI Leaf scans & diagnoses (Diagnosis)
     * - Extension officer validations (PPL Validations)
     * - Irrigation water turns (Irrigation)
     * - Harvest events & yield quality (Harvests)
     */
    public function index(Request $request, Farm $farm): JsonResponse
    {
        $user = $request->user();

        // Access check
        if ($farm->farmer_user_id !== $user->id && ! $user->hasRole('admin') && ! $user->hasRole('extension_officer')) {
            return ApiResponse::error('Akses ditolak untuk lahan ini.', 403);
        }

        $activeSeason = CropSeason::where('farm_id', $farm->id)
            ->where('status', 'active')
            ->with('variety')
            ->latest('id')
            ->first();

        $seasonStartDate = $activeSeason?->planting_date 
            ? Carbon::parse($activeSeason->planting_date) 
            : ($activeSeason?->planned_planting_date ? Carbon::parse($activeSeason->planned_planting_date) : now()->subMonths(4));

        $timelineEvents = collect();

        // 1. Farm Activities
        $activities = FarmActivity::whereHas('cropSeason', fn ($q) => $q->where('farm_id', $farm->id))
            ->get();

        foreach ($activities as $act) {
            $date = $act->occurred_at ? Carbon::parse($act->occurred_at) : now();
            $timelineEvents->push([
                'id'          => 'activity_' . $act->id,
                'category'    => 'activity',
                'title'       => ucfirst($act->type ?? 'Aktivitas Tani'),
                'description' => $act->notes ?: 'Pencatatan kegiatan budidaya lahan.',
                'occurred_at' => $date->toIso8601String(),
                'date_human'  => $date->translatedFormat('d M Y, H:i'),
                'status'      => 'completed',
                'icon'        => 'activity',
                'cost'        => $act->cost ? (double) $act->cost : null,
                'extra'       => [
                    'activity_id' => $act->id,
                    'type'        => $act->type,
                ],
            ]);
        }

        // 2. Disease Scans
        $scans = DiseaseScan::where('farm_id', $farm->id)
            ->where('scanned_at', '>=', $seasonStartDate)
            ->with('recommendation')
            ->get();

        foreach ($scans as $scan) {
            $date = $scan->scanned_at ? Carbon::parse($scan->scanned_at) : now();
            $isHealthy = $scan->quality_status === 'healthy';
            $confidencePct = $scan->confidence ? round((float) $scan->confidence * 100, 1) . '%' : '-';

            $timelineEvents->push([
                'id'          => 'scan_' . $scan->id,
                'category'    => 'diagnosis',
                'title'       => $isHealthy ? 'Pemeriksaan Daun: Kondisi Sehat' : "Diagnosa AI: {$scan->predicted_class}",
                'description' => $isHealthy
                    ? 'Tanaman dalam kondisi optimal dan bebas dari serangan hama/patogen.'
                    : "Terdeteksi keyakinan {$confidencePct}. " . ($scan->recommendation?->action ?: 'Segera lakukan tindakan isolasi.'),
                'occurred_at' => $date->toIso8601String(),
                'date_human'  => $date->translatedFormat('d M Y, H:i'),
                'status'      => $isHealthy ? 'healthy' : 'diseased',
                'icon'        => 'leaf_scan',
                'extra'       => [
                    'scan_id'         => $scan->id,
                    'predicted_class' => $scan->predicted_class,
                    'confidence'      => $scan->confidence,
                    'image_url'       => $scan->image_url,
                ],
            ]);
        }

        // 3. PPL Validations
        $pplValidations = PplValidation::whereHas('scan', fn ($q) => $q->where('farm_id', $farm->id))
            ->with(['ppl:id,name', 'scan:id,predicted_class'])
            ->get();

        foreach ($pplValidations as $val) {
            $date = $val->validated_at ? Carbon::parse($val->validated_at) : Carbon::parse($val->created_at);
            $pplName = $val->ppl?->name ?? 'Petugas PPL';
            $disease = $val->scan?->predicted_class ?? 'Penyakit Tanaman';

            $statusText = match ($val->status) {
                'validated'     => 'Divalidasi Lapangan',
                'rejected'      => 'Tidak Terkonfirmasi',
                'needs_revisit' => 'Perlu Pemeriksaan Ulang',
                default         => 'Menunggu Tinjauan Petugas',
            };

            $timelineEvents->push([
                'id'          => 'ppl_' . $val->id,
                'category'    => 'ppl_visit',
                'title'       => "Verifikasi PPL: {$statusText}",
                'description' => "Petugas {$pplName} memeriksa kasus {$disease}." . ($val->notes ? " Catatan: {$val->notes}" : ''),
                'occurred_at' => $date->toIso8601String(),
                'date_human'  => $date->translatedFormat('d M Y, H:i'),
                'status'      => $val->status,
                'icon'        => 'verified',
                'extra'       => [
                    'validation_id' => $val->id,
                    'ppl_name'      => $pplName,
                    'status'        => $val->status,
                ],
            ]);
        }

        // 4. Harvests
        $harvests = Harvest::whereHas('cropSeason', fn ($q) => $q->where('farm_id', $farm->id))
            ->get();

        foreach ($harvests as $h) {
            $date = $h->harvest_date ? Carbon::parse($h->harvest_date) : now();
            $timelineEvents->push([
                'id'          => 'harvest_' . $h->id,
                'category'    => 'harvest',
                'title'       => 'Pencatatan Panen Raya',
                'description' => "Hasil panen {$h->quantity} {$h->unit} (Grade: {$h->quality_grade}, Kadar Air: {$h->moisture_percent}%).",
                'occurred_at' => $date->toIso8601String(),
                'date_human'  => $date->translatedFormat('d M Y'),
                'status'      => 'success',
                'icon'        => 'harvest',
                'extra'       => [
                    'quantity'         => $h->quantity,
                    'unit'             => $h->unit,
                    'grade'            => $h->quality_grade,
                    'moisture_percent' => $h->moisture_percent,
                ],
            ]);
        }

        // 5. Irrigation Schedules
        $irrigations = IrrigationSchedule::where('farm_id', $farm->id)
            ->where('schedule_date', '>=', $seasonStartDate)
            ->get();

        foreach ($irrigations as $irr) {
            $date = Carbon::parse($irr->schedule_date);
            $timelineEvents->push([
                'id'          => 'irrigation_' . $irr->id,
                'category'    => 'irrigation',
                'title'       => 'Jadwal Giliran Air Irigasi',
                'description' => "Pintu air blok {$irr->irrigation_block} dibuka pukul {$irr->start_time} - {$irr->end_time}.",
                'occurred_at' => $date->toIso8601String(),
                'date_human'  => $date->translatedFormat('d M Y'),
                'status'      => $irr->status,
                'icon'        => 'irrigation',
                'extra'       => [
                    'block' => $irr->irrigation_block,
                    'time'  => "{$irr->start_time} - {$irr->end_time}",
                ],
            ]);
        }

        // Sort descending by date
        $sorted = $timelineEvents->sortByDesc('occurred_at')->values()->all();

        return ApiResponse::success('Timeline terpadu lahan berhasil diambil.', [
            'farm_id'   => $farm->id,
            'farm_name' => $farm->name,
            'season'    => $activeSeason ? [
                'id'                      => $activeSeason->id,
                'variety_name'            => $activeSeason->variety?->name ?? 'Inpari 32',
                'planting_date'           => $activeSeason->planting_date,
                'estimated_harvest_date'  => $activeSeason->estimated_harvest_date,
                'status'                  => $activeSeason->status,
            ] : null,
            'events_count' => count($sorted),
            'timeline'     => $sorted,
        ]);
    }
}
