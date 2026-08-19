<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Petani - P.A.D.I.</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#0f172a] font-sans antialiased flex items-center justify-center px-4">

    <div class="w-full max-w-md">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-[#1b5e20] rounded-2xl mb-4 shadow-lg">
                <svg class="w-9 h-9 text-white" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white">P.A.D.I.</h1>
            <p class="text-slate-400 text-sm mt-1">Panel Petani — Kelola Website Usaha Tani Anda</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-2xl p-8">

            <h2 class="text-xl font-bold text-[#0f172a] mb-2">Masuk ke Panel Petani</h2>
            <p class="text-slate-500 text-sm mb-6">Gunakan email dan password yang sama dengan aplikasi P.A.D.I. mobile.</p>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-4 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('farmer.login.submit') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-[#0f172a] mb-1">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                        class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20] focus:border-transparent transition"
                        placeholder="petani@email.com"
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-[#0f172a] mb-1">Password</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20] focus:border-transparent transition"
                        placeholder="Password"
                    >
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="remember" id="remember" class="accent-[#1b5e20]">
                    <label for="remember" class="text-sm text-slate-600">Ingat saya</label>
                </div>

                <button
                    type="submit"
                    class="w-full bg-[#1b5e20] hover:bg-[#145218] text-white font-semibold py-3 px-6 rounded-lg transition-all shadow-sm hover:shadow-md active:scale-[0.99]"
                >
                    Masuk
                </button>

            </form>

        </div>

        <p class="text-center text-slate-500 text-xs mt-6">
            Belum punya akun? Daftar melalui aplikasi P.A.D.I. di smartphone Anda.
        </p>

    </div>

</body>
</html>
