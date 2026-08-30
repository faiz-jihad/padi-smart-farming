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
            <a href="{{ route('admin.soil.report.pdf', $soilDetection) }}" class="btn-soil-action">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4m8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak Laporan</span>
            </a>

            <a href="{{ route('admin.soil.index') }}" class="btn-soil-action">
                <span>← Kembali ke Daftar</span>
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

    {{-- DEDICATED SECTION: SISTEM KOMPARASI & JADWAL IRIGASI PADI (3 SUMBER) --}}
    @php
        $comparisonService = app(\App\Services\Irrigation\IrrigationComparisonService::class);
        $irrigationAnalysis = $comparisonService->compareForFarm(
            $soilDetection->farm,
            $soilDetection
        );
        $systemRec = $irrigationAnalysis['system_recommendation'];
        $fieldSchedule = $irrigationAnalysis['field_schedule'];
        $officialContext = $irrigationAnalysis['official_context'];
        $irrigationSchedule = $irrigationAnalysis['irrigation_schedule'];
        $comparison = $irrigationAnalysis['comparison'];
    @endphp

    <section class="data-card" style="border: 2px solid #81c784; background: #ffffff; margin-bottom: 32px; box-shadow: 0 4px 12px rgba(22, 101, 52, 0.08); border-radius: 16px; overflow: hidden;">
        <div class="data-header" style="background: #f0fdf4; border-bottom: 1px solid #c8e6c9; padding: 20px 28px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div>
                <h2 style="color: #1b5e20; font-size: 22px; font-weight: 800; margin: 0 0 4px 0;">Sistem Analisis & Jadwal Irigasi Padi</h2>
                <p style="color: #166534; font-size: 13px; margin: 0;">Sintesis 3 Sumber Informasi: Rekomendasi Sistem (Sensor/AWD), Jadwal Lapangan (Raksa Bumi), dan Data Resmi PU/WRDC</p>
            </div>

            <div style="display: flex; gap: 10px; align-items: center;">
                <span class="soil-status-badge" style="font-size:13px; padding:6px 14px; background:{{ $comparison['badge_color'] }}; color:#ffffff; font-weight:700; border-radius: 8px;">
                    {{ $comparison['status_label'] }}
                </span>
            </div>
        </div>

        <div style="padding: 28px;">
            {{-- HERO SECTION: JADWAL IRIGASI KONKRET --}}
            <div style="background: {{ $comparison['bg_color'] }}; border: 2px solid {{ $comparison['border_color'] }}; padding: 24px 28px; border-radius: 14px; margin-bottom: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 14px; margin-bottom: 12px;">
                    <div>
                        <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: {{ $comparison['badge_color'] }}; letter-spacing: 0.05em; display: block; margin-bottom: 4px;">
                            {{ $irrigationSchedule['display_badge'] }} — {{ strtoupper($comparison['status_label']) }}
                        </span>
                        <h3 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0 0 4px 0;">
                            {{ $irrigationSchedule['date_formatted'] }} ({{ $irrigationSchedule['time_range'] }})
                        </h3>
                        <p style="margin: 0; font-size: 14px; color: #475569;">
                            Sumber Penetapan: <strong style="color:#0f172a;">{{ $irrigationSchedule['source_label'] }}</strong>
                            @if(!empty($irrigationSchedule['officer_name']))
                                | Petugas: <strong style="color:#0f172a;">{{ $irrigationSchedule['officer_name'] }}</strong>
                            @endif
                        </p>
                    </div>

                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        @if($fieldSchedule['has_schedule'])
                            <button type="button" onclick="openIrrigationModal('modalEditSchedule')" class="btn-soil-action" style="background:#0284c7; color:#ffffff; border:none; padding:8px 14px; font-weight:700; border-radius:8px; cursor:pointer;">
                                ✏ Edit Jadwal
                            </button>
                            <form action="{{ route('admin.irrigation-schedules.destroy', $fieldSchedule['schedule_id']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal irigasi lapangan ini?')" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="soil_detection_id" value="{{ $soilDetection->sample_code }}">
                                <button type="submit" class="btn-soil-action" style="background:#ef4444; color:#ffffff; border:none; padding:8px 14px; font-weight:700; border-radius:8px; cursor:pointer;">
                                    🗑 Hapus
                                </button>
                            </form>
                        @else
                            <button type="button" onclick="openIrrigationModal('modalInputSchedule')" class="btn-soil-action" style="background:#166534; color:#ffffff; border:none; padding:10px 18px; font-weight:700; border-radius:8px; cursor:pointer; box-shadow: 0 2px 6px rgba(22, 101, 52, 0.2);">
                                ➕ Input Jadwal Irigasi
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Parameter Genangan & Volume Air --}}
                <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin: 16px 0; background: #ffffff; padding: 16px; border-radius: 10px; border: 1px solid {{ $comparison['border_color'] }};">
                    <div>
                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Target Genangan</span>
                        <strong style="font-size: 16px; color: #166534;">{{ $irrigationSchedule['target_water_depth'] }}</strong>
                    </div>
                    <div>
                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Estimasi Volume Air</span>
                        <strong style="font-size: 16px; color: #166534;">{{ $irrigationSchedule['water_volume'] }}</strong>
                    </div>
                    <div>
                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Petak Irigasi</span>
                        <strong style="font-size: 14px; color: #0f172a;">{{ $irrigationSchedule['irrigation_block'] }}</strong>
                    </div>
                    <div>
                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Sumber Saluran</span>
                        <strong style="font-size: 14px; color: #0f172a;">{{ Str::limit($irrigationSchedule['water_source'], 25) }}</strong>
                    </div>
                </div>

                {{-- Headline & Rekomendasi Tindakan --}}
                <div style="font-size: 13.5px; color: #334155; line-height: 1.6; margin-top: 8px;">
                    <p style="margin: 0 0 6px 0;"><strong>Analisis:</strong> {{ $comparison['explanation'] }}</p>
                    <p style="margin: 0; color: #166534; font-weight: 700;"><strong>Arahan Tindakan:</strong> {{ $comparison['final_recommendation'] }}</p>
                </div>
            </div>

            {{-- 3 KARTU SUMBER INFORMASI INTERAKTIF (CLICKABLE) --}}
            <div style="margin-bottom: 8px; display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em;">3 SUMBER INFORMASI IRIGASI (KLIK KARTU UNTUK LIHAT DETAIL LENGKAP)</span>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; margin-bottom: 24px;">
                {{-- KARTU 1: REKOMENDASI SISTEM --}}
                <div onclick="openIrrigationModal('modalSystemRec')" class="interactive-source-card" style="cursor: pointer; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 14px; padding: 20px; transition: all 0.2s ease; display:flex; flex-direction:column; justify-content:space-between;">
                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                            <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #166534;">1. REKOMENDASI SISTEM</span>
                            <span style="font-size: 10px; font-weight: 700; background: #e8f5e9; color: #166534; padding: 2px 6px; border-radius: 4px;">Sensor & Satelit</span>
                        </div>
                        <h4 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 8px 0;">{{ $systemRec['status_label'] }}</h4>
                        <ul style="margin: 0; padding-left: 18px; font-size: 12px; color: #475569; line-height: 1.6;">
                            <li>Kelembaban: <strong>{{ number_format($soilDetection->moisture_percentage, 1) }}%</strong></li>
                            <li>Target Genangan: <strong>{{ $systemRec['target_water_depth'] }}</strong></li>
                            <li>Kebutuhan Volume: <strong>{{ $systemRec['water_volume'] }}</strong></li>
                            <li>Waktu Ideal: <strong>{{ $systemRec['recommended_time_slot'] }}</strong></li>
                        </ul>
                    </div>
                    <div style="margin-top:14px; padding-top:10px; border-top:1px dashed #e2e8f0; font-size: 11px; color: #166534; font-weight: 700; display:flex; justify-content:space-between; align-items:center;">
                        <span>🔍 Buka Detail Evaluasi</span>
                        <span>&rarr;</span>
                    </div>
                </div>

                {{-- KARTU 2: JADWAL LAPANGAN / RAKSA BUMI --}}
                <div onclick="openIrrigationModal('{{ $fieldSchedule['has_schedule'] ? 'modalFieldDetail' : 'modalInputSchedule' }}')" class="interactive-source-card" style="cursor: pointer; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 14px; padding: 20px; transition: all 0.2s ease; display:flex; flex-direction:column; justify-content:space-between;">
                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                            <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #0284c7;">2. JADWAL LAPANGAN</span>
                            <span style="font-size: 10px; font-weight: 700; background: #e0f2fe; color: #0284c7; padding: 2px 6px; border-radius: 4px;">Raksa Bumi</span>
                        </div>
                        @if ($fieldSchedule['has_schedule'])
                            <h4 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 8px 0;">{{ $fieldSchedule['schedule_date_formatted'] }}</h4>
                            <ul style="margin: 0; padding-left: 18px; font-size: 12px; color: #475569; line-height: 1.6;">
                                <li>Waktu: <strong>{{ $fieldSchedule['time_range'] }}</strong></li>
                                <li>Sumber: <strong>{{ $fieldSchedule['source_label'] }}</strong></li>
                                <li>Petugas: <strong>{{ $fieldSchedule['officer_name'] ?? 'Petugas Desa' }}</strong></li>
                                <li>Petak: <strong>{{ $fieldSchedule['irrigation_block'] ?? 'Tersier Utama' }}</strong></li>
                            </ul>
                        @else
                            <h4 style="font-size: 15px; font-weight: 700; color: #b91c1c; margin: 0 0 8px 0;">Belum Ada Jadwal</h4>
                            <p style="font-size: 12px; color: #64748b; margin: 0; line-height: 1.5;">
                                Tidak ada jadwal lapangan aktif di database. Klik untuk input jadwal Raksa Bumi.
                            </p>
                        @endif
                    </div>
                    <div style="margin-top:14px; padding-top:10px; border-top:1px dashed #e2e8f0; font-size: 11px; color: #0284c7; font-weight: 700; display:flex; justify-content:space-between; align-items:center;">
                        <span>{{ $fieldSchedule['has_schedule'] ? '🔍 Lihat / Kelola Jadwal' : '➕ Input Jadwal Irigasi' }}</span>
                        <span>&rarr;</span>
                    </div>
                </div>

                {{-- KARTU 3: DATA RESMI PU / WRDC / BBWS --}}
                <div onclick="openIrrigationModal('modalOfficialContext')" class="interactive-source-card" style="cursor: pointer; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 14px; padding: 20px; transition: all 0.2s ease; display:flex; flex-direction:column; justify-content:space-between;">
                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                            <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #b45309;">3. DATA RESMI PU / WRDC</span>
                            <span style="font-size: 10px; font-weight: 700; background: #fef3c7; color: #b45309; padding: 2px 6px; border-radius: 4px;">Ditjen SDA</span>
                        </div>
                        <h4 style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 0 0 8px 0;">{{ Str::limit($officialContext['daerah_irigasi'], 32) }}</h4>
                        <ul style="margin: 0; padding-left: 18px; font-size: 12px; color: #475569; line-height: 1.6;">
                            <li>Balai: <strong>{{ Str::limit($officialContext['bbws_bws'], 28) }}</strong></li>
                            <li>Kewenangan: <strong>{{ Str::limit($officialContext['authority'], 28) }}</strong></li>
                            <li>Sumber: <strong>{{ Str::limit($officialContext['primary_source'], 28) }}</strong></li>
                            <li>Status: <strong>{{ $officialContext['water_supply_status'] }}</strong></li>
                        </ul>
                    </div>
                    <div style="margin-top:14px; padding-top:10px; border-top:1px dashed #e2e8f0; font-size: 11px; color: #b45309; font-weight: 700; display:flex; justify-content:space-between; align-items:center;">
                        <span>🔍 Buka Detail Infrastruktur</span>
                        <span>&rarr;</span>
                    </div>
                </div>
            </div>

            {{-- DAFTAR ITEM TINDAKAN --}}
            <div style="background: #ffffff; border-left: 5px solid #1b5e20; padding: 16px 20px; border-radius: 10px; border: 1px solid #c8e6c9; border-left-width: 5px;">
                <span style="font-weight: 700; font-size: 14px; color: #0f172a; display: block; margin-bottom: 6px;">Langkah Tindakan Lapangan Direkomendasikan:</span>
                <ul style="margin: 0; padding-left: 20px; font-size: 13px; color: #334155; line-height: 1.6;">
                    @foreach($comparison['action_items'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- MODAL 1: DETAIL REKOMENDASI SISTEM --}}
    {{-- ========================================================================= --}}
    <div id="modalSystemRec" class="irrigation-modal-backdrop" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.65); backdrop-filter:blur(4px); align-items:center; justify-content:center;">
        <div class="irrigation-modal-content" style="background:#ffffff; border-radius:16px; max-width:680px; width:90%; max-height:90vh; overflow-y:auto; padding:28px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.3); border:1px solid #cbd5e1;">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e2e8f0; padding-bottom:14px; margin-bottom:18px;">
                <div>
                    <span style="font-size:11px; font-weight:800; color:#166534; text-transform:uppercase;">SUMBER 1: REKOMENDASI SISTEM</span>
                    <h3 style="margin:2px 0 0 0; font-size:20px; font-weight:800; color:#0f172a;">Detail Evaluasi Agronomi & Sistem AWD/SRI</h3>
                </div>
                <button type="button" onclick="closeIrrigationModal('modalSystemRec')" style="background:none; border:none; font-size:22px; cursor:pointer; color:#64748b;">&times;</button>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                <div style="background:#f0fdf4; padding:14px; border-radius:10px; border:1px solid #bbf7d0;">
                    <span style="font-size:11px; font-weight:700; color:#166534; text-transform:uppercase; display:block;">Kelembaban Tanah</span>
                    <strong style="font-size:22px; color:#166534;">{{ number_format($soilDetection->moisture_percentage, 1) }}%</strong>
                    <span style="font-size:11px; color:#475569; display:block;">Status: {{ $systemRec['status_label'] }}</span>
                </div>
                <div style="background:#f0fdf4; padding:14px; border-radius:10px; border:1px solid #bbf7d0;">
                    <span style="font-size:11px; font-weight:700; color:#166534; text-transform:uppercase; display:block;">Suhu Tanah</span>
                    <strong style="font-size:22px; color:#166534;">{{ $soilDetection->soil_temp_celsius ? number_format($soilDetection->soil_temp_celsius, 1) . ' °C' : '27.0 °C (Standar)' }}</strong>
                    <span style="font-size:11px; color:#475569; display:block;">Metode: {{ $systemRec['recommended_time_slot'] }}</span>
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <h4 style="font-size:14px; font-weight:700; color:#0f172a; margin:0 0 6px 0;">Parameter Kebutuhan Air Tanaman:</h4>
                <table style="width:100%; border-collapse:collapse; font-size:13px;">
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:8px 0; color:#64748b;">Target Ketinggian Genangan</td>
                        <td style="padding:8px 0; font-weight:700; color:#0f172a; text-align:right;">{{ $systemRec['target_water_depth'] }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:8px 0; color:#64748b;">Estimasi Volume Kebutuhan Air</td>
                        <td style="padding:8px 0; font-weight:700; color:#0f172a; text-align:right;">{{ $systemRec['water_volume'] }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:8px 0; color:#64748b;">Waktu Pengairan Ideal</td>
                        <td style="padding:8px 0; font-weight:700; color:#0f172a; text-align:right;">{{ $systemRec['recommended_time_slot'] }} (Minim Evaporasi)</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0; color:#64748b;">Estimasi Gilir Pengairan Berikutnya</td>
                        <td style="padding:8px 0; font-weight:700; color:#0f172a; text-align:right;">{{ $systemRec['next_schedule'] }}</td>
                    </tr>
                </table>
            </div>

            <div style="background:#f8fafc; border-left:4px solid #166534; padding:14px 18px; border-radius:8px; margin-bottom:20px;">
                <strong style="font-size:13px; color:#0f172a; display:block; margin-bottom:4px;">Dasar Rekomendasi & Petunjuk Agronomi Lengkap:</strong>
                <p style="margin:0; font-size:13px; color:#334155; line-height:1.6;">
                    {{ $systemRec['action_recommendation'] }}
                </p>
            </div>

            <div style="text-align:right;">
                <button type="button" onclick="closeIrrigationModal('modalSystemRec')" style="background:#e2e8f0; color:#334155; border:none; padding:8px 18px; border-radius:8px; font-weight:700; cursor:pointer;">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL 2: FORM INPUT JADWAL IRIGASI BARU --}}
    {{-- ========================================================================= --}}
    <div id="modalInputSchedule" class="irrigation-modal-backdrop" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.65); backdrop-filter:blur(4px); align-items:center; justify-content:center;">
        <div class="irrigation-modal-content" style="background:#ffffff; border-radius:16px; max-width:620px; width:90%; max-height:90vh; overflow-y:auto; padding:28px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.3); border:1px solid #cbd5e1;">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e2e8f0; padding-bottom:14px; margin-bottom:18px;">
                <div>
                    <span style="font-size:11px; font-weight:800; color:#0284c7; text-transform:uppercase;">JADWAL LAPANGAN</span>
                    <h3 style="margin:2px 0 0 0; font-size:20px; font-weight:800; color:#0f172a;">Input Jadwal Gilir Air Irigasi</h3>
                </div>
                <button type="button" onclick="closeIrrigationModal('modalInputSchedule')" style="background:none; border:none; font-size:22px; cursor:pointer; color:#64748b;">&times;</button>
            </div>

            <form action="{{ route('admin.irrigation-schedules.store') }}" method="POST">
                @csrf
                <input type="hidden" name="farm_id" value="{{ $soilDetection->farm_id }}">
                <input type="hidden" name="soil_detection_id" value="{{ $soilDetection->sample_code }}">
                <input type="hidden" name="soil_id" value="{{ $soilDetection->id }}">

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Tanggal Pengairan *</label>
                        <input type="date" name="schedule_date" value="{{ $irrigationSchedule['date'] ?? now()->format('Y-m-d') }}" required style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Sumber Penetapan Jadwal *</label>
                        <select name="source" required style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                            <option value="raksa_bumi" selected>Petugas Raksa Bumi Desa</option>
                            <option value="manual">Petani (Input Mandiri)</option>
                            <option value="officer">Penyuluh Pertanian Lapangan (PPL)</option>
                            <option value="system">Rekomendasi Sistem AI</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Jam Mulai</label>
                        <input type="time" name="start_time" value="06:00" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Jam Selesai</label>
                        <input type="time" name="end_time" value="08:30" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Nama Petugas / Raksa Bumi</label>
                        <input type="text" name="officer_name" placeholder="Contoh: Pak Raksa Subarkah" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Blok / Petak Irigasi Tersier</label>
                        <input type="text" name="irrigation_block" placeholder="Contoh: Blok Tersier Barat 02" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Sumber Air / Saluran Irigasi</label>
                    <input type="text" name="water_source" value="{{ $officialContext['primary_source'] !== 'Belum tersedia dari sumber resmi' ? $officialContext['primary_source'] : 'Saluran Sekunder/Tersier Desa' }}" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Catatan Tambahan (Opsional)</label>
                    <textarea name="notes" rows="2" placeholder="Catatan kondisi pembagian air atau instruksi khusus..." style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; resize:vertical;"></textarea>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" onclick="closeIrrigationModal('modalInputSchedule')" style="background:#e2e8f0; color:#334155; border:none; padding:9px 16px; border-radius:8px; font-weight:700; cursor:pointer;">
                        Batal
                    </button>
                    <button type="submit" style="background:#166534; color:#ffffff; border:none; padding:9px 20px; border-radius:8px; font-weight:700; cursor:pointer;">
                        Simpan Jadwal Irigasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL 2B: DETAIL / EDIT JADWAL LAPANGAN --}}
    {{-- ========================================================================= --}}
    @if($fieldSchedule['has_schedule'])
    <div id="modalEditSchedule" class="irrigation-modal-backdrop" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.65); backdrop-filter:blur(4px); align-items:center; justify-content:center;">
        <div class="irrigation-modal-content" style="background:#ffffff; border-radius:16px; max-width:620px; width:90%; max-height:90vh; overflow-y:auto; padding:28px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.3); border:1px solid #cbd5e1;">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e2e8f0; padding-bottom:14px; margin-bottom:18px;">
                <div>
                    <span style="font-size:11px; font-weight:800; color:#0284c7; text-transform:uppercase;">JADWAL LAPANGAN</span>
                    <h3 style="margin:2px 0 0 0; font-size:20px; font-weight:800; color:#0f172a;">Edit Jadwal Irigasi</h3>
                </div>
                <button type="button" onclick="closeIrrigationModal('modalEditSchedule')" style="background:none; border:none; font-size:22px; cursor:pointer; color:#64748b;">&times;</button>
            </div>

            <form action="{{ route('admin.irrigation-schedules.update', $fieldSchedule['schedule_id']) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="soil_detection_id" value="{{ $soilDetection->sample_code }}">
                <input type="hidden" name="soil_id" value="{{ $soilDetection->id }}">

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Tanggal Pengairan *</label>
                        <input type="date" name="schedule_date" value="{{ $fieldSchedule['schedule_date'] }}" required style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Sumber Penetapan</label>
                        <select name="source" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                            <option value="raksa_bumi" {{ $fieldSchedule['source'] === 'raksa_bumi' ? 'selected' : '' }}>Petugas Raksa Bumi</option>
                            <option value="manual" {{ $fieldSchedule['source'] === 'manual' ? 'selected' : '' }}>Petani (Input Mandiri)</option>
                            <option value="officer" {{ $fieldSchedule['source'] === 'officer' ? 'selected' : '' }}>Penyuluh Lapangan (PPL)</option>
                            <option value="system" {{ $fieldSchedule['source'] === 'system' ? 'selected' : '' }}>Rekomendasi Sistem</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Jam Mulai</label>
                        <input type="time" name="start_time" value="{{ $fieldSchedule['start_time'] }}" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Jam Selesai</label>
                        <input type="time" name="end_time" value="{{ $fieldSchedule['end_time'] }}" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Nama Petugas / Raksa Bumi</label>
                        <input type="text" name="officer_name" value="{{ $fieldSchedule['officer_name'] }}" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Blok / Petak Irigasi</label>
                        <input type="text" name="irrigation_block" value="{{ $fieldSchedule['irrigation_block'] }}" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Sumber Air / Saluran</label>
                    <input type="text" name="water_source" value="{{ $fieldSchedule['water_source'] }}" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Catatan</label>
                    <textarea name="notes" rows="2" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">{{ $fieldSchedule['notes'] }}</textarea>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" onclick="closeIrrigationModal('modalEditSchedule')" style="background:#e2e8f0; color:#334155; border:none; padding:9px 16px; border-radius:8px; font-weight:700; cursor:pointer;">
                        Batal
                    </button>
                    <button type="submit" style="background:#0284c7; color:#ffffff; border:none; padding:9px 20px; border-radius:8px; font-weight:700; cursor:pointer;">
                        Perbarui Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL DETAIL JADWAL --}}
    <div id="modalFieldDetail" class="irrigation-modal-backdrop" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.65); backdrop-filter:blur(4px); align-items:center; justify-content:center;">
        <div class="irrigation-modal-content" style="background:#ffffff; border-radius:16px; max-width:620px; width:90%; max-height:90vh; overflow-y:auto; padding:28px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.3); border:1px solid #cbd5e1;">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e2e8f0; padding-bottom:14px; margin-bottom:18px;">
                <div>
                    <span style="font-size:11px; font-weight:800; color:#0284c7; text-transform:uppercase;">SUMBER 2: JADWAL LAPANGAN</span>
                    <h3 style="margin:2px 0 0 0; font-size:20px; font-weight:800; color:#0f172a;">Detail Jadwal Gilir Air Lapangan</h3>
                </div>
                <button type="button" onclick="closeIrrigationModal('modalFieldDetail')" style="background:none; border:none; font-size:22px; cursor:pointer; color:#64748b;">&times;</button>
            </div>

            <div style="background:#f0f9ff; border:1px solid #bae6fd; padding:18px; border-radius:12px; margin-bottom:18px;">
                <span style="font-size:12px; font-weight:800; color:#0284c7; text-transform:uppercase; display:block;">Waktu Gilir Air</span>
                <h4 style="font-size:22px; font-weight:800; color:#0f172a; margin:4px 0;">{{ $fieldSchedule['schedule_date_formatted'] }}</h4>
                <p style="margin:0; font-size:14px; color:#0369a1; font-weight:700;">{{ $fieldSchedule['time_range'] }}</p>
            </div>

            <table style="width:100%; border-collapse:collapse; font-size:13px; margin-bottom:20px;">
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:#64748b;">Sumber Input</td>
                    <td style="padding:8px 0; font-weight:700; color:#0f172a; text-align:right;">{{ $fieldSchedule['source_label'] }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:#64748b;">Nama Petugas / Raksa Bumi</td>
                    <td style="padding:8px 0; font-weight:700; color:#0f172a; text-align:right;">{{ $fieldSchedule['officer_name'] ?? 'Petugas Desa' }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:#64748b;">Blok / Petak Irigasi</td>
                    <td style="padding:8px 0; font-weight:700; color:#0f172a; text-align:right;">{{ $fieldSchedule['irrigation_block'] ?? 'Tersier Utama' }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:#64748b;">Sumber Saluran Air</td>
                    <td style="padding:8px 0; font-weight:700; color:#0f172a; text-align:right;">{{ $fieldSchedule['water_source'] ?? 'Saluran Tersier' }}</td>
                </tr>
                @if(!empty($fieldSchedule['notes']))
                <tr>
                    <td style="padding:8px 0; color:#64748b;">Catatan Lapangan</td>
                    <td style="padding:8px 0; color:#0f172a; text-align:right;">{{ $fieldSchedule['notes'] }}</td>
                </tr>
                @endif
            </table>

            <div style="display:flex; justify-content:space-between; align-items:center;">
                <button type="button" onclick="closeIrrigationModal('modalFieldDetail'); openIrrigationModal('modalEditSchedule');" style="background:#0284c7; color:#ffffff; border:none; padding:8px 16px; border-radius:8px; font-weight:700; cursor:pointer;">
                    ✏ Edit Jadwal Ini
                </button>
                <button type="button" onclick="closeIrrigationModal('modalFieldDetail')" style="background:#e2e8f0; color:#334155; border:none; padding:8px 18px; border-radius:8px; font-weight:700; cursor:pointer;">
                    Tutup
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL 3: DATA RESMI PU / WRDC / BBWS --}}
    {{-- ========================================================================= --}}
    <div id="modalOfficialContext" class="irrigation-modal-backdrop" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.65); backdrop-filter:blur(4px); align-items:center; justify-content:center;">
        <div class="irrigation-modal-content" style="background:#ffffff; border-radius:16px; max-width:680px; width:90%; max-height:90vh; overflow-y:auto; padding:28px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.3); border:1px solid #cbd5e1;">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e2e8f0; padding-bottom:14px; margin-bottom:18px;">
                <div>
                    <span style="font-size:11px; font-weight:800; color:#b45309; text-transform:uppercase;">SUMBER 3: DATA RESMI DITJEN SDA PU / WRDC</span>
                    <h3 style="margin:2px 0 0 0; font-size:20px; font-weight:800; color:#0f172a;">Konteks Daerah Irigasi & Balai Wilayah Sungai</h3>
                </div>
                <button type="button" onclick="closeIrrigationModal('modalOfficialContext')" style="background:none; border:none; font-size:22px; cursor:pointer; color:#64748b;">&times;</button>
            </div>

            <div style="background:#fef3c7; border:1px solid #fde68a; padding:16px 20px; border-radius:12px; margin-bottom:18px;">
                <span style="font-size:11px; font-weight:800; color:#92400e; text-transform:uppercase; display:block;">Nama Daerah Irigasi (DI)</span>
                <h4 style="font-size:20px; font-weight:800; color:#0f172a; margin:4px 0 2px 0;">{{ $officialContext['daerah_irigasi'] }}</h4>
                <p style="margin:0; font-size:12px; color:#78350f;">Pengelola Wilayah: <strong>{{ $officialContext['bbws_bws'] }}</strong></p>
            </div>

            <table style="width:100%; border-collapse:collapse; font-size:13px; margin-bottom:20px;">
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:#64748b;">Tingkat Kewenangan</td>
                    <td style="padding:8px 0; font-weight:700; color:#0f172a; text-align:right;">{{ $officialContext['authority'] }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:#64748b;">Sumber Pasokan Utama</td>
                    <td style="padding:8px 0; font-weight:700; color:#0f172a; text-align:right;">{{ $officialContext['primary_source'] }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:#64748b;">Tipe Skema Irigasi</td>
                    <td style="padding:8px 0; font-weight:700; color:#0f172a; text-align:right;">{{ $officialContext['scheme_type'] }}</td>
                </tr>
                @if(!empty($officialContext['service_area_ha']))
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:#64748b;">Luas Areal Layanan Irigasi</td>
                    <td style="padding:8px 0; font-weight:700; color:#0f172a; text-align:right;">{{ number_format($officialContext['service_area_ha'], 0, ',', '.') }} Ha</td>
                </tr>
                @endif
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 0; color:#64748b;">Status Ketersediaan Pasokan Air</td>
                    <td style="padding:8px 0; font-weight:700; color:#166534; text-align:right;">{{ $officialContext['water_supply_status'] }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#64748b;">Status Konektivitas API</td>
                    <td style="padding:8px 0; font-weight:700; color:#0f172a; text-align:right;">
                        {{ $officialContext['is_live_api'] ? 'Live REST API WRDC' : 'Basis Data Referensi Daerah Irigasi Terverifikasi' }}
                    </td>
                </tr>
            </table>

            <div style="background:#f8fafc; border-left:4px solid #b45309; padding:14px 18px; border-radius:8px; margin-bottom:20px;">
                <strong style="font-size:12px; color:#0f172a; display:block; margin-bottom:2px;">Catatan Resmi & Transparansi Integrasi Data:</strong>
                <p style="margin:0; font-size:12px; color:#475569; line-height:1.5;">
                    {{ $officialContext['notice'] }}
                </p>
            </div>

            <div style="text-align:right;">
                <button type="button" onclick="closeIrrigationModal('modalOfficialContext')" style="background:#e2e8f0; color:#334155; border:none; padding:8px 18px; border-radius:8px; font-weight:700; cursor:pointer;">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- VANILLA JAVASCRIPT FOR MODALS & CARD HOVER --}}
    <script>
        function openIrrigationModal(modalId) {
            var modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function closeIrrigationModal(modalId) {
            var modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        // Close on background click
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('irrigation-modal-backdrop')) {
                event.target.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    </script>

    <style>
        .interactive-source-card:hover {
            transform: translateY(-3px);
            border-color: #166534 !important;
            box-shadow: 0 8px 16px rgba(0,0,0,0.06);
        }
    </style>

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
