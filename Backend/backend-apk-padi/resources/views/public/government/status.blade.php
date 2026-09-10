<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - P.A.D.I. B2G</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F8FAF8;
            color: #111827;
            margin: 0;
            padding: 0;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            box-sizing: border-box;
            background-color: #16A34A;
            color: #ffffff;
            font-weight: 700;
            border-radius: 0.75rem;
            padding: 0.875rem 1.5rem;
            font-size: 0.875rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            box-shadow: 0 8px 20px -4px rgba(22, 163, 74, 0.4);
            transition: all 0.2s ease;
        }
        .btn-action:hover {
            background-color: #15803D;
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between">

    <header style="width: 100%; padding: 1.5rem 1rem; box-sizing: border-box;">
        <div style="max-width: 56rem; margin: 0 auto; display: flex; align-items: center; justify-content: space-between;">
            <a href="{{ route('government.index') }}" style="display: flex; align-items: center; gap: 0.65rem; text-decoration: none;">
                <div style="width: 2.25rem; height: 2.25rem; border-radius: 9999px; background-color: #DCFCE7; display: flex; align-items: center; justify-content: center; color: #16A34A; font-weight: 700;">
                    🌾
                </div>
                <span style="font-size: 1.25rem; font-weight: 900; letter-spacing: -0.025em; color: #030712;">P.A.D.I.</span>
                <span style="font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.65rem; border-radius: 9999px; background-color: #DCFCE7; color: #166534; border: 1px solid #BBF7D0;">Status Langganan B2G</span>
            </a>
            <a href="{{ route('government.index') }}" style="font-size: 0.75rem; font-weight: 600; color: #6B7280; text-decoration: none;" onmouseover="this.style.color='#111827'" onmouseout="this.style.color='#6B7280'">&larr; Kembali ke Portal B2G</a>
        </div>
    </header>

    <main style="max-width: 36rem; margin: 1.5rem auto; width: 100%; padding: 0 1rem; box-sizing: border-box;">
        <div style="background: #ffffff; border-radius: 2rem; padding: 2.5rem; border: 1px solid #E5E7EB; box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.08); text-align: center;">

            @php
                $payment = $subscription->latestPayment;
                $isPaid = in_array($payment?->transaction_status, ['settlement', 'capture']);
            @endphp

            @if ($subscription->isActive())
                {{-- Active State --}}
                <div style="width: 4rem; height: 4rem; border-radius: 1.25rem; background-color: #ECFDF5; color: #16A34A; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; border: 1px solid #A7F3D0; box-shadow: 0 8px 20px rgba(22, 163, 74, 0.15);">
                    <svg style="width: 2rem; height: 2rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <h1 style="font-size: 1.5rem; font-weight: 900; color: #030712; margin-bottom: 0.5rem;">Langganan B2G Telah Aktif</h1>
                <p style="font-size: 0.8rem; color: #4B5563; margin-bottom: 1.5rem;">Token otentikasi API instansi Anda telah diverifikasi dan diterbitkan. Masa aktif berlaku hingga <strong style="color: #111827;">{{ $subscription->expires_at?->translatedFormat('d F Y') }}</strong>.</p>

                <div style="background-color: #071910; color: #ffffff; border-radius: 1.25rem; padding: 1.5rem; text-align: left; margin-bottom: 1.5rem; border: 1px solid rgba(16, 185, 129, 0.3);">
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #94A3B8; margin-bottom: 0.5rem;">
                        <span>Instansi:</span>
                        <span style="font-weight: 700; color: #ffffff;">{{ $subscription->agency_name }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #94A3B8; margin-bottom: 0.75rem;">
                        <span>Masa Berlaku:</span>
                        <span style="font-weight: 700; color: #34D399;">{{ $subscription->remaining_days }} Hari Tersisa</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; color: #94A3B8; border-top: 1px solid #0F2E1E; padding-top: 0.75rem;">
                        <span>Format Header API:</span>
                        <code style="font-family: monospace; color: #34D399; font-weight: 700; font-size: 0.8rem; background-color: rgba(6, 78, 59, 0.6); padding: 0.25rem 0.65rem; border-radius: 0.35rem; border: 1px solid rgba(5, 150, 105, 0.4);">Bearer {{ $subscription->token_preview }}</code>
                    </div>
                </div>

                <a href="{{ route('government.docs') }}" class="btn-action">
                    <span>Buka Panduan Akses B2G API</span>
                    <svg style="width: 1rem; height: 1rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>

            @elseif ($isPaid || $subscription->status === 'PENDING_APPROVAL')
                {{-- Pending Approval State --}}
                <div style="width: 4rem; height: 4rem; border-radius: 1.25rem; background-color: #FFFBEB; color: #D97706; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; border: 1px solid #FDE68A;">
                    <svg style="width: 2rem; height: 2rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <h1 style="font-size: 1.5rem; font-weight: 900; color: #030712; margin-bottom: 0.5rem;">Pembayaran Berhasil Diterima</h1>
                <div style="display: inline-block; padding: 0.35rem 0.85rem; border-radius: 9999px; background-color: #FEF3C7; border: 1px solid #FDE68A; color: #92400E; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;">
                    Menunggu Persetujuan Administrator
                </div>
                <p style="font-size: 0.8rem; color: #4B5563; line-height: 1.6; margin-bottom: 1.5rem;">
                    Konfirmasi pembayaran tagihan Rp {{ number_format($subscription->amount, 0, ',', '.') }} telah diterima secara resmi. Administrator P.A.D.I. sedang memvalidasi data instansi dan akan segera mengaktifkan Token 6 Digit Anda.
                </p>

                <div style="background-color: #F8FAFC; border-radius: 1rem; padding: 1.25rem; text-align: left; font-size: 0.75rem; border: 1px solid #E2E8F0; margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="color: #64748B;">Order ID:</span>
                        <code style="font-family: monospace; font-weight: 700; color: #0F172A;">{{ $payment?->order_id }}</code>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="color: #64748B;">Instansi:</span>
                        <span style="font-weight: 700; color: #0F172A;">{{ $subscription->agency_name }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748B;">Status Pembayaran:</span>
                        <span style="font-weight: 700; color: #15803D;">Dikonfirmasi (Lunas)</span>
                    </div>
                </div>

                <button onclick="window.location.reload();" style="width: 100%; box-sizing: border-box; padding: 0.875rem; border-radius: 0.75rem; font-weight: 700; color: #1F2937; background-color: #F3F4F6; border: 1px solid #E5E7EB; cursor: pointer; font-size: 0.875rem;">
                    Segarkan Status (Refresh)
                </button>

            @else
                {{-- Waiting Payment State --}}
                <div style="width: 4rem; height: 4rem; border-radius: 1.25rem; background-color: #F1F5F9; color: #475569; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; border: 1px solid #CBD5E1;">
                    <svg style="width: 2rem; height: 2rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
                <h1 style="font-size: 1.5rem; font-weight: 900; color: #030712; margin-bottom: 0.5rem;">Menunggu Pembayaran</h1>
                <p style="font-size: 0.8rem; color: #4B5563; margin-bottom: 1.5rem;">Pendaftaran data instansi berhasil tersimpan. Silakan hubungi Administrator P.A.D.I. via WhatsApp untuk mendapatkan instruksi pembayaran resmi tagihan Rp {{ number_format($subscription->amount, 0, ',', '.') }}.</p>

                <a href="{{ route('government.checkout', $subscription->id) }}" class="btn-action">
                    <span>Buka Instruksi Billing WhatsApp &rarr;</span>
                </a>
            @endif

        </div>
    </main>

    <footer style="text-align: center; padding: 1.5rem; font-size: 0.75rem; color: #9CA3AF;">
        P.A.D.I. Smart Farming &bull; Portal Pemerintah Business-to-Government (B2G)
    </footer>

</body>
</html>
