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
        <a href="{{ route('admin.soil.index') }}" style="color:#64748b; text-decoration:none;">Deteksi Tanah</a>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="soil-breadcrumb-current">Tambah Uji Sampel (Dual Input)</span>
    </nav>

    {{-- Page Header --}}
    <div class="soil-header">
        <div class="soil-header-content">
            <h1 class="soil-title">Pengujian Kualitas Tanah & Jadwal Irigasi</h1>
            <p class="soil-description">Dapat diisi secara <strong>Manual (Uji Lab)</strong> atau ditarik <strong>Otomatis dari AgroMonitoring API</strong> untuk menghitung dosis hara & jadwal pengairan irigasi padi.</p>
        </div>

        <div class="soil-header-actions">
            <a href="{{ route('admin.soil.index') }}" class="btn-soil-action">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Daftar</span>
            </a>
        </div>
    </div>

    {{-- Validation Alerts --}}
    @if ($errors->any())
        <div class="soil-alert soil-alert-danger" id="alert-validation">
            <div>
                <strong>Validasi Gagal:</strong>
                <ul style="margin: 6px 0 0 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="soil-alert-close" onclick="document.getElementById('alert-validation').remove()">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Dual Input Mode Selector Bar --}}
    <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; padding:16px 24px; margin-bottom:24px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <span style="font-weight:700; font-size:14px; color:#0f172a;">Metode Pengisian Data:</span>
            <div style="display:flex; background:#f1f5f9; padding:4px; border-radius:10px; gap:4px;">
                <button type="button" id="btn-mode-manual" class="btn-soil-action btn-soil-primary" style="padding:6px 14px; font-size:12px;" onclick="setFormMode('manual')">
                    Input Manual Lab
                </button>
                <button type="button" id="btn-mode-api" class="btn-soil-action" style="padding:6px 14px; font-size:12px;" onclick="setFormMode('api')">
                    Tarik Otomatis AgroMonitoring API
                </button>
            </div>
        </div>

        <button type="button" id="btn-fetch-api" class="btn-soil-action" style="background:#e8f5e9; border-color:#81c784; color:#1b5e20;" onclick="fetchAgroMonitoringData()">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>Tarik Data Otomatis AgroMonitoring API</span>
        </button>
    </div>

    {{-- Feedback Banner --}}
    <div id="api-feedback-banner" class="soil-alert soil-alert-success" style="display:none; margin-bottom:24px;">
        <span id="api-feedback-text">Data kelembaban & suhu tanah AgroMonitoring berhasil diambil!</span>
    </div>

    {{-- Form Section --}}
    <section class="data-card">
        <div class="data-header">
            <div>
                <h2>Formulir Sampel & Parameter Irigasi</h2>
                <p>Data dapat disesuaikan kembali sebelum dilakukan evaluasi kesehatan tanah & jadwal pengairan</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.soil.store') }}" style="padding: 28px;">
            @csrf

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="farm_id">Pilih Lahan Pertanian <span style="color:#dc2626;">*</span></label>
                    <select name="farm_id" id="farm_id" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff; outline:none;" onchange="autoFetchIfApiMode()" required>
                        <option value="">-- Pilih Lahan --</option>
                        @foreach ($farms as $farm)
                            <option value="{{ $farm->id }}" data-lat="{{ $farm->latitude }}" data-lng="{{ $farm->longitude }}" @selected(old('farm_id', request('farm_id')) == $farm->id)>
                                {{ $farm->name }} — Pemilik: {{ $farm->farmer?->name ?? 'Tanpa Petani' }} ({{ $farm->area_ha ?? 0 }} Ha)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="soil_type">Jenis Tanah <span style="color:#dc2626;">*</span></label>
                    <select name="soil_type" id="soil_type" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff; outline:none;" required>
                        <option value="loam" @selected(old('soil_type') === 'loam')>Lempung Berpasir / Loam (Ideal Padi)</option>
                        <option value="alluvial" @selected(old('soil_type') === 'alluvial')>Aluvial (Endapan)</option>
                        <option value="clay" @selected(old('soil_type') === 'clay')>Liat / Clay</option>
                        <option value="sandy_loam" @selected(old('soil_type') === 'sandy_loam')>Pasir Berlempung / Sandy Loam</option>
                        <option value="latosol" @selected(old('soil_type') === 'latosol')>Latosol / Merah Kuning</option>
                        <option value="peat" @selected(old('soil_type') === 'peat')>Gambut / Peat</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="sample_code">Kode Sampel Laboratorium</label>
                <input type="text" name="sample_code" id="sample_code" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff; outline:none; box-sizing:border-box;" placeholder="SOIL-2026-XXXX (Kosongkan untuk pembuatan otomatis)" value="{{ old('sample_code') }}">
            </div>

            <hr style="border:none; border-top:1px solid #f1f5f9; margin: 24px 0;">

            <h3 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px 0;">Parameter Keasaman (pH) & Unsur Hara Utama (NPK)</h3>

            <div style="display:grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 24px;">
                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="ph_level">Derajat Keasaman (pH) <span style="color:#dc2626;">*</span></label>
                    <input type="number" step="0.1" min="3" max="11" name="ph_level" id="ph_level" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff; outline:none; box-sizing:border-box;" value="{{ old('ph_level', 6.5) }}" required>
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="nitrogen_ppm">Nitrogen (N) - ppm <span style="color:#dc2626;">*</span></label>
                    <input type="number" step="1" min="0" max="1000" name="nitrogen_ppm" id="nitrogen_ppm" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff; outline:none; box-sizing:border-box;" value="{{ old('nitrogen_ppm', 120) }}" required>
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="phosphorus_ppm">Fosfor (P) - ppm <span style="color:#dc2626;">*</span></label>
                    <input type="number" step="1" min="0" max="500" name="phosphorus_ppm" id="phosphorus_ppm" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff; outline:none; box-sizing:border-box;" value="{{ old('phosphorus_ppm', 25) }}" required>
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="potassium_ppm">Kalium (K) - ppm <span style="color:#dc2626;">*</span></label>
                    <input type="number" step="1" min="0" max="1000" name="potassium_ppm" id="potassium_ppm" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff; outline:none; box-sizing:border-box;" value="{{ old('potassium_ppm', 150) }}" required>
                </div>
            </div>

            <hr style="border:none; border-top:1px solid #f1f5f9; margin: 24px 0;">

            <h3 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px 0;">Fisik Tanah & Parameter Irigasi</h3>

            <div style="display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-bottom: 24px;">
                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="moisture_percentage">Kelembaban Tanah (%) <span style="color:#dc2626;">*</span></label>
                    <input type="number" step="0.1" min="0" max="100" name="moisture_percentage" id="moisture_percentage" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff; outline:none; box-sizing:border-box;" value="{{ old('moisture_percentage', request('moisture_percentage', 50.0)) }}" oninput="updateLiveIrrigationPreview()" required>
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="organic_matter_percentage">Bahan Organik (%) <span style="color:#dc2626;">*</span></label>
                    <input type="number" step="0.1" min="0" max="30" name="organic_matter_percentage" id="organic_matter_percentage" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff; outline:none; box-sizing:border-box;" value="{{ old('organic_matter_percentage', 2.5) }}" required>
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="soil_temp_celsius">Suhu Tanah (°C)</label>
                    <input type="number" step="0.1" min="-10" max="60" name="soil_temp_celsius" id="soil_temp_celsius" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff; outline:none; box-sizing:border-box;" value="{{ old('soil_temp_celsius', request('soil_temp_celsius', 26.5)) }}" placeholder="Contoh: 26.5">
                </div>
            </div>

            {{-- Live Irrigation Preview Box --}}
            <div id="irrigation-preview-box" style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; padding:16px 20px; margin-bottom:24px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                    <span style="font-size:12px; font-weight:800; text-transform:uppercase; color:#1b5e20;">Estimasi Rekomendasi Jadwal Irigasi Padi</span>
                    <span id="preview-irrigation-badge" class="soil-status-badge status-optimal">Kelembaban Optimal</span>
                </div>
                <p id="preview-irrigation-text" style="margin:0 0 8px 0; font-size:13px; color:#334155;">
                    Tunda Pengairan — Kondisi air & kelembaban tanah optimal untuk tanaman padi. Pertahankan ketinggian air 2-3 cm.
                </p>
                <div style="display:flex; gap:16px; font-size:12px; color:#64748b;">
                    <span>Waktu Pengairan: <strong id="preview-time-slot" style="color:#0f172a;">Tunda Pengairan</strong></span> |
                    <span>Kedalaman Air: <strong id="preview-water-depth" style="color:#0f172a;">2 - 3 cm</strong></span> |
                    <span>Volume Air: <strong id="preview-water-vol" style="color:#0f172a;">0 - 20 m3/ha</strong></span>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-bottom: 28px;">
                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="tested_at">Waktu Pengujian Sampel <span style="color:#dc2626;">*</span></label>
                    <input type="datetime-local" name="tested_at" id="tested_at" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff; outline:none; box-sizing:border-box;" value="{{ old('tested_at', date('Y-m-d\TH:i')) }}" required>
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="notes">Catatan Laboratorium / Petugas Field</label>
                    <textarea name="notes" id="notes" rows="2" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff; outline:none; box-sizing:border-box;" placeholder="Kondisi fisik lahan saat pengambilan sampel, rekomendasi pemupukan awal, dll.">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div style="display:flex; align-items:center; gap:12px;">
                <button type="submit" class="btn-soil-action btn-soil-primary" style="padding:12px 24px;">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Analisis & Simpan Sampel Tanah</span>
                </button>

                <a href="{{ route('admin.soil.index') }}" class="btn-soil-action">Batal</a>
            </div>
        </form>
    </section>
