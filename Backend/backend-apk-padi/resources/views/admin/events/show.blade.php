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
        <a href="{{ route('admin.events.index') }}" style="color:#64748b; text-decoration:none;">Agenda &amp; Acara</a>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="events-breadcrumb-current">Detail Acara #{{ $event->id }}</span>
    </nav>

    {{-- Page Header --}}
    <div class="events-header">
        <div class="events-header-content">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <span class="badge-cat">
                    {{ ucfirst(str_replace('_', ' ', $event->category)) }}
                </span>
                <span class="badge-st {{ $event->status == 'upcoming' ? 'upcoming' : '' }}">
                    {{ ucfirst($event->status) }}
                </span>
            </div>
            <h1 class="events-title">{{ $event->title }}</h1>
            <p class="events-description">Diselenggarakan oleh <strong>{{ $event->organizer }}</strong> • Kuota: {{ $event->registered_count }} / {{ $event->quota }} Orang</p>
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.events.edit', $event) }}" class="btn-event btn-event-primary">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                </svg>
                <span>Edit Acara</span>
            </a>
            <a href="{{ route('admin.events.index') }}" class="btn-event">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <line x1="19" y1="12" x2="5" y2="12" />
                    <polyline points="12 19 5 12 12 5" />
                </svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    {{-- Event Detail Overview Grid --}}
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 24px;">
        {{-- Left: Event Details --}}
        <div class="events-panel" style="margin-bottom: 0;">
            <div class="events-panel-header">
                <h2 class="events-panel-title">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                        <polyline points="10 9 9 9 8 9" />
                    </svg>
                    <span>Informasi &amp; Silabus Acara</span>
                </h2>
            </div>
            <div style="padding: 24px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                    <div>
                        <p style="font-size: 11.5px; color: #64748b; margin: 0 0 4px 0; font-weight: 700; text-transform: uppercase;">WAKTU PELAKSANAAN</p>
                        <p style="font-size: 14px; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 6px;">
                            <svg width="15" height="15" fill="none" stroke="#15803d" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            <span>{{ $event->event_date ? $event->event_date->format('l, d F Y') : '-' }}</span>
                        </p>
                        <p style="font-size: 13px; color: #475569; margin: 3px 0 0 0; font-weight: 500;">
                            {{ $event->event_time }}
                        </p>
                    </div>

                    <div>
                        <p style="font-size: 11.5px; color: #64748b; margin: 0 0 4px 0; font-weight: 700; text-transform: uppercase;">LOKASI / TEMPAT</p>
                        <p style="font-size: 14px; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 6px;">
                            <svg width="15" height="15" fill="none" stroke="#15803d" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            <span>{{ $event->location_name }}</span>
                        </p>
                        @if($event->location_address)
                            <p style="font-size: 12.5px; color: #64748b; margin: 3px 0 0 0;">
                                {{ $event->location_address }}
                            </p>
                        @endif
                    </div>

                    <div>
                        <p style="font-size: 11.5px; color: #64748b; margin: 0 0 4px 0; font-weight: 700; text-transform: uppercase;">NARASUMBER / PEMATERI</p>
                        <p style="font-size: 14px; font-weight: 700; color: #15803d; margin: 0; display: flex; align-items: center; gap: 6px;">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z" />
                                <path d="M19 10v2a7 7 0 0 1-14 0v-2" />
                                <line x1="12" y1="19" x2="12" y2="23" />
                                <line x1="8" y1="23" x2="16" y2="23" />
                            </svg>
                            <span>{{ $event->speaker ?: 'Tim Penyuluh Pertanian' }}</span>
                        </p>
                    </div>

                    <div>
                        <p style="font-size: 11.5px; color: #64748b; margin: 0 0 4px 0; font-weight: 700; text-transform: uppercase;">KONTAK &amp; NARAHUBUNG</p>
                        <p style="font-size: 14px; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 6px;">
                            <svg width="15" height="15" fill="none" stroke="#15803d" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                            </svg>
                            <span>{{ $event->contact_person ?: '-' }}</span>
                        </p>
                    </div>
                </div>

                <div style="border-top: 1px solid var(--event-border); padding-top: 20px;">
                    <p style="font-size: 12px; color: #64748b; margin: 0 0 8px 0; font-weight: 700; text-transform: uppercase;">RINCIAN MATERI KEGIATAN</p>
                    <div style="font-size: 13.5px; line-height: 1.65; color: #334155; white-space: pre-line; background: #fafafa; padding: 16px; border-radius: 10px; border: 1px solid var(--event-border);">{{ $event->description }}</div>
                </div>
            </div>
        </div>

        {{-- Right: Stats & Quota --}}
        <div class="events-panel" style="margin-bottom: 0;">
            <div class="events-panel-header">
                <h2 class="events-panel-title">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M18 20V10" />
                        <path d="M12 20V4" />
                        <path d="M6 20v-6" />
                    </svg>
                    <span>Kapasitas Kuota</span>
                </h2>
            </div>
            <div style="padding: 24px; text-align: center;">
                @php
                    $fillPercent = $event->quota > 0 ? min(100, round(($event->registered_count / $event->quota) * 100)) : 0;
                @endphp
                <div style="font-size: 38px; font-weight: 900; color: var(--event-primary); line-height: 1;">
                    {{ $event->registered_count }}
                </div>
                <p style="font-size: 13px; color: #64748b; margin: 4px 0 16px 0;">Petani Terdaftar dari Kuota <strong>{{ $event->quota }}</strong> Orang</p>

                <div class="quota-progress" style="height: 8px;">
                    <div class="quota-progress-bar" style="width: {{ $fillPercent }}%;"></div>
                </div>
                <p style="font-size: 13px; font-weight: 700; color: var(--event-primary); margin: 8px 0 20px 0;">{{ $fillPercent }}% Kapasitas Terisi</p>

                <div style="background: #fafafa; border-radius: 10px; padding: 14px; text-align: left; font-size: 12.5px; color: #475569; border: 1px solid var(--event-border);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span>Biaya:</span>
                        <strong>{{ $event->price_type == 'free' ? 'Gratis' : 'Berbayar' }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span>Dibuat oleh:</span>
                        <strong>{{ $event->creator?->name ?? 'Admin P.A.D.I.' }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Dibuat pada:</span>
                        <strong>{{ $event->created_at?->format('d M Y H:i') }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Registered Farmers List --}}
    <div class="events-panel">
        <div class="events-panel-header">
            <h2 class="events-panel-title">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
                <span>Daftar Petani yang Telah Mendaftar ({{ $event->registrations->count() }} Orang)</span>
            </h2>
        </div>

        <div class="events-table-wrap">
            <table class="events-table">
                <thead>
                    <tr>
                        <th style="width: 6%;">No</th>
                        <th style="width: 32%;">Nama Petani</th>
                        <th style="width: 26%;">Kontak (Email / WhatsApp)</th>
                        <th style="width: 22%;">Waktu Pendaftaran</th>
                        <th style="width: 14%; text-align: right;">Status Tiket</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($event->registrations as $index => $reg)
                        <tr>
                            <td style="font-weight: 700; color: #64748b;">{{ $index + 1 }}</td>
                            <td>
                                <div style="font-weight: 700; color: #0f172a; font-size: 13.5px;">
                                    {{ $reg->user?->name ?? 'Petani P.A.D.I.' }}
                                </div>
                                <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                    ID User: #{{ $reg->user_id }}
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 12.5px; color: #1e293b;">
                                    {{ $reg->user?->email ?? '-' }}
                                </div>
                                <div style="font-size: 12px; color: #15803d; font-weight: 600; margin-top: 2px;">
                                    {{ $reg->user?->phone ?? '-' }}
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 12.5px; color: #1e293b; font-weight: 500;">
                                    {{ $reg->registered_at ? $reg->registered_at->format('d M Y H:i') : $reg->created_at->format('d M Y H:i') }} WIB
                                </div>
                            </td>
                            <td style="text-align: right;">
                                <span class="badge-st upcoming" style="font-size: 11px;">
                                    Terverifikasi
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 36px 20px; color: #64748b;">
                                <div style="display:inline-flex; padding:10px; background:#f0fdf4; border-radius:50%; margin-bottom:10px;">
                                    <svg width="24" height="24" fill="none" stroke="#15803d" stroke-width="2" viewBox="0 0 24 24">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                        <line x1="16" y1="2" x2="16" y2="6" />
                                        <line x1="8" y1="2" x2="8" y2="6" />
                                        <line x1="3" y1="10" x2="21" y2="10" />
                                    </svg>
                                </div>
                                <p style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 3px;">Belum Ada Petani Terdaftar</p>
                                <p style="font-size: 12px; color: #64748b; margin: 0;">
                                    Pendaftaran akan otomatis tercatat ketika petani menekan tombol "Daftar Acara" di aplikasi mobile P.A.D.I.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
