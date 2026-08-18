<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="{{ $profile['headline'] ?? 'Katalog Pasokan Hasil Panen Komoditas Beras dan Gabah' }} — {{ $profile['business_name'] }}">
    <meta name="robots" content="{{ $isPreview ? 'noindex, nofollow' : 'index, follow' }}">

    <title>{{ $profile['business_name'] }} &mdash; Etalase Pasokan P.A.D.I.</title>

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
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* ==========================================================================
           1. DESIGN TOKENS & RESET
           ========================================================================== */
        :root {
            --green-950: #173f18;
            --green-900: #24551e;
            --green-800: #356d20;
            --green-600: #68a81f;
            --lime: #78c800;
            --lime-bright: #92dc18;

            --green-soft: #eaf4dd;
            --green-pale: #f4f9eb;
            --green-tint: #f7faef;

            --white: #ffffff;
            --cream: #fbfcf6;
            --black: #181b17;

            --gray-700: #555d52;
            --gray-500: #838a80;
            --gray-200: #e6eadf;
            --gray-100: #f0f3ea;

            --radius-xs: 6px;
            --radius-sm: 10px;
            --radius-md: 16px;
            --radius-lg: 22px;
            --radius-xl: 28px;
            --radius-full: 9999px;

            --shadow-subtle: 0 4px 20px rgba(23, 63, 24, 0.06);
            --shadow-card: 0 10px 30px rgba(23, 63, 24, 0.08);
            --shadow-shell: 0 24px 70px rgba(44, 78, 31, 0.12);

            --transition: 200ms ease;
            --transition-slow: 350ms ease;
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
            background:
                radial-gradient(circle at 10% 20%, rgba(146, 220, 24, 0.12), transparent 35%),
                radial-gradient(circle at 90% 80%, rgba(120, 200, 0, 0.08), transparent 30%),
                #f5f9ed;
            color: var(--black);
            line-height: 1.7;
            font-size: 15px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
            padding: 0;
        }

        img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        a {
            color: inherit;
            text-decoration: none;
            transition: var(--transition);
        }

        button {
            font-family: inherit;
            border: none;
            background: transparent;
            cursor: pointer;
        }

        :focus-visible {
            outline: 2px solid var(--lime);
            outline-offset: 3px;
        }

        /* ==========================================================================
           2. SHELL & CONTAINER
           ========================================================================== */
        .site-shell {
            width: min(1440px, calc(100% - 80px));
            margin: 36px auto 60px;
            background: var(--white);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-shell);
            border: 1px solid rgba(230, 234, 223, 0.8);
        }

        .container {
            width: 100%;
            max-width: 1260px;
            margin: 0 auto;
            padding: 0 32px;
        }

        /* ==========================================================================
           3. TYPOGRAPHY & BUTTONS
           ========================================================================== */
        h1, h2, h3, h4 {
            font-weight: 700;
            letter-spacing: -0.035em;
            color: var(--green-950);
            line-height: 1.15;
        }

        .section-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.6px;
            color: var(--green-800);
            margin-bottom: 12px;
        }

        .section-eyebrow::before {
            content: '';
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background-color: var(--lime);
        }

        .heading-highlight {
            position: relative;
            display: inline-block;
            z-index: 1;
        }

        .heading-highlight::after {
            content: '';
            position: absolute;
            left: -2px;
            right: -2px;
            bottom: 3px;
            height: 0.4em;
            background: rgba(146, 220, 24, 0.3);
            z-index: -1;
            border-radius: 4px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 700;
            border-radius: var(--radius-full);
            transition: var(--transition);
            white-space: nowrap;
            letter-spacing: -0.1px;
            cursor: pointer;
        }

        .btn__icon-circle {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: transform var(--transition);
        }

        .btn:hover .btn__icon-circle {
            transform: translateX(3px);
        }

        .btn--lime {
            background-color: var(--lime);
            color: var(--green-950);
        }

        .btn--lime:hover {
            background-color: var(--lime-bright);
            transform: translateY(-1px);
        }

        .btn--lime .btn__icon-circle {
            background-color: var(--green-950);
            color: var(--lime);
        }

        .btn--white {
            background-color: var(--white);
            color: var(--green-950);
            box-shadow: var(--shadow-subtle);
        }

        .btn--white:hover {
            background-color: var(--green-pale);
            transform: translateY(-1px);
        }

        .btn--white .btn__icon-circle {
            background-color: var(--green-soft);
            color: var(--green-950);
        }

        .btn--dark {
            background-color: var(--green-950);
            color: var(--white);
        }

        .btn--dark:hover {
            background-color: var(--green-900);
            transform: translateY(-1px);
        }

        .btn--dark .btn__icon-circle {
            background-color: var(--lime);
            color: var(--green-950);
        }

        .btn--outline {
            background: transparent;
            color: var(--green-950);
            border: 1.5px solid var(--gray-200);
        }

        .btn--outline:hover {
            border-color: var(--green-800);
            background-color: var(--green-pale);
        }

        /* ==========================================================================
           4. PREVIEW BANNER
           ========================================================================== */
        .preview-banner {
            position: sticky;
            top: 0;
            z-index: 1200;
            background-color: var(--green-950);
            color: var(--white);
            border-bottom: 2px solid var(--lime);
            padding: 8px 24px;
            font-size: 13px;
            font-weight: 600;
        }

        .preview-banner__inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .preview-banner__btn {
            background: var(--lime);
            color: var(--green-950);
            padding: 5px 14px;
            border-radius: var(--radius-full);
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .preview-banner__btn:hover {
            background: var(--lime-bright);
        }

        /* ==========================================================================
           5. NAVBAR (GREENMARKET MINIMAL STYLE)
           ========================================================================== */
        .site-header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background-color: var(--white);
            border-bottom: 1px solid var(--gray-200);
            transition: var(--transition);
        }

        .site-header.scrolled {
            box-shadow: var(--shadow-subtle);
        }

        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 78px;
            gap: 24px;
        }

        .navbar__brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            flex-shrink: 0;
        }

        .navbar__logo {
            width: 44px;
            height: 44px;
            border-radius: var(--radius-sm);
            background-color: var(--green-950);
            color: var(--lime);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            font-weight: 800;
            overflow: hidden;
            flex-shrink: 0;
        }

        .navbar__logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .navbar__brand-name {
            font-size: 18px;
            font-weight: 800;
            color: var(--green-950);
            letter-spacing: -0.4px;
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
            color: var(--gray-700);
            transition: color var(--transition);
            position: relative;
            padding: 4px 0;
        }

        .navbar__link:hover {
            color: var(--green-950);
        }

        .navbar__link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--lime);
            transition: width var(--transition);
            border-radius: 2px;
        }

        .navbar__link:hover::after {
            width: 100%;
        }

        .navbar__actions {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .nav-toggle {
            display: none;
            width: 42px;
            height: 42px;
            border-radius: var(--radius-sm);
            background-color: var(--gray-100);
            color: var(--green-950);
            align-items: center;
            justify-content: center;
            font-size: 17px;
        }

        /* Mobile Menu Drawer */
        .mobile-menu {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(23, 63, 24, 0.4);
            backdrop-filter: blur(4px);
            z-index: 2000;
            opacity: 0;
            pointer-events: none;
            transition: opacity var(--transition);
        }

        .mobile-menu.is-open {
            opacity: 1;
            pointer-events: auto;
        }

        .mobile-menu__panel {
            position: absolute;
            top: 0;
            right: 0;
            width: 85%;
            max-width: 340px;
            height: 100%;
            background: var(--white);
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: -8px 0 24px rgba(0, 0, 0, 0.15);
            transform: translateX(100%);
            transition: transform var(--transition-slow);
        }

        .mobile-menu.is-open .mobile-menu__panel {
            transform: translateX(0);
        }

        .mobile-menu__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--gray-200);
        }

        .mobile-menu__close {
            width: 38px;
            height: 38px;
            border-radius: var(--radius-sm);
            background: var(--gray-100);
            color: var(--green-950);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .mobile-menu__nav {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .mobile-menu__link {
            font-size: 16px;
            font-weight: 700;
            color: var(--green-950);
            display: block;
            padding: 6px 0;
        }

        /* ==========================================================================
           6. HERO SECTION (GREENMARKET ASYMMETRIC 3-IMAGE GRID)
           ========================================================================== */
        .hero {
            padding: 24px 32px 48px;
            background-color: var(--white);
        }

        .hero__grid {
            display: grid;
            grid-template-columns: 1.7fr 0.65fr 0.65fr;
            gap: 16px;
            min-height: 520px;
        }

        .hero__main {
            position: relative;
            border-radius: var(--radius-lg);
            overflow: hidden;
            background-color: var(--green-950);
            color: var(--white);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: clamp(32px, 5vw, 56px);
            background-size: cover;
            background-position: center;
        }

        .hero__main::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                180deg,
                rgba(23, 63, 24, 0.2) 0%,
                rgba(23, 63, 24, 0.65) 50%,
                rgba(23, 63, 24, 0.92) 100%
            );
            z-index: 1;
        }

        .hero__main-content {
            position: relative;
            z-index: 2;
            max-width: 620px;
        }

        .hero__badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: var(--radius-full);
            background: rgba(255, 255, 255, 0.16);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            font-size: 11.5px;
            font-weight: 800;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: var(--lime);
            margin-bottom: 18px;
        }

        .hero__title {
            font-size: clamp(34px, 4.5vw, 56px);
            font-weight: 700;
            line-height: 1.02;
            letter-spacing: -1.8px;
            color: var(--white);
            margin-bottom: 16px;
        }

        .hero__desc {
            font-size: clamp(14.5px, 1.3vw, 16px);
            color: rgba(255, 255, 255, 0.85);
            line-height: 1.65;
            margin-bottom: 24px;
            max-width: 520px;
        }

        .hero__actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .hero__categories {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .hero__category-chip {
            padding: 4px 12px;
            border-radius: var(--radius-full);
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(6px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 12px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
        }

        /* Hero Secondary Side Columns */
        .hero__side-col {
            border-radius: var(--radius-lg);
            overflow: hidden;
            position: relative;
            background-color: var(--gray-100);
        }

        .hero__side-col img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform var(--transition-slow);
        }

        .hero__side-col:hover img {
            transform: scale(1.05);
        }

        .hero__side-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            background: linear-gradient(to top, rgba(23, 63, 24, 0.85), transparent);
            color: var(--white);
            font-size: 13px;
            font-weight: 700;
        }

        /* ==========================================================================
           7. ABOUT & MOSAIC SECTION
           ========================================================================== */
        .about-section {
            padding: clamp(64px, 7vw, 96px) 0;
            background-color: var(--white);
            border-top: 1px solid var(--gray-200);
        }

        .about-header-grid {
            display: grid;
            grid-template-columns: 0.8fr 1.4fr 1fr;
            gap: 32px;
            align-items: start;
            margin-bottom: 48px;
        }

        .about-mosaic {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        .mosaic-card {
            border-radius: var(--radius-md);
            overflow: hidden;
            background-color: var(--green-pale);
            border: 1px solid var(--gray-200);
            min-height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 28px 24px;
            position: relative;
            transition: transform var(--transition);
        }

        .mosaic-card:hover {
            transform: translateY(-3px);
        }

        .mosaic-card--image {
            padding: 0;
            background-color: var(--gray-200);
        }

        .mosaic-card--image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .mosaic-card--dark {
            background-color: var(--green-950);
            color: var(--white);
            border-color: transparent;
        }

        .mosaic-card__value {
            font-size: clamp(32px, 3vw, 42px);
            font-weight: 800;
            color: var(--green-950);
            line-height: 1;
            margin-bottom: 8px;
            letter-spacing: -0.04em;
        }

        .mosaic-card--dark .mosaic-card__value {
            color: var(--lime);
        }

        .mosaic-card__label {
            font-size: 13.5px;
            font-weight: 600;
            color: var(--gray-700);
        }

        .mosaic-card--dark .mosaic-card__label {
            color: rgba(255, 255, 255, 0.8);
        }

        /* ==========================================================================
           8. CAPABILITY & ACCORDION SECTION
           ========================================================================== */
        .capability-section {
            padding: clamp(64px, 7vw, 96px) 0;
            background-color: var(--green-tint);
            border-top: 1px solid var(--gray-200);
            border-bottom: 1px solid var(--gray-200);
        }

        .capability-grid {
            display: grid;
            grid-template-columns: 0.9fr 1.1fr 1.2fr;
            gap: 36px;
            align-items: start;
        }

        .capability-image-box {
            border-radius: var(--radius-md);
            overflow: hidden;
            aspect-ratio: 4 / 4.8;
            box-shadow: var(--shadow-card);
            background-color: var(--gray-200);
        }

        .capability-image-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Accordion */
        .accordion-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .accordion-item {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-sm);
            overflow: hidden;
            transition: var(--transition);
        }

        .accordion-item.is-open {
            border-color: var(--green-800);
            box-shadow: var(--shadow-subtle);
        }

        .accordion-trigger {
            width: 100%;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            text-align: left;
            font-size: 15px;
            font-weight: 700;
            color: var(--green-950);
        }

        .accordion-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--green-pale);
            color: var(--green-950);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: transform var(--transition);
            flex-shrink: 0;
        }

        .accordion-item.is-open .accordion-icon {
            transform: rotate(180deg);
            background: var(--lime);
            color: var(--green-950);
        }

        .accordion-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
            font-size: 14px;
            color: var(--gray-700);
            line-height: 1.65;
            padding: 0 20px;
        }

        .accordion-item.is-open .accordion-body {
            padding: 0 20px 18px;
            max-height: 200px;
        }

        /* ==========================================================================
           9. PRODUCTS SECTION (MOST POPULAR COMPACT GRID)
           ========================================================================== */
        .products-section {
            padding: clamp(64px, 7vw, 96px) 0;
            background-color: var(--white);
        }

        .products-section__header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .product-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-sm);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform var(--transition), box-shadow var(--transition);
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-card);
            border-color: var(--green-800);
        }

        .product-card__media {
            position: relative;
            aspect-ratio: 1 / 1;
            background-color: var(--gray-100);
            overflow: hidden;
        }

        .product-card__media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform var(--transition-slow);
        }

        .product-card:hover .product-card__media img {
            transform: scale(1.05);
        }

        .product-card__status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 10.5px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: var(--radius-full);
            letter-spacing: 0.3px;
        }

        .product-card__status-badge--in-stock {
            background: var(--lime);
            color: var(--green-950);
        }

        .product-card__status-badge--out-of-stock {
            background: var(--gray-500);
            color: var(--white);
        }

        .product-card__content {
            padding: 16px 18px 18px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .product-card__name {
            font-size: 16px;
            font-weight: 700;
            color: var(--green-950);
            margin-bottom: 6px;
        }

        .product-card__price-row {
            display: flex;
            align-items: baseline;
            gap: 4px;
            margin-bottom: 8px;
        }

        .product-card__price {
            font-size: 17px;
            font-weight: 800;
            color: var(--green-800);
        }

        .product-card__unit {
            font-size: 12px;
            color: var(--gray-500);
        }

        .product-card__stock-info {
            font-size: 12px;
            color: var(--gray-700);
            margin-bottom: 14px;
        }

        .product-card__cta {
            margin-top: auto;
            width: 100%;
            padding: 9px 16px;
            font-size: 13px;
            border-radius: var(--radius-sm);
        }

        /* ==========================================================================
           10. HARVEST SECTION (REKAM PANEN)
           ========================================================================== */
        .harvest-section {
            padding: clamp(64px, 7vw, 96px) 0;
            background-color: var(--green-tint);
            border-top: 1px solid var(--gray-200);
        }

        .harvest-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 40px;
        }

        .harvest-card {
            background-color: var(--green-pale);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-md);
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: var(--transition);
        }

        .harvest-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-subtle);
        }

        .harvest-card:nth-child(3n + 2) {
            background-color: var(--green-950);
            color: var(--white);
            border-color: transparent;
        }

        .harvest-card:nth-child(3n + 2) h3 {
            color: var(--white);
        }

        .harvest-card:nth-child(3n + 2) .harvest-card__date {
            color: var(--lime);
        }

        .harvest-card:nth-child(3n + 2) .harvest-card__sub {
            color: rgba(255, 255, 255, 0.7);
        }

        .harvest-card:nth-child(3n + 2) .harvest-card__foot {
            border-top-color: rgba(255, 255, 255, 0.12);
        }

        .harvest-card:nth-child(3n + 2) .harvest-card__grade {
            background: rgba(120, 200, 0, 0.2);
            color: var(--lime);
        }

        .harvest-card__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .harvest-card__date {
            font-size: 13px;
            font-weight: 700;
            color: var(--green-800);
        }

        .harvest-card__grade {
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: var(--radius-full);
            background: var(--green-soft);
            color: var(--green-950);
        }

        .harvest-card__variety {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .harvest-card__sub {
            font-size: 13px;
            color: var(--gray-700);
            margin-bottom: 20px;
        }

        .harvest-card__foot {
            padding-top: 14px;
            border-top: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
        }

        .harvest-card__volume {
            font-size: 16px;
            font-weight: 800;
        }

        /* ==========================================================================
           11. GALLERY SECTION
           ========================================================================== */
        .gallery-section {
            padding: clamp(64px, 7vw, 96px) 0;
            background-color: var(--white);
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: 1.3fr 1fr 1fr;
            grid-template-rows: 240px 240px;
            gap: 16px;
            margin-top: 40px;
        }

        .gallery-item {
            position: relative;
            border-radius: var(--radius-md);
            overflow: hidden;
            background-color: var(--gray-100);
        }

        .gallery-item--large {
            grid-column: 1 / 2;
            grid-row: 1 / 3;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform var(--transition-slow);
        }

        .gallery-item:hover img {
            transform: scale(1.04);
        }

        .gallery-item__overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 16px 20px;
            background: linear-gradient(to top, rgba(23, 63, 24, 0.85), transparent);
            color: var(--white);
            font-size: 13px;
            font-weight: 600;
            opacity: 0;
            transition: opacity var(--transition);
        }

        .gallery-item:hover .gallery-item__overlay {
            opacity: 1;
        }

        /* ==========================================================================
           12. CONTACT SECTION (COMPACT CTA)
           ========================================================================== */
        .contact-cta {
            padding: 48px 32px;
            background-color: var(--green-pale);
            border-top: 1px solid var(--gray-200);
        }

        .contact-cta__inner {
            background-color: var(--green-950);
            color: var(--white);
            border-radius: var(--radius-lg);
            padding: clamp(36px, 5vw, 60px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 32px;
            flex-wrap: wrap;
        }

        .contact-cta__content h2 {
            font-size: clamp(24px, 3.2vw, 34px);
            color: var(--white);
            margin-bottom: 8px;
        }

        .contact-cta__content p {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.8);
            max-width: 520px;
        }

        .contact-cta__actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .contact-cta__meta {
            display: flex;
            gap: 16px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.7);
        }

        .contact-cta__meta a:hover {
            color: var(--lime);
        }

        /* ==========================================================================
           13. FOOTER
           ========================================================================== */
        .site-footer {
            background-color: var(--green-950);
            color: rgba(255, 255, 255, 0.7);
            padding: 56px 32px 28px;
            font-size: 13.5px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.4fr 0.8fr 1fr 0.8fr;
            gap: 36px;
            margin-bottom: 40px;
        }

        .footer-col__title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--white);
            margin-bottom: 16px;
        }

        .footer-nav {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .footer-nav a:hover {
            color: var(--lime);
        }

        .footer-contact-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .footer-contact-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .footer-contact-list i {
            color: var(--lime);
            margin-top: 4px;
        }

        .footer-gallery-thumb {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .footer-gallery-thumb img {
            border-radius: var(--radius-xs);
            aspect-ratio: 1/1;
            object-fit: cover;
        }

        .footer-bottom {
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
        }

        /* ==========================================================================
           14. REVEAL ON SCROLL
           ========================================================================== */
        .reveal {
            opacity: 0;
            transform: translateY(18px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }

        .reveal.is-visible {
            opacity: 1;
            transform: none;
        }

        @media (prefers-reduced-motion: reduce) {
            .reveal {
                opacity: 1;
                transform: none;
                transition: none;
            }
        }

        /* ==========================================================================
           15. RESPONSIVE BREAKPOINTS
           ========================================================================== */
        @media (max-width: 1200px) {
            .hero__grid {
                grid-template-columns: 1.5fr 1fr;
            }

            .hero__side-col:last-child {
                display: none;
            }

            .about-mosaic {
                grid-template-columns: repeat(2, 1fr);
            }

            .capability-grid {
                grid-template-columns: 1fr 1.2fr;
            }

            .capability-image-box {
                display: none;
            }

            .products-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 1024px) {
            .site-shell {
                width: 100%;
                margin: 0;
                border-radius: 0;
                box-shadow: none;
                border: none;
            }

            .about-header-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .gallery-grid {
                grid-template-columns: repeat(2, 1fr);
                grid-template-rows: auto;
            }

            .gallery-item--large {
                grid-column: 1 / 3;
                aspect-ratio: 16 / 9;
            }

            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 20px;
            }

            .navbar__nav,
            .navbar__actions {
                display: none;
            }

            .nav-toggle {
                display: flex;
            }

            .mobile-menu {
                display: block;
            }

            .hero {
                padding: 16px 16px 32px;
            }

            .hero__grid {
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .hero__side-col {
                display: none;
            }

            .hero__main {
                min-height: 480px;
                padding: 28px 20px;
            }

            .capability-grid {
                grid-template-columns: 1fr;
            }

            .products-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .harvest-grid {
                grid-template-columns: 1fr;
            }

            .contact-cta {
                padding: 32px 16px;
            }

            .contact-cta__inner {
                padding: 28px 20px;
            }

            .footer-grid {
                grid-template-columns: 1fr;
                gap: 28px;
            }

            .gallery-grid {
                grid-template-columns: 1fr;
            }

            .gallery-item--large {
                grid-column: 1 / 2;
            }
        }

        @media (max-width: 480px) {
            .products-grid {
                grid-template-columns: 1fr;
            }

            .about-mosaic {
                grid-template-columns: 1fr;
            }

            .hero__actions .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    {{-- ==========================================================================
         PREVIEW BANNER
         ========================================================================== --}}
    @if ($isPreview)
        <div class="preview-banner" role="banner" aria-label="Mode Pratinjau">
            <div class="container preview-banner__inner">
                <span>
                    <i class="fas fa-eye"></i> Mode Preview &mdash; Anda sedang melihat tampilan publik etalase usaha Anda.
                </span>
                <a href="{{ route('farmer.website.index') }}" class="preview-banner__btn">
                    Kembali ke Dashboard <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    @endif

    <div class="site-shell">

        {{-- ==========================================================================
             NAVBAR
             ========================================================================== --}}
        <header class="site-header" id="navbar">
            <div class="container">
                <nav class="navbar" aria-label="Navigasi Utama">
                    <a href="#beranda" class="navbar__brand">
                        <div class="navbar__logo">
                            @if (!empty($profile['logo_url']))
                                <img src="{{ $profile['logo_url'] }}" alt="{{ $profile['business_name'] }}" style="width:100%; height:100%; object-fit:cover;">
                            @else
                                <i class="fas fa-seedling"></i>
                            @endif
                        </div>
                        <span class="navbar__brand-name">{{ $profile['business_name'] }}</span>
                    </a>

                    <ul class="navbar__nav">
                        <li><a href="#beranda" class="navbar__link">Beranda</a></li>
                        <li><a href="#tentang" class="navbar__link">Tentang</a></li>
                        @if ($sections['show_products'] && count($products) > 0)
                            <li><a href="#produk" class="navbar__link">Produk</a></li>
                        @endif
                        @if ($sections['show_harvests'] && count($harvests) > 0)
                            <li><a href="#panen" class="navbar__link">Panen</a></li>
                        @endif
                        @if ($sections['show_gallery'] && count($gallery) > 0)
                            <li><a href="#galeri" class="navbar__link">Galeri</a></li>
                        @endif
                        @if ($sections['show_contact'] && $contact)
                            <li><a href="#kontak" class="navbar__link">Kontak</a></li>
                        @endif
                    </ul>

                    <div class="navbar__actions">
                        @if ($sections['show_contact'] && !empty($contact['whatsapp']))
                            <a href="{{ $contact['whatsapp'] }}" target="_blank" rel="noopener noreferrer" class="btn btn--lime" style="padding: 10px 20px; font-size: 13.5px;">
                                Hubungi Kami
                                <span class="btn__icon-circle"><i class="fas fa-arrow-up-right-from-square"></i></span>
                            </a>
                        @elseif ($sections['show_contact'])
                            <a href="#kontak" class="btn btn--lime" style="padding: 10px 20px; font-size: 13.5px;">
                                Hubungi
                                <span class="btn__icon-circle"><i class="fas fa-arrow-right"></i></span>
                            </a>
                        @endif
                    </div>

                    <button type="button" class="nav-toggle" id="navToggle" aria-expanded="false" aria-controls="mobileDrawer" aria-label="Buka Menu">
                        <i class="fas fa-bars"></i>
                    </button>
                </nav>
            </div>
        </header>

        {{-- Mobile Drawer --}}
        <div class="mobile-menu" id="mobileDrawer" role="dialog" aria-modal="true" aria-label="Menu Navigasi Mobile">
            <div class="mobile-menu__panel">
                <div>
                    <div class="mobile-menu__header">
                        <div class="navbar__brand">
                            <div class="navbar__logo">
                                @if (!empty($profile['logo_url']))
                                    <img src="{{ $profile['logo_url'] }}" alt="{{ $profile['business_name'] }}" style="width:100%; height:100%; object-fit:cover;">
                                @else
                                    <i class="fas fa-seedling"></i>
                                @endif
                            </div>
                            <span class="navbar__brand-name">{{ $profile['business_name'] }}</span>
                        </div>
                        <button type="button" class="mobile-menu__close" id="navClose" aria-label="Tutup Menu">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>

                    <ul class="mobile-menu__nav">
                        <li><a href="#beranda" class="mobile-menu__link mobile-item">Beranda</a></li>
                        <li><a href="#tentang" class="mobile-menu__link mobile-item">Tentang</a></li>
                        @if ($sections['show_products'] && count($products) > 0)
                            <li><a href="#produk" class="mobile-menu__link mobile-item">Produk</a></li>
                        @endif
                        @if ($sections['show_harvests'] && count($harvests) > 0)
                            <li><a href="#panen" class="mobile-menu__link mobile-item">Panen</a></li>
                        @endif
                        @if ($sections['show_gallery'] && count($gallery) > 0)
                            <li><a href="#galeri" class="mobile-menu__link mobile-item">Galeri</a></li>
                        @endif
                        @if ($sections['show_contact'] && $contact)
                            <li><a href="#kontak" class="mobile-menu__link mobile-item">Kontak</a></li>
                        @endif
                    </ul>
                </div>

                <div>
                    @if ($sections['show_contact'] && !empty($contact['whatsapp']))
                        <a href="{{ $contact['whatsapp'] }}" target="_blank" rel="noopener noreferrer" class="btn btn--lime" style="width: 100%;">
                            Chat WhatsApp <i class="fab fa-whatsapp"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <header>
            {{-- ==========================================================================
                 HERO SECTION (GREENMARKET 3-IMAGE ASYMMETRIC GRID)
                 ========================================================================== --}}
            @php
                $heroCover = !empty($profile['cover_image_url'])
                    ? $profile['cover_image_url']
                    : 'https://images.unsplash.com/photo-1586771107445-d3ca888129ff?auto=format&fit=crop&w=1600&q=80';

                $galleryColl = collect($gallery ?? []);
                $sideImg1 = $galleryColl->get(0);
                $sideImg2 = $galleryColl->get(1);

                $sideUrl1 = $sideImg1 
                    ? (is_array($sideImg1) ? ($sideImg1['image_url'] ?? null) : (isset($sideImg1->image_url) ? $sideImg1->image_url : asset('storage/' . ($sideImg1->image_path ?? ''))))
                    : null;
                $sideUrl2 = $sideImg2 
                    ? (is_array($sideImg2) ? ($sideImg2['image_url'] ?? null) : (isset($sideImg2->image_url) ? $sideImg2->image_url : asset('storage/' . ($sideImg2->image_path ?? ''))))
                    : null;

                $uniqueCommodities = collect($products ?? [])->map(function($p) {
                    return is_array($p) ? ($p['commodity'] ?? null) : ($p->commodity ?? null);
                })->filter()->unique()->take(4);
            @endphp

            <div class="hero" id="beranda">
                <div class="hero__grid">
                    {{-- Main Large Cover Image --}}
                    <div class="hero__main reveal" style="background-image: url('{{ $heroCover }}');">
                        <div class="hero__main-content">
                            @if (!empty($profile['is_verified']))
                                <div class="hero__badge">
                                    <i class="fas fa-certificate"></i> TERVERIFIKASI P.A.D.I.
                                </div>
                            @else
                                <div class="hero__badge">
                                    <i class="fas fa-seedling"></i> P.A.D.I. AGRICULTURE
                                </div>
                            @endif

                            <h1 class="hero__title">
                                {{ $profile['headline'] ?? $profile['business_name'] }}
                            </h1>

                            @if (!empty($profile['description']))
                                <p class="hero__desc">
                                    {{ $profile['description'] }}
                                </p>
                            @endif

                            <div class="hero__actions">
                                @if ($sections['show_products'] && count($products) > 0)
                                    <a href="#produk" class="btn btn--lime">
                                        Lihat Produk
                                        <span class="btn__icon-circle"><i class="fas fa-arrow-right"></i></span>
                                    </a>
                                @endif

                                @if ($sections['show_contact'] && !empty($contact['whatsapp']))
                                    <a href="{{ $contact['whatsapp'] }}" target="_blank" rel="noopener noreferrer" class="btn btn--white">
                                        Hubungi
                                        <span class="btn__icon-circle"><i class="fas fa-arrow-up-right-from-square"></i></span>
                                    </a>
                                @elseif ($sections['show_contact'])
                                    <a href="#kontak" class="btn btn--white">
                                        Hubungi
                                        <span class="btn__icon-circle"><i class="fas fa-arrow-right"></i></span>
                                    </a>
                                @endif
                            </div>

                            @if ($uniqueCommodities->isNotEmpty())
                                <div class="hero__categories">
                                    <span style="font-size: 12px; color: rgba(255,255,255,0.75); margin-right: 4px;">Komoditas:</span>
                                    @foreach ($uniqueCommodities as $comm)
                                        <span class="hero__category-chip">{{ $comm }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Side Image 1 --}}
                    @if ($sideUrl1)
                        <div class="hero__side-col reveal">
                            <img src="{{ $sideUrl1 }}" alt="Lahan Pertanian" loading="eager">
                            <div class="hero__side-overlay">
                                <span>Dokumentasi Budidaya</span>
                            </div>
                        </div>
                    @endif

                    {{-- Side Image 2 --}}
                    @if ($sideUrl2)
                        <div class="hero__side-col reveal">
                            <img src="{{ $sideUrl2 }}" alt="Hasil Panen" loading="eager">
                            <div class="hero__side-overlay">
                                <span>Standar Mutu P.A.D.I.</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </header>

        <main>
            {{-- ==========================================================================
                 ABOUT & MOSAIC SECTION
                 ========================================================================== --}}
            <section class="about-section" id="tentang">
                <div class="container">
                    <div class="about-header-grid reveal">
                        <div>
                            <span class="section-eyebrow">Tentang Kami</span>
                        </div>
                        <div>
                            <h2 style="font-size: clamp(26px, 3.5vw, 36px);">
                                <span class="heading-highlight">Tumbuh Bersama</span>, Menghasilkan Pasokan Pertanian Lebih Baik.
                            </h2>
                        </div>
                        <div>
                            <p style="color: var(--gray-700); font-size: 14.5px;">
                                {{ $profile['description'] ?? 'Berfokus pada penyediaan hasil panen terkurasi dari lahan terverifikasi dengan konsistensi mutu dan kejujuran spesifikasi.' }}
                            </p>
                        </div>
                    </div>

                    {{-- Mosaic Grid --}}
                    <div class="about-mosaic">
                        @if ($sections['show_productivity'] && $statistics && !empty($statistics['total_area_ha']))
                            <div class="mosaic-card reveal">
                                <div class="mosaic-card__value">
                                    {{ $statistics['total_area_ha'] }}<span style="font-size: 20px; font-weight: 700; color: var(--green-800);"> Ha</span>
                                </div>
                                <div class="mosaic-card__label">Total Luas Lahan Pengelolaan</div>
                            </div>
                        @endif

                        @if ($sideUrl1)
                            <div class="mosaic-card mosaic-card--image reveal">
                                <img src="{{ $sideUrl1 }}" alt="Lahan" loading="lazy">
                            </div>
                        @endif

                        @if ($sections['show_productivity'] && $statistics && !empty($statistics['total_seasons']))
                            <div class="mosaic-card mosaic-card--dark reveal">
                                <div class="mosaic-card__value">
                                    {{ $statistics['total_seasons'] }}
                                </div>
                                <div class="mosaic-card__label">Musim Tanam Terdokumentasi</div>
                            </div>
                        @endif

                        @if ($sections['show_productivity'] && $statistics && !empty($statistics['latest_productivity']))
                            <div class="mosaic-card reveal">
                                <div class="mosaic-card__value">
                                    {{ $statistics['latest_productivity'] }}<span style="font-size: 18px; font-weight: 700; color: var(--green-800);"> t/ha</span>
                                </div>
                                <div class="mosaic-card__label">Produktivitas Panen Terakhir</div>
                            </div>
                        @elseif ($sideUrl2)
                            <div class="mosaic-card mosaic-card--image reveal">
                                <img src="{{ $sideUrl2 }}" alt="Panen" loading="lazy">
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            {{-- ==========================================================================
                 CAPABILITY & ACCORDION SECTION
                 ========================================================================== --}}
            <section class="capability-section">
                <div class="container">
                    <div class="capability-grid">
                        <div class="reveal">
                            <span class="section-eyebrow">Kapabilitas</span>
                            <h2 style="font-size: clamp(26px, 3.2vw, 34px); margin-bottom: 16px;">
                                Informasi & Kapabilitas Pasokan
                            </h2>
                            <p style="color: var(--gray-700); font-size: 14.5px; margin-bottom: 24px;">
                                Detail kesiapan lahan, kapasitas produksi, dan mekanisme pasokan yang siap bekerja sama dengan mitra distribusi.
                            </p>
                            @if ($sections['show_contact'] && !empty($contact['whatsapp']))
                                <a href="{{ $contact['whatsapp'] }}" target="_blank" rel="noopener noreferrer" class="btn btn--dark">
                                    Konsultasi Pasokan
                                    <span class="btn__icon-circle"><i class="fas fa-arrow-right"></i></span>
                                </a>
                            @endif
                        </div>

                        <div class="capability-image-box reveal">
                            <img src="{{ $sideUrl1 ?: $heroCover }}" alt="{{ $profile['business_name'] }}" loading="lazy">
                        </div>

                        <div class="reveal">
                            <div class="accordion-list" id="capabilityAccordion">
                                @if ($sections['show_productivity'] && $statistics && !empty($statistics['total_area_ha']))
                                    <div class="accordion-item is-open">
                                        <button type="button" class="accordion-trigger" aria-expanded="true">
                                            <span>Informasi Lahan & Kapasitas</span>
                                            <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
                                        </button>
                                        <div class="accordion-body">
                                            Pengelolaan lahan produktif seluas <strong>{{ $statistics['total_area_ha'] }} Hektar</strong> dengan rekam jejak {{ $statistics['total_seasons'] ?? 0 }} siklus tanam aktif.
                                        </div>
                                    </div>
                                @endif

                                @if ($sections['show_products'] && count($products) > 0)
                                    <div class="accordion-item">
                                        <button type="button" class="accordion-trigger" aria-expanded="false">
                                            <span>Kesiapan Produk Komoditas</span>
                                            <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
                                        </button>
                                        <div class="accordion-body">
                                            Tersedia <strong>{{ count($products) }} komoditas</strong> siap pasok dengan spesifikasi tertera jelas langsung dari gudang/lahan.
                                        </div>
                                    </div>
                                @endif

                                @if ($sections['show_harvests'] && count($harvests) > 0)
                                    <div class="accordion-item">
                                        <button type="button" class="accordion-trigger" aria-expanded="false">
                                            <span>Transparansi Riwayat Panen</span>
                                            <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
                                        </button>
                                        <div class="accordion-body">
                                            Terdapat <strong>{{ count($harvests) }} catatan panen</strong> dengan rincian varietas, periode, dan grade mutu yang terverifikasi.
                                        </div>
                                    </div>
                                @endif

                                @if ($sections['show_location'] && !empty($location['address']))
                                    <div class="accordion-item">
                                        <button type="button" class="accordion-trigger" aria-expanded="false">
                                            <span>Lokasi Operasional</span>
                                            <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
                                        </button>
                                        <div class="accordion-body">
                                            Lahan dan fasilitas pascapanen beralamat di <strong>{{ $location['address'] }}</strong>.
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ==========================================================================
                 PRODUCTS SECTION (MOST POPULAR COMPACT GRID)
                 ========================================================================== --}}
            @if ($sections['show_products'] && count($products) > 0)
                <section class="products-section" id="produk">
                    <div class="container">
                        <div class="products-section__header reveal">
                            <div>
                                <span class="section-eyebrow">Katalog Komoditas</span>
                                <h2 style="font-size: clamp(26px, 3.5vw, 36px);">
                                    <span class="heading-highlight">Produk Unggulan</span> ↗
                                </h2>
                            </div>
                            <div>
                                <p style="color: var(--gray-700); font-size: 14.5px;">
                                    Komoditas pilihan yang siap dipesan langsung dari usaha tani.
                                </p>
                            </div>
                        </div>

                        <div class="products-grid">
                            @foreach ($products as $product)
                                @php
                                    $prodImg = is_array($product) ? ($product['image_url'] ?? '') : ($product->image_url ?? '');
                                    $prodCommodity = is_array($product) ? ($product['commodity'] ?? '') : ($product->commodity ?? '');
                                    $prodQty = is_array($product) ? ($product['quantity'] ?? null) : ($product->quantity ?? null);
                                    $prodUnit = is_array($product) ? ($product['unit'] ?? '') : ($product->unit ?? '');
                                    $prodPrice = is_array($product) ? ($product['price_per_unit'] ?? null) : ($product->price_per_unit ?? null);
                                    $prodDesc = is_array($product) ? ($product['description'] ?? '') : ($product->description ?? '');
                                    $prodLink = is_array($product) ? ($product['sales_link'] ?? '') : ($product->sales_link ?? '');

                                    $inStock = ($prodQty !== null && $prodQty > 0);
                                @endphp
                                <article class="product-card reveal">
                                    <div class="product-card__media">
                                        @if (!empty($prodImg))
                                            <img src="{{ $prodImg }}" alt="{{ $prodCommodity }}" loading="lazy">
                                        @else
                                            <img src="https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=500&q=80"
                                                 alt="{{ $prodCommodity }}" loading="lazy">
                                        @endif

                                        @if ($inStock)
                                            <span class="product-card__status-badge product-card__status-badge--in-stock">Tersedia</span>
                                        @elseif ($prodQty !== null && $prodQty == 0)
                                            <span class="product-card__status-badge product-card__status-badge--out-of-stock">Stok Habis</span>
                                        @endif
                                    </div>

                                    <div class="product-card__content">
                                        <h3 class="product-card__name">{{ $prodCommodity }}</h3>

                                        @if (!empty($prodPrice))
                                            <div class="product-card__price-row">
                                                <span class="product-card__price">
                                                    Rp {{ number_format($prodPrice, 0, ',', '.') }}
                                                </span>
                                                @if (!empty($prodUnit))
                                                    <span class="product-card__unit">/ {{ $prodUnit }}</span>
                                                @endif
                                            </div>
                                        @endif

                                        @if (!empty($prodQty) && !empty($prodUnit))
                                            <div class="product-card__stock-info">
                                                Stok: {{ $prodQty }} {{ $prodUnit }}
                                            </div>
                                        @endif

                                        @if (!empty($prodLink))
                                            <a href="{{ $prodLink }}" target="_blank" rel="noopener noreferrer" class="btn btn--lime product-card__cta">
                                                <i class="fas fa-bag-shopping"></i> Pesan
                                            </a>
                                        @elseif ($sections['show_contact'] && !empty($contact['whatsapp']))
                                            @php
                                                $waMsg = urlencode("Halo {$profile['business_name']}, saya tertarik untuk memesan komoditas {$prodCommodity}. Mohon info detail ketersediaan.");
                                                $waUrl = $contact['whatsapp'] . (str_contains($contact['whatsapp'], '?') ? '&' : '?') . "text={$waMsg}";
                                            @endphp
                                            <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn--lime product-card__cta">
                                                <i class="fab fa-whatsapp"></i> Pesan
                                            </a>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            {{-- ==========================================================================
                 HARVEST SECTION (REKAM PANEN)
                 ========================================================================== --}}
            @if ($sections['show_harvests'] && count($harvests) > 0)
                <section class="harvest-section" id="panen">
                    <div class="container">
                        <div class="reveal">
                            <span class="section-eyebrow">Transparansi</span>
                            <h2 style="font-size: clamp(26px, 3.5vw, 36px);">Rekam Panen</h2>
                            <p style="color: var(--gray-700); font-size: 14.5px;">
                                Data produksi yang tercatat dan transparan dari setiap siklus panen.
                            </p>
                        </div>

                        <div class="harvest-grid">
                            @foreach ($harvests as $harvest)
                                @php
                                    $hDate = is_array($harvest) ? $harvest['harvest_date'] : $harvest->harvest_date;
                                    $hGrade = is_array($harvest) ? ($harvest['quality_grade'] ?? 'A') : ($harvest->quality_grade ?? 'A');
                                    $hVariety = is_array($harvest) ? ($harvest['variety_name'] ?? 'Varietas Padi') : ($harvest->variety_name ?? 'Varietas Padi');
                                    $hFarm = is_array($harvest) ? ($harvest['farm_name'] ?? 'Lahan Utama') : ($harvest->farm_name ?? 'Lahan Utama');
                                    $hQty = is_array($harvest) ? ($harvest['quantity'] ?? 0) : ($harvest->quantity ?? 0);
                                    $hUnit = is_array($harvest) ? ($harvest['unit'] ?? '') : ($harvest->unit ?? '');
                                @endphp
                                <div class="harvest-card reveal">
                                    <div>
                                        <div class="harvest-card__header">
                                            <span class="harvest-card__date">
                                                {{ \Carbon\Carbon::parse($hDate)->translatedFormat('F Y') }}
                                            </span>
                                            <span class="harvest-card__grade">
                                                Grade {{ $hGrade }}
                                            </span>
                                        </div>

                                        <h3 class="harvest-card__variety">
                                            {{ $hVariety }}
                                        </h3>

                                        <div class="harvest-card__sub">
                                            {{ $hFarm }}
                                        </div>
                                    </div>

                                    <div class="harvest-card__foot">
                                        <span>Hasil Panen</span>
                                        <span class="harvest-card__volume">
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
                 GALLERY SECTION (EDITORIAL MASONRY)
                 ========================================================================== --}}
            @if ($sections['show_gallery'] && count($gallery) > 0)
                <section class="gallery-section" id="galeri">
                    <div class="container">
                        <div class="reveal">
                            <span class="section-eyebrow">Dokumentasi</span>
                            <h2 style="font-size: clamp(26px, 3.5vw, 36px);">Dokumentasi Lapangan</h2>
                            <p style="color: var(--gray-700); font-size: 14.5px;">
                                Sekilas aktivitas lapangan, proses budidaya, dan pascapanen.
                            </p>
                        </div>

                        <div class="gallery-grid">
                            @foreach ($gallery as $item)
                                @php
                                    $imgSrc = is_array($item) 
                                        ? ($item['image_url'] ?? '') 
                                        : (isset($item->image_url) ? $item->image_url : asset('storage/' . ($item->image_path ?? '')));
                                    $cap = is_array($item) ? ($item['caption'] ?? '') : ($item->caption ?? '');
                                    $isLarge = ($loop->first);
                                @endphp
                                <div class="gallery-item {{ $isLarge ? 'gallery-item--large' : '' }} reveal">
                                    <img src="{{ $imgSrc }}" alt="{{ $cap ?: 'Dokumentasi ' . $profile['business_name'] }}" loading="lazy">
                                    @if (!empty($cap))
                                        <div class="gallery-item__overlay">{{ $cap }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            {{-- ==========================================================================
                 CONTACT SECTION (COMPACT CTA)
                 ========================================================================== --}}
            @if ($sections['show_contact'] && $contact)
                <section class="contact-cta" id="kontak">
                    <div class="contact-cta__inner reveal">
                        <div class="contact-cta__content">
                            <h2>Siap bekerja sama?</h2>
                            <p>
                                Hubungi langsung untuk ketersediaan produk, harga, pembelian, atau kerja sama pasokan berkelanjutan.
                            </p>
                        </div>

                        <div class="contact-cta__actions">
                            @if (!empty($contact['whatsapp']))
                                <a href="{{ $contact['whatsapp'] }}" target="_blank" rel="noopener noreferrer" class="btn btn--lime">
                                    <i class="fab fa-whatsapp"></i> Chat WhatsApp
                                    <span class="btn__icon-circle"><i class="fas fa-arrow-right"></i></span>
                                </a>
                            @endif

                            <div class="contact-cta__meta">
                                @if (!empty($contact['public_phone']))
                                    <a href="tel:{{ $contact['public_phone'] }}">
                                        <i class="fas fa-phone"></i> {{ $contact['public_phone'] }}
                                    </a>
                                @endif
                                @if (!empty($contact['public_email']))
                                    <a href="mailto:{{ $contact['public_email'] }}">
                                        <i class="fas fa-envelope"></i> {{ $contact['public_email'] }}
                                    </a>
                                @endif
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
                <div class="footer-grid">
                    <div>
                        <h3 class="footer-col__title">{{ $profile['business_name'] }}</h3>
                        <p style="line-height: 1.6; margin-bottom: 16px;">
                            {{ $profile['headline'] ?? 'Penyedia hasil panen pertanian berkualitas dengan komitmen transparansi dan mutu terjamin.' }}
                        </p>
                        <span style="font-size: 12px; color: var(--lime);">
                            <i class="fas fa-certificate"></i> Terverifikasi P.A.D.I.
                        </span>
                    </div>

                    <div>
                        <h4 class="footer-col__title">Navigasi</h4>
                        <ul class="footer-nav">
                            <li><a href="#beranda">Beranda</a></li>
                            <li><a href="#tentang">Tentang Kami</a></li>
                            @if ($sections['show_products'] && count($products) > 0)
                                <li><a href="#produk">Produk</a></li>
                            @endif
                            @if ($sections['show_harvests'] && count($harvests) > 0)
                                <li><a href="#panen">Panen</a></li>
                            @endif
                            @if ($sections['show_gallery'] && count($gallery) > 0)
                                <li><a href="#galeri">Galeri</a></li>
                            @endif
                        </ul>
                    </div>

                    <div>
                        <h4 class="footer-col__title">Kontak</h4>
                        <ul class="footer-contact-list">
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
                        </ul>
                    </div>

                    <div>
                        <h4 class="footer-col__title">Dokumentasi</h4>
                        @if (isset($gallery) && count($gallery) > 0)
                            <div class="footer-gallery-thumb">
                                @foreach (collect($gallery)->take(4) as $thumb)
                                    @php
                                        $thumbSrc = is_array($thumb) 
                                            ? ($thumb['image_url'] ?? '') 
                                            : (isset($thumb->image_url) ? $thumb->image_url : asset('storage/' . ($thumb->image_path ?? '')));
                                    @endphp
                                    <img src="{{ $thumbSrc }}" alt="Galeri" loading="lazy">
                                @endforeach
                            </div>
                        @else
                            <p style="font-size: 12.5px; color: rgba(255, 255, 255, 0.45);">
                                Dokumentasi lahan usaha tani.
                            </p>
                        @endif
                    </div>
                </div>

                <div class="footer-bottom">
                    <div>
                        &copy; {{ date('Y') }} {{ $profile['business_name'] }}. Hak cipta dilindungi.
                    </div>
                    <div>
                        Powered by <strong>P.A.D.I.</strong>
                    </div>
                </div>
            </div>
        </footer>

    </div> {{-- /.site-shell --}}

    {{-- ==========================================================================
         SCRIPTS (VANILLA JAVASCRIPT)
         ========================================================================== --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Navbar Scroll State
            const navbar = document.getElementById('navbar');
            function handleScroll() {
                if (!navbar) return;
                if (window.scrollY > 20) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            }
            window.addEventListener('scroll', handleScroll, { passive: true });
            handleScroll();

            // 2. Mobile Menu Toggle
            const navToggle = document.getElementById('navToggle');
            const navClose = document.getElementById('navClose');
            const mobileDrawer = document.getElementById('mobileDrawer');
            const mobileItems = document.querySelectorAll('.mobile-item');

            function openDrawer() {
                if (mobileDrawer) {
                    mobileDrawer.classList.add('is-open');
                    if (navToggle) navToggle.setAttribute('aria-expanded', 'true');
                    document.body.style.overflow = 'hidden';
                }
            }

            function closeDrawer() {
                if (mobileDrawer) {
                    mobileDrawer.classList.remove('is-open');
                    if (navToggle) navToggle.setAttribute('aria-expanded', 'false');
                    document.body.style.overflow = '';
                }
            }

            if (navToggle) navToggle.addEventListener('click', openDrawer);
            if (navClose) navClose.addEventListener('click', closeDrawer);
            if (mobileDrawer) {
                mobileDrawer.addEventListener('click', function (e) {
                    if (e.target === mobileDrawer) closeDrawer();
                });
            }
            mobileItems.forEach(item => item.addEventListener('click', closeDrawer));

            // 3. Capability Accordion (Single Open)
            const accordionTriggers = document.querySelectorAll('.accordion-trigger');
            accordionTriggers.forEach(trigger => {
                trigger.addEventListener('click', function () {
                    const item = this.parentElement;
                    const isOpen = item.classList.contains('is-open');

                    // Close all
                    document.querySelectorAll('.accordion-item').forEach(el => {
                        el.classList.remove('is-open');
                        const btn = el.querySelector('.accordion-trigger');
                        if (btn) btn.setAttribute('aria-expanded', 'false');
                    });

                    // Toggle current
                    if (!isOpen) {
                        item.classList.add('is-open');
                        this.setAttribute('aria-expanded', 'true');
                    }
                });
            });

            // 4. Smooth Scrolling for Internal Links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    const targetId = this.getAttribute('href');
                    if (targetId === '#' || targetId === '') return;
                    const targetEl = document.querySelector(targetId);
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

            // 5. Reveal on Scroll Animation (IntersectionObserver)
            if ('IntersectionObserver' in window) {
                const revealObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.08,
                    rootMargin: '0px 0px -30px 0px'
                });

                document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));
            } else {
                document.querySelectorAll('.reveal').forEach(el => el.classList.add('is-visible'));
            }
        });
    </script>
</body>

</html>