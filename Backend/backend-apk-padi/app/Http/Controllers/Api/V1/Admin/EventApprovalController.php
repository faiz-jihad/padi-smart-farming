<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\AgricultureEvent;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventApprovalController extends Controller
{
    /**
     * Approve a pending farmer submission event.
     */
    public function approve(Request $request, AgricultureEvent $event, AdminNotificationService $notificationService): JsonResponse
    {
        if ($event->source !== 'farmer_submission') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pengajuan agenda dari petani yang memerlukan persetujuan.',
            ], 422);
        }

        if ($event->approval_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Agenda ini sudah diproses dan tidak berstatus pending.',
            ], 422);
        }

        $event->update([
            'approval_status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        if ($event->created_by) {
            $notificationService->notifyUser(
                $event->created_by,
                'Pengajuan Agenda Disetujui',
                "Pengajuan agenda \"{$event->title}\" Anda telah disetujui oleh admin dan kini telah dipublikasikan.",
                'system',
                ['event_id' => $event->id, 'action' => 'event_approved']
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan agenda berhasil disetujui dan telah dipublikasikan.',
            'data' => new EventResource($event->fresh()),
        ]);
    }

    /**
     * Reject a pending farmer submission event with a reason.
     */
    public function reject(Request $request, AgricultureEvent $event, AdminNotificationService $notificationService): JsonResponse
    {
        if ($event->source !== 'farmer_submission') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pengajuan agenda dari petani yang dapat ditolak.',
            ], 422);
        }

        if ($event->approval_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Agenda ini sudah diproses dan tidak berstatus pending.',
            ], 422);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $event->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        if ($event->created_by) {
            $notificationService->notifyUser(
                $event->created_by,
                'Pengajuan Agenda Ditolak',
                "Pengajuan agenda \"{$event->title}\" Anda belum dapat disetujui. Alasan: {$event->rejection_reason}",
                'system',
                [
                    'event_id' => $event->id,
                    'action' => 'event_rejected',
                    'rejection_reason' => $event->rejection_reason,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan agenda telah ditolak.',
            'data' => new EventResource($event->fresh()),
        ]);
    }
}
