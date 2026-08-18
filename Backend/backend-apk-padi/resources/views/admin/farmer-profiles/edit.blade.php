@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/farmer-profile.css') }}">

@php
    $baseDomain = config('domains.base', 'localhost');
@endphp

<div class="fp-page">
    {{-- Breadcrumb --}}
    <nav class="fp-breadcrumb" aria-label="Breadcrumb">
        <span>Admin</span>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <a href="{{ route('admin.farmer-profiles.index') }}" style="color:#64748b; text-decoration:none;">Profil Publik Petani</a>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="fp-breadcrumb-current">Edit Profil Publik</span>
    </nav>

    {{-- Page Header --}}
    <div class="fp-header">
        <div>
            <h1 class="fp-title">Edit Profil Publik: {{ $profile->business_name }}</h1>
            <p class="fp-description">Kelola data website publik untuk {{ $profile->farmer?->name }} (<code>{{ $profile->subdomain }}.{{ $baseDomain }}</code>).</p>
        </div>

        <div style="display:flex; align-items:center; gap:10px;">
            @if ($profile->subdomain)
                <a href="{{ $profile->publicUrl() }}" target="_blank" class="admin-btn admin-btn--secondary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                        <polyline points="15 3 21 3 21 9"/>
                    </svg>
                    Lihat Website
                </a>
            @endif
            <a href="{{ route('admin.farmer-profiles.index') }}" class="admin-btn admin-btn--secondary">
                Kembali
            </a>
        </div>
    </div>

    {{-- Error Alert --}}
    @if ($errors->any())
        <div class="admin-alert admin-alert--error" role="alert">
            <p style="font-weight:700; margin:0 0 4px 0;">Mohon periksa kembali form berikut:</p>
            <ul style="margin:0; padding-left:20px; font-size:12px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.farmer-profiles.update', $profile) }}" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        {{-- Section 1: Akun & Domain Subdomain --}}
        <div class="fp-card">
            <div class="fp-card-header">
                <div class="fp-card-step">1</div>
                <div>
                    <h2 class="fp-card-title">Informasi Kepemilikan & Template</h2>
                    <p class="fp-card-subtitle">Akun petani terdaftar dan pilihan template website.</p>
                </div>
            </div>

            <div class="fp-grid-2">
                <div class="fp-field">
                    <label class="fp-label">Pemilik Akun Petani</label>
                    <input type="text" class="admin-input" value="{{ $profile->farmer?->name }} ({{ $profile->farmer?->email }})" disabled style="background:#f8fafc; color:#64748b;">
                </div>

                <div class="fp-field">
                    <label class="fp-label" for="profile_template_id">
                        Template Website <span class="fp-required">*</span>
                    </label>
                    <select name="profile_template_id" id="profile_template_id" class="admin-select" required>
                        @foreach ($templates as $tpl)
                            <option value="{{ $tpl->id }}" {{ old('profile_template_id', $profile->profile_template_id) == $tpl->id ? 'selected' : '' }}>
                                {{ $tpl->name }} &mdash; {{ Str::limit($tpl->description, 50) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="fp-field fp-full">
                    <label class="fp-label" for="subdomain">
                        Alamat Subdomain Website <span class="fp-required">*</span>
                    </label>
                    <div class="fp-input-subdomain">
                        <input type="text" name="subdomain" id="subdomain"
                            value="{{ old('subdomain', $profile->subdomain) }}"
                            required maxlength="40"
                            class="admin-input">
                        <span class="fp-subdomain-suffix">.{{ $baseDomain }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2: Identitas Usaha Tani --}}
        <div class="fp-card">
            <div class="fp-card-header">
                <div class="fp-card-step">2</div>
                <div>
                    <h2 class="fp-card-title">Identitas & Profil Usaha Tani</h2>
                    <p class="fp-card-subtitle">Nama resmi, slogan, dan narasi pengalaman bertani.</p>
                </div>
            </div>

            <div class="fp-grid-2">
                <div class="fp-field">
                    <label class="fp-label" for="business_name">
                        Nama Usaha Tani <span class="fp-required">*</span>
                    </label>
                    <input type="text" name="business_name" id="business_name"
                        value="{{ old('business_name', $profile->business_name) }}"
                        required maxlength="150"
                        class="admin-input">
                </div>

                <div class="fp-field">
                    <label class="fp-label" for="headline">
                        Tagline / Slogan Usaha
                    </label>
                    <input type="text" name="headline" id="headline"
                        value="{{ old('headline', $profile->headline) }}"
                        maxlength="255"
                        class="admin-input">
                </div>

                <div class="fp-field fp-full">
                    <label class="fp-label" for="description">
                        Deskripsi & Narasi Usaha
                    </label>
                    <textarea name="description" id="description" rows="4" maxlength="3000"
                        class="admin-textarea">{{ old('description', $profile->description) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Section 3: Media & Foto --}}
        <div class="fp-card">
            <div class="fp-card-header">
                <div class="fp-card-step">3</div>
                <div>
                    <h2 class="fp-card-title">Foto Logo & Banner Cover</h2>
                    <p class="fp-card-subtitle">Perbarui logo atau banner cover profil publik.</p>
                </div>
            </div>

            <div class="fp-grid-2">
                <div class="fp-field">
                    <label class="fp-label">Logo Usaha Tani</label>
                    @if ($profile->logo_path)
                        <div style="margin-bottom:10px; display:flex; align-items:center; gap:12px;">
                            <img src="{{ asset('storage/' . $profile->logo_path) }}" alt="Logo" style="width:48px; height:48px; border-radius:10px; object-fit:cover; border:1px solid #cbd5e1;">
                            <span style="font-size:12px; color:#64748b;">Logo saat ini</span>
                        </div>
                    @endif
                    <div class="fp-upload-box">
                        <input type="file" name="logo" id="logo" accept="image/png,image/jpeg,image/webp" class="admin-input" style="padding:6px; font-size:12px;">
                        <p class="fp-hint">Pilih gambar baru untuk mengganti logo.</p>
                    </div>
                </div>

                <div class="fp-field">
                    <label class="fp-label">Foto Cover / Banner Halaman</label>
                    @if ($profile->cover_image_path)
                        <div style="margin-bottom:10px; display:flex; align-items:center; gap:12px;">
                            <img src="{{ asset('storage/' . $profile->cover_image_path) }}" alt="Cover" style="width:96px; height:48px; border-radius:10px; object-fit:cover; border:1px solid #cbd5e1;">
                            <span style="font-size:12px; color:#64748b;">Cover saat ini</span>
                        </div>
                    @endif
                    <div class="fp-upload-box">
                        <input type="file" name="cover_image" id="cover_image" accept="image/png,image/jpeg,image/webp" class="admin-input" style="padding:6px; font-size:12px;">
                        <p class="fp-hint">Pilih gambar baru untuk mengganti banner.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 4: Kontak & Lokasi Publik --}}
        <div class="fp-card">
            <div class="fp-card-header">
                <div class="fp-card-step">4</div>
                <div>
                    <h2 class="fp-card-title">Kontak & Media Sosial Publik</h2>
                    <p class="fp-card-subtitle">Saluran komunikasi yang ditampilkan di website.</p>
                </div>
            </div>

            <div class="fp-grid-2">
                <div class="fp-field">
                    <label class="fp-label" for="whatsapp">Nomor WhatsApp</label>
                    <input type="text" name="whatsapp" id="whatsapp" value="{{ old('whatsapp', $profile->whatsapp) }}"
                        class="admin-input">
                </div>

                <div class="fp-field">
                    <label class="fp-label" for="public_phone">Nomor Telepon</label>
                    <input type="text" name="public_phone" id="public_phone" value="{{ old('public_phone', $profile->public_phone) }}"
                        class="admin-input">
                </div>

                <div class="fp-field">
                    <label class="fp-label" for="public_email">Email Publik</label>
                    <input type="email" name="public_email" id="public_email" value="{{ old('public_email', $profile->public_email) }}"
                        class="admin-input">
                </div>

                <div class="fp-field">
                    <label class="fp-label" for="public_address">Lokasi Umum</label>
                    <input type="text" name="public_address" id="public_address" value="{{ old('public_address', $profile->public_address) }}"
                        class="admin-input">
                </div>

                <div class="fp-field">
                    <label class="fp-label" for="instagram_url">Tautan Instagram</label>
                    <input type="url" name="instagram_url" id="instagram_url" value="{{ old('instagram_url', $profile->instagram_url) }}"
                        class="admin-input">
                </div>

                <div class="fp-field">
                    <label class="fp-label" for="facebook_url">Tautan Facebook Page</label>
                    <input type="url" name="facebook_url" id="facebook_url" value="{{ old('facebook_url', $profile->facebook_url) }}"
                        class="admin-input">
                </div>
            </div>
        </div>

        {{-- Section 5: Status & Kontrol Privasi --}}
        <div class="fp-card">
            <div class="fp-card-header">
                <div class="fp-card-step">5</div>
                <div>
                    <h2 class="fp-card-title">Status Publikasi & Kontrol Privasi</h2>
                    <p class="fp-card-subtitle">Pilih status tayang website dan tentukan bagian data yang diizinkan tampil di publik.</p>
                </div>
            </div>

            <div class="fp-grid-2" style="margin-bottom:24px;">
                <div class="fp-field">
                    <label class="fp-label" for="website_status">
                        Status Website <span class="fp-required">*</span>
                    </label>
                    <select name="website_status" id="website_status" class="admin-select" required>
                        <option value="published" {{ old('website_status', $profile->website_status?->value) === 'published' ? 'selected' : '' }}>Tayang (Published)</option>
                        <option value="draft" {{ old('website_status', $profile->website_status?->value) === 'draft' ? 'selected' : '' }}>Draft (Disimpan Sementara)</option>
                        <option value="review" {{ old('website_status', $profile->website_status?->value) === 'review' ? 'selected' : '' }}>Menunggu Review</option>
                        <option value="suspended" {{ old('website_status', $profile->website_status?->value) === 'suspended' ? 'selected' : '' }}>Ditangguhkan (Suspended)</option>
                    </select>
                </div>

                <div class="fp-field">
                    <label class="fp-label" for="verification_status">
                        Status Verifikasi P.A.D.I. <span class="fp-required">*</span>
                    </label>
                    <select name="verification_status" id="verification_status" class="admin-select" required>
                        <option value="verified" {{ old('verification_status', $profile->verification_status?->value) === 'verified' ? 'selected' : '' }}>Terverifikasi P.A.D.I. (Badge Centang)</option>
                        <option value="unverified" {{ old('verification_status', $profile->verification_status?->value) === 'unverified' ? 'selected' : '' }}>Belum Diverifikasi</option>
                        <option value="rejected" {{ old('verification_status', $profile->verification_status?->value) === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="fp-label" style="margin-bottom:12px;">Pilihan Visibilitas Bagian Publik:</label>
                <div class="fp-toggle-grid">
                    @php
                        $sectionDescriptions = [
                            'show_products'       => 'Daftar listing hasil panen aktif di Marketplace',
                            'show_location'       => 'Nama wilayah kecamatan dan kabupaten',
                            'show_gallery'        => 'Koleksi foto galeri dokumentasi tani',
                            'show_contact'        => 'Nomor WhatsApp, telepon, dan email publik',
                            'show_harvests'       => 'Rekam jejak musim dan varietas panen',
                            'show_productivity'   => 'Statistik total luas lahan dan produktivitas ton/ha',
                            'show_fields'         => 'Data umum lahan (tanpa koordinat GPS)',
                            'show_active_variety' => 'Varietas padi yang sedang dalam masa tanam aktif',
                        ];
                    @endphp

                    @foreach (\App\Models\FarmerPublicProfile::DEFAULT_SECTION_SETTINGS as $secKey => $secDefault)
                        <label class="fp-toggle-item">
                            <input type="checkbox" name="section_settings[{{ $secKey }}]" value="1"
                                {{ old("section_settings.{$secKey}", ($settings[$secKey] ?? false) ? '1' : '0') == '1' ? 'checked' : '' }}>
                            <div>
                                <div class="fp-toggle-label">{{ ucwords(str_replace('_', ' ', str_replace('show_', '', $secKey))) }}</div>
                                <div class="fp-toggle-desc">{{ $sectionDescriptions[$secKey] ?? 'Visibilitas bagian data ini.' }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="fp-actions">
            <a href="{{ route('admin.farmer-profiles.index') }}" class="admin-btn admin-btn--secondary">
                Batal
            </a>
            <button type="submit" class="admin-btn">
                Simpan Perubahan
            </button>
        </div>

    </form>
</div>

@endsection
