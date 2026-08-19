@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/users.css') }}">

<div class="users-page">
    {{-- Breadcrumb --}}
    <nav class="users-breadcrumb" aria-label="Breadcrumb">
        <span>Admin</span>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="users-breadcrumb-current">Pengguna</span>
    </nav>

    {{-- Page Header --}}
    <div class="users-header">
        <div class="users-header-content">
            <h1 class="users-title">Manajemen Pengguna</h1>
            <p class="users-description">Kelola akun pengguna, hak akses role, status verifikasi, dan pemantauan sistem.</p>
        </div>

        <button type="button" class="users-add-button" onclick="openModal('create-user-modal')">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            <span>Tambah Pengguna</span>
        </button>
    </div>

    {{-- Status Alerts --}}
    @if(session('status'))
        <div class="users-alert users-alert-success" id="alert-status">
            <span>{{ session('status') }}</span>
            <button type="button" class="users-alert-close" onclick="document.getElementById('alert-status').remove()">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="users-alert users-alert-danger" id="alert-errors">
            <span>{{ $errors->first() }}</span>
            <button type="button" class="users-alert-close" onclick="document.getElementById('alert-errors').remove()">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Stat KPI Cards --}}
    <div class="users-stat-grid">
        <div class="users-stat-card">
            <div class="users-stat-content">
                <p class="users-stat-label">Total Pengguna</p>
                <h3 class="users-stat-value">{{ number_format($stats['total'], 0, ',', '.') }}</h3>
                <p class="users-stat-description">Terdaftar di database</p>
            </div>
            <div class="users-stat-icon users-stat-icon-green">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
        </div>

        <div class="users-stat-card">
            <div class="users-stat-content">
                <p class="users-stat-label">Pengguna Aktif</p>
                <h3 class="users-stat-value">{{ number_format($stats['active'], 0, ',', '.') }}</h3>
                <p class="users-stat-description">Akun berstatus aktif</p>
            </div>
            <div class="users-stat-icon users-stat-icon-emerald">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <div class="users-stat-card">
            <div class="users-stat-content">
                <p class="users-stat-label">Administrator</p>
                <h3 class="users-stat-value">{{ number_format($stats['admins'], 0, ',', '.') }}</h3>
                <p class="users-stat-description">Akses penuh sistem</p>
            </div>
            <div class="users-stat-icon users-stat-icon-blue">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
        </div>

        <div class="users-stat-card">
            <div class="users-stat-content">
                <p class="users-stat-label">Ditangguhkan</p>
                <h3 class="users-stat-value">{{ number_format($stats['suspended'], 0, ',', '.') }}</h3>
                <p class="users-stat-description">Akun dibatasi</p>
            </div>
            <div class="users-stat-icon users-stat-icon-red">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Main Users Table Card --}}
    <section class="users-table-card">
        <div class="users-table-header">
            <div class="users-table-heading">
                <h2>Daftar Akun Pengguna</h2>
                <p>Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} akun</p>
            </div>

            <div class="users-filters">
                <form method="GET" action="{{ route('admin.users.index') }}" class="users-search-form">
                    <div class="users-search">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, atau telepon...">
                    </div>
                    <button type="submit" class="users-search-btn">Cari</button>
                    @if(request('search'))
                        <a href="{{ route('admin.users.index') }}" class="users-btn-cancel" style="display:inline-flex; align-items:center; text-decoration:none;">Reset</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="users-table-wrapper">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Identitas Pengguna</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Verifikasi</th>
                        <th>Login Terakhir</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        @php
                            $roleThemes = [
                                'farmer' => 'users-role-farmer',
                                'ppl' => 'users-role-ppl',
                                'partner' => 'users-role-partner',
                                'admin' => 'users-role-admin',
                            ];
                            $roleLabels = [
                                'farmer' => 'Petani',
                                'ppl' => 'PPL',
                                'partner' => 'Partner',
                                'admin' => 'Admin',
                            ];
                            $avatarThemes = [
                                'farmer' => 'users-avatar-green',
                                'ppl' => 'users-avatar-blue',
                                'partner' => 'users-avatar-purple',
                                'admin' => 'users-avatar-slate',
                            ];
                            $initials = strtoupper(substr($user->name, 0, 2));
                        @endphp
                        <tr>
                            <td>
                                <div class="users-person">
                                    <div class="users-avatar {{ $avatarThemes[$user->role] ?? 'users-avatar-slate' }}">
                                        {{ $initials }}
                                    </div>
                                    <div class="users-person-info">
                                        <p>{{ $user->name }}</p>
                                        <span>{{ $user->email }}</span>
                                        <span class="users-phone-badge">{{ $user->phone }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="users-role {{ $roleThemes[$user->role] ?? 'users-role-admin' }}">
                                    {{ $roleLabels[$user->role] ?? ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>
                                @if($user->status === 'active')
                                    <span class="users-status users-status-active">
                                        <span class="users-status-dot"></span>
                                        Aktif
                                    </span>
                                @elseif($user->status === 'suspended')
                                    <span class="users-status users-status-suspended">
                                        <span class="users-status-dot"></span>
                                        Suspended
                                    </span>
                                @else
                                    <span class="users-status users-status-inactive">
                                        <span class="users-status-dot"></span>
                                        Tidak Aktif
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($user->verification_status === 'verified')
                                    <span class="users-verify users-verify-verified">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Verified
                                    </span>
                                @elseif($user->verification_status === 'pending')
                                    <span class="users-verify users-verify-pending">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Pending
                                    </span>
                                @else
                                    <span class="users-verify users-verify-rejected">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Rejected
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="users-date">
                                    {{ $user->last_login_at ? $user->last_login_at->format('d M Y, H:i') : 'Belum Pernah' }}
                                </span>
                            </td>
                            <td class="users-action-cell">
                                <div class="users-actions">
                                    <button
                                        type="button"
                                        class="users-btn-edit"
                                        onclick="openEditModal({{ json_encode([
                                            'id' => $user->id,
                                            'name' => $user->name,
                                            'email' => $user->email,
                                            'phone' => $user->phone,
                                            'role' => $user->role,
                                            'status' => $user->status,
                                            'verification_status' => $user->verification_status,
                                            'update_url' => route('admin.users.update', $user),
                                        ]) }})"
                                    >
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </button>

                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="users-btn-delete"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus akun {{ $user->name }}?')"
                                            @disabled(auth()->id() === $user->id)
                                        >
                                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="users-empty">
                                Tidak ditemukan data pengguna di database.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="users-pagination">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </section>
