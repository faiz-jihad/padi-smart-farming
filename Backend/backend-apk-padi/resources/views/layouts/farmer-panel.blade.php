<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Website Saya' }} - P.A.D.I.</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-white font-sans antialiased" style="color: #0f172a;">

    {{-- Top navigation bar --}}
    <header class="bg-[#1b5e20] shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                {{-- Brand --}}
                <a href="{{ route('farmer.website.index') }}" class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-[#1b5e20]" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                        </svg>
                    </div>
                    <span class="text-white font-bold text-lg tracking-tight">P.A.D.I.</span>
                    <span class="text-green-200 text-sm hidden sm:block">Panel Petani</span>
                </a>

                {{-- Nav items --}}
                <nav class="hidden md:flex items-center gap-6">
                    <a href="{{ route('farmer.website.index') }}"
                        class="text-sm font-medium {{ request()->routeIs('farmer.website.index') ? 'text-white' : 'text-green-200 hover:text-white' }} transition-colors">
                        Website Saya
                    </a>
                    <a href="{{ route('farmer.website.edit') }}"
                        class="text-sm font-medium {{ request()->routeIs('farmer.website.edit') ? 'text-white' : 'text-green-200 hover:text-white' }} transition-colors">
                        Edit Profil
                    </a>
                    <a href="{{ route('farmer.website.template') }}"
                        class="text-sm font-medium {{ request()->routeIs('farmer.website.template') ? 'text-white' : 'text-green-200 hover:text-white' }} transition-colors">
                        Pilih Template
                    </a>
                    <a href="{{ route('farmer.website.sections') }}"
                        class="text-sm font-medium {{ request()->routeIs('farmer.website.sections') ? 'text-white' : 'text-green-200 hover:text-white' }} transition-colors">
                        Privasi
                    </a>
                </nav>

                {{-- User & logout --}}
                <div class="flex items-center gap-4">
                    <span class="text-green-200 text-sm hidden sm:block">
                        {{ auth('farmer')->user()->name }}
                    </span>
                    <form method="POST" action="{{ route('farmer.logout') }}">
                        @csrf
                        <button type="submit"
                            class="text-xs text-green-200 hover:text-white border border-green-600 hover:border-white px-3 py-1.5 rounded-lg transition-all">
                            Keluar
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </header>

    {{-- Flash messages --}}
    @if (session('status'))
        <div class="bg-[#1b5e20]/10 border-l-4 border-[#1b5e20] px-6 py-4 text-sm text-[#0f172a]" role="alert">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 px-6 py-4 text-sm text-red-800" role="alert">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Main content --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    {{-- Mobile bottom nav --}}
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50">
        <div class="grid grid-cols-4 h-16">
            <a href="{{ route('farmer.website.index') }}"
                class="flex flex-col items-center justify-center gap-1 text-xs {{ request()->routeIs('farmer.website.index') ? 'text-[#1b5e20]' : 'text-gray-500' }}">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m3 10 9-7 9 7"/>
                    <path d="M5 9v11h14V9"/>
                </svg>
                <span>Beranda</span>
            </a>
            <a href="{{ route('farmer.website.edit') }}"
                class="flex flex-col items-center justify-center gap-1 text-xs {{ request()->routeIs('farmer.website.edit') ? 'text-[#1b5e20]' : 'text-gray-500' }}">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                <span>Edit</span>
            </a>
            <a href="{{ route('farmer.website.template') }}"
                class="flex flex-col items-center justify-center gap-1 text-xs {{ request()->routeIs('farmer.website.template') ? 'text-[#1b5e20]' : 'text-gray-500' }}">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect width="7" height="9" x="3" y="3" rx="1"/>
                    <rect width="7" height="5" x="14" y="3" rx="1"/>
                    <rect width="7" height="9" x="14" y="12" rx="1"/>
                    <rect width="7" height="5" x="3" y="16" rx="1"/>
                </svg>
                <span>Template</span>
            </a>
            <a href="{{ route('farmer.website.sections') }}"
                class="flex flex-col items-center justify-center gap-1 text-xs {{ request()->routeIs('farmer.website.sections') ? 'text-[#1b5e20]' : 'text-gray-500' }}">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                </svg>
                <span>Privasi</span>
            </a>
        </div>
    </nav>

    {{-- Bottom padding for mobile nav --}}
    <div class="h-16 md:hidden"></div>

</body>
</html>
