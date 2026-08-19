@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/soil.css') }}">

<div class="soil-page">
    {{-- Breadcrumb --}}
    <nav class="soil-breadcrumb" aria-label="Breadcrumb">
        <span>Admin</span>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <a href="{{ route('admin.soil.index') }}" style="color:#64748b; text-decoration:none;">Kualitas Tanah</a>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="soil-breadcrumb-current">Tambah Uji Sampel Tanah</span>
    </nav>

    {{-- Page Header --}}
    <div class="soil-header">
        <div class="soil-header-content">
            <h1 class="soil-title">Uji Kualitas Tanah &amp; Parameter Irigasi</h1>
            <p class="soil-description">Input data sampel pengujian laboratorium atau sinkronisasi dengan sensor agroklimat satelit untuk kalkulasi hara, status keasaman (pH), dan jadwal pengairan padi.</p>
        </div>

        <div class="soil-header-actions">
            <a href="{{ route('admin.soil.index') }}" class="btn-soil-action">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Daftar</span>
            </a>
        </div>
    </div>

    {{-- Validation Errors Alert --}}
    @if ($errors->any())
        <div class="soil-alert soil-alert-danger" id="alert-validation" style="margin-bottom: 20px;">
            <div>
                <strong>Validasi Formulir Gagal:</strong>
                <ul style="margin: 6px 0 0 0; padding-left: 18px; font-size: 13px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="soil-alert-close" onclick="document.getElementById('alert-validation').remove()">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Dual Input Mode Bar --}}
    <div class="soil-mode-bar">
        <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
            <span style="font-weight: 700; font-size: 13px; color: #0f172a;">Metode Pengisian Data:</span>
            <div class="soil-mode-toggle">
                <button type="button" id="btn-mode-manual" class="soil-mode-btn is-active" onclick="setFormMode('manual')">
                    Input Manual Lab
                </button>
                <button type="button" id="btn-mode-api" class="soil-mode-btn" onclick="setFormMode('api')">
                    Otomatis AgroMonitoring API
                </button>
            </div>
        </div>

        <button type="button" id="btn-fetch-api" class="btn-soil-action" style="background: #f0fdf4; border-color: #bbf7d0; color: #166534;" onclick="fetchAgroMonitoringData()">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span id="btn-fetch-text">Tarik Data Otomatis AgroMonitoring API</span>
        </button>
    </div>

    {{-- Feedback Banner --}}
    <div id="api-feedback-banner" class="soil-alert soil-alert-success" style="display: none; margin-bottom: 20px;">
        <span id="api-feedback-text">Data kelembaban &amp; suhu tanah AgroMonitoring berhasil ditarik dan disematkan ke formulir.</span>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('admin.soil.store') }}" id="soil-form">
        @csrf

        {{-- Card 1: Identitas Lahan & Sampel --}}
        <section class="soil-form-card">
            <div class="soil-form-card__header">
                <div>
                    <h2 class="soil-form-card__title">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="18" height="18" style="color:#166534;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2h1.5a2.5 2.5 0 002.5-2.5V7a2 2 0 00-2-2h-2c-.6 0-1.1-.3-1.4-.8l-1.2-2M15 21a6 6 0 100-12 6 6 0 000 12z"/>
                        </svg>
                        Identitas Lahan &amp; Informasi Pengujian
                    </h2>
                    <p class="soil-form-card__subtitle">Tentukan petak sawah yang diuji serta klasifikasi tekstur tanah</p>
                </div>
            </div>

            <div class="soil-form-card__body">
                <div class="soil-form-grid soil-form-grid--2" style="margin-bottom: 18px;">
                    <div class="soil-form-group">
                        <label class="soil-form-label" for="farm_id">
                            <span>Lahan Pertanian <span class="req">*</span></span>
                        </label>
                        <select name="farm_id" id="farm_id" class="soil-select" onchange="autoFetchIfApiMode()" required>
                            <option value="">-- Pilih Lahan Pertanian --</option>
                            @foreach ($farms as $farm)
                                <option value="{{ $farm->id }}" data-lat="{{ $farm->latitude }}" data-lng="{{ $farm->longitude }}" @selected(old('farm_id', request('farm_id')) == $farm->id)>
                                    {{ $farm->name }} &mdash; Petani: {{ $farm->farmer?->name ?? 'Tanpa Petani' }} ({{ $farm->area_ha ?? 0 }} Ha)
                                </option>
                            @endforeach
                        </select>
                        <span class="soil-field-hint">Petak sawah tempat pengambilan sampel tanah laboratorium</span>
                    </div>

                    <div class="soil-form-group">
                        <label class="soil-form-label" for="soil_type">
                            <span>Jenis / Tekstur Tanah <span class="req">*</span></span>
                        </label>
                        <select name="soil_type" id="soil_type" class="soil-select" required>
                            <option value="loam" @selected(old('soil_type') === 'loam')>Lempung Berpasir / Loam (Ideal Padi Sawah)</option>
                            <option value="alluvial" @selected(old('soil_type') === 'alluvial')>Aluvial (Endapan Sungai / Dataran Rendah)</option>
                            <option value="clay" @selected(old('soil_type') === 'clay')>Liat / Clay (Kapasitas Retensi Air Tinggi)</option>
                            <option value="sandy_loam" @selected(old('soil_type') === 'sandy_loam')>Pasir Berlempung / Sandy Loam</option>
                            <option value="latosol" @selected(old('soil_type') === 'latosol')>Latosol / Merah Kuning</option>
                            <option value="peat" @selected(old('soil_type') === 'peat')>Gambut / Peat (Lahan Rawa)</option>
                        </select>
                        <span class="soil-field-hint">Tekstur tanah memengaruhi permeabilitas &amp; retensi hara</span>
                    </div>
                </div>

                <div class="soil-form-grid soil-form-grid--2">
                    <div class="soil-form-group">
                        <label class="soil-form-label" for="sample_code">
                            <span>Kode Sampel Laboratorium <span class="opt-hint">(Opsional)</span></span>
                        </label>
                        <input type="text" name="sample_code" id="sample_code" class="soil-input" placeholder="SOIL-2026-XXXX (Otomatis dibuat jika kosong)" value="{{ old('sample_code') }}">
                        <span class="soil-field-hint">Nomor registrasi unik dari dokumen hasil uji lab</span>
                    </div>

                    <div class="soil-form-group">
                        <label class="soil-form-label" for="tested_at">
                            <span>Waktu Pengujian Sampel <span class="req">*</span></span>
                        </label>
                        <input type="datetime-local" name="tested_at" id="tested_at" class="soil-input" value="{{ old('tested_at', date('Y-m-d\TH:i')) }}" required>
                        <span class="soil-field-hint">Tanggal &amp; jam pelaksanaan analisa sampel</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Card 2: Parameter Kimiawi & Unsur Hara Utama (NPK & pH) --}}
        <section class="soil-form-card">
            <div class="soil-form-card__header">
                <div>
                    <h2 class="soil-form-card__title">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="18" height="18" style="color:#166534;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                        Parameter Keasaman (pH) &amp; Unsur Hara Makro (NPK)
                    </h2>
                    <p class="soil-form-card__subtitle">Kandungan nutrisi esensial tanah untuk formulasi dosis pupuk presisi</p>
                </div>
            </div>

            <div class="soil-form-card__body">
                <div class="soil-form-grid soil-form-grid--4">
                    {{-- pH --}}
                    <div class="soil-form-group">
                        <label class="soil-form-label" for="ph_level">
                            <span>Derajat Keasaman (pH) <span class="req">*</span></span>
                        </label>
                        <div class="soil-input-group">
                            <input type="number" step="0.1" min="3.0" max="11.0" name="ph_level" id="ph_level" class="soil-input" value="{{ old('ph_level', 6.5) }}" required>
                            <span class="soil-input-addon">pH</span>
                        </div>
                        <span class="soil-field-hint">Ideal padi: <strong>6.0 &ndash; 7.0</strong></span>
                    </div>

                    {{-- Nitrogen --}}
                    <div class="soil-form-group">
                        <label class="soil-form-label" for="nitrogen_ppm">
                            <span>Nitrogen (N) <span class="req">*</span></span>
                        </label>
                        <div class="soil-input-group">
                            <input type="number" step="1" min="0" max="1000" name="nitrogen_ppm" id="nitrogen_ppm" class="soil-input" value="{{ old('nitrogen_ppm', 120) }}" required>
                            <span class="soil-input-addon">ppm</span>
                        </div>
                        <span class="soil-field-hint">Optimal: <strong>100 &ndash; 180 ppm</strong></span>
                    </div>

                    {{-- Phosphorus --}}
                    <div class="soil-form-group">
                        <label class="soil-form-label" for="phosphorus_ppm">
                            <span>Fosfor (P) <span class="req">*</span></span>
                        </label>
                        <div class="soil-input-group">
                            <input type="number" step="1" min="0" max="500" name="phosphorus_ppm" id="phosphorus_ppm" class="soil-input" value="{{ old('phosphorus_ppm', 25) }}" required>
                            <span class="soil-input-addon">ppm</span>
                        </div>
                        <span class="soil-field-hint">Optimal: <strong>20 &ndash; 40 ppm</strong></span>
                    </div>

                    {{-- Potassium --}}
                    <div class="soil-form-group">
                        <label class="soil-form-label" for="potassium_ppm">
                            <span>Kalium (K) <span class="req">*</span></span>
                        </label>
                        <div class="soil-input-group">
                            <input type="number" step="1" min="0" max="1000" name="potassium_ppm" id="potassium_ppm" class="soil-input" value="{{ old('potassium_ppm', 150) }}" required>
                            <span class="soil-input-addon">ppm</span>
                        </div>
                        <span class="soil-field-hint">Optimal: <strong>120 &ndash; 200 ppm</strong></span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Card 3: Sifat Fisik Tanah & Sensor Agroklimat --}}
        <section class="soil-form-card">
            <div class="soil-form-card__header">
                <div>
                    <h2 class="soil-form-card__title">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="18" height="18" style="color:#166534;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Fisik Tanah &amp; Parameter Irigasi
                    </h2>
                    <p class="soil-form-card__subtitle">Kadar air dan suhu lapisan perakaran untuk jadwal irigasi presisi</p>
                </div>
            </div>

            <div class="soil-form-card__body">
                <div class="soil-form-grid soil-form-grid--3" style="margin-bottom: 20px;">
                    {{-- Moisture --}}
                    <div class="soil-form-group">
                        <label class="soil-form-label" for="moisture_percentage">
                            <span>Kelembaban Tanah <span class="req">*</span></span>
                        </label>
                        <div class="soil-input-group">
                            <input type="number" step="0.1" min="0" max="100" name="moisture_percentage" id="moisture_percentage" class="soil-input" value="{{ old('moisture_percentage', request('moisture_percentage', 50.0)) }}" oninput="updateLiveIrrigationPreview()" required>
                            <span class="soil-input-addon">%</span>
                        </div>
                        <span class="soil-field-hint">Optimal fase anakan: <strong>45 &ndash; 75%</strong></span>
                    </div>

                    {{-- Organic Matter --}}
                    <div class="soil-form-group">
                        <label class="soil-form-label" for="organic_matter_percentage">
                            <span>Bahan Organik (C-Organik) <span class="req">*</span></span>
                        </label>
                        <div class="soil-input-group">
                            <input type="number" step="0.1" min="0" max="30" name="organic_matter_percentage" id="organic_matter_percentage" class="soil-input" value="{{ old('organic_matter_percentage', 2.5) }}" required>
                            <span class="soil-input-addon">%</span>
                        </div>
                        <span class="soil-field-hint">Target kesuburan: <strong>&ge; 2.0%</strong></span>
                    </div>

                    {{-- Soil Temperature --}}
                    <div class="soil-form-group">
                        <label class="soil-form-label" for="soil_temp_celsius">
                            <span>Suhu Lapisan Tanah <span class="opt-hint">(Opsional)</span></span>
                        </label>
                        <div class="soil-input-group">
                            <input type="number" step="0.1" min="-10" max="60" name="soil_temp_celsius" id="soil_temp_celsius" class="soil-input" value="{{ old('soil_temp_celsius', request('soil_temp_celsius', 26.5)) }}" placeholder="26.5">
                            <span class="soil-input-addon">&deg;C</span>
                        </div>
                        <span class="soil-field-hint">Suhu ideal akar: <strong>24 &ndash; 30 &deg;C</strong></span>
                    </div>
                </div>

                {{-- Live Irrigation Preview Box --}}
                <div id="irrigation-preview-box" class="soil-irrigation-card">
                    <div class="soil-irrigation-card__header">
                        <span class="soil-irrigation-card__title">Estimasi Rekomendasi Jadwal Irigasi Padi</span>
                        <span id="preview-irrigation-badge" class="soil-status-badge status-optimal">Kelembaban Optimal</span>
                    </div>
                    <p id="preview-irrigation-text" class="soil-irrigation-card__desc">
                        Tunda Pengairan &mdash; Kondisi air &amp; kelembaban tanah optimal untuk tanaman padi. Pertahankan ketinggian air 2-3 cm.
                    </p>
                    <div class="soil-irrigation-metrics">
                        <span>Waktu Pengairan: <strong id="preview-time-slot">Tunda Pengairan</strong></span>
                        <span>&bull;</span>
                        <span>Kedalaman Air: <strong id="preview-water-depth">2 &ndash; 3 cm</strong></span>
                        <span>&bull;</span>
                        <span>Volume Air Target: <strong id="preview-water-vol">0 &ndash; 20 m&sup3;/ha</strong></span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Card 4: Catatan Agronomi & Aksi Simpan --}}
        <section class="soil-form-card">
            <div class="soil-form-card__header">
                <div>
                    <h2 class="soil-form-card__title">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="18" height="18" style="color:#166534;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Catatan Agronomi &amp; Tindakan Lapangan
                    </h2>
                    <p class="soil-form-card__subtitle">Catatan observasi fisik lahan saat pengambilan sampel tanah</p>
                </div>
            </div>

            <div class="soil-form-card__body">
                <div class="soil-form-group" style="margin-bottom: 22px;">
                    <label class="soil-form-label" for="notes">
                        <span>Catatan Tambahan Petugas Lapangan <span class="opt-hint">(Opsional)</span></span>
                    </label>
                    <textarea name="notes" id="notes" rows="3" class="soil-textarea" placeholder="Contoh: Kondisi tanah sedikit retak halus di petak timur, riwayat pemupukan NPK 15 hari lalu, saluran drainase bersih.">{{ old('notes') }}</textarea>
                </div>

                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px; padding-top: 14px; border-top: 1px solid #f1f5f9;">
                    <a href="{{ route('admin.soil.index') }}" class="btn-soil-action">
                        Batal
                    </a>
                    <button type="submit" class="btn-soil-action btn-soil-primary" style="padding: 11px 24px;">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Analisis &amp; Simpan Sampel Tanah</span>
                    </button>
                </div>
            </div>
        </section>
    </form>