</div>

{{-- MODAL TAMBAH PENGGUNA --}}
<div class="users-modal-backdrop" id="create-user-modal">
    <div class="users-modal-card">
        <div class="users-modal-header">
            <div>
                <h3 class="users-modal-title">Tambah Pengguna Baru</h3>
                <p class="users-modal-subtitle">Isi formulir untuk membuat akun pengguna di sistem P.A.D.I.</p>
            </div>
            <button type="button" class="users-modal-close" onclick="closeModal('create-user-modal')">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="users-modal-body">
                <div class="users-modal-grid">
                    <div class="users-modal-grid--full">
                        <label class="users-field-label" for="create-name">Nama Lengkap</label>
                        <input type="text" id="create-name" name="name" class="users-input" value="{{ old('name') }}" placeholder="Contoh: Ahmad Wijaya" required>
                    </div>

                    <div>
                        <label class="users-field-label" for="create-email">Alamat Email</label>
                        <input type="email" id="create-email" name="email" class="users-input" value="{{ old('email') }}" placeholder="nama@email.com" required>
                    </div>

                    <div>
                        <label class="users-field-label" for="create-phone">Nomor Telepon</label>
                        <input type="text" id="create-phone" name="phone" class="users-input" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" required>
                    </div>

                    <div class="users-modal-grid--full">
                        <label class="users-field-label" for="create-password">Password</label>
                        <input type="password" id="create-password" name="password" class="users-input" placeholder="Minimal 8 karakter" required>
                    </div>

                    <div>
                        <label class="users-field-label" for="create-role">Role Akses</label>
                        <select id="create-role" name="role" class="users-select" required>
                            <option value="farmer" @selected(old('role') === 'farmer')>Petani</option>
                            <option value="ppl" @selected(old('role') === 'ppl')>PPL (Penyuluh)</option>
                            <option value="partner" @selected(old('role') === 'partner')>Partner / Pembeli</option>
                            <option value="admin" @selected(old('role') === 'admin')>Administrator</option>
                        </select>
                    </div>

                    <div>
                        <label class="users-field-label" for="create-status">Status Akun</label>
                        <select id="create-status" name="status" class="users-select" required>
                            <option value="active" @selected(old('status') === 'active')>Aktif</option>
                            <option value="inactive" @selected(old('status') === 'inactive')>Tidak Aktif</option>
                            <option value="suspended" @selected(old('status') === 'suspended')>Suspended</option>
                        </select>
                    </div>

                    <div class="users-modal-grid--full">
                        <label class="users-field-label" for="create-verification">Status Verifikasi</label>
                        <select id="create-verification" name="verification_status" class="users-select" required>
                            <option value="verified" @selected(old('verification_status', 'verified') === 'verified')>Verified (Terverifikasi)</option>
                            <option value="pending" @selected(old('verification_status') === 'pending')>Pending (Menunggu)</option>
                            <option value="rejected" @selected(old('verification_status') === 'rejected')>Rejected (Ditolak)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="users-modal-footer">
                <button type="button" class="users-btn-cancel" onclick="closeModal('create-user-modal')">Batal</button>
                <button type="submit" class="users-btn-submit">Simpan Pengguna</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT PENGGUNA --}}
