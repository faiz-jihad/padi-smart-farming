@extends('layouts.admin')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/admin/operational.css') }}">

    <div class="admin-page">
        <div class="admin-page__header">
            <div>
                <p class="admin-page__eyebrow">Admin</p>
                <h1 class="admin-page__title">Riwayat Cuaca</h1>
                <p class="admin-page__description">Lihat riwayat lengkap data cuaca yang telah dikumpulkan dari setiap lahan.
                </p>
            </div>
            <div class="admin-page__actions">
                <a href="{{ route('admin.weather.index') }}" class="admin-btn admin-btn--secondary">Kembali</a>
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

        <section class="admin-card">
            <div class="admin-card__header">
                <div class="admin-card__title">
                    <span>Filter</span>
                    <h2>Pencarian Riwayat</h2>
                </div>
            </div>
            <form method="GET" class="admin-filter-form">
                <div class="admin-filter-row">
                    <div class="admin-form-group">
                        <label for="farm_id">Lahan</label>
                        <select name="farm_id" id="farm_id" class="admin-input">
                            <option value="">-- Semua Lahan --</option>
                            @foreach ($farms as $farm)
                                <option value="{{ $farm->id }}" @if ($filters['farm_id'] == $farm->id) selected @endif>
                                    {{ $farm->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="admin-form-group">
                        <label for="from_date">Dari Tanggal</label>
                        <input type="date" name="from_date" id="from_date" class="admin-input"
                            value="{{ $filters['from_date'] ?? '' }}">
                    </div>
                    <div class="admin-form-group">
                        <label for="to_date">Hingga Tanggal</label>
                        <input type="date" name="to_date" id="to_date" class="admin-input"
                            value="{{ $filters['to_date'] ?? '' }}">
                    </div>
                    <div class="admin-form-group" style="display: flex; align-items: flex-end; gap: 0.5rem;">
                        <button type="submit" class="admin-btn">Cari</button>
                        <a href="{{ route('admin.weather.history') }}" class="admin-btn admin-btn--secondary">Reset</a>
                    </div>
                </div>
            </form>
            <div style="margin-top: 1rem;">
                <form action="{{ route('admin.weather.export') }}" method="POST" style="display: inline;">
                    @csrf
                    <input type="hidden" name="farm_id" value="{{ $filters['farm_id'] ?? '' }}">
                    <input type="hidden" name="from_date" value="{{ $filters['from_date'] ?? '' }}">
                    <input type="hidden" name="to_date" value="{{ $filters['to_date'] ?? '' }}">
                    <input type="hidden" name="format" value="csv">
                    <button type="submit" class="admin-link">📥 Export CSV</button>
                </form>
                <form action="{{ route('admin.weather.export') }}" method="POST"
                    style="display: inline; margin-left: 1rem;">
                    @csrf
                    <input type="hidden" name="farm_id" value="{{ $filters['farm_id'] ?? '' }}">
                    <input type="hidden" name="from_date" value="{{ $filters['from_date'] ?? '' }}">
                    <input type="hidden" name="to_date" value="{{ $filters['to_date'] ?? '' }}">
                    <input type="hidden" name="format" value="json">
                    <button type="submit" class="admin-link">📥 Export JSON</button>
                </form>
            </div>
        </section>

        <section class="admin-card">
            <div class="admin-card__header">
                <div class="admin-card__title">
                    <span>Database</span>
                    <h2>Snapshot Cuaca</h2>
                </div>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Lahan</th>
                            <th>Petani</th>
                            <th>Provider</th>
                            <th>Suhu</th>
                            <th>Kelembaban</th>
                            <th>Angin</th>
                            <th>Cuaca</th>
                            <th>Diamati Pada</th>
                            <th>Kadaluarsa</th>
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
                                $isExpired = $snapshot->expires_at->isPast();
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $snapshot->farm?->name ?? '-' }}</strong>
                                </td>
                                <td>
                                    <small>{{ $snapshot->farm?->farmer?->name ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="admin-badge">{{ ucfirst($snapshot->provider) }}</span>
                                </td>
                                <td>
                                    @if ($temp !== 'N/A')
                                        {{ number_format($temp, 1, ',', '.') }}°C
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
                                    <small>{{ ucfirst($weatherDesc) }}</small>
                                </td>
                                <td>
                                    <small>{{ $snapshot->observed_at->format('d M Y H:i') }}</small>
                                </td>
                                <td>
                                    @if ($isExpired)
                                        <span class="admin-badge admin-badge--error">Kadaluarsa</span>
                                    @else
                                        <span class="admin-badge admin-badge--success">Aktif</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="admin-empty">
                                    @if ($filters['farm_id'] || $filters['from_date'] || $filters['to_date'])
                                        Tidak ada data sesuai filter yang dipilih.
                                    @else
                                        Belum ada snapshot cuaca.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="admin-pagination">{{ $snapshots->appends(request()->query())->links() }}</div>
        </section>

        <style>
            .admin-filter-form {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            .admin-filter-row {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
            }

            .admin-form-group {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }

            .admin-form-group label {
                font-weight: 600;
                font-size: 0.875rem;
                color: #333;
            }

            .admin-input {
                padding: 0.5rem 0.75rem;
                border: 1px solid #ddd;
                border-radius: 6px;
                font-family: Poppins, sans-serif;
                font-size: 0.875rem;
            }

            .admin-input:focus {
                outline: none;
                border-color: #16a34a;
                box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
            }

            .admin-link {
                background: none;
                border: none;
                color: #16a34a;
                cursor: pointer;
                text-decoration: underline;
                font-size: 0.875rem;
                padding: 0;
            }

            .admin-link:hover {
                color: #15803d;
            }

            .admin-text--muted {
                color: #999;
            }

            .admin-badge--success {
                background-color: #dcfce7;
                color: #166534;
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
