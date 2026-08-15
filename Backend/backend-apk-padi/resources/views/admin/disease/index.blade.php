@extends('layouts.admin')

@section('content')

<link rel="stylesheet" href="{{ asset('css/admin/disease.css') }}">

<div class="penyakit-page">

    <div class="penyakit-container">

        <div class="penyakit-header">

            <div class="penyakit-header-content">

                <p class="penyakit-eyebrow">
                    Manajemen Penyakit
                </p>

                <h1 class="penyakit-title">
                    Laporan Penyakit
                </h1>

                <p class="penyakit-description">
                    Pantau hasil deteksi penyakit tanaman dan proses validasi laporan P.A.D.I.
                </p>

            </div>

            <div class="penyakit-header-badge">

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

                <div>
                    <span>Monitoring AI</span>
                    <strong>Deteksi Penyakit</strong>
                </div>

            </div>

        </div>


        <div class="penyakit-stat-grid">

            <div class="penyakit-stat-card">

                <div class="penyakit-stat-top">

                    <span class="penyakit-stat-label">
                        Total Scan
                    </span>

                    <div class="penyakit-stat-icon green">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <path d="m21 15-5-5L5 21"/>
                        </svg>

                    </div>

                </div>

                <div class="penyakit-stat-bottom">

                    <strong>
                        428
                    </strong>

                    <span>
                        Hasil scan tanaman
                    </span>

                </div>

            </div>


            <div class="penyakit-stat-card">

                <div class="penyakit-stat-top">

                    <span class="penyakit-stat-label">
                        Terdeteksi Penyakit
                    </span>

                    <div class="penyakit-stat-icon red">

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

                </div>

                <div class="penyakit-stat-bottom">

                    <strong>
                        187
                    </strong>

                    <span>
                        Hasil dengan penyakit
                    </span>

                </div>

            </div>


            <div class="penyakit-stat-card">

                <div class="penyakit-stat-top">

                    <span class="penyakit-stat-label">
                        Perlu Validasi
                    </span>

                    <div class="penyakit-stat-icon orange">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 7v5l3 2"/>
                        </svg>

                    </div>

                </div>

                <div class="penyakit-stat-bottom">

                    <strong>
                        64
                    </strong>

                    <span>
                        Menunggu pemeriksaan
                    </span>

                </div>

            </div>


            <div class="penyakit-stat-card">

                <div class="penyakit-stat-top">

                    <span class="penyakit-stat-label">
                        Sudah Validasi
                    </span>

                    <div class="penyakit-stat-icon blue">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M20 6 9 17l-5-5"/>
                        </svg>

                    </div>

                </div>

                <div class="penyakit-stat-bottom">

                    <strong>
                        124
                    </strong>

                    <span>
                        Telah diperiksa PPL
                    </span>

                </div>

            </div>

        </div>


        <div class="penyakit-data-card">

            <div class="penyakit-data-header">

                <div>

                    <h2>
                        Data Laporan Penyakit
                    </h2>

                    <p>
                        Daftar hasil deteksi penyakit tanaman dari pengguna P.A.D.I.
                    </p>

                </div>

            </div>


            <div class="penyakit-filter-wrapper">

                <div class="penyakit-filter-grid">

                    <div class="penyakit-search">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.35-4.35"/>
                        </svg>

                        <input
                            type="text"
                            placeholder="Cari petani, lahan, atau penyakit..."
                        >

                    </div>


                    <select>

                        <option>
                            Semua Hasil
                        </option>

                        <option>
                            Blast
                        </option>

                        <option>
                            Tungro
                        </option>

                        <option>
                            Hawar Daun Bakteri
                        </option>

                    </select>


                    <select>

                        <option>
                            Semua Status
                        </option>

                        <option>
                            Selesai
                        </option>

                        <option>
                            Diproses
                        </option>

                        <option>
                            Gagal
                        </option>

                    </select>

                </div>

            </div>


            <div class="penyakit-table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>
                                PETANI
                            </th>

                            <th>
                                LAHAN
                            </th>

                            <th>
                                HASIL DETEKSI
                            </th>

                            <th>
                                CONFIDENCE
                            </th>

                            <th>
                                STATUS
                            </th>

                            <th>
                                SCAN
                            </th>

                            <th class="right">
                                AKSI
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach([
                            [
                                'initial' => 'AS',
                                'name' => 'Ahmad Setiawan',
                                'farm' => 'Sawah Sukamaju',
                                'location' => 'Indramayu',
                                'disease' => 'Blast',
                                'confidence' => '92%',
                                'quality' => 'Lolos',
                                'status' => 'Selesai',
                                'date' => '12 Agustus 2026',
                            ],
                            [
                                'initial' => 'BR',
                                'name' => 'Budi Raharjo',
                                'farm' => 'Lahan Makmur',
                                'location' => 'Kramat',
                                'disease' => 'Tungro',
                                'confidence' => '87%',
                                'quality' => 'Lolos',
                                'status' => 'Selesai',
                                'date' => '10 Agustus 2026',
                            ],
                            [
                                'initial' => 'CN',
                                'name' => 'Citra Nugraha',
                                'farm' => 'Sawah Mekar',
                                'location' => 'Subang',
                                'disease' => 'Hawar Daun Bakteri',
                                'confidence' => '81%',
                                'quality' => 'Lolos',
                                'status' => 'Diproses',
                                'date' => '8 Agustus 2026',
                            ],
                            [
                                'initial' => 'DS',
                                'name' => 'Dedi Saputra',
                                'farm' => 'Sawah Harapan',
                                'location' => 'Jalancagak',
                                'disease' => 'Belum terdeteksi',
                                'confidence' => '-',
                                'quality' => 'Ditolak',
                                'status' => 'Gagal',
                                'date' => '5 Agustus 2026',
                            ],
                        ] as $item)

                            <tr>

                                <td>

                                    <div class="penyakit-farmer">

                                        <div class="penyakit-avatar">
                                            {{ $item['initial'] }}
                                        </div>

                                        <div>

                                            <p>
                                                {{ $item['name'] }}
                                            </p>

                                            <span>
                                                {{ $item['location'] }}
                                            </span>

                                        </div>

                                    </div>

                                </td>


                                <td>

                                    <p class="penyakit-farm-name">
                                        {{ $item['farm'] }}
                                    </p>

                                </td>


                                <td>

                                    <div class="penyakit-result">

                                        <strong>
                                            {{ $item['disease'] }}
                                        </strong>

                                        <span>
                                            Hasil deteksi AI
                                        </span>

                                    </div>

                                </td>


                                <td>

                                    <span class="confidence-value">
                                        {{ $item['confidence'] }}
                                    </span>

                                </td>


                                <td>

                                    @if($item['status'] === 'Selesai')

                                        <span class="status-badge success">
                                            Selesai
                                        </span>

                                    @elseif($item['status'] === 'Diproses')

                                        <span class="status-badge warning">
                                            Diproses
                                        </span>

                                    @else

                                        <span class="status-badge danger">
                                            Gagal
                                        </span>

                                    @endif

                                </td>


                                <td>

                                    <div class="scan-date">

                                        <strong>
                                            {{ $item['date'] }}
                                        </strong>

                                        <span>
                                            Kualitas: {{ $item['quality'] }}
                                        </span>

                                    </div>

                                </td>


                                <td class="action-cell">

                                    <button
                                        type="button"
                                        class="detail-button"
                                    >
                                        Detail
                                    </button>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            <div class="penyakit-pagination-wrapper">

                <p class="penyakit-pagination-info">

                    Menampilkan
                    <strong>1–4</strong>
                    dari
                    <strong>428</strong>
                    laporan

                </p>


                <div class="penyakit-pagination">

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