</div>

<script>
    let currentMode = 'manual';

    function setFormMode(mode) {
        currentMode = mode;
        const btnManual = document.getElementById('btn-mode-manual');
        const btnApi = document.getElementById('btn-mode-api');

        if (mode === 'api') {
            btnApi.classList.add('btn-soil-primary');
            btnManual.classList.remove('btn-soil-primary');
            autoFetchIfApiMode();
        } else {
            btnManual.classList.add('btn-soil-primary');
            btnApi.classList.remove('btn-soil-primary');
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
            return;
        }

        const selectedOption = farmSelect.options[farmSelect.selectedIndex];
        const lat = selectedOption.getAttribute('data-lat') || -7.25;
        const lng = selectedOption.getAttribute('data-lng') || 112.75;

        const btnFetch = document.getElementById('btn-fetch-api');
        btnFetch.disabled = true;
        btnFetch.innerHTML = 'Memuat Data AgroMonitoring...';

        fetch(`/admin/weather/inspect?latitude=${lat}&longitude=${lng}`)
            .then(res => res.json())
            .then(res => {
                btnFetch.disabled = false;
                btnFetch.innerHTML = '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg><span>Tarik Data Otomatis AgroMonitoring API</span>';

                if (res.success && res.soil) {
                    const moisture = res.soil.moisture_percentage || 52.0;
                    const temp = res.soil.soil_temp_celsius || 26.5;

                    document.getElementById('moisture_percentage').value = moisture;
                    document.getElementById('soil_temp_celsius').value = temp;

                    // Set standard NPK & pH defaults
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
                btnFetch.innerHTML = '<span>Tarik Data Otomatis AgroMonitoring API</span>';
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
            text.innerText = 'Lahan tergenang penuh (>80%). Buka pintu drainase pembuangan air agar terjadi sirkulasi oksigen di akar padi.';
            timeSlot.innerText = 'Pembukaan Drainase (Segera)';
            depth.innerText = 'Drainase / Pengeringan Lahan';
            vol.innerText = '0 m3/ha (Keluarkan Air)';
        }
    }
</script>
@endsection
