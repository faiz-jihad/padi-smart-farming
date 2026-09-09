@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/marketplace.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/marketplace-create.css') }}">

<div class="market-page">

    {{-- Breadcrumb --}}
    <nav class="market-breadcrumb" aria-label="Breadcrumb">
        <span>Admin</span>

        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>

        <a href="{{ route('admin.marketplace.index') }}">
            Marketplace
        </a>

        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>

        <span class="market-breadcrumb-current">
            Tambah Listing Produk Baru
        </span>
    </nav>


    {{-- Header --}}
    <div class="market-header">
        <div>
            <h1 class="market-title">
                Tambah Listing Hasil Panen / Produk Baru
            </h1>

            <p class="market-description">
                Input produk hasil panen padi, benih, atau olahan dengan link transaksi eksternal & foto produk.
            </p>
        </div>

        <a
            href="{{ route('admin.marketplace.index') }}"
            class="btn-market-action"
        >
            Batal / Kembali
        </a>
    </div>


    {{-- Form Card --}}
    <div class="market-create-card">

        <form
            method="POST"
            action="{{ route('admin.marketplace.store') }}"
        >
            @csrf

            {{-- Data untuk JavaScript --}}
            <div
                id="marketplace-form-data"
                data-farms='@json($farms)'
                hidden
            ></div>


            {{-- PETANI & LAHAN --}}
            <div class="market-form-grid market-form-grid-2">

                <div class="market-form-group">

                    <label for="farmer_id">
                        Pilih Petani
                        <span class="required">*</span>
                    </label>

                    <select
                        name="farmer_id"
                        id="farmer_id"
                        required
                    >
                        <option value="">
                            -- Pilih Petani --
                        </option>

                        @foreach($farmers as $farmer)
                            <option value="{{ $farmer->id }}">
                                {{ $farmer->name }} ({{ $farmer->email }})
                            </option>
                        @endforeach
                    </select>

                </div>


                <div class="market-form-group">

                    <label for="farm_id">
                        Pilih Lahan Pertanian
                        <span class="required">*</span>
                    </label>

                    <select
                        name="farm_id"
                        id="farm_id"
                        required
                        disabled
                    >
                        <option value="">
                            -- Pilih Petani Terlebih Dahulu --
                        </option>
                    </select>

                </div>

            </div>


            {{-- KOMODITAS, JUMLAH, SATUAN --}}
            <div class="market-form-grid market-form-grid-product">

                <div class="market-form-group">

                    <label for="commodity">
                        Nama Komoditas / Produk
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="commodity"
                        id="commodity"
                        placeholder="Contoh: Gabah Kering Giling (GKG) Inpari 32"
                        value="{{ old('commodity') }}"
                        required
                    >

                </div>


                <div class="market-form-group">

                    <label for="quantity">
                        Jumlah Ton/Kg
                        <span class="required">*</span>
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        name="quantity"
                        id="quantity"
                        placeholder="10.0"
                        value="{{ old('quantity', '10.0') }}"
                        required
                    >

                </div>


                <div class="market-form-group">

                    <label for="unit">
                        Satuan
                        <span class="required">*</span>
                    </label>

                    <select
                        name="unit"
                        id="unit"
                        required
                    >
                        <option value="ton">Ton</option>
                        <option value="kg">Kg</option>
                        <option value="karung">Karung</option>
                    </select>

                </div>

            </div>


            {{-- KATEGORI --}}
            <div class="market-form-group market-category-group">

                <div class="market-category-label">

                    <label for="category_id">
                        Kategori
                        <span class="required">*</span>
                    </label>

                    <button
                        type="button"
                        id="open-add-category-modal"
                        class="btn-add-category"
                    >
                        + Tambah Kategori
                    </button>

                </div>


                <select
                    name="category_id"
                    id="category_id"
                    required
                >
                    <option value="">
                        -- Pilih Kategori --
                    </option>

                    @foreach(($categories ?? collect()) as $category)

                        <option
                            value="{{ $category->id }}"
                            data-slug="{{ $category->slug }}"
                            data-icon="{{ $category->icon }}"
                            @selected(old('category_id') == $category->id)
                        >
                            {{ $category->name }}
                        </option>

                    @endforeach

                </select>

                <span class="market-form-help">
                    Pilih kategori yang sesuai dengan jenis produk atau hasil panen.
                </span>

            </div>


            {{-- HARGA & STATUS --}}
            <div class="market-form-grid market-form-grid-2">

                <div class="market-form-group">

                    <label for="price_per_unit">
                        Harga per Satuan (Rp)
                        <span class="required">*</span>
                    </label>

                    <input
                        type="number"
                        step="100"
                        name="price_per_unit"
                        id="price_per_unit"
                        placeholder="7500"
                        value="{{ old('price_per_unit', '7500') }}"
                        required
                    >

                </div>


                <div class="market-form-group">

                    <label for="status">
                        Status Listing
                        <span class="required">*</span>
                    </label>

                    <select
                        name="status"
                        id="status"
                        required
                    >
                        <option value="published">
                            Published (Aktif Tampil)
                        </option>

                        <option value="draft">
                            Draft
                        </option>

                        <option value="closed">
                            Closed / Terjual
                        </option>
                    </select>

                </div>

            </div>


            {{-- SALES LINK & IMAGE --}}
            <div class="market-sales-card">

                <h4>
                    Link Transaksi Penjualan & Foto Produk:
                </h4>


                <div class="market-form-group">

                    <label for="sales_link">
                        Link Transaksi / Penjualan Eksternal
                    </label>

                    <input
                        type="url"
                        name="sales_link"
                        id="sales_link"
                        placeholder="https://wa.me/6281234567890 atau https://tokopedia.com/produk-padi"
                        value="{{ old('sales_link') }}"
                    >

                    <span class="market-form-help">
                        Link menuju WhatsApp, Shopee, Tokopedia, atau Platform E-Commerce B2B.
                    </span>

                </div>


                <div class="market-form-group">

                    <label for="image_url">
                        URL Foto / Gambar Produk
                    </label>

                    <input
                        type="text"
                        name="image_url"
                        id="image_url"
                        placeholder="https://images.unsplash.com/photo-1586201375761-83865001e31c?w=500"
                        value="{{ old('image_url') }}"
                    >

                    <span class="market-form-help">
                        Tautan URL gambar langsung (JPG, PNG, WebP) untuk foto produk.
                    </span>

                </div>

            </div>


            {{-- DESCRIPTION --}}
            <div class="market-form-group market-description-group">

                <label for="description">
                    Deskripsi Produk & Kualitas Panen
                </label>

                <textarea
                    name="description"
                    id="description"
                    rows="3"
                    placeholder="Kadar air 14%, kadar hampa <3%, bebas penyakit dan hama."
                >{{ old('description') }}</textarea>

            </div>


            {{-- BUTTON --}}
            <div class="market-form-actions">

                <button
                    type="submit"
                    class="btn-market-action btn-market-primary"
                >
                    Simpan Produk Baru
                </button>

                <a
                    href="{{ route('admin.marketplace.index') }}"
                    class="btn-market-action"
                >
                    Batal
                </a>

            </div>

        </form>

    </div>

