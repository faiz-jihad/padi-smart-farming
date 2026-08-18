<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $profile['headline'] ?? $profile['business_name'] }} — Digital Profile & Verified Farm by P.A.D.I.">
    <meta name="robots" content="{{ $isPreview ? 'noindex, nofollow' : 'index, follow' }}">

    <title>{{ $profile['business_name'] }} {{ $isPreview ? '(Preview Mode)' : '' }} &mdash; P.A.D.I.</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --hp-dark: #0b1320;
            --hp-surface: #0f172a;
            --hp-card-dark: #1e293b;
            --hp-green-primary: #166534;
            --hp-green-hover: #14532d;
            --hp-green-light: #dcfce7;
            --hp-green-accent: #22c55e;
            --hp-light-bg: #f8fafc;
            --hp-white: #ffffff;
            --hp-border: #e2e8f0;
            --hp-border-dark: #334155;
            --hp-text-main: #0f172a;
            --hp-text-muted: #64748b;
        }

        body {
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
            background-color: var(--hp-light-bg);
            color: var(--hp-text-main);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .hp-header-glass {
            background-color: rgba(11, 19, 32, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .hp-hero-bg {
            background-color: var(--hp-dark);
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid var(--hp-border-dark);
        }

        .hp-hero-pattern {
            position: absolute;
            inset: 0;
            background-image: 
                radial-gradient(rgba(34, 197, 94, 0.08) 1px, transparent 1px),
                linear-gradient(to bottom, transparent, rgba(11, 19, 32, 0.95));
            background-size: 24px 24px, 100% 100%;
        }

        .hp-badge-verified {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(34, 197, 94, 0.12);
            border: 1px solid rgba(34, 197, 94, 0.35);
            color: #4ade80;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            padding: 6px 14px;
            border-radius: 9999px;
        }

        .hp-metric-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px 20px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            transition: all 0.25s ease;
        }

        .hp-metric-card:hover {
            border-color: #166534;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(22, 101, 52, 0.08);
        }

        .hp-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hp-card:hover {
            border-color: #cbd5e1;
            transform: translateY(-3px);
            box-shadow: 0 14px 30px -8px rgba(15, 23, 42, 0.08);
        }

        .hp-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: #166534;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 14px;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(22, 101, 52, 0.2);
            border: 1px solid #166534;
        }

        .hp-btn-primary:hover {
            background-color: #14532d;
            border-color: #14532d;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(22, 101, 52, 0.3);
        }

        .hp-btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: #ffffff;
            color: #0f172a !important;
            font-weight: 700;
            font-size: 14px;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            border: 1px solid #cbd5e1;
            transition: all 0.2s ease;
        }

        .hp-btn-secondary:hover {
            background-color: #f8fafc;
            border-color: #94a3b8;
            transform: translateY(-1px);
        }

        .hp-btn-whatsapp {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: #16a34a;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 14px;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(22, 163, 74, 0.25);
        }

        .hp-btn-whatsapp:hover {
            background-color: #15803d;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(22, 163, 74, 0.35);
        }

        .hp-section-header {
            margin-bottom: 32px;
        }

        .hp-section-tag {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #166534;
            margin-bottom: 6px;
        }

        .hp-section-title {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
            margin: 0;
        }

        .hp-section-desc {
            font-size: 14px;
            color: #64748b;
            margin-top: 6px;
        }
    </style>
