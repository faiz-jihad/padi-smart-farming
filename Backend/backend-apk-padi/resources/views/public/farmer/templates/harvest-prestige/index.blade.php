<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="{{ $profile['headline'] ?? 'Profil Usaha Pertanian dan Pasokan Hasil Panen Padi Berkualitas' }} — {{ $profile['business_name'] }}">
    <meta name="robots" content="{{ $isPreview ? 'noindex, nofollow' : 'index, follow' }}">

    <title>{{ $profile['business_name'] }} &mdash; Profil Usaha Tani</title>

    @if (!empty($profile['logo_url']))
        <link rel="icon" type="image/png" href="{{ $profile['logo_url'] }}">
        <link rel="shortcut icon" href="{{ $profile['logo_url'] }}">
        <link rel="apple-touch-icon" href="{{ $profile['logo_url'] }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google Fonts: Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* ==========================================================================
           1. DESIGN TOKENS & RESET
           ========================================================================== */
        :root {
            --green-950: #123b2b;
            --green-900: #174936;
            --green-800: #205a43;
            --green-700: #2b7055;
            --green-100: #e8f3e9;
            --green-50: #f5f9f5;

            --white: #ffffff;
            --off-white: #fafbf9;
            --black: #141714;
            --gray-800: #262c28;
            --gray-700: #525a54;
            --gray-500: #7d857f;
            --gray-200: #e8ebe8;
            --gray-100: #f2f4f2;

            --radius-xs: 4px;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-full: 9999px;

            --shadow-subtle: 0 4px 20px rgba(18, 59, 43, 0.05);
            --shadow-card: 0 8px 30px rgba(18, 59, 43, 0.07);

            --transition: 180ms ease;
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
            font-family: 'Inter', 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--white);
            color: var(--black);
            line-height: 1.7;
            font-size: 15.5px;
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
            transition: var(--transition);
        }

        button {
            font-family: inherit;
            border: none;
            background: transparent;
            cursor: pointer;
        }

        /* Accessibility Focus */
        :focus-visible {
            outline: 2px solid var(--green-800);
            outline-offset: 3px;
        }

        .container {
            width: 100%;
            max-width: 1180px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* ==========================================================================
           2. TYPOGRAPHY & BUTTONS
           ========================================================================== */
        h1, h2, h3, h4 {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            font-weight: 700;
            letter-spacing: -0.03em;
            color: var(--green-950);
            line-height: 1.2;
        }

        .section-eyebrow {
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.8px;
            color: var(--green-800);
            margin-bottom: 12px;
        }

        .section-title {
            font-size: clamp(28px, 4vw, 40px);
            margin-bottom: 16px;
        }

        .section-subtitle {
            font-size: clamp(15px, 1.4vw, 17px);
            color: var(--gray-700);
            line-height: 1.7;
            max-width: 600px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 13px 28px;
            font-size: 14.5px;
            font-weight: 600;
            border-radius: var(--radius-full);
            transition: var(--transition);
            white-space: nowrap;
            letter-spacing: -0.1px;
        }

        .btn--dark {
            background-color: var(--green-950);
            color: var(--white);
        }

        .btn--dark:hover {
            background-color: var(--green-900);
            color: var(--white);
            transform: translateY(-1px);
        }

        .btn--white {
            background-color: var(--white);
            color: var(--green-950);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .btn--white:hover {
            background-color: var(--off-white);
            color: var(--green-950);
            transform: translateY(-1px);
        }

        .btn--outline-white {
            background: transparent;
            color: var(--white);
            border: 1.5px solid rgba(255, 255, 255, 0.4);
        }

        .btn--outline-white:hover {
            border-color: var(--white);
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
        }

        .btn--outline-dark {
            background: transparent;
            color: var(--green-950);
            border: 1.5px solid var(--gray-200);
        }

        .btn--outline-dark:hover {
            border-color: var(--green-950);
            background-color: var(--green-50);
        }

        /* ==========================================================================
           3. PREVIEW BAR
           ========================================================================== */
        .preview-banner {
            position: sticky;
            top: 0;
            z-index: 1200;
            background-color: var(--green-100);
            color: var(--green-950);
            border-bottom: 1px solid rgba(18, 59, 43, 0.15);
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
            background: var(--green-950);
            color: var(--white);
            padding: 5px 14px;
            border-radius: var(--radius-full);
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .preview-banner__btn:hover {
            background: var(--green-900);
        }

        /* ==========================================================================
           4. NAVBAR
           ========================================================================== */
        .site-header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background-color: var(--white);
            transition: var(--transition);
            border-bottom: 1px solid transparent;
        }

        .site-header.scrolled {
            border-bottom-color: var(--gray-200);
            box-shadow: var(--shadow-subtle);
        }

        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 76px;
            gap: 24px;
        }

        .navbar__brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .navbar__logo {
            width: 44px;
            height: 44px;
            border-radius: var(--radius-sm);
            background-color: var(--green-950);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
            overflow: hidden;
            flex-shrink: 0;
        }

        .navbar__logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .navbar__brand-name {
            font-size: 17px;
            font-weight: 700;
            color: var(--green-950);
            letter-spacing: -0.3px;
        }

        .navbar__nav {
            display: flex;
            align-items: center;
            gap: 32px;
            list-style: none;
        }

        .navbar__link {
            font-size: 14.5px;
            font-weight: 500;
            color: var(--gray-700);
            transition: color var(--transition);
        }

        .navbar__link:hover {
            color: var(--green-950);
        }

        .navbar__actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .nav-toggle {
            display: none;
            width: 40px;
            height: 40px;
            border-radius: var(--radius-sm);
            background-color: var(--gray-100);
            color: var(--green-950);
            align-items: center;
            justify-content: center;
            font-size: 17px;
        }

        /* Mobile Drawer */
        .mobile-menu {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(18, 59, 43, 0.4);
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
            box-shadow: -8px 0 24px rgba(0, 0, 0, 0.1);
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
            margin-bottom: 28px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--gray-200);
        }

        .mobile-menu__close {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            background: var(--gray-100);
            color: var(--green-950);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
        }

        .mobile-menu__nav {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .mobile-menu__link {
            font-size: 16px;
            font-weight: 600;
            color: var(--green-950);
            display: block;
            padding: 6px 0;
        }

        /* ==========================================================================
           5. HERO FULL IMAGE
           ========================================================================== */
        .hero {
            position: relative;
            min-height: 620px;
            display: flex;
            align-items: center;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: var(--white);
            padding: 80px 0 100px;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                90deg,
                rgba(9, 38, 26, 0.82) 0%,
                rgba(9, 38, 26, 0.45) 60%,
                rgba(9, 38, 26, 0.15) 100%
            );
        }

        .hero__container {
            position: relative;
            z-index: 2;
        }

        .hero__content {
            max-width: 620px;
        }

        .hero__badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: var(--radius-full);
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--white);
            margin-bottom: 24px;
        }

        .hero__title {
            font-size: clamp(38px, 5.2vw, 68px);
            font-weight: 700;
            line-height: 1.02;
            letter-spacing: -1.8px;
            color: var(--white);
            margin-bottom: 20px;
        }

        .hero__description {
            font-size: clamp(15px, 1.4vw, 16.5px);
            color: rgba(255, 255, 255, 0.85);
            line-height: 1.7;
            max-width: 520px;
            margin-bottom: 32px;
        }

        .hero__actions {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        /* ==========================================================================
           6. HERO STATISTICS OVERLAP
           ========================================================================== */
        .stats-overlap {
            margin-top: -48px;
            position: relative;
            z-index: 10;
            margin-bottom: 40px;
        }

        .stats-panel {
            background-color: var(--white);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-card);
            border: 1px solid var(--gray-200);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            overflow: hidden;
        }

        .stat-item {
            padding: 24px 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-right: 1px solid var(--gray-200);
            transition: background-color var(--transition);
        }

        .stat-item:last-child {
            border-right: none;
        }

        .stat-item--highlight {
            background-color: var(--green-50);
        }

        .stat-item__value {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 32px;
            font-weight: 800;
            color: var(--green-950);
            line-height: 1.1;
            margin-bottom: 4px;
            letter-spacing: -0.03em;
        }

        .stat-item__value small {
            font-size: 15px;
            font-weight: 600;
            color: var(--green-800);
            margin-left: 2px;
        }

        .stat-item__label {
            font-size: 13px;
            color: var(--gray-700);
            font-weight: 500;
        }

        /* ==========================================================================
           7. EDITORIAL SECTIONS (ABOUT & STORY)
           ========================================================================== */
        .editorial-section {
            padding: clamp(64px, 8vw, 112px) 0;
        }

        .editorial-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: clamp(40px, 6vw, 80px);
            align-items: center;
        }

        .editorial-media {
            position: relative;
        }

        .editorial-media__main {
            border-radius: var(--radius-md);
            overflow: hidden;
            aspect-ratio: 4 / 3.6;
            background-color: var(--gray-100);
        }

        .editorial-media__main img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .editorial-media__secondary {
            position: absolute;
            bottom: -20px;
            right: -20px;
            width: 48%;
            aspect-ratio: 1 / 1;
            border-radius: var(--radius-sm);
            overflow: hidden;
            border: 4px solid var(--white);
            box-shadow: var(--shadow-card);
        }

        .editorial-media__secondary img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .editorial-content {
            display: flex;
            flex-direction: column;
        }

        .editorial-content__lead {
            font-size: 16.5px;
            color: var(--gray-800);
            line-height: 1.8;
            margin-bottom: 28px;
        }

        .meta-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 32px;
        }

        .meta-list__item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 14.5px;
            color: var(--gray-700);
        }

        .meta-list__bullet {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: var(--green-800);
            margin-top: 9px;
            flex-shrink: 0;
        }

        .meta-list__item strong {
            color: var(--green-950);
        }

        /* Second Story Highlights */
        .story-highlights {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-top: 24px;
        }

        .story-highlight-card {
            background-color: var(--green-50);
            padding: 16px 20px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--gray-200);
        }

        .story-highlight-card__label {
            font-size: 12px;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .story-highlight-card__value {
            font-size: 16px;
            font-weight: 700;
            color: var(--green-950);
        }

        /* ==========================================================================
           8. PRODUCTS SECTION
           ========================================================================== */
        .products-section {
            background-color: var(--green-50);
            padding: clamp(72px, 8vw, 120px) 0;
        }

        .products-section__header {
            margin-bottom: 48px;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
        }

        .product-card {
            background-color: var(--white);
            border-radius: var(--radius-md);
            border: 1px solid var(--gray-200);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform var(--transition), box-shadow var(--transition);
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-card);
        }

        .product-card__image-wrap {
            position: relative;
            aspect-ratio: 4 / 3;
            background-color: var(--gray-100);
            overflow: hidden;
        }

        .product-card__image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform var(--transition-slow);
        }

        .product-card:hover .product-card__image {
            transform: scale(1.04);
        }

        .product-card__stock-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(18, 59, 43, 0.85);
            color: var(--white);
            padding: 4px 10px;
            border-radius: var(--radius-full);
            font-size: 11px;
            font-weight: 600;
        }

        .product-card__body {
            padding: 24px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .product-card__title {
            font-size: 18px;
            font-weight: 700;
            color: var(--green-950);
            margin-bottom: 8px;
        }

        .product-card__price {
            font-size: 19px;
            font-weight: 800;
            color: var(--green-900);
            margin-bottom: 12px;
        }

        .product-card__price span {
            font-size: 13px;
            font-weight: 500;
            color: var(--gray-500);
        }

        .product-card__desc {
            font-size: 13.5px;
            color: var(--gray-700);
            line-height: 1.6;
            margin-bottom: 20px;
            flex-grow: 1;
        }

        .product-card__link {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--green-950);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: auto;
        }

        .product-card__link:hover {
            color: var(--green-800);
            gap: 9px;
        }

        /* ==========================================================================
           9. HARVEST SECTION (EDITORIAL TIMELINE)
           ========================================================================== */
        .harvest-section {
            padding: clamp(72px, 8vw, 120px) 0;
            background-color: var(--white);
        }

        .harvest-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 40px;
        }

        .harvest-item {
            padding: 24px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--gray-200);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: var(--transition);
        }

        .harvest-item:hover {
            border-color: var(--green-800);
            background-color: var(--off-white);
        }

        .harvest-item__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .harvest-item__date {
            font-size: 12.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--green-800);
        }

        .harvest-item__grade {
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: var(--radius-full);
            background-color: var(--green-100);
            color: var(--green-950);
        }

        .harvest-item__variety {
            font-size: 17px;
            font-weight: 700;
            color: var(--green-950);
            margin-bottom: 4px;
        }

        .harvest-item__farm {
            font-size: 13px;
            color: var(--gray-500);
            margin-bottom: 16px;
        }

        .harvest-item__footer {
            padding-top: 12px;
            border-top: 1px solid var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
        }

        .harvest-item__volume {
            font-size: 15px;
            font-weight: 700;
            color: var(--green-950);
        }

        /* ==========================================================================
           10. GALLERY SECTION
           ========================================================================== */
        .gallery-section {
            background-color: var(--off-white);
            padding: clamp(72px, 8vw, 120px) 0;
        }

        .gallery-layout {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            grid-template-rows: 220px 220px;
            gap: 16px;
            margin-top: 40px;
        }

        .gallery-photo {
            position: relative;
            border-radius: var(--radius-sm);
            overflow: hidden;
            background-color: var(--gray-200);
        }

        .gallery-photo--large {
            grid-column: 1 / 2;
            grid-row: 1 / 3;
        }

        .gallery-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform var(--transition-slow);
        }

        .gallery-photo:hover img {
            transform: scale(1.04);
        }

        .gallery-photo__caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 16px;
            background: linear-gradient(to top, rgba(18, 59, 43, 0.85), transparent);
            color: var(--white);
            font-size: 12.5px;
            font-weight: 500;
            opacity: 0;
            transition: opacity var(--transition);
        }

        .gallery-photo:hover .gallery-photo__caption {
            opacity: 1;
        }

        /* ==========================================================================
           11. LOCATION SECTION
           ========================================================================== */
        .location-section {
            background-color: var(--white);
            padding: 56px 0;
            border-top: 1px solid var(--gray-200);
            border-bottom: 1px solid var(--gray-200);
        }

        .location-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            flex-wrap: wrap;
        }

        .location-box__info {
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .location-box__icon {
            width: 44px;
            height: 44px;
            border-radius: var(--radius-sm);
            background-color: var(--green-100);
            color: var(--green-950);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        /* ==========================================================================
           12. CONTACT CTA
           ========================================================================== */
        .contact-cta {
            background-color: var(--green-950);
            color: var(--white);
            padding: clamp(64px, 8vw, 96px) 0;
        }

        .contact-cta__grid {
            display: grid;
            grid-template-columns: 1.3fr 0.7fr;
            gap: 40px;
            align-items: center;
        }

        .contact-cta__title {
            font-size: clamp(26px, 3.5vw, 38px);
            color: var(--white);
            margin-bottom: 14px;
        }

        .contact-cta__desc {
            font-size: 15.5px;
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.7;
            max-width: 560px;
        }

        .contact-cta__actions {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 14px;
        }

        .contact-cta__actions .btn--white {
            width: 100%;
            max-width: 280px;
        }

        .contact-cta__sublinks {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            font-size: 13.5px;
            color: rgba(255, 255, 255, 0.7);
        }

        .contact-cta__sublinks a:hover {
            color: var(--white);
        }

        /* ==========================================================================
           13. FOOTER
           ========================================================================== */
        .site-footer {
            background-color: var(--green-950);
            color: rgba(255, 255, 255, 0.65);
            padding: 64px 0 32px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 13.5px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.4fr 0.8fr 1fr 0.8fr;
            gap: 40px;
            margin-bottom: 48px;
        }

        .footer-col__title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--white);
            margin-bottom: 18px;
        }

        .footer-nav {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .footer-nav a:hover {
            color: var(--white);
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
            color: rgba(255, 255, 255, 0.4);
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
            font-size: 12.5px;
        }

        /* ==========================================================================
           14. REVEAL ANIMATION
           ========================================================================== */
        .reveal-item {
            opacity: 0;
            transform: translateY(16px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }

        .reveal-item.is-revealed {
            opacity: 1;
            transform: translateY(0);
        }

        @media (prefers-reduced-motion: reduce) {
            .reveal-item {
                opacity: 1;
                transform: none;
                transition: none;
            }
        }

        /* ==========================================================================
           15. RESPONSIVE BREAKPOINTS
           ========================================================================== */
        @media (max-width: 1024px) {
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .gallery-layout {
                grid-template-columns: repeat(2, 1fr);
                grid-template-rows: auto;
            }

            .gallery-photo--large {
                grid-column: 1 / 3;
                aspect-ratio: 16 / 9;
            }
        }

        @media (max-width: 768px) {
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
                min-height: 540px;
                padding: 60px 0 80px;
            }

            .editorial-grid {
                grid-template-columns: 1fr;
                gap: 36px;
            }

            .editorial-media__secondary {
                display: none;
            }

            .products-grid {
                grid-template-columns: 1fr;
            }

            .harvest-grid {
                grid-template-columns: 1fr;
            }

            .contact-cta__grid {
                grid-template-columns: 1fr;
                gap: 28px;
            }

            .footer-grid {
                grid-template-columns: 1fr;
                gap: 32px;
            }

            .gallery-layout {
                grid-template-columns: 1fr;
            }

            .gallery-photo--large {
                grid-column: 1 / 2;
            }
        }

        @media (max-width: 480px) {
            .hero__title {
                font-size: 34px;
            }

            .stats-panel {
                grid-template-columns: 1fr 1fr;
            }

            .hero__actions {
                flex-direction: column;
                width: 100%;
            }

            .hero__actions .btn {
                width: 100%;
            }

            .contact-cta__actions .btn--white {
                max-width: 100%;
            }
        }
    </style>
</head>

<body>

    {{-- ==========================================================================
         PREVIEW BANNER
         ========================================================================== --}}
    @if ($isPreview)
        <div class="preview-banner" role="banner" aria-label="Status Mode Pratinjau">
            <div class="container preview-banner__inner">
                <span>
                    <i class="fas fa-eye"></i> Mode Preview &mdash; Anda sedang melihat tampilan publik website usaha Anda.
                </span>
                <a href="{{ route('farmer.website.index') }}" class="preview-banner__btn">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    @endif

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
                </ul>

                <div class="navbar__actions">
                    @if ($sections['show_contact'] && !empty($contact['whatsapp']))
                        <a href="{{ $contact['whatsapp'] }}" target="_blank" rel="noopener noreferrer" class="btn btn--dark">
                            Hubungi
                        </a>
                    @elseif ($sections['show_contact'])
                        <a href="#kontak" class="btn btn--dark">
                            Hubungi
                        </a>
                    @endif
                </div>

                <button type="button" class="nav-toggle" id="navToggle" aria-expanded="false" aria-controls="mobileDrawer" aria-label="Buka Menu">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>
        </div>
    </header>

    {{-- Mobile Navigation Drawer --}}
    <div class="mobile-menu" id="mobileDrawer" role="dialog" aria-modal="true" aria-label="Menu Mobile">
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
                    <a href="{{ $contact['whatsapp'] }}" target="_blank" rel="noopener noreferrer" class="btn btn--dark" style="width: 100%;">
                        <i class="fab fa-whatsapp"></i> Chat WhatsApp
                    </a>
                @endif
            </div>
        </div>
    </div>

    <header>
        {{-- ==========================================================================
             HERO FULL IMAGE
             ========================================================================== --}}
        @php
            $heroCover = !empty($profile['cover_image_url'])
                ? $profile['cover_image_url']
                : 'https://images.unsplash.com/photo-1586771107445-d3ca888129ff?auto=format&fit=crop&w=1600&q=80';
        @endphp
        <div class="hero" id="beranda" style="background-image: url('{{ $heroCover }}');">
            <div class="container hero__container">
                <div class="hero__content reveal-item">
                    @if (!empty($profile['is_verified']))
                        <div class="hero__badge">
                            <i class="fas fa-certificate"></i> TERVERIFIKASI P.A.D.I.
                        </div>
                    @else
                        <div class="hero__badge">
                            <i class="fas fa-seedling"></i> USAHA TANI TERPERCAYA
                        </div>
                    @endif

                    <h1 class="hero__title">
                        {{ $profile['headline'] ?? $profile['business_name'] }}
                    </h1>

                    @if (!empty($profile['description']))
                        <p class="hero__description">
                            {{ $profile['description'] }}
                        </p>
                    @endif

                    <div class="hero__actions">
                        @if ($sections['show_products'] && count($products) > 0)
                            <a href="#produk" class="btn btn--white">
                                Lihat Produk
                            </a>
                        @endif

                        @if ($sections['show_contact'] && !empty($contact['whatsapp']))
                            <a href="{{ $contact['whatsapp'] }}" target="_blank" rel="noopener noreferrer" class="btn btn--outline-white">
                                <i class="fab fa-whatsapp"></i> Hubungi Kami
                            </a>
                        @elseif ($sections['show_contact'])
                            <a href="#kontak" class="btn btn--outline-white">
                                Hubungi Kami
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main>
        {{-- ==========================================================================
             STATISTICS STRIP (OVERLAP)
             ========================================================================== --}}
        @php
            $hasStats = ($sections['show_productivity'] && $statistics);
            $hasArea = $hasStats && !empty($statistics['total_area_ha']);
            $hasSeasons = $hasStats && !empty($statistics['total_seasons']);
            $hasProductivity = $hasStats && !empty($statistics['latest_productivity']);
            $productCount = count($products ?? []);
        @endphp

        @if ($hasArea || $hasSeasons || $hasProductivity || $productCount > 0)
            <div class="stats-overlap">
                <div class="container">
                    <div class="stats-panel reveal-item">
                        @if ($hasArea)
                            <div class="stat-item stat-item--highlight">
                                <div class="stat-item__value">
                                    {{ $statistics['total_area_ha'] }}<small>Ha</small>
                                </div>
                                <div class="stat-item__label">Total Luas Lahan</div>
                            </div>
                        @endif

                        @if ($hasSeasons)
                            <div class="stat-item">
                                <div class="stat-item__value">
                                    {{ $statistics['total_seasons'] }}
                                </div>
                                <div class="stat-item__label">Musim Tanam</div>
                            </div>
                        @endif

                        @if ($hasProductivity)
                            <div class="stat-item stat-item--highlight">
                                <div class="stat-item__value">
                                    {{ $statistics['latest_productivity'] }}<small>t/ha</small>
                                </div>
                                <div class="stat-item__label">Produktivitas Panen</div>
                            </div>
                        @endif

                        @if ($productCount > 0)
                            <div class="stat-item">
                                <div class="stat-item__value">
                                    {{ $productCount }}
                                </div>
                                <div class="stat-item__label">Produk Komoditas</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- ==========================================================================
             ABOUT SECTION
             ========================================================================== --}}
        <section class="editorial-section" id="tentang">
            <div class="container">
                <div class="editorial-grid">
                    <div class="editorial-media reveal-item">
                        @php
                            $galleryColl = collect($gallery ?? []);
                            $firstGal = $galleryColl->get(0);
                            $secondGal = $galleryColl->get(1);
                            $thirdGal = $galleryColl->get(2);

                            $aboutMainImg = $heroCover;
                            if ($firstGal) {
                                $aboutMainImg = is_array($firstGal) 
                                    ? ($firstGal['image_url'] ?? $heroCover) 
                                    : (isset($firstGal->image_url) ? $firstGal->image_url : asset('storage/' . ($firstGal->image_path ?? '')));
                            }
                            $aboutSubImg = null;
                            if ($secondGal) {
                                $aboutSubImg = is_array($secondGal) 
                                    ? ($secondGal['image_url'] ?? null) 
                                    : (isset($secondGal->image_url) ? $secondGal->image_url : asset('storage/' . ($secondGal->image_path ?? '')));
                            }
                        @endphp

                        <div class="editorial-media__main">
                            <img src="{{ $aboutMainImg }}" alt="{{ $profile['business_name'] }}" loading="lazy">
                        </div>

                        @if ($aboutSubImg)
                            <div class="editorial-media__secondary">
                                <img src="{{ $aboutSubImg }}" alt="Lahan Usaha Tani" loading="lazy">
                            </div>
                        @endif
                    </div>

                    <div class="editorial-content reveal-item">
                        <span class="section-eyebrow">Tentang Kami</span>
                        <h2 class="section-title">
                            {{ $profile['headline'] ?? 'Membangun hasil pertanian berkualitas dari lahan hingga konsumen.' }}
                        </h2>

                        <p class="editorial-content__lead">
                            {{ $profile['description'] ?? 'Kami berdedikasi menghasilkan komoditas beras dan hasil panen berkualitas tinggi melalui pengelolaan pertanian berkelanjutan dan transparan.' }}
                        </p>

                        <ul class="meta-list">
                            @if ($sections['show_location'] && !empty($location['address']))
                                <li class="meta-list__item">
                                    <span class="meta-list__bullet"></span>
                                    <div><strong>Lokasi Lahan:</strong> {{ $location['address'] }}</div>
                                </li>
                            @endif

                            @if ($hasArea)
                                <li class="meta-list__item">
                                    <span class="meta-list__bullet"></span>
                                    <div><strong>Luas Pengelolaan:</strong> {{ $statistics['total_area_ha'] }} Hektar lahan aktif</div>
                                </li>
                            @endif

                            @if ($hasSeasons)
                                <li class="meta-list__item">
                                    <span class="meta-list__bullet"></span>
                                    <div><strong>Pengalaman Siklus:</strong> {{ $statistics['total_seasons'] }} musim tanam terdokumentasi</div>
                                </li>
                            @endif
                        </ul>

                        <div>
                            @if ($sections['show_contact'] && !empty($contact['whatsapp']))
                                <a href="{{ $contact['whatsapp'] }}" target="_blank" rel="noopener noreferrer" class="btn btn--dark">
                                    Hubungi Kami
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ==========================================================================
             SECOND STORY SECTION (TRANSPARANSI PANEN)
             ========================================================================== --}}
        @if ($sections['show_harvests'] && count($harvests) > 0)
            <section class="editorial-section" style="background-color: var(--off-white); border-top: 1px solid var(--gray-200);">
                <div class="container">
                    <div class="editorial-grid">
                        <div class="editorial-content reveal-item">
                            <span class="section-eyebrow">Transparansi Mutu</span>
                            <h2 class="section-title">
                                Kualitas hasil panen yang tercatat dan terstandarisasi.
                            </h2>

                            <p class="editorial-content__lead">
                                Setiap siklus panen kami didata secara teratur untuk memastikan kesesuaian mutu, stabilitas pasokan, dan kepercayaan bagi setiap mitra usaha.
                            </p>

                            @php
                                $harvestsColl = collect($harvests);
                                $latestHarvest = $harvestsColl->first();
                            @endphp

                            @if ($latestHarvest)
                                <div class="story-highlights">
                                    <div class="story-highlight-card">
                                        <div class="story-highlight-card__label">Panen Terakhir</div>
                                        <div class="story-highlight-card__value">
                                            {{ \Carbon\Carbon::parse(is_array($latestHarvest) ? $latestHarvest['harvest_date'] : $latestHarvest->harvest_date)->translatedFormat('F Y') }}
                                        </div>
                                    </div>
                                    <div class="story-highlight-card">
                                        <div class="story-highlight-card__label">Varietas Utama</div>
                                        <div class="story-highlight-card__value">
                                            {{ (is_array($latestHarvest) ? $latestHarvest['variety_name'] : $latestHarvest->variety_name) ?? 'Varietas Unggul' }}
                                        </div>
                                    </div>
                                    <div class="story-highlight-card">
                                        <div class="story-highlight-card__label">Standar Mutu</div>
                                        <div class="story-highlight-card__value">
                                            Grade {{ (is_array($latestHarvest) ? $latestHarvest['quality_grade'] : $latestHarvest->quality_grade) ?? 'A' }}
                                        </div>
                                    </div>
                                    <div class="story-highlight-card">
                                        <div class="story-highlight-card__label">Total Riwayat</div>
                                        <div class="story-highlight-card__value">
                                            {{ count($harvests) }} Siklus Panen
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="editorial-media reveal-item">
                            @php
                                $storyImg = $heroCover;
                                if ($thirdGal) {
                                    $storyImg = is_array($thirdGal) 
                                        ? ($thirdGal['image_url'] ?? $heroCover) 
                                        : (isset($thirdGal->image_url) ? $thirdGal->image_url : asset('storage/' . ($thirdGal->image_path ?? '')));
                                }
                            @endphp
                            <div class="editorial-media__main">
                                <img src="{{ $storyImg }}" alt="Hasil Panen Terverifikasi" loading="lazy">
                            </div>
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
                    <div class="products-section__header reveal-item">
                        <span class="section-eyebrow">Katalog Komoditas</span>
                        <h2 class="section-title">Hasil Panen & Produk</h2>
                        <p class="section-subtitle">
                            Komoditas pilihan yang tersedia langsung dari lahan usaha tani kami.
                        </p>
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
                            @endphp
                            <div class="product-card reveal-item">
                                <div class="product-card__image-wrap">
                                    @if (!empty($prodImg))
                                        <img src="{{ $prodImg }}" alt="{{ $prodCommodity }}" class="product-card__image" loading="lazy">
                                    @else
                                        <img src="https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=600&q=80"
                                             alt="{{ $prodCommodity }}" class="product-card__image" loading="lazy">
                                    @endif

                                    @if (!empty($prodQty) && !empty($prodUnit))
                                        <span class="product-card__stock-badge">
                                            Stok: {{ $prodQty }} {{ $prodUnit }}
                                        </span>
                                    @endif
                                </div>

                                <div class="product-card__body">
                                    <h3 class="product-card__title">{{ $prodCommodity }}</h3>

                                    @if (!empty($prodPrice))
                                        <div class="product-card__price">
                                            Rp {{ number_format($prodPrice, 0, ',', '.') }}
                                            @if (!empty($prodUnit))
                                                <span>/ {{ $prodUnit }}</span>
                                            @endif
                                        </div>
                                    @endif

                                    @if (!empty($prodDesc))
                                        <p class="product-card__desc">{{ $prodDesc }}</p>
                                    @endif

                                    @if (!empty($prodLink))
                                        <a href="{{ $prodLink }}" target="_blank" rel="noopener noreferrer" class="product-card__link">
                                            Beli / Pesan <i class="fas fa-arrow-right"></i>
                                        </a>
                                    @elseif ($sections['show_contact'] && !empty($contact['whatsapp']))
                                        @php
                                            $waMsg = urlencode("Halo {$profile['business_name']}, saya tertarik dengan produk {$prodCommodity}. Mohon info detail dan ketersediaan stok.");
                                            $waUrl = $contact['whatsapp'] . (str_contains($contact['whatsapp'], '?') ? '&' : '?') . "text={$waMsg}";
                                        @endphp
                                        <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="product-card__link">
                                            Pesan via WhatsApp <i class="fas fa-arrow-right"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- ==========================================================================
             HARVEST SECTION
             ========================================================================== --}}
        @if ($sections['show_harvests'] && count($harvests) > 0)
            <section class="harvest-section" id="panen">
                <div class="container">
                    <div class="reveal-item">
                        <span class="section-eyebrow">Dokumentasi Panen</span>
                        <h2 class="section-title">Riwayat Panen</h2>
                        <p class="section-subtitle">
                            Rekam jejak panen berkala dari siklus produksi lahan kami.
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
                            <div class="harvest-item reveal-item">
                                <div>
                                    <div class="harvest-item__header">
                                        <span class="harvest-item__date">
                                            {{ \Carbon\Carbon::parse($hDate)->translatedFormat('M Y') }}
                                        </span>
                                        <span class="harvest-item__grade">
                                            Grade {{ $hGrade }}
                                        </span>
                                    </div>

                                    <h3 class="harvest-item__variety">
                                        {{ $hVariety }}
                                    </h3>

                                    <div class="harvest-item__farm">
                                        {{ $hFarm }}
                                    </div>
                                </div>

                                <div class="harvest-item__footer">
                                    <span>Volume Panen</span>
                                    <span class="harvest-item__volume">
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
             GALLERY SECTION
             ========================================================================== --}}
        @if ($sections['show_gallery'] && count($gallery) > 0)
            <section class="gallery-section" id="galeri">
                <div class="container">
                    <div class="reveal-item">
                        <span class="section-eyebrow">Dokumentasi</span>
                        <h2 class="section-title">Dokumentasi Lapangan</h2>
                        <p class="section-subtitle">
                            Sekilas kegiatan budidaya, perawatan tanaman, hingga proses pascapanen.
                        </p>
                    </div>

                    <div class="gallery-layout">
                        @foreach ($gallery as $item)
                            @php
                                $imgSrc = is_array($item) 
                                    ? ($item['image_url'] ?? '') 
                                    : (isset($item->image_url) ? $item->image_url : asset('storage/' . ($item->image_path ?? '')));
                                $cap = is_array($item) ? ($item['caption'] ?? '') : ($item->caption ?? '');
                                $isFirst = $loop->first;
                            @endphp
                            <div class="gallery-photo {{ $isFirst ? 'gallery-photo--large' : '' }} reveal-item">
                                <img src="{{ $imgSrc }}" alt="{{ $cap ?: 'Dokumentasi ' . $profile['business_name'] }}" loading="lazy">
                                @if (!empty($cap))
                                    <div class="gallery-photo__caption">{{ $cap }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- ==========================================================================
             LOCATION SECTION
             ========================================================================== --}}
        @if ($sections['show_location'] && !empty($location['address']))
            <section class="location-section">
                <div class="container">
                    <div class="location-box reveal-item">
                        <div class="location-box__info">
                            <div class="location-box__icon">
                                <i class="fas fa-location-dot"></i>
                            </div>
                            <div>
                                <h3 style="font-size: 16px; margin-bottom: 4px;">Lokasi Usaha Tani</h3>
                                <p style="color: var(--gray-700); font-size: 14.5px;">{{ $location['address'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ==========================================================================
             CONTACT CTA
             ========================================================================== --}}
        @if ($sections['show_contact'] && $contact)
            <section class="contact-cta" id="kontak">
                <div class="container">
                    <div class="contact-cta__grid reveal-item">
                        <div>
                            <h2 class="contact-cta__title">Tertarik dengan hasil panen kami?</h2>
                            <p class="contact-cta__desc">
                                Hubungi langsung untuk informasi ketersediaan stok, harga komoditas, pembelian, atau kerja sama pasokan berkelanjutan.
                            </p>
                        </div>

                        <div class="contact-cta__actions">
                            @if (!empty($contact['whatsapp']))
                                <a href="{{ $contact['whatsapp'] }}" target="_blank" rel="noopener noreferrer" class="btn btn--white">
                                    <i class="fab fa-whatsapp"></i> Hubungi Sekarang
                                </a>
                            @endif

                            <div class="contact-cta__sublinks">
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
                        {{ $profile['headline'] ?? 'Usaha pertanian terpercaya dengan komitmen mutu dan pasokan hasil panen berkualitas.' }}
                    </p>
                    <span style="font-size: 12px; color: rgba(255, 255, 255, 0.45);">
                        Terverifikasi Sistem P.A.D.I.
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
                        <p style="font-size: 13px; color: rgba(255, 255, 255, 0.45);">
                            Dokumentasi lapangan usaha tani.
                        </p>
                    @endif
                </div>
            </div>

            <div class="footer-bottom">
                <div>
                    &copy; {{ date('Y') }} {{ $profile['business_name'] }}. Hak cipta dilindungi.
                </div>
                <div>
                    Powered by P.A.D.I.
                </div>
            </div>
        </div>
    </footer>

    {{-- ==========================================================================
         SCRIPTS (VANILLA JAVASCRIPT)
         ========================================================================== --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Navbar Scroll Effect
            const navbar = document.getElementById('navbar');
            function updateNavbar() {
                if (!navbar) return;
                if (window.scrollY > 30) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            }
            window.addEventListener('scroll', updateNavbar, { passive: true });
            updateNavbar();

            // 2. Mobile Menu Toggle
            const navToggle = document.getElementById('navToggle');
            const navClose = document.getElementById('navClose');
            const mobileDrawer = document.getElementById('mobileDrawer');
            const mobileItems = document.querySelectorAll('.mobile-item');

            function openMenu() {
                if (mobileDrawer) {
                    mobileDrawer.classList.add('is-open');
                    if (navToggle) navToggle.setAttribute('aria-expanded', 'true');
                    document.body.style.overflow = 'hidden';
                }
            }

            function closeMenu() {
                if (mobileDrawer) {
                    mobileDrawer.classList.remove('is-open');
                    if (navToggle) navToggle.setAttribute('aria-expanded', 'false');
                    document.body.style.overflow = '';
                }
            }

            if (navToggle) navToggle.addEventListener('click', openMenu);
            if (navClose) navClose.addEventListener('click', closeMenu);
            if (mobileDrawer) {
                mobileDrawer.addEventListener('click', function (e) {
                    if (e.target === mobileDrawer) closeMenu();
                });
            }
            mobileItems.forEach(item => item.addEventListener('click', closeMenu));

            // 3. Smooth Scroll for Anchor Links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    const targetId = this.getAttribute('href');
                    if (targetId === '#' || targetId === '') return;
                    const targetEl = document.querySelector(targetId);
                    if (targetEl) {
                        e.preventDefault();
                        const headerOffset = 80;
                        const elementPosition = targetEl.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // 4. Subtle Reveal on Scroll (IntersectionObserver)
            if ('IntersectionObserver' in window) {
                const revealObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-revealed');
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.08,
                    rootMargin: '0px 0px -30px 0px'
                });

                document.querySelectorAll('.reveal-item').forEach(el => revealObserver.observe(el));
            } else {
                document.querySelectorAll('.reveal-item').forEach(el => el.classList.add('is-revealed'));
            }
        });
    </script>
</body>

</html>