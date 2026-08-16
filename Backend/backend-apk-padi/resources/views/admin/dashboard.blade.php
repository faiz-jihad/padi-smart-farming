@extends('layouts.admin')

@section('content')

<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">

@php
    $metricLinks = [
        'users' => route('admin.users.index'),
        'farm' => route('admin.agriculture.index'),
        'warning' => route('admin.disease.index'),
        'market' => route('admin.marketplace.index'),
    ];
@endphp

<div class="dashboard-page">

    @if(session('status'))
        <div class="dashboard-alert" role="status">
            {{ session('status') }}
        </div>
    @endif

    <header class="dashboard-header">
        <div>
            <nav class="dashboard-breadcrumb" aria-label="Breadcrumb">
                <span>Admin</span>
                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span>Dashboard</span>
            </nav>

            <h1>Dashboard</h1>
            <p>Pantau kondisi operasional dan aktivitas terbaru P.A.D.I.</p>
        </div>

        <div class="dashboard-period" aria-label="Periode data">
            <span>Data hari ini</span>
            <strong>{{ now()->translatedFormat('d F Y') }}</strong>
        </div>
    </header>

    <section class="dashboard-kpi-grid" aria-label="Ringkasan metrik operasional">
        @foreach($metrics as $metric)
            @include('admin.partials.kpi-card', [
                'metric' => $metric,
                'href' => $metricLinks[$metric['icon']] ?? null,
            ])
        @endforeach
    </section>

    <section class="dashboard-overview">

        <article class="dashboard-panel dashboard-panel--activity">
            <div class="dashboard-panel__header">
                <div>
                    <h2>Aktivitas Terbaru</h2>
                    <p>Jejak aksi operasional dari audit log admin.</p>
                </div>

                <a href="{{ route('admin.audit.index') }}" class="dashboard-panel__link">
                    Lihat audit log
                </a>
            </div>

            <div class="dashboard-activity-list">
                @forelse($recentActivities as $activity)
                    <div class="dashboard-activity-item">
                        <span class="dashboard-activity-icon dashboard-tone-{{ $activity['tone'] }}" aria-hidden="true">
                            @include('admin.partials.metric-icon', ['icon' => $activity['icon']])
                        </span>

                        <div class="dashboard-activity-main">
                            <h3>{{ $activity['title'] }}</h3>
                            <dl>
                                <div>
                                    <dt>Pelaku</dt>
                                    <dd>{{ $activity['actor'] }}</dd>
                                </div>
                                <div>
                                    <dt>Modul</dt>
                                    <dd>{{ $activity['module'] }}</dd>
                                </div>
                            </dl>
                        </div>

                        <time class="dashboard-activity-time">{{ $activity['time'] }}</time>
                    </div>
                @empty
                    <div class="dashboard-empty">
                        <span class="dashboard-empty__icon" aria-hidden="true">
                            @include('admin.partials.metric-icon', ['icon' => 'audit'])
                        </span>
                        <p>Belum ada aktivitas audit.</p>
                    </div>
                @endforelse
            </div>
        </article>

        <article class="dashboard-panel">
            <div class="dashboard-panel__header">
                <div>
                    <h2>Notifikasi Sistem</h2>
                    <p>Pesan terbaru untuk administrator.</p>
                </div>

                <span class="dashboard-panel__badge">
                    {{ number_format($adminUnreadNotifications ?? 0, 0, ',', '.') }} belum dibaca
                </span>
            </div>

            <div class="dashboard-notification-list">
                @forelse($systemNotifications as $notification)
                    <div class="dashboard-notification-item">
                        <span class="dashboard-notification-type" aria-hidden="true">
                            {{ strtoupper(substr($notification['type'], 0, 1)) }}
                        </span>

                        <div>
                            <h3>{{ $notification['title'] }}</h3>
                            <p>{{ $notification['body'] }}</p>
                            <time>{{ $notification['time'] }}</time>
                        </div>
                    </div>
                @empty
                    <div class="dashboard-empty">
                        <span class="dashboard-empty__icon" aria-hidden="true">
                            @include('admin.partials.metric-icon', ['icon' => 'broadcast'])
                        </span>
                        <p>Belum ada notifikasi baru.</p>
                    </div>
                @endforelse
            </div>

            @if(($adminUnreadNotifications ?? 0) > 0)
                <form method="POST" action="{{ route('admin.notifications.read') }}" class="dashboard-panel__footer-action">
                    @csrf
                    <button type="submit">Tandai semua dibaca</button>
                </form>
            @endif
        </article>

    </section>

    <section class="dashboard-monitoring" aria-label="Monitoring operasional">

        <article class="dashboard-panel">
            <div class="dashboard-panel__header">
                <div>
                    <h2>Status Pengguna</h2>
                    <p>Kondisi akun dan broadcast yang perlu dipantau.</p>
                </div>

                <a href="{{ route('admin.users.index') }}" class="dashboard-panel__link">
                    Kelola pengguna
                </a>
            </div>

            <dl class="dashboard-definition-grid">
                <div>
                    <dt>Aktif</dt>
                    <dd>{{ number_format($userStats['active'], 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>Tidak aktif</dt>
                    <dd>{{ number_format($userStats['inactive'], 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>Ditangguhkan</dt>
                    <dd>{{ number_format($userStats['suspended'], 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>Broadcast</dt>
                    <dd>{{ number_format($userStats['broadcasts'], 0, ',', '.') }}</dd>
                </div>
            </dl>
        </article>

        <article class="dashboard-panel">
            <div class="dashboard-panel__header">
                <div>
                    <h2>Marketplace</h2>
                    <p>Listing, penawaran, dan kontrak pembelian.</p>
                </div>

                <a href="{{ route('admin.marketplace.index') }}" class="dashboard-panel__link">
                    Lihat marketplace
                </a>
            </div>

            <dl class="dashboard-definition-grid">
                <div>
                    <dt>Listing aktif</dt>
                    <dd>{{ number_format($marketplaceStats['active_listings'], 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>Penawaran</dt>
                    <dd>{{ number_format($marketplaceStats['offers'], 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>Kontrak aktif</dt>
                    <dd>{{ number_format($marketplaceStats['contracts'], 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>Draft/moderasi</dt>
                    <dd>{{ number_format($marketplaceStats['pending_moderation'], 0, ',', '.') }}</dd>
                </div>
            </dl>
        </article>

    </section>

</div>

@endsection
