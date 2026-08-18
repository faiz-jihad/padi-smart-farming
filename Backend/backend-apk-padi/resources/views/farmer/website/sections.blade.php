@extends('layouts.farmer-panel')
@section('title', 'Pengaturan Privasi')

@section('content')

<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-[#0f172a]">Pengaturan Privasi</h1>
        <p class="text-slate-500 text-sm mt-1">Pilih data apa saja yang ingin Anda tampilkan di website publik.</p>
    </div>

    <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 text-sm text-amber-800">
        <strong>Privasi Anda dilindungi.</strong>
        Data sensitif (koordinat lahan, rincian panen, data keuangan) bawaan non-aktif.
        Aktifkan hanya jika Anda memang ingin data tersebut terlihat publik.
    </div>

    <form method="POST" action="{{ route('farmer.website.sections.update') }}" class="space-y-4">
        @csrf

        @php
            $sectionConfig = [
                'show_products' => [
                    'label' => 'Daftar Produk',
                    'desc'  => 'Listing produk yang sudah dipublikasikan di Marketplace.',
                    'safe'  => true,
                ],
                'show_location' => [
                    'label' => 'Lokasi Umum',
                    'desc'  => 'Hanya menampilkan nama kecamatan/kabupaten. Koordinat tidak pernah ditampilkan.',
                    'safe'  => true,
                ],
                'show_gallery' => [
                    'label' => 'Galeri Foto',
                    'desc'  => 'Foto-foto yang Anda unggah ke galeri profil.',
                    'safe'  => true,
                ],
                'show_contact' => [
                    'label' => 'Informasi Kontak',
                    'desc'  => 'WhatsApp, email, dan telepon yang Anda isi di halaman Edit Profil.',
                    'safe'  => true,
                ],
                'show_harvests' => [
                    'label' => 'Riwayat Panen',
                    'desc'  => 'Jumlah, kualitas, dan varietas panen. Tidak ada data harga atau keuangan.',
                    'safe'  => false,
                ],
                'show_productivity' => [
                    'label' => 'Statistik Produktivitas',
                    'desc'  => 'Total lahan (ha), jumlah musim tanam, dan produktivitas ton/ha.',
                    'safe'  => false,
                ],
                'show_fields' => [
                    'label' => 'Data Lahan',
                    'desc'  => 'Nama lahan dan irigasi. Koordinat GPS tidak pernah ditampilkan.',
                    'safe'  => false,
                ],
                'show_active_variety' => [
                    'label' => 'Varietas Tanam Aktif',
                    'desc'  => 'Varietas padi yang sedang ditanam saat ini.',
                    'safe'  => false,
                ],
            ];
        @endphp

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm divide-y divide-gray-50">
            @foreach ($sectionConfig as $key => $config)
                <div class="flex items-start gap-4 p-5">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <p class="text-sm font-semibold text-[#0f172a]">{{ $config['label'] }}</p>
                            @if ($config['safe'])
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">Aman</span>
                            @else
                                <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium">Privat</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500">{{ $config['desc'] }}</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 mt-0.5">
                        <input type="checkbox"
                            name="section_settings[{{ $key }}]"
                            value="1"
                            {{ ($settings[$key] ?? false) ? 'checked' : '' }}
                            class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-[#1b5e20] rounded-full peer peer-checked:bg-[#1b5e20] transition-all after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                    </label>
                </div>
            @endforeach
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('farmer.website.index') }}" class="text-sm text-slate-500 hover:text-[#0f172a] transition-colors">
                Kembali
            </a>
            <button type="submit"
                class="bg-[#1b5e20] hover:bg-[#145218] text-white font-semibold py-3 px-8 rounded-xl transition-all shadow-sm">
                Simpan Pengaturan
            </button>
        </div>

    </form>

</div>

@endsection
