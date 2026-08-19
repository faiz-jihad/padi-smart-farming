@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/marketplace.css') }}">

<div class="market-page">
    {{-- Breadcrumb --}}
    <nav class="market-breadcrumb" aria-label="Breadcrumb">
        <span>Admin</span>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <a href="{{ route('admin.marketplace.index') }}" style="color:#64748b; text-decoration:none;">Marketplace</a>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="market-breadcrumb-current">Tambah Listing Produk Baru</span>
    </nav>

    {{-- Header --}}
    <div class="market-header">
        <div>
            <h1 class="market-title">Tambah Listing Hasil Panen / Produk Baru</h1>
            <p class="market-description">Input produk hasil panen padi, benih, atau olahan dengan link transaksi eksternal & foto produk.</p>
        </div>

        <a href="{{ route('admin.marketplace.index') }}" class="btn-market-action">Batal / Kembali</a>
    </div>

    {{-- Form Card --}}
    <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; padding:28px;">
        <form method="POST" action="{{ route('admin.marketplace.store') }}">
            @csrf

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="farmer_id">Pilih Petani <span style="color:#dc2626;">*</span></label>
                    <select name="farmer_id" id="farmer_id" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff;" required>
                        @foreach($farmers as $farmer)
                            <option value="{{ $farmer->id }}">{{ $farmer->name }} ({{ $farmer->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="farm_id">Pilih Lahan Pertanian <span style="color:#dc2626;">*</span></label>
                    <select name="farm_id" id="farm_id" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff;" required>
                        @foreach($farms as $farm)
                            <option value="{{ $farm->id }}">{{ $farm->name }} ({{ $farm->farmer?->name ?? 'Admin' }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 2fr 1fr 1fr; gap:20px; margin-bottom:20px;">
                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="commodity">Nama Komoditas / Produk <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="commodity" id="commodity" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; box-sizing:border-box;" placeholder="Contoh: Gabah Kering Giling (GKG) Inpari 32" value="{{ old('commodity') }}" required>
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="quantity">Jumlah Ton/Kg <span style="color:#dc2626;">*</span></label>
                    <input type="number" step="0.01" name="quantity" id="quantity" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; box-sizing:border-box;" placeholder="10.0" value="{{ old('quantity', '10.0') }}" required>
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="unit">Satuan <span style="color:#dc2626;">*</span></label>
                    <select name="unit" id="unit" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff;" required>
                        <option value="ton">Ton</option>
                        <option value="kg">Kg</option>
                        <option value="karung">Karung</option>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="price_per_unit">Harga per Satuan (Rp) <span style="color:#dc2626;">*</span></label>
                    <input type="number" step="100" name="price_per_unit" id="price_per_unit" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; box-sizing:border-box;" placeholder="7500" value="{{ old('price_per_unit', '7500') }}" required>
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="status">Status Listing <span style="color:#dc2626;">*</span></label>
                    <select name="status" id="status" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff;" required>
                        <option value="published">Published (Aktif Tampil)</option>
                        <option value="draft">Draft</option>
                        <option value="closed">Closed / Terjual</option>
                    </select>
                </div>
            </div>

            {{-- SALES LINK AND IMAGE URL --}}
            <div style="background:#f0fdf4; border:1px solid #a7f3d0; border-radius:12px; padding:20px; margin-bottom:20px;">
                <h4 style="margin:0 0 12px 0; font-size:14px; font-weight:700; color:#1b5e20;">Link Transaksi Penjualan & Foto Produk:</h4>

                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:4px;" for="sales_link">Link Transaksi / Penjualan Eksternal</label>
                    <input type="url" name="sales_link" id="sales_link" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff; box-sizing:border-box;" placeholder="https://wa.me/6281234567890 atau https://tokopedia.com/produk-padi" value="{{ old('sales_link') }}">
                    <span style="font-size:11px; color:#64748b; display:block; margin-top:4px;">Link menuju WhatsApp, Shopee, Tokopedia, atau Platform E-Commerce B2B.</span>
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:4px;" for="image_url">URL Foto / Gambar Produk</label>
                    <input type="url" name="image_url" id="image_url" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; background:#fff; box-sizing:border-box;" placeholder="https://images.unsplash.com/photo-1586201375761-83865001e31c?w=500" value="{{ old('image_url') }}">
                    <span style="font-size:11px; color:#64748b; display:block; margin-top:4px;">Tautan URL gambar langsung (JPG, PNG, WebP) untuk foto produk.</span>
                </div>
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px;" for="description">Deskripsi Produk & Kualitas Panen</label>
                <textarea name="description" id="description" rows="3" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; box-sizing:border-box;" placeholder="Kadar air 14%, kadar hampa <3%, bebas penyakit dan hama.">{{ old('description') }}</textarea>
            </div>

            <div style="display:flex; gap:12px;">
                <button type="submit" class="btn-market-action btn-market-primary" style="padding:12px 24px; border:none; cursor:pointer;">Simpan Produk Baru</button>
                <a href="{{ route('admin.marketplace.index') }}" class="btn-market-action">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
