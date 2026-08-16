@extends('layouts.admin')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/admin/operational.css') }}">

    <div class="admin-page">
        <div class="admin-page__header">
            <div>
                <p class="admin-page__eyebrow">Admin</p>
                <h1 class="admin-page__title">Tambah Uji Sampel Tanah</h1>
                <p class="admin-page__description">Masukkan data parameter fisik & hara tanah untuk mendapatkan evaluasi dan rekomendasi otomatis.</p>
            </div>
            <div class="admin-page__actions">
                <a href="{{ route('admin.soil.index') }}" class="admin-btn admin-btn--secondary">Kembali</a>
            </div>
        </div>

        @if ($errors->any())
            <div class="admin-alert admin-alert--error">
                <strong>Validasi Gagal:</strong>
                <ul style="margin-top: 0.5rem; padding-left: 1.5rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="admin-card">
            <div class="admin-card__header">
                <div class="admin-card__title">
                    <span>Formulir Sampel</span>
                    <h2>Parameter Uji Kualitas Tanah</h2>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.soil.store') }}" class="admin-form">
                @csrf

                <div class="admin-form-group">
                    <label for="farm_id">Pilih Lahan Pertanian <span style="color: red;">*</span></label>
                    <select name="farm_id" id="farm_id" class="admin-input" required>
                        <option value="">-- Pilih Lahan --</option>
                        @foreach ($farms as $farm)
                            <option value="{{ $farm->id }}" @if (old('farm_id') == $farm->id) selected @endif>
                                {{ $farm->name }} — {{ $farm->farmer?->name ?? 'Tanpa Petani' }} ({{ $farm->area_ha }} Ha)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="admin-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                    <div class="admin-form-group">
                        <label for="sample_code">Kode Sampel (Opsional)</label>
                        <input type="text" name="sample_code" id="sample_code" class="admin-input" placeholder="SOIL-2026-XXXX" value="{{ old('sample_code') }}">
                        <small class="admin-form-hint">Biarkan kosong untuk pembuatan kode otomatis.</small>
                    </div>

                    <div class="admin-form-group">
                        <label for="soil_type">Jenis Tanah <span style="color: red;">*</span></label>
                        <select name="soil_type" id="soil_type" class="admin-input" required>
                            <option value="loam" @if (old('soil_type') === 'loam') selected @endif>Lempung Berpasir / Loam (Ideal Padi)</option>
                            <option value="alluvial" @if (old('soil_type') === 'alluvial') selected @endif>Aluvial (Endapan)</option>
                            <option value="clay" @if (old('soil_type') === 'clay') selected @endif>Liat / Clay</option>
                            <option value="sandy_loam" @if (old('soil_type') === 'sandy_loam') selected @endif>Pasir Berlempung / Sandy Loam</option>
                            <option value="latosol" @if (old('soil_type') === 'latosol') selected @endif>Latosol / Merah Kuning</option>
                            <option value="peat" @if (old('soil_type') === 'peat') selected @endif>Gambut / Peat</option>
                        </select>
                    </div>
                </div>

                <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid #e5e7eb;">
                <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem;">Kandungan Keasaman & Unsur Hara Utama (NPK)</h3>

                <div class="admin-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="admin-form-group">
                        <label for="ph_level">Derajat Keasaman (pH) <span style="color: red;">*</span></label>
                        <input type="number" step="0.1" min="3" max="11" name="ph_level" id="ph_level" class="admin-input" value="{{ old('ph_level', 6.5) }}" required>
                        <small class="admin-form-hint">Ideal padi: 5.5 - 7.0</small>
                    </div>

                    <div class="admin-form-group">
                        <label for="nitrogen_ppm">Nitrogen (N) - ppm <span style="color: red;">*</span></label>
                        <input type="number" step="1" min="0" max="1000" name="nitrogen_ppm" id="nitrogen_ppm" class="admin-input" value="{{ old('nitrogen_ppm', 120) }}" required>
                        <small class="admin-form-hint">Ideal padi: 100 - 180 ppm</small>
                    </div>

                    <div class="admin-form-group">
                        <label for="phosphorus_ppm">Fosfor (P) - ppm <span style="color: red;">*</span></label>
                        <input type="number" step="1" min="0" max="500" name="phosphorus_ppm" id="phosphorus_ppm" class="admin-input" value="{{ old('phosphorus_ppm', 25) }}" required>
                        <small class="admin-form-hint">Ideal padi: 15 - 35 ppm</small>
                    </div>

                    <div class="admin-form-group">
                        <label for="potassium_ppm">Kalium (K) - ppm <span style="color: red;">*</span></label>
                        <input type="number" step="1" min="0" max="1000" name="potassium_ppm" id="potassium_ppm" class="admin-input" value="{{ old('potassium_ppm', 150) }}" required>
                        <small class="admin-form-hint">Ideal padi: 120 - 200 ppm</small>
                    </div>
                </div>

                <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid #e5e7eb;">
                <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem;">Fisik & Kelembaban Tanah</h3>

                <div class="admin-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="admin-form-group">
                        <label for="moisture_percentage">Kelembaban Tanah (%) <span style="color: red;">*</span></label>
                        <input type="number" step="0.1" min="0" max="100" name="moisture_percentage" id="moisture_percentage" class="admin-input" value="{{ old('moisture_percentage', request('moisture_percentage', 50.0)) }}" required>
                        <small class="admin-form-hint">Ideal padi: 45% - 75%</small>
                    </div>

                    <div class="admin-form-group">
                        <label for="organic_matter_percentage">Bahan Organik (%) <span style="color: red;">*</span></label>
                        <input type="number" step="0.1" min="0" max="30" name="organic_matter_percentage" id="organic_matter_percentage" class="admin-input" value="{{ old('organic_matter_percentage', 2.5) }}" required>
                        <small class="admin-form-hint">Ideal padi: >= 2.0%</small>
                    </div>

                    <div class="admin-form-group">
                        <label for="soil_temp_celsius">Suhu Tanah (°C)</label>
                        <input type="number" step="0.1" min="-10" max="60" name="soil_temp_celsius" id="soil_temp_celsius" class="admin-input" value="{{ old('soil_temp_celsius', request('soil_temp_celsius')) }}" placeholder="Contoh: 26.5">
                        <small class="admin-form-hint">Dapat dikosongkan untuk diambil dari AgroMonitoring API.</small>
                    </div>
                </div>

                <div class="admin-form-group" style="margin-top: 1rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="sync_agromonitoring" value="1" @if (old('sync_agromonitoring', 1)) checked @endif style="width: 18px; height: 18px;">
                        <span><strong>Sinkronkan dari AgroMonitoring API</strong> (Lengkapi kelembaban & suhu tanah otomatis sesuai koordinat lahan)</span>
                    </label>
                </div>

                <div class="admin-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-top: 1rem;">
                    <div class="admin-form-group">
                        <label for="tested_at">Waktu Pengujian Sampel <span style="color: red;">*</span></label>
                        <input type="datetime-local" name="tested_at" id="tested_at" class="admin-input" value="{{ old('tested_at', date('Y-m-d\TH:i')) }}" required>
                    </div>

                    <div class="admin-form-group" style="grid-column: span 2;">
                        <label for="notes">Catatan Petugas / Laboratorium</label>
                        <textarea name="notes" id="notes" class="admin-input" rows="2" placeholder="Masukkan catatan kondisi fisik lahan, cuaca saat pengumpulan sampel, dll.">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="admin-form-actions" style="margin-top: 1.5rem;">
                    <button type="submit" class="admin-btn">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg> Analisis & Simpan Sampel Tanah
                    </button>
                    <a href="{{ route('admin.soil.index') }}" class="admin-btn admin-btn--secondary">Batal</a>
                </div>
            </form>
        </section>
    </div>
@endsection
