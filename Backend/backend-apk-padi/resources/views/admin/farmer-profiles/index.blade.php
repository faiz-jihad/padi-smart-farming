@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/farmer-profile.css') }}">

@php
    $statusColors = [
        'draft'     => 'background:#f1f5f9; color:#475569; border:1px solid #cbd5e1;',
        'review'    => 'background:#fef3c7; color:#92400e; border:1px solid #fde68a;',
        'published' => 'background:#dcfce7; color:#166534; border:1px solid #86efac;',
        'suspended' => 'background:#fee2e2; color:#991b1b; border:1px solid #fca5a5;',
    ];
    $verifyColors = [
        'unverified' => 'background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0;',
        'verified'   => 'background:#dcfce7; color:#166534; border:1px solid #86efac;',
        'rejected'   => 'background:#fee2e2; color:#991b1b; border:1px solid #fca5a5;',
    ];
@endphp

<div class="fp-page">
    {{-- Breadcrumb --}}
    <nav class="fp-breadcrumb" aria-label="Breadcrumb">
        <span>Admin</span>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="fp-breadcrumb-current">Website Usaha Tani</span>
    </nav>

    {{-- Header with Create Action --}}
    <div class="fp-header">
        <div>
            <h1 class="fp-title">Website Usaha Tani</h1>
            <p class="fp-description">Monitor, verifikasi, buat, dan kelola website publik seluruh kelompok & usaha tani di bawah domain P.A.D.I.</p>
        </div>

        <div style="display:flex; align-items:center; gap:12px;">
            <a href="{{ route('admin.farmer-profiles.create') }}" class="admin-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Tambah Website Usaha Tani
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="fp-card" style="padding:16px 20px; margin-bottom:20px;">
        <form method="GET" action="{{ route('admin.farmer-profiles.index') }}" style="display:flex; flex-wrap:wrap; align-items:center; gap:12px;">
            <div style="flex:1; min-width:240px;">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama usaha / subdomain / petani..."
                    class="admin-input">
            </div>

            <div style="min-width:160px;">
                <select name="status" class="admin-select">
                    <option value="">Semua Status Website</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Tayang (Published)</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="review" {{ request('status') === 'review' ? 'selected' : '' }}>Menunggu Review</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Ditangguhkan</option>
                </select>
            </div>

            <div style="min-width:160px;">
                <select name="verification" class="admin-select">
                    <option value="">Semua Verifikasi</option>
                    <option value="verified" {{ request('verification') === 'verified' ? 'selected' : '' }}>Terverifikasi</option>
                    <option value="unverified" {{ request('verification') === 'unverified' ? 'selected' : '' }}>Belum Diverifikasi</option>
                    <option value="rejected" {{ request('verification') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <button type="submit" class="admin-btn">Filter</button>
            @if (request()->hasAny(['search', 'status', 'verification']))
                <a href="{{ route('admin.farmer-profiles.index') }}" class="admin-btn admin-btn--secondary">Reset</a>
            @endif
        </form>
    </div>

    {{-- Flash Status --}}
    @if (session('status'))
        <div class="admin-alert admin-alert--success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    {{-- Table Card --}}
    <div class="fp-card" style="padding:0; overflow:hidden;">
        @if ($profiles->isEmpty())
            <div style="text-align:center; padding:60px 20px; color:#64748b;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin:0 auto 12px;">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/>
                    <path d="M2 12h20"/>
                </svg>
                <p style="font-size:14px; font-weight:700; color:#334155; margin:0 0 4px;">Belum ada website usaha tani</p>
                <p style="font-size:12px; margin:0 0 16px;">Klik tombol di bawah untuk membuat website company profile pertama petani.</p>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px;">
                    <thead>
                        <tr style="background:#0f172a; color:#ffffff;">
                            <th style="padding:14px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;">Usaha / Petani</th>
                            <th style="padding:14px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;">Subdomain Website</th>
                            <th style="padding:14px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;">Template</th>
                            <th style="padding:14px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; text-align:center;">Status</th>
                            <th style="padding:14px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; text-align:center;">Verifikasi</th>
                            <th style="padding:14px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; text-align:right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody style="border-top:1px solid #e2e8f0;">
                        @foreach ($profiles as $profile)
                            <tr style="border-bottom:1px solid #f1f5f9; transition:background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                <td style="padding:16px 20px;">
                                    <div style="font-weight:700; color:#0f172a; font-size:14px;">{{ $profile->business_name }}</div>
                                    <div style="font-size:12px; color:#64748b; margin-top:2px;">{{ $profile->farmer?->name }} &bull; {{ $profile->farmer?->phone ?? 'Tanpa HP' }}</div>
                                </td>
                                <td style="padding:16px 20px;">
                                    @if ($profile->subdomain)
                                        <a href="{{ $profile->publicUrl() }}" target="_blank"
                                            style="color:#166534; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                                            {{ $profile->subdomain }}.{{ config('domains.base') }}
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                                <polyline points="15 3 21 3 21 9"/>
                                            </svg>
                                        </a>
                                    @else
                                        <span style="color:#94a3b8; font-size:12px;">Belum dipilih</span>
                                    @endif
                                </td>
                                <td style="padding:16px 20px; color:#334155; font-weight:600;">
                                    {{ $profile->template?->name ?? '—' }}
                                </td>
                                <td style="padding:16px 20px; text-align:center;">
                                    <span style="display:inline-block; font-size:11px; font-weight:700; padding:3px 10px; border-radius:99px; {{ $statusColors[$profile->website_status?->value] ?? '' }}">
                                        {{ $profile->website_status?->label() ?? '-' }}
                                    </span>
                                </td>
                                <td style="padding:16px 20px; text-align:center;">
                                    <span style="display:inline-block; font-size:11px; font-weight:700; padding:3px 10px; border-radius:99px; {{ $verifyColors[$profile->verification_status?->value] ?? '' }}">
                                        {{ $profile->verification_status?->label() ?? '-' }}
                                    </span>
                                </td>
                                <td style="padding:16px 20px; text-align:right;">
                                    <div style="display:inline-flex; align-items:center; gap:6px;">
                                        {{-- Edit Button --}}
                                        <a href="{{ route('admin.farmer-profiles.edit', $profile) }}" class="admin-btn admin-btn--secondary admin-btn--sm">
                                            Edit
                                        </a>

                                        {{-- Verify Button --}}
                                        @if ($profile->verification_status?->value === 'unverified')
                                            <form method="POST" action="{{ route('admin.farmer-profiles.verify', $profile) }}" style="margin:0;">
                                                @csrf
                                                <button type="submit" class="admin-btn admin-btn--success admin-btn--sm">
                                                    Verifikasi
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Suspend / Restore --}}
                                        @if ($profile->website_status?->value === 'published')
                                            <form method="POST" action="{{ route('admin.farmer-profiles.suspend', $profile) }}" style="margin:0;">
                                                @csrf
                                                <button type="submit" class="admin-btn admin-btn--warning admin-btn--sm" onclick="return confirm('Tangguhkan website ini?')">
                                                    Suspend
                                                </button>
                                            </form>
                                        @elseif ($profile->website_status?->value === 'suspended')
                                            <form method="POST" action="{{ route('admin.farmer-profiles.restore', $profile) }}" style="margin:0;">
                                                @csrf
                                                <button type="submit" class="admin-btn admin-btn--success admin-btn--sm">
                                                    Pulihkan
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Delete Button --}}
                                        <form method="POST" action="{{ route('admin.farmer-profiles.destroy', $profile) }}" style="margin:0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn admin-btn--danger admin-btn--sm" onclick="return confirm('Hapus profil website ini secara permanen?')">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($profiles->hasPages())
                <div style="padding:16px 20px; border-top:1px solid #e2e8f0;">
                    {{ $profiles->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
