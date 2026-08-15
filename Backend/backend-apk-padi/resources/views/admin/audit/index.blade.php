@extends('layouts.admin')

@section('content')

<link rel="stylesheet" href="{{ asset('css/admin/audit.css') }}">

<div class="audit-page">

    <div class="audit-container">

        <div class="audit-header">

            <div>
                <p class="audit-eyebrow">
                    Sistem P.A.D.I.
                </p>

                <h1 class="audit-title">
                    Audit Log
                </h1>

                <p class="audit-description">
                    Pantau aktivitas dan perubahan data yang terjadi di dalam sistem.
                </p>
            </div>

            <div class="audit-status">

                <div class="audit-status-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 3 4 6v6c0 5 3.5 8.5 8 10 4.5-1.5 8-5 8-10V6l-8-3Z"/>
                        <path d="m9 12 2 2 4-4"/>
                    </svg>
                </div>

                <div>
                    <span>Monitoring Sistem</span>
                    <strong>Aktif</strong>
                </div>

            </div>

        </div>


        <div class="audit-stat-grid">

            <div class="audit-stat-card">
                <div class="audit-stat-icon green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 12h18"/>
                        <path d="M12 3v18"/>
                    </svg>
                </div>

                <div>
                    <span>Total Aktivitas</span>
                    <strong>1.248</strong>
                </div>
            </div>


            <div class="audit-stat-card">
                <div class="audit-stat-icon blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="7" r="4"/>
                        <path d="M5.5 21a6.5 6.5 0 0 1 13 0"/>
                    </svg>
                </div>

                <div>
                    <span>Pengguna Aktif</span>
                    <strong>86</strong>
                </div>
            </div>


            <div class="audit-stat-card">
                <div class="audit-stat-icon yellow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 6v6l4 2"/>
                        <circle cx="12" cy="12" r="9"/>
                    </svg>
                </div>

                <div>
                    <span>Aktivitas Hari Ini</span>
                    <strong>37</strong>
                </div>
            </div>


            <div class="audit-stat-card">
                <div class="audit-stat-icon purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 19V5"/>
                        <path d="M4 15h5"/>
                        <path d="M9 11h5"/>
                        <path d="M14 7h6"/>
                    </svg>
                </div>

                <div>
                    <span>Jenis Aktivitas</span>
                    <strong>8</strong>
                </div>
            </div>

        </div>


        <div class="audit-card">

            <div class="audit-filter">

                <div class="audit-search">

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7"/>
                        <path d="m20 20-4-4"/>
                    </svg>

                    <input
                        type="text"
                        placeholder="Cari aktivitas, user, entity..."
                    >

                </div>


                <div class="audit-select">

                    <select>
                        <option>Semua Action</option>
                        <option>Created</option>
                        <option>Updated</option>
                        <option>Deleted</option>
                        <option>Login</option>
                        <option>Logout</option>
                    </select>

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m6 9 6 6 6-6"/>
                    </svg>

                </div>


                <div class="audit-select">

                    <select>
                        <option>Semua Entity</option>
                        <option>User</option>
                        <option>Farmer</option>
                        <option>Disease Report</option>
                        <option>Marketplace</option>
                        <option>Broadcast</option>
                    </select>

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m6 9 6 6 6-6"/>
                    </svg>

                </div>


                <button type="button" class="audit-filter-button">

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 5h18"/>
                        <path d="M6 12h12"/>
                        <path d="M10 19h4"/>
                    </svg>

                    Filter

                </button>

            </div>


            <div class="audit-table-wrapper">

                <table class="audit-table">

                    <thead>
                        <tr>
                            <th>WAKTU</th>
                            <th>PENGGUNA</th>
                            <th>ACTION</th>
                            <th>ENTITY</th>
                            <th>IP ADDRESS</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>


                    <tbody>

                        <tr>

                            <td>
                                <div class="audit-time">
                                    <strong>15 Agu 2026</strong>
                                    <span>13:42:18</span>
                                </div>
                            </td>

                            <td>
                                <div class="audit-user">

                                    <div class="audit-avatar">
                                        A
                                    </div>

                                    <div>
                                        <strong>
                                            Admin P.A.D.I.
                                        </strong>

                                        <span>
                                            Administrator
                                        </span>
                                    </div>

                                </div>
                            </td>

                            <td>
                                <span class="audit-action created">
                                    Created
                                </span>
                            </td>

                            <td>
                                <div class="audit-entity">
                                    <strong>
                                        User
                                    </strong>

                                    <span>
                                        #125
                                    </span>
                                </div>
                            </td>

                            <td>
                                <span class="audit-ip">
                                    192.168.1.10
                                </span>
                            </td>

                            <td>
                                <button
                                    type="button"
                                    class="audit-detail-button"
                                    data-action="Created"
                                    data-user="Admin P.A.D.I."
                                    data-entity="User"
                                    data-entity-id="125"
                                    data-ip="192.168.1.10"
                                    data-time="15 Agu 2026 13:42:18"
                                    data-old=""
                                    data-new='{"name":"Ahmad Setiawan","role":"Petani"}'
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"/>
                                        <circle cx="12" cy="12" r="2.5"/>
                                    </svg>

                                    Detail
                                </button>
                            </td>

                        </tr>


                        <tr>

                            <td>
                                <div class="audit-time">
                                    <strong>15 Agu 2026</strong>
                                    <span>12:26:04</span>
                                </div>
                            </td>

                            <td>
                                <div class="audit-user">

                                    <div class="audit-avatar">
                                        B
                                    </div>

                                    <div>
                                        <strong>
                                            Budi Raharjo
                                        </strong>

                                        <span>
                                            PPL
                                        </span>
                                    </div>

                                </div>
                            </td>

                            <td>
                                <span class="audit-action updated">
                                    Updated
                                </span>
                            </td>

                            <td>
                                <div class="audit-entity">
                                    <strong>
                                        Farmer
                                    </strong>

                                    <span>
                                        #84
                                    </span>
                                </div>
                            </td>

                            <td>
                                <span class="audit-ip">
                                    192.168.1.18
                                </span>
                            </td>

                            <td>
                                <button
                                    type="button"
                                    class="audit-detail-button"
                                    data-action="Updated"
                                    data-user="Budi Raharjo"
                                    data-entity="Farmer"
                                    data-entity-id="84"
                                    data-ip="192.168.1.18"
                                    data-time="15 Agu 2026 12:26:04"
                                    data-old='{"status":"inactive"}'
                                    data-new='{"status":"active"}'
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"/>
                                        <circle cx="12" cy="12" r="2.5"/>
                                    </svg>

                                    Detail
                                </button>
                            </td>

                        </tr>


                        <tr>

                            <td>
                                <div class="audit-time">
                                    <strong>15 Agu 2026</strong>
                                    <span>11:08:32</span>
                                </div>
                            </td>

                            <td>
                                <div class="audit-user">

                                    <div class="audit-avatar">
                                        S
                                    </div>

                                    <div>
                                        <strong>
                                            Siti Nurhaliza
                                        </strong>

                                        <span>
                                            Petani
                                        </span>
                                    </div>

                                </div>
                            </td>

                            <td>
                                <span class="audit-action deleted">
                                    Deleted
                                </span>
                            </td>

                            <td>
                                <div class="audit-entity">
                                    <strong>
                                        Disease Report
                                    </strong>

                                    <span>
                                        #47
                                    </span>
                                </div>
                            </td>

                            <td>
                                <span class="audit-ip">
                                    192.168.1.21
                                </span>
                            </td>

                            <td>
                                <button
                                    type="button"
                                    class="audit-detail-button"
                                    data-action="Deleted"
                                    data-user="Siti Nurhaliza"
                                    data-entity="Disease Report"
                                    data-entity-id="47"
                                    data-ip="192.168.1.21"
                                    data-time="15 Agu 2026 11:08:32"
                                    data-old='{"status":"pending","result":"Blast"}'
                                    data-new=""
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"/>
                                        <circle cx="12" cy="12" r="2.5"/>
                                    </svg>

                                    Detail
                                </button>
                            </td>

                        </tr>


                        <tr>

                            <td>
                                <div class="audit-time">
                                    <strong>15 Agu 2026</strong>
                                    <span>09:51:47</span>
                                </div>
                            </td>

                            <td>
                                <div class="audit-user">

                                    <div class="audit-avatar">
                                        A
                                    </div>

                                    <div>
                                        <strong>
                                            Admin P.A.D.I.
                                        </strong>

                                        <span>
                                            Administrator
                                        </span>
                                    </div>

                                </div>
                            </td>

                            <td>
                                <span class="audit-action login">
                                    Login
                                </span>
                            </td>

                            <td>
                                <div class="audit-entity">
                                    <strong>
                                        User
                                    </strong>

                                    <span>
                                        #1
                                    </span>
                                </div>
                            </td>

                            <td>
                                <span class="audit-ip">
                                    127.0.0.1
                                </span>
                            </td>

                            <td>
                                <button
                                    type="button"
                                    class="audit-detail-button"
                                    data-action="Login"
                                    data-user="Admin P.A.D.I."
                                    data-entity="User"
                                    data-entity-id="1"
                                    data-ip="127.0.0.1"
                                    data-time="15 Agu 2026 09:51:47"
                                    data-old=""
                                    data-new=""
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"/>
                                        <circle cx="12" cy="12" r="2.5"/>
                                    </svg>

                                    Detail
                                </button>
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>


            <div class="audit-pagination">

                <p>
                    Menampilkan <strong>1–4</strong> dari <strong>1.248</strong> aktivitas
                </p>

                <div class="audit-pagination-buttons">

                    <button type="button" class="audit-page-button disabled">
                        Sebelumnya
                    </button>

                    <button type="button" class="audit-page-button active">
                        1
                    </button>

                    <button type="button" class="audit-page-button">
                        2
                    </button>

                    <button type="button" class="audit-page-button">
                        3
                    </button>

                    <button type="button" class="audit-page-button">
                        Selanjutnya
                    </button>

                </div>

            </div>

        </div>

    </div>

