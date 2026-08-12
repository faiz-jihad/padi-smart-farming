<header class="sticky top-0 z-30 flex h-20 items-center justify-between border-b border-slate-200 bg-white px-6 lg:px-8">

    <div>
        <p class="text-sm text-slate-500">
            Selamat datang kembali
        </p>

        <h2 class="text-lg font-semibold text-slate-800">
            Admin P.A.D.I.
        </h2>
    </div>

    <div class="flex items-center gap-4">

        <button
            type="button"
            class="relative flex h-10 w-10 items-center justify-center rounded-xl text-slate-500 transition hover:bg-padi-50 hover:text-padi-600"
            aria-label="Notifikasi"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>

            <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-padi-accent"></span>
        </button>

        <div class="h-8 w-px bg-slate-200"></div>

        <div class="flex items-center gap-3">

            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-padi-100 text-sm font-bold text-padi-700">
                A
            </div>

            <div class="hidden sm:block">
                <p class="text-sm font-semibold text-slate-800">
                    Admin P.A.D.I.
                </p>

                <p class="text-xs text-slate-500">
                    Administrator
                </p>
            </div>

            <button
                type="button"
                class="hidden text-slate-400 transition hover:text-padi-600 sm:block"
                aria-label="Menu akun"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m6 9 6 6 6-6"/>
                </svg>
            </button>

        </div>

    </div>

</header>
