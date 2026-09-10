@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/government-data.css') }}?v={{ time() }}">
@endpush

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/government-data.css') }}?v={{ time() }}">

<div class="b2g-page-container">
    {{-- Breadcrumb --}}
    <nav class="b2g-breadcrumb" aria-label="Breadcrumb">
        <span>Admin</span>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <a href="{{ route('admin.government-data.index') }}">Government Data Center</a>
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m7 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="b2g-breadcrumb-current">Detail Langganan #{{ $subscription->id }}</span>
    </nav>

    {{-- Page Header --}}
    <div class="b2g-page-header">
        <div class="b2g-header-title-box">
            <div class="b2g-header-tag">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                Detail Registrasi B2G #{{ $subscription->id }}
            </div>
            <h1 class="b2g-page-title">{{ $subscription->agency_name }}</h1>
            <p class="b2g-page-subtitle">Terdaftar pada {{ $subscription->created_at->format('d M Y, H:i') }} WIB &bull; Status verifikasi dan masa aktif token akses kedinasan.</p>
        </div>
        <div class="b2g-page-actions">
            @if ($subscription->status === 'PENDING_PAYMENT')
                <form method="POST" action="{{ route('admin.government-data.confirm-payment', $subscription->id) }}" onsubmit="return confirm('Konfirmasi pembayaran manual untuk {{ $subscription->agency_name }}? Status akan berubah menjadi Menunggu Approval.');" style="display:inline;">
                    @csrf
                    <button type="submit" class="b2g-btn" style="background:#0284c7; color:#ffffff;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        Konfirmasi Pembayaran Manual
                    </button>
                </form>
            @endif

            @if ($subscription->status === 'PENDING_APPROVAL')
                <form method="POST" action="{{ route('admin.government-data.approve', $subscription->id) }}" onsubmit="return confirm('Setujui langganan dan terbitkan token 6 digit untuk {{ $subscription->agency_name }}?');" style="display:inline;">
                    @csrf
                    <button type="submit" class="b2g-btn b2g-btn--primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        Setujui &amp; Terbitkan Token 6 Digit
                    </button>
                </form>
            @endif

            @if ($subscription->isActive())
                <form method="POST" action="{{ route('admin.government-data.revoke', $subscription->id) }}" onsubmit="return confirm('Yakin ingin mencabut (revoke) token langganan ini? Token tidak akan dapat digunakan lagi.');" style="display:inline;">
                    @csrf
                    <button type="submit" class="b2g-btn b2g-btn--danger">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                        Cabut Akses Token (Revoke)
                    </button>
                </form>
            @endif

            <a href="{{ route('admin.government-data.index') }}" class="b2g-btn b2g-btn--secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Kembali
            </a>
        </div>
    </div>

    {{-- Flash & Generated Token Modal/Banner --}}
    @if (session('generated_token'))
    <div class="b2g-token-banner">
        <div class="b2g-token-banner__header">
            <div class="b2g-token-badge">
                <svg class="w-3.5 h-3.5 inline mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                TOKEN AKSES 6 DIGIT DIAKTIFKAN
            </div>

            <h3 class="b2g-token-title">
                Persetujuan Berhasil untuk {{ session('generated_agency', $subscription->agency_name) }}
            </h3>

            <p class="b2g-token-desc">
                Berikan token akses 6 digit berikut kepada perwakilan resmi instansi kedinasan.
                Token ini aktif selama {{ $subscription->plan_days }} hari
                (berlaku hingga <strong>{{ session('generated_expiry') }}</strong>).
            </p>
        </div>

        <div class="b2g-token-display">
            <div class="b2g-token-code-wrap">
                <span class="b2g-token-code">
                    {{ session('generated_token') }}
                </span>

                <button
                    type="button"
                    class="b2g-token-copy-btn"
                    onclick="navigator.clipboard.writeText('{{ session('generated_token') }}'); this.innerText = '✓ Tersalin!'; setTimeout(() => this.innerText = 'Salin Token', 2000);"
                >
                    <svg class="w-4 h-4 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="9" width="13" height="13" rx="2"/>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                    </svg>
                    Salin Token
                </button>
            </div>

            @if (session('whatsapp_url'))
                <a
                    href="{{ session('whatsapp_url') }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="b2g-token-wa-btn"
                    id="btn-whatsapp-credential"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.372-.025-.521-.075-.149-.669-1.611-.916-2.206-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                        <path d="M20.52 3.449A11.815 11.815 0 0012.04 0C5.495 0 .16 5.335.16 11.88c0 2.092.546 4.134 1.583 5.93L.055 24l6.335-1.662a11.865 11.865 0 005.65 1.438h.005c6.542 0 11.879-5.335 11.879-11.88a11.83 11.83 0 00-3.404-8.447zM12.045 21.785h-.004a9.855 9.855 0 01-5.026-1.378l-.36-.214-3.758.986 1.003-3.666-.235-.375a9.863 9.863 0 01-1.511-5.258c.001-5.45 4.436-9.884 9.89-9.884a9.83 9.83 0 017.002 2.903 9.85 9.85 0 012.898 7.006c-.002 5.45-4.437 9.88-9.899 9.88z"/>
                    </svg>
                    <span>Kirim Credential via WhatsApp</span>
                </a>
            @endif

            <div class="b2g-token-warning">
                ⚠️ Harap catat atau salin token ini sekarang.
                Demi privasi dan keamanan sistem, kode lengkap tidak akan ditampilkan kembali.
            </div>
        </div>
    </div>
    @endif

    {{-- Alert --}}
    @if (session('status'))
        <div class="b2g-alert b2g-alert--success">
            <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    {{-- 2-Column Info Grid --}}
    <div class="b2g-grid-2">
        {{-- Card 1: Informasi Instansi & PIC --}}
        <div class="b2g-card" style="margin-bottom:0;">
            <div class="b2g-card__header">
                <h2 class="b2g-card__title">Informasi Instansi &amp; PIC</h2>
                <span class="b2g-badge b2g-badge--info">DATA RESMI</span>
            </div>
            <div style="padding:24px;">
                <dl class="b2g-info-list">
                    <div class="b2g-info-row">
                        <dt class="b2g-info-dt">Nama Instansi</dt>
                        <dd class="b2g-info-dd" style="font-size:15px; font-weight:800; color:#0f172a;">{{ $subscription->agency_name }}</dd>
                    </div>
                    <div class="b2g-info-row">
                        <dt class="b2g-info-dt">Email Resmi Kedinasan</dt>
                        <dd class="b2g-info-dd">
                            <a href="mailto:{{ $subscription->agency_email }}" style="color:#166534; text-decoration:underline;">{{ $subscription->agency_email }}</a>
                        </dd>
                    </div>
                    <div class="b2g-info-row">
                        <dt class="b2g-info-dt">Nama PIC Penanggung Jawab</dt>
                        <dd class="b2g-info-dd" style="font-weight:700; color:#1e293b;">{{ $subscription->pic_name }}</dd>
                    </div>
                    <div class="b2g-info-row">
                        <dt class="b2g-info-dt">Kontak Telepon / WhatsApp</dt>
                        <dd class="b2g-info-dd">
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $subscription->pic_phone) }}" target="_blank" style="color:#059669; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                                <svg class="w-3.5 h-3.5 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                {{ $subscription->pic_phone }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="b2g-info-dt" style="margin-bottom:6px;">Keperluan &amp; Tujuan Akses Data Pertanian</dt>
                        <dd class="b2g-info-box">
                            {{ $subscription->purpose ?: 'Tidak ada catatan keperluan khusus yang dicantumkan.' }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Card 2: Status Layanan & Token --}}
        <div class="b2g-card" style="margin-bottom:0;">
            <div class="b2g-card__header">
                <h2 class="b2g-card__title">Status Layanan &amp; Token Akses</h2>
                <div>
                    @if ($subscription->status === 'ACTIVE' && $subscription->isActive())
                        <span class="b2g-badge b2g-badge--success">AKTIF</span>
                    @elseif ($subscription->status === 'PENDING_APPROVAL')
                        <span class="b2g-badge b2g-badge--warning">MENUNGGU APPROVAL</span>
                    @elseif ($subscription->status === 'PENDING_PAYMENT')
                        <span class="b2g-badge b2g-badge--neutral">BELUM BAYAR</span>
                    @elseif ($subscription->status === 'REVOKED')
                        <span class="b2g-badge b2g-badge--danger">REVOKED</span>
                    @elseif ($subscription->status === 'EXPIRED' || ($subscription->expires_at && $subscription->expires_at->isPast()))
                        <span class="b2g-badge b2g-badge--danger">KEDALUWARSA</span>
                    @else
                        <span class="b2g-badge b2g-badge--neutral">{{ $subscription->status }}</span>
                    @endif
                </div>
            </div>
            <div style="padding:24px;">
                <dl class="b2g-info-list">
                    <div class="b2g-info-row">
                        <dt class="b2g-info-dt">Paket Layanan</dt>
                        <dd class="b2g-info-dd" style="font-weight:700; color:#0f172a;">{{ $subscription->plan_name }} ({{ $subscription->plan_days }} Hari)</dd>
                    </div>
                    <div>
                        <dt class="b2g-info-dt" style="margin-bottom:6px;">Hak Akses Paket</dt>
                        <dd class="b2g-info-box">
                            <div style="font-weight:800;color:#064e3b;margin-bottom:8px;">{{ $plan['billing_label'] ?? 'Tagihan sesuai paket yang disetujui' }}</div>
                            <ul style="margin:0;padding-left:18px;color:#475569;font-size:12.5px;line-height:1.7;">
                                @foreach (($plan['features'] ?? []) as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            </ul>
                        </dd>
                    </div>
                    <div class="b2g-info-row">
                        <dt class="b2g-info-dt">Biaya Akses Langganan</dt>
                        <dd class="b2g-info-dd" style="font-size:16px; font-weight:900; color:#166534;">Rp {{ number_format($subscription->amount, 0, ',', '.') }}</dd>
                    </div>
                    <div class="b2g-info-row">
                        <dt class="b2g-info-dt">Token Akses 6 Digit (Preview)</dt>
                        <dd class="b2g-info-dd">
                            @if ($subscription->token_preview)
                                <code class="b2g-code" style="font-size:14px; letter-spacing:0.1em;">{{ $subscription->token_preview }}</code>
                            @else
                                <span style="font-size:12.5px; color:#94a3b8;">Belum di-generate</span>
                            @endif
                        </dd>
                    </div>
                    <div class="b2g-info-row">
                        <dt class="b2g-info-dt">Masa Berlaku Sisa</dt>
                        <dd class="b2g-info-dd">
                            @if ($subscription->isActive())
                                <span class="b2g-badge b2g-badge--success" style="font-size:12px;">{{ $subscription->remaining_days }} HARI TERSISA</span>
                            @else
                                <span style="font-size:12.5px; color:#94a3b8;">-</span>
                            @endif
                        </dd>
                    </div>
                    <div class="b2g-info-row">
                        <dt class="b2g-info-dt">Waktu Aktivasi</dt>
                        <dd class="b2g-info-dd" style="color:#334155;">{{ $subscription->started_at ? $subscription->started_at->format('d M Y, H:i') . ' WIB' : '-' }}</dd>
                    </div>
                    <div class="b2g-info-row">
                        <dt class="b2g-info-dt">Waktu Kedaluwarsa</dt>
                        <dd class="b2g-info-dd" style="color:#334155;">{{ $subscription->expires_at ? $subscription->expires_at->format('d M Y, H:i') . ' WIB' : '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    {{-- Card 3: Riwayat Pembayaran --}}
    <div class="b2g-card" style="margin-top:24px;">
        <div class="b2g-card__header">
            <div>
                <h2 class="b2g-card__title">Riwayat Transaksi Tagihan &amp; Pembayaran</h2>
                <p class="b2g-card__subtitle">Catatan transaksi tagihan kedinasan, konfirmasi manual WhatsApp, atau riwayat pembayaran resmi.</p>
            </div>
        </div>
        <div class="b2g-table-wrap">
            <table class="b2g-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Transaction ID</th>
                        <th>Metode Bayar</th>
                        <th>Nominal</th>
                        <th>Status Transaksi</th>
                        <th>Waktu Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subscription->payments as $payment)
                        @php
                            $method = $payment->payment_method;
                            if ($method === 'whatsapp_manual') {
                                $methodLabel = 'WhatsApp / Manual';
                            } elseif ($method) {
                                $methodLabel = strtoupper($method);
                            } else {
                                $methodLabel = 'Manual';
                            }
                        @endphp
                        <tr>
                            <td>
                                <code class="b2g-code">{{ $payment->order_id }}</code>
                            </td>
                            <td>
                                <span style="font-size:12.5px; color:#64748b; font-family:ui-monospace, monospace;">{{ $payment->transaction_id ?: 'Manual Admin' }}</span>
                            </td>
                            <td>
                                <span class="b2g-badge b2g-badge--neutral" style="font-weight:700;">{{ $methodLabel }}</span>
                            </td>
                            <td>
                                <span style="font-size:14px; font-weight:800; color:#0f172a;">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                            </td>
                            <td>
                                @if ($payment->transaction_status === 'settlement' || $payment->transaction_status === 'capture')
                                    <span class="b2g-badge b2g-badge--success">LUNAS (CONFIRMED)</span>
                                @elseif ($payment->transaction_status === 'pending')
                                    <span class="b2g-badge b2g-badge--warning">MENUNGGU BAYAR</span>
                                @else
                                    <span class="b2g-badge b2g-badge--neutral">{{ strtoupper($payment->transaction_status) }}</span>
                                @endif
                            </td>
                            <td>
                                <span style="font-size:12.5px; color:#64748b;">{{ $payment->paid_at ? $payment->paid_at->format('d M Y, H:i') . ' WIB' : ($payment->created_at ? $payment->created_at->format('d M Y, H:i') . ' WIB' : '-') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="b2g-empty-state" style="padding:32px 20px;">
                                    <p class="b2g-empty-desc">Belum ada catatan transaksi pembayaran untuk pendaftaran ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
