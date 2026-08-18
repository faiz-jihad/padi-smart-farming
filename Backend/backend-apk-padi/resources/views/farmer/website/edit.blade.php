@extends('layouts.farmer-panel')
@section('title', 'Edit Profil Website')

@section('content')

@php
    $baseDomain = config('domains.base', 'localhost');
    $scheme = app()->environment('production') ? 'https' : 'http';
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-[#0f172a]">Edit Profil Website</h1>
        <p class="text-slate-500 text-sm mt-1">Informasi yang Anda isi akan ditampilkan di halaman publik.</p>
    </div>

    <form method="POST" action="{{ route('farmer.website.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Section 1: Basic Info --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
            <h2 class="font-bold text-[#0f172a] mb-5 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-[#1b5e20] text-white text-xs flex items-center justify-center font-bold">1</span>
                Informasi Utama
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="business_name">
                        Nama Usaha <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="business_name" id="business_name"
                        value="{{ old('business_name', $profile->business_name) }}"
                        required maxlength="150"
                        class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20] focus:border-transparent transition"
                        placeholder="Contoh: UD Tani Maju, Pak Joko Farm">
                    @error('business_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="headline">
                        Tagline / Slogan
                    </label>
                    <input type="text" name="headline" id="headline"
                        value="{{ old('headline', $profile->headline) }}"
                        maxlength="255"
                        class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20] focus:border-transparent transition"
                        placeholder="Contoh: Beras Organik Berkualitas dari Ladang Kami">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="description">
                        Deskripsi Usaha
                    </label>
                    <textarea name="description" id="description" rows="4" maxlength="3000"
                        class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20] focus:border-transparent transition resize-none"
                        placeholder="Ceritakan tentang usaha tani Anda — pengalaman, produk unggulan, dan keunikan...">{{ old('description', $profile->description) }}</textarea>
                </div>

            </div>
        </div>

        {{-- Section 2: Media --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
            <h2 class="font-bold text-[#0f172a] mb-5 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-[#1b5e20] text-white text-xs flex items-center justify-center font-bold">2</span>
                Foto & Media
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- Logo --}}
                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-2">Logo Usaha</label>
                    @if ($profile->logo_path)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $profile->logo_path) }}" alt="Logo" class="w-20 h-20 object-cover rounded-xl border border-gray-100">
                        </div>
                    @endif
                    <input type="file" name="logo" id="logo" accept="image/*"
                        class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-[#1b5e20]/10 file:text-[#1b5e20] hover:file:bg-[#1b5e20]/20 transition">
                    <p class="text-xs text-slate-400 mt-1">JPG, PNG, WebP. Maks 2MB. Rasio 1:1 disarankan.</p>
                    @error('logo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Cover --}}
                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-2">Foto Cover</label>
                    @if ($profile->cover_image_path)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $profile->cover_image_path) }}" alt="Cover" class="w-full h-20 object-cover rounded-xl border border-gray-100">
                        </div>
                    @endif
                    <input type="file" name="cover_image" id="cover_image" accept="image/*"
                        class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-[#1b5e20]/10 file:text-[#1b5e20] hover:file:bg-[#1b5e20]/20 transition">
                    <p class="text-xs text-slate-400 mt-1">JPG, PNG, WebP. Maks 4MB. Rasio 16:9 disarankan.</p>
                    @error('cover_image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Section 3: Contact --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
            <h2 class="font-bold text-[#0f172a] mb-1 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-[#1b5e20] text-white text-xs flex items-center justify-center font-bold">3</span>
                Kontak Publik
            </h2>
            <p class="text-xs text-slate-500 mb-5 ml-8">Hanya tampilkan kontak yang memang Anda inginkan diketahui publik.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="whatsapp">WhatsApp</label>
                    <input type="text" name="whatsapp" id="whatsapp"
                        value="{{ old('whatsapp', $profile->whatsapp) }}"
                        class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20] focus:border-transparent transition"
                        placeholder="08123456789">
                    <p class="text-xs text-slate-400 mt-1">Akan diubah otomatis ke format internasional.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="public_phone">Telepon Publik</label>
                    <input type="text" name="public_phone" id="public_phone"
                        value="{{ old('public_phone', $profile->public_phone) }}"
                        class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20] focus:border-transparent transition"
                        placeholder="022-xxxxxxx">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="public_email">Email Publik</label>
                    <input type="email" name="public_email" id="public_email"
                        value="{{ old('public_email', $profile->public_email) }}"
                        class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20] focus:border-transparent transition"
                        placeholder="kontak@usahatani.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="public_address">Alamat Umum</label>
                    <input type="text" name="public_address" id="public_address"
                        value="{{ old('public_address', $profile->public_address) }}"
                        class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20] focus:border-transparent transition"
                        placeholder="Contoh: Kec. Ciawi, Kab. Bogor, Jawa Barat">
                    <p class="text-xs text-slate-400 mt-1">Jangan cantumkan alamat lengkap demi privasi.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="instagram_url">Instagram</label>
                    <input type="url" name="instagram_url" id="instagram_url"
                        value="{{ old('instagram_url', $profile->instagram_url) }}"
                        class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20] focus:border-transparent transition"
                        placeholder="https://instagram.com/tanianda">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="facebook_url">Facebook</label>
                    <input type="url" name="facebook_url" id="facebook_url"
                        value="{{ old('facebook_url', $profile->facebook_url) }}"
                        class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20] focus:border-transparent transition"
                        placeholder="https://facebook.com/tanianda">
                </div>

            </div>
        </div>

        {{-- Section 4: Subdomain --}}
        <div id="subdomain-section" class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
            <h2 class="font-bold text-[#0f172a] mb-1 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-[#1b5e20] text-white text-xs flex items-center justify-center font-bold">4</span>
                Subdomain Website
            </h2>
            <p class="text-xs text-slate-500 mb-5 ml-8">Pilih nama unik untuk website Anda.</p>

            <div x-data="{
                subdomain: '{{ $profile->subdomain ?? '' }}',
                status: null,
                message: '',
                checking: false,
                checkUrl: '{{ route('farmer.website.subdomain.check') }}',
                baseDomain: '{{ $baseDomain }}',
                scheme: '{{ $scheme }}',
                debounceTimer: null,
                check() {
                    clearTimeout(this.debounceTimer);
                    if (this.subdomain.length < 3) { this.status = null; return; }
                    this.debounceTimer = setTimeout(() => {
                        this.checking = true;
                        fetch(this.checkUrl + '?subdomain=' + encodeURIComponent(this.subdomain))
                            .then(r => r.json())
                            .then(data => {
                                this.status = data.available ? 'available' : 'taken';
                                this.message = data.available ? (this.scheme + '://' + this.subdomain + '.' + this.baseDomain) : (data.message || 'Tidak tersedia');
                                this.checking = false;
                            });
                    }, 400);
                }
            }">

                <div class="flex items-stretch gap-0 max-w-lg">
                    <input type="text" name="subdomain_preview" id="subdomain_preview"
                        x-model="subdomain"
                        @input="check()"
                        maxlength="40"
                        class="flex-1 border border-r-0 border-gray-200 rounded-l-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20] focus:border-transparent transition"
                        placeholder="namaanda">
                    <span class="inline-flex items-center px-4 text-sm text-slate-500 bg-gray-50 border border-l-0 border-gray-200 rounded-r-lg whitespace-nowrap">
                        .{{ $baseDomain }}
                    </span>
                </div>

                {{-- Status indicator --}}
                <div class="mt-2 h-5">
                    <template x-if="checking">
                        <p class="text-xs text-slate-400">Memeriksa...</p>
                    </template>
                    <template x-if="!checking && status === 'available'">
                        <p class="text-xs text-green-600 font-medium" x-text="message"></p>
                    </template>
                    <template x-if="!checking && status === 'taken'">
                        <p class="text-xs text-red-500" x-text="message"></p>
                    </template>
                </div>

                @error('subdomain') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                {{-- Subdomain is saved via separate form --}}
            </div>

            <form method="POST" action="{{ route('farmer.website.subdomain.update') }}" class="mt-4">
                @csrf
                <input type="hidden" name="subdomain" id="subdomain_hidden"
                    x-bind:value="$el.form.querySelector('[x-model=subdomain]').value ?? ''"
                    x-ref="hidden_subdomain">
                <script>
                    // Sync Alpine.js value to hidden input before form submit
                    document.addEventListener('DOMContentLoaded', function () {
                        const form = document.querySelector('[action="{{ route('farmer.website.subdomain.update') }}"]');
                        form.addEventListener('submit', function () {
                            document.getElementById('subdomain_hidden').value =
                                document.getElementById('subdomain_preview').value;
                        });
                    });
                </script>
                <button type="submit"
                    class="bg-[#0f172a] text-white text-sm font-semibold px-5 py-2.5 rounded-lg hover:bg-[#1e293b] transition-all">
                    Simpan Subdomain
                </button>
            </form>

        </div>

        {{-- Save button --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('farmer.website.index') }}" class="text-sm text-slate-500 hover:text-[#0f172a] transition-colors">
                Kembali
            </a>
            <button type="submit"
                class="bg-[#1b5e20] hover:bg-[#145218] text-white font-semibold py-3 px-8 rounded-xl transition-all shadow-sm hover:shadow-md active:scale-[0.99]">
                Simpan Profil
            </button>
        </div>

    </form>

</div>

@endsection
