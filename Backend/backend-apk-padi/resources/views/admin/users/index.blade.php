@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/operational.css') }}">

<div class="admin-page">
    <div class="admin-page__header">
        <div>
            <p class="admin-page__eyebrow">Admin</p>
            <h1 class="admin-page__title">Pengguna</h1>
            <p class="admin-page__description">Data pengguna langsung dari tabel users. Perubahan role, status, dan verifikasi tersimpan ke database.</p>
        </div>

        <form method="GET" action="{{ route('admin.users.index') }}" class="admin-form--inline">
            <input class="admin-input" type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, telepon">
            <button class="admin-button" type="submit">Cari</button>
        </form>
    </div>

    @if(session('status'))
        <div class="admin-alert">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="admin-alert">{{ $errors->first() }}</div>
    @endif

    <div class="admin-grid">
        <div class="admin-stat"><span>Total</span><strong>{{ number_format($stats['total'], 0, ',', '.') }}</strong></div>
        <div class="admin-stat"><span>Aktif</span><strong>{{ number_format($stats['active'], 0, ',', '.') }}</strong></div>
        <div class="admin-stat"><span>Admin</span><strong>{{ number_format($stats['admins'], 0, ',', '.') }}</strong></div>
        <div class="admin-stat"><span>Suspended</span><strong>{{ number_format($stats['suspended'], 0, ',', '.') }}</strong></div>
    </div>

    <section class="admin-card">
        <div class="admin-card__header">
            <div class="admin-card__title">
                <span>Database</span>
                <h2>Daftar Akun</h2>
            </div>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Verifikasi</th>
                        <th>Login Terakhir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <strong>{{ $user->name }}</strong>
                                <small>{{ $user->email ?? '-' }}</small>
                                <small>{{ $user->phone ?? '-' }}</small>
                            </td>
                            <td>
                                <form id="user-update-{{ $user->id }}" method="POST" action="{{ route('admin.users.update', $user) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select class="admin-select" name="role">
                                        @foreach(['farmer' => 'Petani', 'ppl' => 'PPL', 'partner' => 'Partner', 'admin' => 'Admin'] as $value => $label)
                                            <option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td>
                                <select class="admin-select" name="status" form="user-update-{{ $user->id }}">
                                    @foreach(['active' => 'Aktif', 'inactive' => 'Tidak Aktif', 'suspended' => 'Suspended'] as $value => $label)
                                        <option value="{{ $value }}" @selected($user->status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select class="admin-select" name="verification_status" form="user-update-{{ $user->id }}">
                                    @foreach(['pending' => 'Pending', 'verified' => 'Verified', 'rejected' => 'Rejected'] as $value => $label)
                                        <option value="{{ $value }}" @selected($user->verification_status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>{{ $user->last_login_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td><button class="admin-button" type="submit" form="user-update-{{ $user->id }}">Simpan</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="admin-empty">Belum ada pengguna di database.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">{{ $users->withQueryString()->links() }}</div>
    </section>
</div>
@endsection
