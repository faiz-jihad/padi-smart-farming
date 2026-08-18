<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $profile['headline'] ?? 'Katalog Pasokan Hasil Panen Komoditas Beras dan Gabah' }} — {{ $profile['business_name'] }}">
    <meta name="robots" content="{{ $isPreview ? 'noindex, nofollow' : 'index, follow' }}">

    <title>{{ $profile['business_name'] }} &mdash; Etalase Pasokan Padi</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --mp-green: #166534;
            --mp-green-dark: #14532d;
            --mp-dark: #0f172a;
            --mp-slate: #334155;
            --mp-muted: #64748b;
            --mp-border: #e2e8f0;
            --mp-bg: #f8fafc;
            --mp-white: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
            background-color: var(--mp-bg);
            color: var(--mp-dark);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .mp-container {
            max-width: 1160px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Top Bar */
        .mp-topbar {
            background-color: #0f172a;
            color: #94a3b8;
            font-size: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #1e293b;
        }

        /* Navbar */
        .mp-navbar {
            background-color: #ffffff;
            border-bottom: 1px solid var(--mp-border);
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .mp-nav-inner {
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .mp-logo-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--mp-dark);
        }

        .mp-logo-img {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid var(--mp-border);
        }

        .mp-logo-initial {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            background-color: var(--mp-green);
            color: #ffffff;
            font-weight: 800;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Buttons */
        .mp-btn-green {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: var(--mp-green);
            color: #ffffff !important;
            font-size: 13px;
            font-weight: 700;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            border: 1px solid var(--mp-green);
            transition: all 0.15s ease;
        }

        .mp-btn-green:hover {
            background-color: var(--mp-green-dark);
            border-color: var(--mp-green-dark);
        }

        .mp-btn-wa {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: #16a34a;
            color: #ffffff !important;
            font-size: 13px;
            font-weight: 700;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .mp-btn-wa:hover {
            background-color: #15803d;
        }

        /* Supplier Card */
        .mp-supplier-card {
            background: #ffffff;
            border: 1px solid var(--mp-border);
            border-radius: 14px;
            overflow: hidden;
            margin-top: 32px;
            margin-bottom: 40px;
        }

        .mp-cover-banner {
            height: 220px;
            background: #0f172a;
            position: relative;
            overflow: hidden;
        }

        .mp-cover-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.9;
        }

        .mp-supplier-info {
            padding: 24px 32px 32px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 24px;
        }

        /* Product Cards */
        .mp-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }

        .mp-card {
            background: #ffffff;
            border: 1px solid var(--mp-border);
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .mp-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.05);
        }
    </style>
