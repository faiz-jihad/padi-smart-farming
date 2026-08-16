@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/operational.css') }}">

<div class="admin-page">
    <div class="admin-page__header">
        <div>
            <p class="admin-page__eyebrow">Admin</p>
            <h1 class="admin-page__title">Early Warning</h1>
            <p class="admin-page__description">Kirim peringatan operasional dan pantau subscription alert dari database.</p>
        </div>
    </div>

    @if(session('status'))
        <div class="admin-alert">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="admin-alert">{{ $errors->first() }}</div>
    @endif

    <div class="admin-grid">
        <div class="admin-stat"><span>Subscription</span><strong>{{ number_format($stats['subscriptions'], 0, ',', '.') }}</strong></div>
        <div class="admin-stat"><span>Aktif</span><strong>{{ number_format($stats['active_subscriptions'], 0, ',', '.') }}</strong></div>
        <div class="admin-stat"><span>Laporan Pending</span><strong>{{ number_format($stats['community_reports'], 0, ',', '.') }}</strong></div>
        <div class="admin-stat"><span>Warning</span><strong>{{ number_format($stats['warnings'], 0, ',', '.') }}</strong></div>
    </div>

    <div class="admin-layout-two">
        <section class="admin-card">
            <div class="admin-card__header"><div class="admin-card__title"><span>Form</span><h2>Kirim Warning</h2></div></div>
            <form method="POST" action="{{ route('admin.early-warning.store') }}" class="admin-form">
                @csrf
                <label class="admin-field"><span>Judul</span><input class="admin-input" name="title" value="{{ old('title') }}" required></label>
                <label class="admin-field"><span>Isi Peringatan</span><textarea class="admin-textarea" name="body" required>{{ old('body') }}</textarea></label>
                <button class="admin-button" type="submit">Kirim Realtime</button>
            </form>
        </section>

        <section class="admin-card">
            <div class="admin-card__header"><div class="admin-card__title"><span>Realtime</span><h2>Riwayat Warning</h2></div></div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr><th>Judul</th><th>Penerima</th><th>Waktu</th></tr></thead>
                    <tbody>
                        @forelse($warnings as $warning)
                            <tr>
                                <td><strong>{{ $warning->title }}</strong><small>{{ $warning->body }}</small></td>
                                <td>{{ $warning->user?->name ?? '-' }}</td>
                                <td>{{ $warning->created_at?->format('d M Y H:i') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="admin-empty">Belum ada warning di database.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <section class="admin-card">
        <div class="admin-card__header"><div class="admin-card__title"><span>Database</span><h2>Alert Subscription</h2></div></div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>Petani</th><th>Lahan</th><th>Radius</th><th>Status</th><th>Dibuat</th></tr></thead>
                <tbody>
                    @forelse($subscriptions as $subscription)
                        <tr>
                            <td>{{ $subscription->farmer?->name ?? '-' }}</td>
                            <td>{{ $subscription->farm?->name ?? '-' }}</td>
                            <td>{{ $subscription->radius_km }} km</td>
                            <td><span class="admin-badge">{{ $subscription->is_active ? 'active' : 'inactive' }}</span></td>
                            <td>{{ $subscription->created_at?->format('d M Y H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="admin-empty">Belum ada alert subscription di database.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">{{ $subscriptions->withQueryString()->links() }}</div>
    </section>
</div>
@endsection
