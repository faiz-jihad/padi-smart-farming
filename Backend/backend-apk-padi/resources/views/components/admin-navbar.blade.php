<header class="admin-navbar">

    @php
        $adminUser = auth()->user();
        $adminName = $adminUser?->name ?? 'Admin P.A.D.I.';
        $adminInitial = strtoupper(substr($adminName, 0, 1));
        $contextTitle = $title ?? 'Dashboard';
    @endphp

    <div class="admin-navbar__left">
        <button
            type="button"
            id="sidebarToggle"
            class="admin-navbar__menu-button"
            aria-label="Buka menu navigasi"
            aria-expanded="false"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 6h16" />
                <path d="M4 12h16" />
                <path d="M4 18h16" />
            </svg>
        </button>

        <div class="admin-navbar__context" aria-label="Konteks halaman">
            <nav class="admin-navbar__breadcrumb" aria-label="Breadcrumb">
                <span>Admin</span>
                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span>{{ $contextTitle }}</span>
            </nav>
            <p>{{ $contextTitle }}</p>
        </div>
    </div>

    <div class="admin-navbar__right">

        <div class="admin-navbar__notification-wrap">

            <button
                type="button"
                id="adminNotificationToggle"
                class="admin-navbar__notification {{ request()->routeIs('admin.notifications.*') ? 'is-active' : '' }}"
                aria-label="Buka notifikasi"
                aria-expanded="false"
                aria-controls="adminNotificationPanel"
            >

                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>

                <strong
                    id="adminNotificationBadge"
                    class="admin-navbar__notification-count"
                    data-count="{{ $adminUnreadNotifications ?? 0 }}"
                    @if(($adminUnreadNotifications ?? 0) === 0) hidden @endif
                >
                    {{ ($adminUnreadNotifications ?? 0) > 9 ? '9+' : ($adminUnreadNotifications ?? 0) }}
                </strong>

            </button>

            <div id="adminNotificationPanel" class="admin-navbar__notification-panel" hidden>

                <div class="admin-navbar__notification-header">
                    <div>
                        <p>Notifikasi</p>
                        <span><span data-admin-notification-count>{{ $adminUnreadNotifications ?? 0 }}</span> belum dibaca</span>
                    </div>

                    <div style="display:flex; align-items:center; gap:6px;">


                        @if(($adminUnreadNotifications ?? 0) > 0)
                            <form method="POST" action="{{ route('admin.notifications.read') }}">
                                @csrf
                                <button type="submit">
                                    Tandai dibaca
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div id="adminNotificationList" class="admin-navbar__notification-list">
                    @forelse(($adminNavNotifications ?? collect()) as $notification)
                        <div class="admin-navbar__notification-item {{ $notification->read_at ? '' : 'is-unread' }}">
                            <div class="admin-navbar__notification-icon" aria-hidden="true">
                                {{ strtoupper(substr($notification->type, 0, 1)) }}
                            </div>

                            <div class="admin-navbar__notification-content">
                                <p>
                                    {{ $notification->title }}
                                </p>

                                <span>
                                    {{ $notification->body }}
                                </span>

                                <small>
                                    {{ optional($notification->created_at)->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                    @empty
                        <div class="admin-navbar__notification-empty">
                            <span data-admin-notification-empty>
                                Belum ada notifikasi baru.
                            </span>
                        </div>
                    @endforelse
                </div>

                <div class="dashboard-panel__footer-action">
                    <a href="{{ route('admin.notifications.index') }}" class="dashboard-view-all">
                        Lihat semua notifikasi →
                    </a>
                </div>

            </div>

        </div>

        <div class="admin-navbar__account-wrap">
            <button
                type="button"
                id="adminAccountToggle"
                class="admin-navbar__account"
                aria-label="Buka menu akun"
                aria-expanded="false"
                aria-controls="adminAccountPanel"
            >
                <span class="admin-navbar__avatar" aria-hidden="true">
                    {{ $adminInitial }}
                </span>

                <span class="admin-navbar__profile-info">
                    <span>{{ $adminName }}</span>
                    <small>
                        {{ \App\Enums\UserRole::tryFrom(auth()->user()->role)?->label() ?? auth()->user()->role }}
                    </small>
                </span>

                <svg class="admin-navbar__chevron" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="m5 8 5 5 5-5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>

            <div id="adminAccountPanel" class="admin-navbar__account-panel" hidden>
                <div class="admin-navbar__account-summary">
                    <strong>{{ $adminName }}</strong>
                    <span>
                        {{ \App\Enums\UserRole::tryFrom(auth()->user()->role)?->label() ?? auth()->user()->role }}
                    </span>
                </div>

                <form method="POST" action="{{ route('admin.logout') }}" class="admin-navbar__logout-form">
                    @csrf
                    <button type="submit" class="admin-navbar__logout-button">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <path d="M16 17l5-5-5-5"/>
                            <path d="M21 12H9"/>
                        </svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </div>

    </div>

</header>

{{-- Inline script: wiring dropdown navbar tanpa bergantung pada Vite build --}}
<script>
(function () {
    function initPopover(toggleId, panelId) {
        var toggle = document.getElementById(toggleId);
        var panel  = document.getElementById(panelId);
        if (!toggle || !panel) return;
        if (toggle.dataset.popoverBound === 'true') return;

        toggle.dataset.popoverBound = 'true';

        function open()  { panel.hidden = false; toggle.setAttribute('aria-expanded', 'true'); }
        function close() { panel.hidden = true;  toggle.setAttribute('aria-expanded', 'false'); }

        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            e.stopImmediatePropagation();
            panel.hidden ? open() : close();
        }, true);

        panel.addEventListener('click', function (e) { e.stopPropagation(); });

        document.addEventListener('click', close);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { close(); toggle.focus(); }
        });
    }

    // Jalankan setelah elemen navbar sudah ada di DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initPopover('adminNotificationToggle', 'adminNotificationPanel');
            initPopover('adminAccountToggle',      'adminAccountPanel');
        });
    } else {
        initPopover('adminNotificationToggle', 'adminNotificationPanel');
        initPopover('adminAccountToggle',      'adminAccountPanel');
    }
}());
</script>
