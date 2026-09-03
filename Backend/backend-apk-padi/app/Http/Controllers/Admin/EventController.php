<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgricultureEvent;
use App\Services\Admin\AdminAuditLogger;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(
        private AdminAuditLogger $auditLogger,
        private AdminNotificationService $notificationService
    ) {}

    /**
     * Display a listing of agriculture events.
     */
    public function index(Request $request): View
    {
        $query = AgricultureEvent::query()
            ->with(['creator', 'registrations.user'])
            ->withCount('registrations');

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('location_name', 'like', "%{$search}%")
                    ->orWhere('organizer', 'like', "%{$search}%")
                    ->orWhere('speaker', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $events = $query->orderBy('event_date', 'desc')->paginate(12)->withQueryString();

        $stats = [
            'total' => AgricultureEvent::count(),
            'upcoming' => AgricultureEvent::where('event_date', '>=', now()->toDateString())->count(),
            'total_registrations' => AgricultureEvent::sum('registered_count'),
            'workshops' => AgricultureEvent::where('category', 'workshop')->count(),
        ];

        return view('admin.events.index', compact('events', 'stats'));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create(): View
    {
        return view('admin.events.create');
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|in:workshop,field_day,bazaar,irrigation,webinar',
            'event_date' => 'required|date',
            'event_time' => 'required|string|max:50',
            'location_name' => 'required|string|max:255',
            'location_address' => 'nullable|string|max:500',
            'is_online' => 'nullable|boolean',
            'organizer' => 'required|string|max:255',
            'speaker' => 'nullable|string|max:255',
            'quota' => 'required|integer|min:1',
            'price_type' => 'required|string|in:free,paid',
            'asset_image' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'status' => 'required|string|in:upcoming,ongoing,completed,cancelled',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['is_online'] = $request->has('is_online');
        $validated['registered_count'] = 0;

        if (empty($validated['asset_image'])) {
            $validated['asset_image'] = match ($validated['category']) {
                'field_day' => 'assets/images/onboarding_2.jpeg',
                'bazaar' => 'assets/images/onboarding_3.jpeg',
                default => 'assets/images/onboarding_1.jpeg',
            };
        }

        $event = AgricultureEvent::create($validated);

        // Audit Log & Notifications
        $this->auditLogger->write('admin_event_created', $event, null, $event->toArray(), $request);
        $this->notificationService->notifyAdmins(
            'Acara Pertanian Baru',
            "Kegiatan \"{$event->title}\" ({$event->category}) pada {$event->event_date} telah ditambahkan ke agenda.",
            'system'
        );
        $this->notificationService->notifyFarmers(
            'Undangan Kegiatan & Pelatihan Pertanian',
            "Tersedia kegiatan baru: \"{$event->title}\" pada tanggal {$event->event_date} di {$event->location_name}.",
            'crop_alert'
        );
        $this->notificationService->notifyExtensionOfficers(
            'Agenda Penyuluhan Pertanian Baru',
            "Kegiatan \"{$event->title}\" dijadwalkan pada {$event->event_date}.",
            'ppl_validation'
        );

        return redirect()->route('admin.events.index')
            ->with('status', 'Acara pertanian baru berhasil ditambahkan dan notifikasi agenda telah disiarkan.');
    }

    /**
     * Display the specified event with registrations list.
     */
    public function show(AgricultureEvent $event): View
    {
        $event->load(['creator', 'registrations.user']);

        return view('admin.events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(AgricultureEvent $event): View
    {
        return view('admin.events.edit', compact('event'));
    }

    /**
     * Update the specified event in storage.
     */
    public function update(Request $request, AgricultureEvent $event): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|in:workshop,field_day,bazaar,irrigation,webinar',
            'event_date' => 'required|date',
            'event_time' => 'required|string|max:50',
            'location_name' => 'required|string|max:255',
            'location_address' => 'nullable|string|max:500',
            'is_online' => 'nullable|boolean',
            'organizer' => 'required|string|max:255',
            'speaker' => 'nullable|string|max:255',
            'quota' => 'required|integer|min:1',
            'price_type' => 'required|string|in:free,paid',
            'asset_image' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'status' => 'required|string|in:upcoming,ongoing,completed,cancelled',
        ]);

        $oldValues = $event->toArray();
        $validated['is_online'] = $request->has('is_online');

        $event->update($validated);

        // Audit Log & Notifications
        $this->auditLogger->write('admin_event_updated', $event, $oldValues, $event->toArray(), $request);
        $this->notificationService->notifyAdmins(
            'Acara Pertanian Diperbarui',
            "Jadwal/data acara \"{$event->title}\" telah diperbarui.",
            'system'
        );

        return redirect()->route('admin.events.index')
            ->with('status', 'Data acara pertanian berhasil diperbarui dan notifikasi telah dikirim.');
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy(Request $request, AgricultureEvent $event): RedirectResponse
    {
        $oldValues = $event->toArray();
        $eventTitle = $event->title;
        $eventId = $event->id;

        $event->delete();

        $this->auditLogger->write('admin_event_deleted', AgricultureEvent::class, $oldValues, null, $request, $eventId);
        $this->notificationService->notifyAdmins(
            'Acara Pertanian Dihapus',
            "Kegiatan \"{$eventTitle}\" telah dihapus dari agenda sistem.",
            'system'
        );

        return redirect()->route('admin.events.index')
            ->with('status', 'Acara berhasil dihapus dari jadwal.');
    }
}
