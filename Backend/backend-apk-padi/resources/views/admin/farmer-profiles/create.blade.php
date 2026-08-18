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
        <span class="fp-breadcrumb-current">Tambah Profil Publik Baru</span>
    </nav>

    {{-- Page Header --}}
    <div class="fp-header">
        <div>
            <h1 class="fp-title">Tambah Profil Publik Petani</h1>
            <p class="fp-description">Publikasikan profil usaha tani, etalase panen, dan digital identity resmi petani di bawah domain P.A.D.I.</p>
        </div>

        <a href="{{ route('admin.farmer-profiles.index') }}" class="admin-btn admin-btn--secondary">
            Batal / Kembali
        </a>
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

    <form method="POST" action="{{ route('admin.farmer-profiles.store') }}" enctype="multipart/form-data">
        @csrf

        {{-- Section 1: Akun & Domain Subdomain --}}
        <div class="fp-card">
            <div class="fp-card-header">
                <div class="fp-card-step">1</div>
                <div>
                    <h2 class="fp-card-title">Akun Petani & Subdomain Website</h2>
                    <p class="fp-card-subtitle">Pilih petani pemilik usaha dan tentukan alamat subdomain website publiknya.</p>
                </div>
            </div>

            <div class="fp-grid-2">
                {{-- Pilih Petani --}}
                <div class="fp-field">
                    <label class="fp-label" for="farmer_id">
                        Pilih Akun Petani <span class="fp-required">*</span>
                    </label>
                    <select name="farmer_id" id="farmer_id" class="admin-select" required>
                        <option value="">-- Pilih Akun Petani --</option>
                        @foreach ($farmers as $farmer)
                            <option value="{{ $farmer->id }}" {{ old('farmer_id', $selectedFarmerId) == $farmer->id ? 'selected' : '' }}>
                                {{ $farmer->name }} &mdash; {{ $farmer->email }} ({{ $farmer->phone ?? 'Tanpa No HP' }})
                            </option>
                        @endforeach
                    </select>
                    <p class="fp-hint">Hanya menampilkan petani aktif yang belum memiliki website publik.</p>
                </div>

                {{-- Template Pilihan --}}
                <div class="fp-field">
                    <label class="fp-label" for="profile_template_id">
                        Template Website <span class="fp-required">*</span>
                    </label>
                    <select name="profile_template_id" id="profile_template_id" class="admin-select" required>
                        @foreach ($templates as $tpl)
                            <option value="{{ $tpl->id }}" {{ old('profile_template_id') == $tpl->id ? 'selected' : '' }}>
                                {{ $tpl->name }} &mdash; {{ Str::limit($tpl->description, 50) }}
                            </option>
                        @endforeach
                    </select>
                    <p class="fp-hint">Semua template resmi dirancang responsif dan konsisten.</p>
                </div>

                {{-- Subdomain --}}
                <div class="fp-field fp-full">
                    <label class="fp-label" for="subdomain">
                        Alamat Subdomain Website <span class="fp-required">*</span>
                    </label>
                    <div class="fp-input-subdomain">
                        <input type="text" name="subdomain" id="subdomain"
                            value="{{ old('subdomain') }}"
                            required maxlength="40"
                            class="admin-input"
                            placeholder="contoh: pakjoko atau tanimaju">
                        <span class="fp-subdomain-suffix">.{{ $baseDomain }}</span>
                    </div>
                    <p class="fp-hint">Hanya huruf kecil, angka, dan tanda hubung (-). Contoh: <code>pakjoko</code> akan menjadi <code>pakjoko.{{ $baseDomain }}</code>.</p>
                </div>
            </div>
        </div>

        {{-- Section 2: Identitas Usaha Tani --}}
        <div class="fp-card">
            <div class="fp-card-header">
                <div class="fp-card-step">2</div>
                <div>
                    <h2 class="fp-card-title">Identitas & Profil Usaha Tani</h2>
                    <p class="fp-card-subtitle">Nama resmi kelompok tani, slogan pemikat, dan narasi pengalaman bertani.</p>
                </div>
            </div>

            <div class="fp-grid-2">
                <div class="fp-field">
                    <label class="fp-label" for="business_name">
                        Nama Usaha Tani <span class="fp-required">*</span>
                    </label>
                    <input type="text" name="business_name" id="business_name"
                        value="{{ old('business_name') }}"
                        required maxlength="150"
                        class="admin-input"
                        placeholder="Contoh: UD Tani Makmur Jaya">
                </div>

                <div class="fp-field">
                    <label class="fp-label" for="headline">
                        Tagline / Slogan Usaha
                    </label>
                    <input type="text" name="headline" id="headline"
                        value="{{ old('headline') }}"
                        maxlength="255"
                        class="admin-input"
                        placeholder="Contoh: Produsen Beras Organik Kualitas Premium">
                </div>

                <div class="fp-field fp-full">
                    <label class="fp-label" for="description">
                        Deskripsi & Narasi Usaha
                    </label>
                    <textarea name="description" id="description" rows="4" maxlength="3000"
                        class="admin-textarea"
                        placeholder="Ceritakan sejarah usaha, luasan lahan garapan, varietas unggulan padi yang dibudidayakan, dan komitmen mutu kepada calon pembeli...">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Section 3: Media & Foto --}}
        <div class="fp-card">
            <div class="fp-card-header">
                <div class="fp-card-step">3</div>
                <div>
                    <h2 class="fp-card-title">Foto Logo & Banner Cover</h2>
                    <p class="fp-card-subtitle">Unggah identitas visual untuk mempercantik halaman depan website publik.</p>
                </div>
            </div>

            <div class="fp-grid-2">
                <div class="fp-field">
                    <label class="fp-label">Logo Usaha Tani</label>
                    <div class="fp-upload-box">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.75" style="margin:0 auto 8px;">
                            <circle cx="12" cy="12" r="10"/>
                            <circle cx="12" cy="10" r="3"/>
                            <path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662"/>
                        </svg>
                        <input type="file" name="logo" id="logo" accept="image/png,image/jpeg,image/webp" class="admin-input" style="padding:6px; font-size:12px;">
                        <p class="fp-hint">Format PNG, JPG, WebP. Maks 2MB. Rasio 1:1 disarankan.</p>
                    </div>
                </div>

                <div class="fp-field">
                    <label class="fp-label">Foto Cover / Banner Halaman</label>
                    <div class="fp-upload-box">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.75" style="margin:0 auto 8px;">
                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                            <circle cx="9" cy="9" r="2"/>
                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                        </svg>
                        <input type="file" name="cover_image" id="cover_image" accept="image/png,image/jpeg,image/webp" class="admin-input" style="padding:6px; font-size:12px;">
                        <p class="fp-hint">Format PNG, JPG, WebP. Maks 4MB. Rasio 16:9 disarankan.</p>
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
                    <p class="fp-card-subtitle">Saluran komunikasi yang dapat dihubungi langsung oleh calon pembeli hasil panen.</p>
                </div>
            </div>

            <div class="fp-grid-2">
                <div class="fp-field">
                    <label class="fp-label" for="whatsapp">Nomor WhatsApp Resmi</label>
                    <input type="text" name="whatsapp" id="whatsapp" value="{{ old('whatsapp') }}"
                        class="admin-input" placeholder="Contoh: 081234567890">
                    <p class="fp-hint">Akan dihubungkan langsung ke tombol CTA WhatsApp.</p>
                </div>

                <div class="fp-field">
                    <label class="fp-label" for="public_phone">Nomor Telepon Kantor / Rumah</label>
                    <input type="text" name="public_phone" id="public_phone" value="{{ old('public_phone') }}"
                        class="admin-input" placeholder="Contoh: 0231-123456">
                </div>

                <div class="fp-field">
                    <label class="fp-label" for="public_email">Email Korespondensi Publik</label>
                    <input type="email" name="public_email" id="public_email" value="{{ old('public_email') }}"
                        class="admin-input" placeholder="kontak@tanimaju.id">
                </div>

                <div class="fp-field">
                    <label class="fp-label" for="public_address">Lokasi Umum Usaha</label>
                    <input type="text" name="public_address" id="public_address" value="{{ old('public_address') }}"
                        class="admin-input" placeholder="Contoh: Kec. Kandanghaur, Kab. Indramayu, Jawa Barat">
                    <p class="fp-hint">Cukup sebutkan kecamatan & kabupaten untuk menjaga privasi.</p>
                </div>

                <div class="fp-field">
                    <label class="fp-label" for="instagram_url">Tautan Instagram</label>
                    <input type="url" name="instagram_url" id="instagram_url" value="{{ old('instagram_url') }}"
                        class="admin-input" placeholder="https://instagram.com/tanimakmur">
                </div>

                <div class="fp-field">
                    <label class="fp-label" for="facebook_url">Tautan Facebook Page</label>
                    <input type="url" name="facebook_url" id="facebook_url" value="{{ old('facebook_url') }}"
                        class="admin-input" placeholder="https://facebook.com/tanimakmur">
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
                        <option value="published" {{ old('website_status', 'published') === 'published' ? 'selected' : '' }}>Tayang (Published)</option>
                        <option value="draft" {{ old('website_status') === 'draft' ? 'selected' : '' }}>Draft (Disimpan Sementara)</option>
                        <option value="review" {{ old('website_status') === 'review' ? 'selected' : '' }}>Menunggu Review</option>
                        <option value="suspended" {{ old('website_status') === 'suspended' ? 'selected' : '' }}>Ditangguhkan (Suspended)</option>
                    </select>
                </div>

                <div class="fp-field">
                    <label class="fp-label" for="verification_status">
                        Status Verifikasi P.A.D.I. <span class="fp-required">*</span>
                    </label>
                    <select name="verification_status" id="verification_status" class="admin-select" required>
                        <option value="verified" {{ old('verification_status', 'verified') === 'verified' ? 'selected' : '' }}>Terverifikasi P.A.D.I. (Badge Centang)</option>
                        <option value="unverified" {{ old('verification_status') === 'unverified' ? 'selected' : '' }}>Belum Diverifikasi</option>
                        <option value="rejected" {{ old('verification_status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
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

                    @foreach ($defaults as $secKey => $secDefault)
                        <label class="fp-toggle-item">
                            <input type="checkbox" name="section_settings[{{ $secKey }}]" value="1"
                                {{ old("section_settings.{$secKey}", $secDefault ? '1' : '0') == '1' ? 'checked' : '' }}>
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
                Simpan & Buat Website Publik
            </button>
        </div>

    </form>
</div>

@endsection
