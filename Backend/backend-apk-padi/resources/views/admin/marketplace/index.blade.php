@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/operational.css') }}">

<div class="admin-page">
    <div class="admin-page__header">
        <div>
            <p class="admin-page__eyebrow">Admin</p>
            <h1 class="admin-page__title">Marketplace</h1>
            <p class="admin-page__description">Moderasi listing hasil panen dan status penawaran langsung dari database marketplace.</p>
        </div>
    </div>

    @if(session('status'))
        <div class="admin-alert">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="admin-alert">{{ $errors->first() }}</div>
    @endif

    <div class="admin-grid">
        <div class="admin-stat"><span>Listing</span><strong>{{ number_format($stats['listings'], 0, ',', '.') }}</strong></div>
        <div class="admin-stat"><span>Published</span><strong>{{ number_format($stats['published'], 0, ',', '.') }}</strong></div>
        <div class="admin-stat"><span>Offer</span><strong>{{ number_format($stats['offers'], 0, ',', '.') }}</strong></div>
        <div class="admin-stat"><span>Pending Offer</span><strong>{{ number_format($stats['pending_offers'], 0, ',', '.') }}</strong></div>
    </div>

    <section class="admin-card">
        <div class="admin-card__header"><div class="admin-card__title"><span>Database</span><h2>Listing Panen</h2></div></div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>Komoditas</th><th>Petani</th><th>Jumlah</th><th>Harga</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($listings as $listing)
                        <tr>
                            <td><strong>{{ $listing->commodity }}</strong><small>{{ $listing->description ?? '-' }}</small><small>{{ $listing->farm?->name ?? '-' }}</small></td>
                            <td>{{ $listing->farmer?->name ?? '-' }}</td>
                            <td>{{ number_format((float) $listing->quantity, 2, ',', '.') }} {{ $listing->unit }}</td>
                            <td>Rp {{ number_format((float) $listing->price_per_unit, 0, ',', '.') }}</td>
                            <td><span class="admin-badge">{{ $listing->status }}</span></td>
                            <td>
                                <form method="POST" action="{{ route('admin.marketplace.listings.update', $listing) }}" class="admin-form--inline">
                                    @csrf
                                    @method('PATCH')
                                    <select class="admin-select" name="status">
                                        @foreach(['draft', 'published', 'closed', 'rejected', 'expired'] as $status)
                                            <option value="{{ $status }}" @selected($listing->status === $status)>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    <button class="admin-button" type="submit">Update</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="admin-empty">Belum ada listing marketplace di database.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">{{ $listings->withQueryString()->links() }}</div>
    </section>

    <section class="admin-card">
        <div class="admin-card__header"><div class="admin-card__title"><span>Action</span><h2>Penawaran Terbaru</h2></div></div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>Offer</th><th>Partner</th><th>Harga</th><th>Jumlah</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($offers as $offer)
                        <tr>
                            <td><strong>#{{ $offer->id }}</strong><small>{{ $offer->listing?->commodity ?? '-' }}</small></td>
                            <td>{{ $offer->partner?->name ?? '-' }}</td>
                            <td>Rp {{ number_format((float) $offer->offered_price, 0, ',', '.') }}</td>
                            <td>{{ number_format((float) $offer->quantity, 2, ',', '.') }}</td>
                            <td><span class="admin-badge">{{ $offer->status }}</span></td>
                            <td>
                                <form method="POST" action="{{ route('admin.marketplace.offers.update', $offer) }}" class="admin-form--inline">
                                    @csrf
                                    @method('PATCH')
                                    <select class="admin-select" name="status">
                                        @foreach(['pending', 'accepted', 'rejected', 'cancelled'] as $status)
                                            <option value="{{ $status }}" @selected($offer->status === $status)>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    <button class="admin-button" type="submit">Update</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="admin-empty">Belum ada offer marketplace di database.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
