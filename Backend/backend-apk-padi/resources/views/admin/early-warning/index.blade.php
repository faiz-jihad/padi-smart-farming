@extends('layouts.admin')

@section('content')

<link rel="stylesheet" href="{{ asset('css/admin/early-warning.css') }}">

<div class="early-warning-page">

    <div class="early-warning-container">

        <div class="early-warning-header">

            <div class="early-warning-header-content">

                <p class="early-warning-eyebrow">
                    Sistem Peringatan Dini
                </p>

                <h1 class="early-warning-title">
                    Early Warning
                </h1>

                <p class="early-warning-description">
                    Pantau laporan kondisi penyakit dan kelola peringatan dini berdasarkan wilayah petani.
                </p>

            </div>

            <button
                type="button"
                class="early-warning-add-button"
            >

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                >
                    <path d="M12 5v14"/>
                    <path d="M5 12h14"/>
                </svg>

                <span>
                    Buat Peringatan
                </span>

            </button>

        </div>


        <div class="early-warning-stat-grid">

            {{-- TOTAL PERINGATAN --}}
            <div class="early-warning-stat-card">

                <div class="early-warning-stat-top">

                    <span class="early-warning-stat-label">
                        Total Peringatan
                    </span>

                    <div class="early-warning-stat-icon green">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                            <path d="M10 21h4"/>
                        </svg>

                    </div>

                </div>

                <div class="early-warning-stat-bottom">

                    <strong>
                        24
                    </strong>

                    <span>
                        Peringatan tercatat
                    </span>

                </div>

            </div>


            {{-- PERINGATAN AKTIF --}}
            <div class="early-warning-stat-card">

                <div class="early-warning-stat-top">

                    <span class="early-warning-stat-label">
                        Peringatan Aktif
                    </span>

                    <div class="early-warning-stat-icon orange">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M12 2v6"/>
                            <path d="M12 16v6"/>
                            <path d="m4.93 4.93 4.24 4.24"/>
                            <path d="m14.83 14.83 4.24 4.24"/>
                            <path d="M2 12h6"/>
                            <path d="M16 12h6"/>
                            <path d="m4.93 19.07 4.24-4.24"/>
                            <path d="m14.83 9.17 4.24-4.24"/>
                        </svg>

                    </div>

                </div>

                <div class="early-warning-stat-bottom">

                    <strong>
                        8
                    </strong>

                    <span>
                        Wilayah terdampak
                    </span>

                </div>

            </div>


            {{-- LAPORAN KOMUNITAS --}}
            <div class="early-warning-stat-card">

                <div class="early-warning-stat-top">

                    <span class="early-warning-stat-label">
                        Laporan Komunitas
                    </span>

                    <div class="early-warning-stat-icon red">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/>
                            <path d="M8 9h8"/>
                            <path d="M8 13h5"/>
                        </svg>

                    </div>

                </div>

                <div class="early-warning-stat-bottom">

                    <strong>
                        37
                    </strong>

                    <span>
                        Laporan penyakit
                    </span>

                </div>

            </div>


            {{-- PENERIMA PERINGATAN --}}
            <div class="early-warning-stat-card">

                <div class="early-warning-stat-top">

                    <span class="early-warning-stat-label">
                        Penerima Peringatan
                    </span>

                    <div class="early-warning-stat-icon blue">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>

                    </div>

                </div>

                <div class="early-warning-stat-bottom">

                    <strong>
                        186
                    </strong>

                    <span>
                        Petani berlangganan
                    </span>

                </div>

            </div>

        </div>


        {{-- PERINGATAN AKTIF --}}
        <div class="early-warning-main-grid">

            <div class="early-warning-panel">

                <div class="early-warning-panel-header">

                    <div>

                        <h2>
                            Peringatan Aktif
                        </h2>

                        <p>
                            Daftar peringatan yang sedang berlaku di wilayah petani.
                        </p>

                    </div>

                    <span class="active-warning-count">
                        8 Aktif
                    </span>

                </div>


                <div class="warning-list">

                    @foreach([
                        [
                            'title' => 'Potensi Serangan Blast',
                            'region' => 'Indramayu',
                            'detail' => 'Terdapat laporan penyakit yang konsisten di sekitar wilayah.',
                            'severity' => 'Tinggi',
                            'type' => 'danger',
                            'time' => '18 menit lalu',
                        ],
                        [
                            'title' => 'Peringatan Kondisi Tanaman',
                            'region' => 'Subang',
                            'detail' => 'Beberapa laporan menunjukkan peningkatan kasus penyakit.',
                            'severity' => 'Waspada',
                            'type' => 'warning',
                            'time' => '42 menit lalu',
                        ],
                        [
                            'title' => 'Pemantauan Penyakit',
                            'region' => 'Kramat',
                            'detail' => 'Wilayah masuk dalam pemantauan berdasarkan laporan petani.',
                            'severity' => 'Informasi',
                            'type' => 'info',
                            'time' => '1 jam lalu',
                        ],
                    ] as $warning)

                        <div class="warning-item">

                            <div class="warning-icon {{ $warning['type'] }}">

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                                    <path d="M12 8v4"/>
                                    <path d="M12 16h.01"/>
                                </svg>

                            </div>


                            <div class="warning-content">

                                <div class="warning-title-row">

                                    <div>

                                        <h3>
                                            {{ $warning['title'] }}
                                        </h3>

                                        <span>
                                            {{ $warning['region'] }}
                                        </span>

                                    </div>

                                    <span class="severity-badge {{ $warning['type'] }}">
                                        {{ $warning['severity'] }}
                                    </span>

                                </div>

                                <p>
                                    {{ $warning['detail'] }}
                                </p>

                                <small>
                                    {{ $warning['time'] }}
                                </small>

                            </div>


                            <button
                                type="button"
                                class="warning-detail-button"
                            >
                                Detail
                            </button>

                        </div>

                    @endforeach

                </div>

            </div>


            {{-- RINGKASAN WILAYAH --}}
            <div class="early-warning-region-panel">

                <div class="early-warning-region-header">

                    <div>

                        <h2>
                            Wilayah Terdampak
                        </h2>

                        <p>
                            Ringkasan kondisi berdasarkan laporan.
                        </p>

                    </div>

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>

                </div>


                <div class="region-summary">

                    <div class="region-row">

                        <div>

                            <strong>
                                Indramayu
                            </strong>

                            <span>
                                12 laporan
                            </span>

                        </div>

                        <span class="region-status danger">
                            Tinggi
                        </span>

                    </div>


                    <div class="region-row">

                        <div>

                            <strong>
                                Subang
                            </strong>

                            <span>
                                8 laporan
                            </span>

                        </div>

                        <span class="region-status warning">
                            Waspada
                        </span>

                    </div>


                    <div class="region-row">

                        <div>

                            <strong>
                                Karawang
                            </strong>

                            <span>
                                5 laporan
                            </span>

                        </div>

                        <span class="region-status warning">
                            Waspada
                        </span>

                    </div>


                    <div class="region-row">

                        <div>

                            <strong>
                                Kramat
                            </strong>

                            <span>
                                3 laporan
                            </span>

                        </div>

                        <span class="region-status info">
                            Informasi
                        </span>

                    </div>

                </div>


                <button
                    type="button"
                    class="region-map-button"
                >

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>

                    Lihat Peta Peringatan

                </button>

            </div>

        </div>


        {{-- BROADCAST --}}
        <div class="broadcast-panel">

            <div class="broadcast-header">

                <div>

                    <h2>
                        Riwayat Broadcast
                    </h2>

                    <p>
                        Notifikasi peringatan yang dibuat oleh administrator.
                    </p>

                </div>

                <button
                    type="button"
                    class="broadcast-link"
                >
                    Lihat Semua
                </button>

            </div>


            <div class="broadcast-table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>
                                JUDUL
                            </th>

                            <th>
                                TIPE
                            </th>

                            <th>
                                STATUS
                            </th>

                            <th>
                                DITERBITKAN
                            </th>

                            <th class="right">
                                AKSI
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach([
                            [
                                'title' => 'Peringatan Potensi Blast',
                                'type' => 'Peringatan',
                                'status' => 'Terbit',
                                'date' => '12 Agustus 2026',
                            ],
                            [
                                'title' => 'Pemantauan Penyakit Wilayah Subang',
                                'type' => 'Informasi',
                                'status' => 'Terbit',
                                'date' => '10 Agustus 2026',
                            ],
                            [
                                'title' => 'Kondisi Tanaman Musim Hujan',
                                'type' => 'Peringatan',
                                'status' => 'Draft',
                                'date' => '-',
                            ],
                        ] as $broadcast)

                            <tr>

                                <td>

                                    <strong class="broadcast-title">
                                        {{ $broadcast['title'] }}
                                    </strong>

                                </td>

                                <td>

                                    <span class="broadcast-type">
                                        {{ $broadcast['type'] }}
                                    </span>

                                </td>

                                <td>

                                    @if($broadcast['status'] === 'Terbit')

                                        <span class="broadcast-status published">
                                            Terbit
                                        </span>

                                    @else

                                        <span class="broadcast-status draft">
                                            Draft
                                        </span>

                                    @endif

                                </td>

                                <td>

                                    <span class="broadcast-date">
                                        {{ $broadcast['date'] }}
                                    </span>

                                </td>

                                <td class="broadcast-action">

                                    <button
                                        type="button"
                                        class="broadcast-detail-button"
                                    >
                                        Detail
                                    </button>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            <div class="early-warning-pagination-wrapper">

                <p>
                    Menampilkan
                    <strong>1–3</strong>
                    dari
                    <strong>24</strong>
                    broadcast
                </p>

                <div class="early-warning-pagination">

                    <button
                        type="button"
                        disabled
                        class="pagination-button pagination-prev"
                    >
                        Sebelumnya
                    </button>

                    <button
                        type="button"
                        class="pagination-number active"
                    >
                        1
                    </button>

                    <button
                        type="button"
                        class="pagination-number"
                    >
                        2
                    </button>

                    <button
                        type="button"
                        class="pagination-button pagination-next"
                    >
                        Selanjutnya
                    </button>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
