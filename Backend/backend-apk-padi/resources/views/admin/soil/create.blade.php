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
        <span class="soil-breadcrumb-current">Tambah Uji Sampel</span>
    </nav>

    {{-- Page Header --}}
    <div class="soil-header">
        <div class="soil-header-content">
            <h1 class="soil-title">Tambah Uji Sampel Kualitas Tanah</h1>
            <p class="soil-description">Masukkan parameter fisik & hara tanah (pH, Nitrogen, Fosfor, Kalium, Kelembaban) untuk mendapatkan rekomendasi pemupukan otomatis.</p>
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

    {{-- Form Section --}}
    <section class="data-card">
        <div class="data-header">
            <div>
                <h2>Formulir Sampel Kualitas Tanah</h2>
                <p>Isi data parameter sampel tanah laboratorium atau pengujian lapangan</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.soil.store') }}" style="padding: 28px;">
            @csrf

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="farm_id">Pilih Lahan Pertanian <span style="color:#dc2626;">*</span></label>
                    <select name="farm_id" id="farm_id" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff; outline:none;" required>
                        <option value="">-- Pilih Lahan --</option>
                        @foreach ($farms as $farm)
                            <option value="{{ $farm->id }}" @selected(old('farm_id', request('farm_id')) == $farm->id)>
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
                <p style="font-size:12px; color:#64748b; margin:4px 0 0 0;">Jika dikosongkan, sistem akan menggenerasi kode sampel unik secara otomatis.</p>
            </div>

            <hr style="border:none; border-top:1px solid #f1f5f9; margin: 24px 0;">

            <h3 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px 0;">Parameter Keasaman (pH) & Unsur Hara Utama (NPK)</h3>

            <div style="display:grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 24px;">
                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="ph_level">Derajat Keasaman (pH) <span style="color:#dc2626;">*</span></label>
                    <input type="number" step="0.1" min="3" max="11" name="ph_level" id="ph_level" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff; outline:none; box-sizing:border-box;" value="{{ old('ph_level', 6.5) }}" required>
                    <p style="font-size:12px; color:#64748b; margin:4px 0 0 0;">Ideal padi: 5.5 - 7.0</p>
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="nitrogen_ppm">Nitrogen (N) - ppm <span style="color:#dc2626;">*</span></label>
                    <input type="number" step="1" min="0" max="1000" name="nitrogen_ppm" id="nitrogen_ppm" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff; outline:none; box-sizing:border-box;" value="{{ old('nitrogen_ppm', 120) }}" required>
                    <p style="font-size:12px; color:#64748b; margin:4px 0 0 0;">Ideal padi: 100 - 180 ppm</p>
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="phosphorus_ppm">Fosfor (P) - ppm <span style="color:#dc2626;">*</span></label>
                    <input type="number" step="1" min="0" max="500" name="phosphorus_ppm" id="phosphorus_ppm" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff; outline:none; box-sizing:border-box;" value="{{ old('phosphorus_ppm', 25) }}" required>
                    <p style="font-size:12px; color:#64748b; margin:4px 0 0 0;">Ideal padi: 15 - 35 ppm</p>
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="potassium_ppm">Kalium (K) - ppm <span style="color:#dc2626;">*</span></label>
                    <input type="number" step="1" min="0" max="1000" name="potassium_ppm" id="potassium_ppm" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff; outline:none; box-sizing:border-box;" value="{{ old('potassium_ppm', 150) }}" required>
                    <p style="font-size:12px; color:#64748b; margin:4px 0 0 0;">Ideal padi: 120 - 200 ppm</p>
                </div>
            </div>

            <hr style="border:none; border-top:1px solid #f1f5f9; margin: 24px 0;">

            <h3 style="font-size:16px; font-weight:700; color:#0f172a; margin:0 0 16px 0;">Fisik & Kelembaban Tanah</h3>

            <div style="display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-bottom: 24px;">
                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="moisture_percentage">Kelembaban Tanah (%) <span style="color:#dc2626;">*</span></label>
                    <input type="number" step="0.1" min="0" max="100" name="moisture_percentage" id="moisture_percentage" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff; outline:none; box-sizing:border-box;" value="{{ old('moisture_percentage', request('moisture_percentage', 50.0)) }}" required>
                    <p style="font-size:12px; color:#64748b; margin:4px 0 0 0;">Ideal padi: 45% - 75%</p>
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="organic_matter_percentage">Bahan Organik (%) <span style="color:#dc2626;">*</span></label>
                    <input type="number" step="0.1" min="0" max="30" name="organic_matter_percentage" id="organic_matter_percentage" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff; outline:none; box-sizing:border-box;" value="{{ old('organic_matter_percentage', 2.5) }}" required>
                    <p style="font-size:12px; color:#64748b; margin:4px 0 0 0;">Ideal padi: &gt;= 2.0%</p>
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="soil_temp_celsius">Suhu Tanah (°C)</label>
                    <input type="number" step="0.1" min="-10" max="60" name="soil_temp_celsius" id="soil_temp_celsius" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff; outline:none; box-sizing:border-box;" value="{{ old('soil_temp_celsius', request('soil_temp_celsius')) }}" placeholder="Contoh: 26.5">
                    <p style="font-size:12px; color:#64748b; margin:4px 0 0 0;">Dapat dikosongkan untuk diisi otomatis</p>
                </div>
            </div>

            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:14px 18px; margin-bottom: 24px;">
                <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                    <input type="checkbox" name="sync_agromonitoring" value="1" @checked(old('sync_agromonitoring', 1)) style="width:18px; height:18px;">
                    <span style="font-size:13px; color:#334155;"><strong>Sinkronkan dari AgroMonitoring API</strong> (Mengambil estimasi kelembaban & suhu tanah sesuai koordinat lahan)</span>
                </label>
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
@endsection
