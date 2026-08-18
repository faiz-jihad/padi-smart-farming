@extends('layouts.farmer-panel')
@section('title', 'Website Saya')

@section('content')

@php
    $farmer = auth('farmer')->user();
    $hasProfile = $profile !== null;
    $isPublished = $profile?->isPublished() ?? false;
    $isVerified = $profile?->isVerified() ?? false;
    $subdomain = $profile?->subdomain;
    $publicUrl = $profile?->publicUrl();
    $baseDomain = config('domains.base', 'localhost');
    $scheme = app()->environment('production') ? 'https' : 'http';

    // Progress checklist
    $steps = [
        'profile'  => $hasProfile && $profile->business_name,
        'template' => $hasProfile && $profile->profile_template_id,
        'subdomain'=> $hasProfile && $subdomain,
        'publish'  => $isPublished,
    ];
    $completedSteps = count(array_filter($steps));
    $progressPct = ($completedSteps / 4) * 100;
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#0f172a]">Website Saya</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola dan publikasikan profil usaha tani Anda.</p>
        </div>

        @if ($isPublished && $publicUrl)
            <a href="{{ $publicUrl }}" target="_blank"
                class="inline-flex items-center gap-2 bg-[#1b5e20] text-white text-sm font-semibold px-5 py-2.5 rounded-xl hover:bg-[#145218] transition-all shadow-sm">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                    <polyline points="15 3 21 3 21 9"/>
                    <line x1="10" x2="21" y1="14" y2="3"/>
                </svg>
                Buka Website
            </a>
        @elseif ($steps['profile'] && $steps['template'] && $steps['subdomain'])
            <form method="POST" action="{{ route('farmer.website.publish') }}">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 bg-[#1b5e20] text-white text-sm font-semibold px-5 py-2.5 rounded-xl hover:bg-[#145218] transition-all shadow-sm">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Publikasikan Website
                </button>
            </form>
        @endif
    </div>

    {{-- Status cards row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        @php
            $statusCards = [
                [
                    'label'   => 'Status Website',
                    'value'   => $isPublished ? 'Tayang' : ($profile?->website_status?->label() ?? 'Draft'),
                    'dot'     => $isPublished ? 'bg-green-500' : 'bg-amber-400',
                    'icon'    => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>',
                ],
                [
                    'label'   => 'Verifikasi',
                    'value'   => $isVerified ? 'Terverifikasi' : ($profile?->verification_status?->label() ?? 'Belum Diverifikasi'),
                    'dot'     => $isVerified ? 'bg-green-500' : 'bg-slate-300',
                    'icon'    => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>',
                ],
                [
                    'label'   => 'Subdomain',
                    'value'   => $subdomain ? "{$subdomain}.{$baseDomain}" : 'Belum dipilih',
                    'dot'     => $subdomain ? 'bg-green-500' : 'bg-slate-300',
                    'icon'    => '<path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z"/><path d="M12 2c-2.8 4-4 7-4 10 0 3 1.2 6 4 10"/><path d="M12 2c2.8 4 4 7 4 10 0 3-1.2 6-4 10"/><path d="M2 12h20"/>',
                ],
                [
                    'label'   => 'Template',
                    'value'   => $profile?->template?->name ?? 'Belum dipilih',
                    'dot'     => $profile?->template ? 'bg-green-500' : 'bg-slate-300',
                    'icon'    => '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>',
                ],
            ];
        @endphp

        @foreach ($statusCards as $card)
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-2 h-2 rounded-full {{ $card['dot'] }}"></div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ $card['label'] }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#1b5e20] flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        {!! $card['icon'] !!}
                    </svg>
                    <p class="text-sm font-semibold text-[#0f172a] truncate">{{ $card['value'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Setup progress --}}
    @if (! $isPublished)
        <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-[#0f172a]">Langkah Setup Website</h2>
                <span class="text-sm font-semibold text-[#1b5e20]">{{ $completedSteps }}/4 selesai</span>
            </div>

            {{-- Progress bar --}}
            <div class="h-2 bg-gray-100 rounded-full mb-6 overflow-hidden">
                <div class="h-full bg-[#1b5e20] rounded-full transition-all duration-500"
                    style="width: {{ $progressPct }}%"></div>
            </div>

            {{-- Steps --}}
            <div class="space-y-4">

                @php
                    $setupSteps = [
                        [
                            'done'  => $steps['profile'],
                            'label' => 'Isi Profil Usaha',
                            'desc'  => 'Nama usaha, deskripsi, kontak, dan foto.',
                            'route' => route('farmer.website.edit'),
                            'cta'   => $steps['profile'] ? 'Edit' : 'Mulai',
                        ],
                        [
                            'done'  => $steps['template'],
                            'label' => 'Pilih Template Website',
                            'desc'  => 'Tentukan tampilan website publik Anda.',
                            'route' => route('farmer.website.template'),
                            'cta'   => $steps['template'] ? 'Ganti' : 'Pilih',
                        ],
                        [
                            'done'  => $steps['subdomain'],
                            'label' => 'Tentukan Subdomain',
                            'desc'  => "Contoh: namaanda.{$baseDomain}",
                            'route' => route('farmer.website.edit') . '#subdomain-section',
                            'cta'   => $steps['subdomain'] ? 'Ubah' : 'Tentukan',
                        ],
                        [
                            'done'  => $steps['publish'],
                            'label' => 'Publikasikan Website',
                            'desc'  => 'Website Anda akan dapat diakses publik.',
                            'route' => '#',
                            'cta'   => 'Tayang',
                        ],
                    ];
                @endphp

                @foreach ($setupSteps as $i => $step)
                    <div class="flex items-center gap-4">
                        {{-- Step indicator --}}
                        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center
                            {{ $step['done'] ? 'bg-[#1b5e20] text-white' : 'bg-gray-100 text-slate-400' }}">
                            @if ($step['done'])
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            @else
                                <span class="text-xs font-bold">{{ $i + 1 }}</span>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-[#0f172a] {{ $step['done'] ? 'line-through text-slate-400' : '' }}">
                                {{ $step['label'] }}
                            </p>
                            <p class="text-xs text-slate-500">{{ $step['desc'] }}</p>
                        </div>

                        @if (! $step['done'] && $step['route'] !== '#')
                            <a href="{{ $step['route'] }}"
                                class="flex-shrink-0 text-xs font-semibold text-[#1b5e20] border border-[#1b5e20] px-3 py-1.5 rounded-lg hover:bg-[#1b5e20] hover:text-white transition-all">
                                {{ $step['cta'] }}
                            </a>
                        @elseif ($step['done'])
                            <span class="flex-shrink-0 text-xs font-medium text-green-600">Selesai</span>
                        @endif
                    </div>

                    @if (! $loop->last)
                        <div class="ml-4 w-px h-4 bg-gray-100"></div>
                    @endif
                @endforeach

            </div>
        </div>
    @endif

    {{-- Published — URL & QR section --}}
    @if ($isPublished && $publicUrl)
        <div class="bg-[#1b5e20] rounded-2xl p-6 text-white">
            <div class="flex items-start justify-between flex-wrap gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        @if ($isVerified)
                            <span class="inline-flex items-center gap-1 bg-white/20 text-white text-xs font-semibold px-2.5 py-1 rounded-full">
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                                    <path d="m9 12 2 2 4-4"/>
                                </svg>
                                Terverifikasi P.A.D.I.
                            </span>
                        @endif
                    </div>
                    <h2 class="text-xl font-bold mb-1">{{ $profile->business_name }}</h2>
                    <a href="{{ $publicUrl }}" target="_blank"
                        class="text-green-200 text-sm hover:text-white transition-colors underline underline-offset-2">
                        {{ $publicUrl }}
                    </a>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('farmer.website.preview') }}" target="_blank"
                        class="text-sm font-medium border border-white/30 hover:border-white text-white px-4 py-2 rounded-lg transition-all">
                        Preview
                    </a>
                    <form method="POST" action="{{ route('farmer.website.unpublish') }}">
                        @csrf
                        <button type="submit"
                            class="text-sm font-medium border border-white/30 hover:border-white text-white px-4 py-2 rounded-lg transition-all"
                            onclick="return confirm('Yakin ingin menonaktifkan website?')">
                            Nonaktifkan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Quick actions --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('farmer.website.edit') }}"
            class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md hover:border-[#1b5e20]/30 transition-all group">
            <div class="w-10 h-10 bg-[#1b5e20]/10 rounded-xl flex items-center justify-center mb-3 group-hover:bg-[#1b5e20]/20 transition-colors">
                <svg class="w-5 h-5 text-[#1b5e20]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
            </div>
            <p class="font-semibold text-[#0f172a] text-sm">Edit Profil</p>
            <p class="text-xs text-slate-500 mt-0.5">Nama, foto, kontak, deskripsi</p>
        </a>
        <a href="{{ route('farmer.website.template') }}"
            class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md hover:border-[#1b5e20]/30 transition-all group">
            <div class="w-10 h-10 bg-[#1b5e20]/10 rounded-xl flex items-center justify-center mb-3 group-hover:bg-[#1b5e20]/20 transition-colors">
                <svg class="w-5 h-5 text-[#1b5e20]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect width="7" height="9" x="3" y="3" rx="1"/>
                    <rect width="7" height="5" x="14" y="3" rx="1"/>
                    <rect width="7" height="9" x="14" y="12" rx="1"/>
                    <rect width="7" height="5" x="3" y="16" rx="1"/>
                </svg>
            </div>
            <p class="font-semibold text-[#0f172a] text-sm">Pilih Template</p>
            <p class="text-xs text-slate-500 mt-0.5">Ganti tampilan website</p>
        </a>
        <a href="{{ route('farmer.website.sections') }}"
            class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md hover:border-[#1b5e20]/30 transition-all group">
            <div class="w-10 h-10 bg-[#1b5e20]/10 rounded-xl flex items-center justify-center mb-3 group-hover:bg-[#1b5e20]/20 transition-colors">
                <svg class="w-5 h-5 text-[#1b5e20]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                </svg>
            </div>
            <p class="font-semibold text-[#0f172a] text-sm">Pengaturan Privasi</p>
            <p class="text-xs text-slate-500 mt-0.5">Kontrol data yang tampil</p>
        </a>
    </div>

</div>

@endsection
