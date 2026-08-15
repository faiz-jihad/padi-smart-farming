@extends('layouts.admin')

@section('content')

<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">

<div class="dashboard-page">

    @if(session('status'))
        <div class="dashboard-alert">
            {{ session('status') }}
        </div>
    @endif

    <section class="dashboard-hero">
        <div>
            <p class="dashboard-eyebrow">
                Ringkasan Sistem
            </p>

            <h1 class="dashboard-title">
                Dashboard Admin P.A.D.I.
            </h1>

            <p class="dashboard-description">
                Pantau pengguna, aktivitas lahan, marketplace, broadcast, audit, dan notifikasi operasional dari satu tempat.
            </p>
        </div>

        <div class="dashboard-hero-card">
            <span class="dashboard-hero-label">
                Notifikasi belum dibaca
            </span>

            <strong>
                {{ $adminUnreadNotifications ?? 0 }}
            </strong>

            <p>
                Sistem akan menampilkan notifikasi terbaru pada navbar dan panel dashboard.
            </p>
        </div>
    </section>

    <section class="dashboard-stats">
        @foreach($metrics as $metric)
            <article class="dashboard-stat-card">
                <div class="stat-top">
                    <div class="stat-icon stat-icon-{{ $metric['tone'] }}">
                        @include('admin.partials.metric-icon', ['icon' => $metric['icon']])
                    </div>

                    <span class="stat-badge stat-badge-{{ $metric['tone'] }}">
                        {{ $metric['label'] }}
                    </span>
                </div>

                <p class="stat-value">
                    {{ number_format($metric['value'], 0, ',', '.') }}
                </p>

                <p class="stat-helper">
                    {{ $metric['helper'] }}
                </p>
            </article>
        @endforeach
    </section>

    <section class="dashboard-grid dashboard-grid-main">

        <article class="dashboard-panel activity-panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">
                        Aktivitas Terbaru
                    </h2>

                    <p class="panel-description">
                        Diambil dari audit log admin.
                    </p>
                </div>

                <a href="{{ route('admin.audit.index') }}" class="panel-link">
                    Lihat audit
                </a>
            </div>

            <div class="activity-list">
                @forelse($recentActivities as $activity)
                    <div class="activity-item">
                        <div class="activity-icon activity-{{ $activity['tone'] }}">
                            @include('admin.partials.metric-icon', ['icon' => $activity['icon']])
                        </div>

                        <div class="activity-content">
                            <p class="activity-title">
                                {{ $activity['title'] }}
                            </p>

                            <p class="activity-description">
                                {{ $activity['description'] }}
                            </p>
                        </div>

                        <span class="activity-time">
                            {{ $activity['time'] }}
                        </span>
                    </div>
                @empty
                    <div class="dashboard-empty">
                        Belum ada aktivitas audit.
                    </div>
                @endforelse
            </div>
        </article>

        <article class="dashboard-panel notification-panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">
                        Notifikasi Sistem
                    </h2>

                    <p class="panel-description">
                        Pesan terbaru untuk admin.
                    </p>
                </div>

                @if(($adminUnreadNotifications ?? 0) > 0)
                    <form method="POST" action="{{ route('admin.notifications.read') }}">
                        @csrf
                        <button type="submit" class="panel-action">
                            Tandai dibaca
                        </button>
                    </form>
                @endif
            </div>

            <div class="notification-list">
                @forelse($systemNotifications as $notification)
                    <div class="notification-item">
                        <div class="notification-type">
                            {{ strtoupper(substr($notification['type'], 0, 1)) }}
                        </div>

                        <div>
                            <p>
                                {{ $notification['title'] }}
                            </p>

                            <span>
                                {{ $notification['body'] }}
                            </span>

                            <small>
                                {{ $notification['time'] }}
                            </small>
                        </div>
                    </div>
                @empty
                    <div class="dashboard-empty">
                        Belum ada notifikasi.
                    </div>
                @endforelse
            </div>
        </article>

    </section>

    <section class="dashboard-grid dashboard-grid-secondary">

        <article class="dashboard-panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">
                        Status Pengguna
                    </h2>

                    <p class="panel-description">
                        Kondisi akun yang perlu dipantau admin.
                    </p>
                </div>

                <a href="{{ route('admin.users.index') }}" class="panel-link">
                    Kelola
                </a>
            </div>

            <div class="status-grid">
                <div class="status-card">
                    <span>Aktif</span>
                    <strong>{{ number_format($userStats['active'], 0, ',', '.') }}</strong>
                </div>

                <div class="status-card">
                    <span>Tidak aktif</span>
                    <strong>{{ number_format($userStats['inactive'], 0, ',', '.') }}</strong>
                </div>

                <div class="status-card status-card-warning">
                    <span>Ditangguhkan</span>
                    <strong>{{ number_format($userStats['suspended'], 0, ',', '.') }}</strong>
                </div>

                <div class="status-card">
                    <span>Broadcast</span>
                    <strong>{{ number_format($userStats['broadcasts'], 0, ',', '.') }}</strong>
                </div>
            </div>
        </article>

        <article class="dashboard-panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">
                        Marketplace
                    </h2>

                    <p class="panel-description">
                        Listing, penawaran, dan kontrak pembelian.
                    </p>
                </div>

                <a href="{{ route('admin.marketplace.index') }}" class="panel-link">
                    Lihat
                </a>
            </div>

            <div class="marketplace-grid">
                <div class="marketplace-card">
                    <p>Listing aktif</p>
                    <strong>{{ number_format($marketplaceStats['active_listings'], 0, ',', '.') }}</strong>
                </div>

                <div class="marketplace-card">
                    <p>Penawaran</p>
                    <strong>{{ number_format($marketplaceStats['offers'], 0, ',', '.') }}</strong>
                </div>

                <div class="marketplace-card">
                    <p>Kontrak aktif</p>
                    <strong>{{ number_format($marketplaceStats['contracts'], 0, ',', '.') }}</strong>
                </div>

                <div class="marketplace-card marketplace-card-warning">
                    <p>Draft/moderasi</p>
                    <strong>{{ number_format($marketplaceStats['pending_moderation'], 0, ',', '.') }}</strong>
                </div>
            </div>
        </article>

    </section>

</div>

@endsection
