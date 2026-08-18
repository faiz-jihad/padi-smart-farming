@extends('layouts.admin')

@php
    $title = 'Profil Publik Petani';
    $statusColors = [
        'draft'     => 'bg-slate-100 text-slate-600',
        'review'    => 'bg-amber-100 text-amber-700',
        'published' => 'bg-green-100 text-green-700',
        'suspended' => 'bg-red-100 text-red-700',
    ];
    $verifyColors = [
        'unverified' => 'bg-slate-100 text-slate-500',
        'verified'   => 'bg-green-100 text-green-700',
        'rejected'   => 'bg-red-100 text-red-700',
    ];
@endphp

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[#0f172a]">Profil Publik Petani</h1>
            <p class="text-slate-500 text-sm mt-1">Monitor, verifikasi, dan kelola website publik seluruh petani.</p>
        </div>
        <div class="text-sm text-slate-500">
            {{ $profiles->total() }} profil terdaftar
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.farmer-profiles.index') }}" class="flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
            placeholder="Cari nama usaha / subdomain / petani..."
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20] min-w-[240px]">
        <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]">
            <option value="">Semua Status</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="review" {{ request('status') === 'review' ? 'selected' : '' }}>Menunggu Review</option>
            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Tayang</option>
            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Ditangguhkan</option>
        </select>
        <select name="verification" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]">
            <option value="">Semua Verifikasi</option>
            <option value="unverified" {{ request('verification') === 'unverified' ? 'selected' : '' }}>Belum Diverifikasi</option>
            <option value="verified" {{ request('verification') === 'verified' ? 'selected' : '' }}>Terverifikasi</option>
            <option value="rejected" {{ request('verification') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
        </select>
        <button type="submit" class="bg-[#1b5e20] text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-[#145218] transition-all">Filter</button>
        @if (request()->hasAny(['search', 'status', 'verification']))
            <a href="{{ route('admin.farmer-profiles.index') }}" class="text-sm text-slate-500 hover:text-[#0f172a] px-4 py-2 border border-gray-200 rounded-lg transition-colors">Reset</a>
        @endif
    </form>

    {{-- Flash --}}
    @if (session('status'))
        <div class="bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        @if ($profiles->isEmpty())
            <div class="text-center py-20 text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z"/>
                </svg>
                <p class="text-sm font-medium">Belum ada profil publik petani.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-[#0f172a] text-white">
                        <tr>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide">Petani / Usaha</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide">Subdomain</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide">Template</th>
                            <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wide">Status Website</th>
                            <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wide">Verifikasi</th>
                            <th class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wide">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($profiles as $profile)
                            <tr class="hover:bg-[#1b5e20]/5 transition-colors">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-[#0f172a]">{{ $profile->business_name }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $profile->farmer?->name }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($profile->subdomain)
                                        <a href="{{ $profile->publicUrl() }}" target="_blank"
                                            class="text-[#1b5e20] text-xs font-medium hover:underline">
                                            {{ $profile->subdomain }}.{{ config('domains.base') }}
                                        </a>
                                    @else
                                        <span class="text-slate-300 text-xs">Belum dipilih</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-xs text-slate-500">
                                    {{ $profile->template?->name ?? '—' }}
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusColors[$profile->website_status?->value] ?? 'bg-slate-100 text-slate-500' }}">
                                        {{ $profile->website_status?->label() ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $verifyColors[$profile->verification_status?->value] ?? 'bg-slate-100 text-slate-500' }}">
                                        {{ $profile->verification_status?->label() ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">

                                        @if ($profile->verification_status?->value === 'unverified')
                                            <form method="POST" action="{{ route('admin.farmer-profiles.verify', $profile) }}">
                                                @csrf
                                                <button type="submit" class="text-xs font-semibold text-green-700 hover:text-green-900 border border-green-200 hover:border-green-400 px-3 py-1.5 rounded-lg transition-all">
                                                    Verifikasi
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.farmer-profiles.reject', $profile) }}">
                                                @csrf
                                                <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800 border border-red-200 hover:border-red-400 px-3 py-1.5 rounded-lg transition-all"
                                                    onclick="return confirm('Tolak verifikasi profil ini?')">
                                                    Tolak
                                                </button>
                                            </form>
                                        @endif

                                        @if ($profile->website_status?->value === 'published')
                                            <form method="POST" action="{{ route('admin.farmer-profiles.suspend', $profile) }}">
                                                @csrf
                                                <button type="submit" class="text-xs font-semibold text-amber-700 hover:text-amber-900 border border-amber-200 hover:border-amber-400 px-3 py-1.5 rounded-lg transition-all"
                                                    onclick="return confirm('Tangguhkan website petani ini?')">
                                                    Tangguhkan
                                                </button>
                                            </form>
                                        @elseif ($profile->website_status?->value === 'suspended')
                                            <form method="POST" action="{{ route('admin.farmer-profiles.restore', $profile) }}">
                                                @csrf
                                                <button type="submit" class="text-xs font-semibold text-[#1b5e20] hover:text-[#145218] border border-green-200 hover:border-[#1b5e20] px-3 py-1.5 rounded-lg transition-all">
                                                    Pulihkan
                                                </button>
                                            </form>
                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($profiles->hasPages())
                <div class="px-5 py-4 border-t border-gray-50">
                    {{ $profiles->links() }}
                </div>
            @endif
        @endif
    </div>

</div>

@endsection