</div>


{{-- =========================================================
     MODAL TAMBAH KATEGORI
========================================================= --}}
<div
    id="add-category-modal"
    class="category-modal"
    aria-hidden="true"
>

    <div
        id="add-category-modal-content"
        class="category-modal-content"
        role="dialog"
        aria-modal="true"
        aria-labelledby="add-category-title"
    >

        {{-- Header --}}
        <div class="category-modal-header">

            <div>

                <h3 id="add-category-title">
                    Tambah Kategori
                </h3>

                <p>
                    Tambahkan kategori baru untuk marketplace.
                </p>

            </div>

            <button
                type="button"
                id="close-add-category-modal"
                class="category-modal-close"
                aria-label="Tutup"
            >
                &times;
            </button>

        </div>


        {{-- Form --}}
        <form
            id="add-category-form"
            method="POST"
            action="{{ route('admin.marketplace.categories.store') }}"
        >

            @csrf

            <div class="category-modal-body">

                {{-- Nama --}}
                <div class="category-form-group">

                    <label for="new_category_name">
                        Nama Kategori
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="name"
                        id="new_category_name"
                        required
                        autocomplete="off"
                        placeholder="Contoh: GKP Panen"
                    >

                </div>


                {{-- Slug --}}
                <div class="category-form-group">

                    <label for="new_category_slug">
                        Slug
                    </label>

                    <input
                        type="text"
                        name="slug"
                        id="new_category_slug"
                        autocomplete="off"
                        placeholder="gkp-panen"
                    >

                    <span class="market-form-help">
                        Kosongkan jika ingin slug dibuat otomatis dari nama.
                    </span>

                </div>


                {{-- Icon --}}
                <div class="category-form-group">

                    <label for="new_category_icon">
                        Icon
                    </label>

                    <input
                        type="text"
                        name="icon"
                        id="new_category_icon"
                        autocomplete="off"
                        placeholder="grass"
                    >

                    <span class="market-form-help">
                        Contoh: grass, grain, rice_bowl, spa.
                    </span>

                </div>


                {{-- Urutan --}}
                <div class="category-form-group">

                    <label for="new_category_sort_order">
                        Urutan
                    </label>

                    <input
                        type="text"
                        id="new_category_sort_order"
                        value="{{ $nextSortOrder }}"
                        readonly
                    >
                    <span class="market-form-help">
                        Urutan dibuat otomatis berdasarkan kategori terakhir.
                    </span>

                </div>


                {{-- Status --}}
                <label class="category-active">

                    <input
                        type="checkbox"
                        name="is_active"
                        id="new_category_is_active"
                        value="1"
                        checked
                    >

                    <span>
                        Aktifkan kategori
                    </span>

                </label>


                {{-- Error --}}
                <div
                    id="add-category-error"
                    class="category-error"
                    role="alert"
                ></div>

            </div>


            {{-- Footer --}}
            <div class="category-modal-footer">

                <button
                    type="button"
                    id="cancel-add-category"
                    class="category-btn category-btn-secondary"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    id="save-category-button"
                    class="category-btn category-btn-primary"
                >
                    Simpan Kategori
                </button>

            </div>

        </form>

    </div>

</div>
@endsection


@push('scripts')
    <script src="{{ asset('js/admin/marketplace-create.js') }}"></script>
@endpush
