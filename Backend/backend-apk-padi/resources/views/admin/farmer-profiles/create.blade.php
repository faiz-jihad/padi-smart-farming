@extends('layouts.admin')

@php
    $title = 'Tambah Profil Publik Petani';
    $baseDomain = config('domains.base', 'localhost');
@endphp

@section('content')

<div class="space-y-6 max-w-4xl">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[#0f172a]">Tambah Profil Publik Petani</h1>
            <p class="text-slate-500 text-sm mt-1">Buat website company profile untuk petani langsung dari panel admin.</p>
        </div>
        <a href="{{ route('admin.farmer-profiles.index') }}"
            class="text-sm text-slate-600 hover:text-[#0f172a] border border-gray-200 px-4 py-2 rounded-lg transition-colors">
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
            <p class="text-sm font-semibold text-red-800 mb-1">Terdapat kesalahan pengisian:</p>
            <ul class="list-disc list-inside text-xs text-red-700 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.farmer-profiles.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Section 1: Farmer Account & Basic Info --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm space-y-5">
            <h2 class="font-bold text-[#0f172a] text-base border-b border-gray-100 pb-3">1. Akun Petani & Informasi Usaha</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="farmer_id">
                        Pilih Petani <span class="text-red-500">*</span>
                    </label>
                    <select name="farmer_id" id="farmer_id" required
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]">
                        <option value="">-- Pilih Akun Petani --</option>
                        @foreach ($farmers as $farmer)
                            <option value="{{ $farmer->id }}" {{ old('farmer_id', $selectedFarmerId) == $farmer->id ? 'selected' : '' }}>
                                {{ $farmer->name }} ({{ $farmer->email }} | {{ $farmer->phone }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="profile_template_id">
                        Template Website <span class="text-red-500">*</span>
                    </label>
                    <select name="profile_template_id" id="profile_template_id" required
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]">
                        @foreach ($templates as $tpl)
                            <option value="{{ $tpl->id }}" {{ old('profile_template_id') == $tpl->id ? 'selected' : '' }}>
                                {{ $tpl->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="subdomain">
                        Subdomain Website <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-stretch max-w-md">
                        <input type="text" name="subdomain" id="subdomain"
                            value="{{ old('subdomain') }}"
                            required maxlength="40"
                            class="flex-1 border border-r-0 border-gray-200 rounded-l-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]"
                            placeholder="pakjoko">
                        <span class="inline-flex items-center px-4 text-sm text-slate-500 bg-gray-50 border border-l-0 border-gray-200 rounded-r-lg">
                            .{{ $baseDomain }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">3–40 karakter huruf kecil, angka, dan tanda hubung (-).</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="business_name">
                        Nama Usaha Tani <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="business_name" id="business_name"
                        value="{{ old('business_name') }}"
                        required maxlength="150"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]"
                        placeholder="Contoh: UD Tani Makmur Jaya">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="headline">
                        Tagline / Slogan
                    </label>
                    <input type="text" name="headline" id="headline"
                        value="{{ old('headline') }}"
                        maxlength="255"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]"
                        placeholder="Contoh: Beras Organik Berkualitas Premium">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="description">
                        Deskripsi Profil Usaha
                    </label>
                    <textarea name="description" id="description" rows="3" maxlength="3000"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20] resize-none"
                        placeholder="Ceritakan tentang profil dan keunggulan usaha tani...">{{ old('description') }}</textarea>
                </div>

            </div>
        </div>

        {{-- Section 2: Media & Contact --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm space-y-5">
            <h2 class="font-bold text-[#0f172a] text-base border-b border-gray-100 pb-3">2. Foto & Kontak Publik</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1">Logo Usaha</label>
                    <input type="file" name="logo" accept="image/*"
                        class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#1b5e20]/10 file:text-[#1b5e20]">
                    <p class="text-xs text-slate-400 mt-1">Format: JPG, PNG, WebP (maks 2MB).</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1">Foto Cover / Banner</label>
                    <input type="file" name="cover_image" accept="image/*"
                        class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#1b5e20]/10 file:text-[#1b5e20]">
                    <p class="text-xs text-slate-400 mt-1">Format: JPG, PNG, WebP (maks 4MB).</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="whatsapp">WhatsApp</label>
                    <input type="text" name="whatsapp" id="whatsapp" value="{{ old('whatsapp') }}"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]"
                        placeholder="08123456789">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="public_phone">Telepon</label>
                    <input type="text" name="public_phone" id="public_phone" value="{{ old('public_phone') }}"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]"
                        placeholder="021-xxxxxxx">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="public_email">Email Publik</label>
                    <input type="email" name="public_email" id="public_email" value="{{ old('public_email') }}"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]"
                        placeholder="kontak@tani.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="public_address">Lokasi Umum</label>
                    <input type="text" name="public_address" id="public_address" value="{{ old('public_address') }}"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]"
                        placeholder="Kec. Kandanghaur, Kab. Indramayu">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="instagram_url">Link Instagram</label>
                    <input type="url" name="instagram_url" id="instagram_url" value="{{ old('instagram_url') }}"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]"
                        placeholder="https://instagram.com/akun">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="facebook_url">Link Facebook</label>
                    <input type="url" name="facebook_url" id="facebook_url" value="{{ old('facebook_url') }}"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]"
                        placeholder="https://facebook.com/akun">
                </div>

            </div>
        </div>

        {{-- Section 3: Privacy & Status --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm space-y-5">
            <h2 class="font-bold text-[#0f172a] text-base border-b border-gray-100 pb-3">3. Status & Kontrol Privasi</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-4">
                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="website_status">
                        Status Website <span class="text-red-500">*</span>
                    </label>
                    <select name="website_status" id="website_status" required
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]">
                        <option value="published" {{ old('website_status', 'published') === 'published' ? 'selected' : '' }}>Tayang (Published)</option>
                        <option value="draft" {{ old('website_status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="review" {{ old('website_status') === 'review' ? 'selected' : '' }}>Menunggu Review</option>
                        <option value="suspended" {{ old('website_status') === 'suspended' ? 'selected' : '' }}>Ditangguhkan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="verification_status">
                        Status Verifikasi <span class="text-red-500">*</span>
                    </label>
                    <select name="verification_status" id="verification_status" required
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]">
                        <option value="verified" {{ old('verification_status', 'verified') === 'verified' ? 'selected' : '' }}>Terverifikasi P.A.D.I.</option>
                        <option value="unverified" {{ old('verification_status') === 'unverified' ? 'selected' : '' }}>Belum Diverifikasi</option>
                        <option value="rejected" {{ old('verification_status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
            </div>

            <div>
                <p class="text-sm font-semibold text-[#0f172a] mb-3">Tampilkan Bagian pada Website Publik:</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    @foreach ($defaults as $secKey => $secDefault)
                        <label class="flex items-center gap-2 p-3 bg-gray-50 rounded-xl border border-gray-100 cursor-pointer hover:bg-gray-100 transition-colors">
                            <input type="checkbox" name="section_settings[{{ $secKey }}]" value="1"
                                {{ old("section_settings.{$secKey}", $secDefault ? '1' : '0') == '1' ? 'checked' : '' }}
                                class="accent-[#1b5e20] rounded">
                            <span class="font-medium text-slate-700">{{ ucwords(str_replace('_', ' ', str_replace('show_', '', $secKey))) }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.farmer-profiles.index') }}"
                class="px-5 py-2.5 text-sm font-semibold text-slate-600 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                Batal
            </a>
            <button type="submit"
                class="px-6 py-2.5 text-sm font-semibold text-white bg-[#1b5e20] hover:bg-[#145218] rounded-xl transition-all shadow-sm">
                Simpan & Buat Website
            </button>
        </div>

    </form>

</div>

@endsection
