@extends('layouts.admin')

@section('content')

<link rel="stylesheet" href="{{ asset('css/admin/broadcast.css') }}">

<div class="broadcast-page">

    <div class="broadcast-container">

        <div class="broadcast-header">

            <div>
                <p class="broadcast-eyebrow">
                    Sistem P.A.D.I.
                </p>

                <h1 class="broadcast-title">
                    Broadcast
                </h1>

                <p class="broadcast-description">
                    Kelola pengumuman dan informasi yang dibuat oleh administrator.
                </p>
            </div>

            <button type="button" class="broadcast-add-button">

                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14"/>
                    <path d="M5 12h14"/>
                </svg>

                <span>Buat Broadcast</span>

            </button>

        </div>


        <div class="broadcast-stat-grid">

            <div class="broadcast-stat-card">

                <div class="broadcast-stat-icon green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 12h18"/>
                        <path d="M12 3v18"/>
                    </svg>
                </div>

                <div>
                    <span>Total Broadcast</span>
                    <strong>24</strong>
                </div>

            </div>


            <div class="broadcast-stat-card">

                <div class="broadcast-stat-icon blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m5 12 5 5L20 7"/>
                    </svg>
                </div>

                <div>
                    <span>Published</span>
                    <strong>18</strong>
                </div>

            </div>


            <div class="broadcast-stat-card">

                <div class="broadcast-stat-icon yellow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 7v5l3 2"/>
                    </svg>
                </div>

                <div>
                    <span>Draft</span>
                    <strong>4</strong>
                </div>

            </div>


            <div class="broadcast-stat-card">

                <div class="broadcast-stat-icon red">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="m15 9-6 6"/>
                        <path d="m9 9 6 6"/>
                    </svg>
                </div>

                <div>
                    <span>Expired</span>
                    <strong>2</strong>
                </div>

            </div>

        </div>


        <div class="broadcast-card">

            <div class="broadcast-toolbar">

                <div class="broadcast-tabs">

                    <button type="button" class="broadcast-tab active">
                        Semua
                    </button>

                    <button type="button" class="broadcast-tab">
                        Draft
                    </button>

                    <button type="button" class="broadcast-tab">
                        Published
                    </button>

                    <button type="button" class="broadcast-tab">
                        Expired
                    </button>

                </div>


                <div class="broadcast-search">

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7"/>
                        <path d="m20 20-4-4"/>
                    </svg>

                    <input
                        type="text"
                        placeholder="Cari broadcast..."
                    >

                </div>

            </div>


            <div class="broadcast-table-wrapper">

                <table class="broadcast-table">

                    <thead>
                        <tr>
                            <th>JUDUL</th>
                            <th>TIPE</th>
                            <th>STATUS</th>
                            <th>PUBLISHED</th>
                            <th>EXPIRED</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr>

                            <td>
                                <div class="broadcast-title-cell">
                                    <strong>
                                        Informasi Jadwal Tanam
                                    </strong>

                                    <span>
                                        Informasi jadwal tanam padi untuk periode berikutnya.
                                    </span>
                                </div>
                            </td>

                            <td>
                                <span class="broadcast-type type-info">
                                    Info
                                </span>
                            </td>

                            <td>
                                <span class="broadcast-status published">
                                    Published
                                </span>
                            </td>

                            <td>
                                <span class="broadcast-date">
                                    15 Agu 2026 08:00
                                </span>
                            </td>

                            <td>
                                <span class="broadcast-date">
                                    30 Agu 2026 23:59
                                </span>
                            </td>

                            <td>
                                <div class="broadcast-actions">

                                    <button type="button" class="broadcast-action view" title="Lihat">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"/>
                                            <circle cx="12" cy="12" r="2.5"/>
                                        </svg>
                                    </button>

                                    <button type="button" class="broadcast-action edit" title="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                        </svg>
                                    </button>

                                    <button type="button" class="broadcast-action delete" title="Hapus">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 6h18"/>
                                            <path d="M8 6V4h8v2"/>
                                            <path d="M19 6l-1 15H6L5 6"/>
                                            <path d="M10 11v6"/>
                                            <path d="M14 11v6"/>
                                        </svg>
                                    </button>

                                </div>
                            </td>

                        </tr>


                        <tr>

                            <td>
                                <div class="broadcast-title-cell">
                                    <strong>
                                        Peringatan Cuaca
                                    </strong>

                                    <span>
                                        Himbauan kepada petani terkait kondisi cuaca wilayah.
                                    </span>
                                </div>
                            </td>

                            <td>
                                <span class="broadcast-type type-warning">
                                    Warning
                                </span>
                            </td>

                            <td>
                                <span class="broadcast-status published">
                                    Published
                                </span>
                            </td>

                            <td>
                                <span class="broadcast-date">
                                    14 Agu 2026 10:30
                                </span>
                            </td>

                            <td>
                                <span class="broadcast-date">
                                    20 Agu 2026 23:59
                                </span>
                            </td>

                            <td>
                                <div class="broadcast-actions">

                                    <button type="button" class="broadcast-action view" title="Lihat">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"/>
                                            <circle cx="12" cy="12" r="2.5"/>
                                        </svg>
                                    </button>

                                    <button type="button" class="broadcast-action edit" title="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                        </svg>
                                    </button>

                                    <button type="button" class="broadcast-action delete" title="Hapus">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 6h18"/>
                                            <path d="M8 6V4h8v2"/>
                                            <path d="M19 6l-1 15H6L5 6"/>
                                            <path d="M10 11v6"/>
                                            <path d="M14 11v6"/>
                                        </svg>
                                    </button>

                                </div>
                            </td>

                        </tr>


                        <tr>

                            <td>
                                <div class="broadcast-title-cell">
                                    <strong>
                                        Program Bantuan Petani
                                    </strong>

                                    <span>
                                        Informasi program bantuan dan pendaftaran bagi petani.
                                    </span>
                                </div>
                            </td>

                            <td>
                                <span class="broadcast-type type-announcement">
                                    Announcement
                                </span>
                            </td>

                            <td>
                                <span class="broadcast-status draft">
                                    Draft
                                </span>
                            </td>

                            <td>
                                <span class="broadcast-date">
                                    -
                                </span>
                            </td>

                            <td>
                                <span class="broadcast-date">
                                    -
                                </span>
                            </td>

                            <td>
                                <div class="broadcast-actions">

                                    <button type="button" class="broadcast-action view" title="Lihat">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"/>
                                            <circle cx="12" cy="12" r="2.5"/>
                                        </svg>
                                    </button>

                                    <button type="button" class="broadcast-action edit" title="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                        </svg>
                                    </button>

                                    <button type="button" class="broadcast-action delete" title="Hapus">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 6h18"/>
                                            <path d="M8 6V4h8v2"/>
                                            <path d="M19 6l-1 15H6L5 6"/>
                                            <path d="M10 11v6"/>
                                            <path d="M14 11v6"/>
                                        </svg>
                                    </button>

                                </div>
                            </td>

                        </tr>


                        <tr>

                            <td>
                                <div class="broadcast-title-cell">
                                    <strong>
                                        Pemeliharaan Sistem
                                    </strong>

                                    <span>
                                        Pemberitahuan pemeliharaan sistem P.A.D.I.
                                    </span>
                                </div>
                            </td>

                            <td>
                                <span class="broadcast-type type-system">
                                    System
                                </span>
                            </td>

                            <td>
                                <span class="broadcast-status expired">
                                    Expired
                                </span>
                            </td>

                            <td>
                                <span class="broadcast-date">
                                    01 Agu 2026 20:00
                                </span>
                            </td>

                            <td>
                                <span class="broadcast-date">
                                    02 Agu 2026 02:00
                                </span>
                            </td>

                            <td>
                                <div class="broadcast-actions">

                                    <button type="button" class="broadcast-action view" title="Lihat">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"/>
                                            <circle cx="12" cy="12" r="2.5"/>
                                        </svg>
                                    </button>

                                    <button type="button" class="broadcast-action edit" title="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                        </svg>
                                    </button>

                                    <button type="button" class="broadcast-action delete" title="Hapus">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 6h18"/>
                                            <path d="M8 6V4h8v2"/>
                                            <path d="M19 6l-1 15H6L5 6"/>
                                            <path d="M10 11v6"/>
                                            <path d="M14 11v6"/>
                                        </svg>
                                    </button>

                                </div>
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>


            <div class="broadcast-pagination">

                <p>
                    Menampilkan <strong>1–4</strong> dari <strong>24</strong> broadcast
                </p>

                <div class="broadcast-pagination-buttons">

                    <button type="button" class="broadcast-page-button disabled">
                        Sebelumnya
                    </button>

                    <button type="button" class="broadcast-page-button active">
                        1
                    </button>

                    <button type="button" class="broadcast-page-button">
                        2
                    </button>

                    <button type="button" class="broadcast-page-button">
                        3
                    </button>

                    <button type="button" class="broadcast-page-button">
                        Selanjutnya
                    </button>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
