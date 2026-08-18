<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $profile['headline'] ?? $profile['business_name'] }} — Agritech & Smart Farming Profile by P.A.D.I.">
    <meta name="robots" content="{{ $isPreview ? 'noindex, nofollow' : 'index, follow' }}">

    <title>{{ $profile['business_name'] }} {{ $isPreview ? '(Preview Mode)' : '' }} &mdash; P.A.D.I.</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --am-dark: #0f172a;
            --am-green: #166534;
            --am-green-light: #dcfce7;
            --am-green-accent: #22c55e;
            --am-surface: #ffffff;
            --am-bg: #f8fafc;
            --am-border: #e2e8f0;
            --am-text: #0f172a;
            --am-muted: #64748b;
        }

        body {
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
            background-color: var(--am-bg);
            color: var(--am-text);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .am-navbar {
            background-color: #ffffff;
            border-bottom: 1px solid var(--am-border);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .am-hero {
            background-color: #ffffff;
            border-bottom: 1px solid var(--am-border);
            padding: 64px 24px;
        }

        .am-card {
            background: #ffffff;
            border: 1px solid var(--am-border);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            transition: all 0.25s ease;
        }

        .am-card:hover {
            border-color: #cbd5e1;
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -6px rgba(15, 23, 42, 0.08);
        }

        .am-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: var(--am-green);
            color: #ffffff !important;
            font-weight: 700;
            font-size: 14px;
            padding: 11px 22px;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid var(--am-green);
        }

        .am-btn-primary:hover {
            background-color: #14532d;
            border-color: #14532d;
            transform: translateY(-1px);
        }

        .am-btn-whatsapp {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: #16a34a;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 14px;
            padding: 11px 22px;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .am-btn-whatsapp:hover {
            background-color: #15803d;
            transform: translateY(-1px);
        }

        .am-tag {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--am-green);
            margin-bottom: 6px;
        }

        .am-title {
            font-size: 24px;
            font-weight: 800;
            color: var(--am-dark);
            letter-spacing: -0.02em;
            margin: 0;
        }

        .am-hero-image {
            width: 360px;
            height: 240px;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--am-border);
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
            flex-shrink: 0;
        }

        @media (max-width: 860px) {
            .am-hero-image {
                display: none;
            }
        }
    </style>

