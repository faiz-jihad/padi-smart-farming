<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\AgricultureEvent;
use App\Models\EventRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    /**
     * Display a listing of upcoming & active agriculture events.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $events = AgricultureEvent::query()
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
     * Store a newly created event (Admin / Officer).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|in:workshop,field_day,bazaar,irrigation',
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

        $validated['created_by'] = $request->user()->id;
        $validated['status'] = 'upcoming';

        $event = AgricultureEvent::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Acara berhasil dibuat.',
            'data' => new EventResource($event),
        ], 201);
    }

    /**
     * Display the specified event.
     */
    public function show(Request $request, AgricultureEvent $event): JsonResponse
    {
        $user = $request->user();

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

        // Check if already registered
        $existing = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'Anda sudah terdaftar pada acara ini.',
                'data' => new EventResource($event),
            ]);
        }

        // Check quota
        if ($event->quota > 0 && $event->registered_count >= $event->quota) {
            return response()->json([
                'success' => false,
                'message' => 'Mohon maaf, kuota pendaftaran untuk acara ini sudah penuh.',
            ], 422);
        }

        DB::transaction(function () use ($event, $user, $request): void {
            EventRegistration::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'notes' => $request->input('notes'),
                'registered_at' => now(),
            ]);

            $event->increment('registered_count');
        });

        $event->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran acara berhasil! Tiket Anda telah aktif.',
            'data' => new EventResource($event),
        ]);
    }
}
