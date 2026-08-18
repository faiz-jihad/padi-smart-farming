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
        :root { --mp-green: #1b5e20; --mp-black: #0f172a; --mp-white: #ffffff; --mp-surface: #f0fdf4; --mp-border: #d1fae5; }
        body { background: var(--mp-surface); color: var(--mp-black); font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        .mp-product-card { background: white; border: 2px solid var(--mp-border); border-radius: 16px; overflow: hidden; transition: border-color .2s, box-shadow .2s; }
        .mp-product-card:hover { border-color: var(--mp-green); box-shadow: 0 4px 20px rgba(27,94,32,.1); }
        .mp-cta-btn { display: block; text-align: center; background: var(--mp-green); color: white; font-weight: 700; font-size: 14px; padding: 12px; border-radius: 10px; text-decoration: none; transition: opacity .2s; }
        .mp-cta-btn:hover { opacity: .9; }
        .mp-header { background: var(--mp-green); color: white; position: sticky; top: 0; z-index: 100; }
    </style>
</head>
<body>

    @if ($isPreview)
        <div style="background:#0f172a;color:white;text-align:center;padding:10px;font-size:13px;font-weight:600;">
            Mode Preview
            <a href="{{ route('farmer.website.index') }}" style="color:#6ee7b7;text-decoration:underline;margin-left:12px;">Kembali</a>
        </div>
    @endif

    <header class="mp-header">
        <div class="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if ($profile['logo_url'])
                    <img src="{{ $profile['logo_url'] }}" alt="Logo" class="w-7 h-7 rounded-full object-cover ring-2 ring-white/30">
                @endif
                <span style="font-weight:700;font-size:15px;">{{ $profile['business_name'] }}</span>
                @if ($profile['is_verified'])
                    <span style="background:rgba(255,255,255,.2);color:white;font-size:11px;font-weight:600;padding:3px 10px;border-radius:99px;">Terverifikasi P.A.D.I.</span>
                @endif
            </div>
            @if ($sections['show_contact'] && !empty($contact['whatsapp']))
                <a href="{{ $contact['whatsapp'] }}" target="_blank" style="background:white;color:var(--mp-green);font-size:13px;font-weight:700;padding:8px 18px;border-radius:8px;text-decoration:none;">Hubungi WA</a>
            @endif
        </div>
    </header>

    {{-- Hero — product-centric --}}
    <div style="background:var(--mp-green);" class="py-10">
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex items-center gap-5">
                @if ($profile['logo_url'])
                    <img src="{{ $profile['logo_url'] }}" alt="Logo" style="width:64px;height:64px;border-radius:16px;object-fit:cover;border:3px solid rgba(255,255,255,.3);">
                @endif
                <div>
                    <h1 style="color:white;font-size:clamp(22px,4vw,36px);font-weight:800;letter-spacing:-.01em;">{{ $profile['business_name'] }}</h1>
                    @if ($profile['headline'])
                        <p style="color:rgba(255,255,255,.75);font-size:14px;margin-top:4px;">{{ $profile['headline'] }}</p>
                    @endif
                    @if ($sections['show_location'] && !empty($location['address']))
                        <p style="color:rgba(255,255,255,.55);font-size:12px;margin-top:6px;">{{ $location['address'] }}</p>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-6 py-10 space-y-10">

        {{-- Produk — prominently first --}}
        @if ($sections['show_products'] && count($products) > 0)
            <section>
                <div class="flex items-center justify-between mb-5">
                    <h2 style="font-size:20px;font-weight:800;color:var(--mp-black);">Produk Tersedia</h2>
                    <span style="background:var(--mp-green);color:white;font-size:12px;font-weight:600;padding:4px 12px;border-radius:99px;">{{ count($products) }} Produk</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($products as $product)
                        <div class="mp-product-card">
                            @if ($product['image_url'])
                                <img src="{{ $product['image_url'] }}" alt="{{ $product['commodity'] }}" style="width:100%;height:200px;object-fit:cover;">
                            @else
                                <div style="height:200px;background:#f1fdf4;display:flex;align-items:center;justify-content:center;">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#86efac" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg>
                                </div>
                            @endif
                            <div style="padding:18px;">
                                <p style="font-weight:700;font-size:16px;color:var(--mp-black);">{{ $product['commodity'] }}</p>
                                <p style="font-size:13px;color:#6b7280;margin-top:2px;">Stok: {{ $product['quantity'] }} {{ $product['unit'] }}</p>
                                @if ($product['price_per_unit'])
                                    <p style="font-size:18px;font-weight:800;color:var(--mp-green);margin-top:8px;">
                                        Rp{{ number_format($product['price_per_unit'], 0, ',', '.') }}
                                        <span style="font-size:13px;font-weight:400;color:#6b7280;">/{{ $product['unit'] }}</span>
                                    </p>
                                @endif
                                @if ($product['description'])
                                    <p style="font-size:13px;color:#6b7280;margin-top:8px;line-height:1.5;">{{ Str::limit($product['description'], 80) }}</p>
                                @endif
                                @if ($product['sales_link'])
                                    <a href="{{ $product['sales_link'] }}" target="_blank" rel="nofollow noopener" class="mp-cta-btn" style="margin-top:14px;">
                                        Beli / Pesan Sekarang
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($profile['description'])
            <section style="background:white;border-radius:16px;padding:24px;border:1px solid var(--mp-border);">
                <h2 style="font-size:16px;font-weight:700;margin-bottom:10px;">Tentang Kami</h2>
                <p style="font-size:15px;line-height:1.8;color:#374151;">{{ $profile['description'] }}</p>
            </section>
        @endif

        @if ($sections['show_productivity'] && $statistics)
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div style="background:white;border-radius:12px;padding:20px;text-align:center;border:1px solid var(--mp-border);">
                    <p style="font-size:26px;font-weight:800;color:var(--mp-green);">{{ $statistics['total_area_ha'] }} ha</p>
                    <p style="font-size:12px;color:#6b7280;margin-top:4px;">Total Lahan</p>
                </div>
                <div style="background:white;border-radius:12px;padding:20px;text-align:center;border:1px solid var(--mp-border);">
                    <p style="font-size:26px;font-weight:800;color:var(--mp-green);">{{ $statistics['total_seasons'] }}</p>
                    <p style="font-size:12px;color:#6b7280;margin-top:4px;">Musim Tanam</p>
                </div>
                @if ($statistics['latest_productivity'])
                    <div style="background:white;border-radius:12px;padding:20px;text-align:center;border:1px solid var(--mp-border);">
                        <p style="font-size:26px;font-weight:800;color:var(--mp-green);">{{ $statistics['latest_productivity'] }} t/ha</p>
                        <p style="font-size:12px;color:#6b7280;margin-top:4px;">Produktivitas</p>
                    </div>
                @endif
            </div>
        @endif

        @if ($sections['show_gallery'] && count($gallery) > 0)
            <section>
                <h2 style="font-size:18px;font-weight:800;margin-bottom:14px;">Galeri</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @foreach ($gallery as $img)
                        <div style="border-radius:10px;overflow:hidden;aspect-ratio:1;border:2px solid var(--mp-border);">
                            <img src="{{ asset('storage/'.$img->image_path) }}" alt="{{ $img->caption ?? 'Galeri' }}" style="width:100%;height:100%;object-fit:cover;">
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($sections['show_contact'] && $contact)
            <section style="background:var(--mp-black);border-radius:16px;padding:32px;text-align:center;">
                <h2 style="color:white;font-size:22px;font-weight:800;margin-bottom:8px;">Siap Bertransaksi?</h2>
                <p style="color:rgba(255,255,255,.6);font-size:14px;margin-bottom:20px;">Hubungi langsung untuk harga terbaik.</p>
                <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:10px;">
                    @if ($contact['whatsapp'])
                        <a href="{{ $contact['whatsapp'] }}" target="_blank" style="background:#16a34a;color:white;font-weight:700;font-size:14px;padding:12px 24px;border-radius:10px;text-decoration:none;">WhatsApp Sekarang</a>
                    @endif
                    @if ($contact['public_email'])
                        <a href="mailto:{{ $contact['public_email'] }}" style="background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:white;font-weight:600;font-size:14px;padding:12px 24px;border-radius:10px;text-decoration:none;">Email</a>
                    @endif
                </div>
            </section>
        @endif

    </div>

    <footer style="background:var(--mp-black);color:rgba(255,255,255,.4);font-size:12px;text-align:center;padding:14px;">
        Ditenagai <strong style="color:rgba(255,255,255,.7);">P.A.D.I.</strong>
    </footer>

</body>
</html>
