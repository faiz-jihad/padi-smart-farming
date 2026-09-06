@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/events.css') }}">

<div class="events-page">
    {{-- Breadcrumb --}}
    <nav class="events-breadcrumb" aria-label="Breadcrumb">
        <span>Admin</span>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="events-breadcrumb-current">Agenda &amp; Acara Pertanian</span>
    </nav>

    {{-- Page Header --}}
    <div class="events-header">
        <div class="events-header-content">
            <h1 class="events-title">Manajemen Agenda &amp; Acara Tani</h1>
            <p class="events-description">Kelola jadwal pelatihan petani, sekolah lapang, bazar gabah kemitraan, dan musyawarah gilir air yang terhubung ke aplikasi mobile.</p>
        </div>

        <a href="{{ route('admin.events.create') }}" class="btn-event btn-event-primary">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            <span>Buat Acara Baru</span>
        </a>
    </div>

    {{-- Status Alerts --}}
    @if(session('status'))
        <div style="background:#f0fdf4; border:1px solid #bbf7d0; color:#15803d; padding:12px 18px; border-radius:10px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between;" id="alert-status">
            <div style="display:flex; align-items:center; gap:10px; font-weight:600; font-size:13.5px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
            <button type="button" style="background:transparent; border:none; cursor:pointer; color:#15803d;" onclick="document.getElementById('alert-status').remove()">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Stat KPI Cards (Green and White) --}}
    <div class="events-stat-grid">
        <div class="events-stat-card">
            <div>
                <p class="events-stat-label">Total Agenda Acara</p>
                <h3 class="events-stat-number">{{ number_format($stats['total'], 0, ',', '.') }}</h3>
                <p class="events-stat-desc">Semua kegiatan terdaftar</p>
            </div>
            <div class="events-stat-icon">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
            </div>
        </div>

        <div class="events-stat-card">
            <div>
                <p class="events-stat-label">Pengajuan Petani</p>
                <h3 class="events-stat-number" style="{{ $stats['pending_proposals'] > 0 ? 'color:#d97706;' : '' }}">{{ number_format($stats['pending_proposals'], 0, ',', '.') }}</h3>
                <p class="events-stat-desc">{{ $stats['pending_proposals'] > 0 ? 'Menunggu persetujuan admin' : 'Semua pengajuan ditinjau' }}</p>
            </div>
            <div class="events-stat-icon" style="{{ $stats['pending_proposals'] > 0 ? 'background:#fef3c7; color:#d97706;' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                </svg>
            </div>
        </div>

        <div class="events-stat-card">
            <div>
                <p class="events-stat-label">Acara Mendatang</p>
                <h3 class="events-stat-number">{{ number_format($stats['upcoming'], 0, ',', '.') }}</h3>
                <p class="events-stat-desc">Siap dihadiri petani</p>
            </div>
            <div class="events-stat-icon">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <div class="events-stat-card">
            <div>
                <p class="events-stat-label">Total Petani Terdaftar</p>
                <h3 class="events-stat-number">{{ number_format($stats['total_registrations'], 0, ',', '.') }}</h3>
                <p class="events-stat-desc">Tiket aktif peserta</p>
            </div>
            <div class="events-stat-icon">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Main Panel --}}
    <div class="events-panel">
        {{-- Search & Filter Toolbar --}}
        <form method="GET" action="{{ route('admin.events.index') }}" class="events-filter-bar">
            <div style="flex: 1; min-width: 200px; position: relative;">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama acara, lokasi, penyelenggara..." class="events-input" style="width: 100%;">
            </div>

            <div style="min-width: 160px;">
                <select name="approval_status" class="events-input" style="width: 100%;">
                    <option value="all">Semua Status Persetujuan</option>
                    <option value="approved" {{ request('approval_status') == 'approved' ? 'selected' : '' }}>Disetujui (Publik)</option>
                    <option value="pending" {{ request('approval_status') == 'pending' ? 'selected' : '' }}>Pending Review Petani</option>
                    <option value="rejected" {{ request('approval_status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <div style="min-width: 150px;">
                <select name="category" class="events-input" style="width: 100%;">
                    <option value="all">Semua Kategori</option>
                    <option value="workshop" {{ request('category') == 'workshop' ? 'selected' : '' }}>Pelatihan &amp; Workshop</option>
                    <option value="field_day" {{ request('category') == 'field_day' ? 'selected' : '' }}>Sekolah Lapang</option>
                    <option value="bazaar" {{ request('category') == 'bazaar' ? 'selected' : '' }}>Bazar &amp; Pasar Tani</option>
                    <option value="irrigation" {{ request('category') == 'irrigation' ? 'selected' : '' }}>Jadwal Gilir Air</option>
                    <option value="webinar" {{ request('category') == 'webinar' ? 'selected' : '' }}>Webinar Online</option>
                </select>
            </div>

            <div style="min-width: 140px;">
                <select name="status" class="events-input" style="width: 100%;">
                    <option value="all">Semua Siklus</option>
                    <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Mendatang</option>
                    <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Berlangsung</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn-event btn-event-primary" style="padding: 9px 16px;">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <span>Cari</span>
                </button>
                <a href="{{ route('admin.events.index') }}" class="btn-event" style="padding: 9px 14px;">
                    Reset
                </a>
            </div>
        </form>

        {{-- Table --}}
        <div class="events-table-wrap">
            <table class="events-table">
                <thead>
                    <tr>
                        <th style="width: 32%;">Agenda Acara</th>
                        <th style="width: 22%;">Waktu &amp; Tempat</th>
                        <th style="width: 20%;">Penyelenggara / Kontak</th>
                        <th style="width: 16%;">Kuota Pendaftaran</th>
                        <th style="width: 10%; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                        @php
                            $categoryLabel = match($event->category) {
                                'field_day' => 'Sekolah Lapang',
                                'bazaar' => 'Bazar Tani',
                                'irrigation' => 'Gilir Air',
                                'webinar' => 'Webinar',
                                default => 'Workshop',
                            };
                            $fillPercent = $event->quota > 0 ? min(100, round(($event->registered_count / $event->quota) * 100)) : 0;
                        @endphp
                        <tr>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 5px;">
                                    <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                        <span class="badge-cat">
                                            {{ $categoryLabel }}
                                        </span>
                                        <span class="badge-st {{ $event->status == 'upcoming' ? 'upcoming' : '' }}">
                                            {{ ucfirst($event->status) }}
                                        </span>
                                        @if($event->approval_status == 'pending')
                                            <span style="background: #fef3c7; color: #b45309; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; border: 1px solid #fde68a;">
                                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <circle cx="12" cy="12" r="10"/>
                                                    <polyline points="12 6 12 12 16 14"/>
                                                </svg>
                                                Pending Review
                                            </span>
                                        @elseif($event->approval_status == 'rejected')
                                            <span style="background: #fee2e2; color: #b91c1c; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 6px; border: 1px solid #fca5a5;">
                                                Ditolak
                                            </span>
                                        @elseif($event->source == 'farmer_submission')
                                            <span style="background: #dcfce7; color: #15803d; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 6px; border: 1px solid #86efac;">
                                                Pengajuan Petani (ACC)
                                            </span>
                                        @endif
                                    </div>
                                    <a href="{{ route('admin.events.show', $event) }}" style="font-weight: 700; color: #0f172a; text-decoration: none; font-size: 14px; line-height: 1.35;">
                                        {{ $event->title }}
                                    </a>
                                    @if($event->source == 'farmer_submission' && $event->creator)
                                        <div style="font-size: 11.5px; color: #0369a1; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                            <span>Diajukan oleh: <strong>{{ $event->creator->name }}</strong></span>
                                        </div>
                                    @endif
                                    @if($event->speaker)
                                        <div style="font-size: 12px; color: #15803d; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z" />
                                                <path d="M19 10v2a7 7 0 0 1-14 0v-2" />
                                                <line x1="12" y1="19" x2="12" y2="23" />
                                                <line x1="8" y1="23" x2="16" y2="23" />
                                            </svg>
                                            <span>{{ $event->speaker }}</span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 13px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 5px;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:#15803d;">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                        <line x1="16" y1="2" x2="16" y2="6" />
                                        <line x1="8" y1="2" x2="8" y2="6" />
                                        <line x1="3" y1="10" x2="21" y2="10" />
                                    </svg>
                                    <span>{{ $event->event_date ? $event->event_date->format('d M Y') : '-' }}</span>
                                </div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 2px; font-weight: 500;">
                                    {{ $event->event_time }}
                                </div>
                                <div style="font-size: 12px; color: #475569; margin-top: 2px; display: flex; align-items: center; gap: 4px;">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:#64748b;">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                        <circle cx="12" cy="10" r="3" />
                                    </svg>
                                    <span>{{ $event->location_name }}</span>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 13px; font-weight: 600; color: #1e293b;">
                                    {{ $event->organizer }}
                                </div>
                                @if($event->contact_person)
                                    <div style="font-size: 12px; color: #15803d; font-weight: 600; margin-top: 2px; display: flex; align-items: center; gap: 4px;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                                        </svg>
                                        <span>{{ $event->contact_person }}</span>
                                    </div>
                                @endif
                                <div style="font-size: 11.5px; color: #94a3b8; margin-top: 2px;">
                                    Biaya: <strong>{{ $event->price_type == 'free' ? 'Gratis' : 'Berbayar' }}</strong>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; justify-content: space-between; font-size: 12.5px; font-weight: 700;">
                                    <span style="color: #0f172a;">{{ $event->registered_count }} Petani</span>
                                    <span style="color: #64748b;">/ {{ $event->quota }}</span>
                                </div>
                                <div class="quota-progress">
                                    <div class="quota-progress-bar" style="width: {{ $fillPercent }}%;"></div>
                                </div>
                                <div style="font-size: 11px; color: #64748b; font-weight: 500; margin-top: 3px;">
                                    {{ $fillPercent }}% kuota terisi
                                </div>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; justify-content: flex-end; gap: 5px; align-items: center;">
                                    @if($event->approval_status == 'pending')
                                        <form method="POST" action="{{ route('admin.events.approve', $event) }}" onsubmit="return confirm('Setujui dan publikasikan agenda pengajuan petani ini?')" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn-event" style="padding: 6px 10px; font-size: 12px; background: #15803d; color: white; border-color: #15803d; font-weight: 700;" title="Setujui &amp; ACC Acara">
                                                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" width="14" height="14">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                </svg>
                                                <span>ACC</span>
                                            </button>
                                        </form>
                                        <button type="button" class="btn-event btn-event-danger" style="padding: 6px 10px; font-size: 12px;" onclick="openRejectModal('{{ $event->id }}', '{{ addslashes($event->title) }}')" title="Tolak Pengajuan">
                                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="14" height="14">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            <span>Tolak</span>
                                        </button>
                                    @endif

                                    <a href="{{ route('admin.events.show', $event) }}" class="btn-event" style="padding: 6px 10px; font-size: 12px;" title="Lihat Detail Acara">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.events.edit', $event) }}" class="btn-event" style="padding: 6px 10px; font-size: 12px;" title="Edit Acara">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.events.destroy', $event) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus agenda acara ini?')" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-event btn-event-danger" style="padding: 6px 10px; font-size: 12px;" title="Hapus Acara">
                                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <polyline points="3 6 5 6 21 6" />
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 44px 20px; color: #64748b;">
                                <div style="display:inline-flex; padding:12px; background:#f0fdf4; border-radius:50%; margin-bottom:12px;">
                                    <svg width="32" height="32" fill="none" stroke="#15803d" stroke-width="2" viewBox="0 0 24 24">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                        <line x1="16" y1="2" x2="16" y2="6" />
                                        <line x1="8" y1="2" x2="8" y2="6" />
                                        <line x1="3" y1="10" x2="21" y2="10" />
                                    </svg>
                                </div>
                                <p style="font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Belum Ada Agenda Acara Pertanian</p>
                                <p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">
                                    Jadwal pelatihan atau musyawarah yang dibuat akan otomatis muncul di aplikasi petani.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($events->hasPages())
            <div style="padding: 14px 18px; border-top: 1px solid var(--event-border); display: flex; justify-content: center;">
                {{ $events->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Reject Event Modal --}}
<div id="reject-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; width: 100%; max-width: 480px; padding: 24px; margin: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="background: #fee2e2; border-radius: 50%; padding: 6px; color: #dc2626; display: flex;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: #0f172a;">Tolak Pengajuan Agenda</h3>
            </div>
            <button type="button" onclick="closeRejectModal()" style="background: transparent; border: none; cursor: pointer; color: #64748b;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>

        <form id="reject-form" method="POST" action="">
            @csrf
            <p style="font-size: 13.5px; color: #475569; margin: 0 0 12px 0;">
                Anda akan menolak pengajuan agenda: <strong id="reject-event-title" style="color: #0f172a;"></strong>.
            </p>

            <div style="margin-bottom: 16px;">
                <label for="rejection_reason" style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; text-transform: uppercase;">
                    Alasan Penolakan <span style="color: #dc2626;">*</span>
                </label>
                <textarea name="rejection_reason" id="rejection_reason" rows="3" required placeholder="Jelaskan alasan penolakan agar petani dapat memperbaiki pengajuannya..." style="width: 100%; border-radius: 8px; border: 1px solid #cbd5e1; padding: 10px 12px; font-size: 13px; font-family: inherit; resize: vertical;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" onclick="closeRejectModal()" class="btn-event" style="padding: 8px 16px;">
                    Batal
                </button>
                <button type="submit" class="btn-event btn-event-danger" style="padding: 8px 18px; font-weight: 700;">
                    Tolak Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(eventId, eventTitle) {
    const modal = document.getElementById('reject-modal');
    const form = document.getElementById('reject-form');
    const titleElem = document.getElementById('reject-event-title');
    
    form.action = "{{ url('admin/events') }}/" + eventId + "/reject";
    titleElem.textContent = eventTitle;
    document.getElementById('rejection_reason').value = '';
    
    modal.style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('reject-modal').style.display = 'none';
}
</script>
@endsection
