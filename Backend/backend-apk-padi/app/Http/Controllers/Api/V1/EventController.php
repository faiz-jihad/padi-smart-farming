<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\AgricultureEvent;
use App\Models\EventRegistration;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    /**
     * Display a listing of approved upcoming & active agriculture events.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $events = AgricultureEvent::query()
            ->where('approval_status', 'approved')
            ->when($user, function ($query) use ($user): void {
                $query->withExists(['registrations as is_user_registered' => function ($q) use ($user): void {
                    $q->where('user_id', $user->id);
                }]);
            })
            ->when($request->query('category') && $request->query('category') !== 'all', function ($query) use ($request): void {
                $query->where('category', $request->query('category'));
            })
            ->orderBy('event_date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar acara berhasil diambil.',
            'data' => EventResource::collection($events),
        ]);
    }

    /**
     * Display a listing of the authenticated farmer's own submissions (pending/approved/rejected).
     */
    public function mySubmissions(Request $request): JsonResponse
    {
        $user = $request->user();

        $events = AgricultureEvent::query()
            ->where('created_by', $user->id)
            ->when($user, function ($query) use ($user): void {
                $query->withExists(['registrations as is_user_registered' => function ($q) use ($user): void {
                    $q->where('user_id', $user->id);
                }]);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar pengajuan agenda Anda berhasil diambil.',
            'data' => EventResource::collection($events),
        ]);
    }

    /**
     * Store a newly created event proposal or official event.
     */
    public function store(Request $request, AdminNotificationService $notificationService): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|in:workshop,field_day,bazaar,irrigation,webinar',
            'event_date' => 'required|date',
            'event_time' => 'nullable|string|max:50',
            'location_name' => 'required|string|max:255',
            'location_address' => 'nullable|string|max:500',
            'is_online' => 'nullable|boolean',
            'organizer' => 'required|string|max:255',
            'speaker' => 'nullable|string|max:255',
            'quota' => 'nullable|integer|min:1',
            'price_type' => 'nullable|string|in:free,paid',
            'asset_image' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
        ]);

        $user = $request->user();
        $validated['created_by'] = $user->id;
        $validated['status'] = 'upcoming';

        $isOfficialCreator = $user->role === 'admin'
            || $user->role === 'extension_officer'
            || $user->role === 'ppl'
            || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'extension_officer']));

        if ($isOfficialCreator) {
            $validated['source'] = 'official';
            $validated['approval_status'] = 'approved';
            $validated['approved_by'] = null;
            $validated['approved_at'] = null;
            $validated['rejection_reason'] = null;

            $event = AgricultureEvent::create($validated);

            $notificationService->notifyAdmins(
                'Acara Pertanian Baru',
                "Kegiatan \"{$event->title}\" ({$event->category}) pada {$event->event_date} telah ditambahkan ke agenda.",
                'system'
            );
            $notificationService->notifyFarmers(
                'Undangan Kegiatan & Pelatihan Pertanian',
                "Tersedia kegiatan baru: \"{$event->title}\" pada tanggal {$event->event_date} di {$event->location_name}.",
                'crop_alert'
            );
            $notificationService->notifyExtensionOfficers(
                'Agenda Penyuluhan Pertanian Baru',
                "Kegiatan \"{$event->title}\" dijadwalkan pada {$event->event_date}.",
                'ppl_validation'
            );

            $message = 'Acara berhasil dibuat.';
        } else {
            $validated['source'] = 'farmer_submission';
            $validated['approval_status'] = 'pending';
            $validated['approved_by'] = null;
            $validated['approved_at'] = null;
            $validated['rejection_reason'] = null;

            $event = AgricultureEvent::create($validated);

            $notificationService->notifyAdmins(
                'Pengajuan Agenda Baru dari Petani',
                "Petani {$user->name} mengajukan agenda \"{$event->title}\" untuk direview.",
                'system',
                ['event_id' => $event->id, 'action' => 'event_submission_review']
            );

            $message = 'Pengajuan agenda berhasil dikirim dan menunggu persetujuan admin.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => new EventResource($event),
        ], 201);
    }

    /**
     * Display the specified event with visibility authorization.
     */
    public function show(Request $request, AgricultureEvent $event): JsonResponse
    {
        $user = $request->user();

        // Check visibility: approved events are public; pending/rejected submissions only visible to creator and admins
        if ($event->approval_status !== 'approved') {
            $canView = $user && (
                $user->id === $event->created_by ||
                $user->role === 'admin' ||
                (method_exists($user, 'hasRole') && $user->hasRole('admin'))
            );

            if (! $canView) {
                abort(404, 'Agenda acara tidak ditemukan.');
            }
        }

        // Load the registration status for the authenticated user
        if ($user) {
            $event->is_user_registered = $event->registrations()
                ->where('user_id', $user->id)
                ->exists();
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail acara berhasil diambil.',
            'data' => new EventResource($event),
        ]);
    }

    /**
     * Register the authenticated user for an event.
     */
    public function register(Request $request, AgricultureEvent $event): JsonResponse
    {
        $user = $request->user();

        // Enforce registration only for approved events
        if ($event->approval_status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Pendaftaran hanya dapat dilakukan untuk acara yang telah disetujui.',
            ], 422);
        }

        // Event creator cannot register to their own submitted event
        if ($event->created_by && (int) $event->created_by === (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat mendaftar sebagai peserta pada acara yang Anda ajukan sendiri.',
            ], 422);
        }

        // Check if already registered
        $existing = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'already_registered' => true,
                'message' => 'Anda sudah terdaftar pada acara ini. Tiket Anda tetap aktif.',
                'data' => new EventResource($event),
                'ticket' => [
                    'ticket_code' => $existing->ticket_code,
                    'ticket_status' => $existing->ticket_status,
                    'registered_at' => $existing->registered_at?->toIso8601String(),
                ],
            ]);
        }

        // Check quota
        if ($event->quota > 0 && $event->registered_count >= $event->quota) {
            return response()->json([
                'success' => false,
                'message' => 'Mohon maaf, kuota pendaftaran untuk acara ini sudah penuh.',
            ], 422);
        }

        $registration = DB::transaction(function () use ($event, $user, $request): EventRegistration {
            $reg = EventRegistration::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'notes' => $request->input('notes'),
                'registered_at' => now(),
            ]);

            $event->increment('registered_count');

            return $reg;
        });

        $event->refresh();

        return response()->json([
            'success' => true,
            'already_registered' => false,
            'message' => 'Pendaftaran acara berhasil! Tiket Anda telah diterbitkan.',
            'data' => new EventResource($event),
            'ticket' => [
                'ticket_code' => $registration->ticket_code,
                'ticket_status' => $registration->ticket_status,
                'registered_at' => $registration->registered_at?->toIso8601String(),
            ],
        ]);
    }
}
