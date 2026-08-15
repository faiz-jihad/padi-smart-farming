@extends('layouts.admin')

@section('content')

<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">

<div class="dashboard-page">

    <div class="dashboard-header">
        <div>
            <p class="dashboard-eyebrow">
                Ringkasan Sistem
            </p>

            <h1 class="dashboard-title">
                Dashboard
            </h1>

            <p class="dashboard-description">
                Pantau aktivitas dan kondisi ekosistem P.A.D.I.
            </p>
        </div>
    </div>


    <div class="dashboard-stats">

        <div class="dashboard-stat-card">
            <div class="stat-top">
                <div class="stat-icon stat-icon-green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>

                <span class="stat-badge stat-badge-green">
                    Pengguna
                </span>
            </div>

            <p class="stat-label">
                Total Pengguna
            </p>

            <p class="stat-value">
                1.250
            </p>

            <p class="stat-helper">
                Seluruh akun terdaftar
            </p>
        </div>


        <div class="dashboard-stat-card">
            <div class="stat-top">
                <div class="stat-icon stat-icon-green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22V8"/>
                        <path d="M5 12c0-3 2-5 7-5s7 2 7 5"/>
                        <path d="M5 12c0 4 3 7 7 7s7-3 7-7"/>
                        <path d="M12 8c-2-3-1-5 0-6 1 1 2 3 0 6Z"/>
                    </svg>
                </div>

                <span class="stat-badge stat-badge-green">
                    Panen
                </span>
            </div>

            <p class="stat-label">
                Total Panen
            </p>

            <p class="stat-value">
                486
            </p>

            <p class="stat-helper">
                Data hasil panen tercatat
            </p>
        </div>


        <div class="dashboard-stat-card">
            <div class="stat-top">
                <div class="stat-icon stat-icon-orange">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 3v18"/>
                        <path d="M3 12h18"/>
                        <path d="m5 5 14 14"/>
                        <path d="m19 5-14 14"/>
                    </svg>
                </div>

                <span class="stat-badge stat-badge-orange">
                    Perlu dipantau
                </span>
            </div>

            <p class="stat-label">
                Laporan Penyakit
            </p>

            <p class="stat-value">
                127
            </p>

            <p class="stat-helper">
                Scan dan laporan komunitas
            </p>
        </div>


        <div class="dashboard-stat-card">
            <div class="stat-top">
                <div class="stat-icon stat-icon-green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 3h18v18H3z"/>
                        <path d="M7 7h10"/>
                        <path d="M7 11h10"/>
                        <path d="M7 15h6"/>
                    </svg>
                </div>

                <span class="stat-badge stat-badge-green">
                    Marketplace
                </span>
            </div>

            <p class="stat-label">
                Aktivitas Marketplace
            </p>

            <p class="stat-value">
                386
            </p>

            <p class="stat-helper">
                Listing dan penawaran
            </p>
        </div>

    </div>


    <div class="dashboard-main-grid">

        <div class="dashboard-panel activity-panel">

            <div class="panel-header">
                <div>
                    <h2 class="panel-title">
                        Aktivitas Terbaru
                    </h2>

                    <p class="panel-description">
                        Aktivitas terbaru pada platform P.A.D.I.
                    </p>
                </div>

                <button type="button" class="panel-link">
                    Lihat semua
                </button>
            </div>


            <div class="activity-list">

                <div class="activity-item">

                    <div class="activity-icon activity-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                        </svg>
                    </div>

                    <div class="activity-content">
                        <p class="activity-title">
                            Pengguna baru terdaftar
                        </p>

                        <p class="activity-description">
                            Petani baru bergabung ke platform
                        </p>
                    </div>

                    <span class="activity-time">
                        5 menit lalu
                    </span>

                </div>


                <div class="activity-item">

                    <div class="activity-icon activity-orange">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                            <path d="M12 8v4"/>
                            <path d="M12 16h.01"/>
                        </svg>
                    </div>

                    <div class="activity-content">
                        <p class="activity-title">
                            Laporan penyakit membutuhkan validasi
                        </p>

                        <p class="activity-description">
                            Menunggu pemeriksaan dan validasi PPL
                        </p>
                    </div>

                    <span class="activity-time">
                        18 menit lalu
                    </span>

                </div>


                <div class="activity-item">

                    <div class="activity-icon activity-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 3h18v18H3z"/>
                            <path d="M7 7h10"/>
                            <path d="M7 11h10"/>
                            <path d="M7 15h6"/>
                        </svg>
                    </div>

                    <div class="activity-content">
                        <p class="activity-title">
                            Listing hasil panen baru
                        </p>

                        <p class="activity-description">
                            Petani menambahkan hasil panen ke marketplace
                        </p>
                    </div>

                    <span class="activity-time">
                        32 menit lalu
                    </span>

                </div>


                <div class="activity-item">

                    <div class="activity-icon activity-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 3v18"/>
                            <path d="M5 12c0-3 2-5 7-5s7 2 7 5"/>
                            <path d="M5 12c0 4 3 7 7 7s7-3 7-7"/>
                        </svg>
                    </div>

                    <div class="activity-content">
                        <p class="activity-title">
                            Data panen berhasil dicatat
                        </p>

                        <p class="activity-description">
                            Data hasil panen masuk ke sistem
                        </p>
                    </div>

                    <span class="activity-time">
                        1 jam lalu
                    </span>

                </div>

            </div>

        </div>


        <div class="warning-panel">

            <div class="warning-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 3v18"/>
                    <path d="M3 12h18"/>
                    <path d="m5 5 14 14"/>
                    <path d="m19 5-14 14"/>
                </svg>
            </div>

            <h2 class="warning-title">
                Early Warning
            </h2>

            <p class="warning-description">
                Pantau peringatan penyakit dan kondisi wilayah berdasarkan laporan yang masuk dari sistem.
            </p>


            <div class="warning-summary">

                <p class="warning-label">
                    Peringatan aktif
                </p>

                <p class="warning-number">
                    8 wilayah
                </p>

                <p class="warning-helper">
                    Membutuhkan pemantauan
                </p>

            </div>


            <div class="warning-risk">

                <div class="risk-header">
                    <span>
                        Risiko tinggi
                    </span>

                    <strong>
                        3 wilayah
                    </strong>
                </div>

                <div class="risk-progress">
                    <div class="risk-progress-value"></div>
                </div>

            </div>


            <button type="button" class="warning-button">
                Lihat Peringatan
            </button>

        </div>

    </div>


    <div class="dashboard-bottom-grid">

        <div class="dashboard-panel">

            <div class="panel-header">

                <div>
                    <h2 class="panel-title">
                        Tren Penyakit
                    </h2>

                    <p class="panel-description">
                        Ringkasan laporan penyakit tanaman
                    </p>
                </div>

                <span class="panel-badge panel-badge-orange">
                    Bulan ini
                </span>

            </div>


            <div class="disease-list">

                <div class="disease-item">

                    <div class="disease-header">
                        <span>
                            Hawar Daun Bakteri
                        </span>

                        <strong>
                            42 laporan
                        </strong>
                    </div>

                    <div class="disease-progress">
                        <div class="disease-progress-value disease-84"></div>
                    </div>

                </div>


                <div class="disease-item">

                    <div class="disease-header">
                        <span>
                            Blast
                        </span>

                        <strong>
                            31 laporan
                        </strong>
                    </div>

                    <div class="disease-progress">
                        <div class="disease-progress-value disease-62"></div>
                    </div>

                </div>


                <div class="disease-item">

                    <div class="disease-header">
                        <span>
                            Wereng
                        </span>

                        <strong>
                            24 laporan
                        </strong>
                    </div>

                    <div class="disease-progress">
                        <div class="disease-progress-value disease-48"></div>
                    </div>

                </div>


                <div class="disease-item">

                    <div class="disease-header">
                        <span>
                            Tungro
                        </span>

                        <strong>
                            17 laporan
                        </strong>
                    </div>

                    <div class="disease-progress">
                        <div class="disease-progress-value disease-34"></div>
                    </div>

                </div>

            </div>

        </div>


        <div class="dashboard-panel">

            <div class="panel-header">

                <div>
                    <h2 class="panel-title">
                        Monitoring Marketplace
                    </h2>

                    <p class="panel-description">
                        Ringkasan aktivitas pasar hasil panen
                    </p>
                </div>

                <span class="panel-badge panel-badge-green">
                    Aktif
                </span>

            </div>


            <div class="marketplace-grid">

                <div class="marketplace-card">
                    <p>
                        Listing Aktif
                    </p>

                    <strong>
                        86
                    </strong>
                </div>

                <div class="marketplace-card">
                    <p>
                        Penawaran
                    </p>

                    <strong>
                        143
                    </strong>
                </div>

                <div class="marketplace-card">
                    <p>
                        Kontrak Berjalan
                    </p>

                    <strong>
                        27
                    </strong>
                </div>

                <div class="marketplace-card">
                    <p>
                        Menunggu Moderasi
                    </p>

                    <strong class="marketplace-warning">
                        12
                    </strong>
                </div>

            </div>


            <div class="marketplace-footer">

                <div>
                    <p>
                        Aktivitas marketplace
                    </p>

                    <strong>
                        Listing → Penawaran → Kontrak
                    </strong>
                </div>

                <button type="button">
                    Kelola
                </button>

            </div>

        </div>

    </div>


    <div class="dashboard-panel ai-panel">

        <div class="panel-header">

            <div>
                <h2 class="panel-title">
                    Monitoring AI
                </h2>

                <p class="panel-description">
                    Ringkasan proses deteksi penyakit menggunakan AI
                </p>
            </div>

            <span class="panel-badge panel-badge-green">
                Sistem AI
            </span>

        </div>


        <div class="ai-grid">

            <div class="ai-card">
                <p>
                    Total Scan
                </p>

                <strong>
                    1.842
                </strong>
            </div>

            <div class="ai-card">
                <p>
                    Scan Berhasil
                </p>

                <strong class="ai-success">
                    1.756
                </strong>
            </div>

            <div class="ai-card">
                <p>
                    Perlu Validasi PPL
                </p>

                <strong class="ai-warning">
                    64
                </strong>
            </div>

            <div class="ai-card">
                <p>
                    Gagal Diproses
                </p>

                <strong class="ai-danger">
                    22
                </strong>
            </div>

        </div>

    </div>

</div>

@endsection
