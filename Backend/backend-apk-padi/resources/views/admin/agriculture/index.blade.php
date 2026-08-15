@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/operational.css') }}">

<div class="admin-page">
    <div class="admin-page__header">
        <div>
            <p class="admin-page__eyebrow">Admin</p>
            <h1 class="admin-page__title">Pertanian</h1>
            <p class="admin-page__description">Ringkasan lahan, musim tanam, dan panen langsung dari database pertanian P.A.D.I.</p>
        </div>
    </div>

    <div class="admin-grid">
        <div class="admin-stat"><span>Lahan</span><strong>{{ number_format($stats['farms'], 0, ',', '.') }}</strong></div>
        <div class="admin-stat"><span>Total Hektare</span><strong>{{ number_format($stats['area'], 2, ',', '.') }}</strong></div>
        <div class="admin-stat"><span>Musim Aktif</span><strong>{{ number_format($stats['active_seasons'], 0, ',', '.') }}</strong></div>
        <div class="admin-stat"><span>Panen</span><strong>{{ number_format($stats['harvests'], 0, ',', '.') }}</strong></div>
    </div>

    <section class="admin-card">
        <div class="admin-card__header"><div class="admin-card__title"><span>Database</span><h2>Lahan Terdaftar</h2></div></div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>Lahan</th><th>Petani</th><th>Luas</th><th>Irigasi</th><th>Koordinat</th><th>Musim</th></tr></thead>
                <tbody>
                    @forelse($farms as $farm)
                        <tr>
                            <td><strong>{{ $farm->name }}</strong><small>{{ $farm->irrigation_notes ?? '-' }}</small></td>
                            <td>{{ $farm->farmer?->name ?? '-' }}</td>
                            <td>{{ number_format((float) $farm->area_ha, 2, ',', '.') }} ha</td>
                            <td><span class="admin-badge">{{ $farm->irrigation_type }}</span></td>
                            <td>{{ $farm->latitude }}, {{ $farm->longitude }}</td>
                            <td>{{ $farm->cropSeasons->count() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="admin-empty">Belum ada lahan di database.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">{{ $farms->withQueryString()->links() }}</div>
    </section>

    <section class="admin-card">
        <div class="admin-card__header"><div class="admin-card__title"><span>Terbaru</span><h2>Musim Tanam</h2></div></div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>Lahan</th><th>Varietas</th><th>Status</th><th>Tanam</th><th>Estimasi Panen</th></tr></thead>
                <tbody>
                    @forelse($cropSeasons as $season)
                        <tr>
                            <td><strong>{{ $season->farm?->name ?? '-' }}</strong><small>{{ $season->farm?->farmer?->name ?? '-' }}</small></td>
                            <td>{{ $season->variety?->name ?? '-' }}</td>
                            <td><span class="admin-badge">{{ $season->status }}</span></td>
                            <td>{{ $season->planting_date ?? $season->planned_planting_date ?? '-' }}</td>
                            <td>{{ $season->estimated_harvest_date ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="admin-empty">Belum ada musim tanam di database.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
