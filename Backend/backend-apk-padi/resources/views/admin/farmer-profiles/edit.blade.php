@extends('layouts.admin')

@php
    $title = 'Edit Profil Publik: ' . $profile->business_name;
    $baseDomain = config('domains.base', 'localhost');
@endphp

@section('content')

<div class="space-y-6 max-w-4xl">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[#0f172a]">Edit Profil Publik Petani</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola website untuk {{ $profile->farmer?->name }} ({{ $profile->subdomain }}.{{ $baseDomain }}).</p>
        </div>
        <div class="flex items-center gap-3">
            @if ($profile->subdomain)
                <a href="{{ $profile->publicUrl() }}" target="_blank"
                    class="text-sm text-[#1b5e20] hover:text-[#145218] border border-green-200 px-4 py-2 rounded-lg transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                        <polyline points="15 3 21 3 21 9"/>
                    </svg>
                    Lihat Website
                </a>
            @endif
            <a href="{{ route('admin.farmer-profiles.index') }}"
                class="text-sm text-slate-600 hover:text-[#0f172a] border border-gray-200 px-4 py-2 rounded-lg transition-colors">
                Kembali
            </a>
        </div>
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

    <form method="POST" action="{{ route('admin.farmer-profiles.update', $profile) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PATCH')

        {{-- Section 1: Basic Info --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm space-y-5">
            <h2 class="font-bold text-[#0f172a] text-base border-b border-gray-100 pb-3">1. Informasi Usaha</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1">Pemilik / Petani</label>
                    <p class="text-sm font-semibold text-[#0f172a] bg-gray-50 px-4 py-2.5 rounded-lg border border-gray-200">
                        {{ $profile->farmer?->name }} ({{ $profile->farmer?->email }})
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="profile_template_id">
                        Template Website <span class="text-red-500">*</span>
                    </label>
                    <select name="profile_template_id" id="profile_template_id" required
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]">
                        @foreach ($templates as $tpl)
                            <option value="{{ $tpl->id }}" {{ old('profile_template_id', $profile->profile_template_id) == $tpl->id ? 'selected' : '' }}>
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
                            value="{{ old('subdomain', $profile->subdomain) }}"
                            required maxlength="40"
                            class="flex-1 border border-r-0 border-gray-200 rounded-l-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]"
                            placeholder="pakjoko">
                        <span class="inline-flex items-center px-4 text-sm text-slate-500 bg-gray-50 border border-l-0 border-gray-200 rounded-r-lg">
                            .{{ $baseDomain }}
                        </span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="business_name">
                        Nama Usaha Tani <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="business_name" id="business_name"
                        value="{{ old('business_name', $profile->business_name) }}"
                        required maxlength="150"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="headline">
                        Tagline / Slogan
                    </label>
                    <input type="text" name="headline" id="headline"
                        value="{{ old('headline', $profile->headline) }}"
                        maxlength="255"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="description">
                        Deskripsi Usaha
                    </label>
                    <textarea name="description" id="description" rows="3" maxlength="3000"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20] resize-none">{{ old('description', $profile->description) }}</textarea>
                </div>

            </div>
        </div>

        {{-- Section 2: Media & Contact --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm space-y-5">
            <h2 class="font-bold text-[#0f172a] text-base border-b border-gray-100 pb-3">2. Media & Kontak</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1">Logo Usaha</label>
                    @if ($profile->logo_path)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $profile->logo_path) }}" alt="Logo" class="w-14 h-14 rounded-lg object-cover border border-gray-200">
                        </div>
                    @endif
                    <input type="file" name="logo" accept="image/*"
                        class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#1b5e20]/10 file:text-[#1b5e20]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1">Foto Cover</label>
                    @if ($profile->cover_image_path)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $profile->cover_image_path) }}" alt="Cover" class="w-full h-14 rounded-lg object-cover border border-gray-200">
                        </div>
                    @endif
                    <input type="file" name="cover_image" accept="image/*"
                        class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#1b5e20]/10 file:text-[#1b5e20]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="whatsapp">WhatsApp</label>
                    <input type="text" name="whatsapp" id="whatsapp" value="{{ old('whatsapp', $profile->whatsapp) }}"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="public_phone">Telepon</label>
                    <input type="text" name="public_phone" id="public_phone" value="{{ old('public_phone', $profile->public_phone) }}"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="public_email">Email Publik</label>
                    <input type="email" name="public_email" id="public_email" value="{{ old('public_email', $profile->public_email) }}"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="public_address">Lokasi Umum</label>
                    <input type="text" name="public_address" id="public_address" value="{{ old('public_address', $profile->public_address) }}"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="instagram_url">Link Instagram</label>
                    <input type="url" name="instagram_url" id="instagram_url" value="{{ old('instagram_url', $profile->instagram_url) }}"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="facebook_url">Link Facebook</label>
                    <input type="url" name="facebook_url" id="facebook_url" value="{{ old('facebook_url', $profile->facebook_url) }}"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]">
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
                        <option value="published" {{ old('website_status', $profile->website_status?->value) === 'published' ? 'selected' : '' }}>Tayang (Published)</option>
                        <option value="draft" {{ old('website_status', $profile->website_status?->value) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="review" {{ old('website_status', $profile->website_status?->value) === 'review' ? 'selected' : '' }}>Menunggu Review</option>
                        <option value="suspended" {{ old('website_status', $profile->website_status?->value) === 'suspended' ? 'selected' : '' }}>Ditangguhkan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#0f172a] mb-1" for="verification_status">
                        Status Verifikasi <span class="text-red-500">*</span>
                    </label>
                    <select name="verification_status" id="verification_status" required
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1b5e20]">
                        <option value="verified" {{ old('verification_status', $profile->verification_status?->value) === 'verified' ? 'selected' : '' }}>Terverifikasi P.A.D.I.</option>
                        <option value="unverified" {{ old('verification_status', $profile->verification_status?->value) === 'unverified' ? 'selected' : '' }}>Belum Diverifikasi</option>
                        <option value="rejected" {{ old('verification_status', $profile->verification_status?->value) === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
            </div>

            <div>
                <p class="text-sm font-semibold text-[#0f172a] mb-3">Tampilkan Bagian pada Website Publik:</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    @foreach (\App\Models\FarmerPublicProfile::DEFAULT_SECTION_SETTINGS as $secKey => $secDefault)
                        <label class="flex items-center gap-2 p-3 bg-gray-50 rounded-xl border border-gray-100 cursor-pointer hover:bg-gray-100 transition-colors">
                            <input type="checkbox" name="section_settings[{{ $secKey }}]" value="1"
                                {{ old("section_settings.{$secKey}", ($settings[$secKey] ?? false) ? '1' : '0') == '1' ? 'checked' : '' }}
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
                Simpan Perubahan
            </button>
        </div>

    </form>

</div>

@endsection
