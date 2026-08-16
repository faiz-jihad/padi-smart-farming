@extends('layouts.admin')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/admin/operational.css') }}">

    <div class="admin-page">
        <div class="admin-page__header">
            <div>
                <p class="admin-page__eyebrow">Admin — Laporan Analisis Tanah</p>
                <h1 class="admin-page__title">Sampel #{{ $soilDetection->sample_code }}</h1>
                <p class="admin-page__description">
                    Lahan: <strong>{{ $soilDetection->farm?->name ?? 'Lahan Tidak Ditemukan' }}</strong> |
                    Petani: <strong>{{ $soilDetection->farm?->farmer?->name ?? '-' }}</strong> |
                    Tanggal Uji: <strong>{{ $soilDetection->tested_at->format('d M Y H:i') }}</strong>
                </p>
            </div>
            <div class="admin-page__actions">
                <button onclick="window.print()" class="admin-btn admin-btn--secondary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg> Cetak Laporan
                </button>
                <a href="{{ route('admin.soil.index') }}" class="admin-btn admin-btn--secondary">Kembali</a>
            </div>
        </div>

        @if (session('status'))
            <div class="admin-alert admin-alert--success">
                {{ session('status') }}
            </div>
        @endif

        <!-- Card Overall Score -->
        <section class="admin-card" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
                <div>
                    <span style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8;">Hasil Evaluasi Kualitas Tanah</span>
                    <h2 style="font-size: 1.75rem; margin: 0.25rem 0 0.5rem 0; font-weight: 700;">
                        @if ($soilDetection->soil_status === 'optimal')
                            Kondisi Tanah Optimal Untuk Tanaman Padi
                        @elseif ($soilDetection->soil_status === 'needs_fertilizer')
                            Tanah Membutuhkan Penambahan Unsur Hara / Pemupukan
                        @elseif ($soilDetection->soil_status === 'warning')
                            Perlu Perhatian & Pengkondisian Tanah
                        @else
                            Kondisi Tanah Kritis — Butuh Penanganan Segera
                        @endif
                    </h2>
                    <p style="margin: 0; color: #cbd5e1; font-size: 0.95rem;">
                        Jenis Tanah: <strong style="color: #60a5fa;">{{ ucfirst($soilDetection->soil_type) }}</strong> |
                        Suhu Tanah: <strong>{{ $soilDetection->soil_temp_celsius ? number_format($soilDetection->soil_temp_celsius, 1) . '°C' : 'Data cuaca AgroMonitoring' }}</strong> |
                        Dianalisis Oleh: <strong>{{ $soilDetection->creator?->name ?? 'Sistem AI P.A.D.I' }}</strong>
                    </p>
                </div>

                <div style="display: flex; align-items: center; background: rgba(255, 255, 255, 0.1); padding: 1rem 1.5rem; border-radius: 12px; gap: 1rem;">
                    <div style="text-align: center;">
                        <span style="display: block; font-size: 0.75rem; color: #94a3b8; text-transform: uppercase;">Skor Kesehatan</span>
                        <span style="font-size: 2.5rem; font-weight: 800; color: {{ $soilDetection->soil_health_score >= 80 ? '#4ade80' : ($soilDetection->soil_health_score >= 60 ? '#60a5fa' : ($soilDetection->soil_health_score >= 45 ? '#fbbf24' : '#f87171')) }};">
                            {{ $soilDetection->soil_health_score }}<small style="font-size: 1.2rem;">/100</small>
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Grid Radar Parameter Hara -->
        <div class="admin-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
            <!-- pH Card -->
            <div class="admin-card" style="padding: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <span style="font-weight: 600; color: #475569;">Derajat Keasaman (pH)</span>
                    <span style="font-size: 1.25rem; font-weight: 800; color: {{ $soilDetection->ph_level < 5.5 ? '#dc2626' : ($soilDetection->ph_level > 7.5 ? '#d97706' : '#16a34a') }};">
                        {{ number_format($soilDetection->ph_level, 1) }}
                    </span>
                </div>
                <div style="background: #e2e8f0; height: 10px; border-radius: 5px; overflow: hidden;">
                    <div style="width: {{ min(100, max(10, ($soilDetection->ph_level / 14) * 100)) }}%; background: {{ $soilDetection->ph_level < 5.5 ? '#dc2626' : ($soilDetection->ph_level > 7.5 ? '#d97706' : '#16a34a') }}; height: 100%;"></div>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #64748b; margin-top: 0.5rem;">
                    <span>Asam (&lt; 5.5)</span>
                    <span>Ideal (5.5 - 7.0)</span>
                    <span>Basa (&gt; 7.5)</span>
                </div>
            </div>

            <!-- Nitrogen Card -->
            <div class="admin-card" style="padding: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <span style="font-weight: 600; color: #475569;">Nitrogen (N)</span>
                    <span style="font-size: 1.25rem; font-weight: 800; color: {{ $soilDetection->nitrogen_ppm < 100 ? '#d97706' : '#16a34a' }};">
                        {{ number_format($soilDetection->nitrogen_ppm, 0) }} <small style="font-size: 0.8rem;">ppm</small>
                    </span>
                </div>
                <div style="background: #e2e8f0; height: 10px; border-radius: 5px; overflow: hidden;">
                    <div style="width: {{ min(100, ($soilDetection->nitrogen_ppm / 250) * 100) }}%; background: #2563eb; height: 100%;"></div>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #64748b; margin-top: 0.5rem;">
                    <span>Rendah (&lt;100)</span>
                    <span>Ideal (100 - 180)</span>
                    <span>Tinggi (&gt;200)</span>
                </div>
            </div>

            <!-- Fosfor Card -->
            <div class="admin-card" style="padding: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <span style="font-weight: 600; color: #475569;">Fosfor (P)</span>
                    <span style="font-size: 1.25rem; font-weight: 800; color: {{ $soilDetection->phosphorus_ppm < 15 ? '#d97706' : '#16a34a' }};">
                        {{ number_format($soilDetection->phosphorus_ppm, 0) }} <small style="font-size: 0.8rem;">ppm</small>
                    </span>
                </div>
                <div style="background: #e2e8f0; height: 10px; border-radius: 5px; overflow: hidden;">
                    <div style="width: {{ min(100, ($soilDetection->phosphorus_ppm / 50) * 100) }}%; background: #16a34a; height: 100%;"></div>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #64748b; margin-top: 0.5rem;">
                    <span>Rendah (&lt;15)</span>
                    <span>Ideal (15 - 35)</span>
                    <span>Tinggi (&gt;40)</span>
                </div>
            </div>

            <!-- Kalium Card -->
            <div class="admin-card" style="padding: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <span style="font-weight: 600; color: #475569;">Kalium (K)</span>
                    <span style="font-size: 1.25rem; font-weight: 800; color: {{ $soilDetection->potassium_ppm < 120 ? '#d97706' : '#16a34a' }};">
                        {{ number_format($soilDetection->potassium_ppm, 0) }} <small style="font-size: 0.8rem;">ppm</small>
                    </span>
                </div>
                <div style="background: #e2e8f0; height: 10px; border-radius: 5px; overflow: hidden;">
                    <div style="width: {{ min(100, ($soilDetection->potassium_ppm / 300) * 100) }}%; background: #9333ea; height: 100%;"></div>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #64748b; margin-top: 0.5rem;">
                    <span>Rendah (&lt;120)</span>
                    <span>Ideal (120 - 200)</span>
                    <span>Tinggi (&gt;220)</span>
                </div>
            </div>

            <!-- Kelembaban Card -->
            <div class="admin-card" style="padding: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <span style="font-weight: 600; color: #475569;">Kelembaban Tanah</span>
                    <span style="font-size: 1.25rem; font-weight: 800; color: #0284c7;">
                        {{ number_format($soilDetection->moisture_percentage, 1) }}%
                    </span>
                </div>
                <div style="background: #e2e8f0; height: 10px; border-radius: 5px; overflow: hidden;">
                    <div style="width: {{ min(100, $soilDetection->moisture_percentage) }}%; background: #0284c7; height: 100%;"></div>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #64748b; margin-top: 0.5rem;">
                    <span>Kering (&lt;35%)</span>
                    <span>Ideal Padi (45 - 75%)</span>
                    <span>Jenuh (&gt;90%)</span>
                </div>
            </div>

            <!-- Bahan Organik Card -->
            <div class="admin-card" style="padding: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <span style="font-weight: 600; color: #475569;">Bahan Organik</span>
                    <span style="font-size: 1.25rem; font-weight: 800; color: {{ $soilDetection->organic_matter_percentage < 2.0 ? '#d97706' : '#16a34a' }};">
                        {{ number_format($soilDetection->organic_matter_percentage, 1) }}%
                    </span>
                </div>
                <div style="background: #e2e8f0; height: 10px; border-radius: 5px; overflow: hidden;">
                    <div style="width: {{ min(100, ($soilDetection->organic_matter_percentage / 5) * 100) }}%; background: #854d0e; height: 100%;"></div>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #64748b; margin-top: 0.5rem;">
                    <span>Rendah (&lt;2.0%)</span>
                    <span>Ideal (&gt;= 2.0%)</span>
                    <span>Tinggi (&gt;4.0%)</span>
                </div>
            </div>
        </div>

        <!-- Rekomendasi Agronomi -->
        <section class="admin-card" style="margin-bottom: 1.5rem;">
            <div class="admin-card__header">
                <div class="admin-card__title">
                    <span>Rekomendasi Pemupukan & Lahan</span>
                    <h2>Tindakan Perbaikan Agronomi Padi</h2>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 1rem;">
                @forelse($soilDetection->recommendations_json ?? [] as $rec)
                    <div style="padding: 1rem 1.25rem; border-radius: 8px; border-left: 5px solid {{ ($rec['level'] ?? '') === 'critical' ? '#dc2626' : (($rec['level'] ?? '') === 'warning' ? '#f59e0b' : '#16a34a') }}; background: {{ ($rec['level'] ?? '') === 'critical' ? '#fef2f2' : (($rec['level'] ?? '') === 'warning' ? '#fffbeb' : '#f0fdf4') }};">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                            <strong style="font-size: 1rem; color: #1e293b;">{{ $rec['title'] ?? 'Rekomendasi' }}</strong>
                            <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; padding: 0.2rem 0.6rem; border-radius: 4px; background: rgba(0,0,0,0.06);">{{ $rec['category'] ?? 'Agronomi' }}</span>
                        </div>
                        <p style="margin: 0; color: #334155; font-size: 0.95rem; line-height: 1.5;">
                            {{ $rec['action'] ?? '' }}
                        </p>
                    </div>
                @empty
                    <p class="admin-empty">Tidak ada rekomendasi khusus. Kondisi hara tanah dalam keadaan normal.</p>
                @endforelse
            </div>
        </section>

        <!-- Korelasi Cuaca Lahan -->
        @if ($latestWeather)
            @php
                $weatherPayload = $latestWeather->payload_json ?? [];
                $temp = $weatherPayload['main']['temp'] ?? 'N/A';
                $humidity = $weatherPayload['main']['humidity'] ?? 'N/A';
                $wind = $weatherPayload['wind']['speed'] ?? 'N/A';
                $desc = $weatherPayload['weather'][0]['description'] ?? 'N/A';
            @endphp
            <section class="admin-card">
                <div class="admin-card__header">
                    <div class="admin-card__title">
                        <span>Korelasi Iklim Lahan</span>
                        <h2>Data Cuaca Terkini dari OpenWeather / AgroMonitoring</h2>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 2rem; flex-wrap: wrap;">
                    <div style="font-size: 1.1rem; color: #334155;">
                        Kondisi: <strong>{{ ucfirst($desc) }}</strong><br>
                        <small class="admin-text--muted">Diamati pada {{ $latestWeather->observed_at->format('d M Y H:i') }}</small>
                    </div>
                    <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                        <div style="background: #f8fafc; padding: 0.75rem 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <span style="font-size: 0.8rem; color: #64748b; display: block;">Suhu Udara</span>
                            <strong style="font-size: 1.2rem; color: #0f172a;">{{ $temp }}°C</strong>
                        </div>
                        <div style="background: #f8fafc; padding: 0.75rem 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <span style="font-size: 0.8rem; color: #64748b; display: block;">Kelembaban Udara</span>
                            <strong style="font-size: 1.2rem; color: #0f172a;">{{ $humidity }}%</strong>
                        </div>
                        <div style="background: #f8fafc; padding: 0.75rem 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <span style="font-size: 0.8rem; color: #64748b; display: block;">Kecepatan Angin</span>
                            <strong style="font-size: 1.2rem; color: #0f172a;">{{ $wind }} m/s</strong>
                        </div>
                    </div>
                </div>
            </section>
        @endif
    </div>
@endsection
