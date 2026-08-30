<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>
        Laporan Analisis Kualitas Tanah - {{ $soilDetection->sample_code }}
    </title>

    <style>
        @page {
            size: A4;
            margin: 35px 40px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 10px;
            line-height: 1.5;
        }

        .header {
            border-bottom: 2px solid #1b5e20;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }

        .brand {
            font-size: 22px;
            font-weight: bold;
            color: #1b5e20;
            margin: 0;
        }

        .brand-subtitle {
            color: #64748b;
            font-size: 9px;
            margin-top: 2px;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0 4px;
        }

        .report-code {
            font-size: 10px;
            color: #64748b;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 5px 0;
            vertical-align: top;
        }

        .info-label {
            width: 25%;
            color: #64748b;
            font-weight: bold;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1b5e20;
            border-left: 4px solid #1b5e20;
            padding-left: 8px;
            margin: 20px 0 10px;
        }

        .status-box {
            border: 1px solid #81c784;
            padding: 14px;
            margin-bottom: 15px;
        }

        .status-label {
            font-size: 8px;
            text-transform: uppercase;
            color: #1b5e20;
            font-weight: bold;
        }

        .status-title {
            font-size: 14px;
            font-weight: bold;
            margin: 3px 0;
        }

        .status-meta {
            color: #64748b;
            font-size: 9px;
        }

        .score-box {
            text-align: center;
            border: 1px solid #81c784;
            padding: 10px;
            margin-top: 10px;
        }

        .score-label {
            display: block;
            color: #1b5e20;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .score {
            font-size: 26px;
            font-weight: bold;
            color: #1b5e20;
        }

        .score-max {
            color: #64748b;
            font-size: 11px;
        }

        .parameter-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .parameter-table th {
            background: #f0fdf4;
            color: #1b5e20;
            font-weight: bold;
            text-align: left;
            padding: 8px;
            border: 1px solid #c8e6c9;
        }

        .parameter-table td {
            padding: 8px;
            border: 1px solid #d1d5db;
        }

        .value {
            font-weight: bold;
        }

        .irrigation-box {
            border: 1px solid #81c784;
            padding: 14px;
        }

        .irrigation-header {
            font-size: 13px;
            font-weight: bold;
            color: #1b5e20;
            margin-bottom: 10px;
        }

        .irrigation-table {
            width: 100%;
            border-collapse: collapse;
        }

        .irrigation-table td {
            width: 50%;
            border: 1px solid #d1d5db;
            padding: 9px;
            vertical-align: top;
        }

        .item-label {
            color: #64748b;
            font-size: 8px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .item-value {
            display: block;
            font-size: 11px;
            font-weight: bold;
            margin-top: 3px;
        }

        .recommendation {
            border: 1px solid #c8e6c9;
            border-left: 4px solid #1b5e20;
            padding: 10px 12px;
            margin-bottom: 8px;
        }

        .recommendation-title {
            font-weight: bold;
            font-size: 10px;
        }

        .recommendation-category {
            color: #1b5e20;
            font-size: 8px;
            font-weight: bold;
        }

        .recommendation-action {
            margin-top: 4px;
            color: #334155;
        }

        .weather-table {
            width: 100%;
            border-collapse: collapse;
        }

        .weather-table td {
            border: 1px solid #d1d5db;
            padding: 9px;
            width: 33.33%;
        }

        .action-box {
            border: 1px solid #c8e6c9;
            border-left: 4px solid #1b5e20;
            padding: 12px;
            margin-top: 12px;
        }

        .footer {
            margin-top: 25px;
            padding-top: 10px;
            border-top: 1px solid #d1d5db;
            text-align: center;
            color: #64748b;
            font-size: 8px;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <div class="header">
        <div class="brand">P.A.D.I.</div>

        <div class="brand-subtitle">
            Predictive Agriculture & Disease Intelligence
        </div>

        <div class="report-title">
            LAPORAN ANALISIS KUALITAS TANAH
        </div>

        <div class="report-code">
            Nomor Sampel: {{ $soilDetection->sample_code }}
        </div>
    </div>


    {{-- INFORMASI SAMPEL --}}
    <table class="info-table">
        <tr>
            <td class="info-label">Lahan</td>
            <td>{{ $soilDetection->farm?->name ?? 'Lahan Tidak Ditemukan' }}</td>
        </tr>

        <tr>
            <td class="info-label">Pemilik</td>
            <td>{{ $soilDetection->farm?->farmer?->name ?? '-' }}</td>
        </tr>

        <tr>
            <td class="info-label">Tanggal Uji</td>
            <td>{{ $soilDetection->tested_at->format('d M Y H:i') }}</td>
        </tr>

        <tr>
            <td class="info-label">Jenis Tanah</td>
            <td>{{ ucfirst($soilDetection->soil_type) }}</td>
        </tr>

        <tr>
            <td class="info-label">Suhu Tanah</td>
            <td>
                {{ $soilDetection->soil_temp_celsius
                    ? number_format($soilDetection->soil_temp_celsius, 1) . ' °C'
                    : 'Data AgroMonitoring'
                }}
            </td>
        </tr>

        <tr>
            <td class="info-label">Dianalisis Oleh</td>
            <td>{{ $soilDetection->creator?->name ?? 'Sistem AI P.A.D.I' }}</td>
        </tr>
    </table>


    {{-- HASIL EVALUASI --}}
    <div class="section-title">
        HASIL EVALUASI KUALITAS TANAH
    </div>

    <div class="status-box">

        <div class="status-label">
            Kondisi Tanah
        </div>

        <div class="status-title">
            @if ($soilDetection->soil_status === 'optimal')
                Kondisi Tanah Subur & Optimal Untuk Tanaman Padi

            @elseif ($soilDetection->soil_status === 'needs_fertilizer')
                Tanah Membutuhkan Penambahan Unsur Hara / Pemupukan

            @elseif ($soilDetection->soil_status === 'warning')
                Perlu Perhatian & Pengkondisian Kesuburan Tanah

            @else
                Kondisi Tanah Kritis — Membutuhkan Penanganan Segera
            @endif
        </div>

        <div class="score-box">
            <span class="score-label">
                Skor Kesehatan Tanah
            </span>

            <span class="score">
                {{ $soilDetection->soil_health_score }}
            </span>

            <span class="score-max">
                /100
            </span>
        </div>

    </div>


    {{-- PARAMETER TANAH --}}
    <div class="section-title">
        PARAMETER KUALITAS TANAH
    </div>

    <table class="parameter-table">
        <thead>
            <tr>
                <th>Parameter</th>
                <th>Nilai</th>
                <th>Rentang Acuan</th>
            </tr>
        </thead>

        <tbody>

            <tr>
                <td>Derajat Keasaman (pH)</td>
                <td class="value">
                    {{ number_format($soilDetection->ph_level, 1) }}
                </td>
                <td>
                    Ideal 5.5 – 7.0
                </td>
            </tr>

            <tr>
                <td>Nitrogen (N)</td>
                <td class="value">
                    {{ number_format($soilDetection->nitrogen_ppm, 0) }} ppm
                </td>
                <td>
                    Ideal 100 – 180 ppm
                </td>
            </tr>

            <tr>
                <td>Fosfor (P)</td>
                <td class="value">
                    {{ number_format($soilDetection->phosphorus_ppm, 0) }} ppm
                </td>
                <td>
                    Ideal 15 – 35 ppm
                </td>
            </tr>

            <tr>
                <td>Kalium (K)</td>
                <td class="value">
                    {{ number_format($soilDetection->potassium_ppm, 0) }} ppm
                </td>
                <td>
                    Ideal 120 – 200 ppm
                </td>
            </tr>

            <tr>
                <td>Kelembaban Tanah</td>
                <td class="value">
                    {{ number_format($soilDetection->moisture_percentage, 1) }}%
                </td>
                <td>
                    Ideal Padi 45 – 75%
                </td>
            </tr>

            <tr>
                <td>Bahan Organik</td>
                <td class="value">
                    {{ number_format($soilDetection->organic_matter_percentage, 1) }}%
                </td>
                <td>
                    Ideal ≥ 2.0%
                </td>
            </tr>

        </tbody>
    </table>


    {{-- IRIGASI --}}
    <div class="section-title">
        JADWAL & REKOMENDASI PENGAIRAN IRIGASI PADI
    </div>

    <div class="irrigation-box">

        <div class="irrigation-header">
            {{ $irrigation['status_label'] }}
        </div>

        <table class="irrigation-table">

            <tr>
                <td>
                    <span class="item-label">
                        Jadwal Pengairan
                    </span>

                    <span class="item-value">
                        {{ $irrigation['exact_date_time'] }}
                    </span>
                </td>

                <td>
                    <span class="item-label">
                        Slot Waktu
                    </span>

                    <span class="item-value">
                        {{ $irrigation['recommended_time_slot'] }}
                    </span>
                </td>
            </tr>

            <tr>
                <td>
                    <span class="item-label">
                        Target Kedalaman Air
                    </span>

                    <span class="item-value">
                        {{ $irrigation['target_water_depth'] }}
                    </span>
                </td>

                <td>
                    <span class="item-label">
                        Estimasi Volume Air
                    </span>

                    <span class="item-value">
                        {{ $irrigation['water_volume'] }}
                    </span>
                </td>
            </tr>

            <tr>
                <td>
                    <span class="item-label">
                        Irigasi Berikutnya
                    </span>

                    <span class="item-value">
                        {{ $irrigation['next_schedule'] }}
                    </span>
                </td>

                <td>
                    <span class="item-label">
                        Kelembaban Saat Ini
                    </span>

                    <span class="item-value">
                        {{ number_format($soilDetection->moisture_percentage, 1) }}%
                    </span>
                </td>
            </tr>

        </table>

        <div class="action-box">
            <strong>Petunjuk Tindakan Irigasi</strong>

            <div>
                {{ $irrigation['action_recommendation'] }}
            </div>
        </div>

    </div>


    {{-- REKOMENDASI AGRONOMI --}}
    <div class="section-title">
        REKOMENDASI PEMUPUKAN & PERBAIKAN LAHAN
    </div>

    @forelse($soilDetection->recommendations_json ?? [] as $rec)

        <div class="recommendation">

            <div class="recommendation-title">
                {{ $rec['title'] ?? 'Rekomendasi Agronomi' }}
            </div>

            <div class="recommendation-category">
                {{ $rec['category'] ?? 'Agronomi' }}
            </div>

            <div class="recommendation-action">
                {{ $rec['action'] ?? '' }}
            </div>

        </div>

    @empty

        <div class="recommendation">
            Tidak ada rekomendasi khusus.
            Kondisi hara tanah dalam keadaan normal dan seimbang.
        </div>

    @endforelse


    {{-- CUACA --}}
    @if ($latestWeather)

        @php
            $weatherPayload = $latestWeather->payload_json ?? [];

            $temp = $weatherPayload['main']['temp'] ?? 'N/A';
            $humidity = $weatherPayload['main']['humidity'] ?? 'N/A';
            $wind = $weatherPayload['wind']['speed'] ?? 'N/A';
            $desc = $weatherPayload['weather'][0]['description'] ?? 'N/A';
        @endphp

        <div class="section-title">
            KORELASI CUACA & IKLIM LAHAN
        </div>

        <table class="weather-table">

            <tr>

                <td>
                    <span class="item-label">
                        Kondisi Cuaca
                    </span>

                    <span class="item-value">
                        {{ ucfirst($desc) }}
                    </span>
                </td>

                <td>
                    <span class="item-label">
                        Suhu Udara
                    </span>

                    <span class="item-value">
                        {{ $temp }} °C
                    </span>
                </td>

                <td>
                    <span class="item-label">
                        Kelembaban Udara
                    </span>

                    <span class="item-value">
                        {{ $humidity }}%
                    </span>
                </td>

            </tr>

            <tr>

                <td>
                    <span class="item-label">
                        Kecepatan Angin
                    </span>

                    <span class="item-value">
                        {{ $wind }} m/s
                    </span>
                </td>

                <td colspan="2">
                    <span class="item-label">
                        Pengamatan Terakhir
                    </span>

                    <span class="item-value">
                        {{ $latestWeather->observed_at->format('d M Y H:i') }}
                    </span>
                </td>

            </tr>

        </table>

    @endif


    {{-- FOOTER --}}
    <div class="footer">
        Laporan ini dibuat secara otomatis oleh sistem P.A.D.I.
        <br>
        Predictive Agriculture & Disease Intelligence
    </div>

</body>
</html>