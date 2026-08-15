@extends('layouts.admin')

@section('content')

<link rel="stylesheet" href="{{ asset('css/admin/marketplace.css') }}">

<div class="marketplace-page">

    <div class="marketplace-container">

        <div class="marketplace-header">

            <div class="marketplace-header-content">

                <p class="marketplace-eyebrow">
                    Manajemen Marketplace
                </p>

                <h1 class="marketplace-title">
                    Marketplace
                </h1>

                <p class="marketplace-description">
                    Pantau listing hasil panen, penawaran pembeli, kontrak, dan transaksi marketplace P.A.D.I.
                </p>

            </div>

            <div class="marketplace-header-badge">

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M3 3h18v18H3z"/>
                    <path d="M7 7h10"/>
                    <path d="M7 11h10"/>
                    <path d="M7 15h6"/>
                </svg>

                <div>

                    <span>
                        Market Service
                    </span>

                    <strong>
                        Monitoring Transaksi
                    </strong>

                </div>

            </div>

        </div>


        <div class="marketplace-stat-grid">

            <div class="marketplace-stat-card">

                <div class="marketplace-stat-top">

                    <span class="marketplace-stat-label">
                        Listing Aktif
                    </span>

                    <div class="marketplace-stat-icon green">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M6 2h12v20H6z"/>
                            <path d="M9 6h6"/>
                            <path d="M9 10h6"/>
                            <path d="M9 14h3"/>
                        </svg>

                    </div>

                </div>

                <div class="marketplace-stat-bottom">

                    <strong>
                        86
                    </strong>

                    <span>
                        Penawaran hasil panen
                    </span>

                </div>

            </div>


            <div class="marketplace-stat-card">

                <div class="marketplace-stat-top">

                    <span class="marketplace-stat-label">
                        Penawaran Masuk
                    </span>

                    <div class="marketplace-stat-icon orange">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M20 7h-9"/>
                            <path d="M20 12h-9"/>
                            <path d="M20 17h-9"/>
                            <path d="M4 7h.01"/>
                            <path d="M4 12h.01"/>
                            <path d="M4 17h.01"/>
                        </svg>

                    </div>

                </div>

                <div class="marketplace-stat-bottom">

                    <strong>
                        34
                    </strong>

                    <span>
                        Menunggu proses
                    </span>

                </div>

            </div>


            <div class="marketplace-stat-card">

                <div class="marketplace-stat-top">

                    <span class="marketplace-stat-label">
                        Kontrak Berjalan
                    </span>

                    <div class="marketplace-stat-icon blue">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <path d="M14 2v6h6"/>
                            <path d="M8 13h8"/>
                            <path d="M8 17h5"/>
                        </svg>

                    </div>

                </div>

                <div class="marketplace-stat-bottom">

                    <strong>
                        18
                    </strong>

                    <span>
                        Kontrak aktif
                    </span>

                </div>

            </div>


            <div class="marketplace-stat-card">

                <div class="marketplace-stat-top">

                    <span class="marketplace-stat-label">
                        Nilai Transaksi
                    </span>

                    <div class="marketplace-stat-icon yellow">

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
                            <path d="M16 8h-5a2 2 0 1 0 0 4h2a2 2 0 1 1 0 4H8"/>
                            <path d="M12 6v2"/>
                            <path d="M12 16v2"/>
                        </svg>

                    </div>

                </div>

                <div class="marketplace-stat-bottom">

                    <strong>
                        Rp 248 Jt
                    </strong>

                    <span>
                        Nilai kontrak aktif
                    </span>

                </div>

            </div>

        </div>


        <div class="marketplace-data-card">

            <div class="marketplace-data-header">

                <div>

                    <h2>
                        Listing Marketplace
                    </h2>

                    <p>
                        Kelola dan pantau penawaran hasil panen dari petani.
                    </p>

                </div>

            </div>


            <div class="marketplace-filter-wrapper">

                <div class="marketplace-filter-grid">

                    <div class="marketplace-search">

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
                            placeholder="Cari komoditas atau petani..."
                        >

                    </div>


                    <select>

                        <option>
                            Semua Komoditas
                        </option>

                        <option>
                            Gabah
                        </option>

                        <option>
                            Beras
                        </option>

                        <option>
                            Pupuk
                        </option>

                    </select>


                    <select>

                        <option>
                            Semua Status
                        </option>

                        <option>
                            Aktif
                        </option>

                        <option>
                            Draft
                        </option>

                        <option>
                            Terjual
                        </option>

                        <option>
                            Kedaluwarsa
                        </option>

                    </select>

                </div>

            </div>


            <div class="marketplace-table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>
                                KOMODITAS
                            </th>

                            <th>
                                PETANI
                            </th>

                            <th>
                                JUMLAH
                            </th>

                            <th>
                                HARGA
                            </th>

                            <th>
                                STATUS
                            </th>

                            <th>
                                PUBLIKASI
                            </th>

                            <th class="right">
                                AKSI
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach([
                            [
                                'commodity' => 'Gabah Kering Panen',
                                'code' => 'GKP',
                                'farmer' => 'Ahmad Setiawan',
                                'location' => 'Indramayu',
                                'quantity' => '2.500 kg',
                                'price' => 'Rp 6.200 / kg',
                                'status' => 'Aktif',
                                'date' => '12 Agustus 2026',
                            ],
                            [
                                'commodity' => 'Beras Medium',
                                'code' => 'Beras',
                                'farmer' => 'Budi Raharjo',
                                'location' => 'Subang',
                                'quantity' => '1.200 kg',
                                'price' => 'Rp 12.500 / kg',
                                'status' => 'Aktif',
                                'date' => '11 Agustus 2026',
                            ],
                            [
                                'commodity' => 'Gabah Kering Panen',
                                'code' => 'GKP',
                                'farmer' => 'Citra Nugraha',
                                'location' => 'Karawang',
                                'quantity' => '3.000 kg',
                                'price' => 'Rp 6.100 / kg',
                                'status' => 'Terjual',
                                'date' => '8 Agustus 2026',
                            ],
                            [
                                'commodity' => 'Pupuk Urea',
                                'code' => 'Pupuk',
                                'farmer' => 'Dedi Saputra',
                                'location' => 'Kramat',
                                'quantity' => '500 kg',
                                'price' => 'Rp 2.200 / kg',
                                'status' => 'Draft',
                                'date' => '-',
                            ],
                        ] as $item)

                            <tr>

                                <td>

                                    <div class="commodity-cell">

                                        <div class="commodity-icon">

                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <path d="M12 22V4"/>
                                                <path d="M8 8c-2-2-3-4-3-6"/>
                                                <path d="M16 8c2-2 3-4 3-6"/>
                                                <path d="M8 14c-2-2-3-4-3-6"/>
                                                <path d="M16 14c2-2 3-4 3-6"/>
                                            </svg>

                                        </div>

                                        <div>

                                            <strong>
                                                {{ $item['commodity'] }}
                                            </strong>

                                            <span>
                                                {{ $item['code'] }}
                                            </span>

                                        </div>

                                    </div>

                                </td>


                                <td>

                                    <div class="farmer-cell">

                                        <strong>
                                            {{ $item['farmer'] }}
                                        </strong>

                                        <span>
                                            {{ $item['location'] }}
                                        </span>

                                    </div>

                                </td>


                                <td>

                                    <span class="quantity-value">
                                        {{ $item['quantity'] }}
                                    </span>

                                </td>


                                <td>

                                    <span class="price-value">
                                        {{ $item['price'] }}
                                    </span>

                                </td>


                                <td>

                                    @if($item['status'] === 'Aktif')

                                        <span class="listing-status active">
                                            Aktif
                                        </span>

                                    @elseif($item['status'] === 'Terjual')

                                        <span class="listing-status sold">
                                            Terjual
                                        </span>

                                    @else

                                        <span class="listing-status draft">
                                            Draft
                                        </span>

                                    @endif

                                </td>


                                <td>

                                    <span class="publish-date">
                                        {{ $item['date'] }}
                                    </span>

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


            <div class="marketplace-pagination-wrapper">

                <p>
                    Menampilkan
                    <strong>1–4</strong>
                    dari
                    <strong>86</strong>
                    listing
                </p>

                <div class="marketplace-pagination">

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
                        class="pagination-number"
                    >
                        3
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


        <div class="marketplace-secondary-grid">

            <div class="marketplace-secondary-card">

                <div class="secondary-card-header">

                    <div>

                        <h2>
                            Penawaran Masuk
                        </h2>

                        <p>
                            Penawaran harga dari pembeli terhadap listing petani.
                        </p>

                    </div>

                    <span>
                        34 Penawaran
                    </span>

                </div>


                <div class="offer-list">

                    <div class="offer-item">

                        <div class="offer-avatar">
                            M
                        </div>

                        <div class="offer-content">

                            <strong>
                                Mitra Beras Nusantara
                            </strong>

                            <span>
                                Gabah Kering Panen · 1.000 kg
                            </span>

                        </div>

                        <div class="offer-price">

                            <strong>
                                Rp 6.350/kg
                            </strong>

                            <span>
                                Menunggu
                            </span>

                        </div>

                    </div>


                    <div class="offer-item">

                        <div class="offer-avatar">
                            P
                        </div>

                        <div class="offer-content">

                            <strong>
                                PT Pangan Sejahtera
                            </strong>

                            <span>
                                Beras Medium · 500 kg
                            </span>

                        </div>

                        <div class="offer-price">

                            <strong>
                                Rp 12.700/kg
                            </strong>

                            <span>
                                Menunggu
                            </span>

                        </div>

                    </div>


                    <div class="offer-item">

                        <div class="offer-avatar">
                            K
                        </div>

                        <div class="offer-content">

                            <strong>
                                Koperasi Tani Makmur
                            </strong>

                            <span>
                                Gabah Kering Panen · 2.000 kg
                            </span>

                        </div>

                        <div class="offer-price">

                            <strong>
                                Rp 6.250/kg
                            </strong>

                            <span>
                                Diproses
                            </span>

                        </div>

                    </div>

                </div>

            </div>


            <div class="marketplace-secondary-card">

                <div class="secondary-card-header">

                    <div>

                        <h2>
                            Kontrak Berjalan
                        </h2>

                        <p>
                            Ringkasan kontrak pembelian yang sedang berlangsung.
                        </p>

                    </div>

                </div>


                <div class="contract-summary">

                    <div class="contract-summary-item">

                        <span>
                            Kontrak aktif
                        </span>

                        <strong>
                            18
                        </strong>

                    </div>


                    <div class="contract-summary-item">

                        <span>
                            Menunggu DP
                        </span>

                        <strong>
                            5
                        </strong>

                    </div>


                    <div class="contract-summary-item">

                        <span>
                            Selesai
                        </span>

                        <strong>
                            42
                        </strong>

                    </div>

                </div>


                <div class="contract-progress">

                    <div class="contract-progress-header">

                        <span>
                            Proses transaksi
                        </span>

                        <strong>
                            72%
                        </strong>

                    </div>

                    <div class="progress-track">

                        <div class="progress-value"></div>

                    </div>

                    <p>
                        Sebagian besar kontrak aktif sudah memasuki proses pembayaran.
                    </p>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
