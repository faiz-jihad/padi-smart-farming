@extends('layouts.admin')

@section('content')

<div class="space-y-6">

    <div>
        <p class="text-sm font-semibold text-padi-600">
            Ringkasan Sistem
        </p>

        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
            Dashboard
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Pantau aktivitas dan kondisi ekosistem P.A.D.I.
        </p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between">

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-padi-100 text-padi-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>

                <span class="rounded-full bg-padi-50 px-2.5 py-1 text-xs font-semibold text-padi-600">
                    Pengguna
                </span>

            </div>

            <p class="mt-5 text-sm text-slate-500">
                Total Pengguna
            </p>

            <p class="mt-1 text-3xl font-bold text-slate-900">
                1.250
            </p>

            <p class="mt-1 text-xs text-slate-400">
                Seluruh akun terdaftar
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between">

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-padi-100 text-padi-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22V8"/>
                        <path d="M5 12c0-3 2-5 7-5s7 2 7 5"/>
                        <path d="M5 12c0 4 3 7 7 7s7-3 7-7"/>
                        <path d="M12 8c-2-3-1-5 0-6 1 1 2 3 0 6Z"/>
                    </svg>
                </div>

                <span class="rounded-full bg-padi-50 px-2.5 py-1 text-xs font-semibold text-padi-600">
                    Panen
                </span>

            </div>

            <p class="mt-5 text-sm text-slate-500">
                Total Panen
            </p>

            <p class="mt-1 text-3xl font-bold text-slate-900">
                486
            </p>

            <p class="mt-1 text-xs text-slate-400">
                Data hasil panen tercatat
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between">

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 3v18"/>
                        <path d="M3 12h18"/>
                        <path d="m5 5 14 14"/>
                        <path d="m19 5-14 14"/>
                    </svg>
                </div>

                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-600">
                    Perlu dipantau
                </span>

            </div>

            <p class="mt-5 text-sm text-slate-500">
                Laporan Penyakit
            </p>

            <p class="mt-1 text-3xl font-bold text-slate-900">
                127
            </p>

            <p class="mt-1 text-xs text-slate-400">
                Scan dan laporan komunitas
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between">

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-padi-100 text-padi-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 3h18v18H3z"/>
                        <path d="M7 7h10"/>
                        <path d="M7 11h10"/>
                        <path d="M7 15h6"/>
                    </svg>
                </div>

                <span class="rounded-full bg-padi-50 px-2.5 py-1 text-xs font-semibold text-padi-600">
                    Marketplace
                </span>

            </div>

            <p class="mt-5 text-sm text-slate-500">
                Aktivitas Marketplace
            </p>

            <p class="mt-1 text-3xl font-bold text-slate-900">
                386
            </p>

            <p class="mt-1 text-xs text-slate-400">
                Listing dan penawaran
            </p>
        </div>

    </div>

    <div class="grid gap-6 xl:grid-cols-3">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2">

            <div class="flex items-start justify-between gap-4">

                <div>
                    <h2 class="text-lg font-bold text-slate-900">
                        Aktivitas Terbaru
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Aktivitas terbaru pada platform P.A.D.I.
                    </p>
                </div>

                <button
                    type="button"
                    class="shrink-0 text-sm font-semibold text-padi-600 transition hover:text-padi-700"
                >
                    Lihat semua
                </button>

            </div>

            <div class="mt-6 divide-y divide-slate-100">

                <div class="flex items-center gap-4 py-4 first:pt-0">

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-padi-100 text-padi-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-slate-800">
                            Pengguna baru terdaftar
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Petani baru bergabung ke platform
                        </p>
                    </div>

                    <span class="shrink-0 text-xs text-slate-400">
                        5 menit lalu
                    </span>

                </div>

                <div class="flex items-center gap-4 py-4">

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                            <path d="M12 8v4"/>
                            <path d="M12 16h.01"/>
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-slate-800">
                            Laporan penyakit membutuhkan validasi
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Menunggu pemeriksaan dan validasi PPL
                        </p>
                    </div>

                    <span class="shrink-0 text-xs text-slate-400">
                        18 menit lalu
                    </span>

                </div>

                <div class="flex items-center gap-4 py-4">

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-padi-100 text-padi-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 3h18v18H3z"/>
                            <path d="M7 7h10"/>
                            <path d="M7 11h10"/>
                            <path d="M7 15h6"/>
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-slate-800">
                            Listing hasil panen baru
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Petani menambahkan hasil panen ke marketplace
                        </p>
                    </div>

                    <span class="shrink-0 text-xs text-slate-400">
                        32 menit lalu
                    </span>

                </div>

                <div class="flex items-center gap-4 py-4 last:pb-0">

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-padi-100 text-padi-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 3v18"/>
                            <path d="M5 12c0-3 2-5 7-5s7 2 7 5"/>
                            <path d="M5 12c0 4 3 7 7 7s7-3 7-7"/>
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-slate-800">
                            Data panen berhasil dicatat
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Data hasil panen masuk ke sistem
                        </p>
                    </div>

                    <span class="shrink-0 text-xs text-slate-400">
                        1 jam lalu
                    </span>

                </div>

            </div>

        </div>

        <div class="rounded-2xl bg-padi-600 p-6 text-white shadow-sm">

            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-padi-accent text-padi-800">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 3v18"/>
                    <path d="M3 12h18"/>
                    <path d="m5 5 14 14"/>
                    <path d="m19 5-14 14"/>
                </svg>
            </div>

            <h2 class="mt-6 text-lg font-bold">
                Early Warning
            </h2>

            <p class="mt-2 text-sm leading-6 text-padi-100">
                Pantau peringatan penyakit dan kondisi wilayah berdasarkan laporan yang masuk dari sistem.
            </p>

            <div class="mt-6 rounded-xl bg-padi-700 p-4">

                <p class="text-xs text-padi-200">
                    Peringatan aktif
                </p>

                <p class="mt-1 text-3xl font-bold">
                    8 wilayah
                </p>

                <p class="mt-1 text-xs text-padi-200">
                    Membutuhkan pemantauan
                </p>

            </div>

            <div class="mt-4 rounded-xl border border-padi-500 bg-padi-600 p-4">

                <div class="flex items-center justify-between">
                    <span class="text-sm text-padi-100">
                        Risiko tinggi
                    </span>

                    <span class="text-sm font-bold text-padi-accent">
                        3 wilayah
                    </span>
                </div>

                <div class="mt-3 h-2 overflow-hidden rounded-full bg-padi-700">
                    <div class="h-full w-3/5 rounded-full bg-padi-accent"></div>
                </div>

            </div>

            <button
                type="button"
                class="mt-5 w-full rounded-xl bg-padi-accent px-4 py-3 text-sm font-bold text-padi-800 transition hover:bg-yellow-300"
            >
                Lihat Peringatan
            </button>

        </div>

    </div>

    <div class="grid gap-6 xl:grid-cols-2">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="flex items-start justify-between">

                <div>
                    <h2 class="text-lg font-bold text-slate-900">
                        Tren Penyakit
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Ringkasan laporan penyakit tanaman
                    </p>
                </div>

                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-600">
                    Bulan ini
                </span>

            </div>

            <div class="mt-6 space-y-5">

                <div>
                    <div class="mb-2 flex items-center justify-between text-sm">
                        <span class="font-medium text-slate-700">
                            Hawar Daun Bakteri
                        </span>

                        <span class="font-semibold text-slate-900">
                            42 laporan
                        </span>
                    </div>

                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full w-[84%] rounded-full bg-padi-600"></div>
                    </div>
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between text-sm">
                        <span class="font-medium text-slate-700">
                            Blast
                        </span>

                        <span class="font-semibold text-slate-900">
                            31 laporan
                        </span>
                    </div>

                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full w-[62%] rounded-full bg-padi-500"></div>
                    </div>
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between text-sm">
                        <span class="font-medium text-slate-700">
                            Wereng
                        </span>

                        <span class="font-semibold text-slate-900">
                            24 laporan
                        </span>
                    </div>

                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full w-[48%] rounded-full bg-padi-500"></div>
                    </div>
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between text-sm">
                        <span class="font-medium text-slate-700">
                            Tungro
                        </span>

                        <span class="font-semibold text-slate-900">
                            17 laporan
                        </span>
                    </div>

                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full w-[34%] rounded-full bg-padi-400"></div>
                    </div>
                </div>

            </div>

        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="flex items-start justify-between">

                <div>
                    <h2 class="text-lg font-bold text-slate-900">
                        Monitoring Marketplace
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Ringkasan aktivitas pasar hasil panen
                    </p>
                </div>

                <span class="rounded-full bg-padi-50 px-3 py-1 text-xs font-semibold text-padi-600">
                    Aktif
                </span>

            </div>

            <div class="mt-6 grid grid-cols-2 gap-4">

                <div class="rounded-xl bg-padi-50 p-4">
                    <p class="text-xs text-slate-500">
                        Listing Aktif
                    </p>

                    <p class="mt-1 text-2xl font-bold text-padi-700">
                        86
                    </p>
                </div>

                <div class="rounded-xl bg-padi-50 p-4">
                    <p class="text-xs text-slate-500">
                        Penawaran
                    </p>

                    <p class="mt-1 text-2xl font-bold text-padi-700">
                        143
                    </p>
                </div>

                <div class="rounded-xl bg-padi-50 p-4">
                    <p class="text-xs text-slate-500">
                        Kontrak Berjalan
                    </p>

                    <p class="mt-1 text-2xl font-bold text-padi-700">
                        27
                    </p>
                </div>

                <div class="rounded-xl bg-padi-50 p-4">
                    <p class="text-xs text-slate-500">
                        Menunggu Moderasi
                    </p>

                    <p class="mt-1 text-2xl font-bold text-amber-600">
                        12
                    </p>
                </div>

            </div>

            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-5">

                <div>
                    <p class="text-xs text-slate-500">
                        Aktivitas marketplace
                    </p>

                    <p class="mt-1 text-sm font-semibold text-slate-800">
                        Listing → Penawaran → Kontrak
                    </p>
                </div>

                <button
                    type="button"
                    class="text-sm font-semibold text-padi-600 hover:text-padi-700"
                >
                    Kelola
                </button>

            </div>

        </div>

    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

        <div class="flex items-start justify-between">

            <div>
                <h2 class="text-lg font-bold text-slate-900">
                    Monitoring AI
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Ringkasan proses deteksi penyakit menggunakan AI
                </p>
            </div>

            <span class="rounded-full bg-padi-50 px-3 py-1 text-xs font-semibold text-padi-600">
                Sistem AI
            </span>

        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                <p class="text-xs text-slate-500">
                    Total Scan
                </p>

                <p class="mt-1 text-2xl font-bold text-slate-900">
                    1.842
                </p>
            </div>

            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                <p class="text-xs text-slate-500">
                    Scan Berhasil
                </p>

                <p class="mt-1 text-2xl font-bold text-padi-600">
                    1.756
                </p>
            </div>

            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                <p class="text-xs text-slate-500">
                    Perlu Validasi PPL
                </p>

                <p class="mt-1 text-2xl font-bold text-amber-600">
                    64
                </p>
            </div>

            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                <p class="text-xs text-slate-500">
                    Gagal Diproses
                </p>

                <p class="mt-1 text-2xl font-bold text-red-600">
                    22
                </p>
            </div>

        </div>

    </div>

</div>

@endsection
