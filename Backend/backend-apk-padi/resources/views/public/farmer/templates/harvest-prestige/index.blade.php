<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $profile['headline'] ?? $profile['business_name'] }} — Platform P.A.D.I.">
    <meta name="robots" content="{{ $isPreview ? 'noindex, nofollow' : 'index, follow' }}">

    <title>{{ $profile['business_name'] }} {{ $isPreview ? '(Preview)' : '' }} - P.A.D.I.</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --hp-black:  #0f172a;
            --hp-green:  #1b5e20;
            --hp-white:  #ffffff;
            --hp-ivory:  #f7f5ef;
            --hp-muted:  #6b7280;
            --hp-border: #e5e7eb;
        }

        .hp-cover {
            background-color: var(--hp-black);
            min-height: 400px;
            position: relative;
        }
        .hp-cover-bg {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            opacity: 0.35;
        }
        .hp-cover-pattern {
            position: absolute;
            inset: 0;
            background-image: repeating-linear-gradient(
                45deg,
                rgba(27,94,32,0.08) 0,
                rgba(27,94,32,0.08) 1px,
                transparent 0,
                transparent 10px
            );
        }
        .hp-verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(27,94,32,0.15);
            border: 1px solid rgba(27,94,32,0.4);
            color: #166534;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 99px;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }
        .hp-stat-card {
            background: var(--hp-white);
            border: 1px solid var(--hp-border);
            border-radius: 16px;
            padding: 20px 24px;
            text-align: center;
        }
        .hp-product-card {
            background: var(--hp-white);
            border: 1px solid var(--hp-border);
            border-radius: 16px;
            overflow: hidden;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .hp-product-card:hover {
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            transform: translateY(-2px);
        }
        .section-divider {
            height: 1px;
            background: var(--hp-border);
            margin: 48px 0;
        }
    </style>
</head>
<body style="background: var(--hp-ivory); color: var(--hp-black); font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;">

    {{-- Preview Banner --}}
    @if ($isPreview)
        <div style="background: #1b5e20; color: white; text-align: center; padding: 10px 16px; font-size: 13px; font-weight: 600; position: sticky; top: 0; z-index: 999;">
            Mode Preview — Ini tampilan website publik Anda sebelum dipublikasikan.
            <a href="{{ route('farmer.website.index') }}" style="color: #a7f3d0; text-decoration: underline; margin-left: 16px;">Kembali ke Panel</a>
        </div>
    @endif

    {{-- Sticky Navbar --}}
    <header style="background: var(--hp-black); position: sticky; top: {{ $isPreview ? '42px' : '0' }}; z-index: 100;">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if ($profile['logo_url'])
                    <img src="{{ $profile['logo_url'] }}" alt="Logo" class="w-8 h-8 rounded-full object-cover ring-2 ring-white/20">
                @endif
                <span style="color: #ffffff; font-weight: 700; font-size: 15px;">{{ $profile['business_name'] }}</span>
                @if ($profile['is_verified'])
                    <div style="color: #4ade80; display: flex; align-items: center;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                            <path d="m9 12 2 2 4-4"/>
                        </svg>
                    </div>
                @endif
            </div>

            <nav class="hidden md:flex items-center gap-6">
                @if ($sections['show_products'] && count($products) > 0)
                    <a href="#produk" style="color: rgba(255,255,255,0.7); font-size: 13px; font-weight: 500; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.7)'">Produk</a>
                @endif
                @if ($sections['show_harvests'] && count($harvests) > 0)
                    <a href="#panen" style="color: rgba(255,255,255,0.7); font-size: 13px; font-weight: 500; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.7)'">Riwayat Panen</a>
                @endif
                @if ($sections['show_gallery'] && count($gallery) > 0)
                    <a href="#galeri" style="color: rgba(255,255,255,0.7); font-size: 13px; font-weight: 500; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.7)'">Galeri</a>
                @endif
                @if ($sections['show_contact'] && $contact)
                    <a href="#kontak" style="background: #1b5e20; color: white; font-size: 13px; font-weight: 600; padding: 8px 20px; border-radius: 8px; text-decoration: none;">Hubungi</a>
                @endif
            </nav>
        </div>
    </header>

    {{-- Hero / Cover Section --}}
    <section class="hp-cover">
        @if ($profile['cover_image_url'])
            <div class="hp-cover-bg" style="background-image: url('{{ $profile['cover_image_url'] }}');"></div>
        @endif
        <div class="hp-cover-pattern"></div>

        <div class="max-w-6xl mx-auto px-6 relative z-10 py-16 md:py-24 flex flex-col items-start">

            {{-- Verified badge --}}
            @if ($profile['is_verified'])
                <div class="hp-verified-badge mb-5">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                        <path d="m9 12 2 2 4-4"/>
                    </svg>
                    Terverifikasi P.A.D.I.
                </div>
            @endif

            {{-- Logo + name --}}
            <div class="flex items-center gap-5 mb-6">
                @if ($profile['logo_url'])
                    <img src="{{ $profile['logo_url'] }}" alt="Logo" class="w-20 h-20 rounded-2xl object-cover ring-4 ring-white/20 shadow-xl">
                @endif
                <div>
                    <h1 style="color: #ffffff; font-size: clamp(28px,5vw,48px); font-weight: 800; line-height: 1.1; letter-spacing: -0.02em;">
                        {{ $profile['business_name'] }}
                    </h1>
                    @if ($profile['headline'])
                        <p style="color: rgba(255,255,255,0.7); font-size: 16px; margin-top: 8px;">{{ $profile['headline'] }}</p>
                    @endif
                </div>
            </div>

            {{-- Location chip --}}
            @if ($sections['show_location'] && !empty($location['address']))
                <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: rgba(255,255,255,0.85); font-size: 13px; padding: 6px 14px; border-radius: 99px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0Z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    {{ $location['address'] }}
                </div>
            @endif


        </div>
    </section>

    {{-- Main content --}}
    <div class="max-w-6xl mx-auto px-6 py-12">

        {{-- Description --}}
        @if ($profile['description'])
            <section class="mb-12">
                <div class="max-w-3xl">
                    <p style="font-size: 17px; line-height: 1.8; color: #374151;">{{ $profile['description'] }}</p>
                </div>
            </section>
        @endif

        {{-- Statistics --}}
        @if ($sections['show_productivity'] && $statistics)
            <section class="mb-12">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="hp-stat-card">
                        <p style="font-size: 32px; font-weight: 800; color: var(--hp-green);">{{ $statistics['total_area_ha'] }}</p>
                        <p style="font-size: 13px; color: var(--hp-muted); margin-top: 4px;">Hektar Lahan</p>
                    </div>
                    <div class="hp-stat-card">
                        <p style="font-size: 32px; font-weight: 800; color: var(--hp-green);">{{ $statistics['total_seasons'] }}</p>
                        <p style="font-size: 13px; color: var(--hp-muted); margin-top: 4px;">Musim Tanam</p>
                    </div>
                    @if ($statistics['latest_productivity'])
                        <div class="hp-stat-card">
                            <p style="font-size: 32px; font-weight: 800; color: var(--hp-green);">{{ $statistics['latest_productivity'] }}</p>
                            <p style="font-size: 13px; color: var(--hp-muted); margin-top: 4px;">Ton/Ha Terakhir</p>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        {{-- Products --}}
        @if ($sections['show_products'] && count($products) > 0)
            <div class="section-divider"></div>
            <section id="produk" class="mb-12">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 style="font-size: 24px; font-weight: 800; color: var(--hp-black); letter-spacing: -0.01em;">Produk Tersedia</h2>
                        <p style="color: var(--hp-muted); font-size: 14px; margin-top: 4px;">{{ count($products) }} produk aktif dari {{ $profile['business_name'] }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($products as $product)
                        <div class="hp-product-card">
                            @if ($product['image_url'])
                                <img src="{{ $product['image_url'] }}" alt="{{ $product['commodity'] }}"
                                    class="w-full h-44 object-cover">
                            @else
                                <div class="w-full h-44 flex items-center justify-center" style="background: #f1f5f9;">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                                    </svg>
                                </div>
                            @endif
                            <div class="p-4">
                                <p style="font-weight: 700; font-size: 15px; color: var(--hp-black);">{{ $product['commodity'] }}</p>
                                <p style="font-size: 13px; color: var(--hp-muted); margin-top: 2px;">
                                    {{ $product['quantity'] }} {{ $product['unit'] }}
                                </p>
                                @if ($product['price_per_unit'])
                                    <p style="font-weight: 700; color: var(--hp-green); margin-top: 8px; font-size: 15px;">
                                        Rp{{ number_format($product['price_per_unit'], 0, ',', '.') }}/{{ $product['unit'] }}
                                    </p>
                                @endif
                                @if ($product['description'])
                                    <p style="font-size: 12px; color: var(--hp-muted); margin-top: 6px; line-height: 1.5;">
                                        {{ Str::limit($product['description'], 80) }}
                                    </p>
                                @endif
                                @if ($product['sales_link'])
                                    <a href="{{ $product['sales_link'] }}" target="_blank" rel="nofollow noopener"
                                        style="display: block; margin-top: 12px; text-align: center; background: var(--hp-green); color: white; font-size: 13px; font-weight: 600; padding: 10px; border-radius: 8px; text-decoration: none;">
                                        Beli Sekarang
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Harvest History --}}
        @if ($sections['show_harvests'] && count($harvests) > 0)
            <div class="section-divider"></div>
            <section id="panen" class="mb-12">
                <h2 style="font-size: 24px; font-weight: 800; color: var(--hp-black); letter-spacing: -0.01em; margin-bottom: 24px;">Riwayat Panen</h2>
                <div class="overflow-x-auto">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--hp-black); color: white;">
                                <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Tanggal</th>
                                <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Lahan</th>
                                <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Varietas</th>
                                <th style="padding: 12px 16px; text-align: right; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Hasil</th>
                                <th style="padding: 12px 16px; text-align: center; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Kualitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($harvests as $i => $harvest)
                                <tr style="background: {{ $i % 2 === 0 ? '#ffffff' : '#f9fafb' }}; border-bottom: 1px solid var(--hp-border);">
                                    <td style="padding: 12px 16px; font-size: 14px; color: var(--hp-muted);">
                                        {{ \Carbon\Carbon::parse($harvest['harvest_date'])->format('M Y') }}
                                    </td>
                                    <td style="padding: 12px 16px; font-size: 14px; color: var(--hp-black);">{{ $harvest['farm_name'] ?? '-' }}</td>
                                    <td style="padding: 12px 16px; font-size: 14px; color: var(--hp-black);">{{ $harvest['variety_name'] ?? '-' }}</td>
                                    <td style="padding: 12px 16px; font-size: 14px; font-weight: 700; color: var(--hp-green); text-align: right;">
                                        {{ $harvest['quantity'] }} {{ $harvest['unit'] }}
                                    </td>
                                    <td style="padding: 12px 16px; text-align: center;">
                                        @if ($harvest['quality_grade'])
                                            <span style="background: #dcfce7; color: #166534; font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 99px;">
                                                {{ $harvest['quality_grade'] }}
                                            </span>
                                        @else
                                            <span style="color: var(--hp-muted); font-size: 13px;">-</span>
                                        @endif
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
            <div class="section-divider"></div>
            <section id="galeri" class="mb-12">
                <h2 style="font-size: 24px; font-weight: 800; color: var(--hp-black); letter-spacing: -0.01em; margin-bottom: 24px;">Galeri</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach ($gallery as $img)
                        <div style="border-radius: 12px; overflow: hidden; aspect-ratio: 1; background: #f1f5f9;">
                            <img src="{{ asset('storage/' . $img->image_path) }}"
                                alt="{{ $img->caption ?? 'Galeri' }}"
                                style="width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.3s;"
                                onmouseover="this.style.transform='scale(1.05)'"
                                onmouseout="this.style.transform='scale(1)'">
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Contact CTA --}}
        @if ($sections['show_contact'] && $contact)
            <div class="section-divider"></div>
            <section id="kontak" class="mb-12">
                <div style="background: var(--hp-black); border-radius: 20px; padding: 40px 32px; text-align: center;">
                    <h2 style="color: white; font-size: 28px; font-weight: 800; letter-spacing: -0.01em; margin-bottom: 12px;">
                        Tertarik Bertransaksi?
                    </h2>
                    <p style="color: rgba(255,255,255,0.6); font-size: 15px; margin-bottom: 28px;">
                        Hubungi {{ $profile['business_name'] }} langsung melalui WhatsApp atau email.
                    </p>

                    <div class="flex flex-wrap justify-center gap-3">
                        @if ($contact['whatsapp'])
                            <a href="{{ $contact['whatsapp'] }}" target="_blank"
                                style="display: inline-flex; align-items: center; gap: 8px; background: #16a34a; color: white; font-weight: 600; font-size: 14px; padding: 12px 24px; border-radius: 10px; text-decoration: none;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/>
                                </svg>
                                WhatsApp
                            </a>
                        @endif
                        @if ($contact['public_email'])
                            <a href="mailto:{{ $contact['public_email'] }}"
                                style="display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; font-weight: 600; font-size: 14px; padding: 12px 24px; border-radius: 10px; text-decoration: none;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect width="20" height="16" x="2" y="4" rx="2"/>
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                                </svg>
                                Email
                            </a>
                        @endif
                        @if ($contact['public_phone'])
                            <a href="tel:{{ $contact['public_phone'] }}"
                                style="display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; font-weight: 600; font-size: 14px; padding: 12px 24px; border-radius: 10px; text-decoration: none;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 10.23 19.79 19.79 0 0 1 1.6 1.6a2 2 0 0 1 1.99-2.18h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 6.4a16 16 0 0 0 6 6l.92-.92a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 13.92z"/>
                                </svg>
                                {{ $contact['public_phone'] }}
                            </a>
                        @endif
                    </div>

                    {{-- Social media --}}
                    @if ($profile['instagram_url'] || $profile['facebook_url'])
                        <div class="flex justify-center gap-4 mt-5">
                            @if ($profile['instagram_url'])
                                <a href="{{ $profile['instagram_url'] }}" target="_blank" rel="nofollow noopener"
                                    style="color: rgba(255,255,255,0.5); text-decoration: none; font-size: 13px;"
                                    onmouseover="this.style.color='rgba(255,255,255,0.9)'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">
                                    Instagram
                                </a>
                            @endif
                            @if ($profile['facebook_url'])
                                <a href="{{ $profile['facebook_url'] }}" target="_blank" rel="nofollow noopener"
                                    style="color: rgba(255,255,255,0.5); text-decoration: none; font-size: 13px;"
                                    onmouseover="this.style.color='rgba(255,255,255,0.9)'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">
                                    Facebook
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </section>
        @endif

    </div>

    {{-- Footer --}}
    <footer style="background: var(--hp-black); color: rgba(255,255,255,0.4); font-size: 12px; text-align: center; padding: 20px 16px;">
        <p>Halaman ini ditenagai oleh <strong style="color: rgba(255,255,255,0.7);">P.A.D.I.</strong> — Predictive Agriculture & Disease Intelligence</p>
    </footer>

</body>
</html>