</head>
<body>

    {{-- Preview Bar --}}
    @if ($isPreview)
        <div style="background-color:#166534; color:#ffffff; padding:10px 16px; font-size:13px; font-weight:700; text-align:center; position:sticky; top:0; z-index:999; display:flex; align-items:center; justify-content:center; gap:16px;">
            <span>Mode Preview Publikasi &mdash; Tampilan resmi etalase pasokan</span>
            <a href="{{ route('farmer.website.index') }}" style="background:#ffffff; color:#166534; padding:4px 12px; border-radius:6px; font-size:12px; text-decoration:none; font-weight:700;">
                Kembali ke Panel
            </a>
        </div>
    @endif

    {{-- Top Bar --}}
    <div class="mp-topbar">
        <div class="mp-container" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
            <div>
                <span>Pusat Pasokan Komoditas Padi Resmi</span>
                @if ($sections['show_location'] && !empty($location['address']))
                    <span> &bull; Wilayah Distribusi: {{ $location['address'] }}</span>
                @endif
            </div>
            @if ($sections['show_contact'] && !empty($contact['public_phone']))
                <div>
                    <span>Hubungi Sales: <strong>{{ $contact['public_phone'] }}</strong></span>
                </div>
            @endif
        </div>
    </div>

    {{-- Navbar --}}
    <header class="mp-navbar">
        <div class="mp-container">
            <div class="mp-nav-inner">
                <a href="#hero" class="mp-logo-wrap">
                    @if ($profile['logo_url'])
                        <img src="{{ $profile['logo_url'] }}" alt="{{ $profile['business_name'] }}" class="mp-logo-img">
                    @else
                        <div class="mp-logo-initial">
                            {{ substr($profile['business_name'], 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <h2 style="font-size:17px; font-weight:800; color:#0f172a; margin:0; line-height:1.2;">{{ $profile['business_name'] }}</h2>
                        <p style="font-size:11px; color:#64748b; margin:2px 0 0; font-weight:500;">Pemasok & Distributor Komoditas Padi</p>
                    </div>
                </a>

                <div style="display:flex; align-items:center; gap:20px;">
                    <a href="#katalog" style="color:#334155; font-size:13px; font-weight:600; text-decoration:none;">Daftar Komoditas</a>
                    @if ($sections['show_contact'] && !empty($contact['whatsapp']))
                        <a href="{{ $contact['whatsapp'] }}" target="_blank" class="mp-btn-wa">
                            Pesan via WhatsApp
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </header>

    {{-- Container --}}
    <main class="mp-container" style="padding-bottom:64px;">

        {{-- Supplier Hero Card --}}
        <div id="hero" class="mp-supplier-card">
            <div class="mp-cover-banner">
                <img src="{{ $profile['cover_image_url'] }}" alt="{{ $profile['business_name'] }}">
            </div>

            <div class="mp-supplier-info">
                <div style="max-width:700px;">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px; flex-wrap:wrap;">
                        <span style="display:inline-flex; align-items:center; gap:4px; background:#dcfce7; color:#166534; font-size:11px; font-weight:700; padding:4px 10px; border-radius:6px; border:1px solid #86efac;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            Pemasok Terverifikasi
                        </span>
                        @if ($sections['show_location'] && !empty($location['address']))
                            <span style="font-size:12px; color:#64748b;">📍 {{ $location['address'] }}</span>
                        @endif
                    </div>

                    <h1 style="font-size:26px; font-weight:900; color:#0f172a; margin:0 0 8px 0; letter-spacing:-0.02em;">
                        {{ $profile['business_name'] }}
                    </h1>

                    @if ($profile['headline'])
                        <p style="font-size:15px; font-weight:600; color:#166534; margin:0 0 12px 0;">
                            {{ $profile['headline'] }}
                        </p>
                    @endif

                    @if ($profile['description'])
                        <p style="font-size:13px; color:#64748b; line-height:1.7; margin:0;">
                            {{ $profile['description'] }}
                        </p>
                    @endif
                </div>

                @if ($sections['show_productivity'] && $statistics)
                    <div style="background:#f8fafc; border:1px solid var(--mp-border); border-radius:10px; padding:18px 24px; min-width:200px;">
                        <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Kapasitas Lahan</div>
                        <div style="font-size:24px; font-weight:900; color:#166534; margin:4px 0;">{{ $statistics['total_area_ha'] }} Ha</div>
                        <div style="font-size:12px; color:#94a3b8;">{{ $statistics['total_seasons'] }} Musim Terdata</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Products Grid --}}
        @if ($sections['show_products'] && count($products) > 0)
            <section id="katalog" style="margin-bottom:48px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
                    <div>
                        <h2 style="font-size:20px; font-weight:800; color:#0f172a; margin:0;">Daftar Hasil Panen Siap Pasok</h2>
                        <p style="font-size:13px; color:#64748b; margin:2px 0 0;">Kuantitas dan harga transparan langsung dari gudang / sawah petani.</p>
                    </div>
                    <span style="font-size:12px; font-weight:700; color:#166534; background:#dcfce7; padding:4px 12px; border-radius:6px;">
                        {{ count($products) }} Komoditas
                    </span>
                </div>

                <div class="mp-grid">
                    @foreach ($products as $product)
                        <div class="mp-card">
                            <div>
                                <div style="height:190px; background:#f1f5f9; position:relative; overflow:hidden;">
                                    <img src="{{ $product['image_url'] }}" alt="{{ $product['commodity'] }}" style="width:100%; height:100%; object-fit:cover;">
                                    <div style="position:absolute; top:10px; right:10px; background:#0f172a; color:#ffffff; font-size:11px; font-weight:700; padding:3px 8px; border-radius:4px;">
                                        Tersedia: {{ $product['quantity'] }} {{ $product['unit'] }}
                                    </div>
                                </div>

                                <div style="padding:20px;">
                                    <h3 style="font-size:16px; font-weight:800; color:#0f172a; margin:0 0 6px 0;">{{ $product['commodity'] }}</h3>
                                    
                                    @if ($product['price_per_unit'])
                                        <div style="font-size:20px; font-weight:900; color:#166534; margin-bottom:8px;">
                                            Rp{{ number_format($product['price_per_unit'], 0, ',', '.') }}
                                            <span style="font-size:12px; font-weight:500; color:#64748b;">/ {{ $product['unit'] }}</span>
                                        </div>
                                    @endif

                                    @if ($product['description'])
                                        <p style="font-size:13px; color:#64748b; line-height:1.6; margin:0;">{{ $product['description'] }}</p>
                                    @endif
                                </div>
                            </div>

                            <div style="padding:0 20px 20px;">
                                @if ($product['sales_link'])
                                    <a href="{{ $product['sales_link'] }}" target="_blank" rel="nofollow noopener" class="mp-btn-green" style="width:100%;">
                                        Beli Sekarang
                                    </a>
                                @elseif ($sections['show_contact'] && !empty($contact['whatsapp']))
                                    @php
                                        $waMsg = urlencode("Halo {$profile['business_name']}, saya ingin menanyakan ketersediaan pasokan {$product['commodity']}.");
                                        $waUrl = $contact['whatsapp'] . (str_contains($contact['whatsapp'], '?') ? '&' : '?') . "text={$waMsg}";
                                    @endphp
                                    <a href="{{ $waUrl }}" target="_blank" class="mp-btn-wa" style="width:100%;">
                                        Pesan via WhatsApp
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Contact Box --}}
        @if ($sections['show_contact'] && $contact)
            <section id="kontak">
                <div style="background:#ffffff; border:1px solid var(--mp-border); border-radius:12px; padding:32px; text-align:center;">
                    <h2 style="font-size:22px; font-weight:900; color:#0f172a; margin:0 0 8px 0;">Kontak Langsung Pemasok</h2>
                    <p style="font-size:13px; color:#64748b; margin:0 0 20px 0;">Hubungi kami untuk negosiasi kuantitas besar, sampel beras, atau jadwal pengiriman rutin.</p>
                    
                    <div style="display:flex; align-items:center; justify-content:center; gap:12px; flex-wrap:wrap;">
                        @if (!empty($contact['whatsapp']))
                            <a href="{{ $contact['whatsapp'] }}" target="_blank" class="mp-btn-wa" style="padding:10px 24px;">
                                WhatsApp Langsung
                            </a>
                        @endif
                        @if (!empty($contact['public_phone']))
                            <a href="tel:{{ $contact['public_phone'] }}" class="mp-btn-green" style="background:#ffffff; color:#0f172a !important; border-color:#cbd5e1; padding:10px 24px;">
                                Telepon: {{ $contact['public_phone'] }}
                            </a>
                        @endif
                    </div>
                </div>
            </section>
        @endif

    </main>

    {{-- Footer --}}
    <footer style="background:#0f172a; color:#94a3b8; font-size:12px; padding:24px 0; text-align:center; border-top:1px solid #1e293b;">
        <div class="mp-container">
            <div style="font-weight:700; color:#ffffff; font-size:13px; margin-bottom:4px;">{{ $profile['business_name'] }}</div>
            <p style="margin:0; color:#64748b;">Etalase resmi komoditas pertanian P.A.D.I.</p>
            <div style="margin-top:8px; font-size:11px; color:#475569;">
                &copy; {{ date('Y') }} {{ $profile['business_name'] }}. Seluruh hak cipta dilindungi.
            </div>
        </div>
    </footer>

</body>
</html>
