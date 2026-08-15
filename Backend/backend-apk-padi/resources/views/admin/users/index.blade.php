@extends('layouts.admin')

@section('content')

<link rel="stylesheet" href="{{ asset('css/admin/users.css') }}">

<div class="users-page">

    <div class="users-header">

        <div class="users-header-content">
            <p class="users-eyebrow">
                Manajemen Admin
            </p>

            <h1 class="users-title">
                Pengguna
            </h1>

            <p class="users-description">
                Kelola pengguna yang terdaftar dalam sistem P.A.D.I.
            </p>
        </div>

        <button type="button" class="users-add-button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14"/>
                <path d="M5 12h14"/>
            </svg>

            <span>Tambah Pengguna</span>
        </button>

    </div>


    <div class="users-stat-grid">

        <div class="users-stat-card">

            <div class="users-stat-content">

                <p class="users-stat-label">
                    Total Pengguna
                </p>

                <p class="users-stat-value">
                    1.248
                </p>

                <p class="users-stat-description">
                    Seluruh pengguna terdaftar
                </p>

            </div>

            <div class="users-stat-icon users-stat-icon-green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>

        </div>


        <div class="users-stat-card">

            <div class="users-stat-content">

                <p class="users-stat-label">
                    Petani
                </p>

                <p class="users-stat-value">
                    986
                </p>

                <p class="users-stat-description">
                    Pengguna dengan role Petani
                </p>

            </div>

            <div class="users-stat-icon users-stat-icon-green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22V8"/>
                    <path d="M5 12c0-3 2-5 7-5s7 2 7 5"/>
                    <path d="M5 12c0 4 3 7 7 7s7-3 7-7"/>
                    <path d="M12 8c-2-3-1-5 0-6 1 1 1 3 0 6Z"/>
                </svg>
            </div>

        </div>


        <div class="users-stat-card">

            <div class="users-stat-content">

                <p class="users-stat-label">
                    PPL
                </p>

                <p class="users-stat-value">
                    184
                </p>

                <p class="users-stat-description">
                    Penyuluh Pertanian Lapangan
                </p>

            </div>

            <div class="users-stat-icon users-stat-icon-yellow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>

        </div>


        <div class="users-stat-card">

            <div class="users-stat-content">

                <p class="users-stat-label">
                    Mitra
                </p>

                <p class="users-stat-value">
                    78
                </p>

                <p class="users-stat-description">
                    Mitra terdaftar
                </p>

            </div>

            <div class="users-stat-icon users-stat-icon-blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>

        </div>

    </div>


    <div class="users-table-card">

        <div class="users-table-header">

            <div class="users-table-heading">

                <h2>
                    Daftar Pengguna
                </h2>

                <p>
                    Daftar pengguna yang terdaftar pada sistem P.A.D.I.
                </p>

            </div>


            <div class="users-filters">

                <div class="users-search">

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.3-4.3"/>
                    </svg>

                    <input
                        type="text"
                        placeholder="Cari pengguna..."
                    >

                </div>


                <select>
                    <option>Semua Role</option>
                    <option>Petani</option>
                    <option>PPL</option>
                    <option>Mitra</option>
                    <option>Admin</option>
                </select>


                <select>
                    <option>Semua Status</option>
                    <option>Aktif</option>
                    <option>Menunggu</option>
                    <option>Nonaktif</option>
                </select>

            </div>

        </div>


        <div class="users-table-wrapper">

            <table class="users-table">

                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Bergabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>


                <tbody>

                    <tr>

                        <td>
                            <div class="users-person">

                                <div class="users-avatar users-avatar-green">
                                    AS
                                </div>

                                <div class="users-person-info">
                                    <p>
                                        Ahmad Setiawan
                                    </p>

                                    <span>
                                        ahmad@example.com
                                    </span>
                                </div>

                            </div>
                        </td>

                        <td>
                            <span class="users-role users-role-green">
                                Petani
                            </span>
                        </td>

                        <td>
                            <span class="users-status users-status-active">
                                <span></span>
                                Aktif
                            </span>
                        </td>

                        <td>
                            <span class="users-date">
                                12 Agustus 2026
                            </span>
                        </td>

                        <td class="users-action-cell">
                            <button type="button" class="users-detail-button">
                                Detail
                            </button>
                        </td>

                    </tr>


                    <tr>

                        <td>
                            <div class="users-person">

                                <div class="users-avatar users-avatar-yellow">
                                    BR
                                </div>

                                <div class="users-person-info">
                                    <p>
                                        Budi Raharjo
                                    </p>

                                    <span>
                                        budi@example.com
                                    </span>
                                </div>

                            </div>
                        </td>

                        <td>
                            <span class="users-role users-role-yellow">
                                PPL
                            </span>
                        </td>

                        <td>
                            <span class="users-status users-status-active">
                                <span></span>
                                Aktif
                            </span>
                        </td>

                        <td>
                            <span class="users-date">
                                10 Agustus 2026
                            </span>
                        </td>

                        <td class="users-action-cell">
                            <button type="button" class="users-detail-button">
                                Detail
                            </button>
                        </td>

                    </tr>


                    <tr>

                        <td>
                            <div class="users-person">

                                <div class="users-avatar users-avatar-blue">
                                    CN
                                </div>

                                <div class="users-person-info">
                                    <p>
                                        Citra Nugraha
                                    </p>

                                    <span>
                                        citra@example.com
                                    </span>
                                </div>

                            </div>
                        </td>

                        <td>
                            <span class="users-role users-role-blue">
                                Mitra
                            </span>
                        </td>

                        <td>
                            <span class="users-status users-status-pending">
                                <span></span>
                                Menunggu
                            </span>
                        </td>

                        <td>
                            <span class="users-date">
                                8 Agustus 2026
                            </span>
                        </td>

                        <td class="users-action-cell">
                            <button type="button" class="users-detail-button">
                                Detail
                            </button>
                        </td>

                    </tr>


                    <tr>

                        <td>
                            <div class="users-person">

                                <div class="users-avatar users-avatar-purple">
                                    DS
                                </div>

                                <div class="users-person-info">
                                    <p>
                                        Dedi Saputra
                                    </p>

                                    <span>
                                        dedi@example.com
                                    </span>
                                </div>

                            </div>
                        </td>

                        <td>
                            <span class="users-role users-role-green">
                                Petani
                            </span>
                        </td>

                        <td>
                            <span class="users-status users-status-inactive">
                                <span></span>
                                Nonaktif
                            </span>
                        </td>

                        <td>
                            <span class="users-date">
                                5 Agustus 2026
                            </span>
                        </td>

                        <td class="users-action-cell">
                            <button type="button" class="users-detail-button">
                                Detail
                            </button>
                        </td>

                    </tr>

                </tbody>

            </table>

        </div>


        <div class="users-pagination">

            <p>
                Menampilkan
                <strong>1–4</strong>
                dari
                <strong>1.248</strong>
                pengguna
            </p>


            <div class="users-pagination-buttons">

                <button type="button" class="users-page-button users-page-disabled">
                    Sebelumnya
                </button>

                <button type="button" class="users-page-button users-page-active">
                    1
                </button>

                <button type="button" class="users-page-button">
                    2
                </button>

                <button type="button" class="users-page-button">
                    Selanjutnya
                </button>

            </div>

        </div>

    </div>

</div>

@endsection
