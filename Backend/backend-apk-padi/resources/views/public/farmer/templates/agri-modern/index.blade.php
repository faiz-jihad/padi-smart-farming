<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="{{ $profile['headline'] ?? 'Profil Usaha Pertanian dan Distribusi Beras' }} — {{ $profile['business_name'] }}">
    <meta name="robots" content="{{ $isPreview ? 'noindex, nofollow' : 'index, follow' }}">
    <title>{{ $profile['business_name'] }} &mdash; Profil Usaha Pertanian</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font & Icons -->
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Lora:ital,wght@0,600;1,600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary: #1a5c3a;
            --primary-dark: #0f3d26;
            --primary-light: #e8f5ed;
            --accent: #c8a951;
            --accent-light: #f5edd7;
            --dark: #1a1a1a;
            --gray-800: #2d3748;
            --gray-600: #4a5568;
            --gray-400: #a0aec0;
            --gray-200: #e2e8f0;
            --gray-100: #f7fafc;
            --white: #ffffff;
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 16px;
            --radius-xl: 20px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--white);
            color: var(--gray-800);
            line-height: 1.7;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* ===== TOP BAR ===== */
        .topbar {
            background: var(--primary-dark);
            color: rgba(255, 255, 255, 0.9);
            font-size: 12px;
            padding: 8px 0;
            letter-spacing: 0.3px;
        }

        .topbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .topbar-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .topbar-item i {
            color: var(--accent);
            font-size: 13px;
        }

        /* ===== NAVBAR ===== */
        .navbar {
            background: var(--white);
            border-bottom: 2px solid var(--gray-100);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            box-shadow: var(--shadow-md);
            border-bottom-color: var(--gray-200);
        }

        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 76px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
        }

        .brand-logo {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-md);
            overflow: hidden;
            border: 2px solid var(--primary-light);
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 22px;
            color: white;
            flex-shrink: 0;
        }

        .brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .brand-text {
            display: flex;
            flex-direction: column;
        }

        .brand-name {
            font-size: 18px;
            font-weight: 800;
            color: var(--dark);
            letter-spacing: -0.5px;
            line-height: 1.2;
        }

        .brand-tag {
            font-size: 11px;
            color: var(--primary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 28px;
            list-style: none;
        }

        .nav-link {
            color: var(--gray-600);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
            position: relative;
            padding: 4px 0;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .nav-cta {
            background: var(--primary);
            color: white !important;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .nav-cta:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(26, 92, 58, 0.3);
        }

        /* ===== HERO ===== */
        .hero {
            position: relative;
            background: linear-gradient(135deg, #f8faf9 0%, #edf2ef 100%);
            padding: 60px 0;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(200, 169, 81, 0.08) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero .container {
            position: relative;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(26, 92, 58, 0.1);
            color: var(--primary);
            font-size: 13px;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 50px;
            margin-bottom: 20px;
            border: 1px solid rgba(26, 92, 58, 0.2);
        }

        .hero-badge i {
            font-size: 12px;
        }

        .hero-title {
            font-family: 'Lora', serif;
            font-size: 42px;
            font-weight: 700;
            color: var(--dark);
            line-height: 1.2;
            letter-spacing: -1px;
            margin-bottom: 16px;
        }

        .hero-subtitle {
            font-size: 16px;
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 12px;
        }

        .hero-description {
            font-size: 15px;
            color: var(--gray-600);
            line-height: 1.8;
            margin-bottom: 28px;
            max-width: 540px;
        }

        .hero-actions {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 13px 24px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26, 92, 58, 0.25);
        }

        .btn-accent {
            background: var(--accent);
            color: var(--dark);
        }

        .btn-accent:hover {
            background: #b8983d;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(200, 169, 81, 0.3);
        }

        .btn-outline {
            background: transparent;
            color: var(--gray-800);
            border: 2px solid var(--gray-200);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-light);
        }

        .hero-image-wrapper {
            position: relative;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
            border: 4px solid white;
        }

        .hero-image-wrapper img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            display: block;
        }

        .hero-image-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.7) 0%, transparent 100%);
            padding: 30px 20px 15px;
            color: white;
        }

        .hero-image-overlay span {
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* ===== STATS ===== */
        .stats-section {
            background: var(--white);
            padding: 40px 0;
            border-bottom: 1px solid var(--gray-100);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .stat-card {
            background: var(--gray-100);
            border-radius: var(--radius-md);
            padding: 24px;
            text-align: center;
            transition: all 0.3s;
            border: 1px solid transparent;
        }

        .stat-card:hover {
            background: var(--white);
            border-color: var(--gray-200);
            box-shadow: var(--shadow-md);
            transform: translateY(-4px);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin: 0 auto 12px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 800;
            color: var(--dark);
            line-height: 1;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 13px;
            color: var(--gray-600);
            font-weight: 600;
        }

        /* ===== SECTION HEADER ===== */
        .section {
            padding: 60px 0;
        }

        .section-alt {
            background: var(--gray-100);
        }

        .section-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-label {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--accent);
            margin-bottom: 8px;
            display: block;
        }

        .section-title {
            font-family: 'Lora', serif;
            font-size: 32px;
            font-weight: 700;
            color: var(--dark);
            letter-spacing: -0.5px;
        }

        .section-divider {
            width: 60px;
            height: 3px;
            background: var(--accent);
            margin: 16px auto 0;
            border-radius: 2px;
        }

        /* ===== PRODUCT CARDS ===== */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 25px;
        }

        .product-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            overflow: hidden;
            border: 1px solid var(--gray-200);
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-4px);
            border-color: var(--primary);
        }

        .product-image {
            height: 220px;
            position: relative;
            overflow: hidden;
            background: var(--gray-100);
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .product-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: var(--primary);
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 50px;
            letter-spacing: 0.5px;
        }

        .product-content {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-name {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .product-price {
            font-size: 22px;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 12px;
        }

        .product-price span {
            font-size: 13px;
            font-weight: 500;
            color: var(--gray-400);
        }

        .product-desc {
            font-size: 13px;
            color: var(--gray-600);
            line-height: 1.6;
            margin-bottom: 16px;
            flex: 1;
        }

        .product-action {
            display: flex;
            gap: 10px;
        }

        .product-action .btn {
            flex: 1;
            justify-content: center;
            padding: 10px 16px;
            font-size: 13px;
        }

        /* ===== TABLE ===== */
        .table-wrapper {
            background: var(--white);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .table-scroll {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        thead {
            background: var(--primary-dark);
            color: white;
        }

        th {
            padding: 16px 18px;
            text-align: left;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        td {
            padding: 16px 18px;
            border-bottom: 1px solid var(--gray-100);
            color: var(--gray-600);
        }

        tbody tr:hover {
            background: var(--gray-100);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .quality-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .quality-a {
            background: #fef3c7;
            color: #92400e;
        }

        .quality-b {
            background: #dbeafe;
            color: #1e40af;
        }

        /* ===== GALLERY ===== */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .gallery-item {
            position: relative;
            border-radius: var(--radius-md);
            overflow: hidden;
            aspect-ratio: 4/3;
            cursor: pointer;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s;
        }

        .gallery-item:hover {
            box-shadow: var(--shadow-lg);
            transform: scale(1.02);
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .gallery-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8) 0%, transparent 100%);
            color: white;
            font-size: 12px;
            font-weight: 600;
            padding: 30px 16px 12px;
        }

        /* ===== CONTACT ===== */
        .contact-section {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #1a3a2a 100%);
            color: white;
            padding: 60px 0;
        }

        .contact-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-xl);
            padding: 50px;
            text-align: center;
            max-width: 700px;
            margin: 0 auto;
            backdrop-filter: blur(10px);
        }

        .contact-title {
            font-family: 'Lora', serif;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .contact-desc {
            color: rgba(255, 255, 255, 0.7);
            font-size: 15px;
            line-height: 1.7;
            margin-bottom: 30px;
        }

        .contact-actions {
            display: flex;
            gap: 14px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .contact-actions .btn {
            padding: 14px 28px;
            font-size: 15px;
        }

        .btn-wa {
            background: #25D366;
            color: white;
        }

        .btn-wa:hover {
            background: #1eb955;
            box-shadow: 0 8px 20px rgba(37, 211, 102, 0.3);
        }

        .contact-info {
            display: flex;
            justify-content: center;
            gap: 24px;
            flex-wrap: wrap;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 24px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.7);
        }

        .contact-info i {
            color: var(--accent);
            margin-right: 6px;
        }

        /* ===== FOOTER ===== */
        .footer {
            background: var(--dark);
            color: rgba(255, 255, 255, 0.7);
            padding: 40px 0;
            font-size: 13px;
        }

        .footer .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .footer-brand {
            font-size: 18px;
            font-weight: 700;
            color: white;
            margin-bottom: 4px;
        }

        .footer-links {
            display: flex;
            gap: 20px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: white;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .hero .container {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .hero-title {
                font-size: 32px;
            }

            .hero-image-wrapper img {
                height: 300px;
            }

            .nav-menu {
                gap: 16px;
            }

            .nav-link {
                font-size: 13px;
            }
        }

        @media (max-width: 768px) {
            .navbar .container {
                height: auto;
                padding: 15px 20px;
                flex-direction: column;
                gap: 15px;
            }

            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
                gap: 12px;
            }

            .products-grid {
                grid-template-columns: 1fr;
            }

            .section-title {
                font-size: 26px;
            }

            .contact-card {
                padding: 30px 20px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .hero-actions {
                flex-direction: column;
            }

            .hero-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>

    {{-- Preview Mode Banner --}}
    @if ($isPreview)
        <div
            style="background: #c8a951; color: #1a1a1a; padding: 12px 20px; font-size: 13px; font-weight: 700; text-align: center; display: flex; align-items: center; justify-content: center; gap: 16px; flex-wrap: wrap;">
            <span><i class="fas fa-eye"></i>&nbsp; Mode Preview - Ini adalah tampilan publik website usaha Anda</span>
            <a href="{{ route('farmer.website.index') }}"
                style="background: #1a1a1a; color: #c8a951; padding: 6px 16px; border-radius: 6px; text-decoration: none; font-weight: 700; transition: all 0.3s;">
                <i class="fas fa-arrow-left"></i>&nbsp; Kembali ke Dashboard
            </a>
        </div>
    @endif

    {{-- Top Bar --}}
    <div class="topbar">
        <div class="container">
            <div class="topbar-item">
                <i class="fas fa-certificate"></i>
                <span>Profil Usaha Tani Terverifikasi</span>
            </div>
            @if ($sections['show_location'] && !empty($location['address']))
                <div class="topbar-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>{{ $location['address'] }}</span>
                </div>
            @endif
            @if ($sections['show_contact'] && !empty($contact['public_phone']))
                <div class="topbar-item">
                    <i class="fas fa-headset"></i>
                    <span>Layanan: {{ $contact['public_phone'] }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="navbar" id="navbar">
        <div class="container">
            <a href="#" class="brand">
                @if ($profile['logo_url'])
                    <div class="brand-logo">
                        <img src="{{ $profile['logo_url'] }}" alt="{{ $profile['business_name'] }}">
                    </div>
                @else
                    <div class="brand-logo">
                        {{ strtoupper(substr($profile['business_name'], 0, 1)) }}
                    </div>
                @endif
                <div class="brand-text">
                    <span class="brand-name">{{ $profile['business_name'] }}</span>
                    <span class="brand-tag">Distributor Hasil Tani</span>
                </div>
            </a>

            <ul class="nav-menu">
                @if ($sections['show_products'] && count($products) > 0)
                    <li><a href="#produk" class="nav-link">Produk</a></li>
                @endif
                @if ($sections['show_harvests'] && count($harvests) > 0)
                    <li><a href="#panen" class="nav-link">Data Panen</a></li>
                @endif
                @if ($sections['show_gallery'] && count($gallery) > 0)
                    <li><a href="#galeri" class="nav-link">Galeri</a></li>
                @endif
                @if ($sections['show_contact'] && $contact)
                    <li><a href="#kontak" class="nav-cta">Konsultasi Gratis</a></li>
                @endif
            </ul>
        </div>
    </nav>

    {{-- Hero --}}
    <section class="hero">
        <div class="container">
            <div>
                @if ($profile['is_verified'])
                    <div class="hero-badge">
                        <i class="fas fa-shield-alt"></i> Terverifikasi Resmi
                    </div>
                @endif

                <h1 class="hero-title">{{ $profile['business_name'] }}</h1>

                @if ($profile['headline'])
                    <p class="hero-subtitle">{{ $profile['headline'] }}</p>
                @endif

                <p class="hero-description">
                    {{ $profile['description'] ?? 'Menyediakan hasil pertanian berkualitas premium langsung dari lahan terpercaya. Fokus pada transparansi proses dan kepuasan pelanggan jangka panjang.' }}
                </p>

                <div class="hero-actions">
                    @if ($sections['show_contact'] && !empty($contact['whatsapp']))
                        <a href="{{ $contact['whatsapp'] }}" target="_blank" class="btn btn-wa">
                            <i class="fab fa-whatsapp"></i> Chat via WhatsApp
                        </a>
                    @endif
                    @if ($sections['show_products'] && count($products) > 0)
                        <a href="#produk" class="btn btn-outline">
                            <i class="fas fa-box"></i> Lihat Katalog
                        </a>
                    @endif
                </div>
            </div>

            <div class="hero-image-wrapper">
                <img src="{{ $profile['cover_image_url'] }}" alt="{{ $profile['business_name'] }}">
                <div class="hero-image-overlay">
                    <span><i class="fas fa-leaf"></i>&nbsp; Hasil Pertanian Berkualitas</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Statistics --}}
    @if ($sections['show_productivity'] && $statistics)
        <section class="stats-section">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-ruler-combined"></i></div>
                        <div class="stat-value">{{ $statistics['total_area_ha'] }} <small style="font-size:14px;">Ha</small>
                        </div>
                        <div class="stat-label">Total Luas Lahan</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
                        <div class="stat-value">{{ $statistics['total_seasons'] }}</div>
                        <div class="stat-label">Musim Tanam</div>
                    </div>
                    @if ($statistics['latest_productivity'])
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                            <div class="stat-value">{{ $statistics['latest_productivity'] }} <small
                                    style="font-size:14px;">t/ha</small></div>
                            <div class="stat-label">Produktivitas</div>
                        </div>
                    @endif
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-truck"></i></div>
                        <div class="stat-value">24/7</div>
                        <div class="stat-label">Siap Distribusi</div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <main>
        {{-- Products --}}
        @if ($sections['show_products'] && count($products) > 0)
            <section class="section" id="produk">
                <div class="container">
                    <div class="section-header">
                        <span class="section-label">Katalog</span>
                        <h2 class="section-title">Hasil Pertanian Unggulan</h2>
                        <div class="section-divider"></div>
                    </div>

                    <div class="products-grid">
                        @foreach ($products as $product)
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="{{ $product['image_url'] }}" alt="{{ $product['commodity'] }}">
                                    <div class="product-badge">Stok: {{ $product['quantity'] }} {{ $product['unit'] }}</div>
                                </div>
                                <div class="product-content">
                                    <h3 class="product-name">{{ $product['commodity'] }}</h3>
                                    @if ($product['price_per_unit'])
                                        <div class="product-price">
                                            Rp {{ number_format($product['price_per_unit'], 0, ',', '.') }}
                                            <span>/ {{ $product['unit'] }}</span>
                                        </div>
                                    @endif
                                    @if ($product['description'])
                                        <p class="product-desc">{{ $product['description'] }}</p>
                                    @endif
                                    <div class="product-action">
                                        @if ($product['sales_link'])
                                            <a href="{{ $product['sales_link'] }}" target="_blank" rel="nofollow"
                                                class="btn btn-primary">
                                                <i class="fas fa-shopping-cart"></i> Pesan
                                            </a>
                                        @elseif ($sections['show_contact'] && !empty($contact['whatsapp']))
                                            @php
                                                $waMsg = urlencode("Halo {$profile['business_name']}, saya tertarik dengan produk {$product['commodity']}. Mohon info detail dan ketersediaan.");
                                                $waUrl = $contact['whatsapp'] . (str_contains($contact['whatsapp'], '?') ? '&' : '?') . "text={$waMsg}";
                                            @endphp
                                            <a href="{{ $waUrl }}" target="_blank" class="btn btn-primary">
                                                <i class="fab fa-whatsapp"></i> Tanya Produk
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- Harvest Data --}}
        @if ($sections['show_harvests'] && count($harvests) > 0)
            <section class="section section-alt" id="panen">
                <div class="container">
                    <div class="section-header">
                        <span class="section-label">Transparansi</span>
                        <h2 class="section-title">Rekam Data Panen</h2>
                        <div class="section-divider"></div>
                    </div>

                    <div class="table-wrapper">
                        <div class="table-scroll">
                            <table>
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-calendar"></i>&nbsp; Periode</th>
                                        <th><i class="fas fa-map"></i>&nbsp; Lahan</th>
                                        <th><i class="fas fa-seedling"></i>&nbsp; Varietas</th>
                                        <th style="text-align:right;"><i class="fas fa-weight"></i>&nbsp; Volume</th>
                                        <th style="text-align:center;"><i class="fas fa-award"></i>&nbsp; Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($harvests as $harvest)
                                        <tr>
                                            <td style="font-weight:600; color:var(--dark);">
                                                {{ \Carbon\Carbon::parse($harvest['harvest_date'])->translatedFormat('F Y') }}
                                            </td>
                                            <td>{{ $harvest['farm_name'] ?? 'Lahan Utama' }}</td>
                                            <td style="color:var(--primary); font-weight:600;">
                                                {{ $harvest['variety_name'] ?? 'Varietas Unggul' }}
                                            </td>
                                            <td style="text-align:right; font-weight:700;">
                                                {{ number_format($harvest['quantity'], 1, ',', '.') }} {{ $harvest['unit'] }}
                                            </td>
                                            <td style="text-align:center;">
                                                @php
                                                    $grade = $harvest['quality_grade'] ?? 'A';
                                                    $gradeClass = ($grade == 'A' || $grade == 'Premium') ? 'quality-a' : 'quality-b';
                                                @endphp
                                                <span class="quality-badge {{ $gradeClass }}">{{ $grade }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- Gallery --}}
        @if ($sections['show_gallery'] && count($gallery) > 0)
            <section class="section" id="galeri">
                <div class="container">
                    <div class="section-header">
                        <span class="section-label">Dokumentasi</span>
                        <h2 class="section-title">Galeri Lapangan</h2>
                        <div class="section-divider"></div>
                    </div>

                    <div class="gallery-grid">
                        @foreach ($gallery as $item)
                            @php
                                $imgSrc = is_array($item) ? $item['image_url'] : asset('storage/' . $item->image_path);
                                $cap = is_array($item) ? ($item['caption'] ?? '') : ($item->caption ?? '');
                            @endphp
                            <div class="gallery-item">
                                <img src="{{ $imgSrc }}" alt="{{ $cap ?: 'Galeri Usaha Tani' }}" loading="lazy">
                                @if ($cap)
                                    <div class="gallery-caption">{{ $cap }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- Contact --}}
        @if ($sections['show_contact'] && $contact)
            <section class="contact-section" id="kontak">
                <div class="container">
                    <div class="contact-card">
                        <span class="section-label" style="color:var(--accent); display:block; margin-bottom:10px;">
                            <i class="fas fa-envelope"></i>&nbsp; Hubungi Kami
                        </span>
                        <h2 class="contact-title">Konsultasi & Pemesanan</h2>
                        <p class="contact-desc">
                            Dapatkan hasil pertanian berkualitas premium dengan harga kompetitif. Tim kami siap membantu
                            kebutuhan Anda.
                        </p>

                        <div class="contact-actions">
                            @if (!empty($contact['whatsapp']))
                                <a href="{{ $contact['whatsapp'] }}" target="_blank" class="btn btn-wa">
                                    <i class="fab fa-whatsapp"></i> WhatsApp Business
                                </a>
                            @endif
                            @if (!empty($contact['public_phone']))
                                <a href="tel:{{ $contact['public_phone'] }}" class="btn btn-outline">
                                    <i class="fas fa-phone"></i> {{ $contact['public_phone'] }}
                                </a>
                            @endif
                            @if (!empty($contact['public_email']))
                                <a href="mailto:{{ $contact['public_email'] }}" class="btn btn-outline">
                                    <i class="fas fa-at"></i> Email
                                </a>
                            @endif
                        </div>

                        <div class="contact-info">
                            @if ($sections['show_location'] && !empty($location['address']))
                                <span><i class="fas fa-location-dot"></i> {{ $location['address'] }}</span>
                            @endif
                            <span><i class="fas fa-clock"></i> Buka Setiap Hari 06:00-20:00</span>
                        </div>
                    </div>
                </div>
            </section>
        @endif
    </main>

    {{-- Footer --}}
    <footer class="footer">
        <div class="container">
            <div>
                <div class="footer-brand">{{ $profile['business_name'] }}</div>
                <div style="color: rgba(255,255,255,0.5); font-size:12px;">
                    Profil Usaha Pertanian Terverifikasi • Ekosistem P.A.D.I.
                </div>
            </div>
            <div class="footer-links">
                <a href="#produk">Produk</a>
                <a href="#panen">Data Panen</a>
                <a href="#galeri">Galeri</a>
                <a href="#kontak">Kontak</a>
            </div>
            <div style="color: rgba(255,255,255,0.4); font-size:12px;">
                &copy; {{ date('Y') }} {{ $profile['business_name'] }}. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function () {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>

</body>

</html>