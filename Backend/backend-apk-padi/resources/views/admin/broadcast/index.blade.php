@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/broadcast.css') }}">

<div class="broadcast-page">
    {{-- Breadcrumb --}}
    <nav class="broadcast-breadcrumb" aria-label="Breadcrumb">
        <span>Admin</span>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="broadcast-breadcrumb-current">Siaran Informasi Broadcast</span>
    </nav>

    {{-- Page Header --}}
    <div class="broadcast-header">
        <div class="broadcast-header-content">
            <h1 class="broadcast-title">Manajemen Siaran Informasi Broadcast</h1>
            <p class="broadcast-description">Kirimkan pengumuman, peringatan cuaca/hama, atau informasi irigasi langsung ke notifikasi akun petani & penyuluh.</p>
        </div>
    </div>

    {{-- Status Alerts --}}
    @if(session('status'))
        <div class="broadcast-alert broadcast-alert-success" id="alert-status">
            <span>{{ session('status') }}</span>
            <button type="button" style="background:transparent; border:none; cursor:pointer;" onclick="document.getElementById('alert-status').remove()">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Stat KPI Cards --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Total Broadcast</p>
                <h3 class="stat-number">{{ number_format($stats['total'], 0, ',', '.') }}</h3>
                <p class="stat-description">Terdaftar di database</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Published / Terkirim</p>
                <h3 class="stat-number" style="color:#1b5e20;">{{ number_format($stats['published'], 0, ',', '.') }}</h3>
                <p class="stat-description">Aktif diterima pengguna</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Draft</p>
                <h3 class="stat-number">{{ number_format($stats['draft'], 0, ',', '.') }}</h3>
                <p class="stat-description">Belum dipublikasikan</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Kadaluarsa</p>
                <h3 class="stat-number" style="color:#dc2626;">{{ number_format($stats['expired'], 0, ',', '.') }}</h3>
                <p class="stat-description">Masa berlaku habis</p>
            </div>
        </div>
    </div>

    {{-- Form Buat Broadcast Card --}}
    <section class="data-card" style="border: 2px solid #a7f3d0; background: #ffffff; margin-bottom: 32px;">
        <div class="data-header" style="background: #f0fdf4; border-bottom: 1px solid #c8e6c9;">
            <div>
                <h2 style="color: #1b5e20; font-size: 18px;">Buat Siaran Informasi Baru</h2>
                <p style="color: #166534;">Input pesan pengumuman atau peringatan untuk dikirimkan secara langsung ke aplikasi mobile pengguna</p>
            </div>
        </div>

        <div style="padding: 24px;">
            <form method="POST" action="{{ route('admin.broadcast.store') }}">
                @csrf

                <div style="display:grid; grid-template-columns: 2fr 1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;" for="title">Judul Siaran <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="title" id="title" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:13px; box-sizing:border-box;" placeholder="Contoh: Peringatan Dini Hama Wereng Karawang" value="{{ old('title') }}" required>
                    </div>

                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;" for="type">Tipe Pesan <span style="color:#dc2626;">*</span></label>
                        <select name="type" id="type" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:13px; background:#fff;" required>
                            <option value="info">Informasi Umum</option>
                            <option value="warning">Peringatan / Warning</option>
                            <option value="announcement">Pengumuman Resmi</option>
                            <option value="system">Pemberitahuan Sistem</option>
                        </select>
                    </div>

                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;" for="target_role">Sasaran Penerima <span style="color:#dc2626;">*</span></label>
                        <select name="target_role" id="target_role" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:13px; background:#fff;" required>
                            <option value="all">Semua Pengguna (Petani & Partner)</option>
                            <option value="farmer">Khusus Petani</option>
                            <option value="partner">Khusus Penyuluh / Mitra</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;" for="message">Isi Pesan Siaran <span style="color:#dc2626;">*</span></label>
                    <textarea name="message" id="message" rows="3" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:13px; box-sizing:border-box;" placeholder="Tuliskan petunjuk teknis atau instruksi siaran lengkap..." required>{{ old('message') }}</textarea>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr auto; gap:16px; align-items:end;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;" for="status">Status Publikasi <span style="color:#dc2626;">*</span></label>
                        <select name="status" id="status" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:13px; background:#fff;" required>
                            <option value="published">Published (Langsung Kirim Notifikasi)</option>
                            <option value="draft">Draft (Simpan Dulu)</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>

                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;" for="expires_at">Batas Kedaluwarsa (Opsional)</label>
                        <input type="datetime-local" name="expires_at" id="expires_at" style="width:100%; padding:9px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:13px; background:#fff; box-sizing:border-box;">
                    </div>

                    <button type="submit" class="btn-broadcast-action btn-broadcast-primary" style="padding:11px 24px; border:none; cursor:pointer;">
                        Kirim Siaran Informasi
                    </button>
                </div>
            </form>
        </div>
    </section>

    {{-- Daftar Broadcast Card --}}
    <section class="data-card">
        <div class="data-header">
            <div>
                <h2>Daftar Riwayat Siaran Informasi Broadcast</h2>
                <p>Menampilkan {{ $broadcasts->firstItem() ?? 0 }} - {{ $broadcasts->lastItem() ?? 0 }} dari {{ $broadcasts->total() }} broadcast terdaftar</p>
            </div>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="filter-wrapper">
            <form method="GET" action="{{ route('admin.broadcast.index') }}" class="filter-form">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari judul siaran atau pesan..." style="padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:13px; outline:none; width:240px;">

                <select name="type" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Tipe Pesan</option>
                    <option value="info" @selected(($filters['type'] ?? '') === 'info')>Informasi Umum</option>
                    <option value="warning" @selected(($filters['type'] ?? '') === 'warning')>Peringatan</option>
                    <option value="announcement" @selected(($filters['type'] ?? '') === 'announcement')>Pengumuman</option>
                    <option value="system" @selected(($filters['type'] ?? '') === 'system')>Sistem</option>
                </select>

                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="published" @selected(($filters['status'] ?? '') === 'published')>Published</option>
                    <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Draft</option>
                    <option value="expired" @selected(($filters['status'] ?? '') === 'expired')>Expired</option>
                </select>

                <button type="submit" class="btn-filter-submit">Filter</button>
                @if(!empty($filters['search']) || !empty($filters['type']) || !empty($filters['status']))
                    <a href="{{ route('admin.broadcast.index') }}" class="btn-broadcast-action" style="padding:9px 14px;">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Judul & Isi Pesan</th>
                        <th>Tipe</th>
                        <th>Target Penerima</th>
                        <th>Dipublikasi Pada</th>
                        <th>Status</th>
                        <th style="text-align:right;">Aksi Management</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($broadcasts as $b)
                        @php
                            $targetLabel = match($b->target_role) {
                                'farmer' => 'Khusus Petani',
                                'partner' => 'Khusus Penyuluh',
                                default => 'Semua Pengguna',
                            };
                        @endphp
                        <tr>
                            <td style="max-width: 320px;">
                                <strong style="font-size:15px; color:#0f172a; display:block;">{{ $b->title }}</strong>
                                <p style="font-size:12px; color:#334155; margin:4px 0; line-height:1.4;">{{ Str::limit($b->message, 120) }}</p>
                                <span style="font-size:11px; color:#64748b;">Dibuat oleh: {{ $b->admin?->name ?? 'Admin P.A.D.I.' }}</span>
                            </td>
                            <td>
                                <span class="type-badge type-{{ $b->type }}">{{ strtoupper($b->type) }}</span>
                            </td>
                            <td>
                                <span style="font-size:12px; font-weight:700; color:#1b5e20;">{{ $targetLabel }}</span>
                            </td>
                            <td>
                                <span style="font-size:13px; color:#334155;">{{ $b->published_at ? $b->published_at->format('d M Y H:i') : '-' }}</span>
                            </td>
                            <td>
                                <span class="type-badge {{ $b->status === 'published' ? 'type-info' : ($b->status === 'draft' ? 'type-system' : 'type-warning') }}">
                                    {{ strtoupper($b->status) }}
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <div style="display:flex; justify-content:flex-end; gap:6px; align-items:center;">
                                    <form method="POST" action="{{ route('admin.broadcast.update', $b) }}" style="display:inline-flex; gap:4px;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="title" value="{{ $b->title }}">
                                        <input type="hidden" name="message" value="{{ $b->message }}">
                                        <input type="hidden" name="type" value="{{ $b->type }}">
                                        <input type="hidden" name="target_role" value="{{ $b->target_role }}">
                                        <select name="status" style="padding:6px 10px; border:1px solid #cbd5e1; border-radius:8px; font-size:12px; background:#fff;">
                                            <option value="published" @selected($b->status === 'published')>Publish</option>
                                            <option value="draft" @selected($b->status === 'draft')>Draft</option>
                                            <option value="expired" @selected($b->status === 'expired')>Expired</option>
                                        </select>
                                        <button type="submit" class="btn-broadcast-action" style="padding:6px 12px; font-size:12px; background:#1b5e20; color:#fff; border-color:#1b5e20; cursor:pointer;">Update</button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.broadcast.destroy', $b) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus broadcast {{ $b->title }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-broadcast-action" style="background:#fef2f2; color:#dc2626; border-color:#fca5a5; padding:6px 12px; font-size:12px; cursor:pointer;">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:48px; text-align:center; color:#64748b;">Belum ada siaran broadcast terdaftar di database.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($broadcasts->hasPages())
            <div class="pagination-wrapper">
                {{ $broadcasts->withQueryString()->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
