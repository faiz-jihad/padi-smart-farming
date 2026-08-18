<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="{{ $profile['headline'] ?? 'Profil Usaha Pertanian dan Pasokan Hasil Panen Padi Berkualitas' }} — {{ $profile['business_name'] }}">
    <meta name="robots" content="{{ $isPreview ? 'noindex, nofollow' : 'index, follow' }}">
    <title>{{ $profile['business_name'] }} &mdash; Pemasok Pertanian Terverifikasi</title>

    @if (!empty($profile['logo_url']))
        <link rel="icon" type="image/png" href="{{ $profile['logo_url'] }}">
        <link rel="shortcut icon" href="{{ $profile['logo_url'] }}">
        <link rel="apple-touch-icon" href="{{ $profile['logo_url'] }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* ==========================================================================
           1. DESIGN TOKENS & RESET
           ========================================================================== */
        :root {
            --forest: #143D2B;
            --forest-dark: #0B281B;
            --forest-light: #1E523B;
            --forest-card: #123726;
            --forest-border: rgba(255, 255, 255, 0.12);
            --lime: #B7FF00;
            --lime-soft: #D7FF73;
            --lime-glow: rgba(183, 255, 0, 0.35);
            --white: #FFFFFF;
            --off-white: #F6F7F4;
            --surface-alt: #EEF1EC;
            --black: #101713;
            --muted: #68716B;
            --muted-light: #9DA6A0;
            --border: #E6EAE5;
            --border-subtle: #EDF1EC;

            --radius-xs: 8px;
            --radius-sm: 12px;
            --radius-md: 18px;
            --radius-lg: 24px;
            --radius-xl: 32px;
            --radius-full: 9999px;

            --shadow-xs: 0 1px 3px rgba(16, 23, 19, 0.04);
            --shadow-sm: 0 4px 14px rgba(16, 23, 19, 0.05);
            --shadow-md: 0 12px 30px rgba(16, 23, 19, 0.08);
            --shadow-lg: 0 20px 48px rgba(16, 23, 19, 0.12);
            --shadow-lime: 0 10px 28px rgba(183, 255, 0, 0.25);

            --transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            --transition-slow: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            font-size: 16px;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--white);
            color: var(--black);
            line-height: 1.65;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
        }

        img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button {
            font-family: inherit;
            border: none;
            background: transparent;
            cursor: pointer;
        }

        /* Accessibility Focus */
        :focus-visible {
            outline: 2px solid var(--lime);
            outline-offset: 3px;
        }

        /* Container Layout */
        .container {
            width: 100%;
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* ==========================================================================
           2. BUTTONS & BADGES
           ========================================================================== */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 13px 26px;
            font-size: 14px;
            font-weight: 700;
            border-radius: var(--radius-full);
            transition: var(--transition);
            cursor: pointer;
            text-align: center;
            white-space: nowrap;
            letter-spacing: -0.2px;
        }

        .btn i {
            transition: transform 0.25s ease;
            font-size: 14px;
        }

        .btn:hover i.fa-arrow-right,
        .btn:hover i.fa-arrow-up-right-from-square {
            transform: translateX(4px);
        }

        .btn--lime {
            background-color: var(--lime);
            color: var(--forest-dark);
            box-shadow: var(--shadow-lime);
        }

        .btn--lime:hover {
            background-color: var(--lime-soft);
            transform: translateY(-2px);
            box-shadow: 0 14px 32px rgba(183, 255, 0, 0.4);
            color: var(--forest-dark);
        }

        .btn--forest {
            background-color: var(--forest);
            color: var(--white);
        }

        .btn--forest:hover {
            background-color: var(--forest-dark);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn--outline-dark {
            background: transparent;
            color: var(--forest-dark);
            border: 1.5px solid var(--border);
        }

        .btn--outline-dark:hover {
            border-color: var(--forest);
            background-color: var(--off-white);
            color: var(--forest);
            transform: translateY(-2px);
        }

        .btn--outline-light {
            background: rgba(255, 255, 255, 0.08);
            color: var(--white);
            border: 1.5px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
        }

        .btn--outline-light:hover {
            background: rgba(255, 255, 255, 0.16);
            border-color: var(--lime);
            color: var(--lime);
            transform: translateY(-2px);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: var(--radius-full);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .badge--pill-forest {
            background-color: rgba(20, 61, 43, 0.08);
            color: var(--forest);
            border: 1px solid rgba(20, 61, 43, 0.15);
        }

        .section-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--forest);
            margin-bottom: 12px;
        }

        .section-eyebrow--light {
            color: var(--lime);
        }

        .section-eyebrow::before {
            content: "";
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: currentColor;
        }

        .section-heading {
            font-size: clamp(1.75rem, 3.5vw, 2.75rem);
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.03em;
            color: var(--forest-dark);
            margin-bottom: 16px;
        }

        .section-heading--light {
            color: var(--white);
        }

        .section-subheading {
            font-size: clamp(0.95rem, 1.5vw, 1.125rem);
            color: var(--muted);
            line-height: 1.7;
            max-width: 600px;
        }

        .section-subheading--light {
            color: rgba(255, 255, 255, 0.75);
        }

        /* ==========================================================================
           3. PREVIEW BAR
           ========================================================================== */
        .preview-banner {
            position: sticky;
            top: 0;
            z-index: 1100;
            background: var(--forest-dark);
            color: var(--white);
            border-bottom: 2px solid var(--lime);
            padding: 10px 24px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
        }

        .preview-banner__inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .preview-banner__info {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .preview-banner__badge {
            background: var(--lime);
            color: var(--forest-dark);
            font-size: 11px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .preview-banner__btn {
            background: rgba(255, 255, 255, 0.12);
            color: var(--white);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 6px 16px;
            border-radius: var(--radius-full);
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }

        .preview-banner__btn:hover {
            background: var(--lime);
            color: var(--forest-dark);
            border-color: var(--lime);
        }

        /* ==========================================================================
           4. TOP CONTACT BAR
           ========================================================================== */
        .top-contact-bar {
            background-color: var(--forest-dark);
            color: rgba(255, 255, 255, 0.85);
            font-size: 12.5px;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            letter-spacing: -0.1px;
        }

        .top-contact-bar__inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .top-contact-bar__list {
            display: flex;
            align-items: center;
            gap: 22px;
            flex-wrap: wrap;
            list-style: none;
        }

        .top-contact-bar__item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: color 0.2s ease;
        }

        .top-contact-bar__item i {
            color: var(--lime);
            font-size: 13px;
        }

        .top-contact-bar__item a:hover {
            color: var(--lime-soft);
        }

        .top-contact-bar__status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 600;
            color: var(--lime-soft);
        }

        .top-contact-bar__status-dot {
            width: 7px;
            height: 7px;
            background-color: var(--lime);
            border-radius: 50%;
            box-shadow: 0 0 8px var(--lime);
            animation: pulse-dot 2s infinite ease-in-out;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.8); }
        }

        /* ==========================================================================
           5. NAVBAR
           ========================================================================== */
        .site-header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background-color: var(--white);
            border-bottom: 1px solid var(--border-subtle);
            transition: var(--transition);
        }

        .site-header.scrolled {
            background-color: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom-color: var(--border);
            box-shadow: var(--shadow-sm);
        }

        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 80px;
            gap: 20px;
        }

        .navbar__brand {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            flex-shrink: 0;
        }

        .navbar__logo {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-sm);
            background: var(--forest);
            color: var(--lime);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 800;
            overflow: hidden;
            border: 2px solid var(--off-white);
            box-shadow: var(--shadow-xs);
            flex-shrink: 0;
        }

        .navbar__logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .navbar__brand-info {
            display: flex;
            flex-direction: column;
        }

        .navbar__brand-name {
            font-size: 17px;
            font-weight: 800;
            color: var(--forest-dark);
            letter-spacing: -0.4px;
            line-height: 1.2;
        }

        .navbar__brand-tagline {
            font-size: 11px;
            font-weight: 700;
            color: var(--forest);
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .navbar__nav {
            display: flex;
            align-items: center;
            gap: 32px;
            list-style: none;
        }

        .navbar__link {
            font-size: 14.5px;
            font-weight: 600;
            color: var(--muted);
            transition: color 0.2s ease;
            position: relative;
            padding: 6px 0;
        }

        .navbar__link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--forest);
            transition: width 0.25s ease;
            border-radius: 2px;
        }

        .navbar__link:hover {
            color: var(--forest-dark);
        }

        .navbar__link:hover::after {
            width: 100%;
        }

        .navbar__actions {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .navbar__toggle {
            display: none;
            width: 44px;
            height: 44px;
            border-radius: var(--radius-sm);
            background: var(--off-white);
            color: var(--forest-dark);
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: var(--transition);
        }

        .navbar__toggle:hover {
            background: var(--surface-alt);
        }

        /* Mobile Drawer */
        .mobile-drawer {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(11, 40, 27, 0.6);
            backdrop-filter: blur(8px);
            z-index: 2000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .mobile-drawer.is-open {
            opacity: 1;
            pointer-events: auto;
        }

        .mobile-drawer__panel {
            position: absolute;
            top: 0;
            right: 0;
            width: 85%;
            max-width: 380px;
            height: 100%;
            background: var(--white);
            padding: 30px 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: -10px 0 30px rgba(0, 0, 0, 0.2);
            transform: translateX(100%);
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            overflow-y: auto;
        }

        .mobile-drawer.is-open .mobile-drawer__panel {
            transform: translateX(0);
        }

        .mobile-drawer__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }

        .mobile-drawer__close {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--off-white);
            color: var(--forest-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .mobile-drawer__nav {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .mobile-drawer__link {
            font-size: 17px;
            font-weight: 700;
            color: var(--forest-dark);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 0;
        }

        .mobile-drawer__link i {
            font-size: 14px;
            color: var(--muted-light);
        }

        .mobile-drawer__footer {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
        }

        /* ==========================================================================
           6. HERO SECTION
           ========================================================================== */
        .hero {
            position: relative;
            background-color: var(--off-white);
            padding: clamp(60px, 8vw, 100px) 0;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -10%;
            right: -5%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(183, 255, 0, 0.12) 0%, rgba(20, 61, 43, 0.03) 50%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero__grid {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: clamp(32px, 5vw, 64px);
            align-items: center;
        }

        .hero__content {
            position: relative;
            z-index: 2;
        }

        .hero__badge-row {
            margin-bottom: 20px;
        }

        .hero__title {
            font-size: clamp(2.25rem, 5.2vw, 3.75rem);
            font-weight: 800;
            line-height: 1.12;
            letter-spacing: -0.035em;
            color: var(--forest-dark);
            margin-bottom: 20px;
        }

        .hero__headline {
            font-size: clamp(1.1rem, 2vw, 1.35rem);
            font-weight: 700;
            color: var(--forest);
            line-height: 1.45;
            margin-bottom: 18px;
            letter-spacing: -0.01em;
        }

        .hero__description {
            font-size: clamp(0.95rem, 1.4vw, 1.05rem);
            color: var(--muted);
            line-height: 1.75;
            margin-bottom: 36px;
            max-width: 560px;
        }

        .hero__actions {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        /* Hero Media / Editorial Collage */
        .hero__media {
            position: relative;
            z-index: 1;
        }

        .hero__collage {
            position: relative;
            padding: 20px 0 20px 20px;
        }

        .hero__main-image-wrap {
            position: relative;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: 5px solid var(--white);
            aspect-ratio: 4 / 3.4;
            background-color: var(--surface-alt);
        }

        .hero__main-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition-slow);
        }

        .hero__collage:hover .hero__main-image {
            transform: scale(1.03);
        }

        .hero__secondary-card {
            position: absolute;
            bottom: -15px;
            left: -15px;
            background: var(--forest-dark);
            color: var(--white);
            padding: 16px 20px;
            border-radius: var(--radius-md);
            border: 2px solid var(--lime);
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            gap: 14px;
            max-width: 260px;
            backdrop-filter: blur(10px);
            z-index: 3;
        }

        .hero__secondary-card-icon {
            width: 44px;
            height: 44px;
            border-radius: var(--radius-sm);
            background: var(--lime);
            color: var(--forest-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .hero__secondary-card-text h4 {
            font-size: 13px;
            font-weight: 800;
            color: var(--white);
            margin-bottom: 2px;
        }

        .hero__secondary-card-text p {
            font-size: 11px;
            color: var(--lime-soft);
            line-height: 1.3;
        }

        .hero__floating-shape {
            position: absolute;
            top: -10px;
            right: -10px;
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: var(--lime);
            opacity: 0.9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--forest-dark);
            font-size: 24px;
            box-shadow: var(--shadow-lime);
            z-index: 3;
            animation: float-shape 4s ease-in-out infinite alternate;
        }

        @keyframes float-shape {
            0% { transform: translateY(0px) rotate(0deg); }
            100% { transform: translateY(-8px) rotate(8deg); }
        }

        /* ==========================================================================
           7. AGRICULTURE MARQUEE
           ========================================================================== */
        .marquee-strip {
            background-color: var(--forest-dark);
            color: var(--white);
            padding: 18px 0;
            overflow: hidden;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            position: relative;
        }

        .marquee-strip__track {
            display: flex;
            width: max-content;
            animation: marquee 32s linear infinite;
        }

        .marquee-strip__content {
            display: flex;
            align-items: center;
            gap: 40px;
            padding-right: 40px;
            font-size: 13.5px;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .marquee-strip__content span.star {
            color: var(--lime);
            font-size: 16px;
        }

        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        @media (prefers-reduced-motion: reduce) {
            .marquee-strip__track {
                animation: none;
                overflow-x: auto;
            }
        }

        /* ==========================================================================
           8. ABOUT / COMPANY PROFILE
           ========================================================================== */
        .about-section {
            padding: clamp(70px, 9vw, 120px) 0;
            background-color: var(--white);
        }

        .about__grid {
            display: grid;
            grid-template-columns: 0.9fr 1.1fr;
            gap: clamp(36px, 6vw, 72px);
            align-items: center;
        }

        .about__media-grid {
            position: relative;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .about__photo {
            border-radius: var(--radius-md);
            overflow: hidden;
            background-color: var(--surface-alt);
            box-shadow: var(--shadow-sm);
        }

        .about__photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition-slow);
        }

        .about__photo:hover img {
            transform: scale(1.05);
        }

        .about__photo--tall {
            grid-row: span 2;
            aspect-ratio: 3/4;
            border-radius: var(--radius-lg);
        }

        .about__photo--square {
            aspect-ratio: 1/1;
        }

        .about__content {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .about__lead {
            font-size: clamp(1rem, 1.6vw, 1.15rem);
            color: var(--forest-dark);
            line-height: 1.8;
            font-weight: 500;
            margin-bottom: 24px;
        }

        .about__meta-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-bottom: 32px;
            list-style: none;
        }

        .about__meta-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            font-size: 14.5px;
            color: var(--muted);
        }

        .about__meta-icon {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            background: rgba(20, 61, 43, 0.06);
            color: var(--forest);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        /* ==========================================================================
           9. STATISTICS STRIP
           ========================================================================== */
        .stats-section {
            background-color: var(--off-white);
            padding: 50px 0;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        .stats-strip {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
            align-items: stretch;
        }

        .stat-card {
            background: var(--white);
            padding: 28px 24px;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-color: var(--forest);
        }

        .stat-card__icon {
            font-size: 20px;
            color: var(--forest);
            margin-bottom: 12px;
        }

        .stat-card__value {
            font-size: clamp(2rem, 3.5vw, 2.75rem);
            font-weight: 900;
            color: var(--forest-dark);
            line-height: 1;
            margin-bottom: 6px;
            letter-spacing: -0.03em;
        }

        .stat-card__value small {
            font-size: 16px;
            font-weight: 700;
            color: var(--forest);
            margin-left: 4px;
        }

        .stat-card__label {
            font-size: 13px;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        /* ==========================================================================
           10. PRODUCTS SECTION
           ========================================================================== */
        .products-section {
            background-color: var(--forest-dark);
            color: var(--white);
            padding: clamp(70px, 9vw, 120px) 0;
            position: relative;
        }

        .products-section__header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 50px;
            flex-wrap: wrap;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 28px;
        }

        .product-card {
            background-color: var(--forest-card);
            border: 1px solid var(--forest-border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: var(--transition);
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-6px);
            border-color: var(--lime);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
        }

        .product-card--featured {
            border-color: rgba(183, 255, 0, 0.4);
            box-shadow: 0 0 30px rgba(183, 255, 0, 0.08);
        }

        .product-card__image-wrap {
            position: relative;
            width: 100%;
            height: 230px;
            background-color: rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .product-card__image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition-slow);
        }

        .product-card:hover .product-card__image {
            transform: scale(1.06);
        }

        .product-card__badge {
            position: absolute;
            top: 14px;
            right: 14px;
            background: rgba(11, 40, 27, 0.85);
            color: var(--lime);
            border: 1px solid rgba(183, 255, 0, 0.3);
            backdrop-filter: blur(8px);
            padding: 5px 12px;
            border-radius: var(--radius-full);
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .product-card__featured-tag {
            position: absolute;
            top: 14px;
            left: 14px;
            background: var(--lime);
            color: var(--forest-dark);
            padding: 4px 10px;
            border-radius: var(--radius-full);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .product-card__body {
            padding: 24px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .product-card__title {
            font-size: 19px;
            font-weight: 800;
            color: var(--white);
            margin-bottom: 8px;
            letter-spacing: -0.3px;
        }

        .product-card__price-wrap {
            display: flex;
            align-items: baseline;
            gap: 6px;
            margin-bottom: 14px;
        }

        .product-card__price {
            font-size: 22px;
            font-weight: 900;
            color: var(--lime);
            letter-spacing: -0.5px;
        }

        .product-card__unit {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            font-weight: 500;
        }

        .product-card__description {
            font-size: 13.5px;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.6;
            margin-bottom: 24px;
            flex-grow: 1;
        }

        .product-card__actions {
            margin-top: auto;
        }

        .product-card__actions .btn {
            width: 100%;
        }

        /* ==========================================================================
           11. HARVEST HISTORY (TIMELINE / CARDS)
           ========================================================================== */
        .harvest-section {
            background-color: var(--off-white);
            padding: clamp(70px, 9vw, 120px) 0;
        }

        .harvest-section__header {
            margin-bottom: 48px;
        }

        .harvest-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
            gap: 20px;
        }

        .harvest-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 24px;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        .harvest-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-color: var(--forest);
        }

        .harvest-card__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border-subtle);
        }

        .harvest-card__date {
            font-size: 13px;
            font-weight: 700;
            color: var(--forest);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .harvest-card__grade {
            padding: 3px 10px;
            border-radius: var(--radius-full);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .harvest-card__grade--premium {
            background-color: rgba(183, 255, 0, 0.2);
            color: var(--forest-dark);
            border: 1px solid rgba(183, 255, 0, 0.5);
        }

        .harvest-card__grade--standard {
            background-color: rgba(20, 61, 43, 0.08);
            color: var(--forest);
            border: 1px solid rgba(20, 61, 43, 0.15);
        }

        .harvest-card__variety {
            font-size: 17px;
            font-weight: 800;
            color: var(--forest-dark);
            margin-bottom: 6px;
        }

        .harvest-card__farm {
            font-size: 13px;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 18px;
        }

        .harvest-card__quantity {
            background-color: var(--off-white);
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .harvest-card__quantity-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--muted);
        }

        .harvest-card__quantity-value {
            font-size: 16px;
            font-weight: 800;
            color: var(--forest-dark);
        }

        /* ==========================================================================
           12. GALLERY SECTION
           ========================================================================== */
        .gallery-section {
            background-color: var(--white);
            padding: clamp(70px, 9vw, 120px) 0;
        }

        .gallery-section__header {
            margin-bottom: 48px;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 20px;
        }

        .gallery-card {
            position: relative;
            border-radius: var(--radius-md);
            overflow: hidden;
            background-color: var(--surface-alt);
            box-shadow: var(--shadow-sm);
            cursor: pointer;
        }

        .gallery-card--col-8 {
            grid-column: span 8;
            aspect-ratio: 16 / 10;
        }

        .gallery-card--col-4 {
            grid-column: span 4;
            aspect-ratio: 1 / 1;
        }

        .gallery-card--col-6 {
            grid-column: span 6;
            aspect-ratio: 16 / 10;
        }

        .gallery-card--col-3 {
            grid-column: span 3;
            aspect-ratio: 1 / 1;
        }

        .gallery-card__img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition-slow);
        }

        .gallery-card:hover .gallery-card__img {
            transform: scale(1.05);
        }

        .gallery-card__overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(11, 40, 27, 0.85) 0%, rgba(11, 40, 27, 0.2) 60%, transparent 100%);
            opacity: 0;
            display: flex;
            align-items: flex-end;
            padding: 24px;
            transition: var(--transition);
        }

        .gallery-card:hover .gallery-card__overlay {
            opacity: 1;
        }

        .gallery-card__caption {
            color: var(--white);
            font-size: 14px;
            font-weight: 600;
            line-height: 1.4;
            transform: translateY(10px);
            transition: var(--transition);
        }

        .gallery-card:hover .gallery-card__caption {
            transform: translateY(0);
        }

        /* ==========================================================================
           13. CONTACT CTA
           ========================================================================== */
        .contact-section {
            padding: clamp(60px, 8vw, 100px) 0;
            background-color: var(--off-white);
        }

        .contact-banner {
            background-color: var(--lime);
            color: var(--forest-dark);
            border-radius: var(--radius-xl);
            padding: clamp(40px, 6vw, 70px) clamp(24px, 5vw, 60px);
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .contact-banner::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.4) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .contact-banner__grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 40px;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .contact-banner__title {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 900;
            line-height: 1.15;
            letter-spacing: -0.03em;
            color: var(--forest-dark);
            margin-bottom: 16px;
        }

        .contact-banner__desc {
            font-size: clamp(1rem, 1.6vw, 1.15rem);
            color: rgba(11, 40, 27, 0.85);
            line-height: 1.7;
            max-width: 540px;
        }

        .contact-banner__actions {
            display: flex;
            flex-direction: column;
            gap: 14px;
            align-items: flex-start;
        }

        .contact-banner__actions .btn--forest {
            padding: 16px 36px;
            font-size: 15.5px;
            width: 100%;
            max-width: 320px;
        }

        .contact-banner__secondary-links {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            width: 100%;
        }

        .contact-banner__secondary-links .btn {
            background: rgba(11, 40, 27, 0.1);
            color: var(--forest-dark);
            border: 1px solid rgba(11, 40, 27, 0.2);
            font-size: 13px;
            padding: 10px 18px;
        }

        .contact-banner__secondary-links .btn:hover {
            background: rgba(11, 40, 27, 0.2);
        }

        /* ==========================================================================
           14. FOOTER
           ========================================================================== */
        .site-footer {
            background-color: var(--forest-dark);
            color: rgba(255, 255, 255, 0.7);
            padding: 70px 0 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .footer__grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr;
            gap: 48px;
            margin-bottom: 50px;
        }

        .footer__brand-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--white);
            margin-bottom: 8px;
            letter-spacing: -0.3px;
        }

        .footer__brand-desc {
            font-size: 13.5px;
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.7;
            max-width: 360px;
            margin-bottom: 20px;
        }

        .footer__badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-full);
            font-size: 12px;
            color: var(--lime);
            font-weight: 700;
        }

        .footer__col-title {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--white);
            margin-bottom: 20px;
        }

        .footer__nav {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .footer__link {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            transition: color 0.2s ease;
        }

        .footer__link:hover {
            color: var(--lime);
        }

        .footer__contact-info {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 12px;
            font-size: 13.5px;
        }

        .footer__contact-info li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .footer__contact-info i {
            color: var(--lime);
            font-size: 14px;
            margin-top: 3px;
        }

        .footer__bottom {
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            font-size: 12.5px;
            color: rgba(255, 255, 255, 0.45);
        }

        .footer__powered {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: rgba(255, 255, 255, 0.75);
            font-weight: 600;
        }

        .footer__powered span {
            color: var(--lime);
            font-weight: 800;
        }

        /* ==========================================================================
           15. ANIMATIONS & REVEAL
           ========================================================================== */
        .reveal-on-scroll {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1), transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }

        .reveal-on-scroll.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* ==========================================================================
           16. RESPONSIVE BREAKPOINTS
           ========================================================================== */
        @media (max-width: 1024px) {
            .hero__grid {
                grid-template-columns: 1fr;
                gap: 48px;
            }

            .about__grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .contact-banner__grid {
                grid-template-columns: 1fr;
                gap: 32px;
            }

            .footer__grid {
                grid-template-columns: 1fr 1fr;
                gap: 36px;
            }

            .gallery-card--col-8,
            .gallery-card--col-4,
            .gallery-card--col-6,
            .gallery-card--col-3 {
                grid-column: span 6;
                aspect-ratio: 4 / 3;
            }
        }

        @media (max-width: 768px) {
            .navbar__nav,
            .navbar__actions {
                display: none;
            }

            .navbar__toggle {
                display: flex;
            }

            .mobile-drawer {
                display: block;
            }

            .top-contact-bar__inner {
                justify-content: center;
                text-align: center;
            }

            .top-contact-bar__status {
                display: none;
            }

            .gallery-card--col-8,
            .gallery-card--col-4,
            .gallery-card--col-6,
            .gallery-card--col-3 {
                grid-column: span 12;
                aspect-ratio: 16 / 10;
            }

            .footer__grid {
                grid-template-columns: 1fr;
                gap: 32px;
            }

            .footer__bottom {
                flex-direction: column;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .hero__actions {
                flex-direction: column;
                width: 100%;
            }

            .hero__actions .btn {
                width: 100%;
            }

            .hero__secondary-card {
                position: static;
                margin-top: 16px;
                max-width: 100%;
            }

            .hero__collage {
                padding: 0;
            }

            .contact-banner__actions {
                width: 100%;
            }

            .contact-banner__actions .btn--forest {
                max-width: 100%;
            }
        }
    </style>
</head>

<body>

    {{-- ==========================================================================
         PREVIEW MODE BAR
         ========================================================================== --}}
    @if ($isPreview)
        <div class="preview-banner" role="banner" aria-label="Pratinjau Website">
            <div class="container preview-banner__inner">
                <div class="preview-banner__info">
                    <span class="preview-banner__badge"><i class="fas fa-eye"></i> Mode Preview</span>
                    <span>Tampilan Publik Website Usaha Pertanian Anda</span>
                </div>
                <a href="{{ route('farmer.website.index') }}" class="preview-banner__btn">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    @endif

    {{-- ==========================================================================
         TOP CONTACT BAR
         ========================================================================== --}}
    @if (($sections['show_contact'] && (!empty($contact['public_phone']) || !empty($contact['public_email']))) || ($sections['show_location'] && !empty($location['address'])))
        <div class="top-contact-bar">
            <div class="container top-contact-bar__inner">
                <ul class="top-contact-bar__list">
                    @if ($sections['show_contact'] && !empty($contact['public_phone']))
                        <li class="top-contact-bar__item">
                            <i class="fas fa-phone"></i>
                            <a href="tel:{{ $contact['public_phone'] }}" aria-label="Telepon">{{ $contact['public_phone'] }}</a>
                        </li>
                    @endif
                    @if ($sections['show_contact'] && !empty($contact['public_email']))
                        <li class="top-contact-bar__item">
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:{{ $contact['public_email'] }}" aria-label="Email">{{ $contact['public_email'] }}</a>
                        </li>
                    @endif
                    @if ($sections['show_location'] && !empty($location['address']))
                        <li class="top-contact-bar__item">
                            <i class="fas fa-location-dot"></i>
                            <span>{{ $location['address'] }}</span>
                        </li>
                    @endif
                </ul>
                <div class="top-contact-bar__status">
                    <span class="top-contact-bar__status-dot"></span>
                    <span>Mitra Tani Terverifikasi P.A.D.I.</span>
                </div>
            </div>
        </div>
    @endif

    {{-- ==========================================================================
         NAVBAR
         ========================================================================== --}}
    <header class="site-header" id="navbar">
        <div class="container">
            <nav class="navbar" aria-label="Navigasi Utama">
                <a href="#beranda" class="navbar__brand" aria-label="{{ $profile['business_name'] }}">
                    <div class="navbar__logo">
                        @if (!empty($profile['logo_url']))
                            <img src="{{ $profile['logo_url'] }}" alt="{{ $profile['business_name'] }}" style="width:100%; height:100%; object-fit:cover;">
                        @else
                            <i class="fas fa-seedling"></i>
                        @endif
                    </div>
                    <div class="navbar__brand-info">
                        <span class="navbar__brand-name">{{ $profile['business_name'] }}</span>
                        <span class="navbar__brand-tagline">Etalase Komoditas Tani</span>
                    </div>
                </a>

                <ul class="navbar__nav">
                    <li><a href="#beranda" class="navbar__link">Beranda</a></li>
                    <li><a href="#tentang" class="navbar__link">Tentang</a></li>
                    @if ($sections['show_products'] && count($products) > 0)
                        <li><a href="#produk" class="navbar__link">Produk</a></li>
                    @endif
                    @if ($sections['show_harvests'] && count($harvests) > 0)
                        <li><a href="#panen" class="navbar__link">Riwayat Panen</a></li>
                    @endif
                    @if ($sections['show_gallery'] && count($gallery) > 0)
                        <li><a href="#galeri" class="navbar__link">Galeri</a></li>
                    @endif
                </ul>

                <div class="navbar__actions">
                    @if ($sections['show_contact'] && !empty($contact['whatsapp']))
                        <a href="{{ $contact['whatsapp'] }}" target="_blank" rel="noopener noreferrer" class="btn btn--forest">
                            <i class="fab fa-whatsapp"></i> Hubungi Kami
                        </a>
                    @elseif ($sections['show_contact'])
                        <a href="#kontak" class="btn btn--forest">
                            <i class="fas fa-envelope"></i> Hubungi Kami
                        </a>
                    @endif
                </div>

                <button type="button" class="navbar__toggle" id="mobileMenuOpen" aria-label="Buka Menu Navigasi">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>
        </div>
    </header>

    {{-- Mobile Menu Drawer --}}
    <div class="mobile-drawer" id="mobileDrawer" role="dialog" aria-modal="true" aria-label="Menu Navigasi Mobile">
        <div class="mobile-drawer__panel">
            <div>
                <div class="mobile-drawer__header">
                    <div class="navbar__brand">
                        <div class="navbar__logo">
                            @if (!empty($profile['logo_url']))
                                <img src="{{ $profile['logo_url'] }}" alt="{{ $profile['business_name'] }}" style="width:100%; height:100%; object-fit:cover;">
                            @else
                                <i class="fas fa-seedling"></i>
                            @endif
                        </div>
                        <div class="navbar__brand-info">
                            <span class="navbar__brand-name">{{ $profile['business_name'] }}</span>
                        </div>
                    </div>
                    <button type="button" class="mobile-drawer__close" id="mobileMenuClose" aria-label="Tutup Menu">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>

                <ul class="mobile-drawer__nav">
                    <li><a href="#beranda" class="mobile-drawer__link mobile-link-item">Beranda <i class="fas fa-chevron-right"></i></a></li>
                    <li><a href="#tentang" class="mobile-drawer__link mobile-link-item">Tentang Kami <i class="fas fa-chevron-right"></i></a></li>
                    @if ($sections['show_products'] && count($products) > 0)
                        <li><a href="#produk" class="mobile-drawer__link mobile-link-item">Produk Unggulan <i class="fas fa-chevron-right"></i></a></li>
                    @endif
                    @if ($sections['show_harvests'] && count($harvests) > 0)
                        <li><a href="#panen" class="mobile-drawer__link mobile-link-item">Riwayat Panen <i class="fas fa-chevron-right"></i></a></li>
                    @endif
                    @if ($sections['show_gallery'] && count($gallery) > 0)
                        <li><a href="#galeri" class="mobile-drawer__link mobile-link-item">Galeri Produksi <i class="fas fa-chevron-right"></i></a></li>
                    @endif
                    @if ($sections['show_contact'] && $contact)
                        <li><a href="#kontak" class="mobile-drawer__link mobile-link-item">Kontak & Pemesanan <i class="fas fa-chevron-right"></i></a></li>
                    @endif
                </ul>
            </div>

            <div class="mobile-drawer__footer">
                @if ($sections['show_contact'] && !empty($contact['whatsapp']))
                    <a href="{{ $contact['whatsapp'] }}" target="_blank" rel="noopener noreferrer" class="btn btn--lime" style="width: 100%;">
                        <i class="fab fa-whatsapp"></i> Chat WhatsApp
                    </a>
                @endif
            </div>
        </div>
    </div>

    <main>
        {{-- ==========================================================================
             HERO SECTION
             ========================================================================== --}}
        <section class="hero" id="beranda">
            <div class="container">
                <div class="hero__grid">
                    <div class="hero__content reveal-on-scroll">
                        <div class="hero__badge-row">
                            <span class="badge badge--pill-forest">
                                <i class="fas fa-shield-check" style="color: var(--forest);"></i>
                                Pemasok Pertanian Terverifikasi
                            </span>
                        </div>

                        <h1 class="hero__title">
                            {{ $profile['business_name'] }}
                        </h1>

                        <p class="hero__headline">
                            {{ $profile['headline'] ?? 'Pasokan Hasil Pertanian Berkualitas Langsung dari Petani' }}
                        </p>

                        <p class="hero__description">
                            {{ $profile['description'] ?? 'Menyediakan pasokan komoditas pertanian terbaik dengan standar kualitas mutu terjamin, proses transparan, dan kemitraan pasokan berkesinambungan langsung dari lahan kami.' }}
                        </p>

                        <div class="hero__actions">
                            @if ($sections['show_products'] && count($products) > 0)
                                <a href="#produk" class="btn btn--lime">
                                    Lihat Produk <i class="fas fa-arrow-right"></i>
                                </a>
                            @endif

                            @if ($sections['show_contact'] && !empty($contact['whatsapp']))
                                <a href="{{ $contact['whatsapp'] }}" target="_blank" rel="noopener noreferrer" class="btn btn--outline-dark">
                                    <i class="fab fa-whatsapp"></i> Hubungi Kami
                                </a>
                            @elseif ($sections['show_contact'])
                                <a href="#kontak" class="btn btn--outline-dark">
                                    <i class="fas fa-envelope"></i> Hubungi Kami
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="hero__media reveal-on-scroll">
                        <div class="hero__collage">
                            <div class="hero__main-image-wrap">
                                @if (!empty($profile['cover_image_url']))
                                    <img src="{{ $profile['cover_image_url'] }}" alt="{{ $profile['business_name'] }}" class="hero__main-image">
                                @else
                                    <img src="https://images.unsplash.com/photo-1586771107445-d3ca888129ff?auto=format&fit=crop&w=1200&q=80"
                                         alt="{{ $profile['business_name'] }}" class="hero__main-image">
                                @endif
                            </div>

                            <div class="hero__floating-shape" aria-hidden="true">
                                <i class="fas fa-seedling"></i>
                            </div>

                            <div class="hero__secondary-card">
                                <div class="hero__secondary-card-icon">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="hero__secondary-card-text">
                                    <h4>Verified Supplier</h4>
                                    <p>Standar Mutu Terkurasi P.A.D.I.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ==========================================================================
             AGRICULTURE MARQUEE
             ========================================================================== --}}
        <div class="marquee-strip" aria-hidden="true">
            <div class="marquee-strip__track">
                <div class="marquee-strip__content">
                    <span>BERAS PREMIUM</span>
                    <span class="star">✦</span>
                    <span>GABAH KERING</span>
                    <span class="star">✦</span>
                    <span>PASOKAN LANGSUNG PETANI</span>
                    <span class="star">✦</span>
                    <span>HASIL PANEN BERKUALITAS</span>
                    <span class="star">✦</span>
                    <span>MITRA PERTANIAN</span>
                    <span class="star">✦</span>
                    <span>TRANSPARANSI MUTU</span>
                    <span class="star">✦</span>
                </div>
                <div class="marquee-strip__content">
                    <span>BERAS PREMIUM</span>
                    <span class="star">✦</span>
                    <span>GABAH KERING</span>
                    <span class="star">✦</span>
                    <span>PASOKAN LANGSUNG PETANI</span>
                    <span class="star">✦</span>
                    <span>HASIL PANEN BERKUALITAS</span>
                    <span class="star">✦</span>
                    <span>MITRA PERTANIAN</span>
                    <span class="star">✦</span>
                    <span>TRANSPARANSI MUTU</span>
                    <span class="star">✦</span>
                </div>
            </div>
        </div>

        {{-- ==========================================================================
             ABOUT / COMPANY PROFILE
             ========================================================================== --}}
        <section class="about-section" id="tentang">
            <div class="container">
                <div class="about__grid">
                    <div class="about__media-grid reveal-on-scroll">
                        @php
                            $coverImg = !empty($profile['cover_image_url'])
                                ? $profile['cover_image_url']
                                : 'https://images.unsplash.com/photo-1586771107445-d3ca888129ff?auto=format&fit=crop&w=800&q=80';

                            $galColl = collect($gallery ?? []);
                            $first = $galColl->get(0);
                            $second = $galColl->get(1);

                            $galImg1 = null;
                            $galImg2 = null;
                            if ($first) {
                                $galImg1 = is_array($first) 
                                    ? ($first['image_url'] ?? null) 
                                    : (isset($first->image_url) ? $first->image_url : asset('storage/' . ($first->image_path ?? '')));
                            }
                            if ($second) {
                                $galImg2 = is_array($second) 
                                    ? ($second['image_url'] ?? null) 
                                    : (isset($second->image_url) ? $second->image_url : asset('storage/' . ($second->image_path ?? '')));
                            }
                        @endphp

                        <div class="about__photo about__photo--tall">
                            <img src="{{ $coverImg }}" alt="{{ $profile['business_name'] }}" loading="lazy">
                        </div>

                        <div class="about__photo about__photo--square">
                            <img src="{{ $galImg1 ?: $coverImg }}" alt="Lahan Pertanian" loading="lazy">
                        </div>

                        <div class="about__photo about__photo--square">
                            <img src="{{ $galImg2 ?: $coverImg }}" alt="Produksi Pertanian" loading="lazy">
                        </div>
                    </div>

                    <div class="about__content reveal-on-scroll">
                        <span class="section-eyebrow">Tentang Kami</span>
                        <h2 class="section-heading">
                            Membangun Pasokan Pertanian yang Lebih Transparan dan Terpercaya
                        </h2>

                        <p class="about__lead">
                            {{ $profile['description'] ?? 'Kami berfokus pada budidaya komoditas padi dan pertanian dengan mengedepankan kualitas panen unggul, konsistensi stok, dan kejujuran spesifikasi mutu untuk seluruh mitra distribusi dan konsumen.' }}
                        </p>

                        <ul class="about__meta-list">
                            @if ($sections['show_location'] && !empty($location['address']))
                                <li class="about__meta-item">
                                    <div class="about__meta-icon">
                                        <i class="fas fa-map-location-dot"></i>
                                    </div>
                                    <div>
                                        <strong style="color: var(--forest-dark); display: block; font-size: 13.5px;">Lokasi Operasional</strong>
                                        <span>{{ $location['address'] }}</span>
                                    </div>
                                </li>
                            @endif

                            @if ($sections['show_productivity'] && $statistics && !empty($statistics['total_area_ha']))
                                <li class="about__meta-item">
                                    <div class="about__meta-icon">
                                        <i class="fas fa-layer-group"></i>
                                    </div>
                                    <div>
                                        <strong style="color: var(--forest-dark); display: block; font-size: 13.5px;">Luas Pengelolaan Lahan</strong>
                                        <span>{{ $statistics['total_area_ha'] }} Hektar lahan produktif</span>
                                    </div>
                                </li>
                            @endif

                            @if ($sections['show_productivity'] && $statistics && !empty($statistics['total_seasons']))
                                <li class="about__meta-item">
                                    <div class="about__meta-icon">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                    <div>
                                        <strong style="color: var(--forest-dark); display: block; font-size: 13.5px;">Musim Terdata</strong>
                                        <span>{{ $statistics['total_seasons'] }} siklus panen terdokumentasi</span>
                                    </div>
                                </li>
                            @endif
                        </ul>

                        <div>
                            @if ($sections['show_contact'] && !empty($contact['whatsapp']))
                                <a href="{{ $contact['whatsapp'] }}" target="_blank" rel="noopener noreferrer" class="btn btn--forest">
                                    Konsultasi Pasokan <i class="fas fa-arrow-right"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ==========================================================================
             STATISTICS STRIP
             ========================================================================== --}}
        @if ($sections['show_productivity'] && $statistics)
            <section class="stats-section">
                <div class="container">
                    <div class="stats-strip reveal-on-scroll">
                        @if (isset($statistics['total_area_ha']) && $statistics['total_area_ha'] > 0)
                            <div class="stat-card">
                                <div class="stat-card__icon"><i class="fas fa-vector-square"></i></div>
                                <div class="stat-card__value">{{ $statistics['total_area_ha'] }}<small>Ha</small></div>
                                <div class="stat-card__label">Luas Lahan Terdata</div>
                            </div>
                        @endif

                        @if (isset($statistics['total_seasons']) && $statistics['total_seasons'] > 0)
                            <div class="stat-card">
                                <div class="stat-card__icon"><i class="fas fa-wheat-awn"></i></div>
                                <div class="stat-card__value">{{ $statistics['total_seasons'] }}</div>
                                <div class="stat-card__label">Musim Tanam</div>
                            </div>
                        @endif

                        @if (!empty($statistics['latest_productivity']))
                            <div class="stat-card">
                                <div class="stat-card__icon"><i class="fas fa-chart-line"></i></div>
                                <div class="stat-card__value">{{ $statistics['latest_productivity'] }}<small>t/ha</small></div>
                                <div class="stat-card__label">Produktivitas Panen</div>
                            </div>
                        @endif

                        <div class="stat-card">
                            <div class="stat-card__icon"><i class="fas fa-shield-halved"></i></div>
                            <div class="stat-card__value">100<small>%</small></div>
                            <div class="stat-card__label">Pasokan Langsung Petani</div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ==========================================================================
             PRODUCTS SECTION
             ========================================================================== --}}
        @if ($sections['show_products'] && count($products) > 0)
            <section class="products-section" id="produk">
                <div class="container">
                    <div class="products-section__header reveal-on-scroll">
                        <div>
                            <span class="section-eyebrow section-eyebrow--light">Katalog Komoditas</span>
                            <h2 class="section-heading section-heading--light">Pasokan Hasil Panen Kami</h2>
                            <p class="section-subheading section-subheading--light">
                                Komoditas pilihan langsung dari lahan petani dengan standar mutu terbaik.
                            </p>
                        </div>
                        @if ($sections['show_contact'] && !empty($contact['whatsapp']))
                            <div>
                                <a href="{{ $contact['whatsapp'] }}" target="_blank" rel="noopener noreferrer" class="btn btn--outline-light">
                                    <i class="fab fa-whatsapp"></i> Permintaan Khusus
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="products-grid">
                        @foreach ($products as $product)
                            @php
                                $isFeatured = ($loop->iteration % 2 === 0);
                                $prodImg = is_array($product) ? ($product['image_url'] ?? '') : ($product->image_url ?? '');
                                $prodCommodity = is_array($product) ? ($product['commodity'] ?? '') : ($product->commodity ?? '');
                                $prodQty = is_array($product) ? ($product['quantity'] ?? null) : ($product->quantity ?? null);
                                $prodUnit = is_array($product) ? ($product['unit'] ?? '') : ($product->unit ?? '');
                                $prodPrice = is_array($product) ? ($product['price_per_unit'] ?? null) : ($product->price_per_unit ?? null);
                                $prodDesc = is_array($product) ? ($product['description'] ?? '') : ($product->description ?? '');
                                $prodLink = is_array($product) ? ($product['sales_link'] ?? '') : ($product->sales_link ?? '');
                            @endphp
                            <article class="product-card {{ $isFeatured ? 'product-card--featured' : '' }} reveal-on-scroll">
                                <div class="product-card__image-wrap">
                                    @if (!empty($prodImg))
                                        <img src="{{ $prodImg }}" alt="{{ $prodCommodity }}" class="product-card__image" loading="lazy">
                                    @else
                                        <img src="https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=600&q=80"
                                             alt="{{ $prodCommodity }}" class="product-card__image" loading="lazy">
                                    @endif

                                    @if ($isFeatured)
                                        <span class="product-card__featured-tag">Pilihan Utama</span>
                                    @endif

                                    @if (!empty($prodQty) && !empty($prodUnit))
                                        <span class="product-card__badge">
                                            Stok: {{ $prodQty }} {{ $prodUnit }}
                                        </span>
                                    @endif
                                </div>

                                <div class="product-card__body">
                                    <h3 class="product-card__title">{{ $prodCommodity }}</h3>

                                    @if (!empty($prodPrice))
                                        <div class="product-card__price-wrap">
                                            <span class="product-card__price">
                                                Rp {{ number_format($prodPrice, 0, ',', '.') }}
                                            </span>
                                            @if (!empty($prodUnit))
                                                <span class="product-card__unit">/ {{ $prodUnit }}</span>
                                            @endif
                                        </div>
                                    @endif

                                    @if (!empty($prodDesc))
                                        <p class="product-card__description">{{ $prodDesc }}</p>
                                    @endif

                                    <div class="product-card__actions">
                                        @if (!empty($prodLink))
                                            <a href="{{ $prodLink }}" target="_blank" rel="noopener noreferrer" class="btn btn--lime">
                                                <i class="fas fa-bag-shopping"></i> Beli / Pesan
                                            </a>
                                        @elseif ($sections['show_contact'] && !empty($contact['whatsapp']))
                                            @php
                                                $waMsg = urlencode("Halo {$profile['business_name']}, saya tertarik dengan produk {$prodCommodity}. Mohon info detail dan ketersediaan stok.");
                                                $waUrl = $contact['whatsapp'] . (str_contains($contact['whatsapp'], '?') ? '&' : '?') . "text={$waMsg}";
                                            @endphp
                                            <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn--lime">
                                                <i class="fab fa-whatsapp"></i> Beli / Pesan
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- ==========================================================================
             HARVEST HISTORY
             ========================================================================== --}}
        @if ($sections['show_harvests'] && count($harvests) > 0)
            <section class="harvest-section" id="panen">
                <div class="container">
                    <div class="harvest-section__header reveal-on-scroll">
                        <span class="section-eyebrow">Transparansi Mutu</span>
                        <h2 class="section-heading">Riwayat Panen</h2>
                        <p class="section-subheading">
                            Catatan rekam jejak panen berkala dari siklus produksi lahan kami untuk menjamin keterbukaan pasokan.
                        </p>
                    </div>

                    <div class="harvest-cards-grid">
                        @foreach ($harvests as $harvest)
                            @php
                                $hDate = is_array($harvest) ? $harvest['harvest_date'] : $harvest->harvest_date;
                                $grade = is_array($harvest) ? ($harvest['quality_grade'] ?? 'A') : ($harvest->quality_grade ?? 'A');
                                $hVariety = is_array($harvest) ? ($harvest['variety_name'] ?? 'Varietas Unggul') : ($harvest->variety_name ?? 'Varietas Unggul');
                                $hFarm = is_array($harvest) ? ($harvest['farm_name'] ?? 'Lahan Utama') : ($harvest->farm_name ?? 'Lahan Utama');
                                $hQty = is_array($harvest) ? ($harvest['quantity'] ?? 0) : ($harvest->quantity ?? 0);
                                $hUnit = is_array($harvest) ? ($harvest['unit'] ?? '') : ($harvest->unit ?? '');
                                $isGradeA = in_array(strtoupper($grade), ['A', 'PREMIUM', 'SUPER', 'GRADE A']);
                            @endphp
                            <div class="harvest-card reveal-on-scroll">
                                <div>
                                    <div class="harvest-card__top">
                                        <div class="harvest-card__date">
                                            <i class="fas fa-calendar-days"></i>
                                            <span>
                                                {{ \Carbon\Carbon::parse($hDate)->translatedFormat('d F Y') }}
                                            </span>
                                        </div>
                                        <span class="harvest-card__grade {{ $isGradeA ? 'harvest-card__grade--premium' : 'harvest-card__grade--standard' }}">
                                            Grade {{ $grade }}
                                        </span>
                                    </div>

                                    <h3 class="harvest-card__variety">
                                        {{ $hVariety }}
                                    </h3>

                                    <div class="harvest-card__farm">
                                        <i class="fas fa-location-crosshairs"></i>
                                        <span>{{ $hFarm }}</span>
                                    </div>
                                </div>

                                <div class="harvest-card__quantity">
                                    <span class="harvest-card__quantity-label">Volume Hasil</span>
                                    <span class="harvest-card__quantity-value">
                                        {{ number_format($hQty, 1, ',', '.') }} {{ $hUnit }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- ==========================================================================
             GALLERY
             ========================================================================== --}}
        @if ($sections['show_gallery'] && count($gallery) > 0)
            <section class="gallery-section" id="galeri">
                <div class="container">
                    <div class="gallery-section__header reveal-on-scroll">
                        <span class="section-eyebrow">Dokumentasi</span>
                        <h2 class="section-heading">Dokumentasi Lahan & Produksi</h2>
                        <p class="section-subheading">
                            Sekilas aktivitas lapangan, perawatan tanaman, hingga proses pascapanen komoditas kami.
                        </p>
                    </div>

                    <div class="gallery-grid">
                        @foreach ($gallery as $item)
                            @php
                                $imgSrc = is_array($item) 
                                    ? ($item['image_url'] ?? '') 
                                    : (isset($item->image_url) ? $item->image_url : asset('storage/' . ($item->image_path ?? '')));
                                $cap = is_array($item) ? ($item['caption'] ?? '') : ($item->caption ?? '');
                                
                                // Editorial column pattern
                                $colClass = 'gallery-card--col-4';
                                if ($loop->iteration === 1) {
                                    $colClass = 'gallery-card--col-8';
                                } elseif ($loop->iteration === 4) {
                                    $colClass = 'gallery-card--col-6';
                                } elseif ($loop->iteration === 5) {
                                    $colClass = 'gallery-card--col-6';
                                }
                            @endphp
                            <div class="gallery-card {{ $colClass }} reveal-on-scroll">
                                <img src="{{ $imgSrc }}" alt="{{ $cap ?: 'Dokumentasi Pertanian ' . $profile['business_name'] }}" class="gallery-card__img" loading="lazy">
                                @if (!empty($cap))
                                    <div class="gallery-card__overlay">
                                        <p class="gallery-card__caption">{{ $cap }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- ==========================================================================
             CONTACT CTA
             ========================================================================== --}}
        @if ($sections['show_contact'] && $contact)
            <section class="contact-section" id="kontak">
                <div class="container">
                    <div class="contact-banner reveal-on-scroll">
                        <div class="contact-banner__grid">
                            <div>
                                <h2 class="contact-banner__title">Butuh Pasokan Beras atau Gabah?</h2>
                                <p class="contact-banner__desc">
                                    Hubungi kami untuk kebutuhan pembelian, kerja sama pasokan, maupun distribusi dalam jumlah besar. Kami siap melayani pengiriman berkala dengan mutu terstandarisasi.
                                </p>
                            </div>

                            <div class="contact-banner__actions">
                                @if (!empty($contact['whatsapp']))
                                    <a href="{{ $contact['whatsapp'] }}" target="_blank" rel="noopener noreferrer" class="btn btn--forest">
                                        <i class="fab fa-whatsapp"></i> Chat WhatsApp
                                    </a>
                                @endif

                                <div class="contact-banner__secondary-links">
                                    @if (!empty($contact['public_phone']))
                                        <a href="tel:{{ $contact['public_phone'] }}" class="btn">
                                            <i class="fas fa-phone"></i> {{ $contact['public_phone'] }}
                                        </a>
                                    @endif
                                    @if (!empty($contact['public_email']))
                                        <a href="mailto:{{ $contact['public_email'] }}" class="btn">
                                            <i class="fas fa-envelope"></i> Email Kami
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif
    </main>

    {{-- ==========================================================================
         FOOTER
         ========================================================================== --}}
    <footer class="site-footer">
        <div class="container">
            <div class="footer__grid">
                <div>
                    <div class="footer__brand-title">{{ $profile['business_name'] }}</div>
                    <p class="footer__brand-desc">
                        Etalase Resmi Komoditas Pertanian. Menyediakan pasokan hasil panen berkualitas langsung dari petani terpercaya.
                    </p>
                    <div class="footer__badge">
                        <i class="fas fa-certificate"></i> Terverifikasi Ekosistem P.A.D.I.
                    </div>
                </div>

                <div>
                    <h3 class="footer__col-title">Navigasi Cepat</h3>
                    <ul class="footer__nav">
                        <li><a href="#beranda" class="footer__link">Beranda</a></li>
                        <li><a href="#tentang" class="footer__link">Tentang Kami</a></li>
                        @if ($sections['show_products'] && count($products) > 0)
                            <li><a href="#produk" class="footer__link">Produk Komoditas</a></li>
                        @endif
                        @if ($sections['show_harvests'] && count($harvests) > 0)
                            <li><a href="#panen" class="footer__link">Riwayat Panen</a></li>
                        @endif
                        @if ($sections['show_gallery'] && count($gallery) > 0)
                            <li><a href="#galeri" class="footer__link">Galeri Produksi</a></li>
                        @endif
                    </ul>
                </div>

                <div>
                    <h3 class="footer__col-title">Kontak Operasional</h3>
                    <ul class="footer__contact-info">
                        @if ($sections['show_location'] && !empty($location['address']))
                            <li>
                                <i class="fas fa-location-dot"></i>
                                <span>{{ $location['address'] }}</span>
                            </li>
                        @endif
                        @if ($sections['show_contact'] && !empty($contact['public_phone']))
                            <li>
                                <i class="fas fa-phone"></i>
                                <span>{{ $contact['public_phone'] }}</span>
                            </li>
                        @endif
                        @if ($sections['show_contact'] && !empty($contact['public_email']))
                            <li>
                                <i class="fas fa-envelope"></i>
                                <span>{{ $contact['public_email'] }}</span>
                            </li>
                        @endif
                        <li>
                            <i class="fas fa-clock"></i>
                            <span>Layanan: Setiap Hari (07.00 - 18.00 WIB)</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="footer__bottom">
                <div>
                    &copy; {{ date('Y') }} {{ $profile['business_name'] }}. Hak cipta dilindungi.
                </div>
                <div class="footer__powered">
                    Powered by <span>P.A.D.I.</span>
                </div>
            </div>
        </div>
    </footer>

    {{-- ==========================================================================
         MICRO-INTERACTIONS & SCRIPTS (VANILLA JS)
         ========================================================================== --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Navbar Scroll State
            const navbar = document.getElementById('navbar');
            function handleScroll() {
                if (!navbar) return;
                if (window.scrollY > 40) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            }
            window.addEventListener('scroll', handleScroll, { passive: true });
            handleScroll();

            // 2. Mobile Drawer Navigation
            const mobileMenuOpen = document.getElementById('mobileMenuOpen');
            const mobileMenuClose = document.getElementById('mobileMenuClose');
            const mobileDrawer = document.getElementById('mobileDrawer');
            const mobileLinks = document.querySelectorAll('.mobile-link-item');

            function openDrawer() {
                if (mobileDrawer) {
                    mobileDrawer.classList.add('is-open');
                    document.body.style.overflow = 'hidden';
                }
            }

            function closeDrawer() {
                if (mobileDrawer) {
                    mobileDrawer.classList.remove('is-open');
                    document.body.style.overflow = '';
                }
            }

            if (mobileMenuOpen) mobileMenuOpen.addEventListener('click', openDrawer);
            if (mobileMenuClose) mobileMenuClose.addEventListener('click', closeDrawer);
            if (mobileDrawer) {
                mobileDrawer.addEventListener('click', function (e) {
                    if (e.target === mobileDrawer) closeDrawer();
                });
            }
            mobileLinks.forEach(link => {
                link.addEventListener('click', closeDrawer);
            });

            // 3. Smooth Scrolling for Internal Hash Anchors
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    const href = this.getAttribute('href');
                    if (href === '#' || href === '') return;
                    const targetEl = document.querySelector(href);
                    if (targetEl) {
                        e.preventDefault();
                        const headerOffset = 90;
                        const elementPosition = targetEl.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // 4. Reveal on Scroll Animation (IntersectionObserver)
            if ('IntersectionObserver' in window) {
                const revealElements = document.querySelectorAll('.reveal-on-scroll');
                const revealObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    root: null,
                    threshold: 0.12,
                    rootMargin: '0px 0px -40px 0px'
                });

                revealElements.forEach(el => revealObserver.observe(el));
            } else {
                document.querySelectorAll('.reveal-on-scroll').forEach(el => {
                    el.classList.add('is-visible');
                });
            }
        });
    </script>
</body>

</html>