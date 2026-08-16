@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/operational.css') }}">

<div class="admin-page">
    <div class="admin-page__header">
        <div>
            <p class="admin-page__eyebrow">Admin</p>
            <h1 class="admin-page__title">Laporan Penyakit</h1>
            <p class="admin-page__description">Pantau scan penyakit dan laporan komunitas langsung dari database.</p>
        </div>
    </div>

    @if(session('status'))
        <div class="admin-alert">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="admin-alert">{{ $errors->first() }}</div>
    @endif

    <div class="admin-grid">
        <div class="admin-stat"><span>Scan</span><strong>{{ number_format($stats['scans'], 0, ',', '.') }}</strong></div>
        <div class="admin-stat"><span>Laporan</span><strong>{{ number_format($stats['reported'], 0, ',', '.') }}</strong></div>
        <div class="admin-stat"><span>Pending</span><strong>{{ number_format($stats['pending_reports'], 0, ',', '.') }}</strong></div>
        <div class="admin-stat"><span>Valid Image</span><strong>{{ number_format($stats['valid_images'], 0, ',', '.') }}</strong></div>
    </div>

    <section class="admin-card">
        <div class="admin-card__header"><div class="admin-card__title"><span>Database</span><h2>Scan Penyakit</h2></div></div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>Petani</th><th>Lahan</th><th>Kelas Prediksi</th><th>Confidence</th><th>Kualitas</th><th>Waktu</th></tr></thead>
                <tbody>
                    @forelse($scans as $scan)
                        <tr>
                            <td>{{ $scan->farmer?->name ?? '-' }}</td>
                            <td>{{ $scan->farm?->name ?? '-' }}</td>
                            <td><strong>{{ $scan->predicted_class ?? '-' }}</strong><small>{{ $scan->model_version ?? '-' }}</small></td>
                            <td>{{ $scan->confidence ? number_format((float) $scan->confidence * 100, 2, ',', '.') . '%' : '-' }}</td>
                            <td><span class="admin-badge">{{ $scan->quality_status }}</span></td>
                            <td>{{ $scan->scanned_at?->format('d M Y H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="admin-empty">Belum ada scan penyakit di database.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">{{ $scans->withQueryString()->links() }}</div>
    </section>

    <section class="admin-card">
        <div class="admin-card__header"><div class="admin-card__title"><span>Action</span><h2>Laporan Komunitas</h2></div></div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>ID</th><th>Petani</th><th>Radius</th><th>Status</th><th>Dilaporkan</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td>#{{ $report->id }}</td>
                            <td>{{ $report->farmer?->name ?? '-' }}</td>
                            <td>{{ $report->radius_km }} km</td>
                            <td><span class="admin-badge">{{ $report->status }}</span></td>
                            <td>{{ $report->reported_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.disease.reports.update', $report) }}" class="admin-form--inline">
                                    @csrf
                                    @method('PATCH')
                                    <select class="admin-select" name="status">
                                        @foreach(['pending', 'verified', 'rejected', 'resolved'] as $status)
                                            <option value="{{ $status }}" @selected($report->status === $status)>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    <button class="admin-button" type="submit">Update</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="admin-empty">Belum ada laporan komunitas di database.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