</head>
<body>

    {{-- Preview Bar --}}
    @if ($isPreview)
        <div style="background-color:#166534; color:#ffffff; padding:10px 16px; font-size:13px; font-weight:700; text-align:center; position:sticky; top:0; z-index:999; display:flex; align-items:center; justify-content:center; gap:16px;">
            <span>Mode Preview Publikasi &mdash; Tampilan resmi profil usaha tani Anda</span>
            <a href="{{ route('farmer.website.index') }}" style="background:#ffffff; color:#166534; padding:4px 12px; border-radius:6px; font-size:12px; text-decoration:none; font-weight:700;">
                Kembali ke Panel
            </a>
        </div>
    @endif

    {{-- Top Navbar --}}
    <header class="am-navbar" style="top:{{ $isPreview ? '41px' : '0' }};">
        <div style="max-width:1200px; margin:0 auto; padding:0 24px; height:68px; display:flex; align-items:center; justify-content:space-between;">
            <a href="#hero" style="display:flex; align-items:center; gap:12px; text-decoration:none;">
                @if ($profile['logo_url'])
                    <img src="{{ $profile['logo_url'] }}" alt="{{ $profile['business_name'] }}" style="width:36px; height:36px; border-radius:10px; object-fit:cover; border:1px solid #cbd5e1;">
                @else
                    <div style="width:36px; height:36px; border-radius:10px; background:#166534; color:#ffffff; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:15px;">
                        {{ substr($profile['business_name'], 0, 1) }}
                    </div>
                @endif
                <div>
                    <div style="font-weight:800; font-size:15px; color:#0f172a; letter-spacing:-0.01em;">{{ $profile['business_name'] }}</div>
                    <div style="font-size:11px; color:#64748b;">Mitra Pertanian Digital</div>
                </div>
            </a>

            <nav style="display:flex; align-items:center; gap:20px;">
                @if ($sections['show_products'] && count($products) > 0)
                    <a href="#katalog" style="color:#475569; font-size:13px; font-weight:600; text-decoration:none;">Katalog</a>
                @endif
                @if ($sections['show_harvests'] && count($harvests) > 0)
                    <a href="#riwayat" style="color:#475569; font-size:13px; font-weight:600; text-decoration:none;">Rekam Panen</a>
                @endif
                @if ($sections['show_gallery'] && count($gallery) > 0)
                    <a href="#dokumentasi" style="color:#475569; font-size:13px; font-weight:600; text-decoration:none;">Dokumentasi</a>
                @endif
                @if ($sections['show_contact'] && $contact)
                    <a href="#kontak" class="am-btn-primary" style="padding:8px 16px; font-size:12px;">
                        Hubungi
                    </a>
                @endif
            </nav>
        </div>
    </header>

    {{-- Hero Section --}}
    <section id="hero" class="am-hero">
        <div style="max-width:1200px; margin:0 auto; display:grid; grid-template-columns:1fr auto; gap:40px; align-items:center;">
            <div style="max-width:760px;">
                {{-- Badges --}}
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:18px; flex-wrap:wrap;">
                    @if ($profile['is_verified'])
                        <span style="display:inline-flex; align-items:center; gap:5px; background:#dcfce7; color:#166534; font-size:12px; font-weight:700; padding:4px 12px; border-radius:9999px; border:1px solid #86efac;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                                <path d="m9 12 2 2 4-4"/>
                            </svg>
                            Terverifikasi P.A.D.I.
                        </span>
                    @endif

                    @if ($sections['show_location'] && !empty($location['address']))
                        <span style="display:inline-flex; align-items:center; gap:5px; background:#f1f5f9; color:#475569; font-size:12px; font-weight:600; padding:4px 12px; border-radius:9999px; border:1px solid #e2e8f0;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0Z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            {{ $location['address'] }}
                        </span>
                    @endif
                </div>

                <h1 style="font-size:clamp(28px, 4.5vw, 44px); font-weight:900; color:#0f172a; letter-spacing:-0.03em; line-height:1.2; margin:0 0 12px 0;">
                    {{ $profile['business_name'] }}
                </h1>

                @if ($profile['headline'])
                    <p style="font-size:17px; font-weight:500; color:#475569; margin:0 0 20px 0;">
                        {{ $profile['headline'] }}
                    </p>
                @endif

                @if ($profile['description'])
                    <p style="font-size:14px; color:#64748b; line-height:1.8; margin:0 0 28px 0;">
                        {{ $profile['description'] }}
                    </p>
                @endif

                <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                    @if ($sections['show_contact'] && !empty($contact['whatsapp']))
                        <a href="{{ $contact['whatsapp'] }}" target="_blank" class="am-btn-whatsapp">
                            Hubungi via WhatsApp
                        </a>
                    @endif
                    @if ($sections['show_products'] && count($products) > 0)
                        <a href="#katalog" class="am-btn-primary" style="background:#0f172a; border-color:#0f172a;">
                            Lihat Katalog Hasil Panen
                        </a>
                    @endif
                </div>
            </div>

            @if ($profile['cover_image_url'])
                <div class="am-hero-image">
                    <img src="{{ $profile['cover_image_url'] }}" alt="{{ $profile['business_name'] }}" style="width:100%; height:100%; object-fit:cover;">
                </div>
            @endif
        </div>
    </section>


    {{-- Metrics Strip --}}
    @if ($sections['show_productivity'] && $statistics)
        <div style="background:#ffffff; border-bottom:1px solid #e2e8f0; padding:24px;">
            <div style="max-width:1200px; margin:0 auto; display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
                <div class="am-card" style="padding:18px; text-align:center;">
                    <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b;">Luas Lahan Terdata</div>
                    <div style="font-size:28px; font-weight:900; color:#166534; margin:4px 0 0;">{{ $statistics['total_area_ha'] }} Ha</div>
                </div>

                <div class="am-card" style="padding:18px; text-align:center;">
                    <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b;">Musim Budidaya</div>
                    <div style="font-size:28px; font-weight:900; color:#166534; margin:4px 0 0;">{{ $statistics['total_seasons'] }} Musim</div>
                </div>

                @if ($statistics['latest_productivity'])
                    <div class="am-card" style="padding:18px; text-align:center;">
                        <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b;">Produktivitas Terakhir</div>
                        <div style="font-size:28px; font-weight:900; color:#166534; margin:4px 0 0;">{{ $statistics['latest_productivity'] }} Ton/Ha</div>
                    </div>
                @endif

                <div class="am-card" style="padding:18px; text-align:center;">
                    <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b;">Status Mitra</div>
                    <div style="font-size:18px; font-weight:900; color:#166534; margin:10px 0 0;">Aktif & Terverifikasi</div>
                </div>
            </div>
        </div>
    @endif

    {{-- Main Container --}}
    <main style="max-width:1200px; margin:0 auto; padding:48px 24px;" class="space-y-12">

        {{-- Products --}}
        @if ($sections['show_products'] && count($products) > 0)
            <section id="katalog">
                <div style="margin-bottom:24px; display:flex; align-items:flex-end; justify-content:space-between;">
                    <div>
                        <div class="am-tag">Etalase Komoditas</div>
                        <h2 class="am-title">Hasil Panen Siap Pesan</h2>
                    </div>
                    <span style="font-size:12px; font-weight:700; color:#166534; background:#dcfce7; padding:4px 12px; border-radius:9999px;">
                        {{ count($products) }} Listing
                    </span>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:20px;">
                    @foreach ($products as $product)
                        <div class="am-card" style="overflow:hidden; display:flex; flex-direction:column; justify-content:space-between;">
                            <div>
                                <div style="height:180px; background:#f1f5f9; position:relative; overflow:hidden;">
                                    @if ($product['image_url'])
                                        <img src="{{ $product['image_url'] }}" alt="{{ $product['commodity'] }}" style="width:100%; height:100%; object-fit:cover;">
                                    @else
                                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#94a3b8;">
                                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div style="position:absolute; top:10px; right:10px; background:#0f172a; color:#ffffff; font-size:10px; font-weight:700; padding:3px 8px; border-radius:4px;">
                                        {{ $product['quantity'] }} {{ $product['unit'] }}
                                    </div>
                                </div>

                                <div style="padding:18px;">
                                    <h3 style="font-size:16px; font-weight:800; color:#0f172a; margin:0 0 6px 0;">{{ $product['commodity'] }}</h3>
                                    @if ($product['price_per_unit'])
                                        <div style="font-size:18px; font-weight:900; color:#166534; margin-bottom:8px;">
                                            Rp{{ number_format($product['price_per_unit'], 0, ',', '.') }}
                                            <span style="font-size:12px; font-weight:500; color:#64748b;">/ {{ $product['unit'] }}</span>
                                        </div>
                                    @endif
                                    @if ($product['description'])
                                        <p style="font-size:12px; color:#64748b; margin:0; line-height:1.5;">{{ Str::limit($product['description'], 100) }}</p>
                                    @endif
                                </div>
                            </div>

                            <div style="padding:0 18px 18px;">
                                @if ($product['sales_link'])
                                    <a href="{{ $product['sales_link'] }}" target="_blank" rel="nofollow noopener" class="am-btn-primary" style="width:100%; box-sizing:border-box; padding:9px;">
                                        Beli / Pesan
                                    </a>
                                @elseif ($sections['show_contact'] && !empty($contact['whatsapp']))
                                    <a href="{{ $contact['whatsapp'] }}" target="_blank" class="am-btn-whatsapp" style="width:100%; box-sizing:border-box; padding:9px;">
                                        Inquiry via WA
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
            <section id="riwayat">
                <div style="margin-bottom:20px;">
                    <div class="am-tag">Rekam Jejak Produksi</div>
                    <h2 class="am-title">Riwayat Hasil Panen</h2>
                </div>

                <div class="am-card" style="padding:0; overflow:hidden;">
                    <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px;">
                        <thead>
                            <tr style="background:#0f172a; color:#ffffff;">
                                <th style="padding:14px 18px; font-size:11px; font-weight:700; text-transform:uppercase;">Periode</th>
                                <th style="padding:14px 18px; font-size:11px; font-weight:700; text-transform:uppercase;">Lahan</th>
                                <th style="padding:14px 18px; font-size:11px; font-weight:700; text-transform:uppercase;">Varietas</th>
                                <th style="padding:14px 18px; font-size:11px; font-weight:700; text-transform:uppercase; text-align:right;">Hasil</th>
                                <th style="padding:14px 18px; font-size:11px; font-weight:700; text-transform:uppercase; text-align:center;">Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($harvests as $i => $harvest)
                                <tr style="border-bottom:1px solid #f1f5f9; background:{{ $i % 2 === 0 ? '#ffffff' : '#f8fafc' }};">
                                    <td style="padding:14px 18px; font-weight:600;">{{ \Carbon\Carbon::parse($harvest['harvest_date'])->translatedFormat('F Y') }}</td>
                                    <td style="padding:14px 18px; color:#475569;">{{ $harvest['farm_name'] ?? 'Lahan Utama' }}</td>
                                    <td style="padding:14px 18px; font-weight:700; color:#166534;">{{ $harvest['variety_name'] ?? 'Varietas Unggul' }}</td>
                                    <td style="padding:14px 18px; text-align:right; font-weight:800;">{{ number_format($harvest['quantity'], 1, ',', '.') }} {{ $harvest['unit'] }}</td>
                                    <td style="padding:14px 18px; text-align:center;">
                                        <span style="font-size:11px; font-weight:800; padding:3px 10px; border-radius:9999px; background:#dcfce7; color:#166534;">
                                            {{ $harvest['quality_grade'] ?? 'Grade A' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        {{-- Gallery --}}
        @if ($sections['show_gallery'] && count($gallery) > 0)
            <section id="dokumentasi">
                <div style="margin-bottom:20px;">
                    <div class="am-tag">Dokumentasi Budidaya</div>
                    <h2 class="am-title">Galeri Foto Lapangan</h2>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:16px;">
                    @foreach ($gallery as $item)
                        @php
                            $imgSrc = is_array($item) ? $item['image_url'] : asset('storage/' . $item->image_path);
                            $cap = is_array($item) ? ($item['caption'] ?? null) : ($item->caption ?? null);
                        @endphp
                        <div class="am-card" style="aspect-ratio:4/3; overflow:hidden; position:relative;">
                            <img src="{{ $imgSrc }}" alt="{{ $cap ?? 'Galeri' }}" style="width:100%; height:100%; object-fit:cover;">
                            @if ($cap)
                                <div style="position:absolute; bottom:0; left:0; right:0; background:rgba(15,23,42,0.85); color:#ffffff; font-size:11px; padding:8px 12px;">
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
                <div class="am-card" style="background:#0f172a; color:#ffffff; padding:36px; text-align:center; border:none;">
                    <h2 style="font-size:24px; font-weight:800; margin:0 0 8px 0; color:#ffffff;">Hubungi {{ $profile['business_name'] }}</h2>
                    <p style="font-size:14px; color:#94a3b8; margin:0 0 24px 0;">Saluran komunikasi langsung untuk transaksi dan kerja sama pasokan panen.</p>
                    
                    <div style="display:flex; align-items:center; justify-content:center; gap:12px; flex-wrap:wrap;">
                        @if (!empty($contact['whatsapp']))
                            <a href="{{ $contact['whatsapp'] }}" target="_blank" class="am-btn-whatsapp">WhatsApp Langsung</a>
                        @endif
                        @if (!empty($contact['public_email']))
                            <a href="mailto:{{ $contact['public_email'] }}" class="am-btn-primary" style="background:#ffffff; color:#0f172a !important; border-color:#ffffff;">Kirim Email</a>
                        @endif
                        @if (!empty($contact['public_phone']))
                            <a href="tel:{{ $contact['public_phone'] }}" class="am-btn-primary" style="background:transparent; border-color:rgba(255,255,255,0.3);">{{ $contact['public_phone'] }}</a>
                        @endif
                    </div>
                </div>
            </section>
        @endif

    </main>

    {{-- Footer --}}
    <footer style="background:#ffffff; border-top:1px solid #e2e8f0; color:#64748b; font-size:12px; text-align:center; padding:24px;">
        &copy; {{ date('Y') }} {{ $profile['business_name'] }} &bull; Ditenagai oleh <strong>P.A.D.I. Smart Farming Network</strong>
    </footer>

</body>
</html>
