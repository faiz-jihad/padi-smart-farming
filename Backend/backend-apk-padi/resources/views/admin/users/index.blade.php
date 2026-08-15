@extends('layouts.admin')

@section('content')
    <div class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-padi-600">
                    Manajemen Admin
                </p>

                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    Pengguna
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Kelola pengguna yang terdaftar dalam sistem P.A.D.I.
                </p>
            </div>

            <button
                type="button"
                class="inline-flex w-fit items-center gap-2 rounded-xl bg-padi-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-padi-700">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14" />
                    <path d="M5 12h14" />
                </svg>

                Tambah Pengguna
            </button>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Total Pengguna
                        </p>

                        <p class="mt-2 text-3xl font-bold text-slate-900">
                            1.248
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-padi-100 text-padi-600">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    </div>
                </div>

                <p class="mt-3 text-xs text-slate-400">
                    Seluruh pengguna terdaftar
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Petani
                        </p>

                        <p class="mt-2 text-3xl font-bold text-slate-900">
                            986
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M12 22V8" />
                            <path d="M5 12c0-3 2-5 7-5s7 2 7 5" />
                            <path d="M5 12c0 4 3 7 7 7s7-3 7-7" />
                            <path d="M12 8c-2-3-1-5 0-6 1 1 3 2 0 6Z" />
                        </svg>
                    </div>
                </div>

                <p class="mt-3 text-xs text-slate-400">
                    Pengguna dengan role Petani
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            PPL
                        </p>

                        <p class="mt-2 text-3xl font-bold text-slate-900">
                            184
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    </div>
                </div>

                <p class="mt-3 text-xs text-slate-400">
                    Penyuluh Pertanian Lapangan
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Mitra
                        </p>

                        <p class="mt-2 text-3xl font-bold text-slate-900">
                            78
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </div>
                </div>

                <p class="mt-3 text-xs text-slate-400">
                    Mitra terdaftar
                </p>
            </div>

        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-200 px-5 py-5">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">

                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">
                            Daftar Pengguna
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Daftar pengguna yang terdaftar pada sistem P.A.D.I.
                        </p>
                    </div>

                    <div class="flex flex-col gap-2.5 sm:flex-row">

                        <div class="relative">
                            <svg
                                class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2">
                                <circle cx="11" cy="11" r="8" />
                                <path d="m21 21-4.3-4.3" />
                            </svg>

                            <input
                                type="text"
                                placeholder="Cari pengguna..."
                                class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-padi-500 focus:bg-white focus:ring-2 focus:ring-padi-100 sm:w-56">
                        </div>

                        <select
                            class="h-10 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-600 outline-none transition focus:border-padi-500 focus:bg-white focus:ring-2 focus:ring-padi-100">
                            <option>Semua Role</option>
                            <option>Petani</option>
                            <option>PPL</option>
                            <option>Mitra</option>
                            <option>Admin</option>
                        </select>

                        <select
                            class="h-10 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-600 outline-none transition focus:border-padi-500 focus:bg-white focus:ring-2 focus:ring-padi-100">
                            <option>Semua Status</option>
                            <option>Aktif</option>
                            <option>Menunggu</option>
                            <option>Nonaktif</option>
                        </select>

                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[850px] text-left">

                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Pengguna
                            </th>

                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Role
                            </th>

                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Status
                            </th>

                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Bergabung
                            </th>

                            <th class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        <tr class="transition hover:bg-slate-50">

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-padi-100 font-semibold text-padi-700">
                                        AS
                                    </div>

                                    <div>
                                        <p class="font-semibold text-slate-800">
                                            Ahmad Setiawan
                                        </p>

                                        <p class="mt-0.5 text-sm text-slate-500">
                                            ahmad@example.com
                                        </p>
                                    </div>

                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                    Petani
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-2 text-sm font-medium text-emerald-600">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                    Aktif
                                </span>
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-500">
                                12 Agustus 2026
                            </td>

                            <td class="px-6 py-4 text-right">
                                <button
                                    type="button"
                                    class="rounded-lg px-3 py-2 text-sm font-medium text-padi-600 transition hover:bg-padi-50">
                                    Detail
                                </button>
                            </td>

                        </tr>

                        <tr class="transition hover:bg-slate-50">

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 font-semibold text-amber-700">
                                        BR
                                    </div>

                                    <div>
                                        <p class="font-semibold text-slate-800">
                                            Budi Raharjo
                                        </p>

                                        <p class="mt-0.5 text-sm text-slate-500">
                                            budi@example.com
                                        </p>
                                    </div>

                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                    PPL
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-2 text-sm font-medium text-emerald-600">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                    Aktif
                                </span>
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-500">
                                10 Agustus 2026
                            </td>

                            <td class="px-6 py-4 text-right">
                                <button
                                    type="button"
                                    class="rounded-lg px-3 py-2 text-sm font-medium text-padi-600 transition hover:bg-padi-50">
                                    Detail
                                </button>
                            </td>

                        </tr>

                        <tr class="transition hover:bg-slate-50">

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sky-100 font-semibold text-sky-700">
                                        CN
                                    </div>

                                    <div>
                                        <p class="font-semibold text-slate-800">
                                            Citra Nugraha
                                        </p>

                                        <p class="mt-0.5 text-sm text-slate-500">
                                            citra@example.com
                                        </p>
                                    </div>

                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-lg bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700">
                                    Mitra
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-2 text-sm font-medium text-amber-600">
                                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                    Menunggu
                                </span>
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-500">
                                8 Agustus 2026
                            </td>

                            <td class="px-6 py-4 text-right">
                                <button
                                    type="button"
                                    class="rounded-lg px-3 py-2 text-sm font-medium text-padi-600 transition hover:bg-padi-50">
                                    Detail
                                </button>
                            </td>

                        </tr>

                        <tr class="transition hover:bg-slate-50">

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-violet-100 font-semibold text-violet-700">
                                        DS
                                    </div>

                                    <div>
                                        <p class="font-semibold text-slate-800">
                                            Dedi Saputra
                                        </p>

                                        <p class="mt-0.5 text-sm text-slate-500">
                                            dedi@example.com
                                        </p>
                                    </div>

                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                    Petani
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-2 text-sm font-medium text-slate-500">
                                    <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                                    Nonaktif
                                </span>
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-500">
                                5 Agustus 2026
                            </td>

                            <td class="px-6 py-4 text-right">
                                <button
                                    type="button"
                                    class="rounded-lg px-3 py-2 text-sm font-medium text-padi-600 transition hover:bg-padi-50">
                                    Detail
                                </button>
                            </td>

                        </tr>

                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-4 border-t border-slate-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">

                <p class="text-sm text-slate-500">
                    Menampilkan
                    <span class="font-semibold text-slate-700">1–4</span>
                    dari
                    <span class="font-semibold text-slate-700">1.248</span>
                    pengguna
                </p>

                <div class="flex items-center gap-1.5">

                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-400">
                        Sebelumnya
                    </button>

                    <button
                        type="button"
                        class="rounded-lg bg-padi-600 px-3 py-2 text-sm font-semibold text-white">
                        1
                    </button>

                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                        2
                    </button>

                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                        Selanjutnya
                    </button>

                </div>

            </div>

        </div>

    </div>
@endsection
