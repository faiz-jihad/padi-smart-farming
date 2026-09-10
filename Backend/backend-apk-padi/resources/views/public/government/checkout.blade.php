<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
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
        .btn-whatsapp {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            width: 100%;
            background-color: #25D366;
            color: #ffffff;
            font-weight: 800;
            border-radius: 0.875rem;
            padding: 1.1rem 1.5rem;
            font-size: 1.05rem;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 10px 25px -5px rgba(37, 211, 102, 0.4);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-sizing: border-box;
        }
        .btn-whatsapp:hover {
            background-color: #1ebd58;
            transform: translateY(-2px);
            box-shadow: 0 14px 28px -5px rgba(37, 211, 102, 0.5);
            color: #ffffff;
        }
        .btn-secondary-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.85rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 700;
            color: #475569;
            background-color: #F1F5F9;
            border: 1px solid #E2E8F0;
            text-decoration: none;
            transition: all 0.15s ease;
            box-sizing: border-box;
        }
        .btn-secondary-link:hover {
            background-color: #E2E8F0;
            color: #0F172A;
        }
        .flow-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.65rem;
            margin-bottom: 1.5rem;
        }
        .flow-step {
            border: 1px solid #D1FAE5;
            background: #F0FDF4;
            border-radius: 0.95rem;
            padding: 0.85rem;
            font-size: 0.72rem;
            line-height: 1.45;
            color: #166534;
            font-weight: 700;
        }
        .flow-step strong {
            display: block;
            color: #064E3B;
            font-size: 0.76rem;
            margin-bottom: 0.2rem;
        }
        .access-list {
            margin: 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 0.55rem;
        }
        .access-list li {
            display: grid;
            grid-template-columns: 1rem 1fr;
            gap: 0.45rem;
            font-size: 0.78rem;
            color: #475569;
            line-height: 1.5;
        }
        .access-list li:before {
            content: '✓';
            color: #16A34A;
            font-weight: 900;
        }
        @media (max-width: 640px) {
            .flow-grid {
                grid-template-columns: 1fr;
            }
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
                <span style="font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.65rem; border-radius: 9999px; background-color: #DCFCE7; color: #166534; border: 1px solid #BBF7D0;">Billing Resmi B2G</span>
            </a>
            <a href="{{ route('government.index') }}" style="font-size: 0.75rem; font-weight: 600; color: #6B7280; text-decoration: none;" onmouseover="this.style.color='#111827'" onmouseout="this.style.color='#6B7280'">&larr; Kembali ke Portal B2G</a>
        </div>
    </header>

    <main style="max-width: 38rem; margin: 1.5rem auto; width: 100%; padding: 0 1rem; box-sizing: border-box;">
        <div style="background: #ffffff; border-radius: 2rem; padding: 2.5rem; border: 1px solid #E5E7EB; box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.08);">
            
            {{-- Header Title --}}
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="width: 3.75rem; height: 3.75rem; border-radius: 1.25rem; background-color: #DCFCE7; color: #16A34A; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.85rem;">
                    <svg style="width: 2rem; height: 2rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                </div>
                <h1 style="font-size: 1.5rem; font-weight: 900; color: #030712; margin: 0;">Instruksi Billing &amp; Konfirmasi Pembayaran</h1>
                <p style="font-size: 0.8rem; color: #64748B; margin-top: 0.35rem;">ID Registrasi: <code style="font-family: monospace; font-weight: 700; color: #0F172A; padding: 0.15rem 0.5rem; border-radius: 0.35rem; background-color: #F1F5F9;">#{{ $subscription->id }}</code> &bull; Kode Tagihan: <code style="font-family: monospace; font-weight: 700; color: #0F172A; padding: 0.15rem 0.5rem; border-radius: 0.35rem; background-color: #F1F5F9;">{{ $payment?->order_id ?: ('B2G-WA-' . $subscription->id) }}</code></p>
            </div>

            @if (session('status'))
                <div style="margin-bottom: 1.5rem; padding: 1rem 1.25rem; border-radius: 0.875rem; background-color: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; font-size: 0.8rem; font-weight: 600; line-height: 1.5;">
                    {{ session('status') }}
                </div>
            @endif

            <div class="flow-grid">
                <div class="flow-step"><strong>1. Registrasi</strong>Data instansi sudah masuk.</div>
                <div class="flow-step"><strong>2. Billing</strong>PIC menghubungi admin resmi.</div>
                <div class="flow-step"><strong>3. Verifikasi</strong>Admin cek pembayaran dan instansi.</div>
                <div class="flow-step"><strong>4. Akses Aktif</strong>Token API diberikan ke instansi.</div>
            </div>

            {{-- Summary Box --}}
            <div style="background-color: #F8FAFC; border-radius: 1.25rem; padding: 1.5rem; border: 1px solid #E2E8F0; margin-bottom: 1.5rem; font-size: 0.875rem;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.85rem; padding-bottom: 0.85rem; border-bottom: 1px solid #F1F5F9;">
                    <span style="color: #64748B;">Nama Instansi:</span>
                    <span style="font-weight: 800; color: #0F172A; text-align: right; max-width: 60%;">{{ $subscription->agency_name }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <span style="color: #64748B;">Paket Langganan:</span>
                    <span style="font-weight: 700; color: #0F172A;">{{ $subscription->plan_name }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem; gap: 1rem;">
                    <span style="color: #64748B;">Skema Tagihan:</span>
                    <span style="font-weight: 700; color: #0F172A; text-align: right;">{{ $plan['billing_label'] ?? 'Tagihan sesuai paket' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <span style="color: #64748B;">Durasi Akses:</span>
                    <span style="font-weight: 700; color: #0F172A;">{{ $subscription->plan_days }} Hari Kalender</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <span style="color: #64748B;">Nama PIC:</span>
                    <span style="font-weight: 600; color: #334155;">{{ $subscription->pic_name }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <span style="color: #64748B;">Nomor WhatsApp PIC:</span>
                    <span style="font-weight: 600; color: #334155;">{{ $subscription->pic_phone }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <span style="color: #64748B;">Email Resmi:</span>
                    <span style="font-weight: 600; color: #334155;">{{ $subscription->agency_email }}</span>
                </div>
                <div style="border-top: 1px solid #CBD5E1; padding-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 800; color: #0F172A; font-size: 0.95rem;">Total Tagihan:</span>
                    <span style="font-size: 1.6rem; font-weight: 900; color: #15803D; letter-spacing: -0.02em;">Rp {{ number_format($subscription->amount, 0, ',', '.') }}</span>
                </div>
            </div>

            <div style="background-color: #ffffff; border: 1px solid #D1FAE5; border-radius: 1.25rem; padding: 1.25rem; margin-bottom: 1.5rem;">
                <h3 style="margin: 0 0 0.75rem 0; font-size: 0.95rem; font-weight: 900; color: #064E3B;">Hak akses setelah admin menyetujui</h3>
                <ul class="access-list">
                    @foreach (($plan['features'] ?? []) as $feature)
                        <li>{{ $feature }}</li>
                    @endforeach
                    <li>Instansi mendapat halaman status dan dokumentasi untuk memakai endpoint B2G.</li>
                </ul>
            </div>

            {{-- WhatsApp Instruction Box --}}
            <div style="background-color: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 1rem; padding: 1.25rem 1.5rem; margin-bottom: 1.75rem;">
                <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                    <span style="font-size: 1.25rem;">💬</span>
                    <div>
                        <h4 style="margin: 0 0 0.25rem 0; font-size: 0.85rem; font-weight: 800; color: #166534;">Pembayaran Dikonfirmasi Melalui WhatsApp</h4>
                        <p style="margin: 0; font-size: 0.775rem; color: #15803D; line-height: 1.55;">
                            Untuk mempermudah administrasi kedinasan, konfirmasi tagihan dan instruksi nomor rekening pembayaran dilakukan langsung via WhatsApp Administrator P.A.D.I. Klik tombol di bawah untuk mengirim pesan otomatis.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                <a href="{{ $whatsappUrl }}" target="_blank" class="btn-whatsapp">
                    <svg style="width: 1.4rem; height: 1.4rem;" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                    </svg>
                    <span>Hubungi Admin via WhatsApp</span>
                </a>

                <a href="{{ route('government.status', $subscription->id) }}" class="btn-secondary-link">
                    <span>Lihat Status Pendaftaran &amp; Token &rarr;</span>
                </a>
            </div>
        </div>
    </main>

    <footer style="text-align: center; padding: 1.5rem; font-size: 0.75rem; color: #9CA3AF;">
        P.A.D.I. Smart Farming &bull; Saluran Resmi Akses Data Pertanian Pemerintah (B2G)
    </footer>

</body>
</html>
