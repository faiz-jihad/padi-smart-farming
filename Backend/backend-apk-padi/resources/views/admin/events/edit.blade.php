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
        <span class="events-breadcrumb-current">Edit Acara #{{ $event->id }}</span>
    </nav>

    {{-- Page Header --}}
    <div class="events-header">
        <div class="events-header-content">
            <h1 class="events-title">Edit Agenda Acara: {{ $event->title }}</h1>
            <p class="events-description">Perbarui informasi jadwal, kuota, lokasi, atau status pelaksanaan acara.</p>
        </div>
        <a href="{{ route('admin.events.index') }}" class="btn-event">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="19" y1="12" x2="5" y2="12" />
                <polyline points="12 19 5 12 12 5" />
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    @if($errors->any())
        <div style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; padding:12px 18px; border-radius:10px; margin-bottom:20px;">
            <p style="font-weight:700; margin:0 0 6px 0; font-size:13.5px;">Mohon periksa kembali formulir:</p>
            <ul style="margin:0; padding-left:20px; font-size:13px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="events-panel">
        <div class="events-panel-header">
            <h2 class="events-panel-title">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                </svg>
                <span>Perbarui Data Acara</span>
            </h2>
        </div>
        <div style="padding: 24px;">
            <form method="POST" action="{{ route('admin.events.update', $event) }}">
                @csrf
                @method('PATCH')

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                    <div style="grid-column: 1 / -1;">
                        <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:6px;">Judul Acara / Pelatihan *</label>
                        <input type="text" name="title" value="{{ old('title', $event->title) }}" required class="events-input" style="width:100%; font-size:14px; font-weight:600;">
                    </div>

                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:6px;">Kategori Acara *</label>
                        <select name="category" required class="events-input" style="width:100%;">
                            <option value="workshop" {{ old('category', $event->category) == 'workshop' ? 'selected' : '' }}>Pelatihan &amp; Workshop</option>
                            <option value="field_day" {{ old('category', $event->category) == 'field_day' ? 'selected' : '' }}>Sekolah Lapang</option>
                            <option value="bazaar" {{ old('category', $event->category) == 'bazaar' ? 'selected' : '' }}>Bazar &amp; Pasar Tani</option>
                            <option value="irrigation" {{ old('category', $event->category) == 'irrigation' ? 'selected' : '' }}>Jadwal Gilir Air</option>
                            <option value="webinar" {{ old('category', $event->category) == 'webinar' ? 'selected' : '' }}>Webinar Online</option>
                        </select>
                    </div>

                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:6px;">Status Pelaksanaan *</label>
                        <select name="status" required class="events-input" style="width:100%;">
                            <option value="upcoming" {{ old('status', $event->status) == 'upcoming' ? 'selected' : '' }}>Mendatang (Upcoming)</option>
                            <option value="ongoing" {{ old('status', $event->status) == 'ongoing' ? 'selected' : '' }}>Sedang Berlangsung (Ongoing)</option>
                            <option value="completed" {{ old('status', $event->status) == 'completed' ? 'selected' : '' }}>Selesai (Completed)</option>
                            <option value="cancelled" {{ old('status', $event->status) == 'cancelled' ? 'selected' : '' }}>Dibatalkan (Cancelled)</option>
                        </select>
                    </div>

                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:6px;">Tanggal Pelaksanaan *</label>
                        <input type="date" name="event_date" value="{{ old('event_date', $event->event_date ? $event->event_date->format('Y-m-d') : '') }}" required class="events-input" style="width:100%;">
                    </div>

                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:6px;">Waktu / Jam *</label>
                        <input type="text" name="event_time" value="{{ old('event_time', $event->event_time) }}" required class="events-input" style="width:100%;">
                    </div>

                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:6px;">Nama Tempat / Lokasi *</label>
                        <input type="text" name="location_name" value="{{ old('location_name', $event->location_name) }}" required class="events-input" style="width:100%;">
                    </div>

                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:6px;">Alamat Lengkap</label>
                        <input type="text" name="location_address" value="{{ old('location_address', $event->location_address) }}" class="events-input" style="width:100%;">
                    </div>

                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:6px;">Penyelenggara / Institusi *</label>
                        <input type="text" name="organizer" value="{{ old('organizer', $event->organizer) }}" required class="events-input" style="width:100%;">
                    </div>

                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:6px;">Narasumber / Pemateri</label>
                        <input type="text" name="speaker" value="{{ old('speaker', $event->speaker) }}" class="events-input" style="width:100%;">
                    </div>

                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:6px;">Kuota Peserta (Orang) *</label>
                        <input type="number" name="quota" value="{{ old('quota', $event->quota) }}" min="1" required class="events-input" style="width:100%;">
                    </div>

                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:6px;">Biaya Keikutsertaan *</label>
                        <select name="price_type" required class="events-input" style="width:100%;">
                            <option value="free" {{ old('price_type', $event->price_type) == 'free' ? 'selected' : '' }}>Gratis (Free)</option>
                            <option value="paid" {{ old('price_type', $event->price_type) == 'paid' ? 'selected' : '' }}>Berbayar</option>
                        </select>
                    </div>

                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:6px;">Kontak WhatsApp Narahubung</label>
                        <input type="text" name="contact_person" value="{{ old('contact_person', $event->contact_person) }}" class="events-input" style="width:100%;">
                    </div>

                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:6px;">Banner Poster di Aplikasi</label>
                        <select name="asset_image" class="events-input" style="width:100%;">
                            <option value="assets/images/onboarding_1.jpeg" {{ old('asset_image', $event->asset_image) == 'assets/images/onboarding_1.jpeg' ? 'selected' : '' }}>Banner Sawah Hijau (Onboarding 1)</option>
                            <option value="assets/images/onboarding_2.jpeg" {{ old('asset_image', $event->asset_image) == 'assets/images/onboarding_2.jpeg' ? 'selected' : '' }}>Banner Petani &amp; Panen (Onboarding 2)</option>
                            <option value="assets/images/onboarding_3.jpeg" {{ old('asset_image', $event->asset_image) == 'assets/images/onboarding_3.jpeg' ? 'selected' : '' }}>Banner Pasar &amp; Digital (Onboarding 3)</option>
                            <option value="assets/images/splash_background.jpeg" {{ old('asset_image', $event->asset_image) == 'assets/images/splash_background.jpeg' ? 'selected' : '' }}>Banner Hamparan Emas</option>
                        </select>
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:6px;">Deskripsi &amp; Rincian Materi Kegiatan *</label>
                        <textarea name="description" rows="5" required class="events-input" style="width:100%; line-height:1.6;">{{ old('description', $event->description) }}</textarea>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid var(--event-border); padding-top:18px;">
                    <a href="{{ route('admin.events.index') }}" class="btn-event">Batal</a>
                    <button type="submit" class="btn-event btn-event-primary" style="padding:10px 24px;">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                            <polyline points="17 21 17 13 7 13 7 21" />
                            <polyline points="7 3 7 8 15 8" />
                        </svg>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