</head>
<body>

    {{-- Preview Bar --}}
    @if ($isPreview)
        <div style="background-color:#166534; color:#ffffff; padding:10px 16px; font-size:13px; font-weight:700; text-align:center; position:sticky; top:0; z-index:999; border-bottom:1px solid #14532d; display:flex; align-items:center; justify-content:center; gap:16px;">
            <span>Mode Preview Publikasi &mdash; Tampilan resmi profil usaha tani Anda</span>
            <a href="{{ route('farmer.website.index') }}" style="background:#ffffff; color:#166534; padding:4px 12px; border-radius:6px; font-size:12px; text-decoration:none; font-weight:700;">
                Kembali ke Panel
            </a>
        </div>
    @endif

    {{-- Top Navbar --}}
    <header class="hp-header-glass" style="position:sticky; top:{{ $isPreview ? '41px' : '0' }}; z-index:100;">
        <div style="max-width:1200px; margin:0 auto; padding:0 24px; height:70px; display:flex; align-items:center; justify-content:space-between;">
            {{-- Brand Identity --}}
            <a href="#hero" style="display:flex; align-items:center; gap:12px; text-decoration:none;">
                @if ($profile['logo_url'])
                    <img src="{{ $profile['logo_url'] }}" alt="{{ $profile['business_name'] }}" style="width:38px; height:38px; border-radius:10px; object-fit:cover; border:1.5px solid rgba(255,255,255,0.2);">
                @else
                    <div style="width:38px; height:38px; border-radius:10px; background:#166534; color:#ffffff; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:16px; border:1.5px solid rgba(255,255,255,0.2);">
                        {{ substr($profile['business_name'], 0, 1) }}
                    </div>
                @endif
                <div>
                    <div style="font-weight:800; font-size:15px; color:#ffffff; letter-spacing:-0.01em;">{{ $profile['business_name'] }}</div>
                    <div style="font-size:11px; color:#94a3b8;">Profil Usaha Tani P.A.D.I.</div>
                </div>
            </a>

            {{-- Nav Links --}}
            <nav style="display:flex; align-items:center; gap:24px;">
                @if ($sections['show_products'] && count($products) > 0)
                    <a href="#katalog" style="color:#cbd5e1; font-size:13px; font-weight:600; text-decoration:none; transition:color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#cbd5e1'">Katalog Produk</a>
                @endif
                @if ($sections['show_harvests'] && count($harvests) > 0)
                    <a href="#riwayat-panen" style="color:#cbd5e1; font-size:13px; font-weight:600; text-decoration:none; transition:color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#cbd5e1'">Rekam Jejak</a>
                @endif
                @if ($sections['show_gallery'] && count($gallery) > 0)
                    <a href="#dokumentasi" style="color:#cbd5e1; font-size:13px; font-weight:600; text-decoration:none; transition:color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#cbd5e1'">Galeri Lahan</a>
                @endif
                @if ($sections['show_contact'] && $contact)
                    <a href="#kontak" class="hp-btn-primary" style="padding:8px 18px; font-size:13px;">
                        Hubungi Petani
                    </a>
                @endif
            </nav>
        </div>
    </header>

    {{-- Hero Section --}}
    <section id="hero" class="hp-hero-bg">
        @if ($profile['cover_image_url'])
            <div style="position:absolute; inset:0; background-image:url('{{ $profile['cover_image_url'] }}'); background-size:cover; background-position:center; opacity:0.25;"></div>
        @endif
        <div class="hp-hero-pattern"></div>

        <div style="max-width:1200px; margin:0 auto; padding:64px 24px 72px; position:relative; z-index:10;">
            <div style="max-width:800px;">
                
                {{-- Badges Strip --}}
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:20px; flex-wrap:wrap;">
                    @if ($profile['is_verified'])
                        <div class="hp-badge-verified">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                                <path d="m9 12 2 2 4-4"/>
                            </svg>
                            Terverifikasi P.A.D.I.
                        </div>
                    @endif


                    @if ($sections['show_location'] && !empty($location['address']))
                        <div style="display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#cbd5e1; font-size:12px; font-weight:600; padding:6px 14px; border-radius:9999px;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0Z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            {{ $location['address'] }}
                        </div>
                    @endif
                </div>

                {{-- Hero Title & Slogan --}}
                <h1 style="font-size:clamp(32px, 5vw, 52px); font-weight:900; color:#ffffff; letter-spacing:-0.03em; line-height:1.15; margin:0 0 14px 0;">
                    {{ $profile['business_name'] }}
                </h1>

                @if ($profile['headline'])
                    <p style="font-size:18px; font-weight:500; color:#94a3b8; margin:0 0 24px 0; line-height:1.5;">
                        {{ $profile['headline'] }}
                    </p>
                @endif

                @if ($profile['description'])
                    <div style="font-size:15px; color:#cbd5e1; line-height:1.8; margin-bottom:32px; background:rgba(255,255,255,0.04); border-left:3px solid #166534; padding:16px 20px; border-radius:0 12px 12px 0;">
                        {{ $profile['description'] }}
                    </div>
                @endif

                {{-- Quick Actions --}}
                <div style="display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
                    @if ($sections['show_contact'] && !empty($contact['whatsapp']))
                        <a href="{{ $contact['whatsapp'] }}" target="_blank" class="hp-btn-whatsapp">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/>
                            </svg>
                            Hubungi via WhatsApp
                        </a>
                    @endif

                    @if ($sections['show_products'] && count($products) > 0)
                        <a href="#katalog" class="hp-btn-secondary">
                            Lihat Katalog Hasil Panen
                        </a>
                    @endif
                </div>

            </div>
        </div>
    </section>

    {{-- Executive Metrics Strip --}}
    @if ($sections['show_productivity'] && $statistics)
        <div style="background:#ffffff; border-bottom:1px solid #e2e8f0; padding:28px 24px;">
            <div style="max-width:1200px; margin:0 auto; display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:20px;">
                <div class="hp-metric-card">
                    <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b; letter-spacing:0.06em;">Total Luas Lahan</div>
                    <div style="font-size:32px; font-weight:900; color:#166534; margin:4px 0 2px;">{{ $statistics['total_area_ha'] }} <span style="font-size:16px; font-weight:600; color:#64748b;">Ha</span></div>
                    <div style="font-size:12px; color:#94a3b8;">Lahan pertanian produktif</div>
                </div>

                <div class="hp-metric-card">
                    <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b; letter-spacing:0.06em;">Musim Tanam Tercatat</div>
                    <div style="font-size:32px; font-weight:900; color:#166534; margin:4px 0 2px;">{{ $statistics['total_seasons'] }} <span style="font-size:16px; font-weight:600; color:#64748b;">Musim</span></div>
                    <div style="font-size:12px; color:#94a3b8;">Siklus budidaya terdata</div>
                </div>

                @if ($statistics['latest_productivity'])
                    <div class="hp-metric-card">
                        <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b; letter-spacing:0.06em;">Produktivitas Panen</div>
                        <div style="font-size:32px; font-weight:900; color:#166534; margin:4px 0 2px;">{{ $statistics['latest_productivity'] }} <span style="font-size:16px; font-weight:600; color:#64748b;">Ton/Ha</span></div>
                        <div style="font-size:12px; color:#94a3b8;">Rata-rata hasil panen terakhir</div>
                    </div>
                @endif

                <div class="hp-metric-card">
                    <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b; letter-spacing:0.06em;">Status Verifikasi Mutu</div>
                    <div style="font-size:24px; font-weight:900; color:#166534; margin:8px 0 2px; display:flex; align-items:center; justify-content:center; gap:6px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#166534" stroke-width="2.5">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                            <path d="m9 12 2 2 4-4"/>
                        </svg>
                        Tervalidasi
                    </div>
                    <div style="font-size:12px; color:#94a3b8;">Standar Mutu P.A.D.I.</div>
                </div>
            </div>
        </div>
    @endif

    {{-- Main Container --}}
    <main style="max-width:1200px; margin:0 auto; padding:64px 24px;" class="space-y-16">

        {{-- Section: Products / Commodities --}}
        @if ($sections['show_products'] && count($products) > 0)
            <section id="katalog">
                <div class="hp-section-header" style="display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:16px;">
                    <div>
                        <div class="hp-section-tag">Katalog Komoditas</div>
                        <h2 class="hp-section-title">Hasil Panen & Produk Tersedia</h2>
                        <p class="hp-section-desc">Pilihan hasil panen berkualitas langsung dari sumber petani terpercaya.</p>
                    </div>
                    <span style="font-size:13px; font-weight:700; color:#166534; background:#dcfce7; padding:6px 14px; border-radius:9999px;">
                        {{ count($products) }} Komoditas Aktif
                    </span>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:24px;">
                    @foreach ($products as $product)
                        <div class="hp-card" style="display:flex; flex-direction:column;">
                            {{-- Product Image --}}
                            <div style="height:220px; background:#f1f5f9; position:relative; overflow:hidden;">
                                @if ($product['image_url'])
                                    <img src="{{ $product['image_url'] }}" alt="{{ $product['commodity'] }}" style="width:100%; height:100%; object-fit:cover; transition:transform 0.3s;" onmouseover="this.style.transform='scale(1.04)'" onmouseout="this.style.transform='scale(1)'">
                                @else
                                    <div style="width:100%; height:100%; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#94a3b8;">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                                        </svg>
                                        <span style="font-size:12px; margin-top:6px;">Foto Produk</span>
                                    </div>
                                @endif

                                <div style="position:absolute; top:12px; right:12px; background:rgba(15,23,42,0.85); color:#ffffff; font-size:11px; font-weight:700; padding:4px 10px; border-radius:6px; backdrop-filter:blur(4px);">
                                    Stok: {{ $product['quantity'] }} {{ $product['unit'] }}
                                </div>
                            </div>

                            {{-- Product Content --}}
                            <div style="padding:22px; flex:1; display:flex; flex-direction:column; justify-content:space-between;">
                                <div>
                                    <h3 style="font-size:18px; font-weight:800; color:#0f172a; margin:0 0 6px 0;">
                                        {{ $product['commodity'] }}
                                    </h3>

                                    @if ($product['price_per_unit'])
                                        <div style="font-size:20px; font-weight:900; color:#166534; margin-bottom:10px;">
                                            Rp{{ number_format($product['price_per_unit'], 0, ',', '.') }}
                                            <span style="font-size:13px; font-weight:500; color:#64748b;">/ {{ $product['unit'] }}</span>
                                        </div>
                                    @endif

                                    @if ($product['description'])
                                        <p style="font-size:13px; color:#64748b; margin:0 0 16px 0; line-height:1.6;">
                                            {{ Str::limit($product['description'], 120) }}
                                        </p>
                                    @endif
                                </div>

                                <div>
                                    @if ($product['sales_link'])
                                        <a href="{{ $product['sales_link'] }}" target="_blank" rel="nofollow noopener" class="hp-btn-primary" style="width:100%; box-sizing:border-box;">
                                            Pesan / Beli Sekarang
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                                <polyline points="15 3 21 3 21 9"/>
                                            </svg>
                                        </a>
                                    @elseif ($sections['show_contact'] && !empty($contact['whatsapp']))
                                        <a href="{{ $contact['whatsapp'] }}" target="_blank" class="hp-btn-whatsapp" style="width:100%; box-sizing:border-box;">
                                            Tanya Ketersediaan
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Section: Harvest Traceability --}}
        @if ($sections['show_harvests'] && count($harvests) > 0)
            <section id="riwayat-panen" style="margin-top:56px;">
                <div class="hp-section-header">
                    <div class="hp-section-tag">Audit Mutu & Transparansi</div>
                    <h2 class="hp-section-title">Rekam Jejak & Riwayat Panen</h2>
                    <p class="hp-section-desc">Data riwayat panen terverifikasi yang tercatat secara transparan pada ekosistem P.A.D.I.</p>
                </div>

                <div class="hp-card" style="padding:0; overflow:hidden;">
                    <div style="overflow-x:auto;">
                        <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px;">
                            <thead>
                                <tr style="background:#0f172a; color:#ffffff;">
                                    <th style="padding:16px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;">Periode Panen</th>
                                    <th style="padding:16px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;">Lahan Pertanian</th>
                                    <th style="padding:16px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;">Varietas Padi</th>
                                    <th style="padding:16px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; text-align:right;">Volume Panen</th>
                                    <th style="padding:16px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; text-align:center;">Grade Kualitas</th>
                                </tr>
                            </thead>
                            <tbody style="border-top:1px solid #e2e8f0;">
                                @foreach ($harvests as $i => $harvest)
                                    <tr style="border-bottom:1px solid #f1f5f9; background:{{ $i % 2 === 0 ? '#ffffff' : '#f8fafc' }};">
                                        <td style="padding:16px 20px; font-weight:600; color:#0f172a;">
                                            {{ \Carbon\Carbon::parse($harvest['harvest_date'])->translatedFormat('F Y') }}
                                        </td>
                                        <td style="padding:16px 20px; color:#334155;">
                                            {{ $harvest['farm_name'] ?? 'Lahan Utama' }}
                                        </td>
                                        <td style="padding:16px 20px; font-weight:700; color:#166534;">
                                            {{ $harvest['variety_name'] ?? 'Varietas Unggul' }}
                                        </td>
                                        <td style="padding:16px 20px; text-align:right; font-weight:800; color:#0f172a; font-size:14px;">
                                            {{ number_format($harvest['quantity'], 1, ',', '.') }} {{ $harvest['unit'] }}
                                        </td>
                                        <td style="padding:16px 20px; text-align:center;">
                                            <span style="display:inline-block; font-size:11px; font-weight:800; padding:4px 12px; border-radius:9999px; background:#dcfce7; color:#166534; border:1px solid #86efac;">
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

        {{-- Section: Gallery --}}
        @if ($sections['show_gallery'] && count($gallery) > 0)
            <section id="dokumentasi" style="margin-top:56px;">
                <div class="hp-section-header">
                    <div class="hp-section-tag">Dokumentasi Lapangan</div>
                    <h2 class="hp-section-title">Galeri Lahan & Aktivitas Tani</h2>
                    <p class="hp-section-desc">Dokumentasi autentik proses budidaya, kondisi sawah, dan penanganan panen.</p>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:18px;">
                    @foreach ($gallery as $item)
                        @php
                            $imgSrc = is_array($item) ? $item['image_url'] : asset('storage/' . $item->image_path);
                            $cap = is_array($item) ? ($item['caption'] ?? null) : ($item->caption ?? null);
                        @endphp
                        <div class="hp-card" style="padding:0; position:relative; aspect-ratio:4/3; overflow:hidden;">
                            <img src="{{ $imgSrc }}" alt="{{ $cap ?? 'Galeri' }}" style="width:100%; height:100%; object-fit:cover; transition:transform 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                            @if ($cap)
                                <div style="position:absolute; bottom:0; left:0; right:0; background:linear-gradient(to top, rgba(15,23,42,0.9), transparent); color:#ffffff; font-size:12px; font-weight:600; padding:16px 14px 10px;">
                                    {{ $cap }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

            </section>
        @endif

        {{-- Section: Direct Contact & B2B Inquiry --}}
        @if ($sections['show_contact'] && $contact)
            <section id="kontak" style="margin-top:56px;">
                <div style="background-color:#0f172a; border-radius:24px; padding:48px 36px; color:#ffffff; position:relative; overflow:hidden; border:1px solid #1e293b;">
                    <div style="position:relative; z-index:10; max-width:760px; margin:0 auto; text-align:center;">
                        
                        <div style="display:inline-flex; align-items:center; gap:6px; background:rgba(34,197,94,0.12); color:#4ade80; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; padding:6px 16px; border-radius:9999px; margin-bottom:16px;">
                            Kemitraan & Penjualan Langsung
                        </div>

                        <h2 style="font-size:32px; font-weight:900; color:#ffffff; letter-spacing:-0.02em; margin:0 0 12px 0;">
                            Berminat Menjalin Kemitraan atau Membeli Panen?
                        </h2>

                        <p style="font-size:15px; color:#94a3b8; line-height:1.7; margin:0 0 32px 0;">
                            Hubungi langsung pengelola usaha tani <strong>{{ $profile['business_name'] }}</strong> untuk penawaran harga terbaik, pemesanan kuantitas besar, atau kunjungan ke lokasi lahan.
                        </p>

                        {{-- Contact Buttons --}}
                        <div style="display:flex; align-items:center; justify-content:center; gap:14px; flex-wrap:wrap; margin-bottom:32px;">
                            @if (!empty($contact['whatsapp']))
                                <a href="{{ $contact['whatsapp'] }}" target="_blank" class="hp-btn-whatsapp" style="font-size:15px; padding:14px 28px;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/>
                                    </svg>
                                    Chat WhatsApp Langsung
                                </a>
                            @endif

                            @if (!empty($contact['public_email']))
                                <a href="mailto:{{ $contact['public_email'] }}" class="hp-btn-secondary" style="font-size:15px; padding:14px 28px;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect width="20" height="16" x="2" y="4" rx="2"/>
                                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                                    </svg>
                                    Kirim Email Resmi
                                </a>
                            @endif

                            @if (!empty($contact['public_phone']))
                                <a href="tel:{{ $contact['public_phone'] }}" class="hp-btn-secondary" style="font-size:15px; padding:14px 28px;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 10.23 19.79 19.79 0 0 1 1.6 1.6a2 2 0 0 1 1.99-2.18h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 6.4a16 16 0 0 0 6 6l.92-.92a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 13.92z"/>
                                    </svg>
                                    {{ $contact['public_phone'] }}
                                </a>
                            @endif
                        </div>

                        {{-- Social Links --}}
                        @if ($profile['instagram_url'] || $profile['facebook_url'])
                            <div style="display:flex; align-items:center; justify-content:center; gap:18px; font-size:13px; color:#94a3b8; border-top:1px solid rgba(255,255,255,0.08); padding-top:20px;">
                                <span>Media Sosial:</span>
                                @if ($profile['instagram_url'])
                                    <a href="{{ $profile['instagram_url'] }}" target="_blank" rel="nofollow noopener" style="color:#cbd5e1; text-decoration:none; font-weight:600;">Instagram</a>
                                @endif
                                @if ($profile['facebook_url'])
                                    <a href="{{ $profile['facebook_url'] }}" target="_blank" rel="nofollow noopener" style="color:#cbd5e1; text-decoration:none; font-weight:600;">Facebook</a>
                                @endif
                            </div>
                        @endif

                    </div>
                </div>
            </section>
        @endif

    </main>

    {{-- Enterprise Footer --}}
    <footer style="background-color:#0b1320; border-top:1px solid #1e293b; color:#64748b; font-size:13px; padding:36px 24px; text-align:center;">
        <div style="max-width:1200px; margin:0 auto; display:flex; flex-direction:column; align-items:center; gap:12px;">
            <div style="display:flex; align-items:center; gap:8px; color:#ffffff; font-weight:800; font-size:15px;">
                <div style="width:24px; height:24px; border-radius:6px; background:#166534; display:flex; align-items:center; justify-content:center;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                    </svg>
                </div>
                P.A.D.I. Digital Farm Network
            </div>
            <p style="margin:0; max-width:600px; line-height:1.6;">
                Halaman profil publik resmi ini divalidasi dan dilindungi oleh ekosistem <strong>P.A.D.I. (Predictive Agriculture & Disease Intelligence)</strong>.
            </p>
            <div style="font-size:12px; color:#475569; margin-top:8px;">
                &copy; {{ date('Y') }} {{ $profile['business_name'] }} &bull; Seluruh hak cipta dilindungi.
            </div>
        </div>
    </footer>

</body>
</html>
