<link rel="stylesheet" href="{{ asset('css/admin/sidebar.css') }}">

@php
    $adminUser = auth()->user();
    $adminName = $adminUser?->name ?? 'Admin P.A.D.I.';
    $adminInitial = strtoupper(substr($adminName, 0, 1));
@endphp

<div id="sidebarOverlay" class="sidebar-overlay"></div>

<aside class="admin-sidebar">

    <div class="admin-sidebar__brand">

        <a href="{{ route('admin.dashboard') }}" class="admin-sidebar__brand-link">

            <div class="admin-sidebar__logo-wrap">

                <img src="{{ asset('images/padi-logo.jpeg') }}" alt="P.A.D.I. Smart Farming" class="admin-sidebar__logo">

            </div>

            <div class="admin-sidebar__brand-copy">
                <p class="admin-sidebar__brand-name">P.A.D.I.</p>
                <p class="admin-sidebar__brand-subtitle">Admin Console</p>
            </div>

        </a>

    </div>

    <div class="admin-sidebar__scroll">

        <p class="admin-sidebar__section-title">
            Menu Utama
        </p>

        <nav class="admin-sidebar__nav">

            <a href="{{ route('admin.dashboard') }}"
                class="admin-sidebar__link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">

                <svg class="admin-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="m3 10 9-7 9 7" />
                    <path d="M5 9v11h14V9" />
                    <path d="M9 20v-6h6v6" />
                </svg>

                <span>Dashboard</span>

            </a>

            @can('view_users')
                <a href="{{ route('admin.users.index') }}"
                    class="admin-sidebar__link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}">

                    <svg class="admin-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>

                    <span>Pengguna</span>

                </a>
            @endcan

            @can('view_agriculture_data')
                <a href="{{ route('admin.agriculture.index') }}"
                    class="admin-sidebar__link {{ request()->routeIs('admin.agriculture.*') ? 'is-active' : '' }}">

                    <svg class="admin-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M12 22V8" />
                        <path d="M5 12c0-3 2-5 7-5s7 2 7 5" />
                        <path d="M5 12c0 4 3 7 7 7s7-3 7-7" />
                        <path d="M12 8c-2-3-1-5 0-6 1 1 1 3 0 6Z" />
                    </svg>

                    <span>Pertanian</span>

                </a>
            @endcan

            @can('view_weather')
                <a href="{{ route('admin.weather.index') }}"
                    class="admin-sidebar__link {{ request()->routeIs('admin.weather.*') ? 'is-active' : '' }}">

                    <svg class="admin-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M12 2v20" />
                        <path d="M2 12c0-5 4-9 10-9s10 4 10 9" />
                        <path d="M8 19h8" />
                        <path d="M6 15h12" />
                        <circle cx="12" cy="12" r="1" />
                    </svg>

                    <span>Cuaca</span>

                </a>
            @endcan

            @can('view_soil')
                <a href="{{ route('admin.soil.index') }}"
                    class="admin-sidebar__link {{ request()->routeIs('admin.soil.*') ? 'is-active' : '' }}">

                    <svg class="admin-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                        <path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                    </svg>

                    <span>Deteksi Tanah</span>

                </a>
            @endcan

            @can('view_knowledge')
                <a href="{{ route('admin.knowledge.index') }}"
                    class="admin-sidebar__link {{ request()->routeIs('admin.knowledge.*') ? 'is-active' : '' }}">

                    <svg class="admin-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20" />
                    </svg>

                    <span>Pusat Pengetahuan</span>

                </a>
            @endcan

            @can('view_disease')
                <a href="{{ route('admin.disease.index') }}"
                    class="admin-sidebar__link {{ request()->routeIs('admin.disease.*') ? 'is-active' : '' }}">

                    <svg class="admin-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                        <path d="M12 8v4" />
                        <path d="M12 16h.01" />
                    </svg>

                    <span>Laporan Penyakit</span>

                </a>
            @endcan

            @can('view_early_warning')
                <a href="{{ route('admin.early-warning.index') }}"
                    class="admin-sidebar__link {{ request()->routeIs('admin.early-warning.*') ? 'is-active' : '' }}">

                    <svg class="admin-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M12 3v18" />
                        <path d="M3 12h18" />
                        <path d="m5 5 14 14" />
                        <path d="m19 5-14 14" />
                    </svg>

                    <span>Early Warning</span>

                </a>
            @endcan

            @can('view_marketplace')
                <a href="{{ route('admin.marketplace.index') }}"
                    class="admin-sidebar__link {{ request()->routeIs('admin.marketplace.*') ? 'is-active' : '' }}">

                    <svg class="admin-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M3 3h18v18H3z" />
                        <path d="M7 7h10" />
                        <path d="M7 11h10" />
                        <path d="M7 15h6" />
                    </svg>

                    <span>Marketplace</span>

                </a>
            @endcan

            @can('view_farmer_profiles')
                <a href="{{ route('admin.farmer-profiles.index') }}"
                    class="admin-sidebar__link {{ request()->routeIs('admin.farmer-profiles.*') ? 'is-active' : '' }}">

                    <svg class="admin-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M20 21a8 8 0 0 0-16 0"/>
                        <path d="m16.5 2.5 5 5"/>
                        <path d="m16.5 7.5 5-5"/>
                    </svg>

                    <span>Profil Petani</span>

                </a>
            @endcan

        </nav>

        <p class="admin-sidebar__section-title admin-sidebar__section-title--system">
            Sistem
        </p>

        <nav class="admin-sidebar__nav">

            @can('view_broadcast')
            <a href="{{ route('admin.broadcast.index') }}"
                class="admin-sidebar__link {{ request()->routeIs('admin.broadcast.*') ? 'is-active' : '' }}">

                <svg class="admin-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="m3 11 18-5v12L3 14v-3Z" />
                    <path d="M11.6 16.8 13 21H9l-1.8-5.4" />
                </svg>

                <span>Broadcast</span>

            </a>
            @endcan

            @can('view_audit_log')  
            <a href="{{ route('admin.audit.index') }}"
                class="admin-sidebar__link {{ request()->routeIs('admin.audit.*') ? 'is-active' : '' }}">

                <svg class="admin-sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" />
                    <path d="M14 2v6h6" />
                    <path d="M8 13h8" />
                    <path d="M8 17h6" />
                    <path d="M8 9h2" />
                </svg>

                <span>Audit Log</span>

            </a>
            @endcan
        </nav>

        <div class="admin-sidebar__bottom-space"></div>

    </div>

    <div class="admin-sidebar__footer">

        <div class="admin-sidebar__profile">

            <div class="admin-sidebar__avatar">
                {{ $adminInitial }}
            </div>

            <div class="admin-sidebar__profile-content">

                <p class="admin-sidebar__profile-name">
                    {{ $adminName }}
                </p>

                <p class="admin-sidebar__profile-role">
                    Administrator
                </p>

            </div>

        </div>

    </div>

</aside>
