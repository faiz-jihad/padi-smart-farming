@extends('layouts.farmer-panel')

@section('content')
<div class="space-y-8">
    {{-- Header --}}
    <div class="border-b border-gray-100 pb-5">
        <h1 class="text-2xl font-black text-gray-900 tracking-tight">Ringkasan Pertanian & Produktivitas</h1>
        <p class="text-sm text-gray-600 mt-1">Pantau seluruh status siklus tanam, riwayat pemeliharaan, diagnosa daun, dan estimasi produktivitas lahan Anda.</p>
    </div>

    {{-- Farm Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($farms as $farm)
            @php
                $prod = $farmsProductivity->firstWhere('farm_id', $farm->id);
                $activeSeason = $farm->cropSeasons->where('status', 'active')->first();
            @endphp
            <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-900 text-base">{{ $farm->name }}</h3>
                        <span class="text-xs text-gray-500">{{ $farm->regency?->name ?? 'Wilayah Terdaftar' }} &bull; {{ $farm->area_ha }} Ha</span>
                    </div>
                    @if ($prod && $prod['status'] === 'PRODUCTIVE')
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Produktif</span>
                    @elseif ($prod && $prod['status'] === 'NEED_ATTENTION')
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Perlu Perhatian</span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">Masa Tanam</span>
                    @endif
                </div>

                <div class="p-3.5 rounded-xl bg-gray-50 text-xs space-y-1.5 border border-gray-100">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Varietas:</span>
                        <span class="font-bold text-gray-900">{{ $activeSeason?->variety?->name ?? 'Inpari 32' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total Panen Terakhir:</span>
                        <span class="font-semibold text-gray-900">{{ number_format($prod['total_harvest_ton'] ?? 0, 2) }} Ton</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Estimasi Yield:</span>
                        <span class="font-bold text-emerald-700">{{ number_format($prod['productivity_ton_per_ha'] ?? 0, 2) }} Ton/Ha</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-8 text-center text-gray-400 text-sm">
                Belum ada data lahan yang terhubung dengan akun petani Anda.
            </div>
        @endforelse
    </div>

    {{-- 2 Columns: Timeline & Disease Scans --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Activities --}}
        <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-xs space-y-4">
            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <span>🌱</span> Riwayat Aktivitas Tani Terbaru
            </h2>

            <div class="space-y-3">
                @forelse ($recentActivities as $act)
                    @php
                        $date = $act->occurred_at ? \Carbon\Carbon::parse($act->occurred_at) : null;
                    @endphp
                    <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-bold text-gray-900">{{ ucfirst($act->type) }} — {{ $act->cropSeason?->farm?->name ?? 'Lahan' }}</div>
                            <div class="text-gray-500 mt-0.5">{{ $act->notes ?: 'Pencatatan kegiatan budidaya lahan.' }}</div>
                        </div>
                        <div class="text-right text-gray-400 font-medium">
                            {{ $date ? $date->translatedFormat('d M Y') : '-' }}
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 py-6 text-center">Belum ada riwayat aktivitas yang dicatat.</p>
                @endforelse
            </div>
        </div>

        {{-- Disease Monitoring --}}
        <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-xs space-y-4">
            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <span>🔍</span> Hasil Pemeriksaan Daun & Penyakit
            </h2>

            <div class="space-y-3">
                @forelse ($recentScans as $scan)
                    @php
                        $date = $scan->scanned_at ? \Carbon\Carbon::parse($scan->scanned_at) : ($scan->created_at ? \Carbon\Carbon::parse($scan->created_at) : null);
                        $isHealthy = $scan->quality_status === 'healthy';
                    @endphp
                    <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-bold {{ $isHealthy ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $scan->predicted_class ?? 'Pemeriksaan Daun' }}
                            </div>
                            <div class="text-gray-500 mt-0.5">{{ $scan->farm?->name ?? 'Lahan' }} &bull; {{ $scan->recommendation?->action ?? 'Tanaman dalam kondisi optimal.' }}</div>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $isHealthy ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $isHealthy ? 'Sehat' : 'Terdeteksi' }}
                            </span>
                            <div class="text-[10px] text-gray-400 mt-1">{{ $date ? $date->translatedFormat('d M') : '-' }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 py-6 text-center">Belum ada riwayat scan penyakit.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