</div>


<div id="audit-modal" class="audit-modal">

    <div class="audit-modal-backdrop"></div>

    <div class="audit-modal-dialog">

        <div class="audit-modal-header">

            <div>
                <p>Detail Aktivitas</p>

                <h2 id="audit-modal-action">
                    -
                </h2>
            </div>

            <button
                type="button"
                id="audit-modal-close"
                class="audit-modal-close"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m6 6 12 12"/>
                    <path d="m18 6-12 12"/>
                </svg>
            </button>

        </div>


        <div class="audit-modal-body">

            <div class="audit-detail-grid">

                <div>
                    <span>Pengguna</span>
                    <strong id="audit-modal-user">-</strong>
                </div>

                <div>
                    <span>Entity</span>
                    <strong id="audit-modal-entity">-</strong>
                </div>

                <div>
                    <span>IP Address</span>
                    <strong id="audit-modal-ip">-</strong>
                </div>

                <div>
                    <span>Waktu</span>
                    <strong id="audit-modal-time">-</strong>
                </div>

            </div>


            <div class="audit-values-grid">

                <div class="audit-value-card">

                    <div class="audit-value-header old">
                        <strong>Data Sebelumnya</strong>
                    </div>

                    <pre id="audit-modal-old">Tidak ada data sebelumnya.</pre>

                </div>


                <div class="audit-value-card">

                    <div class="audit-value-header new">
                        <strong>Data Setelah Perubahan</strong>
                    </div>

                    <pre id="audit-modal-new">Tidak ada data baru.</pre>

                </div>

            </div>

        </div>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const modal = document.getElementById('audit-modal');
    const closeButton = document.getElementById('audit-modal-close');
    const backdrop = document.querySelector('.audit-modal-backdrop');

    const modalAction = document.getElementById('audit-modal-action');
    const modalUser = document.getElementById('audit-modal-user');
    const modalEntity = document.getElementById('audit-modal-entity');
    const modalIp = document.getElementById('audit-modal-ip');
    const modalTime = document.getElementById('audit-modal-time');
    const modalOld = document.getElementById('audit-modal-old');
    const modalNew = document.getElementById('audit-modal-new');


    function formatJson(value, emptyText) {

        if (!value) {
            return emptyText;
        }

        try {
            return JSON.stringify(JSON.parse(value), null, 2);
        } catch (error) {
            return value;
        }

    }


    function openModal(button) {

        modalAction.textContent = button.dataset.action || '-';
        modalUser.textContent = button.dataset.user || '-';

        const entity = button.dataset.entity || '-';
        const entityId = button.dataset.entityId
            ? ' #' + button.dataset.entityId
            : '';

        modalEntity.textContent = entity + entityId;
        modalIp.textContent = button.dataset.ip || '-';
        modalTime.textContent = button.dataset.time || '-';

        modalOld.textContent = formatJson(
            button.dataset.old,
            'Tidak ada data sebelumnya.'
        );

        modalNew.textContent = formatJson(
            button.dataset.new,
            'Tidak ada data baru.'
        );

        modal.classList.add('show');
        document.body.classList.add('audit-modal-open');

    }


    function closeModal() {

        modal.classList.remove('show');
        document.body.classList.remove('audit-modal-open');

    }


    document.querySelectorAll('.audit-detail-button').forEach(function (button) {

        button.addEventListener('click', function () {
            openModal(button);
        });

    });


    closeButton.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);


    document.addEventListener('keydown', function (event) {

        if (event.key === 'Escape') {
            closeModal();
        }

    });

});
</script>

@endsection
