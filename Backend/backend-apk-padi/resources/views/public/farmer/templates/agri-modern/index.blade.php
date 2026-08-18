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
        :root { --am-green: #1b5e20; --am-black: #0f172a; --am-white: #ffffff; --am-gray: #f8fafc; --am-border: #e2e8f0; --am-muted: #64748b; }
        body { background: var(--am-white); color: var(--am-black); font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        .am-header { background: var(--am-white); border-bottom: 1px solid var(--am-border); position: sticky; top: 0; z-index: 100; }
        .am-hero { background: var(--am-green); color: var(--am-white); }
        .am-card { background: var(--am-white); border: 1px solid var(--am-border); border-radius: 12px; overflow: hidden; transition: box-shadow .2s; }
        .am-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); }
        .am-stat { background: var(--am-gray); border-radius: 12px; padding: 24px; }
        .am-badge { background: #dcfce7; color: #166534; font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 99px; display: inline-flex; align-items: center; gap: 4px; }
        .am-btn-primary { background: var(--am-green); color: white; font-weight: 600; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-block; font-size: 14px; transition: opacity .2s; }
        .am-btn-primary:hover { opacity: 0.9; }
        .am-section-title { font-size: 20px; font-weight: 800; color: var(--am-black); letter-spacing: -.01em; margin-bottom: 16px; }
    </style>
</head>
<body>

    @if ($isPreview)
        <div style="background:#1b5e20;color:white;text-align:center;padding:10px;font-size:13px;font-weight:600;position:sticky;top:0;z-index:999;">
            Mode Preview
            <a href="{{ route('farmer.website.index') }}" style="color:#a7f3d0;text-decoration:underline;margin-left:12px;">Kembali</a>
        </div>
    @endif

    <header class="am-header">
        <div class="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if ($profile['logo_url'])
                    <img src="{{ $profile['logo_url'] }}" alt="Logo" class="w-7 h-7 rounded-full object-cover">
                @endif
                <span style="font-weight:700;font-size:14px;color:var(--am-black);">{{ $profile['business_name'] }}</span>
                @if ($profile['is_verified'])
                    <span class="am-badge">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        Terverifikasi
                    </span>
                @endif
            </div>
            @if ($sections['show_contact'] && !empty($contact['whatsapp']))
                <a href="{{ $contact['whatsapp'] }}" target="_blank" class="am-btn-primary" style="padding:8px 18px;font-size:13px;">Hubungi</a>
            @endif
        </div>
    </header>

    <section class="am-hero py-16">
        <div class="max-w-6xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
                <div>
                    <p style="color:rgba(255,255,255,.6);font-size:12px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;margin-bottom:12px;">
                        @if ($sections['show_location'] && !empty($location['address']))
                            {{ $location['address'] }}
                        @else
                            Profil Usaha Tani
                        @endif
                    </p>

                    <h1 style="font-size:clamp(24px,5vw,42px);font-weight:800;letter-spacing:-.02em;line-height:1.15;">{{ $profile['business_name'] }}</h1>
                    @if ($profile['headline'])
                        <p style="color:rgba(255,255,255,.75);font-size:16px;margin-top:10px;">{{ $profile['headline'] }}</p>
                    @endif
                    @if ($profile['description'])
                        <p style="color:rgba(255,255,255,.65);font-size:14px;margin-top:14px;line-height:1.7;">{{ Str::limit($profile['description'], 180) }}</p>
                    @endif
                </div>
                @if ($profile['cover_image_url'])
                    <div style="border-radius:16px;overflow:hidden;height:260px;">
                        <img src="{{ $profile['cover_image_url'] }}" alt="Cover" style="width:100%;height:100%;object-fit:cover;">
                    </div>
                @endif
            </div>
        </div>
    </section>

    <div class="max-w-6xl mx-auto px-6 py-10 space-y-10">

        @if ($sections['show_productivity'] && $statistics)
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="am-stat text-center">
                    <p style="font-size:28px;font-weight:800;color:var(--am-green);">{{ $statistics['total_area_ha'] }} ha</p>
                    <p style="font-size:12px;color:var(--am-muted);margin-top:4px;">Total Lahan</p>
                </div>
                <div class="am-stat text-center">
                    <p style="font-size:28px;font-weight:800;color:var(--am-green);">{{ $statistics['total_seasons'] }}</p>
                    <p style="font-size:12px;color:var(--am-muted);margin-top:4px;">Musim Tanam</p>
                </div>
                @if ($statistics['latest_productivity'])
                    <div class="am-stat text-center">
                        <p style="font-size:28px;font-weight:800;color:var(--am-green);">{{ $statistics['latest_productivity'] }} t/ha</p>
                        <p style="font-size:12px;color:var(--am-muted);margin-top:4px;">Produktivitas</p>
                    </div>
                @endif
            </div>
        @endif

        @if ($sections['show_products'] && count($products) > 0)
            <section id="produk">
                <h2 class="am-section-title">Produk</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($products as $product)
                        <div class="am-card">
                            @if ($product['image_url'])
                                <img src="{{ $product['image_url'] }}" alt="{{ $product['commodity'] }}" style="width:100%;height:180px;object-fit:cover;">
                            @else
                                <div style="height:180px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;">
                                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg>
                                </div>
                            @endif
                            <div style="padding:16px;">
                                <p style="font-weight:700;font-size:15px;">{{ $product['commodity'] }}</p>
                                <p style="font-size:13px;color:var(--am-muted);margin-top:2px;">{{ $product['quantity'] }} {{ $product['unit'] }}</p>
                                @if ($product['price_per_unit'])
                                    <p style="color:var(--am-green);font-weight:700;margin-top:6px;">Rp{{ number_format($product['price_per_unit'], 0, ',', '.') }}/{{ $product['unit'] }}</p>
                                @endif
                                @if ($product['sales_link'])
                                    <a href="{{ $product['sales_link'] }}" target="_blank" rel="nofollow noopener" class="am-btn-primary" style="margin-top:12px;width:100%;text-align:center;display:block;padding:8px;">Beli</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($sections['show_harvests'] && count($harvests) > 0)
            <section id="panen">
                <h2 class="am-section-title">Riwayat Panen</h2>
                <div style="background:#f8fafc;border-radius:12px;overflow:hidden;">
                    @foreach ($harvests as $i => $harvest)
                        <div style="display:flex;align-items:center;gap:12px;padding:14px 20px;{{ $i > 0 ? 'border-top:1px solid var(--am-border);' : '' }}">
                            <div style="flex:1;">
                                <p style="font-weight:600;font-size:14px;">{{ $harvest['farm_name'] ?? 'Lahan' }} — {{ $harvest['variety_name'] ?? '-' }}</p>
                                <p style="font-size:12px;color:var(--am-muted);">{{ \Carbon\Carbon::parse($harvest['harvest_date'])->format('M Y') }}</p>
                            </div>
                            <p style="font-weight:700;color:var(--am-green);font-size:14px;">{{ $harvest['quantity'] }} {{ $harvest['unit'] }}</p>
                            @if ($harvest['quality_grade'])
                                <span class="am-badge">{{ $harvest['quality_grade'] }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($sections['show_gallery'] && count($gallery) > 0)
            <section id="galeri">
                <h2 class="am-section-title">Galeri</h2>
                <div class="grid grid-cols-3 md:grid-cols-4 gap-3">
                    @foreach ($gallery as $img)
                        <div style="border-radius:10px;overflow:hidden;aspect-ratio:1;">
                            <img src="{{ asset('storage/'.$img->image_path) }}" alt="{{ $img->caption ?? 'Galeri' }}" style="width:100%;height:100%;object-fit:cover;">
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($sections['show_contact'] && $contact)
            <section id="kontak" style="background:var(--am-green);border-radius:16px;padding:32px;text-align:center;">
                <h2 style="color:white;font-size:22px;font-weight:800;margin-bottom:8px;">Hubungi Kami</h2>
                <p style="color:rgba(255,255,255,.7);font-size:14px;margin-bottom:20px;">{{ $profile['business_name'] }}</p>
                <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:10px;">
                    @if ($contact['whatsapp'])
                        <a href="{{ $contact['whatsapp'] }}" target="_blank" style="background:#16a34a;color:white;font-weight:600;font-size:13px;padding:10px 20px;border-radius:8px;text-decoration:none;">WhatsApp</a>
                    @endif
                    @if ($contact['public_email'])
                        <a href="mailto:{{ $contact['public_email'] }}" style="background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);color:white;font-weight:600;font-size:13px;padding:10px 20px;border-radius:8px;text-decoration:none;">Email</a>
                    @endif
                </div>
            </section>
        @endif

    </div>

    <footer style="background:var(--am-black);color:rgba(255,255,255,.4);font-size:12px;text-align:center;padding:16px;">
        Ditenagai <strong style="color:rgba(255,255,255,.7);">P.A.D.I.</strong>
    </footer>

</body>
</html>
