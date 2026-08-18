<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $profile['headline'] ?? 'Penghasil Beras Premium & Varietas Padi Pilihan' }} — {{ $profile['business_name'] }}">
    <meta name="robots" content="{{ $isPreview ? 'noindex, nofollow' : 'index, follow' }}">

    <title>{{ $profile['business_name'] }} &mdash; Penghasil Beras Pilihan</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --rice-green: #1a5c3a;
            --rice-green-dark: #0f3d26;
            --rice-green-light: #e8f5ed;
            --rice-gold: #c8a951;
            --rice-gold-light: #f5edd7;
            --rice-brown: #8b6914;
            --dark: #1a1a1a;
            --gray-800: #2d3748;
            --gray-600: #4a5568;
            --gray-400: #a0aec0;
            --gray-200: #e2e8f0;
            --gray-100: #f7fafc;
            --white: #ffffff;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.07);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --shadow-xl: 0 20px 25px rgba(0,0,0,0.15);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--white);
            color: var(--gray-800);
            line-height: 1.7;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* ===== Preview Bar ===== */
        .preview-bar {
            background: linear-gradient(135deg, #c8a951 0%, #b8983d 100%);
            color: #1a1a1a;
            padding: 12px 24px;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .preview-bar .btn-dashboard {
            background: #1a1a1a;
            color: #c8a951;
            padding: 8px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            font-size: 12px;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .preview-bar .btn-dashboard:hover {
            background: #333;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        /* ===== Top Bar ===== */
        .top-bar {
            background: var(--dark);
            color: rgba(255,255,255,0.8);
            font-size: 12px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .top-bar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .top-bar-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .top-bar-item i {
            color: var(--rice-gold);
            font-size: 14px;
        }

        /* ===== Navbar ===== */
        .navbar {
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(10px);
            border-bottom: 2px solid var(--gray-100);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: var(--transition);
        }

        .navbar.scrolled {
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-bottom-color: var(--rice-gold);
        }

        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 80px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
        }

        .brand-logo {
            width: 52px;
            height: 52px;
            border-radius: var(--radius-md);
            overflow: hidden;
            border: 2px solid var(--rice-green-light);
            background: var(--rice-green);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 24px;
            color: white;
            flex-shrink: 0;
            box-shadow: 0 4px 8px rgba(26,92,58,0.2);
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
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            font-weight: 800;
            color: var(--dark);
            letter-spacing: -0.5px;
            line-height: 1.2;
        }

        .brand-tagline {
            font-size: 11px;
            color: var(--rice-green);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 32px;
            list-style: none;
        }

        .nav-link {
            color: var(--gray-600);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            position: relative;
            padding: 8px 0;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: var(--rice-gold);
            transition: var(--transition);
            border-radius: 2px;
        }

        .nav-link:hover {
            color: var(--rice-green);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .nav-cta {
            background: linear-gradient(135deg, var(--rice-green) 0%, var(--rice-green-dark) 100%);
            color: white !important;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(26,92,58,0.3);
        }

        .nav-cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26,92,58,0.4);
        }

        /* ===== Hero ===== */
        .hero {
            position: relative;
            background: linear-gradient(135deg, #f8faf9 0%, #edf2ef 50%, #e8f0eb 100%);
            padding: 80px 0;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(200,169,81,0.1) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: -20%;
            left: -5%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(26,92,58,0.05) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero .container {
            position: relative;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 60px;
            align-items: center;
            z-index: 1;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(26,92,58,0.1);
            color: var(--rice-green);
            font-size: 13px;
            font-weight: 700;
            padding: 8px 16px;
            border-radius: 50px;
            margin-bottom: 24px;
            border: 1px solid rgba(26,92,58,0.2);
            backdrop-filter: blur(10px);
        }

        .hero-badge i {
            font-size: 14px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            font-weight: 800;
            color: var(--dark);
            line-height: 1.15;
            letter-spacing: -1.5px;
            margin-bottom: 20px;
        }

        .hero-subtitle {
            font-size: 18px;
            color: var(--rice-green);
            font-weight: 600;
            margin-bottom: 16px;
        }

        .hero-description {
            font-size: 16px;
            color: var(--gray-600);
            line-height: 1.8;
            margin-bottom: 32px;
            max-width: 560px;
        }

        .hero-actions {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 28px;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 0;
            height: 0;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            transition: var(--transition);
        }

        .btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--rice-green) 0%, var(--rice-green-dark) 100%);
            color: white;
            box-shadow: 0 8px 20px rgba(26,92,58,0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(26,92,58,0.4);
        }

        .btn-whatsapp {
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            color: white;
            box-shadow: 0 8px 20px rgba(37,211,102,0.3);
        }

        .btn-whatsapp:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(37,211,102,0.4);
        }

        .btn-outline {
            background: white;
            color: var(--gray-800);
            border: 2px solid var(--gray-200);
        }

        .btn-outline:hover {
            border-color: var(--rice-green);
            color: var(--rice-green);
            background: var(--rice-green-light);
        }

        .hero-image-wrapper {
            position: relative;
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-xl);
            border: 4px solid white;
            transform: rotate(2deg);
            transition: var(--transition);
        }

        .hero-image-wrapper:hover {
            transform: rotate(0deg) scale(1.02);
            box-shadow: 0 25px 40px rgba(0,0,0,0.2);
        }

        .hero-image-wrapper img {
            width: 100%;
            height: 450px;
            object-fit: cover;
            display: block;
        }

        .hero-image-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 100%);
            padding: 40px 24px 20px;
            color: white;
        }

        .hero-image-overlay span {
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ===== Stats Section ===== */
        .stats-section {
            background: white;
            padding: 60px 0;
            border-bottom: 1px solid var(--gray-100);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
        }

        .stat-card {
            background: linear-gradient(135deg, #f8faf9 0%, #ffffff 100%);
            border-radius: var(--radius-lg);
            padding: 32px 24px;
            text-align: center;
            transition: var(--transition);
            border: 1px solid var(--gray-200);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--rice-green) 0%, var(--rice-gold) 100%);
            transform: scaleX(0);
            transition: var(--transition);
        }

        .stat-card:hover {
            border-color: var(--rice-gold);
            box-shadow: var(--shadow-lg);
            transform: translateY(-4px);
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--rice-green-light);
            color: var(--rice-green);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 0 auto 16px;
            transition: var(--transition);
        }

        .stat-card:hover .stat-icon {
            background: var(--rice-green);
            color: white;
            transform: rotate(360deg);
        }

        .stat-value {
            font-size: 32px;
            font-weight: 900;
            color: var(--dark);
            line-height: 1;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 14px;
            color: var(--gray-600);
            font-weight: 600;
        }

        /* ===== Section Styles ===== */
        .section {
            padding: 80px 0;
        }

        .section-alt {
            background: var(--gray-100);
        }

        .section-header {
            text-align: center;
            margin-bottom: 48px;
        }

        .section-label {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: var(--rice-gold);
            margin-bottom: 12px;
            display: block;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            font-weight: 800;
            color: var(--dark);
            letter-spacing: -1px;
            margin-bottom: 16px;
        }

        .section-divider {
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--rice-green) 0%, var(--rice-gold) 100%);
            margin: 0 auto;
            border-radius: 2px;
        }

        /* ===== Products ===== */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 32px;
        }

        .product-card {
            background: white;
            border-radius: var(--radius-lg);
            overflow: hidden;
            border: 1px solid var(--gray-200);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .product-card:hover {
            box-shadow: var(--shadow-xl);
            transform: translateY(-8px);
            border-color: var(--rice-gold);
        }

        .product-image {
            height: 250px;
            position: relative;
            overflow: hidden;
            background: var(--gray-100);
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .product-card:hover .product-image img {
            transform: scale(1.1);
        }

        .product-badge {
            position: absolute;
            top: 16px;
            right: 16px;
            background: var(--rice-green);
            color: white;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 50px;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 2;
        }

        .product-content {
            padding: 24px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-name {
            font-size: 20px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 12px;
        }

        .product-price {
            font-size: 24px;
            font-weight: 900;
            color: var(--rice-green);
            margin-bottom: 12px;
        }

        .product-price span {
            font-size: 14px;
            font-weight: 500;
            color: var(--gray-400);
        }

        .product-desc {
            font-size: 14px;
            color: var(--gray-600);
            line-height: 1.6;
            margin-bottom: 20px;
            flex: 1;
        }

        .product-action {
            display: flex;
            gap: 12px;
        }

        .product-action .btn {
            flex: 1;
            justify-content: center;
            padding: 12px 20px;
            font-size: 13px;
        }

        /* ===== Table ===== */
        .table-wrapper {
            background: white;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
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
            background: linear-gradient(135deg, var(--dark) 0%, #2d2d2d 100%);
            color: white;
        }

        th {
            padding: 18px 20px;
            text-align: left;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        td {
            padding: 18px 20px;
            border-bottom: 1px solid var(--gray-100);
            color: var(--gray-600);
        }

        tbody tr {
            transition: var(--transition);
        }

        tbody tr:hover {
            background: var(--rice-green-light);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .quality-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 12px;
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

        /* ===== Gallery ===== */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
        }

        .gallery-item {
            position: relative;
            border-radius: var(--radius-md);
            overflow: hidden;
            aspect-ratio: 4/3;
            cursor: pointer;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            border: 2px solid transparent;
        }

        .gallery-item:hover {
            box-shadow: var(--shadow-xl);
            transform: scale(1.03);
            border-color: var(--rice-gold);
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .gallery-item:hover img {
            transform: scale(1.1);
        }

        .gallery-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, transparent 100%);
            color: white;
            font-size: 13px;
            font-weight: 600;
            padding: 40px 20px 16px;
            opacity: 0;
            transition: var(--transition);
        }

        .gallery-item:hover .gallery-caption {
            opacity: 1;
        }

        /* ===== Contact ===== */
        .contact-section {
            background: linear-gradient(135deg, var(--dark) 0%, #2d2d2d 100%);
            color: white;
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }

        .contact-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.05)"/></svg>');
            background-size: 50px 50px;
            pointer-events: none;
        }

        .contact-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: var(--radius-xl);
            padding: 60px;
            text-align: center;
            max-width: 700px;
            margin: 0 auto;
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 1;
        }

        .contact-title {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 16px;
            color: white;
        }

        .contact-desc {
            color: rgba(255,255,255,0.7);
            font-size: 16px;
            line-height: 1.7;
            margin-bottom: 32px;
        }

        .contact-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 32px;
        }

        .contact-actions .btn {
            padding: 16px 32px;
            font-size: 16px;
        }

        .contact-info {
            display: flex;
            justify-content: center;
            gap: 32px;
            flex-wrap: wrap;
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 32px;
            font-size: 14px;
            color: rgba(255,255,255,0.7);
        }

        .contact-info i {
            color: var(--rice-gold);
            margin-right: 8px;
        }

        /* ===== Footer ===== */
        .footer {
            background: var(--dark);
            color: rgba(255,255,255,0.7);
            padding: 48px 0;
            font-size: 13px;
        }

        .footer .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 24px;
        }

        .footer-brand {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            font-weight: 800;
            color: white;
            margin-bottom: 8px;
        }

        .footer-links {
            display: flex;
            gap: 24px;
        }

        .footer-links a {
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--rice-gold);
        }

        .footer-copy {
            color: rgba(255,255,255,0.4);
            font-size: 12px;
        }

        /* ===== Responsive ===== */
        @media (max-width: 992px) {
            .hero .container {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            
            .hero-title {
                font-size: 36px;
            }
            
            .hero-image-wrapper img {
                height: 350px;
            }
            
            .nav-menu {
                gap: 20px;
            }
            
            .nav-link {
                font-size: 13px;
            }
        }

        @media (max-width: 768px) {
            .navbar .container {
                height: auto;
                padding: 16px 24px;
                flex-direction: column;
                gap: 16px;
            }
            
            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
                gap: 16px;
            }
            
            .section {
                padding: 60px 0;
            }
            
            .section-title {
                font-size: 28px;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .contact-card {
                padding: 40px 24px;
            }
            
            .contact-title {
                font-size: 28px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .hero-actions {
                flex-direction: column;
            }
            
            .hero-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .hero-title {
                font-size: 28px;
            }
            
            .container {
                padding: 0 16px;
            }
            
            .footer .container {
                flex-direction: column;
                text-align: center;
            }
            
            .footer-links {
                flex-direction: column;
                gap: 12px;
            }
        }
    </style>
</head>
<body>

    {{-- Preview Bar --}}
    @if ($isPreview)
        <div class="preview-bar">
            <span><i class="fas fa-eye"></i> Mode Preview - Tampilan Publik Website Usaha</span>
            <a href="{{ route('farmer.website.index') }}" class="btn-dashboard">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    @endif

    {{-- Top Bar --}}
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-item">
                <i class="fas fa-certificate"></i>
                <span>Profil Usaha Tani Terverifikasi</span>
            </div>
            @if ($sections['show_location'] && !empty($location['address']))
                <div class="top-bar-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>{{ $location['address'] }}</span>
                </div>
            @endif
            @if ($sections['show_contact'] && !empty($contact['public_phone']))
                <div class="top-bar-item">
                    <i class="fas fa-headset"></i>
                    <span>Layanan: {{ $contact['public_phone'] }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Navbar --}}
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
                    <span class="brand-tagline">Penghasil Beras Premium</span>
                </div>
            </a>
            
            <ul class="nav-menu">
                @if ($sections['show_products'] && count($products) > 0)
                    <li><a href="#katalog" class="nav-link"><i class="fas fa-box"></i> Katalog</a></li>
                @endif
                @if ($sections['show_harvests'] && count($harvests) > 0)
                    <li><a href="#audit" class="nav-link"><i class="fas fa-chart-bar"></i> Data Panen</a></li>
                @endif
                @if ($sections['show_gallery'] && count($gallery) > 0)
                    <li><a href="#galeri" class="nav-link"><i class="fas fa-images"></i> Galeri</a></li>
                @endif
                @if ($sections['show_contact'] && $contact)
                    <li><a href="#kontak" class="nav-cta"><i class="fas fa-phone"></i> Hubungi Kami</a></li>
                @endif
            </ul>
        </div>
    </nav>

    {{-- Hero --}}
    <section class="hero" id="hero">
        <div class="container">
            <div>
                @if ($profile['is_verified'])
                    <div class="hero-badge">
                        <i class="fas fa-shield-alt"></i> Terverifikasi Resmi P.A.D.I.
                    </div>
                @endif
                
                <h1 class="hero-title">{{ $profile['business_name'] }}</h1>
                
                @if ($profile['headline'])
                    <p class="hero-subtitle">{{ $profile['headline'] }}</p>
                @endif
                
                <p class="hero-description">
                    {{ $profile['description'] ?? 'Menghasilkan beras premium dan varietas padi pilihan melalui praktik pertanian berkelanjutan. Berkomitmen pada kualitas terbaik untuk setiap butir beras yang dihasilkan.' }}
                </p>
                
                <div class="hero-actions">
                    @if ($sections['show_contact'] && !empty($contact['whatsapp']))
                        <a href="{{ $contact['whatsapp'] }}" target="_blank" class="btn btn-whatsapp">
                            <i class="fab fa-whatsapp"></i> Pesan via WhatsApp
                        </a>
                    @endif
                    @if ($sections['show_products'] && count($products) > 0)
                        <a href="#katalog" class="btn btn-outline">
                            <i class="fas fa-box-open"></i> Lihat Produk
                        </a>
                    @endif
                </div>
            </div>
            
            <div class="hero-image-wrapper">
                <img src="{{ $profile['cover_image_url'] }}" alt="{{ $profile['business_name'] }}">
                <div class="hero-image-overlay">
                    <span><i class="fas fa-leaf"></i> Hasil Pertanian Berkualitas Premium</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats --}}
    @if ($sections['show_productivity'] && $statistics)
        <section class="stats-section">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-ruler-combined"></i></div>
                        <div class="stat-value">{{ $statistics['total_area_ha'] }} <small style="font-size:16px;">Ha</small></div>
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
                            <div class="stat-value">{{ $statistics['latest_productivity'] }} <small style="font-size:16px;">t/ha</small></div>
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
            <section class="section" id="katalog">
                <div class="container">
                    <div class="section-header">
                        <span class="section-label">Katalog Produk</span>
                        <h2 class="section-title">Beras & Komoditas Unggulan</h2>
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
                                            <a href="{{ $product['sales_link'] }}" target="_blank" rel="nofollow" class="btn btn-primary">
                                                <i class="fas fa-shopping-cart"></i> Pesan
                                            </a>
                                        @elseif ($sections['show_contact'] && !empty($contact['whatsapp']))
                                            @php
                                                $waMsg = urlencode("Halo {$profile['business_name']}, saya tertarik dengan produk {$product['commodity']}. Mohon info detail dan ketersediaan.");
                                                $waUrl = $contact['whatsapp'] . (str_contains($contact['whatsapp'], '?') ? '&' : '?') . "text={$waMsg}";
                                            @endphp
                                            <a href="{{ $waUrl }}" target="_blank" class="btn btn-whatsapp">
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
            <section class="section section-alt" id="audit">
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
                                        <th><i class="fas fa-calendar"></i> Periode</th>
                                        <th><i class="fas fa-map"></i> Lahan</th>
                                        <th><i class="fas fa-seedling"></i> Varietas</th>
                                        <th style="text-align:right;"><i class="fas fa-weight"></i> Volume</th>
                                        <th style="text-align:center;"><i class="fas fa-award"></i> Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($harvests as $harvest)
                                        <tr>
                                            <td style="font-weight:600; color:var(--dark);">
                                                {{ \Carbon\Carbon::parse($harvest['harvest_date'])->translatedFormat('F Y') }}
                                            </td>
                                            <td>{{ $harvest['farm_name'] ?? 'Lahan Utama' }}</td>
                                            <td style="color:var(--rice-green); font-weight:600;">
                                                {{ $harvest['variety_name'] ?? 'Varietas Padi' }}
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
                        <span class="section-label" style="color:var(--rice-gold); display:block; margin-bottom:12px;">
                            <i class="fas fa-envelope"></i> Hubungi Kami
                        </span>
                        <h2 class="contact-title">Konsultasi & Pemesanan</h2>
                        <p class="contact-desc">
                            Dapatkan beras premium berkualitas terbaik dengan harga kompetitif. Tim kami siap melayani kebutuhan Anda.
                        </p>
                        
                        <div class="contact-actions">
                            @if (!empty($contact['whatsapp']))
                                <a href="{{ $contact['whatsapp'] }}" target="_blank" class="btn btn-whatsapp">
                                    <i class="fab fa-whatsapp"></i> WhatsApp Business
                                </a>
                            @endif
                            @if (!empty($contact['public_phone']))
                                <a href="tel:{{ $contact['public_phone'] }}" class="btn btn-outline" style="background:rgba(255,255,255,0.1); border-color:rgba(255,255,255,0.3); color:white;">
                                    <i class="fas fa-phone"></i> {{ $contact['public_phone'] }}
                                </a>
                            @endif
                            @if (!empty($contact['public_email']))
                                <a href="mailto:{{ $contact['public_email'] }}" class="btn btn-outline" style="background:rgba(255,255,255,0.1); border-color:rgba(255,255,255,0.3); color:white;">
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
                    Penghasil Beras Premium • Terverifikasi P.A.D.I.
                </div>
            </div>
            <div class="footer-links">
                <a href="#katalog"><i class="fas fa-box"></i> Produk</a>
                <a href="#audit"><i class="fas fa-chart-bar"></i> Data Panen</a>
                <a href="#galeri"><i class="fas fa-images"></i> Galeri</a>
                <a href="#kontak"><i class="fas fa-phone"></i> Kontak</a>
            </div>
            <div class="footer-copy">
                &copy; {{ date('Y') }} {{ $profile['business_name'] }}. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
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
        
        // Add loading animation
        document.addEventListener('DOMContentLoaded', function() {
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.5s ease';
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
        });
    </script>

</body>
</html>