</div>

<script>
    let currentMode = 'manual';

    function setFormMode(mode) {
        currentMode = mode;
        const btnManual = document.getElementById('btn-mode-manual');
        const btnApi = document.getElementById('btn-mode-api');

        if (mode === 'api') {
            btnApi.classList.add('is-active');
            btnManual.classList.remove('is-active');
            autoFetchIfApiMode();
        } else {
            btnManual.classList.add('is-active');
            btnApi.classList.remove('is-active');
        }
    }

    function autoFetchIfApiMode() {
        if (currentMode === 'api') {
            fetchAgroMonitoringData();
        }
    }

    function fetchAgroMonitoringData() {
        const farmSelect = document.getElementById('farm_id');
        const farmId = farmSelect.value;

        if (!farmId) {
            alert('Silakan pilih Lahan Pertanian terlebih dahulu.');
            farmSelect.focus();
            return;
        }

        const selectedOption = farmSelect.options[farmSelect.selectedIndex];
        const lat = selectedOption.getAttribute('data-lat') || -6.32;
        const lng = selectedOption.getAttribute('data-lng') || 108.20;

        const btnFetch = document.getElementById('btn-fetch-api');
        const btnFetchText = document.getElementById('btn-fetch-text');
        btnFetch.disabled = true;
        btnFetchText.innerText = 'Memuat Data AgroMonitoring...';

        fetch(`/admin/weather/inspect?latitude=${lat}&longitude=${lng}`)
            .then(res => res.json())
            .then(res => {
                btnFetch.disabled = false;
                btnFetchText.innerText = 'Tarik Data Otomatis AgroMonitoring API';

                if (res.success && res.soil) {
                    const moisture = res.soil.moisture_percentage || 52.0;
                    const temp = res.soil.soil_temp_celsius || 26.5;

                    document.getElementById('moisture_percentage').value = moisture;
                    document.getElementById('soil_temp_celsius').value = temp;

                    // Set standard NPK & pH defaults if empty
                    if (!document.getElementById('ph_level').value || document.getElementById('ph_level').value == 6.5) {
                        document.getElementById('ph_level').value = 6.5;
                    }
                    if (!document.getElementById('nitrogen_ppm').value) {
                        document.getElementById('nitrogen_ppm').value = 120;
                    }

                    const banner = document.getElementById('api-feedback-banner');
                    document.getElementById('api-feedback-text').innerText = `Data AgroMonitoring API Berhasil Ditarik! (Kelembaban: ${moisture}%, Suhu Tanah: ${temp} °C)`;
                    banner.style.display = 'flex';

                    updateLiveIrrigationPreview();
                } else {
                    alert('Gagal mengambil data AgroMonitoring untuk lokasi lahan ini.');
                }
            })
            .catch(err => {
                btnFetch.disabled = false;
                btnFetchText.innerText = 'Tarik Data Otomatis AgroMonitoring API';
                alert('Terjadi kesalahan saat menghubungi server AgroMonitoring API.');
            });
    }

    function updateLiveIrrigationPreview() {
        const moisture = parseFloat(document.getElementById('moisture_percentage').value) || 50;

        const badge = document.getElementById('preview-irrigation-badge');
        const text = document.getElementById('preview-irrigation-text');
        const timeSlot = document.getElementById('preview-time-slot');
        const depth = document.getElementById('preview-water-depth');
        const vol = document.getElementById('preview-water-vol');

        if (moisture < 35) {
            badge.className = 'soil-status-badge status-critical';
            badge.innerText = 'Pengairan Urgen (Kering)';
            text.innerText = 'Segera alirkan air irigasi ke lahan padi untuk mencegah kekeringan zona perakaran. Hindari pengairan saat terik matahari siang.';
            timeSlot.innerText = 'Pagi Hari (06:00 - 08:00 WIB)';
            depth.innerText = '5 - 7 cm';
            vol.innerText = '60 - 80 m3/ha';
        } else if (moisture < 45) {
            badge.className = 'soil-status-badge status-warning';
            badge.innerText = 'Pengairan Berkala';
            text.innerText = 'Lakukan pengairan berselang (intermittent irrigation) secara bertahap untuk menjaga kelembaban optimal tanpa menggenangi berlebihan.';
            timeSlot.innerText = 'Sore Hari (16:00 - 18:00 WIB)';
            depth.innerText = '3 - 5 cm';
            vol.innerText = '40 - 50 m3/ha';
        } else if (moisture <= 80) {
            badge.className = 'soil-status-badge status-optimal';
            badge.innerText = 'Kelembaban Lembab Optimal';
            text.innerText = 'Kondisi air & kelembaban tanah optimal untuk tanaman padi. Pertahankan ketinggian air 2-3 cm dan periksa drainase secara berkala.';
            timeSlot.innerText = 'Tunda Pengairan';
            depth.innerText = '2 - 3 cm';
            vol.innerText = '0 - 20 m3/ha';
        } else {
            badge.className = 'soil-status-badge status-fertilizer';
            badge.innerText = 'Jenuh / Tergenang Penuh';
            text.innerText = 'Lahan tergenang penuh (>80%). Buka pintu drainase pembuangan air agar terjadi sirkulasi oksigen di perakaran padi.';
            timeSlot.innerText = 'Pembukaan Drainase (Segera)';
            depth.innerText = 'Drainase / Pengeringan Lahan';
            vol.innerText = '0 m3/ha (Keluarkan Air)';
        }
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', () => {
        updateLiveIrrigationPreview();
    });
</script>
@endsection
