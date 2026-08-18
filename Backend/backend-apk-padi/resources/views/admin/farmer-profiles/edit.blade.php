@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/farmer-profile.css') }}">

@php
    $baseDomain = config('domains.base', 'localhost');
    $currentLogoUrl = $profile->logo_path ? asset('storage/' . $profile->logo_path) : null;
    $currentCoverUrl = $profile->cover_image_path ? asset('storage/' . $profile->cover_image_path) : 'https://images.unsplash.com/photo-1586771107445-d3ca888129ff?auto=format&fit=crop&w=1200&q=80';
    $activeTemplateCode = $profile->template?->code ?? 'harvest-prestige';
    $farmerListings = $listings ?? collect();
    $farmerFarms = $farms ?? collect();
    $farmerGallery = $gallery ?? collect();
    $defaultFarmId = $farmerFarms->first()?->id ?? 1;
@endphp

<style>
    /* Layout */
    .editor-layout {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 28px;
        align-items: start;
    }

    @media (max-width: 1200px) {
        .editor-layout {
            grid-template-columns: 1fr;
        }
    }

    /* Live Preview Sticky Sidebar */
    .live-preview-panel {
        position: sticky;
        top: 24px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        box-shadow: 0 12px 36px rgba(15, 23, 42, 0.08);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .live-preview-header {
        background: #0f172a;
        color: #ffffff;
        padding: 12px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .live-preview-title {
        font-size: 13px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Template Switcher Tabs */
    .tpl-switcher {
        display: flex;
        background: rgba(255, 255, 255, 0.1);
        padding: 3px;
        border-radius: 999px;
        gap: 2px;
    }

    .tpl-btn {
        padding: 4px 12px;
        font-size: 11px;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.7);
        border-radius: 999px;
        cursor: pointer;
        transition: all 0.2s ease;
        border: none;
        background: transparent;
    }

    .tpl-btn:hover {
        color: #ffffff;
    }

    .tpl-btn.active {
        background: #166534;
        color: #bbf7d0;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }

    /* Editable Inline Fields */
    .editable-live {
        cursor: text;
        position: relative;
        border-radius: 4px;
        transition: all 0.2s ease;
        padding: 2px 4px;
        margin: -2px -4px;
        outline: none;
    }

    .editable-live:hover {
        background: rgba(34, 197, 94, 0.15);
        box-shadow: 0 0 0 1px rgba(34, 197, 94, 0.5);
    }

    .editable-live:focus {
        background: #ffffff;
        color: #0f172a !important;
        box-shadow: 0 0 0 2px #166534;
    }

    /* Direct Photo Interactive Edit Overlays on Mockup */
    .interactive-photo-cover {
        position: relative;
        cursor: pointer;
        overflow: hidden;
        transition: filter 0.2s ease, transform 0.2s ease;
    }

    .interactive-photo-cover:hover::after {
        content: '📷 Klik / Drop untuk Ganti Foto Cover';
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.65);
        color: #ffffff;
        font-size: 11.5px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(2px);
        z-index: 10;
        padding: 10px;
        text-align: center;
        border-radius: inherit;
    }

    .interactive-photo-logo {
        position: relative;
        cursor: pointer;
        overflow: hidden;
        transition: transform 0.2s ease;
    }

    .interactive-photo-logo:hover::after {
        content: '📷';
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.75);
        color: #ffffff;
        font-size: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        border-radius: inherit;
    }

    /* Mockup Container */
    .mockup-viewport {
        background: #f8fafc;
        padding: 16px;
        border-bottom: 1px solid #e2e8f0;
        min-height: 380px;
    }

    /* Template Views */
    .mockup-agri-modern {
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
    }

    .agri-nav {
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        padding: 10px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .agri-brand {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .agri-logo {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #143D2B;
        color: #B7FF00;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 13px;
        overflow: hidden;
    }

    .agri-logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .agri-hero {
        background: #F6F7F4;
        padding: 18px 16px;
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 14px;
        align-items: center;
    }

    .agri-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: rgba(20, 61, 43, 0.08);
        color: #143D2B;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 9.5px;
        font-weight: 800;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .agri-title {
        font-size: 19px;
        font-weight: 800;
        color: #0B281B;
        line-height: 1.15;
        margin-bottom: 6px;
    }

    .agri-headline {
        font-size: 11.5px;
        font-weight: 700;
        color: #143D2B;
        margin-bottom: 6px;
        line-height: 1.35;
    }

    .agri-desc {
        font-size: 10.5px;
        color: #68716B;
        line-height: 1.5;
        margin-bottom: 10px;
        max-height: 48px;
        overflow: hidden;
    }

    .agri-media {
        aspect-ratio: 4/3;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid #ffffff;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        background-size: cover;
        background-position: center;
    }

    .agri-marquee {
        background: #0B281B;
        color: #ffffff;
        padding: 6px 12px;
        font-size: 9.5px;
        font-weight: 800;
        letter-spacing: 1px;
        display: flex;
        justify-content: space-between;
        text-transform: uppercase;
    }

    .mockup-harvest-prestige {
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
    }

    .harvest-hero {
        min-height: 200px;
        background-size: cover;
        background-position: center;
        position: relative;
        padding: 20px 16px 32px;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }

    .harvest-hero::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(18, 59, 43, 0.3) 0%, rgba(18, 59, 43, 0.88) 100%);
    }

    .harvest-hero-content {
        position: relative;
        z-index: 2;
        color: #ffffff;
    }

    .harvest-title {
        font-size: 20px;
        font-weight: 800;
        color: #ffffff;
        line-height: 1.1;
        margin-bottom: 4px;
    }

    .harvest-stats-strip {
        background: #ffffff;
        margin: -14px 12px 12px;
        border-radius: 10px;
        padding: 10px 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        position: relative;
        z-index: 3;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 6px;
        text-align: center;
        border: 1px solid #e8ebe8;
    }

    .mockup-marketplace-pro {
        background: radial-gradient(circle at 10% 20%, rgba(146, 220, 24, 0.15), transparent 40%), #f5f9ed;
        padding: 10px;
        border-radius: 16px;
    }

    .market-shell {
        background: #ffffff;
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid #e6eadf;
        box-shadow: 0 4px 16px rgba(44, 78, 31, 0.08);
    }

    .market-hero {
        padding: 12px;
        display: grid;
        grid-template-columns: 1.6fr 0.7fr;
        gap: 8px;
    }

    .market-hero-main {
        border-radius: 10px;
        background-size: cover;
        background-position: center;
        padding: 14px 12px;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        min-height: 150px;
    }

    .market-hero-main::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 10px;
        background: linear-gradient(180deg, rgba(23, 63, 24, 0.25) 0%, rgba(23, 63, 24, 0.92) 100%);
    }

    .market-content {
        position: relative;
        z-index: 2;
        color: #ffffff;
    }

    /* Drag & Drop */
    .dropzone-container {
        position: relative;
        border: 2px dashed #cbd5e1;
        border-radius: 14px;
        padding: 18px;
        background: #f8fafc;
        text-align: center;
        cursor: pointer;
        transition: all 0.25s ease;
    }

    .dropzone-container.is-dragover {
        border-color: #166534;
        background: #f0fdf4;
        transform: scale(1.01);
    }

    .dropzone-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #e2e8f0;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        margin-bottom: 6px;
    }

    .dropzone-preview {
        display: flex;
        align-items: center;
        gap: 12px;
        text-align: left;
    }

    .dropzone-preview img {
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid #cbd5e1;
    }

    /* Product Item Card in Admin */
    .admin-product-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-bottom: 10px;
        gap: 14px;
        transition: all 0.2s ease;
    }

    .admin-product-item:hover {
        background: #ffffff;
        border-color: #166534;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    .admin-product-thumb {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        object-fit: cover;
        background: #e2e8f0;
        flex-shrink: 0;
    }

    .admin-product-info {
        flex-grow: 1;
        min-width: 0;
    }

    .admin-product-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    /* Gallery Grid in Admin */
    .admin-gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 12px;
        margin-top: 14px;
    }

    .admin-gallery-card {
        position: relative;
        border-radius: 10px;
        overflow: hidden;
        aspect-ratio: 4/3;
        border: 1px solid #e2e8f0;
        background: #f1f5f9;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }

    .admin-gallery-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .admin-gallery-delete-btn {
        position: absolute;
        top: 6px;
        right: 6px;
        background: rgba(220, 38, 38, 0.9);
        color: #ffffff;
        border: none;
        border-radius: 6px;
        width: 26px;
        height: 26px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .admin-gallery-delete-btn:hover {
        background: #b91c1c;
        transform: scale(1.1);
    }

    .admin-gallery-caption {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(180deg, transparent 0%, rgba(15, 23, 42, 0.85) 100%);
        color: #ffffff;
        font-size: 10px;
        padding: 12px 6px 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Modal Overlay */
    .admin-modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .admin-modal.is-open {
        display: flex;
    }

    .admin-modal-card {
        background: #ffffff;
        border-radius: 18px;
        width: 100%;
        max-width: 560px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
        overflow: hidden;
    }

    .admin-modal-header {
        background: #0f172a;
        color: #ffffff;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .admin-modal-body {
        padding: 20px;
        max-height: 75vh;
        overflow-y: auto;
    }
</style>

<div class="fp-page">
    {{-- Breadcrumb --}}
    <nav class="fp-breadcrumb" aria-label="Breadcrumb">
        <span>Admin</span>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <a href="{{ route('admin.farmer-profiles.index') }}" style="color:#64748b; text-decoration:none;">Profil Publik Petani</a>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <span class="fp-breadcrumb-current">Visual Editor & Foto</span>
    </nav>

    {{-- Header --}}
    <div class="fp-header">
        <div>
            <h1 class="fp-title">Visual Live Editor: {{ $profile->business_name }}</h1>
            <p class="fp-description">
                Edit teks dan foto langsung pada web mockup (klik foto cover/logo untuk ganti seketika), kelola galeri dokumentasi sawah, serta atur produk jualan (<code>{{ $profile->subdomain }}.{{ $baseDomain }}</code>).
            </p>
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
            <a href="{{ route('admin.farmer-profiles.index') }}" class="admin-btn admin-btn--secondary">Kembali</a>
        </div>
    </div>

    {{-- Flash Status Alert --}}
    @if (session('status'))
        <div class="admin-alert admin-alert--success" style="background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; padding:12px 16px; border-radius:10px; margin-bottom:20px;">
            <i class="fas fa-check-circle" style="margin-right:6px;"></i> {{ session('status') }}
        </div>
    @endif

    {{-- Error Alert --}}
    @if ($errors->any())
        <div class="admin-alert admin-alert--error" role="alert" style="background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; padding:12px 16px; border-radius:10px; margin-bottom:20px;">
            <p style="font-weight:700; margin:0 0 4px 0;">Mohon periksa kembali form berikut:</p>
            <ul style="margin:0; padding-left:20px; font-size:12px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="editor-layout">

        {{-- LEFT COLUMN: FORM CONTROLS, GALLERY & PRODUCT LISTING --}}
        <div class="editor-left-col">

            {{-- Main Form for Profile Settings --}}
            <form method="POST" action="{{ route('admin.farmer-profiles.update', $profile) }}" enctype="multipart/form-data" id="profileForm">
                @csrf
                @method('PATCH')

                {{-- Section 1: Template & Subdomain --}}
                <div class="fp-card">
                    <div class="fp-card-header">
                        <div class="fp-card-step">1</div>
                        <div>
                            <h2 class="fp-card-title">Pilihan Template & Subdomain</h2>
                            <p class="fp-card-subtitle">Ganti template untuk mengubah tampilan live mockup seketika.</p>
                        </div>
                    </div>

                    <div class="fp-grid-2">
                        <div class="fp-field">
                            <label class="fp-label" for="profile_template_id">
                                Template Website <span class="fp-required">*</span>
                            </label>
                            <select name="profile_template_id" id="profile_template_id" class="admin-select" required onchange="switchTemplateBySelect(this)">
                                @foreach ($templates as $tpl)
                                    <option value="{{ $tpl->id }}" data-code="{{ $tpl->code }}" {{ old('profile_template_id', $profile->profile_template_id) == $tpl->id ? 'selected' : '' }}>
                                        {{ $tpl->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="fp-field">
                            <label class="fp-label" for="subdomain">
                                Subdomain Website <span class="fp-required">*</span>
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
                            <h2 class="fp-card-title">Identitas Usaha Tani</h2>
                            <p class="fp-card-subtitle">Ketik pada form atau edit langsung di layar mockup di samping.</p>
                        </div>
                    </div>

                    <div class="fp-grid-2">
                        <div class="fp-field fp-full">
                            <label class="fp-label" for="business_name">
                                Nama Usaha Tani <span class="fp-required">*</span>
                            </label>
                            <input type="text" name="business_name" id="business_name"
                                value="{{ old('business_name', $profile->business_name) }}"
                                required maxlength="150"
                                class="admin-input sync-input" data-sync-class="sync-name"
                                placeholder="Contoh: UD Tani Maju">
                        </div>

                        <div class="fp-field fp-full">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <label class="fp-label" for="headline">Tagline / Slogan Usaha</label>
                                <button type="button" class="template-badge-btn" onclick="applyTemplateHeadline()" style="background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; padding:3px 8px; border-radius:6px; font-size:11px; cursor:pointer;">
                                    <i class="fas fa-magic"></i> Contoh Slogan
                                </button>
                            </div>
                            <input type="text" name="headline" id="headline"
                                value="{{ old('headline', $profile->headline) }}"
                                maxlength="255"
                                class="admin-input sync-input" data-sync-class="sync-headline"
                                placeholder="Contoh: Pasokan Hasil Panen Padi Berkualitas Langsung dari Petani">
                        </div>

                        <div class="fp-field fp-full">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <label class="fp-label" for="description">Deskripsi Usaha</label>
                                <button type="button" class="template-badge-btn" onclick="applyTemplateDescription()" style="background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; padding:3px 8px; border-radius:6px; font-size:11px; cursor:pointer;">
                                    <i class="fas fa-magic"></i> Contoh Narasi
                                </button>
                            </div>
                            <textarea name="description" id="description" rows="3" maxlength="3000"
                                class="admin-textarea sync-input" data-sync-class="sync-desc"
                                placeholder="Ceritakan riwayat usaha dan komitmen mutu...">{{ old('description', $profile->description) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Section 3: Drag & Drop Media (Logo & Cover) --}}
                <div class="fp-card">
                    <div class="fp-card-header">
                        <div class="fp-card-step">3</div>
                        <div>
                            <h2 class="fp-card-title">Foto Logo & Banner Cover (Drag & Drop)</h2>
                            <p class="fp-card-subtitle">Bisa klik kotak di bawah atau klik langsung foto di layar mockup samping!</p>
                        </div>
                    </div>

                    <div class="fp-grid-2">
                        <div class="fp-field">
                            <label class="fp-label">Logo Usaha (1:1)</label>
                            <div class="dropzone-container" id="logoDropzone" onclick="document.getElementById('logoInput').click()">
                                <input type="file" name="logo" id="logoInput" accept="image/*" style="display:none;">
                                
                                <div id="logoDefaultState" style="{{ $currentLogoUrl ? 'display:none;' : '' }}">
                                    <div class="dropzone-icon"><i class="fas fa-image"></i></div>
                                    <div style="font-size:12.5px; font-weight:700; color:#0f172a;">Drag & drop logo</div>
                                    <div style="font-size:11px; color:#64748b;">atau klik untuk browse</div>
                                </div>

                                <div id="logoPreviewState" class="dropzone-preview" style="{{ $currentLogoUrl ? '' : 'display:none;' }}">
                                    <img id="logoPreviewImg" src="{{ $currentLogoUrl ?: '' }}" alt="Logo" style="width:44px; height:44px; border-radius:8px;">
                                    <div>
                                        <div style="font-size:12px; font-weight:700; color:#0f172a;" id="logoFileName">Logo Aktif</div>
                                        <div style="font-size:11px; color:#166534;">Klik/drop ganti</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="fp-field">
                            <label class="fp-label">Foto Cover Banner (16:9)</label>
                            <div class="dropzone-container" id="coverDropzone" onclick="document.getElementById('coverInput').click()">
                                <input type="file" name="cover_image" id="coverInput" accept="image/*" style="display:none;">
                                
                                <div id="coverDefaultState" style="{{ $profile->cover_image_path ? 'display:none;' : '' }}">
                                    <div class="dropzone-icon"><i class="fas fa-cloud-arrow-up"></i></div>
                                    <div style="font-size:12.5px; font-weight:700; color:#0f172a;">Drag & drop cover</div>
                                    <div style="font-size:11px; color:#64748b;">atau klik untuk browse</div>
                                </div>

                                <div id="coverPreviewState" class="dropzone-preview" style="{{ $profile->cover_image_path ? '' : 'display:none;' }}">
                                    <img id="coverPreviewImg" src="{{ $currentCoverUrl }}" alt="Cover" style="width:64px; height:44px; border-radius:8px;">
                                    <div>
                                        <div style="font-size:12px; font-weight:700; color:#0f172a;" id="coverFileName">Cover Aktif</div>
                                        <div style="font-size:11px; color:#166534;">Klik/drop ganti</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section 4: Kontak Publik --}}
                <div class="fp-card">
                    <div class="fp-card-header">
                        <div class="fp-card-step">4</div>
                        <div>
                            <h2 class="fp-card-title">Kontak & Media Sosial</h2>
                            <p class="fp-card-subtitle">Saluran komunikasi yang ditampilkan di website.</p>
                        </div>
                    </div>

                    <div class="fp-grid-2">
                        <div class="fp-field">
                            <label class="fp-label" for="whatsapp">WhatsApp</label>
                            <input type="text" name="whatsapp" id="whatsapp" value="{{ old('whatsapp', $profile->whatsapp) }}"
                                class="admin-input sync-input" data-sync-class="sync-wa" placeholder="08123456789">
                        </div>

                        <div class="fp-field">
                            <label class="fp-label" for="public_phone">Telepon Publik</label>
                            <input type="text" name="public_phone" id="public_phone" value="{{ old('public_phone', $profile->public_phone) }}"
                                class="admin-input" placeholder="021-xxxxxxx">
                        </div>

                        <div class="fp-field">
                            <label class="fp-label" for="public_email">Email Publik</label>
                            <input type="email" name="public_email" id="public_email" value="{{ old('public_email', $profile->public_email) }}"
                                class="admin-input" placeholder="kontak@tani.com">
                        </div>

                        <div class="fp-field">
                            <label class="fp-label" for="public_address">Lokasi Umum</label>
                            <input type="text" name="public_address" id="public_address" value="{{ old('public_address', $profile->public_address) }}"
                                class="admin-input sync-input" data-sync-class="sync-address" placeholder="Kec. Ciawi, Kab. Bogor">
                        </div>
                    </div>
                </div>

                {{-- Section 5: Status & Visibilitas --}}
                <div class="fp-card">
                    <div class="fp-card-header">
                        <div class="fp-card-step">5</div>
                        <div>
                            <h2 class="fp-card-title">Status Publikasi & Privasi</h2>
                            <p class="fp-card-subtitle">Pengaturan status tayang dan verifikasi.</p>
                        </div>
                    </div>

                    <div class="fp-grid-2">
                        <div class="fp-field">
                            <label class="fp-label" for="website_status">Status Website</label>
                            <select name="website_status" id="website_status" class="admin-select" required>
                                <option value="published" {{ old('website_status', $profile->website_status?->value) === 'published' ? 'selected' : '' }}>Tayang (Published)</option>
                                <option value="draft" {{ old('website_status', $profile->website_status?->value) === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="review" {{ old('website_status', $profile->website_status?->value) === 'review' ? 'selected' : '' }}>Review</option>
                                <option value="suspended" {{ old('website_status', $profile->website_status?->value) === 'suspended' ? 'selected' : '' }}>Ditangguhkan</option>
                            </select>
                        </div>

                        <div class="fp-field">
                            <label class="fp-label" for="verification_status">Status Verifikasi</label>
                            <select name="verification_status" id="verification_status" class="admin-select" required>
                                <option value="verified" {{ old('verification_status', $profile->verification_status?->value) === 'verified' ? 'selected' : '' }}>Terverifikasi (Badge Centang)</option>
                                <option value="unverified" {{ old('verification_status', $profile->verification_status?->value) === 'unverified' ? 'selected' : '' }}>Belum Diverifikasi</option>
                                <option value="rejected" {{ old('verification_status', $profile->verification_status?->value) === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="fp-actions" style="display:flex; justify-content:flex-end; gap:12px; margin-bottom:24px;">
                    <a href="{{ route('admin.farmer-profiles.index') }}" class="admin-btn admin-btn--secondary">Batal</a>
                    <button type="submit" class="admin-btn">Simpan Pengaturan Profil</button>
                </div>
            </form>

            {{-- ==========================================================================
                 SECTION 6: GALERI & FOTO DOKUMENTASI SAWAH / PANEN
                 ========================================================================== --}}
            <div class="fp-card" style="border: 2px solid #e2e8f0; background:#ffffff; margin-bottom:24px;">
                <div class="fp-card-header" style="justify-content:space-between; flex-wrap:wrap; gap:12px;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div class="fp-card-step" style="background:#0f172a;">6</div>
                        <div>
                            <h2 class="fp-card-title">Galeri Foto Dokumentasi Sawah & Panen</h2>
                            <p class="fp-card-subtitle">Upload foto-foto aktivitas sawah, bibit, pemeliharaan, atau hasil panen yang tampil di website.</p>
                        </div>
                    </div>
                </div>

                {{-- Upload New Gallery Photo Form --}}
                <form method="POST" action="{{ route('admin.farmer-profiles.gallery.store', $profile) }}" enctype="multipart/form-data" style="background:#f8fafc; border:1px solid #e2e8f0; padding:16px; border-radius:12px; margin-bottom:16px;">
                    @csrf
                    <div class="fp-grid-2" style="align-items:flex-end; gap:12px;">
                        <div class="fp-field">
                            <label class="fp-label" for="gallery_image">Pilih / Drag Foto Baru (Maks 5MB)</label>
                            <input type="file" name="image" id="gallery_image" accept="image/*" required class="admin-input" style="padding:6px 10px;">
                        </div>
                        <div class="fp-field">
                            <label class="fp-label" for="gallery_caption">Keterangan / Caption Foto</label>
                            <input type="text" name="caption" id="gallery_caption" class="admin-input" placeholder="Contoh: Hamparan Sawah Ciherang Musim Panen">
                        </div>
                    </div>
                    <div style="text-align:right; margin-top:12px;">
                        <button type="submit" class="admin-btn" style="padding:7px 16px; font-size:12px;">
                            <i class="fas fa-cloud-arrow-up"></i> Upload Foto ke Galeri
                        </button>
                    </div>
                </form>

                {{-- Gallery Photos Grid --}}
                @if ($farmerGallery->isNotEmpty())
                    <div class="admin-gallery-grid">
                        @foreach ($farmerGallery as $item)
                            <div class="admin-gallery-card">
                                <img src="{{ $item->imageUrl() }}" alt="{{ $item->caption ?? 'Foto Galeri' }}">
                                @if ($item->caption)
                                    <div class="admin-gallery-caption" title="{{ $item->caption }}">{{ $item->caption }}</div>
                                @endif
                                <form method="POST" action="{{ route('admin.farmer-profiles.gallery.destroy', [$profile, $item]) }}" onsubmit="return confirm('Hapus foto galeri ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-gallery-delete-btn" title="Hapus Foto">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="text-align:center; padding:20px; background:#f8fafc; border:1px dashed #cbd5e1; border-radius:10px;">
                        <i class="fas fa-images" style="font-size:24px; color:#94a3b8; margin-bottom:6px;"></i>
                        <p style="font-size:12px; color:#64748b; margin:0;">Belum ada foto galeri khusus. Website saat ini menampilkan foto dokumentasi default pertanian.</p>
                    </div>
                @endif
            </div>

            {{-- ==========================================================================
                 SECTION 7: KATALOG PRODUK & BARANG YANG DIJUAL DI WEB INI
                 ========================================================================== --}}
            <div class="fp-card" style="border: 2px solid #bbf7d0; background:#fcfffd;">
                <div class="fp-card-header" style="justify-content:space-between; flex-wrap:wrap; gap:12px;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div class="fp-card-step" style="background:#166534;">7</div>
                        <div>
                            <h2 class="fp-card-title">Katalog Produk & Barang Dijual</h2>
                            <p class="fp-card-subtitle">Tambah, edit foto/harga/stok, atau hapus barang yang tampil di website petani ini.</p>
                        </div>
                    </div>

                    <button type="button" onclick="openAddProductModal()" class="admin-btn" style="padding:8px 16px; font-size:12.5px; display:inline-flex; align-items:center; gap:6px;">
                        <i class="fas fa-plus"></i> Tambah Produk
                    </button>
                </div>

                {{-- List of Current Products --}}
                @if ($farmerListings->isNotEmpty())
                    <div class="admin-product-list">
                        @foreach ($farmerListings as $listing)
                            @php
                                $thumb = $listing->image_url ?? $listing->images->first()?->image_url ?? 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=300&q=80';
                            @endphp
                            <div class="admin-product-item">
                                <img src="{{ $thumb }}" alt="{{ $listing->commodity }}" class="admin-product-thumb">
                                
                                <div class="admin-product-info">
                                    <div style="font-weight:700; font-size:14px; color:#0f172a;">
                                        {{ $listing->commodity }}
                                    </div>
                                    <div style="font-size:12px; color:#166534; font-weight:700;">
                                        Rp {{ number_format($listing->price_per_unit, 0, ',', '.') }} / {{ $listing->unit }}
                                        <span style="color:#64748b; font-weight:400; margin-left:6px;">(Stok: {{ $listing->quantity }} {{ $listing->unit }})</span>
                                    </div>
                                    @if ($listing->status === 'published')
                                        <span style="font-size:10px; font-weight:700; color:#166534; background:#dcfce7; padding:2px 6px; border-radius:4px; display:inline-block; margin-top:2px;">
                                            Tayang di Web
                                        </span>
                                    @else
                                        <span style="font-size:10px; font-weight:700; color:#64748b; background:#f1f5f9; padding:2px 6px; border-radius:4px; display:inline-block; margin-top:2px;">
                                            Draft / Non-aktif
                                        </span>
                                    @endif
                                </div>

                                <div class="admin-product-actions">
                                    <button type="button" 
                                        onclick="openEditProductModal({{ json_encode($listing) }})" 
                                        class="admin-btn admin-btn--secondary" 
                                        style="padding:6px 12px; font-size:12px;">
                                        <i class="fas fa-pen"></i> Edit
                                    </button>

                                    <form method="POST" action="{{ route('admin.farmer-profiles.listings.destroy', [$profile, $listing]) }}" onsubmit="return confirm('Hapus produk {{ $listing->commodity }} dari etalase?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-btn admin-btn--secondary" style="padding:6px 10px; font-size:12px; color:#dc2626;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="text-align:center; padding:30px 16px; background:#f8fafc; border:1px dashed #cbd5e1; border-radius:12px;">
                        <i class="fas fa-box-open" style="font-size:32px; color:#94a3b8; margin-bottom:8px;"></i>
                        <p style="font-size:13px; font-weight:600; color:#475569; margin:0 0 4px 0;">Belum ada produk aktif untuk usaha tani ini</p>
                        <p style="font-size:12px; color:#64748b; margin:0 0 12px 0;">Tambahkan barang seperti Beras Premium, Gabah Kering, atau Benih agar langsung tampil di etalase.</p>
                        <button type="button" onclick="openAddProductModal()" class="admin-btn" style="padding:8px 16px; font-size:12px;">
                            <i class="fas fa-plus"></i> Tambah Produk Pertama
                        </button>
                    </div>
                @endif
            </div>

        </div>

        {{-- RIGHT COLUMN: DYNAMIC MULTI-TEMPLATE LIVE MOCKUP WITH DIRECT PHOTO CLICK & DROP --}}
        <div class="editor-right-col">
            <div class="live-preview-panel">
                
                {{-- Header with Template Tabs --}}
                <div class="live-preview-header">
                    <div class="live-preview-title">
                        <i class="fas fa-desktop"></i>
                        <span>Live Mockup (Interactive)</span>
                    </div>

                    <div class="tpl-switcher" role="tablist">
                        <button type="button" class="tpl-btn" data-tpl="agri-modern" onclick="switchTemplate('agri-modern')">
                            Agri Modern
                        </button>
                        <button type="button" class="tpl-btn" data-tpl="harvest-prestige" onclick="switchTemplate('harvest-prestige')">
                            Harvest Prestige
                        </button>
                        <button type="button" class="tpl-btn" data-tpl="marketplace-pro" onclick="switchTemplate('marketplace-pro')">
                            Marketplace Pro
                        </button>
                    </div>
                </div>

                {{-- Viewport Container --}}
                <div class="mockup-viewport">

                    {{-- VIEW 1: AGRI MODERN --}}
                    <div class="mockup-view mockup-agri-modern" id="view-agri-modern" style="display:none;">
                        <div class="agri-nav">
                            <div class="agri-brand">
                                <div class="agri-logo logo-sync-box interactive-photo-logo" title="Klik / Drop untuk ganti Logo Usaha" onclick="document.getElementById('logoInput').click()">
                                    @if ($currentLogoUrl)
                                        <img src="{{ $currentLogoUrl }}" alt="Logo" style="width:100%; height:100%; object-fit:cover;">
                                    @else
                                        <i class="fas fa-seedling"></i>
                                    @endif
                                </div>
                                <span class="sync-name editable-live" contenteditable="true" style="font-size:12.5px; font-weight:800; color:#0B281B;" title="Klik untuk edit teks">
                                    {{ $profile->business_name }}
                                </span>
                            </div>
                            <span style="font-size:9.5px; font-weight:700; color:#143D2B; background:rgba(20,61,43,0.08); padding:3px 8px; border-radius:999px;">
                                Mitra Tani
                            </span>
                        </div>

                        <div class="agri-hero">
                            <div>
                                <div class="agri-badge"><i class="fas fa-shield-check"></i> Terverifikasi P.A.D.I.</div>
                                <h2 class="agri-title sync-name editable-live" contenteditable="true" title="Klik untuk edit nama usaha">
                                    {{ $profile->business_name }}
                                </h2>
                                <div class="agri-headline sync-headline editable-live" contenteditable="true" title="Klik untuk edit slogan">
                                    {{ $profile->headline ?: 'Pasokan Hasil Panen Padi Berkualitas Langsung dari Petani' }}
                                </div>
                                <div class="agri-desc sync-desc editable-live" contenteditable="true" title="Klik untuk edit deskripsi">
                                    {{ $profile->description ?: 'Menyediakan pasokan komoditas pertanian terbaik dengan standar mutu terjamin.' }}
                                </div>
                                <div style="display:inline-flex; align-items:center; gap:6px; background:#B7FF00; color:#0B281B; padding:5px 12px; border-radius:999px; font-size:10px; font-weight:800;">
                                    Lihat Produk <i class="fas fa-arrow-right"></i>
                                </div>
                            </div>

                            <div class="agri-media cover-sync-box interactive-photo-cover" style="background-image: url('{{ $currentCoverUrl }}');" title="Klik / Drop untuk Ganti Foto Banner" onclick="document.getElementById('coverInput').click()"></div>
                        </div>

                        <div class="agri-marquee">
                            <span>BERAS PREMIUM</span>
                            <span class="star">✦</span>
                            <span>GABAH KERING</span>
                            <span class="star">✦</span>
                            <span>TRANSPARANSI MUTU</span>
                        </div>
                    </div>

                    {{-- VIEW 2: HARVEST PRESTIGE --}}
                    <div class="mockup-view mockup-harvest-prestige" id="view-harvest-prestige" style="display:none;">
                        <div class="harvest-hero cover-sync-box interactive-photo-cover" style="background-image: url('{{ $currentCoverUrl }}');" title="Klik / Drop untuk Ganti Foto Banner" onclick="document.getElementById('coverInput').click()">
                            <div class="harvest-hero-content">
                                <div class="harvest-badge"><i class="fas fa-shield-halved"></i> Verified Agriculture Prestige</div>
                                <h2 class="harvest-title sync-name editable-live" contenteditable="true" title="Klik untuk edit nama usaha">
                                    {{ $profile->business_name }}
                                </h2>
                                <div class="harvest-headline sync-headline editable-live" contenteditable="true" title="Klik untuk edit slogan">
                                    {{ $profile->headline ?: 'Penyedia Pasokan Beras Premium & Gabah Kering Unggul' }}
                                </div>
                                <div style="display:inline-flex; align-items:center; gap:6px; background:#B7FF00; color:#123b2b; padding:5px 14px; border-radius:999px; font-size:10px; font-weight:800;">
                                    Katalog Panen <i class="fas fa-arrow-right"></i>
                                </div>
                            </div>
                        </div>

                        <div class="harvest-stats-strip">
                            <div>
                                <div class="harvest-stat-val">12.4 Ha</div>
                                <div style="font-size:9px; color:#7d857f; text-transform:uppercase;">Luas Lahan</div>
                            </div>
                            <div>
                                <div class="harvest-stat-val">8 Musim</div>
                                <div style="font-size:9px; color:#7d857f; text-transform:uppercase;">Terdokumentasi</div>
                            </div>
                            <div>
                                <div class="harvest-stat-val">Grade A</div>
                                <div style="font-size:9px; color:#7d857f; text-transform:uppercase;">Kualitas Panen</div>
                            </div>
                        </div>

                        <div style="padding:0 14px 14px;">
                            <div class="sync-desc editable-live" contenteditable="true" style="font-size:11px; color:#525a54; line-height:1.5;" title="Klik untuk edit deskripsi">
                                {{ $profile->description ?: 'Beras dan gabah berkualitas tinggi yang diproduksi dengan integritas mutu dan transparansi catatan panen.' }}
                            </div>
                        </div>
                    </div>

                    {{-- VIEW 3: MARKETPLACE PRO --}}
                    <div class="mockup-view mockup-marketplace-pro" id="view-marketplace-pro" style="display:none;">
                        <div class="market-shell">
                            <div style="padding:8px 12px; border-bottom:1px solid #e6eadf; display:flex; align-items:center; justify-content:space-between;">
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <div class="logo-sync-box interactive-photo-logo" style="width:24px; height:24px; border-radius:6px; background:#173f18; color:#78c800; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:800; overflow:hidden;" title="Klik / Drop untuk ganti Logo" onclick="document.getElementById('logoInput').click()">
                                        @if ($currentLogoUrl)
                                            <img src="{{ $currentLogoUrl }}" alt="Logo" style="width:100%; height:100%; object-fit:cover;">
                                        @else
                                            <i class="fas fa-seedling"></i>
                                        @endif
                                    </div>
                                    <span class="sync-name editable-live" contenteditable="true" style="font-size:11.5px; font-weight:800; color:#173f18;" title="Klik untuk edit teks">
                                        {{ $profile->business_name }}
                                    </span>
                                </div>
                                <span style="font-size:9px; font-weight:700; color:#173f18; background:#eaf4dd; padding:2px 6px; border-radius:999px;">
                                    GreenMarket Pro
                                </span>
                            </div>

                            <div class="market-hero">
                                <div class="market-hero-main cover-sync-box interactive-photo-cover" style="background-image: url('{{ $currentCoverUrl }}');" title="Klik / Drop untuk Ganti Foto Banner" onclick="document.getElementById('coverInput').click()">
                                    <div class="market-content">
                                        <div style="font-size:8px; font-weight:800; color:#78c800; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:2px;">
                                            ✦ P.A.D.I. Verified
                                        </div>
                                        <h3 class="market-title sync-name editable-live" contenteditable="true" title="Klik untuk edit nama">
                                            {{ $profile->business_name }}
                                        </h3>
                                        <div class="sync-headline editable-live" contenteditable="true" style="font-size:10px; color:rgba(255,255,255,0.85);" title="Klik untuk edit slogan">
                                            {{ $profile->headline ?: 'Etalase Komoditas Hasil Panen' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="market-side cover-sync-box interactive-photo-cover" style="background-image: url('{{ $currentCoverUrl }}');" title="Klik / Drop untuk Ganti Foto Dokumentasi" onclick="document.getElementById('coverInput').click()">
                                    <div style="position:absolute; bottom:6px; left:6px; right:6px; background:rgba(23,63,24,0.85); color:#fff; font-size:8px; font-weight:700; padding:4px; border-radius:6px; text-align:center;">
                                        Dokumentasi
                                    </div>
                                </div>
                            </div>

                            <div style="padding:10px 12px; border-top:1px solid #f0f3ea;">
                                <div class="sync-desc editable-live" contenteditable="true" style="font-size:10.5px; color:#555d52; line-height:1.45;" title="Klik untuk edit deskripsi">
                                    {{ $profile->description ?: 'Hasil panen siap kirim dengan transparansi mutu langsung dari lahan petani.' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="text-align:center; margin-top:12px; display:flex; flex-direction:column; gap:4px;">
                        <span style="font-size:11.5px; font-weight:700; color:#166534;">
                            <i class="fas fa-camera"></i> Tips Foto: Klik / Drag & Drop gambar langsung ke Foto Cover atau Logo di atas!
                        </span>
                        <span style="font-size:11px; color:#64748b;">
                            <i class="fas fa-pen-to-square"></i> Tips Teks: Klik langsung kalimat mana pun untuk mengedit teks secara instan.
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ==========================================================================
     MODAL: TAMBAH PRODUK BARU
     ========================================================================== --}}
<div class="admin-modal" id="modalAddProduct" role="dialog" aria-modal="true">
    <div class="admin-modal-card">
        <div class="admin-modal-header">
            <h3 style="font-size:15px; font-weight:700; color:#fff; margin:0;">
                <i class="fas fa-plus-circle" style="color:#22c55e; margin-right:6px;"></i> Tambah Produk ke Etalase
            </h3>
            <button type="button" onclick="closeModal('modalAddProduct')" style="color:#94a3b8; font-size:18px; cursor:pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.farmer-profiles.listings.store', $profile) }}">
            @csrf
            <div class="admin-modal-body">
                <input type="hidden" name="farm_id" value="{{ $defaultFarmId }}">

                <div class="fp-field" style="margin-bottom:14px;">
                    <label class="fp-label" for="add_commodity">Nama Komoditas <span class="fp-required">*</span></label>
                    <input type="text" name="commodity" id="add_commodity" required class="admin-input" placeholder="Contoh: Beras Premium Ciherang, Gabah Kering">
                </div>

                <div class="fp-grid-2" style="margin-bottom:14px;">
                    <div class="fp-field">
                        <label class="fp-label" for="add_price">Harga per Unit (Rp) <span class="fp-required">*</span></label>
                        <input type="number" name="price_per_unit" id="add_price" required min="0" step="100" class="admin-input" placeholder="15000">
                    </div>

                    <div class="fp-field">
                        <label class="fp-label" for="add_unit">Satuan <span class="fp-required">*</span></label>
                        <select name="unit" id="add_unit" class="admin-select" required>
                            <option value="Kg">Kg</option>
                            <option value="Ton">Ton</option>
                            <option value="Karung">Karung (25 Kg)</option>
                            <option value="Kwintal">Kwintal</option>
                        </select>
                    </div>
                </div>

                <div class="fp-grid-2" style="margin-bottom:14px;">
                    <div class="fp-field">
                        <label class="fp-label" for="add_quantity">Jumlah Stok <span class="fp-required">*</span></label>
                        <input type="number" name="quantity" id="add_quantity" required min="0.1" step="0.1" class="admin-input" placeholder="500">
                    </div>

                    <div class="fp-field">
                        <label class="fp-label" for="add_status">Status Listing <span class="fp-required">*</span></label>
                        <select name="status" id="add_status" class="admin-select" required>
                            <option value="published">Tayang di Web (Published)</option>
                            <option value="draft">Draft (Disimpan Sementara)</option>
                        </select>
                    </div>
                </div>

                <div class="fp-field" style="margin-bottom:14px;">
                    <label class="fp-label" for="add_image_url">URL Foto Produk</label>
                    <input type="url" name="image_url" id="add_image_url" class="admin-input" placeholder="https://images.unsplash.com/... atau link foto">
                </div>

                <div class="fp-field" style="margin-bottom:14px;">
                    <label class="fp-label" for="add_sales_link">Tautan Pembelian (Opsional)</label>
                    <input type="url" name="sales_link" id="add_sales_link" class="admin-input" placeholder="https://shopee.co.id/... (jika ada)">
                </div>

                <div class="fp-field">
                    <label class="fp-label" for="add_description">Deskripsi & Spesifikasi Produk</label>
                    <textarea name="description" id="add_description" rows="3" class="admin-textarea" placeholder="Pulen alami, kadar air 13%, butir patah <10%..."></textarea>
                </div>
            </div>

            <div style="padding:14px 20px; background:#f8fafc; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closeModal('modalAddProduct')" class="admin-btn admin-btn--secondary">Batal</button>
                <button type="submit" class="admin-btn">Tambahkan ke Etalase</button>
            </div>
        </form>
    </div>
</div>

{{-- ==========================================================================
     MODAL: EDIT PRODUK
     ========================================================================== --}}
<div class="admin-modal" id="modalEditProduct" role="dialog" aria-modal="true">
    <div class="admin-modal-card">
        <div class="admin-modal-header">
            <h3 style="font-size:15px; font-weight:700; color:#fff; margin:0;">
                <i class="fas fa-edit" style="color:#22c55e; margin-right:6px;"></i> Edit Produk Etalase
            </h3>
            <button type="button" onclick="closeModal('modalEditProduct')" style="color:#94a3b8; font-size:18px; cursor:pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form method="POST" id="formEditProduct" action="">
            @csrf
            @method('PATCH')
            <div class="admin-modal-body">
                <div class="fp-field" style="margin-bottom:14px;">
                    <label class="fp-label" for="edit_commodity">Nama Komoditas <span class="fp-required">*</span></label>
                    <input type="text" name="commodity" id="edit_commodity" required class="admin-input">
                </div>

                <div class="fp-grid-2" style="margin-bottom:14px;">
                    <div class="fp-field">
                        <label class="fp-label" for="edit_price">Harga per Unit (Rp) <span class="fp-required">*</span></label>
                        <input type="number" name="price_per_unit" id="edit_price" required min="0" step="100" class="admin-input">
                    </div>

                    <div class="fp-field">
                        <label class="fp-label" for="edit_unit">Satuan <span class="fp-required">*</span></label>
                        <select name="unit" id="edit_unit" class="admin-select" required>
                            <option value="Kg">Kg</option>
                            <option value="Ton">Ton</option>
                            <option value="Karung">Karung (25 Kg)</option>
                            <option value="Kwintal">Kwintal</option>
                        </select>
                    </div>
                </div>

                <div class="fp-grid-2" style="margin-bottom:14px;">
                    <div class="fp-field">
                        <label class="fp-label" for="edit_quantity">Jumlah Stok <span class="fp-required">*</span></label>
                        <input type="number" name="quantity" id="edit_quantity" required min="0" step="0.1" class="admin-input">
                    </div>

                    <div class="fp-field">
                        <label class="fp-label" for="edit_status">Status Listing <span class="fp-required">*</span></label>
                        <select name="status" id="edit_status" class="admin-select" required>
                            <option value="published">Tayang di Web (Published)</option>
                            <option value="draft">Draft (Disimpan Sementara)</option>
                        </select>
                    </div>
                </div>

                <div class="fp-field" style="margin-bottom:14px;">
                    <label class="fp-label" for="edit_image_url">URL Foto Produk</label>
                    <input type="url" name="image_url" id="edit_image_url" class="admin-input">
                </div>

                <div class="fp-field" style="margin-bottom:14px;">
                    <label class="fp-label" for="edit_sales_link">Tautan Pembelian (Opsional)</label>
                    <input type="url" name="sales_link" id="edit_sales_link" class="admin-input">
                </div>

                <div class="fp-field">
                    <label class="fp-label" for="edit_description">Deskripsi & Spesifikasi</label>
                    <textarea name="description" id="edit_description" rows="3" class="admin-textarea"></textarea>
                </div>
            </div>

            <div style="padding:14px 20px; background:#f8fafc; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closeModal('modalEditProduct')" class="admin-btn admin-btn--secondary">Batal</button>
                <button type="submit" class="admin-btn">Simpan Perubahan Produk</button>
            </div>
        </form>
    </div>
</div>

{{-- Scripts --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Template Switcher
        let currentTemplate = '{{ $activeTemplateCode }}';

        window.switchTemplate = function (templateCode) {
            currentTemplate = templateCode;

            document.querySelectorAll('.tpl-btn').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.tpl === templateCode);
            });

            document.querySelectorAll('.mockup-view').forEach(view => {
                view.style.display = 'none';
            });
            const activeView = document.getElementById('view-' + templateCode);
            if (activeView) activeView.style.display = 'block';

            const selectEl = document.getElementById('profile_template_id');
            if (selectEl) {
                for (let i = 0; i < selectEl.options.length; i++) {
                    if (selectEl.options[i].dataset.code === templateCode) {
                        selectEl.selectedIndex = i;
                        break;
                    }
                }
            }
        };

        window.switchTemplateBySelect = function (selectEl) {
            const selectedOpt = selectEl.options[selectEl.selectedIndex];
            const code = selectedOpt ? selectedOpt.dataset.code : 'harvest-prestige';
            if (code) switchTemplate(code);
        };

        switchTemplate(currentTemplate);

        // 2. Real-time 2-Way Text Sync
        const syncInputs = document.querySelectorAll('.sync-input');
        syncInputs.forEach(input => {
            input.addEventListener('input', function () {
                const targetClass = this.dataset.syncClass;
                const val = this.value;
                document.querySelectorAll('.' + targetClass).forEach(el => {
                    el.textContent = val || (this.placeholder || '');
                });
            });
        });

        const syncClasses = [
            { cls: 'sync-name', inputId: 'business_name' },
            { cls: 'sync-headline', inputId: 'headline' },
            { cls: 'sync-desc', inputId: 'description' }
        ];

        syncClasses.forEach(item => {
            document.querySelectorAll('.' + item.cls + '.editable-live').forEach(mockupEl => {
                mockupEl.addEventListener('input', function () {
                    const text = this.textContent.trim();
                    const inputEl = document.getElementById(item.inputId);
                    if (inputEl) inputEl.value = text;

                    document.querySelectorAll('.' + item.cls).forEach(other => {
                        if (other !== mockupEl) other.textContent = text;
                    });
                });
            });
        });

        // 3. Drag & Drop & Direct Mockup Photo Editing
        function setupDropzone(dropzoneId, inputId, defaultId, previewId, previewImgId, nameId, isCover = false) {
            const dropzone = document.getElementById(dropzoneId);
            const input = document.getElementById(inputId);
            const defaultState = document.getElementById(defaultId);
            const previewState = document.getElementById(previewId);
            const previewImg = document.getElementById(previewImgId);
            const nameEl = document.getElementById(nameId);

            if (!input) return;

            if (dropzone) {
                ['dragenter', 'dragover'].forEach(name => {
                    dropzone.addEventListener(name, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        dropzone.classList.add('is-dragover');
                    });
                });

                ['dragleave', 'drop'].forEach(name => {
                    dropzone.addEventListener(name, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        dropzone.classList.remove('is-dragover');
                    });
                });

                dropzone.addEventListener('drop', (e) => {
                    const files = e.dataTransfer.files;
                    if (files && files.length > 0) {
                        input.files = files;
                        showPreview(files[0]);
                    }
                });
            }

            input.addEventListener('change', function () {
                if (this.files && this.files.length > 0) {
                    showPreview(this.files[0]);
                }
            });

            function showPreview(file) {
                if (!file.type.startsWith('image/')) {
                    alert('Pilih file gambar valid (JPG, PNG, WebP).');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    const url = e.target.result;
                    if (previewImg) previewImg.src = url;
                    if (nameEl) nameEl.textContent = file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
                    if (defaultState) defaultState.style.display = 'none';
                    if (previewState) previewState.style.display = 'flex';

                    if (isCover) {
                        document.querySelectorAll('.cover-sync-box').forEach(box => {
                            box.style.backgroundImage = `url('${url}')`;
                        });
                    } else {
                        document.querySelectorAll('.logo-sync-box').forEach(box => {
                            box.innerHTML = `<img src="${url}" alt="Logo" style="width:100%; height:100%; object-fit:cover;">`;
                        });
                    }
                };
                reader.readAsDataURL(file);
            }
        }

        setupDropzone('logoDropzone', 'logoInput', 'logoDefaultState', 'logoPreviewState', 'logoPreviewImg', 'logoFileName', false);
        setupDropzone('coverDropzone', 'coverInput', 'coverDefaultState', 'coverPreviewState', 'coverPreviewImg', 'coverFileName', true);

        // Bind Direct Mockup Drop targets
        function setupMockupDrop(targetClass, inputId) {
            const input = document.getElementById(inputId);
            if (!input) return;

            document.querySelectorAll('.' + targetClass).forEach(el => {
                el.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    el.style.outline = '3px solid #22c55e';
                });
                el.addEventListener('dragleave', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    el.style.outline = 'none';
                });
                el.addEventListener('drop', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    el.style.outline = 'none';
                    const files = e.dataTransfer.files;
                    if (files && files.length > 0) {
                        input.files = files;
                        input.dispatchEvent(new Event('change'));
                    }
                });
            });
        }

        setupMockupDrop('cover-sync-box', 'coverInput');
        setupMockupDrop('logo-sync-box', 'logoInput');

        // 4. Modal Helpers
        window.openAddProductModal = function () {
            document.getElementById('modalAddProduct').classList.add('is-open');
        };

        window.openEditProductModal = function (listing) {
            const form = document.getElementById('formEditProduct');
            form.action = '{{ route('admin.farmer-profiles.index') }}/{{ $profile->subdomain }}/listings/' + listing.id;

            document.getElementById('edit_commodity').value = listing.commodity || '';
            document.getElementById('edit_price').value = listing.price_per_unit || 0;
            document.getElementById('edit_unit').value = listing.unit || 'Kg';
            document.getElementById('edit_quantity').value = listing.quantity || 0;
            document.getElementById('edit_status').value = listing.status || 'published';
            document.getElementById('edit_image_url').value = listing.image_url || (listing.images && listing.images[0] ? listing.images[0].image_url : '');
            document.getElementById('edit_sales_link').value = listing.sales_link || '';
            document.getElementById('edit_description').value = listing.description || '';

            document.getElementById('modalEditProduct').classList.add('is-open');
        };

        window.closeModal = function (modalId) {
            document.getElementById(modalId).classList.remove('is-open');
        };

        // 5. Template Helpers
        window.applyTemplateHeadline = function () {
            const h = document.getElementById('headline');
            const name = document.getElementById('business_name').value || 'Usaha Tani';
            h.value = `Pasokan Beras & Hasil Panen Berkualitas dari Ladang ${name}`;
            h.dispatchEvent(new Event('input'));
        };

        window.applyTemplateDescription = function () {
            const d = document.getElementById('description');
            const name = document.getElementById('business_name').value || 'Usaha Tani';
            d.value = `${name} berfokus pada budidaya komoditas padi berkualitas unggul dengan memadukan ketelitian pascapanen dan integritas mutu. Siap bermitra melayani pasokan berkala maupun partai besar.`;
            d.dispatchEvent(new Event('input'));
        };
    });
</script>

@endsection
