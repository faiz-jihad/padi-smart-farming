@extends('layouts.admin')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/admin/operational.css') }}">

    <div class="admin-page">
        <div class="admin-page__header">
            <div>
                <p class="admin-page__eyebrow">Admin</p>
                <h1 class="admin-page__title">Cuaca</h1>
                <p class="admin-page__description">Pantau data cuaca real-time dari seluruh lahan pertanian P.A.D.I.</p>
            </div>
            <div class="admin-page__actions">
                <form action="{{ route('admin.weather.clear-cache') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="admin-btn admin-btn--secondary">Bersihkan Cache</button>
                </form>
                <a href="{{ route('admin.weather.map') }}" class="admin-btn admin-btn--secondary">📍 Peta</a>
                <a href="{{ route('admin.weather.history') }}" class="admin-btn admin-btn--secondary">Riwayat</a>
                <a href="{{ route('admin.weather.settings') }}" class="admin-btn admin-btn--secondary">Pengaturan</a>
            </div>
        </div>

        @if (session('status'))
            <div class="admin-alert admin-alert--success">
                ✓ {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="admin-alert admin-alert--error">
                ✕ {{ session('error') }}
            </div>
        @endif

        <div class="admin-grid">
            <div class="admin-stat">
                <span>Total Lahan</span>
                <strong>{{ number_format($stats['total_farms'], 0, ',', '.') }}</strong>
            </div>
            <div class="admin-stat">
                <span>Lahan dengan Data Cuaca</span>
                <strong>{{ number_format($stats['farms_with_weather'], 0, ',', '.') }}</strong>
            </div>
            <div class="admin-stat">
                <span>Total Snapshot</span>
                <strong>{{ number_format($stats['total_snapshots'], 0, ',', '.') }}</strong>
            </div>
            <div class="admin-stat">
                <span>Data Kadaluarsa</span>
                <strong>{{ number_format($stats['expired_snapshots'], 0, ',', '.') }}</strong>
            </div>
        </div>

        <section class="admin-card">
            <div class="admin-card__header">
                <div class="admin-card__title">
                    <span>Terkini</span>
                    <h2>Data Cuaca Lahan</h2>
                </div>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Lahan</th>
                            <th>Petani</th>
                            <th>Cuaca</th>
                            <th>Suhu</th>
                            <th>Kelembaban</th>
                            <th>Kecepatan Angin</th>
                            <th>Diperbarui</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($farms as $farm)
                            @php
                                $latestWeather = $farm->weatherSnapshots->first();
                                $payload = $latestWeather?->payload_json ?? [];
                                $temp = $payload['main']['temp'] ?? 'N/A';
                                $humidity = $payload['main']['humidity'] ?? 'N/A';
                                $windSpeed = $payload['wind']['speed'] ?? 'N/A';
                                $weatherDesc = $payload['weather'][0]['description'] ?? 'N/A';
                                $weatherIcon = $payload['weather'][0]['icon'] ?? '';
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $farm->name }}</strong>
                                    <small>{{ $farm->location ?? '-' }}</small>
                                </td>
                                <td>{{ $farm->farmer?->name ?? '-' }}</td>
                                <td>
                                    @if ($weatherIcon)
                                        <img src="https://openweathermap.org/img/wn/{{ $weatherIcon }}.png"
                                            alt="{{ $weatherDesc }}" style="width: 32px; height: 32px;">
                                    @endif
                                    <small>{{ ucfirst($weatherDesc) }}</small>
                                </td>
                                <td>
                                    @if ($temp !== 'N/A')
                                        <strong>{{ number_format($temp, 1, ',', '.') }}°C</strong>
                                    @else
                                        <span class="admin-text--muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($humidity !== 'N/A')
                                        {{ $humidity }}%
                                    @else
                                        <span class="admin-text--muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($windSpeed !== 'N/A')
                                        {{ number_format($windSpeed, 1, ',', '.') }} m/s
                                    @else
                                        <span class="admin-text--muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($latestWeather)
                                        <small>{{ $latestWeather->observed_at->diffForHumans() }}</small>
                                    @else
                                        <span class="admin-text--muted">Belum ada data</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.weather.refresh') }}" method="POST"
                                        style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="farm_id" value="{{ $farm->id }}">
                                        <button type="submit" class="admin-link">Perbarui</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="admin-empty">Belum ada data lahan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="admin-pagination">{{ $farms->withQueryString()->links() }}</div>
        </section>

        <section class="admin-card">
            <div class="admin-card__header">
                <div class="admin-card__title">
                    <span>Snapshot Terbaru</span>
                    <h2>Pembaruan Data Cuaca</h2>
                </div>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Lahan</th>
                            <th>Provider</th>
                            <th>Diamati Pada</th>
                            <th>Kadaluarsa</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestSnapshots as $snapshot)
                            @php
                                $isExpired = $snapshot->expires_at->isPast();
                                $expiresIn = $snapshot->expires_at->diffInMinutes(now());
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $snapshot->farm?->name ?? '-' }}</strong>
                                    <small>{{ $snapshot->farm?->farmer?->name ?? '-' }}</small>
                                </td>
                                <td><span class="admin-badge">{{ ucfirst($snapshot->provider) }}</span></td>
                                <td>
                                    <small>{{ $snapshot->observed_at->format('d M Y H:i') }}</small>
                                </td>
                                <td>
                                    @if ($isExpired)
                                        <span class="admin-badge admin-badge--error">Kadaluarsa</span>
                                    @else
                                        <small>{{ abs($expiresIn) }} menit lagi</small>
                                    @endif
                                </td>
                                <td>
                                    @if ($isExpired)
                                        <span class="admin-text--error">●</span>
                                    @else
                                        <span class="admin-text--success">●</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="admin-empty">Belum ada snapshot cuaca.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <style>
            .admin-text--muted {
                color: #999;
                font-size: 0.875rem;
            }

            .admin-text--error {
                color: #dc2626;
                font-size: 1.5rem;
                line-height: 1;
            }

            .admin-text--success {
                color: #16a34a;
                font-size: 1.5rem;
                line-height: 1;
            }

            .admin-badge--error {
                background-color: #fee2e2;
                color: #991b1b;
            }

            .admin-alert {
                padding: 1rem;
                margin-bottom: 1.5rem;
                border-radius: 8px;
                font-weight: 500;
            }

            .admin-alert--success {
                background-color: #dcfce7;
                color: #166534;
                border-left: 4px solid #16a34a;
            }

            .admin-alert--error {
                background-color: #fee2e2;
                color: #991b1b;
                border-left: 4px solid #dc2626;
            }
        </style>
    </div>
@endsection
