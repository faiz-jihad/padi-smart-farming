@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/operational.css') }}">

<div class="admin-page">
    <div class="admin-page__header">
        <div>
            <p class="admin-page__eyebrow">Admin</p>
            <h1 class="admin-page__title">Broadcast</h1>
            <p class="admin-page__description">Broadcast tersimpan di database. Saat dibuat atau diubah, admin menerima notifikasi realtime lewat WebSocket.</p>
        </div>
    </div>

    @if(session('status'))
        <div class="admin-alert">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="admin-alert">{{ $errors->first() }}</div>
    @endif

    <div class="admin-grid">
        <div class="admin-stat"><span>Total</span><strong>{{ number_format($stats['total'], 0, ',', '.') }}</strong></div>
        <div class="admin-stat"><span>Published</span><strong>{{ number_format($stats['published'], 0, ',', '.') }}</strong></div>
        <div class="admin-stat"><span>Draft</span><strong>{{ number_format($stats['draft'], 0, ',', '.') }}</strong></div>
        <div class="admin-stat"><span>Expired</span><strong>{{ number_format($stats['expired'], 0, ',', '.') }}</strong></div>
    </div>

    <section class="admin-card">
        <div class="admin-card__header">
            <div class="admin-card__title">
                <span>Form</span>
                <h2>Buat Broadcast</h2>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.broadcast.store') }}" class="admin-form">
            @csrf
            <label class="admin-field"><span>Judul</span><input class="admin-input" name="title" value="{{ old('title') }}" required></label>
            <label class="admin-field"><span>Pesan</span><textarea class="admin-textarea" name="message" required>{{ old('message') }}</textarea></label>
            <div class="admin-form--inline">
                <label class="admin-field"><span>Tipe</span>
                    <select class="admin-select" name="type">
                        @foreach(['info', 'warning', 'announcement', 'system'] as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="admin-field"><span>Status</span>
                    <select class="admin-select" name="status">
                        @foreach(['draft', 'published', 'expired'] as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="admin-field"><span>Kedaluwarsa</span><input class="admin-input" type="datetime-local" name="expires_at"></label>
                <button class="admin-button" type="submit">Kirim</button>
            </div>
        </form>
    </section>

    <section class="admin-card">
        <div class="admin-card__header">
            <div class="admin-card__title">
                <span>Database</span>
                <h2>Daftar Broadcast</h2>
            </div>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Broadcast</th>
                        <th>Tipe</th>
                        <th>Status</th>
                        <th>Publish</th>
                        <th>Edit</th>
                        <th>Hapus</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($broadcasts as $broadcast)
                        <tr>
                            <td>
                                <strong>{{ $broadcast->title }}</strong>
                                <small>{{ $broadcast->message }}</small>
                                <small>Oleh {{ $broadcast->admin?->name ?? '-' }}</small>
                            </td>
                            <td>
                                <form id="broadcast-update-{{ $broadcast->id }}" method="POST" action="{{ route('admin.broadcast.update', $broadcast) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="title" value="{{ $broadcast->title }}">
                                    <input type="hidden" name="message" value="{{ $broadcast->message }}">
                                    <select class="admin-select" name="type">
                                        @foreach(['info', 'warning', 'announcement', 'system'] as $type)
                                            <option value="{{ $type }}" @selected($broadcast->type === $type)>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td>
                                <select class="admin-select" name="status" form="broadcast-update-{{ $broadcast->id }}">
                                    @foreach(['draft', 'published', 'expired'] as $status)
                                        <option value="{{ $status }}" @selected($broadcast->status === $status)>{{ $status }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>{{ $broadcast->published_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td><button class="admin-button" type="submit" form="broadcast-update-{{ $broadcast->id }}">Update</button></td>
                            <td>
                                <form method="POST" action="{{ route('admin.broadcast.destroy', $broadcast) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="admin-button admin-button--light" type="submit">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="admin-empty">Belum ada broadcast di database.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">{{ $broadcasts->withQueryString()->links() }}</div>
    </section>
</div>
@endsection
