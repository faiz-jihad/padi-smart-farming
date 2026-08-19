@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/operational.css') }}">

<div class="admin-page">
    <div class="admin-page__header">
        <div>
            <p class="admin-page__eyebrow">Admin</p>
            <h1 class="admin-page__title">Audit Log</h1>
            <p class="admin-page__description">Jejak aksi admin langsung dari tabel audit_logs.</p>
        </div>
    </div>

    <div class="admin-grid">
        <div class="admin-stat"><span>Total Log</span><strong>{{ number_format($stats['total'], 0, ',', '.') }}</strong></div>
        <div class="admin-stat"><span>Hari Ini</span><strong>{{ number_format($stats['today'], 0, ',', '.') }}</strong></div>
        <div class="admin-stat"><span>User Aktif Log</span><strong>{{ number_format($stats['users'], 0, ',', '.') }}</strong></div>
        <div class="admin-stat"><span>Aksi Admin</span><strong>{{ number_format($stats['admin_actions'], 0, ',', '.') }}</strong></div>
    </div>

    <section class="admin-card">
        <div class="admin-card__header"><div class="admin-card__title"><span>Database</span><h2>Log Aktivitas</h2></div></div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>Waktu</th><th>User</th><th>Aksi</th><th>Entity</th><th>IP</th><th>Perubahan</th></tr></thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td>{{ $log->user?->name ?? 'Sistem' }}</td>
                            <td><span class="admin-badge">{{ $log->action }}</span></td>
                            <td><strong>{{ class_basename((string) $log->entity_type) }}</strong><small>#{{ $log->entity_id ?? '-' }}</small></td>
                            <td>{{ $log->ip_address ?? '-' }}</td>
                            <td>
                                <small>Old: {{ $log->old_values ? json_encode($log->old_values) : '-' }}</small>
                                <small>New: {{ $log->new_values ? json_encode($log->new_values) : '-' }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="admin-empty">Belum ada audit log di database.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">{{ $logs->withQueryString()->links() }}</div>
    </section>
</div>
@endsection
