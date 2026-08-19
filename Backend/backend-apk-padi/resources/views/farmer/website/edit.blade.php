@extends('layouts.farmer-panel')
@section('title', 'Visual Live Editor Website')

@section('content')

@php
    $baseDomain = config('domains.base', 'localhost');
    $scheme = app()->environment('production') ? 'https' : 'http';
    $currentLogoUrl = $profile->logo_path ? asset('storage/' . $profile->logo_path) : null;
    $currentCoverUrl = $profile->cover_image_path ? asset('storage/' . $profile->cover_image_path) : 'https://images.unsplash.com/photo-1586771107445-d3ca888129ff?auto=format&fit=crop&w=1200&q=80';
    $activeTemplateCode = $profile->template?->code ?? 'harvest-prestige';
    $farmerGallery = $gallery ?? collect();
@endphp

<style>
    .farmer-editor-layout {
        display: grid;
        grid-template-columns: 1.1fr 0.9fr;
        gap: 28px;
        align-items: start;
    }

    @media (max-width: 1200px) {
        .farmer-editor-layout {
            grid-template-columns: 1fr;
        }
    }

    /* Live Preview Sticky Sidebar */
    .farmer-live-preview {
        position: sticky;
        top: 24px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .farmer-live-header {
        background: #0f172a;
        color: #ffffff;
        padding: 12px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    /* Template Tabs */
    .tpl-tabs {
        display: flex;
        background: rgba(255, 255, 255, 0.1);
        padding: 3px;
        border-radius: 999px;
        gap: 2px;
    }

    .tpl-tab-btn {
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.7);
        border-radius: 999px;
        cursor: pointer;
        transition: all 0.2s ease;
        border: none;
        background: transparent;
    }

    .tpl-tab-btn:hover {
        color: #ffffff;
    }

    .tpl-tab-btn.active {
        background: #1b5e20;
        color: #bbf7d0;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }

    /* Editable Inline Fields */
    .editable-inline-text {
        cursor: text;
        position: relative;
        border-radius: 4px;
        transition: all 0.2s ease;
        padding: 2px 4px;
        margin: -2px -4px;
        outline: none;
    }

    .editable-inline-text:hover {
        background: rgba(34, 197, 94, 0.15);
        box-shadow: 0 0 0 1px rgba(34, 197, 94, 0.5);
    }

    .editable-inline-text:focus {
        background: #ffffff;
        color: #0f172a !important;
        box-shadow: 0 0 0 2px #1b5e20;
    }

    /* Direct Photo Edit Hover Overlays */
    .mockup-cover-interactive {
        position: relative;
        cursor: pointer;
        overflow: hidden;
    }

    .mockup-cover-interactive:hover::after {
        content: '📷 Klik / Drop Foto Cover';
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
        border-radius: inherit;
        text-align: center;
        padding: 10px;
    }

    .mockup-logo-interactive {
        position: relative;
        cursor: pointer;
        overflow: hidden;
    }

    .mockup-logo-interactive:hover::after {
        content: '📷';
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.75);
        color: #ffffff;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        border-radius: inherit;
    }

    /* Viewport Container */
    .mockup-viewport-farmer {
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
        padding: 20px 16px;
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
        font-size: 20px;
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
        margin-bottom: 12px;
        max-height: 50px;
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
        position: relative;
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
        min-height: 220px;
        background-size: cover;
        background-position: center;
        position: relative;
        padding: 24px 18px 36px;
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
        font-size: 22px;
        font-weight: 800;
        color: #ffffff;
        line-height: 1.1;
        margin-bottom: 6px;
    }

    .harvest-stats-strip {
        background: #ffffff;
        margin: -16px 14px 14px;
        border-radius: 10px;
        padding: 10px 14px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        position: relative;
        z-index: 3;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        text-align: center;
        border: 1px solid #e8ebe8;
    }

    .harvest-stat-val {
        font-size: 14px;
        font-weight: 800;
        color: #123b2b;
    }

    .harvest-stat-lbl {
        font-size: 9px;
        color: #7d857f;
        text-transform: uppercase;
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
        padding: 16px 12px;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        min-height: 170px;
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

    /* Modern Drag & Drop Zone */
    .dropzone-box {
        position: relative;
        border: 2px dashed #cbd5e1;
        border-radius: 14px;
        padding: 20px;
        background: #f8fafc;
        text-align: center;
        cursor: pointer;
        transition: all 0.25s ease;
        overflow: hidden;
    }

    .dropzone-box.is-dragover {
        border-color: #1b5e20;
        background: #f0fdf4;
        transform: scale(1.01);
        box-shadow: 0 0 0 4px rgba(27, 94, 32, 0.1);
    }

    .dropzone-icon-btn {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: #e2e8f0;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        margin-bottom: 8px;
        transition: all 0.2s ease;
    }

    .farmer-score-panel {
        padding: 16px 20px;
        background: #f0fdf4;
        border-top: 1px solid #dcfce7;
    }

    .score-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11.5px;
        color: #64748b;
    }

    .score-item.done {
        color: #1b5e20;
        font-weight: 600;
    }

    .copy-pill-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        color: #1b5e20;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        padding: 3px 8px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .copy-pill-btn:hover {
        background: #dcfce7;
    }

    /* Gallery Grid in Farmer Panel */
    .farmer-gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 12px;
        margin-top: 14px;
    }

    .farmer-gallery-card {
        position: relative;
        border-radius: 10px;
        overflow: hidden;
        aspect-ratio: 4/3;
        border: 1px solid #e2e8f0;
        background: #f1f5f9;
    }

    .farmer-gallery-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .farmer-gallery-del {
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
    }
