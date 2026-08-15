<header class="admin-navbar">

    @php
        $adminUser = auth()->user();
        $adminName = $adminUser?->name ?? 'Admin P.A.D.I.';
        $adminInitial = strtoupper(substr($adminName, 0, 1));
    @endphp

    <button
        type="button"
        id="sidebarToggle"
        class="admin-navbar__menu-button"
        aria-label="Buka menu"
        aria-expanded="false"
    >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 6h16" />
            <path d="M4 12h16" />
            <path d="M4 18h16" />
        </svg>
    </button>

    <div class="admin-navbar__welcome">

        <p>
            Selamat datang kembali
        </p>

        <h2>
            {{ $adminName }}
        </h2>

    </div>

    <div class="admin-navbar__right">

        <div class="admin-navbar__notification-wrap">

            <button
                type="button"
                id="adminNotificationToggle"
                class="admin-navbar__notification"
                aria-label="Notifikasi"
                aria-expanded="false"
            >

                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>

                @if(($adminUnreadNotifications ?? 0) > 0)
                    <span class="admin-navbar__notification-dot"></span>
                    <strong
                        id="adminNotificationBadge"
                        class="admin-navbar__notification-count"
                        data-count="{{ $adminUnreadNotifications }}"
                    >
                        {{ $adminUnreadNotifications > 9 ? '9+' : $adminUnreadNotifications }}
                    </strong>
                @else
                    <strong
                        id="adminNotificationBadge"
                        class="admin-navbar__notification-count"
                        data-count="0"
                        hidden
                    >0</strong>
                @endif

            </button>

            <div id="adminNotificationPanel" class="admin-navbar__notification-panel" hidden>

                <div class="admin-navbar__notification-header">
                    <div>
                        <p>Notifikasi</p>
                        <span><span data-admin-notification-count>{{ $adminUnreadNotifications ?? 0 }}</span> belum dibaca</span>
                    </div>

                    @if(($adminUnreadNotifications ?? 0) > 0)
                        <form method="POST" action="{{ route('admin.notifications.read') }}">
                            @csrf
                            <button type="submit">
                                Tandai dibaca
                            </button>
                        </form>
                    @endif
                </div>

                <div id="adminNotificationList" class="admin-navbar__notification-list">
                    @forelse(($adminNavNotifications ?? collect()) as $notification)
                        <div class="admin-navbar__notification-item {{ $notification->read_at ? '' : 'is-unread' }}">
                            <div class="admin-navbar__notification-icon">
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
                            Tidak ada notifikasi.
                            </span>
                        </div>
                    @endforelse
                </div>

            </div>

        </div>

        <div class="admin-navbar__divider"></div>

        <div class="admin-navbar__profile">

            <div class="admin-navbar__avatar">
                {{ $adminInitial }}
            </div>

            <div class="admin-navbar__profile-info">

                <p>
                    {{ $adminName }}
                </p>

                <span>
                    Administrator
                </span>

            </div>

            <form method="POST" action="{{ route('admin.logout') }}" class="admin-navbar__logout-form">
                @csrf
                <button
                    type="submit"
                    class="admin-navbar__account-button"
                    aria-label="Keluar admin"
                    title="Keluar admin"
                >

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <path d="M16 17l5-5-5-5"/>
                        <path d="M21 12H9"/>
                    </svg>

                </button>
            </form>

        </div>

    </div>

</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('adminNotificationToggle');
        const panel = document.getElementById('adminNotificationPanel');

        if (!toggle || !panel) {
            return;
        }

        function closePanel() {
            panel.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
        }

        toggle.addEventListener('click', function (event) {
            event.stopPropagation();
            panel.hidden = !panel.hidden;
            toggle.setAttribute('aria-expanded', panel.hidden ? 'false' : 'true');
        });

        panel.addEventListener('click', function (event) {
            event.stopPropagation();
        });

        document.addEventListener('click', closePanel);

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closePanel();
            }
        });
    });
</script>
