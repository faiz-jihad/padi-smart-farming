@extends('layouts.farmer-panel')
@section('title', 'Pilih Template Website')

@section('content')

<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-[#0f172a]">Pilih Template Website</h1>
        <p class="text-slate-500 text-sm mt-1">Template menentukan tampilan halaman publik petani Anda. Semua template disediakan P.A.D.I.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        @foreach ($templates as $tpl)
            @php
                $isActive = $profile?->profile_template_id === $tpl->id;
            @endphp

            <div class="bg-white rounded-2xl border-2 {{ $isActive ? 'border-[#1b5e20]' : 'border-gray-100' }} shadow-sm overflow-hidden group transition-all hover:shadow-md">

                {{-- Template thumbnail placeholder --}}
                <div class="relative h-40 bg-gradient-to-br
                    {{ $tpl->code === 'harvest-prestige' ? 'from-[#0f172a] to-[#1b5e20]' : '' }}
                    {{ $tpl->code === 'agri-modern' ? 'from-[#1b5e20] to-[#2e7d32]' : '' }}
                    {{ $tpl->code === 'marketplace-pro' ? 'from-[#145218] to-[#0f172a]' : '' }}
                    flex items-end p-4 overflow-hidden">

                    {{-- Pattern overlay --}}
                    <div class="absolute inset-0 opacity-10">
                        @if ($tpl->code === 'harvest-prestige')
                            <div class="w-full h-full" style="background-image: repeating-linear-gradient(45deg, #fff 0, #fff 1px, transparent 0, transparent 50%); background-size: 8px 8px;"></div>
                        @elseif ($tpl->code === 'agri-modern')
                            <div class="w-full h-full" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 12px 12px;"></div>
                        @else
                            <div class="w-full h-full" style="background-image: repeating-linear-gradient(0deg, #fff 0, #fff 1px, transparent 0, transparent 20px), repeating-linear-gradient(90deg, #fff 0, #fff 1px, transparent 0, transparent 20px);"></div>
                        @endif
                    </div>

                    {{-- Template name overlay --}}
                    <div class="relative">
                        <p class="text-white text-xs font-medium opacity-70 uppercase tracking-wider">P.A.D.I. Template</p>
                        <p class="text-white font-bold text-base leading-tight">{{ $tpl->name }}</p>
                    </div>

                    @if ($isActive)
                        <div class="absolute top-3 right-3 bg-[#1b5e20] text-white text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1">
                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            Aktif
                        </div>
                    @endif

                </div>

                {{-- Info --}}
                <div class="p-5">
                    <p class="text-sm text-slate-600 leading-relaxed mb-4">{{ $tpl->description }}</p>

                    <form method="POST" action="{{ route('farmer.website.template.select') }}">
                        @csrf
                        <input type="hidden" name="template_id" value="{{ $tpl->id }}">
                        <button type="submit"
                            class="w-full py-2.5 rounded-lg text-sm font-semibold transition-all
                                {{ $isActive
                                    ? 'bg-[#1b5e20] text-white cursor-default'
                                    : 'border border-[#1b5e20] text-[#1b5e20] hover:bg-[#1b5e20] hover:text-white' }}">
                            {{ $isActive ? 'Template Aktif' : 'Pilih Template Ini' }}
                        </button>
                    </form>
                </div>

            </div>
        @endforeach

    </div>

    <div class="text-center pt-2">
        <a href="{{ route('farmer.website.index') }}" class="text-sm text-slate-500 hover:text-[#0f172a] transition-colors">
            Kembali ke Dashboard
        </a>
    </div>

</div>

@endsection
