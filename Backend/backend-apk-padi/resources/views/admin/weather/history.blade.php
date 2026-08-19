@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/weather.css') }}">

<div class="weather-page">
    {{-- Breadcrumb --}}
    <nav class="weather-breadcrumb" aria-label="Breadcrumb">
        <span>Admin</span>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <a href="{{ route('admin.weather.index') }}" style="color:#64748b; text-decoration:none;">Pemantauan Cuaca</a>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="weather-breadcrumb-current">Riwayat Observasi Cuaca</span>
    </nav>

    {{-- Page Header --}}
    <div class="weather-header">
        <div class="weather-header-content">
            <h1 class="weather-title">Riwayat Observasi & Log Cuaca</h1>
            <p class="weather-description">Daftar rekaman historis indikator iklim, suhu, kelembaban udara, dan kecepatan angin untuk setiap lahan pertanian.</p>
        </div>

        <div class="weather-header-actions">
            <a href="{{ route('admin.weather.index') }}" class="btn-weather-action">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>
    </div>

    {{-- Status Alerts --}}
    @if (session('status'))
        <div class="weather-alert weather-alert-success" id="alert-status">
            <span>{{ session('status') }}</span>
            <button type="button" class="weather-alert-close" onclick="document.getElementById('alert-status').remove()">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="weather-alert weather-alert-danger" id="alert-error">
            <span>{{ session('error') }}</span>
            <button type="button" class="weather-alert-close" onclick="document.getElementById('alert-error').remove()">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Selected Farm Highlight Banner (When farm_id filter is active) --}}
    @if ($selectedFarm)
        <section style="background: #1b5e20; color: #ffffff; border-radius: 16px; padding: 24px 28px; margin-bottom: 28px; border: 1px solid #14532d;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                <div>
                    <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #a7f3d0;">LAHAN YANG DIPILIH</span>
                    <h2 style="font-size: 22px; margin: 4px 0 6px 0; font-weight: 800; color: #ffffff;">{{ $selectedFarm->name }}</h2>
                    <p style="margin: 0; color: #e2e8f0; font-size: 13px;">
                        Pemilik: <strong style="color: #ffffff;">{{ $selectedFarm->farmer?->name ?? '-' }}</strong> | 
                        Luas: <strong style="color: #ffffff;">{{ number_format($selectedFarm->area_ha, 2, ',', '.') }} Ha</strong> | 
                        Koordinat: <strong style="color: #ffffff;">{{ $selectedFarm->latitude }}, {{ $selectedFarm->longitude }}</strong>
                    </p>
                </div>

                <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                    <div style="background: #ffffff; padding: 12px 20px; border-radius: 12px; text-align: center; border: 1px solid #166534;">
                        <span style="display: block; font-size: 11px; color: #1b5e20; text-transform: uppercase; font-weight:800;">Total Snapshot Recorded</span>
                        <span style="font-size: 24px; font-weight: 800; color: #1b5e20; line-height: 1;">
                            {{ number_format($snapshots->total(), 0, ',', '.') }}
                        </span>
                    </div>

                    <form action="{{ route('admin.weather.refresh') }}" method="POST">
                        @csrf
                        <input type="hidden" name="farm_id" value="{{ $selectedFarm->id }}">
                        <button type="submit" class="btn-weather-action" style="background:#ffffff; color:#1b5e20; border-color:#ffffff; font-weight:700; padding:12px 18px;">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="16" height="16">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Perbarui Data Cuaca Live</span>
                        </button>
                    </form>
                </div>
            </div>
        </section>
    @endif

    {{-- Data Filter Card --}}
    <section class="data-card">
        <div class="data-header">
            <div>
                <h2>Filter & Pencarian Riwayat</h2>
                <p>Filter data snapshot cuaca berdasarkan lahan pertanian dan rentang tanggal observasi</p>
            </div>

            {{-- Export Buttons --}}
            <div style="display:flex; gap:8px;">
                <form action="{{ route('admin.weather.export') }}" method="POST">
                    @csrf
                    <input type="hidden" name="farm_id" value="{{ $filters['farm_id'] ?? '' }}">
                    <input type="hidden" name="from_date" value="{{ $filters['from_date'] ?? '' }}">
                    <input type="hidden" name="to_date" value="{{ $filters['to_date'] ?? '' }}">
                    <input type="hidden" name="format" value="csv">
                    <button type="submit" class="btn-export">Export CSV</button>
                </form>

                <form action="{{ route('admin.weather.export') }}" method="POST">
                    @csrf
                    <input type="hidden" name="farm_id" value="{{ $filters['farm_id'] ?? '' }}">
                    <input type="hidden" name="from_date" value="{{ $filters['from_date'] ?? '' }}">
                    <input type="hidden" name="to_date" value="{{ $filters['to_date'] ?? '' }}">
                    <input type="hidden" name="format" value="json">
                    <button type="submit" class="btn-export">Export JSON</button>
                </form>
            </div>
        </div>

        <div class="filter-wrapper">
            <form method="GET" action="{{ route('admin.weather.history') }}" class="filter-form">
                <select name="farm_id" class="filter-select" onchange="this.form.submit()">
                    <option value="">-- Semua Lahan Pertanian --</option>
                    @foreach ($farms as $f)
                        <option value="{{ $f->id }}" @selected(($filters['farm_id'] ?? '') == $f->id)>
                            {{ $f->name }} ({{ $f->farmer?->name ?? 'Tanpa Pemilik' }})
                        </option>
                    @endforeach
                </select>

                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="font-size:12px; font-weight:700; color:#64748b;">Dari:</span>
                    <input type="date" name="from_date" class="filter-date" value="{{ $filters['from_date'] ?? '' }}">
                </div>

                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="font-size:12px; font-weight:700; color:#64748b;">Sampai:</span>
                    <input type="date" name="to_date" class="filter-date" value="{{ $filters['to_date'] ?? '' }}">
                </div>

                <button type="submit" class="btn-filter-submit">Filter Data</button>

                @if(!empty($filters['farm_id']) || !empty($filters['from_date']) || !empty($filters['to_date']))
                    <a href="{{ route('admin.weather.history') }}" class="btn-filter-reset">Reset Filter</a>
                @endif
            </form>
        </div>
    </section>

    {{-- History Data Table Card --}}
    <section class="data-card">
        <div class="data-header">
            <div>
                <h2>Tabel Log Snapshot Cuaca</h2>
                <p>Menampilkan {{ $snapshots->firstItem() ?? 0 }} - {{ $snapshots->lastItem() ?? 0 }} dari {{ $snapshots->total() }} record historis</p>
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Lahan Pertanian</th>
                        <th>Pemilik</th>
                        <th>Provider API</th>
                        <th>Suhu Udara</th>
                        <th>Kelembaban</th>
                        <th>Kecepatan Angin</th>
                        <th>Kondisi Cuaca</th>
                        <th>Waktu Observasi</th>
                        <th>Status Snapshot</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($snapshots as $snapshot)
                        @php
                            $payload = $snapshot->payload_json ?? [];
                            $temp = $payload['main']['temp'] ?? 'N/A';
                            $humidity = $payload['main']['humidity'] ?? 'N/A';
                            $windSpeed = $payload['wind']['speed'] ?? 'N/A';
                            $weatherDesc = $payload['weather'][0]['description'] ?? 'N/A';
                            $isExpired = $snapshot->expires_at ? $snapshot->expires_at->isPast() : true;

                            $providerMap = [
                                'system_sensor' => 'Sensor IoT PADI',
                                'openweathermap' => 'OpenWeatherMap',
                                'agromonitoring' => 'AgroMonitoring',
                                'bmkg' => 'BMKG Official',
                                'bmkg_official' => 'BMKG Official',
                            ];
                            $displayProvider = $providerMap[strtolower($snapshot->provider)] ?? ucwords(str_replace('_', ' ', $snapshot->provider));
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $snapshot->farm?->name ?? 'Lahan #' . $snapshot->farm_id }}</strong>
                            </td>
                            <td>
                                <span>{{ $snapshot->farm?->farmer?->name ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="provider-badge">{{ $displayProvider }}</span>
                            </td>
                            <td>
                                @if ($temp !== 'N/A')
                                    <strong style="color:#0f172a;">{{ number_format($temp, 1, ',', '.') }}°C</strong>
                                @else
                                    <span style="color:#94a3b8;">-</span>
                                @endif
                            </td>
                            <td>
                                @if ($humidity !== 'N/A')
                                    <strong style="color:#0f172a;">{{ $humidity }}%</strong>
                                @else
                                    <span style="color:#94a3b8;">-</span>
                                @endif
                            </td>
                            <td>
                                @if ($windSpeed !== 'N/A')
                                    <span>{{ number_format($windSpeed, 1, ',', '.') }} m/s</span>
                                @else
                                    <span style="color:#94a3b8;">-</span>
                                @endif
                            </td>
                            <td>
                                <span style="text-transform:capitalize; font-weight:600; color:#334155;">{{ $weatherDesc }}</span>
                            </td>
                            <td>
                                <span style="font-size:13px; color:#334155;">{{ $snapshot->observed_at ? $snapshot->observed_at->format('d M Y H:i') : '-' }}</span>
                            </td>
                            <td>
                                <span class="status-dot {{ $isExpired ? 'status-dot-expired' : 'status-dot-active' }}"></span>
                                <span style="font-size:13px; font-weight:600; color: {{ $isExpired ? '#dc2626' : '#059669' }};">
                                    {{ $isExpired ? 'Kadaluarsa' : 'Aktif' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="weather-empty">
                                @if (!empty($filters['farm_id']) || !empty($filters['from_date']) || !empty($filters['to_date']))
                                    Tidak ada data riwayat cuaca yang sesuai dengan filter yang dipilih.
                                @else
                                    Belum ada snapshot data cuaca terdaftar di sistem.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($snapshots->hasPages())
            <div class="pagination-wrapper">
                {{ $snapshots->withQueryString()->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
