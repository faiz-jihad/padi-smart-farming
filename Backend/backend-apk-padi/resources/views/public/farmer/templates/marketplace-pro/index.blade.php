<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $profile['headline'] ?? $profile['business_name'] }} — B2B Agricultural Marketplace & Direct Wholesale by P.A.D.I.">
    <meta name="robots" content="{{ $isPreview ? 'noindex, nofollow' : 'index, follow' }}">

    <title>{{ $profile['business_name'] }} {{ $isPreview ? '(Preview Mode)' : '' }} &mdash; P.A.D.I.</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --mp-dark: #0f172a;
            --mp-green: #166534;
            --mp-green-light: #dcfce7;
            --mp-bg: #f8fafc;
            --mp-white: #ffffff;
            --mp-border: #e2e8f0;
            --mp-text: #0f172a;
            --mp-muted: #64748b;
        }

        body {
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
            background-color: var(--mp-bg);
            color: var(--mp-text);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .mp-topbar {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 12px;
            padding: 8px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .mp-header {
            background-color: #ffffff;
            border-bottom: 1px solid var(--mp-border);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        .mp-product-card {
            background: #ffffff;
            border: 1px solid var(--mp-border);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.25s ease;
        }

        .mp-product-card:hover {
            border-color: var(--mp-green);
            transform: translateY(-3px);
            box-shadow: 0 14px 28px -6px rgba(22, 101, 52, 0.12);
        }

        .mp-btn-buy {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background-color: var(--mp-green);
            color: #ffffff !important;
            font-weight: 700;
            font-size: 13px;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
            width: 100%;
            box-sizing: border-box;
        }

        .mp-btn-buy:hover {
            background-color: #14532d;
            transform: translateY(-1px);
        }

        .mp-btn-wa {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background-color: #16a34a;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 13px;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
            width: 100%;
            box-sizing: border-box;
        }

        .mp-btn-wa:hover {
            background-color: #15803d;
            transform: translateY(-1px);
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

    {{-- Top Announcement Bar --}}
    <div class="mp-topbar">
        <div style="display:flex; align-items:center; gap:8px;">
            <span style="background:#166534; padding:2px 8px; border-radius:4px; font-weight:700;">B2B Verified</span>
            <span>Etalase Komoditas Hasil Panen Langsung dari Petani Terverifikasi P.A.D.I.</span>
        </div>
        @if ($sections['show_location'] && !empty($location['address']))
            <span style="color:#cbd5e1;">Wilayah: {{ $location['address'] }}</span>
        @endif
    </div>

    {{-- Header --}}
    <header class="mp-header" style="top:{{ $isPreview ? '41px' : '0' }};">
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
                    <div style="font-weight:800; font-size:16px; color:#0f172a;">{{ $profile['business_name'] }}</div>
                    <div style="font-size:11px; color:#166534; font-weight:600;">Etalase Penjualan Resmi</div>
                </div>
            </a>

            @if ($sections['show_contact'] && !empty($contact['whatsapp']))
                <a href="{{ $contact['whatsapp'] }}" target="_blank" class="mp-btn-wa" style="width:auto; padding:8px 18px; font-size:13px;">
                    Pesan via WhatsApp
                </a>
            @endif
        </div>
    </header>

    {{-- Main Container --}}
    <main style="max-width:1200px; margin:0 auto; padding:36px 24px;" class="space-y-10">

        {{-- Supplier Hero Banner --}}
        <div id="hero" style="background:#ffffff; border:1px solid var(--mp-border); border-radius:18px; padding:32px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:20px;">
                <div style="max-width:720px;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                        @if ($profile['is_verified'])
                            <span style="display:inline-flex; align-items:center; gap:4px; background:#dcfce7; color:#166534; font-size:11px; font-weight:700; padding:4px 10px; border-radius:9999px; border:1px solid #86efac;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                Pemasok Terverifikasi
                            </span>
                        @endif
                        <span style="font-size:12px; color:#64748b;">ID Entitas: #{{ str_pad((string) $profile['id'], 5, '0', STR_PAD_LEFT) }}</span>
                    </div>

                    <h1 style="font-size:28px; font-weight:900; color:#0f172a; margin:0 0 8px 0; letter-spacing:-0.02em;">
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

                {{-- Fast Stats Pill --}}
                @if ($sections['show_productivity'] && $statistics)
                    <div style="background:#f8fafc; border:1px solid var(--mp-border); border-radius:12px; padding:18px 24px; min-width:220px;">
                        <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Kapasitas Produksi</div>
                        <div style="font-size:24px; font-weight:900; color:#166534; margin:4px 0;">{{ $statistics['total_area_ha'] }} Ha</div>
                        <div style="font-size:12px; color:#94a3b8;">{{ $statistics['total_seasons'] }} Musim Terdata</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Marketplace Catalog --}}
        @if ($sections['show_products'] && count($products) > 0)
            <section id="katalog">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
                    <div>
                        <h2 style="font-size:22px; font-weight:800; color:#0f172a; margin:0;">Daftar Hasil Panen Siap Pasok</h2>
                        <p style="font-size:13px; color:#64748b; margin:2px 0 0;">Kuantitas dan harga transparan langsung dari gudang / sawah petani.</p>
                    </div>
                    <span style="font-size:12px; font-weight:700; color:#166534; background:#dcfce7; padding:4px 12px; border-radius:9999px;">
                        {{ count($products) }} Komoditas
                    </span>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:20px;">
                    @foreach ($products as $product)
                        <div class="mp-product-card">
                            <div>
                                <div style="height:190px; background:#f1f5f9; position:relative; overflow:hidden;">
                                    @if ($product['image_url'])
                                        <img src="{{ $product['image_url'] }}" alt="{{ $product['commodity'] }}" style="width:100%; height:100%; object-fit:cover;">
                                    @else
                                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#94a3b8;">
                                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div style="position:absolute; top:10px; right:10px; background:#166534; color:#ffffff; font-size:11px; font-weight:800; padding:4px 10px; border-radius:6px;">
                                        Siap Kirim: {{ $product['quantity'] }} {{ $product['unit'] }}
                                    </div>
                                </div>

                                <div style="padding:18px;">
                                    <h3 style="font-size:16px; font-weight:800; color:#0f172a; margin:0 0 6px 0;">{{ $product['commodity'] }}</h3>
                                    @if ($product['price_per_unit'])
                                        <div style="font-size:20px; font-weight:900; color:#166534; margin-bottom:8px;">
                                            Rp{{ number_format($product['price_per_unit'], 0, ',', '.') }}
                                            <span style="font-size:12px; font-weight:600; color:#64748b;">/ {{ $product['unit'] }}</span>
                                        </div>
                                    @endif
                                    @if ($product['description'])
                                        <p style="font-size:12px; color:#64748b; margin:0; line-height:1.5;">{{ Str::limit($product['description'], 110) }}</p>
                                    @endif
                                </div>
                            </div>

                            <div style="padding:0 18px 18px;">
                                @if ($product['sales_link'])
                                    <a href="{{ $product['sales_link'] }}" target="_blank" rel="nofollow noopener" class="mp-btn-buy">
                                        Pesan Komoditas Ini
                                    </a>
                                @elseif ($sections['show_contact'] && !empty($contact['whatsapp']))
                                    <a href="{{ $contact['whatsapp'] }}" target="_blank" class="mp-btn-wa">
                                        Chat WhatsApp Petani
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
            <section style="margin-top:40px;">
                <h2 style="font-size:20px; font-weight:800; color:#0f172a; margin:0 0 16px 0;">Audit Rekam Jejak Panen</h2>
                <div style="background:#ffffff; border:1px solid var(--mp-border); border-radius:14px; overflow:hidden;">
                    <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px;">
                        <thead>
                            <tr style="background:#0f172a; color:#ffffff;">
                                <th style="padding:14px 18px; font-size:11px; font-weight:700; text-transform:uppercase;">Periode</th>
                                <th style="padding:14px 18px; font-size:11px; font-weight:700; text-transform:uppercase;">Lahan</th>
                                <th style="padding:14px 18px; font-size:11px; font-weight:700; text-transform:uppercase;">Varietas</th>
                                <th style="padding:14px 18px; font-size:11px; font-weight:700; text-transform:uppercase; text-align:right;">Volume</th>
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
            <section style="margin-top:40px;">
                <h2 style="font-size:20px; font-weight:800; color:#0f172a; margin:0 0 16px 0;">Dokumentasi Lahan & Fasilitas</h2>
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:16px;">
                    @foreach ($gallery as $item)
                        @php
                            $imgSrc = is_array($item) ? $item['image_url'] : asset('storage/' . $item->image_path);
                            $cap = is_array($item) ? ($item['caption'] ?? null) : ($item->caption ?? null);
                        @endphp
                        <div style="background:#ffffff; border:1px solid var(--mp-border); border-radius:12px; overflow:hidden; aspect-ratio:4/3; position:relative;">
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


        {{-- Contact Card --}}
        @if ($sections['show_contact'] && $contact)
            <section style="background:#0f172a; border-radius:18px; padding:36px; color:#ffffff; text-align:center;">
                <h2 style="font-size:24px; font-weight:800; color:#ffffff; margin:0 0 8px 0;">Kontak Langsung Pemasok</h2>
                <p style="font-size:14px; color:#94a3b8; margin:0 0 24px 0;">Negosiasi harga kuantitas besar dan kerja sama pengiriman berkala.</p>
                <div style="display:flex; align-items:center; justify-content:center; gap:12px; flex-wrap:wrap;">
                    @if (!empty($contact['whatsapp']))
                        <a href="{{ $contact['whatsapp'] }}" target="_blank" class="mp-btn-wa" style="width:auto; padding:12px 24px; font-size:14px;">WhatsApp Langsung</a>
                    @endif
                    @if (!empty($contact['public_email']))
                        <a href="mailto:{{ $contact['public_email'] }}" style="background:#ffffff; color:#0f172a; font-weight:700; font-size:14px; padding:12px 24px; border-radius:8px; text-decoration:none;">Email Pemasok</a>
                    @endif
                </div>
            </section>
        @endif

    </main>

    {{-- Footer --}}
    <footer style="background:#ffffff; border-top:1px solid var(--mp-border); color:#64748b; font-size:12px; text-align:center; padding:24px;">
        &copy; {{ date('Y') }} {{ $profile['business_name'] }} &bull; Divalidasi oleh <strong>P.A.D.I. B2B Smart Farming Platform</strong>
    </footer>

</body>
</html>
