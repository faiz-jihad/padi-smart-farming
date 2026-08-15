@extends('layouts.admin')

@section('content')

<link rel="stylesheet" href="{{ asset('css/admin/agriculture.css') }}">

<div class="pertanian-page">

    <div class="pertanian-container">

        <div class="pertanian-header">

            <div class="pertanian-header-content">

                <p class="pertanian-eyebrow">
                    Manajemen Pertanian
                </p>

                <h1 class="pertanian-title">
                    Pertanian
                </h1>

                <p class="pertanian-description">
                    Kelola data petani, lahan, musim tanam, dan hasil pertanian P.A.D.I.
                </p>

            </div>

            <button type="button" class="btn-add-land">

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
                    Tambah Data Lahan
                </span>

            </button>

        </div>


        <div class="stat-grid">

            <div class="stat-card">

                <div class="stat-top">

                    <span class="stat-label">
                        Total Petani
                    </span>

                    <div class="stat-icon stat-icon-green">

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

                <div class="stat-bottom">

                    <div class="stat-number">
                        986
                    </div>

                    <div class="stat-description">
                        Petani terdaftar
                    </div>

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-top">

                    <span class="stat-label">
                        Total Lahan
                    </span>

                    <div class="stat-icon stat-icon-dark">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M3 21h18"/>
                            <path d="M5 21V9l7-5 7 5v12"/>
                            <path d="M9 21v-6h6v6"/>
                        </svg>

                    </div>

                </div>

                <div class="stat-bottom">

                    <div class="stat-number">
                        428
                    </div>

                    <div class="stat-description">
                        Lahan terdaftar
                    </div>

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-top">

                    <span class="stat-label">
                        Musim Tanam Aktif
                    </span>

                    <div class="stat-icon stat-icon-orange">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M12 22V8"/>
                            <path d="M5 12c0-3 2-5 7-5s7 2 7 5"/>
                            <path d="M5 12c0 4 3 7 7 7s7-3 7-7"/>
                            <path d="M12 8c-2-3-1-5 0-6 1 1 1 3 0 6Z"/>
                        </svg>

                    </div>

                </div>

                <div class="stat-bottom">

                    <div class="stat-number">
                        312
                    </div>

                    <div class="stat-description">
                        Musim sedang berjalan
                    </div>

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-top">

                    <span class="stat-label">
                        Total Panen
                    </span>

                    <div class="stat-icon stat-icon-dark">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                        >
                            <path d="M12 3v18"/>
                            <path d="M3 12h18"/>
                            <path d="m5 5 14 14"/>
                            <path d="m19 5-14 14"/>
                        </svg>

                    </div>

                </div>

                <div class="stat-bottom">

                    <div class="stat-number">
                        187
                    </div>

                    <div class="stat-description">
                        Data hasil panen
                    </div>

                </div>

            </div>

        </div>


        <div class="data-card">

            <div class="data-header">

                <h2>
                    Data Petani dan Lahan
                </h2>

                <p>
                    Informasi petani dan lahan pertanian yang terdaftar pada sistem.
                </p>

            </div>


            <div class="filter-wrapper">

                <div class="filter-grid">

                    <div class="search-box">

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
                            placeholder="Cari petani atau lahan..."
                        >

                    </div>


                    <select>
                        <option>Semua Provinsi</option>
                        <option>Jawa Barat</option>
                        <option>Jawa Tengah</option>
                    </select>


                    <select>
                        <option>Semua Irigasi</option>
                        <option>Teknis</option>
                        <option>Tadah Hujan</option>
                        <option>Semi Teknis</option>
                    </select>

                </div>

            </div>


            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>
                                PETANI
                            </th>

                            <th>
                                LOKASI
                            </th>

                            <th>
                                LAHAN
                            </th>

                            <th>
                                LUAS
                            </th>

                            <th>
                                IRIGASI
                            </th>

                            <th class="text-right">
                                AKSI
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach([
                            [
                                'initial' => 'AS',
                                'name' => 'Ahmad Setiawan',
                                'phone' => '0812••••••21',
                                'location' => 'Indramayu',
                                'province' => 'Jawa Barat',
                                'land' => 'Sawah Sukamaju',
                                'area' => '1,20 ha',
                                'irrigation' => 'Teknis',
                            ],
                            [
                                'initial' => 'BR',
                                'name' => 'Budi Raharjo',
                                'phone' => '0813••••••45',
                                'location' => 'Kramat',
                                'province' => 'Jawa Tengah',
                                'land' => 'Lahan Makmur',
                                'area' => '2,50 ha',
                                'irrigation' => 'Tadah Hujan',
                            ],
                            [
                                'initial' => 'CN',
                                'name' => 'Citra Nugraha',
                                'phone' => '0857••••••12',
                                'location' => 'Subang',
                                'province' => 'Jawa Barat',
                                'land' => 'Sawah Mekar',
                                'area' => '0,80 ha',
                                'irrigation' => 'Teknis',
                            ],
                            [
                                'initial' => 'DS',
                                'name' => 'Dedi Saputra',
                                'phone' => '0821••••••73',
                                'location' => 'Jalancagak',
                                'province' => 'Jawa Barat',
                                'land' => 'Sawah Harapan',
                                'area' => '1,75 ha',
                                'irrigation' => 'Tadah Hujan',
                            ],
                        ] as $item)

                            <tr>

                                <td>

                                    <div class="farmer-cell">

                                        <div class="farmer-avatar">
                                            {{ $item['initial'] }}
                                        </div>

                                        <div>

                                            <p class="farmer-name">
                                                {{ $item['name'] }}
                                            </p>

                                            <p class="farmer-phone">
                                                {{ $item['phone'] }}
                                            </p>

                                        </div>

                                    </div>

                                </td>


                                <td>

                                    <p class="location-name">
                                        {{ $item['location'] }}
                                    </p>

                                    <p class="location-province">
                                        {{ $item['province'] }}
                                    </p>

                                </td>


                                <td class="table-text">
                                    {{ $item['land'] }}
                                </td>


                                <td class="table-text">
                                    {{ $item['area'] }}
                                </td>


                                <td>

                                    @if($item['irrigation'] === 'Teknis')

                                        <span class="irrigation-badge irrigation-green">
                                            Teknis
                                        </span>

                                    @else

                                        <span class="irrigation-badge irrigation-orange">
                                            Tadah Hujan
                                        </span>

                                    @endif

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


            <div class="pagination-wrapper">

                <p class="pagination-info">
                    Menampilkan
                    <strong>1–4</strong>
                    dari
                    <strong>428</strong>
                    lahan
                </p>


                <div class="pagination">

                    <button
                        type="button"
                        class="pagination-button pagination-prev"
                        disabled
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
