@extends('layouts.admin')

@section('title', 'Pusat Notifikasi & Informasi Sistem')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/notifications.css') }}?v={{ time() }}">
@endpush

@section('content')
    <link rel="stylesheet" href="{{ asset('css/admin/notifications.css') }}?v={{ time() }}">

    <div class="admin-notif-page">

        {{-- ========================================================================= --}}
        {{-- Header Bar                                                                --}}
        {{-- ========================================================================= --}}
        <header class="admin-notif-header">
            <div>
                <nav class="dashboard-breadcrumb" aria-label="Breadcrumb" style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #64748b; font-weight: 600;">
                    <span>Admin</span>
                    <svg width="12" height="12" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span style="color: #0f172a; font-weight: 700;">Notifikasi</span>
                </nav>
                <h1 class="admin-notif-header__title">Pusat Notifikasi &amp; Informasi Sistem</h1>
                <p class="admin-notif-header__subtitle">Pantau seluruh telemetri mikroklimat, peringatan dini agroklimat, dan status operasional P.A.D.I.</p>
            </div>

            <div class="admin-notif-header__actions">
                <button type="button" class="admin-notif-btn admin-notif-btn--secondary" onclick="triggerDevicePushTest()" title="Uji notifikasi langsung ke layar perangkat ini">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    Uji Notifikasi Perangkat
                </button>

                @if(($unreadCount ?? 0) > 0)
                    <form method="POST" action="{{ route('admin.notifications.read') }}" style="margin: 0;">
                        @csrf
                        <button type="submit" class="admin-notif-btn admin-notif-btn--primary">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            Tandai Semua Dibaca
                        </button>
                    </form>
                @endif

                <a href="{{ route('admin.dashboard') }}" class="admin-notif-btn admin-notif-btn--secondary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Dashboard
                </a>
            </div>
        </header>

        @if (session('status'))
            <div class="dashboard-alert" style="padding: 12px 16px; border: 1px solid #bbf7d0; border-left: 4px solid #166534; border-radius: 8px; background: #f0fdf4; color: #14532d; font-size: 13.5px; font-weight: 600;">
                {{ session('status') }}
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- 4-KPI Metric Cards Grid                                                   --}}
        {{-- ========================================================================= --}}
        <section class="admin-notif-kpi-grid" aria-label="Ringkasan Notifikasi">
            {{-- KPI 1: Total Notifikasi --}}
            <div class="admin-notif-kpi-card">
                <div class="admin-notif-kpi-top">
                    <div>
                        <p class="admin-notif-kpi-label">Total Notifikasi</p>
                        <div class="admin-notif-kpi-value">{{ number_format($totalCount ?? $notifications->total(), 0, ',', '.') }}</div>
                    </div>
                    <div class="admin-notif-kpi-icon admin-notif-kpi-icon--green">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    </div>
                </div>
                <p class="admin-notif-kpi-helper">Riwayat tercatat dalam sistem</p>
            </div>

            {{-- KPI 2: Belum Dibaca --}}
            <div class="admin-notif-kpi-card">
                <div class="admin-notif-kpi-top">
                    <div>
                        <p class="admin-notif-kpi-label">Belum Dibaca</p>
                        <div class="admin-notif-kpi-value" style="{{ ($unreadCount ?? 0) > 0 ? 'color: #ea580c;' : '' }}">
                            {{ number_format($unreadCount ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="admin-notif-kpi-icon admin-notif-kpi-icon--orange">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                </div>
                <p class="admin-notif-kpi-helper">Memerlukan perhatian admin</p>
            </div>

            {{-- KPI 3: Peringatan & Mitigasi --}}
            <div class="admin-notif-kpi-card">
                <div class="admin-notif-kpi-top">
                    <div>
                        <p class="admin-notif-kpi-label">Peringatan Dini</p>
                        <div class="admin-notif-kpi-value">{{ number_format($alertCount ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="admin-notif-kpi-icon admin-notif-kpi-icon--red">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                </div>
                <p class="admin-notif-kpi-helper">Cuaca ekstrem, hama &amp; penyakit</p>
            </div>

            {{-- KPI 4: Info Sistem & Operasional --}}
            <div class="admin-notif-kpi-card">
                <div class="admin-notif-kpi-top">
                    <div>
                        <p class="admin-notif-kpi-label">Info Operasional</p>
                        <div class="admin-notif-kpi-value">{{ number_format($infoCount ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="admin-notif-kpi-icon admin-notif-kpi-icon--blue">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    </div>
                </div>
                <p class="admin-notif-kpi-helper">Status akun, audit &amp; sinkronisasi</p>
            </div>
        </section>

        {{-- ========================================================================= --}}
        {{-- Notification List & Filter Panel                                          --}}
        {{-- ========================================================================= --}}
        <main class="admin-notif-panel">
            <div class="admin-notif-panel__header">
                <div class="admin-notif-search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input type="search" id="notifSearchInput" class="admin-notif-search-input" placeholder="Cari notifikasi...">
                </div>

                <div class="admin-notif-tabs">
                    <button type="button" class="admin-notif-tab is-active" data-filter="all">Semua</button>
                    <button type="button" class="admin-notif-tab" data-filter="unread">Belum Dibaca</button>
                    <button type="button" class="admin-notif-tab" data-filter="alert">Peringatan</button>
                    <button type="button" class="admin-notif-tab" data-filter="system">Info Sistem</button>
                </div>
            </div>

            <div class="admin-notif-list" id="notifListContainer">
                @forelse($notifications as $notification)
                    @php
                        $isUnread = empty($notification->read_at);
                        $type = strtolower($notification->type ?? 'general');
                        $isAlert = str_contains($type, 'alert') || str_contains($type, 'warning') || str_contains($type, 'disease') || str_contains($type, 'weather');
                    @endphp

                    <article
                        class="admin-notif-item {{ $isUnread ? 'is-unread' : '' }}"
                        data-status="{{ $isUnread ? 'unread' : 'read' }}"
                        data-category="{{ $isAlert ? 'alert' : 'system' }}"
                    >
                        <div class="admin-notif-item__icon {{ $isAlert ? 'admin-notif-kpi-icon--red' : ($isUnread ? 'admin-notif-kpi-icon--green' : 'admin-notif-kpi-icon--blue') }}">
                            @if($isAlert)
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            @else
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                            @endif
                        </div>

                        <div class="admin-notif-item__main">
                            <div class="admin-notif-item__top">
                                <h3 class="admin-notif-item__title">
                                    @if($isUnread)
                                        <span class="admin-notif-item__dot" title="Belum dibaca"></span>
                                    @endif
                                    {{ $notification->title }}
                                </h3>
                                <time class="admin-notif-item__time" datetime="{{ $notification->created_at?->toIso8601String() }}">
                                    {{ $notification->created_at?->diffForHumans() ?? 'Baru saja' }}
                                </time>
                            </div>

                            <p class="admin-notif-item__body">{{ $notification->body }}</p>

                            <div class="admin-notif-item__tags">
                                <span class="admin-notif-tag {{ $isAlert ? 'admin-notif-tag--alert' : 'admin-notif-tag--system' }}">
                                    {{ $isAlert ? 'Peringatan' : ($notification->type ?? 'Sistem') }}
                                </span>
                                @if($notification->created_at)
                                    <span style="font-size: 11px; color: #94a3b8;">
                                        &bull; {{ $notification->created_at->format('d M Y, H:i') }} WIB
                                    </span>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="admin-notif-empty">
                        <div class="admin-notif-empty__icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        </div>
                        <h4 class="admin-notif-empty__title">Belum Ada Notifikasi</h4>
                        <p class="admin-notif-empty__desc">Seluruh pemberitahuan sistem dan peringatan dini operasional akan ditampilkan di sini.</p>
                    </div>
                @endforelse

                <div id="notifEmptyFiltered" class="admin-notif-empty" style="display: none;">
                    <p class="admin-notif-empty__desc">Tidak ada notifikasi yang sesuai dengan pencarian atau filter yang dipilih.</p>
                </div>
            </div>

            @if(method_exists($notifications, 'hasPages') && $notifications->hasPages())
                <div class="admin-notif-pagination">
                    {{ $notifications->links() }}
                </div>
            @endif
        </main>
    </div>

    <script>
    window.triggerDevicePushTest = async function () {
        if (!window.PadiPush) {
            alert('Fitur notifikasi perangkat sedang dimuat.');
            return;
        }

        const granted = await window.PadiPush.requestPermission();
        if (granted) {
            window.PadiPush.showLocalNotification({
                title: 'Notifikasi P.A.D.I. Berhasil Aktif',
                body: 'Perangkat Anda telah terhubung dan siap menerima peringatan dini agroklimat serta informasi telemetri sawah.',
                url: window.location.href
            });
        } else {
            alert('Izin notifikasi belum diizinkan di browser ini. Silakan klik ikon gembok / perizinan situs di address bar browser dan pilih "Izinkan Notifikasi (Allow)".');
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('notifSearchInput');
        const tabBtns     = document.querySelectorAll('.admin-notif-tab');
        const notifItems  = document.querySelectorAll('.admin-notif-item');
        const emptyFilter = document.getElementById('notifEmptyFiltered');

        let currentFilter = 'all';
        let currentSearch = '';

        function applyFilter() {
            let visibleCount = 0;

            notifItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                const status = item.dataset.status;
                const cat = item.dataset.category;

                const matchesSearch = !currentSearch || text.includes(currentSearch);
                let matchesTab = true;

                if (currentFilter === 'unread') {
                    matchesTab = (status === 'unread');
                } else if (currentFilter === 'alert') {
                    matchesTab = (cat === 'alert');
                } else if (currentFilter === 'system') {
                    matchesTab = (cat === 'system');
                }

                if (matchesSearch && matchesTab) {
                    item.style.display = 'flex';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            if (emptyFilter) {
                emptyFilter.style.display = (visibleCount === 0 && notifItems.length > 0) ? 'flex' : 'none';
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                currentSearch = this.value.trim().toLowerCase();
                applyFilter();
            });
        }

        tabBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                tabBtns.forEach(b => b.classList.remove('is-active'));
                this.classList.add('is-active');
                currentFilter = this.dataset.filter;
                applyFilter();
            });
        });
    });
    </script>
@endsection