@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/operational.css') }}">

<div class="admin-page">
    <div class="admin-page__header">
        <div>
            <p class="admin-page__eyebrow">Admin</p>
            <h1 class="admin-page__title">Pengguna</h1>
            <p class="admin-page__description">CRUD akun pengguna langsung ke tabel users. Admin bisa membuat, mencari, memperbarui, dan menghapus akun tanpa data statis.</p>
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
                <span>Create</span>
                <h2>Tambah Pengguna</h2>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="admin-form admin-form--compact-grid">
            @csrf

            <label class="admin-field">
                <span>Nama</span>
                <input class="admin-input" type="text" name="name" value="{{ old('name') }}" placeholder="Nama pengguna" required>
            </label>

            <label class="admin-field">
                <span>Email</span>
                <input class="admin-input" type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required>
            </label>

            <label class="admin-field">
                <span>Telepon</span>
                <input class="admin-input" type="text" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" required>
            </label>

            <label class="admin-field">
                <span>Password</span>
                <input class="admin-input" type="password" name="password" placeholder="Minimal 8 karakter" required>
            </label>

            <label class="admin-field">
                <span>Role</span>
                <select class="admin-select" name="role" required>
                    @foreach(['farmer' => 'Petani', 'ppl' => 'PPL', 'partner' => 'Partner', 'admin' => 'Admin'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('role', 'farmer') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="admin-field">
                <span>Status</span>
                <select class="admin-select" name="status" required>
                    @foreach(['active' => 'Aktif', 'inactive' => 'Tidak Aktif', 'suspended' => 'Suspended'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', 'active') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="admin-field">
                <span>Verifikasi</span>
                <select class="admin-select" name="verification_status" required>
                    @foreach(['pending' => 'Pending', 'verified' => 'Verified', 'rejected' => 'Rejected'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('verification_status', 'verified') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <div class="admin-form__actions">
                <button class="admin-button" type="submit">Tambah Pengguna</button>
            </div>
        </form>
    </section>

    <section class="admin-card">
        <div class="admin-card__header">
            <div class="admin-card__title">
                <span>Read / Update / Delete</span>
                <h2>Daftar Akun</h2>
            </div>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table admin-table--users">
                <thead>
                    <tr>
                        <th>Identitas</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Verifikasi</th>
                        <th>Password</th>
                        <th>Login Terakhir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <form id="user-update-{{ $user->id }}" method="POST" action="{{ route('admin.users.update', $user) }}">
                                    @csrf
                                    @method('PATCH')
                                </form>

                                <label class="admin-field admin-field--table">
                                    <span>Nama</span>
                                    <input class="admin-input" form="user-update-{{ $user->id }}" type="text" name="name" value="{{ $user->name }}" required>
                                </label>
                                <label class="admin-field admin-field--table">
                                    <span>Email</span>
                                    <input class="admin-input" form="user-update-{{ $user->id }}" type="email" name="email" value="{{ $user->email }}" required>
                                </label>
                                <label class="admin-field admin-field--table">
                                    <span>Telepon</span>
                                    <input class="admin-input" form="user-update-{{ $user->id }}" type="text" name="phone" value="{{ $user->phone }}" required>
                                </label>
                            </td>
                            <td>
                                <select class="admin-select" name="role" form="user-update-{{ $user->id }}">
                                    @foreach(['farmer' => 'Petani', 'ppl' => 'PPL', 'partner' => 'Partner', 'admin' => 'Admin'] as $value => $label)
                                        <option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
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
                            <td>
                                <input class="admin-input" form="user-update-{{ $user->id }}" type="password" name="password" placeholder="Kosongkan jika tetap">
                            </td>
                            <td>{{ $user->last_login_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td>
                                <div class="admin-action-stack">
                                    <button class="admin-button" type="submit" form="user-update-{{ $user->id }}">Simpan</button>

                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            class="admin-button admin-button--light"
                                            type="submit"
                                            onclick="return confirm('Hapus pengguna {{ $user->name }}?')"
                                            @disabled(auth()->id() === $user->id)
                                        >
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="admin-empty">Belum ada pengguna di database.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">{{ $users->withQueryString()->links() }}</div>
    </section>
</div>
@endsection