<div class="users-modal-backdrop" id="edit-user-modal">
    <div class="users-modal-card">
        <div class="users-modal-header">
            <div>
                <h3 class="users-modal-title">Edit Data Pengguna</h3>
                <p class="users-modal-subtitle">Perbarui informasi akun, role, dan verifikasi pengguna.</p>
            </div>
            <button type="button" class="users-modal-close" onclick="closeModal('edit-user-modal')">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>

        <form id="edit-user-form" method="POST" action="">
            @csrf
            @method('PATCH')

            <div class="users-modal-body">
                <div class="users-modal-grid">
                    <div class="users-modal-grid--full">
                        <label class="users-field-label" for="edit-name">Nama Lengkap</label>
                        <input type="text" id="edit-name" name="name" class="users-input" required>
                    </div>

                    <div>
                        <label class="users-field-label" for="edit-email">Alamat Email</label>
                        <input type="email" id="edit-email" name="email" class="users-input" required>
                    </div>

                    <div>
                        <label class="users-field-label" for="edit-phone">Nomor Telepon</label>
                        <input type="text" id="edit-phone" name="phone" class="users-input" required>
                    </div>

                    <div class="users-modal-grid--full">
                        <label class="users-field-label" for="edit-password">Password Baru <span style="font-weight:400; color:#94a3b8;">(opsional)</span></label>
                        <input type="password" id="edit-password" name="password" class="users-input" placeholder="Kosongkan jika tidak ingin mengubah password">
                    </div>

                    <div>
                        <label class="users-field-label" for="edit-role">Role Akses</label>
                        <select id="edit-role" name="role" class="users-select" required>
                            <option value="farmer">Petani</option>
                            <option value="ppl">PPL (Penyuluh)</option>
                            <option value="partner">Partner / Pembeli</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>

                    <div>
                        <label class="users-field-label" for="edit-status">Status Akun</label>
                        <select id="edit-status" name="status" class="users-select" required>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>

                    <div class="users-modal-grid--full">
                        <label class="users-field-label" for="edit-verification">Status Verifikasi</label>
                        <select id="edit-verification" name="verification_status" class="users-select" required>
                            <option value="verified">Verified (Terverifikasi)</option>
                            <option value="pending">Pending (Menunggu)</option>
                            <option value="rejected">Rejected (Ditolak)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="users-modal-footer">
                <button type="button" class="users-btn-cancel" onclick="closeModal('edit-user-modal')">Batal</button>
                <button type="submit" class="users-btn-submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('is-active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('is-active');
            document.body.style.overflow = '';
        }
    }

    function openEditModal(userData) {
        const form = document.getElementById('edit-user-form');
        form.action = userData.update_url;

        document.getElementById('edit-name').value = userData.name;
        document.getElementById('edit-email').value = userData.email;
        document.getElementById('edit-phone').value = userData.phone;
        document.getElementById('edit-password').value = '';
        document.getElementById('edit-role').value = userData.role;
        document.getElementById('edit-status').value = userData.status;
        document.getElementById('edit-verification').value = userData.verification_status;

        openModal('edit-user-modal');
    }

    // Close modal on Escape key press
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal('create-user-modal');
            closeModal('edit-user-modal');
        }
    });

    // Close modal on backdrop click
    document.querySelectorAll('.users-modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', function(event) {
            if (event.target === this) {
                closeModal(this.id);
            }
        });
    });
</script>
@endsection
