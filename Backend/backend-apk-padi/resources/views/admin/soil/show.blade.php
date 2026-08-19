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
        <span class="soil-breadcrumb-current">Laporan Sampel #{{ $soilDetection->sample_code }}</span>
    </nav>

    {{-- Page Header --}}
    <div class="soil-header">
        <div class="soil-header-content">
            <h1 class="soil-title">Laporan Analisis Kualitas Tanah #{{ $soilDetection->sample_code }}</h1>
            <p class="soil-description">
                Lahan: <strong style="color:#0f172a;">{{ $soilDetection->farm?->name ?? 'Lahan Tidak Ditemukan' }}</strong> | 
                Pemilik: <strong style="color:#0f172a;">{{ $soilDetection->farm?->farmer?->name ?? '-' }}</strong> | 
                Tanggal Uji: <strong style="color:#0f172a;">{{ $soilDetection->tested_at->format('d M Y H:i') }}</strong>
            </p>
        </div>

        <div class="soil-header-actions">
            <button onclick="window.print()" class="btn-soil-action">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak Laporan</span>
            </button>

            <a href="{{ route('admin.soil.index') }}" class="btn-soil-action">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Daftar</span>
            </a>
        </div>
    </div>

    {{-- Status Alerts --}}
    @if(session('status'))
        <div class="soil-alert soil-alert-success" id="alert-status">
            <span>{{ session('status') }}</span>
            <button type="button" class="soil-alert-close" onclick="document.getElementById('alert-status').remove()">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Overall Health Header Card (SOLID GREEN, WHITE, BLACK ONLY) --}}
    <section style="background: #1b5e20; color: #ffffff; border-radius: 16px; padding: 24px 28px; margin-bottom: 28px; border: 1px solid #14532d;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
            <div>
                <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #a7f3d0;">HASIL EVALUASI KUALITAS TANAH</span>
                <h2 style="font-size: 22px; margin: 4px 0 6px 0; font-weight: 800; color: #ffffff;">
                    @if ($soilDetection->soil_status === 'optimal')
                        Kondisi Tanah Subur & Optimal Untuk Tanaman Padi
                    @elseif ($soilDetection->soil_status === 'needs_fertilizer')
                        Tanah Membutuhkan Penambahan Unsur Hara / Pemupukan
                    @elseif ($soilDetection->soil_status === 'warning')
                        Perlu Perhatian & Pengkondisian Kesuburan Tanah
                    @else
                        Kondisi Tanah Kritis — Membutuhkan Penanganan Segera
                    @endif
                </h2>
                <p style="margin: 0; color: #e2e8f0; font-size: 13px;">
                    Jenis Tanah: <strong style="color: #ffffff;">{{ ucfirst($soilDetection->soil_type) }}</strong> | 
                    Suhu Tanah: <strong style="color: #ffffff;">{{ $soilDetection->soil_temp_celsius ? number_format($soilDetection->soil_temp_celsius, 1) . ' °C' : 'Data AgroMonitoring' }}</strong> | 
                    Dianalisis Oleh: <strong style="color: #ffffff;">{{ $soilDetection->creator?->name ?? 'Sistem AI P.A.D.I' }}</strong>
                </p>
            </div>

            <div style="background: #ffffff; padding: 14px 24px; border-radius: 12px; text-align: center; border: 1px solid #166534;">
                <span style="display: block; font-size: 11px; color: #1b5e20; text-transform: uppercase; font-weight:800;">Skor Kesehatan Tanah</span>
                <span style="font-size: 36px; font-weight: 800; color: #1b5e20; line-height: 1;">
                    {{ $soilDetection->soil_health_score }}<small style="font-size: 16px; color: #64748b;">/100</small>
                </span>
            </div>
        </div>
    </section>

    {{-- Parameter Metric Cards Grid (HIJAU, PUTIH, HITAM ONLY) --}}
    <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; margin-bottom: 32px;">
        {{-- pH Level --}}
        <div class="data-card" style="margin-bottom:0; padding:20px 24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <span style="font-size:13px; font-weight:700; color:#0f172a; text-transform:uppercase;">Derajat Keasaman (pH)</span>
                <span class="ph-badge ph-optimal" style="background:#e8f5e9; color:#1b5e20; font-weight:800;">
                    {{ number_format($soilDetection->ph_level, 1) }}
                </span>
            </div>
            <div style="background:#e2e8f0; height:8px; border-radius:4px; overflow:hidden; margin-bottom:8px;">
                <div style="width: {{ min(100, max(10, ($soilDetection->ph_level / 14) * 100)) }}%; background: #166534; height:100%;"></div>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:11px; color:#64748b;">
                <span>Asam (&lt; 5.5)</span>
                <span>Ideal (5.5 - 7.0)</span>
                <span>Basa (&gt; 7.5)</span>
            </div>
        </div>

        {{-- Nitrogen (N) --}}
        <div class="data-card" style="margin-bottom:0; padding:20px 24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <span style="font-size:13px; font-weight:700; color:#0f172a; text-transform:uppercase;">Nitrogen (N)</span>
                <span style="font-size:16px; font-weight:800; color: #166534;">
                    {{ number_format($soilDetection->nitrogen_ppm, 0) }} <small style="font-size:12px; color:#64748b;">ppm</small>
                </span>
            </div>
            <div style="background:#e2e8f0; height:8px; border-radius:4px; overflow:hidden; margin-bottom:8px;">
                <div style="width: {{ min(100, ($soilDetection->nitrogen_ppm / 250) * 100) }}%; background: #166534; height:100%;"></div>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:11px; color:#64748b;">
                <span>Rendah (&lt;100)</span>
                <span>Ideal (100 - 180)</span>
                <span>Tinggi (&gt;200)</span>
            </div>
        </div>

        {{-- Fosfor (P) --}}
        <div class="data-card" style="margin-bottom:0; padding:20px 24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <span style="font-size:13px; font-weight:700; color:#0f172a; text-transform:uppercase;">Fosfor (P)</span>
                <span style="font-size:16px; font-weight:800; color: #166534;">
                    {{ number_format($soilDetection->phosphorus_ppm, 0) }} <small style="font-size:12px; color:#64748b;">ppm</small>
                </span>
            </div>
            <div style="background:#e2e8f0; height:8px; border-radius:4px; overflow:hidden; margin-bottom:8px;">
                <div style="width: {{ min(100, ($soilDetection->phosphorus_ppm / 50) * 100) }}%; background: #166534; height:100%;"></div>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:11px; color:#64748b;">
                <span>Rendah (&lt;15)</span>
                <span>Ideal (15 - 35)</span>
                <span>Tinggi (&gt;40)</span>
            </div>
        </div>

        {{-- Kalium (K) --}}
        <div class="data-card" style="margin-bottom:0; padding:20px 24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <span style="font-size:13px; font-weight:700; color:#0f172a; text-transform:uppercase;">Kalium (K)</span>
                <span style="font-size:16px; font-weight:800; color: #166534;">
                    {{ number_format($soilDetection->potassium_ppm, 0) }} <small style="font-size:12px; color:#64748b;">ppm</small>
                </span>
            </div>
            <div style="background:#e2e8f0; height:8px; border-radius:4px; overflow:hidden; margin-bottom:8px;">
                <div style="width: {{ min(100, ($soilDetection->potassium_ppm / 300) * 100) }}%; background: #166534; height:100%;"></div>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:11px; color:#64748b;">
                <span>Rendah (&lt;120)</span>
                <span>Ideal (120 - 200)</span>
                <span>Tinggi (&gt;220)</span>
            </div>
        </div>

        {{-- Kelembaban Tanah --}}
        <div class="data-card" style="margin-bottom:0; padding:20px 24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <span style="font-size:13px; font-weight:700; color:#0f172a; text-transform:uppercase;">Kelembaban Tanah</span>
                <span style="font-size:16px; font-weight:800; color: #166534;">
                    {{ number_format($soilDetection->moisture_percentage, 1) }}%
                </span>
            </div>
            <div style="background:#e2e8f0; height:8px; border-radius:4px; overflow:hidden; margin-bottom:8px;">
                <div style="width: {{ min(100, $soilDetection->moisture_percentage) }}%; background: #166534; height:100%;"></div>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:11px; color:#64748b;">
                <span>Kering (&lt;35%)</span>
                <span>Ideal Padi (45 - 75%)</span>
                <span>Jenuh (&gt;90%)</span>
            </div>
        </div>

        {{-- Bahan Organik --}}
        <div class="data-card" style="margin-bottom:0; padding:20px 24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <span style="font-size:13px; font-weight:700; color:#0f172a; text-transform:uppercase;">Bahan Organik</span>
                <span style="font-size:16px; font-weight:800; color: #166534;">
                    {{ number_format($soilDetection->organic_matter_percentage, 1) }}%
                </span>
            </div>
            <div style="background:#e2e8f0; height:8px; border-radius:4px; overflow:hidden; margin-bottom:8px;">
                <div style="width: {{ min(100, ($soilDetection->organic_matter_percentage / 5) * 100) }}%; background: #166534; height:100%;"></div>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:11px; color:#64748b;">
                <span>Rendah (&lt;2.0%)</span>
                <span>Ideal (&gt;= 2.0%)</span>
                <span>Tinggi (&gt;4.0%)</span>
            </div>
        </div>
    </div>

    {{-- DEDICATED SECTION: JADWAL & REKOMENDASI PENGAIRAN IRIGASI PADI --}}
    @php
        $soilService = app(\App\Services\Soil\SoilDetectionService::class);
        $irrigation = $soilService->calculateIrrigationSchedule(
            (float) $soilDetection->moisture_percentage,
            $soilDetection->soil_temp_celsius ? (float) $soilDetection->soil_temp_celsius : null
        );
    @endphp

    <section class="data-card" style="border: 2px solid #81c784; background: #ffffff; margin-bottom: 32px;">
        <div class="data-header" style="background: #f0fdf4; border-bottom: 1px solid #c8e6c9;">
            <div>
                <h2 style="color: #1b5e20; font-size: 20px;">Jadwal & Rekomendasi Pengairan Irigasi Padi</h2>
                <p style="color: #166534;">Kalkulasi rekomendasi waktu pengairan, kedalaman air, dan volume irigasi sesuai kelembaban tanah</p>
            </div>

            <span class="soil-status-badge status-optimal" style="font-size:13px; padding:6px 14px; background:#1b5e20; color:#ffffff;">
                {{ $irrigation['status_label'] }}
            </span>
        </div>

        <div style="padding: 28px;">
            <div style="background: #ffffff; border: 2px solid #81c784; padding: 20px 24px; border-radius: 14px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                <div>
                    <span style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: #1b5e20; letter-spacing: 0.05em;">JADWAL WAKTU & TANGGAL PENGAIRAN IRIGASI</span>
                    <h3 style="font-size: 22px; font-weight: 800; color: #0f172a; margin: 4px 0 2px 0;">{{ $irrigation['exact_date_time'] }}</h3>
                    <p style="margin: 0; font-size: 13px; color: #64748b;">Slot Waktu Direkomendasikan: <strong>{{ $irrigation['recommended_time_slot'] }}</strong></p>
                </div>

                <div style="background: #e8f5e9; padding: 10px 18px; border-radius: 10px; border: 1px solid #a7f3d0; text-align: right;">
                    <span style="font-size: 11px; font-weight: 700; color: #1b5e20; display: block; text-transform: uppercase;">Kelembaban Tanah saat ini</span>
                    <strong style="font-size: 20px; color: #1b5e20;">{{ number_format($soilDetection->moisture_percentage, 1) }}%</strong>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 24px;">
                <div style="background: #ffffff; padding: 16px; border-radius: 12px; border: 1px solid #c8e6c9;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Waktu Pengairan Ideal</span>
                    <strong style="font-size: 15px; color: #0f172a; display: block; margin-top: 4px;">{{ $irrigation['recommended_time_slot'] }}</strong>
                    <span style="font-size: 11px; color: #166534;">Meminimalkan penguapan matahari</span>
                </div>

                <div style="background: #ffffff; padding: 16px; border-radius: 12px; border: 1px solid #c8e6c9;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Target Kedalaman Air</span>
                    <strong style="font-size: 15px; color: #0f172a; display: block; margin-top: 4px;">{{ $irrigation['target_water_depth'] }}</strong>
                    <span style="font-size: 11px; color: #166534;">Ketinggian genangan di sawah</span>
                </div>

                <div style="background: #ffffff; padding: 16px; border-radius: 12px; border: 1px solid #c8e6c9;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Estimasi Volume Air</span>
                    <strong style="font-size: 15px; color: #0f172a; display: block; margin-top: 4px;">{{ $irrigation['water_volume'] }}</strong>
                    <span style="font-size: 11px; color: #166534;">Kebutuhan pasokan air per ha</span>
                </div>

                <div style="background: #ffffff; padding: 16px; border-radius: 12px; border: 1px solid #c8e6c9;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Estimasi Irigasi Berikutnya</span>
                    <strong style="font-size: 15px; color: #0f172a; display: block; margin-top: 4px;">{{ $irrigation['next_schedule'] }}</strong>
                    <span style="font-size: 11px; color: #166534;">Berdasarkan evaluasi kelembaban</span>
                </div>
            </div>

            <div style="background: #ffffff; border-left: 5px solid #1b5e20; padding: 16px 20px; border-radius: 10px; border: 1px solid #c8e6c9; border-left-width: 5px;">
                <span style="font-weight: 700; font-size: 14px; color: #0f172a; display: block; margin-bottom: 4px;">Petunjuk Tindakan Irigasi:</span>
                <p style="margin: 0; font-size: 14px; color: #334155; line-height: 1.6;">
                    {{ $irrigation['action_recommendation'] }}
                </p>
            </div>
        </div>
    </section>

    {{-- Rekomendasi Agronomi Pemupukan --}}
    <section class="data-card">
        <div class="data-header">
            <div>
                <h2>Rekomendasi Pemupukan & Perbaikan Lahan</h2>
                <p>Panduan tindakan agronomi berdasarkan hasil analisis kadar hara tanah</p>
            </div>
        </div>

        <div style="padding: 24px; display:flex; flex-direction:column; gap:14px;">
            @forelse($soilDetection->recommendations_json ?? [] as $rec)
                <div style="padding: 16px 20px; border-radius: 12px; border-left: 5px solid #1b5e20; background: #f0fdf4; border: 1px solid #c8e6c9; border-left-width: 5px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <strong style="font-size: 15px; color: #0f172a;">{{ $rec['title'] ?? 'Rekomendasi Agronomi' }}</strong>
                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 3px 8px; border-radius: 6px; background: #e8f5e9; color:#1b5e20;">{{ $rec['category'] ?? 'Agronomi' }}</span>
                    </div>
                    <p style="margin: 0; color: #334155; font-size: 14px; line-height: 1.5;">
                        {{ $rec['action'] ?? '' }}
                    </p>
                </div>
            @empty
                <div class="soil-empty">
                    Tidak ada rekomendasi khusus. Kondisi hara tanah dalam keadaan normal & seimbang.
                </div>
            @endforelse
        </div>
    </section>

    {{-- Korelasi Cuaca Lahan --}}
    @if ($latestWeather)
        @php
            $weatherPayload = $latestWeather->payload_json ?? [];
            $temp = $weatherPayload['main']['temp'] ?? 'N/A';
            $humidity = $weatherPayload['main']['humidity'] ?? 'N/A';
            $wind = $weatherPayload['wind']['speed'] ?? 'N/A';
            $desc = $weatherPayload['weather'][0]['description'] ?? 'N/A';
        @endphp
        <section class="data-card">
            <div class="data-header">
                <div>
                    <h2>Korelasi Cuaca & Iklim Lahan</h2>
                    <p>Observasi data iklim saat ini dari AgroMonitoring / OpenWeather API</p>
                </div>
            </div>

            <div style="padding: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
                <div>
                    <span style="font-size: 15px; font-weight: 700; color: #0f172a; text-transform: capitalize;">Kondisi: {{ $desc }}</span><br>
                    <span style="font-size: 12px; color: #64748b;">Pengamatan terakhir: {{ $latestWeather->observed_at->format('d M Y H:i') }}</span>
                </div>

                <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                    <div style="background: #ffffff; padding: 12px 18px; border-radius: 12px; border: 1px solid #e2e8f0;">
                        <span style="font-size: 12px; color: #64748b; display: block;">Suhu Udara</span>
                        <strong style="font-size: 16px; color: #0f172a;">{{ $temp }} °C</strong>
                    </div>
                    <div style="background: #ffffff; padding: 12px 18px; border-radius: 12px; border: 1px solid #e2e8f0;">
                        <span style="font-size: 12px; color: #64748b; display: block;">Kelembaban Udara</span>
                        <strong style="font-size: 16px; color: #0f172a;">{{ $humidity }}%</strong>
                    </div>
                    <div style="background: #ffffff; padding: 12px 18px; border-radius: 12px; border: 1px solid #e2e8f0;">
                        <span style="font-size: 12px; color: #64748b; display: block;">Kecepatan Angin</span>
                        <strong style="font-size: 16px; color: #0f172a;">{{ $wind }} m/s</strong>
                    </div>
                </div>
            </div>
        </section>
    @endif
</div>
@endsection
