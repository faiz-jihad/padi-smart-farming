<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $profile['headline'] ?? 'Penghasil Beras Premium & Varietas Padi Pilihan' }} — {{ $profile['business_name'] }}">
    <meta name="robots" content="{{ $isPreview ? 'noindex, nofollow' : 'index, follow' }}">

    <title>{{ $profile['business_name'] }} &mdash; Penghasil Beras Pilihan</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --hp-green: #166534;
            --hp-green-dark: #14532d;
            --hp-dark: #0f172a;
            --hp-slate: #334155;
            --hp-muted: #64748b;
            --hp-border: #e2e8f0;
            --hp-bg: #f8fafc;
            --hp-white: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
            background-color: #ffffff;
            color: var(--hp-dark);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .container-main {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Top Bar */
        .hp-top-bar {
            background: #0f172a;
            color: #cbd5e1;
            padding: 10px 0;
            font-size: 12px;
            border-bottom: 1px solid #1e293b;
        }

        /* Header Navbar */
        .hp-navbar {
            background: #ffffff;
            border-bottom: 1px solid var(--hp-border);
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .hp-nav-container {
            height: 74px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .hp-logo-box {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--hp-dark);
        }

        .hp-logo-img {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid var(--hp-border);
        }

        .hp-logo-initial {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            background: var(--hp-green);
            color: #ffffff;
            font-weight: 800;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hp-nav-menu {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .hp-nav-link {
            color: var(--hp-slate);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.15s;
        }

        .hp-nav-link:hover {
            color: var(--hp-green);
        }

        /* Hero */
        .hp-hero {
            background-color: #f8fafc;
            border-bottom: 1px solid var(--hp-border);
            padding: 56px 0;
        }

        .hp-hero-grid {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 48px;
            align-items: center;
        }

        @media (max-width: 900px) {
            .hp-hero-grid {
                grid-template-columns: 1fr;
                gap: 32px;
            }
        }

        .hp-hero-banner {
            width: 100%;
            height: 340px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--hp-border);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
        }

        .hp-hero-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Buttons */
        .hp-btn-wa {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #166534;
            color: #ffffff !important;
            font-size: 13px;
            font-weight: 700;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.15s;
        }

        .hp-btn-wa:hover {
            background: #14532d;
        }

        .hp-btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #ffffff;
            color: var(--hp-dark) !important;
            font-size: 13px;
            font-weight: 700;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            border: 1px solid #cbd5e1;
            transition: all 0.15s;
        }

        .hp-btn-secondary:hover {
            background: #f1f5f9;
        }

        /* Products */
        .hp-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }

        .hp-product-item {
            border: 1px solid var(--hp-border);
            border-radius: 12px;
            overflow: hidden;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .hp-product-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.05);
        }

        /* Table */
        .hp-table-wrap {
            border: 1px solid var(--hp-border);
            border-radius: 12px;
            overflow: hidden;
            background: #ffffff;
        }

        .hp-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            text-align: left;
        }

        .hp-table th {
            background: #0f172a;
            color: #ffffff;
            padding: 12px 18px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .hp-table td {
            padding: 14px 18px;
            border-bottom: 1px solid #f1f5f9;
            color: var(--hp-slate);
        }

        .hp-table tr:last-child td {
            border-bottom: none;
        }
    </style>
</head>
<body>

    {{-- Preview Bar --}}
    @if ($isPreview)
        <div style="background-color:#166534; color:#ffffff; padding:10px 16px; font-size:13px; font-weight:700; text-align:center; position:sticky; top:0; z-index:999; display:flex; align-items:center; justify-content:center; gap:16px;">
            <span>Mode Preview Publikasi &mdash; Tampilan resmi profil usaha tani</span>
            <a href="{{ route('farmer.website.index') }}" style="background:#ffffff; color:#166534; padding:4px 12px; border-radius:6px; font-size:12px; text-decoration:none; font-weight:700;">
                Kembali ke Panel
            </a>
        </div>
    @endif

    {{-- Top Bar --}}
    <div class="hp-top-bar">
        <div class="container-main" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
            <div>
                <span>Sentra Produksi Padi Terdaftar</span>
                @if ($sections['show_location'] && !empty($location['address']))
                    <span> &bull; Lokasi: {{ $location['address'] }}</span>
                @endif
            </div>
            @if ($sections['show_contact'] && !empty($contact['public_phone']))
                <div>
                    <span>Kontak Usaha: <strong>{{ $contact['public_phone'] }}</strong></span>
                </div>
            @endif
        </div>
    </div>

    {{-- Navbar --}}
    <header class="hp-navbar">
        <div class="container-main">
            <div class="hp-nav-container">
                <a href="#hero" class="hp-logo-box">
                    @if ($profile['logo_url'])
                        <img src="{{ $profile['logo_url'] }}" alt="{{ $profile['business_name'] }}" class="hp-logo-img">
                    @else
                        <div class="hp-logo-initial">
                            {{ substr($profile['business_name'], 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <h2 style="font-size:17px; font-weight:800; color:#0f172a; margin:0; line-height:1.2;">{{ $profile['business_name'] }}</h2>
                        <p style="font-size:11px; color:#64748b; margin:2px 0 0; font-weight:500;">Usaha Pertanian & Penghasil Beras</p>
                    </div>
                </a>

                <nav class="hp-nav-menu">
                    @if ($sections['show_products'] && count($products) > 0)
                        <a href="#katalog" class="hp-nav-link">Katalog Beras</a>
                    @endif
                    @if ($sections['show_harvests'] && count($harvests) > 0)
                        <a href="#audit" class="hp-nav-link">Data Panen</a>
                    @endif
                    @if ($sections['show_gallery'] && count($gallery) > 0)
                        <a href="#galeri" class="hp-nav-link">Dokumentasi</a>
                    @endif
                    @if ($sections['show_contact'] && $contact)
                        <a href="#kontak" class="hp-btn-wa" style="padding:8px 16px; font-size:12px;">
                            Hubungi Kami
                        </a>
                    @endif
                </nav>
            </div>
        </div>
    </header>

    {{-- Hero --}}
    <section id="hero" class="hp-hero">
        <div class="container-main">
            <div class="hp-hero-grid">
                <div>
                    @if ($profile['is_verified'])
                        <div style="display:inline-flex; align-items:center; gap:6px; background:#dcfce7; color:#166534; font-size:12px; font-weight:700; padding:4px 10px; border-radius:6px; margin-bottom:16px; border:1px solid #86efac;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                                <path d="m9 12 2 2 4-4"/>
                            </svg>
                            Terverifikasi P.A.D.I.
                        </div>
                    @endif


                    <h1 style="font-size:clamp(28px, 4.5vw, 42px); font-weight:900; color:#0f172a; letter-spacing:-0.03em; line-height:1.2; margin:0 0 14px 0;">
                        {{ $profile['business_name'] }}
                    </h1>

                    @if ($profile['headline'])
                        <p style="font-size:17px; font-weight:600; color:#166534; margin:0 0 16px 0;">
                            {{ $profile['headline'] }}
                        </p>
                    @endif

                    @if ($profile['description'])
                        <p style="font-size:14px; color:#475569; line-height:1.75; margin:0 0 28px 0;">
                            {{ $profile['description'] }}
                        </p>
                    @else
                        <p style="font-size:14px; color:#475569; line-height:1.75; margin:0 0 28px 0;">
                            Kami berdedikasi menghasilkan komoditas beras bermutu tinggi melalui praktik budidaya padi yang higienis, teratur, dan terjaga kemurnian varietasnya. Melayani pemesanan langsung dari panen untuk kebutuhan retail dan grosir.
                        </p>
                    @endif

                    <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                        @if ($sections['show_contact'] && !empty($contact['whatsapp']))
                            <a href="{{ $contact['whatsapp'] }}" target="_blank" class="hp-btn-wa">
                                Pesan Langsung via WhatsApp
                            </a>
                        @endif
                        @if ($sections['show_products'] && count($products) > 0)
                            <a href="#katalog" class="hp-btn-secondary">
                                Lihat Produk Tersedia
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Hero Banner Image --}}
                <div class="hp-hero-banner">
                    <img src="{{ $profile['cover_image_url'] }}" alt="{{ $profile['business_name'] }}">
                </div>
            </div>
        </div>
    </section>

    {{-- Metrics --}}
    @if ($sections['show_productivity'] && $statistics)
        <div style="background:#ffffff; border-bottom:1px solid var(--hp-border); padding:24px 0;">
            <div class="container-main">
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px;">
                    <div style="background:#f8fafc; border:1px solid var(--hp-border); border-radius:10px; padding:18px 20px;">
                        <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Luas Area Sawah</div>
                        <div style="font-size:24px; font-weight:900; color:#166534; margin-top:4px;">{{ $statistics['total_area_ha'] }} Hektar</div>
                    </div>
                    <div style="background:#f8fafc; border:1px solid var(--hp-border); border-radius:10px; padding:18px 20px;">
                        <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Musim Panen Terdata</div>
                        <div style="font-size:24px; font-weight:900; color:#166534; margin-top:4px;">{{ $statistics['total_seasons'] }} Siklus</div>
                    </div>
                    @if ($statistics['latest_productivity'])
                        <div style="background:#f8fafc; border:1px solid var(--hp-border); border-radius:10px; padding:18px 20px;">
                            <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Hasil Produktivitas</div>
                            <div style="font-size:24px; font-weight:900; color:#166534; margin-top:4px;">{{ $statistics['latest_productivity'] }} Ton / Ha</div>
                        </div>
                    @endif
                    <div style="background:#f8fafc; border:1px solid var(--hp-border); border-radius:10px; padding:18px 20px;">
                        <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Mutu Komoditas</div>
                        <div style="font-size:18px; font-weight:800; color:#0f172a; margin-top:8px;">Beras Kualitas Utama</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Main Body --}}
    <main class="container-main" style="padding:48px 24px 64px;">

        {{-- Products --}}
        @if ($sections['show_products'] && count($products) > 0)
            <section id="katalog" style="margin-bottom:56px;">
                <div style="display:flex; align-items:flex-end; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
                    <div>
                        <div style="font-size:11px; font-weight:800; color:#166534; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:4px;">Katalog Penjualan</div>
                        <h2 style="font-size:22px; font-weight:900; color:#0f172a; margin:0;">Beras & Komoditas Siap Pesan</h2>
                    </div>
                    <span style="font-size:12px; font-weight:700; color:#166534; background:#dcfce7; padding:4px 12px; border-radius:6px;">
                        {{ count($products) }} Varietas Komoditas
                    </span>
                </div>

                <div class="hp-card-grid">
                    @foreach ($products as $product)
                        <div class="hp-product-item">
                            <div>
                                <div style="height:200px; background:#f1f5f9; position:relative; overflow:hidden;">
                                    <img src="{{ $product['image_url'] }}" alt="{{ $product['commodity'] }}" style="width:100%; height:100%; object-fit:cover;">
                                    <div style="position:absolute; top:10px; right:10px; background:#0f172a; color:#ffffff; font-size:11px; font-weight:700; padding:3px 8px; border-radius:4px;">
                                        Stok: {{ $product['quantity'] }} {{ $product['unit'] }}
                                    </div>
                                </div>

                                <div style="padding:20px;">
                                    <h3 style="font-size:16px; font-weight:800; color:#0f172a; margin:0 0 6px 0;">{{ $product['commodity'] }}</h3>
                                    
                                    @if ($product['price_per_unit'])
                                        <div style="font-size:20px; font-weight:900; color:#166534; margin-bottom:10px;">
                                            Rp{{ number_format($product['price_per_unit'], 0, ',', '.') }}
                                            <span style="font-size:13px; font-weight:500; color:#64748b;">/ {{ $product['unit'] }}</span>
                                        </div>
                                    @endif

                                    @if ($product['description'])
                                        <p style="font-size:13px; color:#64748b; line-height:1.6; margin:0;">{{ $product['description'] }}</p>
                                    @endif
                                </div>
                            </div>

                            <div style="padding:0 20px 20px;">
                                @if ($product['sales_link'])
                                    <a href="{{ $product['sales_link'] }}" target="_blank" rel="nofollow noopener" class="hp-btn-wa" style="width:100%;">
                                        Pesan Sekarang
                                    </a>
                                @elseif ($sections['show_contact'] && !empty($contact['whatsapp']))
                                    @php
                                        $waMsg = urlencode("Halo {$profile['business_name']}, saya tertarik dengan komoditas {$product['commodity']}. Apakah stok masih tersedia?");
                                        $waUrl = $contact['whatsapp'] . (str_contains($contact['whatsapp'], '?') ? '&' : '?') . "text={$waMsg}";
                                    @endphp
                                    <a href="{{ $waUrl }}" target="_blank" class="hp-btn-wa" style="width:100%;">
                                        Tanya via WhatsApp
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Harvest Log --}}
        @if ($sections['show_harvests'] && count($harvests) > 0)
            <section id="audit" style="margin-bottom:56px;">
                <div style="margin-bottom:20px;">
                    <div style="font-size:11px; font-weight:800; color:#166534; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:4px;">Transparansi Panen</div>
                    <h2 style="font-size:22px; font-weight:900; color:#0f172a; margin:0;">Rekam Jejak Panen Berkala</h2>
                </div>

                <div class="hp-table-wrap">
                    <div style="overflow-x:auto;">
                        <table class="hp-table">
                            <thead>
                                <tr>
                                    <th>Waktu Panen</th>
                                    <th>Lokasi Lahan</th>
                                    <th>Varietas Bibit</th>
                                    <th style="text-align:right;">Jumlah Panen</th>
                                    <th style="text-align:center;">Tingkat Mutu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($harvests as $harvest)
                                    <tr>
                                        <td style="font-weight:700; color:#0f172a;">{{ \Carbon\Carbon::parse($harvest['harvest_date'])->translatedFormat('F Y') }}</td>
                                        <td>{{ $harvest['farm_name'] ?? 'Lahan Utama' }}</td>
                                        <td style="font-weight:600; color:#166534;">{{ $harvest['variety_name'] ?? 'Varietas Padi' }}</td>
                                        <td style="text-align:right; font-weight:800; color:#0f172a;">{{ number_format($harvest['quantity'], 1, ',', '.') }} {{ $harvest['unit'] }}</td>
                                        <td style="text-align:center;">
                                            <span style="font-size:11px; font-weight:800; padding:3px 10px; border-radius:6px; background:#dcfce7; color:#166534;">
                                                {{ $harvest['quality_grade'] ?? 'Grade A' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif

        {{-- Gallery --}}
        @if ($sections['show_gallery'] && count($gallery) > 0)
            <section id="galeri" style="margin-bottom:56px;">
                <div style="margin-bottom:20px;">
                    <div style="font-size:11px; font-weight:800; color:#166534; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:4px;">Galeri Lapangan</div>
                    <h2 style="font-size:22px; font-weight:900; color:#0f172a; margin:0;">Dokumentasi Lahan & Panen</h2>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:18px;">
                    @foreach ($gallery as $item)
                        @php
                            $imgSrc = is_array($item) ? $item['image_url'] : asset('storage/' . $item->image_path);
                            $cap = is_array($item) ? ($item['caption'] ?? null) : ($item->caption ?? null);
                        @endphp
                        <div style="border:1px solid var(--hp-border); border-radius:10px; overflow:hidden; aspect-ratio:4/3; position:relative;">
                            <img src="{{ $imgSrc }}" alt="{{ $cap ?? 'Galeri Foto' }}" style="width:100%; height:100%; object-fit:cover;">
                            @if ($cap)
                                <div style="position:absolute; bottom:0; left:0; right:0; background:rgba(15,23,42,0.85); color:#ffffff; font-size:11px; font-weight:600; padding:8px 12px;">
                                    {{ $cap }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Contact --}}
        @if ($sections['show_contact'] && $contact)
            <section id="kontak">
                <div style="background:#f8fafc; border:1px solid var(--hp-border); border-radius:14px; padding:36px; text-align:center;">
                    <div style="max-width:640px; margin:0 auto;">
                        <h2 style="font-size:24px; font-weight:900; color:#0f172a; margin:0 0 8px 0;">
                            Pemesanan & Informasi Pasokan
                        </h2>
                        <p style="font-size:14px; color:#64748b; margin:0 0 24px 0;">
                            Hubungi kami langsung untuk kebutuhan pasokan beras, pembelian partai besar, atau informasi panen mendatang.
                        </p>

                        <div style="display:flex; align-items:center; justify-content:center; gap:12px; flex-wrap:wrap; margin-bottom:20px;">
                            @if (!empty($contact['whatsapp']))
                                <a href="{{ $contact['whatsapp'] }}" target="_blank" class="hp-btn-wa">
                                    Hubungi via WhatsApp
                                </a>
                            @endif
                            @if (!empty($contact['public_phone']))
                                <a href="tel:{{ $contact['public_phone'] }}" class="hp-btn-secondary">
                                    Telepon: {{ $contact['public_phone'] }}
                                </a>
                            @endif
                        </div>

                        @if ($sections['show_location'] && !empty($location['address']))
                            <div style="font-size:13px; color:#64748b; border-top:1px solid var(--hp-border); padding-top:16px;">
                                📍 <strong>Alamat Sawah & Gudang:</strong> {{ $location['address'] }}
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        @endif

    </main>

    {{-- Footer --}}
    <footer style="background:#0f172a; color:#94a3b8; font-size:12px; padding:28px 0; text-align:center; border-top:1px solid #1e293b;">
        <div class="container-main">
            <div style="font-weight:700; color:#ffffff; font-size:14px; margin-bottom:4px;">{{ $profile['business_name'] }}</div>
            <p style="margin:0; color:#64748b;">Halaman profil usaha tani terverifikasi &mdash; Platform Pertanian P.A.D.I.</p>
            <div style="margin-top:10px; font-size:11px; color:#475569;">
                &copy; {{ date('Y') }} {{ $profile['business_name'] }}. Hak cipta dilindungi.
            </div>
        </div>
    </footer>

</body>
</html>
