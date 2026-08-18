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
        <span class="market-breadcrumb-current">Marketplace Hasil Panen</span>
    </nav>

    {{-- Page Header --}}
    <div class="market-header">
        <div class="market-header-content">
            <h1 class="market-title">Manajemen Marketplace & Listing Panen</h1>
            <p class="market-description">Kelola katalog komoditas hasil panen, link transaksi penjualan (Tokopedia/Shopee/WA), foto produk, dan penawaran dari mitra.</p>
        </div>

        <a href="{{ route('admin.marketplace.create') }}" class="btn-market-action btn-market-primary">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            <span>Tambah Listing Produk Baru</span>
        </a>
    </div>

    {{-- Status Alerts --}}
    @if(session('status'))
        <div class="market-alert market-alert-success" id="alert-status">
            <span>{{ session('status') }}</span>
            <button type="button" style="background:transparent; border:none; cursor:pointer;" onclick="document.getElementById('alert-status').remove()">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Stat KPI Cards --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Total Listing</p>
                <h3 class="stat-number">{{ number_format($stats['listings'], 0, ',', '.') }}</h3>
                <p class="stat-description">Produk terdaftar di sistem</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Published / Aktif</p>
                <h3 class="stat-number" style="color:#1b5e20;">{{ number_format($stats['published'], 0, ',', '.') }}</h3>
                <p class="stat-description">Tampil aktif di marketplace</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Total Penawaran</p>
                <h3 class="stat-number">{{ number_format($stats['offers'], 0, ',', '.') }}</h3>
                <p class="stat-description">Tawaran masuk dari mitra</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <p class="stat-label">Penawaran Pending</p>
                <h3 class="stat-number" style="color:#0f172a;">{{ number_format($stats['pending_offers'], 0, ',', '.') }}</h3>
                <p class="stat-description">Menunggu konfirmasi</p>
            </div>
        </div>
    </div>

    {{-- Data Listing Panen Card --}}
    <section class="data-card">
        <div class="data-header">
            <div>
                <h2>Katalog Listing Produk & Hasil Panen</h2>
                <p>Menampilkan {{ $listings->firstItem() ?? 0 }} - {{ $listings->lastItem() ?? 0 }} dari {{ $listings->total() }} produk terdaftar</p>
            </div>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="filter-wrapper">
            <form method="GET" action="{{ route('admin.marketplace.index') }}" class="filter-form">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nama komoditas atau deskripsi..." style="padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:13px; outline:none; width:260px;">

                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="published" @selected(($filters['status'] ?? '') === 'published')>Published</option>
                    <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Draft</option>
                    <option value="closed" @selected(($filters['status'] ?? '') === 'closed')>Closed / Terjual</option>
                    <option value="expired" @selected(($filters['status'] ?? '') === 'expired')>Expired</option>
                </select>

                <button type="submit" class="btn-filter-submit">Filter</button>
                @if(!empty($filters['search']) || !empty($filters['status']))
                    <a href="{{ route('admin.marketplace.index') }}" class="btn-market-action" style="padding:9px 14px;">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Foto Produk</th>
                        <th>Komoditas & Lahan</th>
                        <th>Link Penjualan</th>
                        <th>Petani</th>
                        <th>Jumlah Total</th>
                        <th>Harga Satuan</th>
                        <th>Status</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($listings as $listing)
                        @php
                            $thumb = $listing->image_url ?? ($listing->images->first()?->image_path ?? asset('images/padi-logo.jpeg'));
                        @endphp
                        <tr>
                            <td>
                                <img src="{{ $thumb }}" alt="{{ $listing->commodity }}" class="product-thumb" onerror="this.src='{{ asset('images/padi-logo.jpeg') }}'">
                            </td>
                            <td>
                                <strong style="font-size:15px; color:#0f172a; display:block;">{{ $listing->commodity }}</strong>
                                <span style="font-size:12px; color:#64748b; display:block;">{{ Str::limit($listing->description ?? 'Tidak ada deskripsi', 50) }}</span>
                                <span style="font-size:11px; font-weight:700; color:#1b5e20;">{{ $listing->farm?->name ?? '-' }}</span>
                            </td>
                            <td>
                                @if($listing->sales_link)
                                    <a href="{{ $listing->sales_link }}" target="_blank" class="sales-link-btn" title="{{ $listing->sales_link }}">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="14" height="14">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        <span>Buka Link</span>
                                    </a>
                                @else
                                    <span style="font-size:12px; color:#94a3b8;">- Tidak Ada Link -</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $listing->farmer?->name ?? '-' }}</strong>
                            </td>
                            <td>
                                <span style="font-size:14px; font-weight:700; color:#0f172a;">{{ number_format((float) $listing->quantity, 2, ',', '.') }} {{ $listing->unit }}</span>
                            </td>
                            <td>
                                <span style="font-size:14px; font-weight:800; color:#1b5e20;">Rp {{ number_format((float) $listing->price_per_unit, 0, ',', '.') }}</span>
                            </td>
                            <td>
                                <span class="market-status-badge status-{{ $listing->status }}">
                                    {{ strtoupper($listing->status) }}
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <div style="display:flex; justify-content:flex-end; gap:6px; align-items:center;">
                                    <a href="{{ route('admin.marketplace.edit', $listing) }}" class="btn-market-action" style="padding:6px 12px; font-size:12px;">Edit</a>

                                    <form method="POST" action="{{ route('admin.marketplace.listings.destroy', $listing) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus listing {{ $listing->commodity }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-market-action" style="background:#fef2f2; color:#dc2626; border-color:#fca5a5; padding:6px 12px; font-size:12px; cursor:pointer;">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding:48px; text-align:center; color:#64748b;">Belum ada listing marketplace terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($listings->hasPages())
            <div class="pagination-wrapper">
                {{ $listings->withQueryString()->links() }}
            </div>
        @endif
    </section>

    {{-- Penawaran Terbaru Card --}}
    <section class="data-card">
        <div class="data-header">
            <div>
                <h2>Penawaran Terbaru Dari Pembeli / Mitra</h2>
                <p>10 tawaran pembelian hasil panen terbaru</p>
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID Offer</th>
                        <th>Komoditas Listing</th>
                        <th>Nama Mitra Pembeli</th>
                        <th>Harga Tawaran</th>
                        <th>Jumlah Tawaran</th>
                        <th>Status</th>
                        <th style="text-align:right;">Aksi Update Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($offers as $offer)
                        <tr>
                            <td><strong>#{{ $offer->id }}</strong></td>
                            <td>{{ $offer->listing?->commodity ?? '-' }}</td>
                            <td><strong>{{ $offer->partner?->name ?? '-' }}</strong></td>
                            <td><strong style="color:#1b5e20;">Rp {{ number_format((float) $offer->offered_price, 0, ',', '.') }}</strong></td>
                            <td>{{ number_format((float) $offer->quantity, 2, ',', '.') }}</td>
                            <td><span class="market-status-badge status-{{ $offer->status }}">{{ strtoupper($offer->status) }}</span></td>
                            <td style="text-align:right;">
                                <form method="POST" action="{{ route('admin.marketplace.offers.update', $offer) }}" style="display:inline-flex; gap:6px;">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" style="padding:6px 10px; border:1px solid #cbd5e1; border-radius:8px; font-size:12px; background:#fff;">
                                        @foreach(['pending', 'accepted', 'rejected', 'cancelled'] as $st)
                                            <option value="{{ $st }}" @selected($offer->status === $st)>{{ ucfirst($st) }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn-market-action" style="padding:6px 12px; font-size:12px; background:#1b5e20; color:#fff; border-color:#1b5e20; cursor:pointer;">Update</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding:48px; text-align:center; color:#64748b;">Belum ada penawaran marketplace di database.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