</style>

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#0f172a]">Visual Live Editor Website</h1>
            <p class="text-slate-500 text-sm mt-1">
                Ganti template website secara dinamis, edit teks dan foto langsung di web mockup, serta kelola galeri dokumentasi sawah.
            </p>
        </div>

        @if ($profile->subdomain)
            <a href="{{ $profile->publicUrl() }}" target="_blank"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-sm font-bold text-[#0f172a] rounded-xl hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-arrow-up-right-from-square text-[#1b5e20]"></i> Buka Website Publik
            </a>
        @endif
    </div>

    {{-- Flash Status Alert --}}
    @if (session('status'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <i class="fas fa-check-circle text-green-600"></i> {{ session('status') }}
        </div>
    @endif

    <div class="farmer-editor-layout">

        {{-- LEFT COLUMN: FORM CONTROLS --}}
        <div class="space-y-6">
            <form method="POST" action="{{ route('farmer.website.update') }}" enctype="multipart/form-data" class="space-y-6" id="farmerProfileForm">
                @csrf

                {{-- Section 1: Basic Info --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                    <h2 class="font-bold text-[#0f172a] mb-5 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-[#1b5e20] text-white text-xs flex items-center justify-center font-bold">1</span>
                        Informasi Utama Usaha Tani
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-[#0f172a] mb-1" for="business_name">
                                Nama Usaha <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="business_name" id="business_name"
                                value="{{ old('business_name', $profile->business_name) }}"
                                required maxlength="150"
                                class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20] focus:border-transparent transition sync-farmer-input"
                                data-sync-class="sync-name"
                                placeholder="Contoh: UD Tani Maju">
                            @error('business_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-sm font-medium text-[#0f172a]" for="headline">
                                    Tagline / Slogan
                                </label>
                                <button type="button" class="copy-pill-btn" onclick="applyFarmerHeadline()">
                                    <i class="fas fa-magic"></i> Rekomendasi Slogan
                                </button>
                            </div>
                            <input type="text" name="headline" id="headline"
                                value="{{ old('headline', $profile->headline) }}"
                                maxlength="255"
                                class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20] focus:border-transparent transition sync-farmer-input"
                                data-sync-class="sync-headline"
                                placeholder="Contoh: Pasokan Beras Premium & Gabah Kering Terpercaya">
                        </div>

                        <div class="md:col-span-2">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-sm font-medium text-[#0f172a]" for="description">
                                    Deskripsi Usaha
                                </label>
                                <button type="button" class="copy-pill-btn" onclick="applyFarmerDescription()">
                                    <i class="fas fa-magic"></i> Rekomendasi Narasi
                                </button>
                            </div>
                            <textarea name="description" id="description" rows="4" maxlength="3000"
                                class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20] focus:border-transparent transition resize-none sync-farmer-input"
                                data-sync-class="sync-desc"
                                placeholder="Ceritakan varietas beras unggulan, komitmen mutu, dan riwayat usaha Anda...">{{ old('description', $profile->description) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Section 2: Drag & Drop Media --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                    <h2 class="font-bold text-[#0f172a] mb-5 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-[#1b5e20] text-white text-xs flex items-center justify-center font-bold">2</span>
                        Foto Logo & Foto Cover Banner
                    </h2>
                    <p class="text-xs text-slate-500 mb-4 ml-8">Bisa klik kotak di bawah atau klik langsung foto di layar mockup samping!</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Logo Dropzone --}}
                        <div>
                            <label class="block text-sm font-medium text-[#0f172a] mb-2">Logo Usaha (Rasio 1:1)</label>
                            <div class="dropzone-box" id="farmerLogoDropzone" onclick="document.getElementById('farmerLogoInput').click()">
                                <input type="file" name="logo" id="farmerLogoInput" accept="image/*" style="display:none;">

                                <div id="farmerLogoDefault" style="{{ $currentLogoUrl ? 'display:none;' : '' }}">
                                    <div class="dropzone-icon-btn"><i class="fas fa-image"></i></div>
                                    <div class="text-sm font-bold text-[#0f172a] mb-0.5">Drag & drop logo di sini</div>
                                    <div class="text-xs text-slate-400">atau klik untuk browse file</div>
                                </div>

                                <div id="farmerLogoPreview" class="flex items-center gap-3 text-left" style="{{ $currentLogoUrl ? '' : 'display:none;' }}">
                                    <img id="farmerLogoImg" src="{{ $currentLogoUrl ?: '' }}" alt="Logo" class="w-12 h-12 rounded-lg object-cover border border-slate-200">
                                    <div>
                                        <div class="text-xs font-bold text-[#0f172a]" id="farmerLogoName">Logo Aktif</div>
                                        <div class="text-[11px] text-[#1b5e20]">Klik / drop untuk ganti</div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-slate-400 mt-2">Format: JPG, PNG, WebP. Maks 2MB.</p>
                        </div>

                        {{-- Cover Dropzone --}}
                        <div>
                            <label class="block text-sm font-medium text-[#0f172a] mb-2">Foto Cover Banner (Rasio 16:9)</label>
                            <div class="dropzone-box" id="farmerCoverDropzone" onclick="document.getElementById('farmerCoverInput').click()">
                                <input type="file" name="cover_image" id="farmerCoverInput" accept="image/*" style="display:none;">

                                <div id="farmerCoverDefault" style="{{ $profile->cover_image_path ? 'display:none;' : '' }}">
                                    <div class="dropzone-icon-btn"><i class="fas fa-cloud-arrow-up"></i></div>
                                    <div class="text-sm font-bold text-[#0f172a] mb-0.5">Drag & drop cover di sini</div>
                                    <div class="text-xs text-slate-400">atau klik untuk browse file</div>
                                </div>

                                <div id="farmerCoverPreview" class="flex items-center gap-3 text-left" style="{{ $profile->cover_image_path ? '' : 'display:none;' }}">
                                    <img id="farmerCoverImg" src="{{ $currentCoverUrl }}" alt="Cover" class="w-16 h-12 rounded-lg object-cover border border-slate-200">
                                    <div>
                                        <div class="text-xs font-bold text-[#0f172a]" id="farmerCoverName">Cover Aktif</div>
                                        <div class="text-[11px] text-[#1b5e20]">Klik / drop untuk ganti</div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-slate-400 mt-2">Format: JPG, PNG, WebP. Maks 4MB.</p>
                        </div>
                    </div>
                </div>

                {{-- Section 3: Contact --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                    <h2 class="font-bold text-[#0f172a] mb-1 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-[#1b5e20] text-white text-xs flex items-center justify-center font-bold">3</span>
                        Kontak Publik
                    </h2>
                    <p class="text-xs text-slate-500 mb-5 ml-8">Kontak yang memudahkan calon mitra menghubungi usaha Anda.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-[#0f172a] mb-1" for="whatsapp">WhatsApp</label>
                            <input type="text" name="whatsapp" id="whatsapp"
                                value="{{ old('whatsapp', $profile->whatsapp) }}"
                                class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20] focus:border-transparent transition"
                                placeholder="08123456789">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-[#0f172a] mb-1" for="public_phone">Telepon Publik</label>
                            <input type="text" name="public_phone" id="public_phone"
                                value="{{ old('public_phone', $profile->public_phone) }}"
                                class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20] focus:border-transparent transition"
                                placeholder="022-xxxxxxx">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-[#0f172a] mb-1" for="public_email">Email Publik</label>
                            <input type="email" name="public_email" id="public_email"
                                value="{{ old('public_email', $profile->public_email) }}"
                                class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20] focus:border-transparent transition"
                                placeholder="kontak@usahatani.com">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-[#0f172a] mb-1" for="public_address">Alamat Umum</label>
                            <input type="text" name="public_address" id="public_address"
                                value="{{ old('public_address', $profile->public_address) }}"
                                class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20] focus:border-transparent transition"
                                placeholder="Kec. Ciawi, Kab. Bogor">
                        </div>
                    </div>
                </div>

                {{-- Save button --}}
                <div class="flex items-center justify-between pt-2">
                    <a href="{{ route('farmer.website.index') }}" class="text-sm text-slate-500 hover:text-[#0f172a] transition-colors">
                        Kembali ke Dashboard
                    </a>
                    <button type="submit"
                        class="bg-[#1b5e20] hover:bg-[#145218] text-white font-bold py-3.5 px-8 rounded-xl transition-all shadow-sm hover:shadow-md active:scale-[0.99]">
                        Simpan Perubahan
                    </button>
                </div>
            </form>

            {{-- Section 4: Galeri Dokumentasi Sawah & Panen --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                <h2 class="font-bold text-[#0f172a] mb-1 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#1b5e20] text-white text-xs flex items-center justify-center font-bold">4</span>
                    Galeri & Dokumentasi Kegiatan Tani
                </h2>
                <p class="text-xs text-slate-500 mb-5 ml-8">Unggah foto-foto aktivitas sawah, bibit, pemeliharaan, atau panen padi Anda.</p>

                <form method="POST" action="{{ route('farmer.website.gallery.store') }}" enctype="multipart/form-data" class="bg-slate-50 p-4 rounded-xl border border-slate-200 mb-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#0f172a] mb-1">Pilih Foto (Maks 5MB)</label>
                            <input type="file" name="image" required accept="image/*" class="w-full text-xs text-slate-600 bg-white border border-slate-200 rounded-lg p-2">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#0f172a] mb-1">Caption / Keterangan Foto</label>
                            <input type="text" name="caption" placeholder="Contoh: Pemupukan Organik Pekan Ke-4" class="w-full text-xs border border-slate-200 rounded-lg p-2.5">
                        </div>
                    </div>
                    <div class="text-right mt-3">
                        <button type="submit" class="bg-[#0f172a] hover:bg-slate-800 text-white text-xs font-bold px-4 py-2 rounded-lg transition">
                            <i class="fas fa-cloud-arrow-up mr-1"></i> Upload Foto Galeri
                        </button>
                    </div>
                </form>

                @if ($farmerGallery->isNotEmpty())
                    <div class="farmer-gallery-grid">
                        @foreach ($farmerGallery as $item)
                            <div class="farmer-gallery-card">
                                <img src="{{ $item->imageUrl() }}" alt="{{ $item->caption ?? 'Foto Galeri' }}">
                                <form method="POST" action="{{ route('farmer.website.gallery.destroy', $item) }}" onsubmit="return confirm('Hapus foto ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="farmer-gallery-del" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Section 5: Subdomain --}}
            <div id="subdomain-section" class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                <h2 class="font-bold text-[#0f172a] mb-1 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#1b5e20] text-white text-xs flex items-center justify-center font-bold">5</span>
                    Alamat Subdomain Website
                </h2>
                <p class="text-xs text-slate-500 mb-5 ml-8">Pilih nama unik untuk alamat publik website Anda.</p>

                <div x-data="{
                    subdomain: '{{ $profile->subdomain ?? '' }}',
                    status: null,
                    message: '',
                    checking: false,
                    checkUrl: '{{ route('farmer.website.subdomain.check') }}',
                    baseDomain: '{{ $baseDomain }}',
                    scheme: '{{ $scheme }}',
                    debounceTimer: null,
                    check() {
                        clearTimeout(this.debounceTimer);
                        if (this.subdomain.length < 3) { this.status = null; return; }
                        this.debounceTimer = setTimeout(() => {
                            this.checking = true;
                            fetch(this.checkUrl + '?subdomain=' + encodeURIComponent(this.subdomain))
                                .then(r => r.json())
                                .then(data => {
                                    this.status = data.available ? 'available' : 'taken';
                                    this.message = data.available ? (this.scheme + '://' + this.subdomain + '.' + this.baseDomain) : (data.message || 'Tidak tersedia');
                                    this.checking = false;
                                });
                        }, 400);
                    }
                }">
                    <form method="POST" action="{{ route('farmer.website.subdomain.update') }}">
                        @csrf
                        <div class="flex items-stretch gap-0 max-w-lg">
                            <input type="text" name="subdomain" id="subdomain_input"
                                x-model="subdomain"
                                @input="check()"
                                maxlength="40"
                                class="flex-1 border border-r-0 border-gray-200 rounded-l-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20] focus:border-transparent transition"
                                placeholder="namaanda">
                            <span class="inline-flex items-center px-4 text-sm text-slate-500 bg-gray-50 border border-l-0 border-gray-200 rounded-r-lg whitespace-nowrap">
                                .{{ $baseDomain }}
                            </span>
                        </div>

                        <div class="mt-2 h-5">
                            <template x-if="checking">
                                <p class="text-xs text-slate-400">Memeriksa ketersediaan...</p>
                            </template>
                            <template x-if="!checking && status === 'available'">
                                <p class="text-xs text-green-600 font-medium" x-text="message"></p>
                            </template>
                            <template x-if="!checking && status === 'taken'">
                                <p class="text-xs text-red-500" x-text="message"></p>
                            </template>
                        </div>

                        <button type="submit"
                            class="bg-[#0f172a] text-white text-sm font-bold px-5 py-2.5 rounded-lg hover:bg-[#1e293b] transition-all mt-2">
                            Simpan Subdomain
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: DYNAMIC MULTI-TEMPLATE LIVE MOCKUP WITH DIRECT PHOTO CLICK & DROP --}}
        <div>
            <div class="farmer-live-preview">
                
                {{-- Header with Template Tabs --}}
                <div class="farmer-live-header">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-desktop text-white"></i>
                        <span>Live Mockup (Interactive)</span>
                    </div>

                    <div class="tpl-tabs" role="tablist">
                        <button type="button" class="tpl-tab-btn" data-tpl="agri-modern" onclick="switchFarmerTemplate('agri-modern')">
                            Agri Modern
                        </button>
                        <button type="button" class="tpl-tab-btn" data-tpl="harvest-prestige" onclick="switchFarmerTemplate('harvest-prestige')">
                            Harvest Prestige
                        </button>
                        <button type="button" class="tpl-tab-btn" data-tpl="marketplace-pro" onclick="switchFarmerTemplate('marketplace-pro')">
                            Marketplace Pro
                        </button>
                    </div>
                </div>

                {{-- Viewport Container --}}
                <div class="mockup-viewport-farmer">

                    {{-- VIEW 1: AGRI MODERN --}}
                    <div class="mockup-view mockup-agri-modern" id="fview-agri-modern" style="display:none;">
                        <div class="agri-nav">
                            <div class="agri-brand">
                                <div class="agri-logo logo-sync-target mockup-logo-interactive" title="Klik / Drop untuk Ganti Logo" onclick="document.getElementById('farmerLogoInput').click()">
                                    @if ($currentLogoUrl)
                                        <img src="{{ $currentLogoUrl }}" alt="Logo" style="width:100%; height:100%; object-fit:cover;">
                                    @else
                                        <i class="fas fa-seedling"></i>
                                    @endif
                                </div>
                                <span class="sync-name editable-inline-text" contenteditable="true" style="font-size:12.5px; font-weight:800; color:#0B281B;" title="Klik untuk edit teks">
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
                                <h2 class="agri-title sync-name editable-inline-text" contenteditable="true" title="Ketik untuk edit nama">
                                    {{ $profile->business_name }}
                                </h2>
                                <div class="agri-headline sync-headline editable-inline-text" contenteditable="true" title="Ketik untuk edit slogan">
                                    {{ $profile->headline ?: 'Pasokan Hasil Panen Padi Berkualitas Langsung dari Petani' }}
                                </div>
                                <div class="agri-desc sync-desc editable-inline-text" contenteditable="true" title="Ketik untuk edit deskripsi">
                                    {{ $profile->description ?: 'Menyediakan pasokan komoditas pertanian terbaik dengan standar kualitas mutu terjamin.' }}
                                </div>
                                <div style="display:inline-flex; align-items:center; gap:6px; background:#B7FF00; color:#0B281B; padding:5px 12px; border-radius:999px; font-size:10px; font-weight:800;">
                                    Lihat Produk <i class="fas fa-arrow-right"></i>
                                </div>
                            </div>

                            <div class="agri-media cover-sync-target mockup-cover-interactive" style="background-image: url('{{ $currentCoverUrl }}');" title="Klik / Drop untuk Ganti Foto Banner" onclick="document.getElementById('farmerCoverInput').click()"></div>
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
                    <div class="mockup-view mockup-harvest-prestige" id="fview-harvest-prestige" style="display:none;">
                        <div class="harvest-hero cover-sync-target mockup-cover-interactive" style="background-image: url('{{ $currentCoverUrl }}');" title="Klik / Drop untuk Ganti Foto Banner" onclick="document.getElementById('farmerCoverInput').click()">
                            <div class="harvest-hero-content">
                                <div class="harvest-badge"><i class="fas fa-shield-halved"></i> Verified Agriculture Prestige</div>
                                <h2 class="harvest-title sync-name editable-inline-text" contenteditable="true" title="Ketik untuk edit nama">
                                    {{ $profile->business_name }}
                                </h2>
                                <div class="harvest-headline sync-headline editable-inline-text" contenteditable="true" title="Ketik untuk edit slogan">
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
                                <div class="harvest-stat-lbl">Luas Lahan</div>
                            </div>
                            <div>
                                <div class="harvest-stat-val">8 Musim</div>
                                <div class="harvest-stat-lbl">Terdokumentasi</div>
                            </div>
                            <div>
                                <div class="harvest-stat-val">Grade A</div>
                                <div class="harvest-stat-lbl">Kualitas Panen</div>
                            </div>
                        </div>

                        <div style="padding:0 14px 14px;">
                            <div class="sync-desc editable-inline-text" contenteditable="true" style="font-size:11px; color:#525a54; line-height:1.5;" title="Ketik untuk edit deskripsi">
                                {{ $profile->description ?: 'Beras dan gabah berkualitas tinggi yang diproduksi dengan integritas mutu dan transparansi catatan panen.' }}
                            </div>
                        </div>
                    </div>

                    {{-- VIEW 3: MARKETPLACE PRO --}}
                    <div class="mockup-view mockup-marketplace-pro" id="fview-marketplace-pro" style="display:none;">
                        <div class="market-shell">
                            <div style="padding:8px 12px; border-bottom:1px solid #e6eadf; display:flex; align-items:center; justify-content:space-between;">
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <div class="logo-sync-target mockup-logo-interactive" style="width:24px; height:24px; border-radius:6px; background:#173f18; color:#78c800; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:800; overflow:hidden;" title="Klik / Drop untuk Ganti Logo" onclick="document.getElementById('farmerLogoInput').click()">
                                        @if ($currentLogoUrl)
                                            <img src="{{ $currentLogoUrl }}" alt="Logo" style="width:100%; height:100%; object-fit:cover;">
                                        @else
                                            <i class="fas fa-seedling"></i>
                                        @endif
                                    </div>
                                    <span class="sync-name editable-inline-text" contenteditable="true" style="font-size:11.5px; font-weight:800; color:#173f18;" title="Klik untuk edit teks">
                                        {{ $profile->business_name }}
                                    </span>
                                </div>
                                <span style="font-size:9px; font-weight:700; color:#173f18; background:#eaf4dd; padding:2px 6px; border-radius:999px;">
                                    GreenMarket Pro
                                </span>
                            </div>

                            <div class="market-hero">
                                <div class="market-hero-main cover-sync-target mockup-cover-interactive" style="background-image: url('{{ $currentCoverUrl }}');" title="Klik / Drop untuk Ganti Foto Banner" onclick="document.getElementById('farmerCoverInput').click()">
                                    <div class="market-content">
                                        <div style="font-size:8px; font-weight:800; color:#78c800; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:2px;">
                                            ✦ P.A.D.I. Verified
                                        </div>
                                        <h3 class="market-title sync-name editable-inline-text" contenteditable="true" title="Ketik untuk edit nama">
                                            {{ $profile->business_name }}
                                        </h3>
                                        <div class="sync-headline editable-inline-text" contenteditable="true" style="font-size:10px; color:rgba(255,255,255,0.85);" title="Ketik untuk edit slogan">
                                            {{ $profile->headline ?: 'Etalase Komoditas Hasil Panen' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="market-side cover-sync-target mockup-cover-interactive" style="background-image: url('{{ $currentCoverUrl }}');" title="Klik / Drop untuk Ganti Foto" onclick="document.getElementById('farmerCoverInput').click()">
                                    <div style="position:absolute; bottom:6px; left:6px; right:6px; background:rgba(23,63,24,0.85); color:#fff; font-size:8px; font-weight:700; padding:4px; border-radius:6px; text-align:center;">
                                        Dokumentasi
                                    </div>
                                </div>
                            </div>

                            <div style="padding:10px 12px; border-top:1px solid #f0f3ea;">
                                <div class="sync-desc editable-inline-text" contenteditable="true" style="font-size:10.5px; color:#555d52; line-height:1.45;" title="Ketik untuk edit deskripsi">
                                    {{ $profile->description ?: 'Hasil panen siap kirim dengan transparansi mutu langsung dari lahan petani.' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-3 space-y-1">
                        <p class="text-xs font-bold text-[#1b5e20]">
                            <i class="fas fa-camera"></i> Tips Foto: Klik / Drag & Drop gambar langsung ke Foto Cover atau Logo di atas!
                        </p>
                        <p class="text-xs text-slate-500">
                            <i class="fas fa-pen-to-square"></i> Tips Teks: Klik langsung kalimat mana pun untuk mengedit teks secara instan.
                        </p>
                    </div>
                </div>

                {{-- Score Meter --}}
                <div class="farmer-score-panel">
                    <div class="flex items-center justify-between mb-2 text-xs font-bold text-[#1b5e20]">
                        <span>Kelengkapan Profil Website</span>
                        <span id="farmerScorePercent">0%</span>
                    </div>
                    <div class="h-1.5 bg-[#dcfce7] rounded-full overflow-hidden mb-3">
                        <div class="h-full bg-[#1b5e20] rounded-full transition-all duration-300" id="farmerScoreBar" style="width: 0%;"></div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="score-item" id="fCheckName"><i class="far fa-circle"></i> Nama Usaha</div>
                        <div class="score-item" id="fCheckTagline"><i class="far fa-circle"></i> Slogan / Tagline</div>
                        <div class="score-item" id="fCheckDesc"><i class="far fa-circle"></i> Deskripsi Narasi</div>
                        <div class="score-item" id="fCheckLogo"><i class="far fa-circle"></i> Logo Usaha</div>
                        <div class="score-item" id="fCheckCover"><i class="far fa-circle"></i> Foto Cover</div>
                        <div class="score-item" id="fCheckWa"><i class="far fa-circle"></i> WhatsApp</div>
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>

{{-- Scripts --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Template Switcher
        let activeTpl = '{{ $activeTemplateCode }}';

        window.switchFarmerTemplate = function (tplCode) {
            activeTpl = tplCode;

            document.querySelectorAll('.tpl-tab-btn').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.tpl === tplCode);
            });

            document.querySelectorAll('.mockup-view').forEach(view => {
                view.style.display = 'none';
            });
            const activeView = document.getElementById('fview-' + tplCode);
            if (activeView) activeView.style.display = 'block';
        };

        switchFarmerTemplate(activeTpl);

        // 2. Real-time 2-Way Sync
        const syncInputs = document.querySelectorAll('.sync-farmer-input');
        syncInputs.forEach(input => {
            input.addEventListener('input', function () {
                const targetCls = this.dataset.syncClass;
                const val = this.value;
                document.querySelectorAll('.' + targetCls).forEach(el => {
                    el.textContent = val || (this.placeholder || '');
                });
                calcFarmerScore();
            });
        });

        const syncFieldClasses = [
            { cls: 'sync-name', inputId: 'business_name' },
            { cls: 'sync-headline', inputId: 'headline' },
            { cls: 'sync-desc', inputId: 'description' }
        ];

        syncFieldClasses.forEach(item => {
            document.querySelectorAll('.' + item.cls + '.editable-inline-text').forEach(mockupEl => {
                mockupEl.addEventListener('input', function () {
                    const text = this.textContent.trim();
                    const inputEl = document.getElementById(item.inputId);
                    if (inputEl) inputEl.value = text;

                    document.querySelectorAll('.' + item.cls).forEach(other => {
                        if (other !== mockupEl) other.textContent = text;
                    });
                    calcFarmerScore();
                });
            });
        });

        // 3. Drag & Drop Upload Handlers
        function setupFarmerDropzone(dropzoneId, inputId, defaultId, previewId, previewImgId, nameId, isCover = false) {
            const dropzone = document.getElementById(dropzoneId);
            const input = document.getElementById(inputId);
            const defaultBox = document.getElementById(defaultId);
            const previewBox = document.getElementById(previewId);
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
                    alert('Mohon pilih file gambar (JPG, PNG, atau WebP).');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    const url = e.target.result;
                    if (previewImg) previewImg.src = url;
                    if (nameEl) nameEl.textContent = file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
                    if (defaultBox) defaultBox.style.display = 'none';
                    if (previewBox) previewBox.style.display = 'flex';

                    if (isCover) {
                        document.querySelectorAll('.cover-sync-target').forEach(el => {
                            el.style.backgroundImage = `url('${url}')`;
                        });
                    } else {
                        document.querySelectorAll('.logo-sync-target').forEach(el => {
                            el.innerHTML = `<img src="${url}" alt="Logo" style="width:100%; height:100%; object-fit:cover;">`;
                        });
                    }
                    calcFarmerScore();
                };
                reader.readAsDataURL(file);
            }
        }

        setupFarmerDropzone('farmerLogoDropzone', 'farmerLogoInput', 'farmerLogoDefault', 'farmerLogoPreview', 'farmerLogoImg', 'farmerLogoName', false);
        setupFarmerDropzone('farmerCoverDropzone', 'farmerCoverInput', 'farmerCoverDefault', 'farmerCoverPreview', 'farmerCoverImg', 'farmerCoverName', true);

        // Bind Direct Mockup Drop targets
        function setupFarmerMockupDrop(targetClass, inputId) {
            const input = document.getElementById(inputId);
            if (!input) return;

            document.querySelectorAll('.' + targetClass).forEach(el => {
                el.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    el.style.outline = '3px solid #166534';
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

        setupFarmerMockupDrop('cover-sync-target', 'farmerCoverInput');
        setupFarmerMockupDrop('logo-sync-target', 'farmerLogoInput');

        // 4. Score Calculator
        function calcFarmerScore() {
            let score = 0;
            const hasName = (document.getElementById('business_name')?.value || '').trim().length > 0;
            const hasTagline = (document.getElementById('headline')?.value || '').trim().length > 0;
            const hasDesc = (document.getElementById('description')?.value || '').trim().length > 20;
            const hasLogo = (document.getElementById('farmerLogoInput')?.files?.length > 0) || {{ $currentLogoUrl ? 'true' : 'false' }};
            const hasCover = (document.getElementById('farmerCoverInput')?.files?.length > 0) || {{ $profile->cover_image_path ? 'true' : 'false' }};
            const hasWa = (document.getElementById('whatsapp')?.value || '').trim().length > 0;

            const items = [
                { id: 'fCheckName', valid: hasName, pts: 20 },
                { id: 'fCheckTagline', valid: hasTagline, pts: 15 },
                { id: 'fCheckDesc', valid: hasDesc, pts: 20 },
                { id: 'fCheckLogo', valid: hasLogo, pts: 15 },
                { id: 'fCheckCover', valid: hasCover, pts: 15 },
                { id: 'fCheckWa', valid: hasWa, pts: 15 }
            ];

            items.forEach(it => {
                const el = document.getElementById(it.id);
                if (it.valid) {
                    score += it.pts;
                    if (el) {
                        el.classList.add('done');
                        el.querySelector('i').className = 'fas fa-check-circle text-[#1b5e20]';
                    }
                } else {
                    if (el) {
                        el.classList.remove('done');
                        el.querySelector('i').className = 'far fa-circle text-slate-400';
                    }
                }
            });

            const bar = document.getElementById('farmerScoreBar');
            const pct = document.getElementById('farmerScorePercent');
            if (bar) bar.style.width = score + '%';
            if (pct) pct.textContent = score + '%';
        }

        calcFarmerScore();

        // 5. Template Helpers
        window.applyFarmerHeadline = function () {
            const h = document.getElementById('headline');
            const name = document.getElementById('business_name').value || 'Usaha Tani';
            h.value = `Pasokan Beras Padi Berkualitas & Alami dari Ladang ${name}`;
            h.dispatchEvent(new Event('input'));
        };

        window.applyFarmerDescription = function () {
            const d = document.getElementById('description');
            const name = document.getElementById('business_name').value || 'Usaha Tani';
            d.value = `${name} berkomitmen menghasilkan pasokan gabah dan beras berkualitas terbaik dengan proses tanam alami dan seleksi pascapanen yang ketat. Siap bermitra melayani pasokan berkala maupun partai besar.`;
            d.dispatchEvent(new Event('input'));
        };
    });
</script>

@endsection
