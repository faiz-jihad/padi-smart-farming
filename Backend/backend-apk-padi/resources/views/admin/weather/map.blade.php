@extends('layouts.admin')

@section('title', 'Geo Intelligence & Radar Cuaca Pertanian')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    <link rel="stylesheet" href="{{ asset('css/admin/weather-map.css') }}?v={{ time() }}" />
@endpush

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    <link rel="stylesheet" href="{{ asset('css/admin/weather-map.css') }}?v={{ time() }}" />

    <div class="admin-page">

        {{-- Header Bar --}}
        <div class="admin-page__header">
            <div>
                <h1 class="admin-page__title">Radar Cuaca &amp; Geo Intelligence Wilayah</h1>
                <p class="admin-page__subtitle">Pemetaan Agroklimat Interaktif &bull; Klik sembarang titik atau wilayah untuk inspeksi cuaca &amp; tanah real-time</p>
            </div>
            <div class="admin-page__actions">
                <a href="{{ route('admin.weather.index') }}" class="admin-btn admin-btn--secondary">Dashboard Cuaca</a>
                <a href="{{ route('admin.weather.history') }}" class="admin-btn admin-btn--secondary">Riwayat Snapshot</a>
                <a href="{{ route('admin.soil.index') }}" class="admin-btn admin-btn--primary">+ Deteksi Tanah</a>
            </div>
        </div>

        @if (session('status'))
            <div class="admin-alert admin-alert--success">{{ session('status') }}</div>
        @endif

        {{-- ═══════════════════════════════════════════════ --}}
        {{-- Region Selector & Toolbar                      --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <section class="admin-card" style="padding: 0; overflow: hidden; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
            <div class="geo-region-bar">
                <div class="geo-region-bar__left">
                    <button id="btn-indonesia" type="button" class="geo-btn-chip geo-btn-chip--active" title="Tampilkan peta seluruh provinsi Indonesia">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                        Peta Indonesia
                    </button>

                    <div class="geo-region-group">
                        <span class="geo-region-bar__label">Provinsi</span>
                        <select id="province-select" class="geo-region-select">
                            <option value="">-- Pilih Provinsi --</option>
                        </select>
                    </div>

                    <div class="geo-region-group">
                        <span class="geo-region-bar__label">Kab/Kota</span>
                        <select id="regency-select" class="geo-region-select">
                            <option value="">-- Pilih Kabupaten/Kota --</option>
                        </select>
                    </div>
                </div>

                <div class="geo-region-bar__right">
                    <div class="geo-info-chip">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="3"/><path d="M12 2v3m0 14v3m10-10h-3M5 12H2"/></svg>
                        Klik titik peta atau polygon untuk inspeksi
                    </div>
                </div>
            </div>

            {{-- Map + Side Panel container --}}
            <div class="geo-map-wrapper">
                {{-- Loading overlay --}}
                <div id="geo-loading" class="geo-loading-overlay">
                    <div class="geo-loading-spinner"></div>
                    <span id="geo-loading-text" class="geo-loading-text">Memuat data cuaca &amp; batas geospasial...</span>
                </div>

                {{-- Floating Top Bar: Breadcrumb + Basemap Switcher --}}
                <div class="geo-top-controls">
                    {{-- Breadcrumb Navigation --}}
                    <div id="geo-breadcrumb" class="geo-breadcrumb">
                        <span class="geo-breadcrumb__item is-link" data-action="indonesia">Indonesia</span>
                    </div>

                    {{-- Basemap & Layer Switcher --}}
                    <div class="geo-map-toggles">
                        <button type="button" class="geo-toggle-btn is-active" data-basemap="osm" title="Peta Jalan Standar">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/></svg>
                            Jalan
                        </button>
                        <button type="button" class="geo-toggle-btn" data-basemap="satellite" title="Citra Satelit Esri">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/></svg>
                            Satelit
                        </button>
                        <button type="button" class="geo-toggle-btn" data-basemap="topo" title="Topografi Wilayah">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m8 3 4 8 5-5 5 15H2L8 3z"/></svg>
                            Kontur
                        </button>
                    </div>
                </div>

                {{-- Level Indicator --}}
                <div id="geo-level-pill" class="geo-level-pill">
                    <div class="geo-level-pill__dot"></div>
                    <span id="geo-level-text">Tingkat: Kecamatan (Kandanghaur)</span>
                </div>

                {{-- Leaflet Map --}}
                <div id="geoMap" class="geo-map"></div>

                {{-- Side Panel: Dynamic Content per Administrative Level or Clicked Point --}}
                <div id="geo-side-panel" class="geo-side-panel">
                    <div class="geo-side-panel__header">
                        <div style="flex: 1; min-width: 0;">
                            <div id="sp-badge" class="geo-side-panel__level-badge">Wilayah</div>
                            <h2 id="sp-title" class="geo-side-panel__title">—</h2>
                            <p id="sp-subtitle" class="geo-side-panel__subtitle">—</p>
                        </div>
                        <button id="sp-close" type="button" class="geo-side-panel__close" title="Tutup panel">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>

                    {{-- Navigation Tabs inside Panel --}}
                    <div class="geo-panel-tabs">
                        <button type="button" class="geo-panel-tab is-active" data-tab="weather">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/></svg>
                            Cuaca &amp; Iklim
                        </button>
                        <button type="button" class="geo-panel-tab" data-tab="soil">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 20h10"/><path d="M10 20c5.5-2.5.8-6.4 3-10"/><path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"/></svg>
                            Tanah &amp; Air
                        </button>
                        <button type="button" class="geo-panel-tab" data-tab="agri" id="tab-btn-agri">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            Wilayah &amp; Tani
                        </button>
                    </div>

                    <div id="sp-body" class="geo-side-panel__body">
                        {{-- Dynamically populated by JS --}}
                    </div>

                    <div class="geo-side-panel__footer">
                        <div id="sp-action-soil-wrapper">
                            <a id="sp-soil-btn" href="{{ route('admin.soil.create') }}" class="geo-panel-btn geo-panel-btn--secondary">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14m-7-7h14"/></svg>
                                Uji Sampel Tanah di Titik Ini
                            </a>
                        </div>
                        <button id="sp-drill-btn" type="button" class="geo-panel-btn geo-panel-btn--primary" style="display: none;"></button>
                        <button id="sp-back-btn" type="button" class="geo-panel-btn geo-panel-btn--back" style="display: none;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                            Kembali ke Tingkat Atas
                        </button>
                    </div>
                </div>
            </div>

            {{-- Legend Strip --}}
            <div class="geo-legend-strip">
                <div class="geo-legend-item">
                    <div class="geo-legend-swatch" style="background: rgba(15,118,110,0.25); border: 1.5px solid #0f766e;"></div>
                    <span>Batas Provinsi</span>
                </div>
                <div class="geo-legend-item">
                    <div class="geo-legend-swatch" style="background: rgba(22,101,52,0.25); border: 1.5px solid #16a34a;"></div>
                    <span>Batas Kab/Kota</span>
                </div>
                <div class="geo-legend-item">
                    <div class="geo-legend-swatch" style="background: rgba(37,99,235,0.25); border: 1.5px solid #2563eb;"></div>
                    <span>Batas Kecamatan</span>
                </div>
                <div class="geo-legend-item">
                    <div class="geo-legend-swatch" style="background: rgba(234,88,12,0.25); border: 1.5px solid #ea580c;"></div>
                    <span>Batas Desa</span>
                </div>
                <div class="geo-legend-item">
                    <div class="geo-legend-swatch" style="background: #166534; border-radius: 50%; width: 9px; height: 9px;"></div>
                    <span>Titik Lahan Pertanian</span>
                </div>
                <div class="geo-legend-item">
                    <div class="geo-legend-swatch" style="background: #ef4444; border-radius: 50%; width: 9px; height: 9px;"></div>
                    <span>Titik Inspeksi Cuaca</span>
                </div>
            </div>
        </section>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        // ──────────────────────────────────────────────────────
        // SVG ICONS DICTIONARY (ZERO EMOJIS, CLEAN VECTORS)
        // ──────────────────────────────────────────────────────
        const SVG_ICONS = {
            sun: `<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>`,
            cloudSun: `<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="M20 12h2"/><path d="m19.07 4.93-1.41 1.41"/><path d="M15.947 12.65a4 4 0 0 0-5.925-4.128"/><path d="M13 22H7a5 5 0 1 1 4.9-6H13a3 3 0 0 1 0 6Z"/></svg>`,
            cloud: `<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/></svg>`,
            cloudRain: `<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="M16 14v6"/><path d="M8 14v6"/><path d="M12 16v6"/></svg>`,
            cloudLightning: `<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 16.326A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 .5 8.973"/><path d="m13 12-3 5h4l-3 5"/></svg>`,
            fog: `<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14h16"/><path d="M4 18h16"/><path d="M7 10h10"/><path d="M9 6h6"/></svg>`,
            droplet: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/></svg>`,
            wind: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.7 7.7a2.5 2.5 0 1 1 1.8 4.3H2"/><path d="M9.6 4.6A2 2 0 1 1 11 8H2"/><path d="M12.6 19.4A2 2 0 1 0 14 16H2"/></svg>`,
            pressure: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg>`,
            thermometer: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/></svg>`,
            sprout: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 20h10"/><path d="M10 20c5.5-2.5.8-6.4 3-10"/><path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"/></svg>`,
            farmer: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>`,
            farmArea: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>`,
            village: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/></svg>`,
            lightbulb: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/></svg>`,
            satellite: `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 7 9 3 5 7l4 4"/><path d="m17 11 4 4-4 4-4-4"/><path d="m8 12 4 4"/><path d="m16 8 4-4"/><path d="M9 21a6 6 0 0 0-6-6"/></svg>`,
        };

        function getWeatherSvg(condition, size = 26) {
            if (!condition) return SVG_ICONS.sun;
            const c = condition.toLowerCase();
            if (c.includes('petir') || c.includes('badai') || c.includes('thunder')) return SVG_ICONS.cloudLightning;
            if (c.includes('hujan') || c.includes('rain') || c.includes('gerimis')) return SVG_ICONS.cloudRain;
            if (c.includes('mendung') || c.includes('overcast') || c.includes('tebal')) return SVG_ICONS.cloud;
            if (c.includes('berawan') || c.includes('cloud')) return SVG_ICONS.cloudSun;
            if (c.includes('kabut') || c.includes('fog') || c.includes('mist')) return SVG_ICONS.fog;
            return SVG_ICONS.sun;
        }

        // ──────────────────────────────────────────────────────
        // STATE
        // ──────────────────────────────────────────────────────
        const state = {
            level: 'district',
            provinceId: null,
            provinceName: 'Jawa Barat',
            regencyId: null,
            regencyName: 'Indramayu',
            districtId: null,
            districtName: null,
            villageId: null,
            villageName: null,
            activeTab: 'weather',
            currentData: null,
        };

        // ──────────────────────────────────────────────────────
        // MAP & BASEMAP INIT
        // ──────────────────────────────────────────────────────
        const map = L.map('geoMap', {
            zoomControl: true,
            attributionControl: true
        }).setView([-6.32, 108.20], 10);

        L.control.scale({ position: 'bottomleft', metric: true, imperial: false }).addTo(map);

        const basemaps = {
            osm: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 19
            }),
            satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: '&copy; Esri World Imagery',
                maxZoom: 19
            }),
            topo: L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenTopoMap',
                maxZoom: 17
            }),
        };

        basemaps.osm.addTo(map);
        let currentBasemap = 'osm';

        const layers = {
            province: L.layerGroup().addTo(map),
            regency:  L.layerGroup().addTo(map),
            district: L.layerGroup().addTo(map),
            village:  L.layerGroup().addTo(map),
            farm:     L.layerGroup().addTo(map),
            inspect:  L.layerGroup().addTo(map),
        };

        let activeLayer = null;

        setTimeout(() => { map.invalidateSize(); }, 150);
        setTimeout(() => { map.invalidateSize(); }, 500);
        window.addEventListener('resize', () => { map.invalidateSize(); });

        // ──────────────────────────────────────────────────────
        // DOM REFS
        // ──────────────────────────────────────────────────────
        const loadingEl      = document.getElementById('geo-loading');
        const loadingText    = document.getElementById('geo-loading-text');
        const sidePanel      = document.getElementById('geo-side-panel');
        const spBadge        = document.getElementById('sp-badge');
        const spTitle        = document.getElementById('sp-title');
        const spSubtitle     = document.getElementById('sp-subtitle');
        const spBody         = document.getElementById('sp-body');
        const spDrillBtn     = document.getElementById('sp-drill-btn');
        const spBackBtn      = document.getElementById('sp-back-btn');
        const spSoilBtn      = document.getElementById('sp-soil-btn');
        const levelPillText  = document.getElementById('geo-level-text');
        const btnIndonesia   = document.getElementById('btn-indonesia');
        const provinceSelect = document.getElementById('province-select');
        const regencySelect  = document.getElementById('regency-select');
        const tabBtnAgri     = document.getElementById('tab-btn-agri');

        document.getElementById('sp-close').addEventListener('click', closePanel);
        spBackBtn.addEventListener('click', goBack);
        btnIndonesia.addEventListener('click', loadIndonesia);

        // ──────────────────────────────────────────────────────
        // BASEMAP SWITCHER
        // ──────────────────────────────────────────────────────
        document.querySelectorAll('[data-basemap]').forEach(btn => {
            btn.addEventListener('click', function () {
                const target = this.dataset.basemap;
                if (target === currentBasemap || !basemaps[target]) return;

                map.removeLayer(basemaps[currentBasemap]);
                basemaps[target].addTo(map);
                currentBasemap = target;

                document.querySelectorAll('[data-basemap]').forEach(b => b.classList.remove('is-active'));
                this.classList.add('is-active');
            });
        });

        // ──────────────────────────────────────────────────────
        // TAB SWITCHER
        // ──────────────────────────────────────────────────────
        document.querySelectorAll('.geo-panel-tab').forEach(tab => {
            tab.addEventListener('click', function () {
                document.querySelectorAll('.geo-panel-tab').forEach(t => t.classList.remove('is-active'));
                this.classList.add('is-active');
                state.activeTab = this.dataset.tab;
                renderActiveTabContent();
            });
        });

        // ──────────────────────────────────────────────────────
        // LOADING HELPERS
        // ──────────────────────────────────────────────────────
        function showLoading(msg = 'Memuat data cuaca & geospasial...') {
            loadingText.textContent = msg;
            loadingEl.classList.add('is-visible');
        }
        function hideLoading() {
            loadingEl.classList.remove('is-visible');
        }

        function clearAllLayers() {
            layers.province.clearLayers();
            layers.regency.clearLayers();
            layers.district.clearLayers();
            layers.village.clearLayers();
            layers.farm.clearLayers();
            layers.inspect.clearLayers();
            activeLayer = null;
        }

        // ──────────────────────────────────────────────────────
        // DIRECT MAP CLICK: INSPECT WEATHER AT ANY COORDINATE
        // ──────────────────────────────────────────────────────
        map.on('click', function (e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            inspectCoordinateWeather(lat, lng);
        });

        function inspectCoordinateWeather(lat, lng, nameOverride = null) {
            layers.inspect.clearLayers();

            const inspectIcon = L.divIcon({
                className: 'geo-pin-marker',
                html: `<div class="geo-inspect-pin" title="Titik Cuaca: ${lat.toFixed(4)}, ${lng.toFixed(4)}"></div>`,
                iconSize: [22, 22],
                iconAnchor: [11, 11]
            });

            L.marker([lat, lng], { icon: inspectIcon }).addTo(layers.inspect);

            showLoading(`Menganalisis cuaca koordinat (${lat.toFixed(4)}, ${lng.toFixed(4)})...`);

            fetch(`/admin/weather/inspect?latitude=${lat}&longitude=${lng}`)
                .then(r => r.json())
                .then(res => {
                    hideLoading();
                    if (!res.success) return;

                    state.level = 'point';
                    state.currentData = {
                        type: 'point',
                        title: nameOverride || `Titik Lokasi Cuaca`,
                        subtitle: `GPS: ${lat.toFixed(5)}, ${lng.toFixed(5)} &bull; ${res.provider || 'BMKG/Satelit'}`,
                        latitude: lat,
                        longitude: lng,
                        weather: res.weather,
                        soil: res.soil,
                        forecast: res.forecast || [],
                        warning: res.warning || null,
                        source: res.source || 'BMKG RI & AgroMonitoring',
                    };

                    tabBtnAgri.style.display = 'none';
                    if (state.activeTab === 'agri') state.activeTab = 'weather';
                    updateTabButtons();

                    spBadge.textContent    = 'Titik Koordinat';
                    spTitle.textContent    = state.currentData.title;
                    spSubtitle.innerHTML   = state.currentData.subtitle;

                    spSoilBtn.href = `/admin/soil/create?latitude=${lat}&longitude=${lng}`;
                    spDrillBtn.style.display = 'none';
                    spBackBtn.style.display  = 'none';

                    renderActiveTabContent();
                    openPanel();
                    updateLevelPill(`Inspeksi Titik: ${lat.toFixed(4)}, ${lng.toFixed(4)}`);
                })
                .catch(() => hideLoading());
        }

        // ──────────────────────────────────────────────────────
        // PROVINCE & REGENCY SELECTORS INITIALIZATION
        // ──────────────────────────────────────────────────────
        fetch('/admin/map/provinces')
            .then(r => r.json())
            .then(res => {
                provinceSelect.innerHTML = '<option value="">-- Pilih Provinsi --</option>';
                (res.data || []).forEach(prov => {
                    const opt = document.createElement('option');
                    opt.value = prov.id;
                    opt.textContent = prov.name;
                    opt.dataset.name = prov.name;
                    provinceSelect.appendChild(opt);
                });

                const jabar = [...provinceSelect.options].find(o => o.textContent.toLowerCase().includes('jawa barat'));
                if (jabar) {
                    provinceSelect.value = jabar.value;
                    state.provinceId   = parseInt(jabar.value);
                    state.provinceName = jabar.dataset.name || jabar.textContent;
                } else if (provinceSelect.options.length > 1) {
                    provinceSelect.value = provinceSelect.options[1].value;
                    state.provinceId   = parseInt(provinceSelect.options[1].value);
                    state.provinceName = provinceSelect.options[1].textContent;
                }

                loadRegenciesForProvince(state.provinceId, true);
            })
            .catch(() => {
                provinceSelect.innerHTML = '<option value="">Gagal memuat provinsi</option>';
            });

        function loadRegenciesForProvince(provinceId, isInitial = false) {
            regencySelect.innerHTML = '<option value="">Memuat Kab/Kota...</option>';

            fetch(`/admin/map/regencies?province_id=${provinceId}`)
                .then(r => r.json())
                .then(res => {
                    regencySelect.innerHTML = '<option value="">-- Pilih Kabupaten/Kota --</option>';
                    const list = res.data || [];

                    list.forEach(reg => {
                        const opt = document.createElement('option');
                        opt.value = reg.id;
                        opt.textContent = reg.name;
                        opt.dataset.name = reg.name;
                        opt.dataset.lat = reg.latitude;
                        opt.dataset.lng = reg.longitude;
                        regencySelect.appendChild(opt);
                    });

                    if (isInitial) {
                        const indra = [...regencySelect.options].find(o => o.textContent.toLowerCase().includes('indramayu'));
                        if (indra) {
                            regencySelect.value = indra.value;
                            loadRegency(parseInt(indra.value), indra.dataset.name || indra.textContent);
                        } else if (regencySelect.options.length > 1) {
                            regencySelect.value = regencySelect.options[1].value;
                            loadRegency(parseInt(regencySelect.options[1].value), regencySelect.options[1].textContent);
                        }
                    }
                })
                .catch(() => {
                    regencySelect.innerHTML = '<option value="">Gagal memuat Kab/Kota</option>';
                });
        }

        provinceSelect.addEventListener('change', function () {
            const id = parseInt(this.value);
            if (!id) {
                loadIndonesia();
                return;
            }
            const name = this.options[this.selectedIndex]?.dataset?.name || this.options[this.selectedIndex]?.text || 'Provinsi';
            state.provinceId   = id;
            state.provinceName = name;
            loadProvince(id, name);
        });

        regencySelect.addEventListener('change', function () {
            const id = parseInt(this.value);
            if (!id) {
                if (state.provinceId) {
                    loadProvince(state.provinceId, state.provinceName);
                } else {
                    loadIndonesia();
                }
                return;
            }
            const name = this.options[this.selectedIndex]?.dataset?.name || this.options[this.selectedIndex]?.text || 'Kabupaten';
            loadRegency(id, name);
        });

        // ──────────────────────────────────────────────────────
        // LEVEL 1: INDONESIA
        // ──────────────────────────────────────────────────────
        function loadIndonesia() {
            state.level        = 'indonesia';
            state.provinceId   = null;
            state.provinceName = null;
            state.regencyId    = null;
            state.regencyName  = null;
            state.districtId   = null;
            state.districtName = null;
            state.villageId    = null;
            state.villageName  = null;

            provinceSelect.value = '';
            regencySelect.innerHTML = '<option value="">-- Pilih Kabupaten/Kota --</option>';

            updateBreadcrumb();
            updateLevelPill('38 Provinsi Indonesia');
            showLoading('Memuat 38 batas provinsi Indonesia...');
            closePanel();
            clearAllLayers();

            fetch('/admin/map/geo/provinces')
                .then(r => r.json())
                .then(geojson => {
                    hideLoading();
                    if (!geojson.features || geojson.features.length === 0) return;

                    const geoLayer = L.geoJSON(geojson, {
                        style: {
                            color: '#0f766e',
                            weight: 2,
                            fillColor: '#14b8a6',
                            fillOpacity: 0.28,
                        },
                        onEachFeature: function (feature, layer) {
                            const p = feature.properties;
                            layer.bindTooltip(
                                `<strong>Provinsi ${p.name}</strong><br>${p.regency_count} Kabupaten/Kota &bull; Klik untuk detail wilayah`,
                                { className: 'geo-polygon-tooltip', sticky: true }
                            );

                            layer.on('mouseover', function () {
                                if (activeLayer !== this) {
                                    this.setStyle({ fillOpacity: 0.48, weight: 3, color: '#115e59' });
                                }
                            });

                            layer.on('mouseout', function () {
                                if (activeLayer !== this) {
                                    this.setStyle({ fillOpacity: 0.28, weight: 2, color: '#0f766e' });
                                }
                            });

                            layer.on('click', function (e) {
                                L.DomEvent.stopPropagation(e);
                                activeLayer = this;
                                this.setStyle({ fillOpacity: 0.55, weight: 3, color: '#042f2e' });
                                safeFitBounds(this, 0.05);
                                loadProvince(p.id, p.name);
                            });
                        }
                    });

                    geoLayer.addTo(layers.province);
                    safeFitBounds(geoLayer, 0.02);
                })
                .catch(() => hideLoading());
        }

        // ──────────────────────────────────────────────────────
        // LEVEL 2: PROVINCE
        // ──────────────────────────────────────────────────────
        function loadProvince(provinceId, provinceName) {
            state.level        = 'province';
            state.provinceId   = provinceId;
            state.provinceName = provinceName;
            state.regencyId    = null;
            state.regencyName  = null;
            state.districtId   = null;
            state.districtName = null;
            state.villageId    = null;
            state.villageName  = null;

            provinceSelect.value = provinceId;
            updateBreadcrumb();
            updateLevelPill(`Kab/Kota di ${provinceName}`);
            showLoading(`Memuat batas kabupaten di ${provinceName}...`);
            closePanel();
            clearAllLayers();

            loadRegenciesForProvince(provinceId, false);

            fetch(`/admin/map/geo/regencies?province_id=${provinceId}`)
                .then(r => r.json())
                .then(geojson => {
                    hideLoading();
                    if (!geojson.features || geojson.features.length === 0) return;

                    const geoLayer = L.geoJSON(geojson, {
                        style: {
                            color: '#166534',
                            weight: 2,
                            fillColor: '#22c55e',
                            fillOpacity: 0.28,
                        },
                        onEachFeature: function (feature, layer) {
                            const p = feature.properties;
                            layer.bindTooltip(
                                `<strong>${p.type_label} ${p.name}</strong><br>${p.district_count} Kecamatan &bull; Klik untuk detail wilayah`,
                                { className: 'geo-polygon-tooltip', sticky: true }
                            );

                            layer.on('mouseover', function () {
                                if (activeLayer !== this) {
                                    this.setStyle({ fillOpacity: 0.48, weight: 3, color: '#14532d' });
                                }
                            });

                            layer.on('mouseout', function () {
                                if (activeLayer !== this) {
                                    this.setStyle({ fillOpacity: 0.28, weight: 2, color: '#166534' });
                                }
                            });

                            layer.on('click', function (e) {
                                L.DomEvent.stopPropagation(e);
                                activeLayer = this;
                                this.setStyle({ fillOpacity: 0.55, weight: 3, color: '#052e16' });
                                safeFitBounds(this, 0.05);
                                loadRegency(p.id, p.name);
                            });
                        }
                    });

                    geoLayer.addTo(layers.regency);
                    safeFitBounds(geoLayer, 0.05);
                })
                .catch(() => hideLoading());
        }

        // ──────────────────────────────────────────────────────
        // LEVEL 3: REGENCY → DISTRICTS
        // ──────────────────────────────────────────────────────
        function loadRegency(regencyId, regencyName) {
            state.regencyId    = regencyId;
            state.regencyName  = regencyName;
            state.level        = 'district';
            state.districtId   = null;
            state.districtName = null;
            state.villageId    = null;
            state.villageName  = null;

            regencySelect.value = regencyId;
            updateBreadcrumb();
            updateLevelPill(`Kecamatan (${regencyName})`);
            showLoading(`Memuat peta cuaca kecamatan di ${regencyName}...`);
            closePanel();
            clearAllLayers();

            fetch(`/admin/map/geo/districts?regency_id=${regencyId}`)
                .then(r => r.json())
                .then(geojson => {
                    if (geojson.features && geojson.features.length > 0) {
                        const geoLayer = L.geoJSON(geojson, {
                            style: districtStyle,
                            onEachFeature: onEachDistrict,
                        });

                        geoLayer.addTo(layers.district);
                        safeFitBounds(geoLayer, 0.05);
                        hideLoading();
                    } else {
                        fetch(`/admin/map/geo/regency/${regencyId}`)
                            .then(r => r.json())
                            .then(regFeature => {
                                hideLoading();
                                if (regFeature && regFeature.geometry) {
                                    const layer = L.geoJSON(regFeature, {
                                        style: {
                                            color: '#166534',
                                            weight: 2.8,
                                            fillColor: '#22c55e',
                                            fillOpacity: 0.35,
                                        },
                                        onEachFeature: function (f, l) {
                                            l.bindTooltip(
                                                `<strong>${regFeature.properties?.type_label || 'Kab/Kota'} ${regencyName}</strong><br>Batas Polygon Aktif`,
                                                { className: 'geo-polygon-tooltip', sticky: true }
                                            );
                                        }
                                    }).addTo(layers.regency);
                                    safeFitBounds(layer, 0.05);
                                }

                                inspectCoordinateWeather(regFeature.properties?.lat || -6.32, regFeature.properties?.lng || 108.20, regencyName);
                            })
                            .catch(() => hideLoading());
                    }
                })
                .catch(() => hideLoading());
        }

        // ──────────────────────────────────────────────────────
        // DISTRICT POLYGON STYLES & EVENTS
        // ──────────────────────────────────────────────────────
        function districtStyle() {
            return {
                color:       '#166534',
                weight:      2.2,
                fillColor:   '#22c55e',
                fillOpacity: 0.30,
            };
        }

        function districtHoverStyle() {
            return { fillOpacity: 0.52, weight: 3.2, color: '#14532d', fillColor: '#16a34a' };
        }

        function districtSelectedStyle() {
            return { fillOpacity: 0.65, weight: 3.8, color: '#052e16', fillColor: '#15803d' };
        }

        function onEachDistrict(feature, layer) {
            const props = feature.properties;

            layer.bindTooltip(
                `<strong>Kecamatan ${props.name}</strong><br>${props.farm_count} Lahan Pertanian &bull; ${props.total_area_ha} Ha`,
                { className: 'geo-polygon-tooltip', sticky: true }
            );

            layer.on('mouseover', function () {
                if (activeLayer !== this) {
                    this.setStyle(districtHoverStyle());
                }
            });

            layer.on('mouseout', function () {
                if (activeLayer !== this) {
                    this.setStyle(districtStyle());
                }
            });

            layer.on('click', function (e) {
                L.DomEvent.stopPropagation(e);
                if (activeLayer && activeLayer !== this) {
                    activeLayer.setStyle(districtStyle());
                }
                activeLayer = this;
                this.setStyle(districtSelectedStyle());
                this.bringToFront();

                safeFitBounds(this, 0.08);
                fetchDistrictSummary(props.id, props.name);
            });
        }

        // ──────────────────────────────────────────────────────
        // DISTRICT SUMMARY PANEL
        // ──────────────────────────────────────────────────────
        function fetchDistrictSummary(districtId, districtName) {
            state.districtId   = districtId;
            state.districtName = districtName;
            state.level        = 'district';

            showLoading(`Mengambil parameter cuaca & wilayah Kec. ${districtName}...`);

            fetch(`/admin/map/districts/${districtId}/summary`)
                .then(r => r.json())
                .then(data => {
                    hideLoading();
                    state.currentData = {
                        type: 'district',
                        id: districtId,
                        name: districtName,
                        data: data,
                        weather: data.weather,
                        soil: data.soil,
                        forecast: data.forecast || [],
                        warning: data.warning,
                        statistics: data.statistics || {},
                        agriculture: data.agriculture || {},
                        irrigation: data.irrigation || {},
                        risk: data.risk || {},
                    };

                    tabBtnAgri.style.display = 'flex';
                    updateTabButtons();

                    spBadge.textContent    = 'Kecamatan';
                    spTitle.textContent    = `Kec. ${districtName}`;
                    spSubtitle.textContent = `Kab. ${data.district?.regency || state.regencyName || '—'}`;

                    spSoilBtn.href = `/admin/soil/create?latitude=${data.district?.latitude || -6.32}&longitude=${data.district?.longitude || 108.20}`;

                    if (data.has_sub_villages && (data.statistics?.villages || 0) > 0) {
                        spDrillBtn.style.display = 'flex';
                        spDrillBtn.textContent   = `Lihat Desa / Kelurahan (${data.statistics.villages} Desa)`;
                        spDrillBtn.onclick       = () => drillToVillage(districtId, districtName);
                    } else {
                        spDrillBtn.style.display = 'none';
                    }

                    spBackBtn.style.display  = 'flex';
                    spBackBtn.onclick        = () => loadProvince(state.provinceId, state.provinceName);

                    renderActiveTabContent();
                    openPanel();
                    updateBreadcrumb();
                })
                .catch(() => hideLoading());
        }

        // ──────────────────────────────────────────────────────
        // LEVEL 4: VILLAGES IN DISTRICT
        // ──────────────────────────────────────────────────────
        function drillToVillage(districtId, districtName) {
            state.level        = 'village';
            state.districtId   = districtId;
            state.districtName = districtName;
            state.villageId    = null;
            state.villageName  = null;

            updateBreadcrumb();
            updateLevelPill(`Desa di Kec. ${districtName}`);
            showLoading(`Memuat batas desa & cuaca di Kec. ${districtName}...`);
            closePanel();

            fetch(`/admin/map/geo/villages?district_id=${districtId}`)
                .then(r => r.json())
                .then(geojson => {
                    hideLoading();
                    if (!geojson.features || geojson.features.length === 0) {
                        layers.district.eachLayer(l => l.setStyle(districtStyle()));
                        if (activeLayer) {
                            activeLayer.setStyle(districtSelectedStyle());
                            safeFitBounds(activeLayer, 0.08);
                        }
                        fetchDistrictSummary(districtId, districtName);
                        return;
                    }

                    layers.district.eachLayer(l => l.setStyle({ fillOpacity: 0.04, weight: 1, color: '#94a3b8' }));
                    layers.village.clearLayers();
                    layers.farm.clearLayers();

                    const geoLayer = L.geoJSON(geojson, {
                        style: {
                            color:       '#ea580c',
                            weight:      2,
                            fillColor:   '#f97316',
                            fillOpacity: 0.22,
                        },
                        onEachFeature: function (feature, layer) {
                            const p = feature.properties;
                            layer.bindTooltip(
                                `<strong>Desa ${p.name}</strong><br>${p.farm_count} Lahan &bull; ${p.total_area_ha} Ha`,
                                { className: 'geo-polygon-tooltip', sticky: true }
                            );

                            layer.on('mouseover', function () {
                                if (activeLayer !== this) {
                                    this.setStyle({ fillOpacity: 0.42, weight: 3, color: '#c2410c' });
                                }
                            });

                            layer.on('mouseout', function () {
                                if (activeLayer !== this) {
                                    this.setStyle({ fillOpacity: 0.22, weight: 2, color: '#ea580c' });
                                }
                            });

                            layer.on('click', function (e) {
                                L.DomEvent.stopPropagation(e);
                                activeLayer = this;
                                this.setStyle({ fillOpacity: 0.55, weight: 3, color: '#9a3412', fillColor: '#ea580c' });
                                safeFitBounds(this, 0.08);
                                fetchVillageSummary(p.id, p.name);
                            });
                        }
                    });

                    geoLayer.addTo(layers.village);
                    safeFitBounds(geoLayer, 0.05);
                })
                .catch(() => {
                    hideLoading();
                    layers.district.eachLayer(l => l.setStyle(districtStyle()));
                });
        }

        // ──────────────────────────────────────────────────────
        // VILLAGE SUMMARY PANEL
        // ──────────────────────────────────────────────────────
        function fetchVillageSummary(villageId, villageName) {
            state.villageId   = villageId;
            state.villageName = villageName;

            showLoading(`Mengambil data cuaca Desa ${villageName}...`);

            fetch(`/admin/map/villages/${villageId}/summary`)
                .then(r => r.json())
                .then(data => {
                    hideLoading();
                    state.currentData = {
                        type: 'village',
                        id: villageId,
                        name: villageName,
                        data: data,
                        weather: data.weather,
                        soil: data.soil,
                        forecast: data.forecast || [],
                        warning: data.warning,
                        statistics: data.statistics || {},
                        agriculture: data.agriculture || {},
                        irrigation: data.irrigation || {},
                        risk: data.risk || {},
                    };

                    tabBtnAgri.style.display = 'flex';
                    updateTabButtons();

                    spBadge.textContent    = 'Desa / Kelurahan';
                    spTitle.textContent    = `Desa ${villageName}`;
                    spSubtitle.textContent = `Kec. ${data.village?.district || state.districtName || '—'}, Kab. ${state.regencyName || '—'}`;

                    spSoilBtn.href = `/admin/soil/create?latitude=${data.village?.latitude || -6.32}&longitude=${data.village?.longitude || 108.20}`;

                    spDrillBtn.style.display = 'flex';
                    spDrillBtn.textContent   = 'Lihat Lahan &amp; Titik Sawah';
                    spDrillBtn.onclick       = () => drillToFarm(villageId, villageName);

                    spBackBtn.style.display  = 'flex';
                    spBackBtn.onclick        = () => drillToVillage(state.districtId, state.districtName);

                    renderActiveTabContent();
                    openPanel();
                    updateBreadcrumb();
                })
                .catch(() => hideLoading());
        }

        // ──────────────────────────────────────────────────────
        // LEVEL 5: FARMS IN VILLAGE
        // ──────────────────────────────────────────────────────
        function drillToFarm(villageId, villageName) {
            state.level     = 'farm';
            state.villageId = villageId;

            updateBreadcrumb();
            updateLevelPill(`Lahan Pertanian (${villageName})`);
            showLoading(`Memuat petak lahan &amp; sensor cuaca di Desa ${villageName}...`);
            closePanel();

            layers.village.eachLayer(l => l.setStyle({ fillOpacity: 0.05, weight: 1, color: '#94a3b8' }));
            layers.farm.clearLayers();

            fetch(`/admin/map/geo/farms?village_id=${villageId}`)
                .then(r => r.json())
                .then(geojson => {
                    hideLoading();
                    if (!geojson.features || geojson.features.length === 0) return;

                    const geoLayer = L.geoJSON(geojson, {
                        style: function () {
                            return {
                                color:       '#15803d',
                                weight:      2.5,
                                fillColor:   '#16a34a',
                                fillOpacity: 0.38,
                            };
                        },
                        pointToLayer: function (feature, latlng) {
                            const p = feature.properties;
                            const temp = p.weather?.temperature ? `${Math.round(p.weather.temperature)}°` : 'Lahan';
                            
                            const pinHtml = `
                                <div class="geo-pin-bubble">
                                    <span style="font-weight: 800;">${temp}</span>
                                    <span style="font-size: 0.65rem; color: #475569;">${p.name || 'Lahan'}</span>
                                </div>
                            `;

                            return L.marker(latlng, {
                                icon: L.divIcon({
                                    className: 'geo-pin-marker',
                                    html: pinHtml,
                                    iconSize: [80, 26],
                                    iconAnchor: [40, 13]
                                })
                            });
                        },
                        onEachFeature: function (feature, layer) {
                            const p = feature.properties;
                            layer.on('click', function (e) {
                                L.DomEvent.stopPropagation(e);
                                inspectCoordinateWeather(p.lat, p.lng, p.name || 'Lahan Sawah');
                            });
                        }
                    });

                    geoLayer.addTo(layers.farm);
                    safeFitBounds(geoLayer, 0.12);
                })
                .catch(() => hideLoading());
        }

        // ──────────────────────────────────────────────────────
        // TAB CONTENT RENDERER
        // ──────────────────────────────────────────────────────
        function updateTabButtons() {
            document.querySelectorAll('.geo-panel-tab').forEach(tab => {
                tab.classList.toggle('is-active', tab.dataset.tab === state.activeTab);
            });
        }

        function renderActiveTabContent() {
            const d = state.currentData;
            if (!d) return;

            if (state.activeTab === 'weather') {
                renderWeatherTab(d);
            } else if (state.activeTab === 'soil') {
                renderSoilTab(d);
            } else if (state.activeTab === 'agri') {
                renderAgriTab(d);
            }

            spBody.scrollTop = 0;
        }

        function renderWeatherTab(d) {
            const wt = d.weather || {};
            const fc = d.forecast || [];
            const temp = wt.temperature != null ? Math.round(wt.temperature * 10) / 10 : 29.5;
            const feels = wt.feels_like != null ? Math.round(wt.feels_like * 10) / 10 : (temp + 1.8);
            const condition = wt.description || wt.weather || 'Cerah Berawan';
            const iconSvg = getWeatherSvg(condition, 26);
            const humidity = wt.humidity ?? 75;
            const wind = wt.wind_speed ?? 12;
            const rain = wt.rain ?? 0;
            const pressure = wt.pressure ?? 1011;

            let forecastHtml = '';
            if (fc && fc.length > 0) {
                forecastHtml = `
                    <div style="margin-top: 2px;">
                        <div class="geo-section-title">
                            <span>Prakiraan 5 Hari BMKG</span>
                            <span style="font-size: 0.65rem; color: #166534; font-weight: 600;">Terverifikasi</span>
                        </div>
                        <div class="geo-forecast-list">
                            ${fc.slice(0, 5).map(item => `
                                <div class="geo-forecast-item">
                                    <span class="geo-forecast-item__day">${item.day_name || item.date}</span>
                                    <div class="geo-forecast-item__cond">
                                        <span style="display: inline-flex; align-items: center; color: #047857;">${getWeatherSvg(item.weather || item.description, 14)}</span>
                                        <span style="font-size: 0.72rem;">${item.weather || item.description}</span>
                                    </div>
                                    <div class="geo-forecast-item__temp">
                                        <span>${Math.round(item.temp_min_celsius || 24)}&deg; - ${Math.round(item.temp_max_celsius || 32)}&deg;C</span>
                                        <span class="geo-forecast-item__pop">${item.rain_probability_percentage || 20}%</span>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }

            spBody.innerHTML = `
                <div class="geo-weather-hero">
                    <div class="geo-weather-hero__top">
                        <span class="geo-weather-hero__condition-badge">
                            ${condition}
                        </span>
                        <span class="geo-weather-hero__source">
                            ${SVG_ICONS.satellite} ${d.source || 'BMKG & Radar Satelit'}
                        </span>
                    </div>
                    <div class="geo-weather-hero__main">
                        <div>
                            <div class="geo-weather-hero__temp">${temp}&deg;C</div>
                            <div class="geo-weather-hero__feels">Terasa seperti ${feels}&deg;C</div>
                        </div>
                        <div class="geo-weather-hero__icon-box">
                            ${iconSvg}
                        </div>
                    </div>
                </div>

                <div class="geo-metric-grid">
                    <div class="geo-metric-card">
                        <div class="geo-metric-card__icon geo-metric-card__icon--humidity">${SVG_ICONS.droplet}</div>
                        <div>
                            <div class="geo-metric-card__val">${humidity}%</div>
                            <div class="geo-metric-card__lbl">Kelembapan Udara</div>
                        </div>
                    </div>
                    <div class="geo-metric-card">
                        <div class="geo-metric-card__icon geo-metric-card__icon--wind">${SVG_ICONS.wind}</div>
                        <div>
                            <div class="geo-metric-card__val">${wind} km/j</div>
                            <div class="geo-metric-card__lbl">Kecepatan Angin</div>
                        </div>
                    </div>
                    <div class="geo-metric-card">
                        <div class="geo-metric-card__icon geo-metric-card__icon--rain">${SVG_ICONS.droplet}</div>
                        <div>
                            <div class="geo-metric-card__val">${rain} mm</div>
                            <div class="geo-metric-card__lbl">Curah Hujan</div>
                        </div>
                    </div>
                    <div class="geo-metric-card">
                        <div class="geo-metric-card__icon geo-metric-card__icon--pressure">${SVG_ICONS.pressure}</div>
                        <div>
                            <div class="geo-metric-card__val">${pressure} hPa</div>
                            <div class="geo-metric-card__lbl">Tekanan Atmosfer</div>
                        </div>
                    </div>
                </div>

                <div class="geo-advisory-card ${rain > 10 ? 'geo-advisory-card--warning' : ''}">
                    <div class="geo-advisory-card__icon">${SVG_ICONS.lightbulb}</div>
                    <div>
                        <div class="geo-advisory-card__title">Rekomendasi Agrometeorologi Tani</div>
                        <div class="geo-advisory-card__desc">
                            ${d.warning || (rain > 5
                                ? 'Potensi hujan lokal terdeteksi. Disarankan menunda pemupukan cair dan memastikan saluran drainase sawah lancar.'
                                : 'Kondisi cuaca sangat mendukung untuk aktivitas pemupukan, penyemprotan nutrisi, serta pengairan berkala.')}
                        </div>
                    </div>
                </div>

                ${forecastHtml}
            `;
        }

        function renderSoilTab(d) {
            const sl = d.soil || {};
            const ir = d.irrigation || {};
            const ri = d.risk || {};

            const soilTemp = sl.soil_temp_celsius ?? 27.2;
            const soilMoist = sl.moisture_percentage ?? 65;
            const irrigationPct = ir.coverage_percentage ?? (soilMoist > 50 ? 80 : 45);
            const waterStatus = d.water?.status || (irrigationPct >= 70 ? 'NORMAL' : (irrigationPct >= 40 ? 'TERBATAS' : 'KRITIS'));

            spBody.innerHTML = `
                <div class="geo-section-title">
                    <span>Kondisi Agroklimat &amp; Lapisan Tanah</span>
                    <span style="font-size: 0.65rem; color: #166534; font-weight: 600;">Sensor Satelit Agro</span>
                </div>

                <div class="geo-metric-grid">
                    <div class="geo-metric-card">
                        <div class="geo-metric-card__icon geo-metric-card__icon--soil-temp">${SVG_ICONS.thermometer}</div>
                        <div>
                            <div class="geo-metric-card__val">${soilTemp}&deg;C</div>
                            <div class="geo-metric-card__lbl">Suhu Tanah (10cm)</div>
                        </div>
                    </div>
                    <div class="geo-metric-card">
                        <div class="geo-metric-card__icon geo-metric-card__icon--soil-moist">${SVG_ICONS.sprout}</div>
                        <div>
                            <div class="geo-metric-card__val">${soilMoist}%</div>
                            <div class="geo-metric-card__lbl">Kelembapan Tanah</div>
                        </div>
                    </div>
                </div>

                <div class="geo-progress" style="margin-top: 8px;">
                    <div class="geo-progress__label">
                        <span>Indeks Kelembapan Tanah</span>
                        <span class="geo-progress__value">${soilMoist}% (${soilMoist > 60 ? 'Optimal' : (soilMoist > 35 ? 'Cukup' : 'Kering')})</span>
                    </div>
                    <div class="geo-progress__track">
                        <div class="geo-progress__fill geo-progress__fill--soil" style="width: ${Math.min(100, soilMoist)}%"></div>
                    </div>
                </div>

                <div class="geo-divider"></div>

                <div class="geo-section-title">
                    <span>Sistem Irigasi &amp; Pasokan Air</span>
                </div>

                <div class="geo-progress">
                    <div class="geo-progress__label">
                        <span>Cakupan Irigasi Wilayah</span>
                        <span class="geo-progress__value">${irrigationPct}%</span>
                    </div>
                    <div class="geo-progress__track">
                        <div class="geo-progress__fill" style="width: ${irrigationPct}%"></div>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; padding: 7px 11px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 7px; margin-top: 6px;">
                    <span style="font-size: 0.76rem; color: #475569; font-weight: 600;">Status Pasokan Air:</span>
                    <span class="geo-water-badge ${waterBadgeClass(waterStatus)}">${waterStatus}</span>
                </div>

                <div class="geo-divider"></div>

                <div class="geo-section-title">
                    <span>Penilaian Risiko Musim</span>
                </div>

                <div class="geo-risk-grid">
                    <div class="geo-risk-row">
                        <span class="geo-risk-row__name">Risiko Kekeringan</span>
                        <span class="geo-risk-badge ${riskClass(ri.drought || 'LOW')}">${ri.drought || 'RENDAH'}</span>
                    </div>
                    <div class="geo-risk-row">
                        <span class="geo-risk-row__name">Risiko Banjir / Genangan</span>
                        <span class="geo-risk-badge ${riskClass(ri.flood || 'LOW')}">${ri.flood || 'RENDAH'}</span>
                    </div>
                    <div class="geo-risk-row">
                        <span class="geo-risk-row__name">Risiko Serangan Hama &amp; Wereng</span>
                        <span class="geo-risk-badge ${riskClass(ri.disease || 'MEDIUM')}">${ri.disease || 'SEDANG'}</span>
                    </div>
                </div>
            `;
        }

        function renderAgriTab(d) {
            const s = d.statistics || {};
            const ag = d.agriculture || {};

            const total = (ag.planting || 0) + (ag.harvest_ready || 0) + (ag.idle || 0) || 1;
            const pPct  = Math.round((ag.planting || 0) / total * 100);
            const hPct  = Math.round((ag.harvest_ready || 0) / total * 100);
            const iPct  = 100 - pPct - hPct;

            spBody.innerHTML = `
                <div class="geo-section-title">
                    <span>Statistik Demografi Pertanian</span>
                </div>

                <div class="geo-metric-grid">
                    <button
                        type="button"
                        class="geo-metric-card geo-metric-card--clickable"
                        data-agriculture-action="farmer"
                        title="Lihat data petani di wilayah ini"
                    >
                        <div class="geo-metric-card__icon geo-metric-card__icon--wind">${SVG_ICONS.farmer}</div>
                        <div>
                            <div class="geo-metric-card__val">${s.farmers ?? 0}</div>
                            <div class="geo-metric-card__lbl">Petani Mitra</div>
                        </div>
                    </button>
                    <button
                        type="button"
                        class="geo-metric-card geo-metric-card--clickable"
                        data-agriculture-action="farm"
                        title="Lihat data lahan di wilayah ini"
                    >
                        <div class="geo-metric-card__icon geo-metric-card__icon--soil-moist">${SVG_ICONS.sprout}</div>
                        <div>
                            <div class="geo-metric-card__val">${s.farms ?? 0}</div>
                            <div class="geo-metric-card__lbl">Petak Lahan</div>
                        </div>
                    </button>
                    <div class="geo-metric-card">
                        <div class="geo-metric-card__icon geo-metric-card__icon--rain">${SVG_ICONS.farmArea}</div>
                        <div>
                            <div class="geo-metric-card__val">${s.farm_area_hectare ?? 0}</div>
                            <div class="geo-metric-card__lbl">Total Hektar (Ha)</div>
                        </div>
                    </div>
                    <div class="geo-metric-card">
                        <div class="geo-metric-card__icon geo-metric-card__icon--humidity">${SVG_ICONS.village}</div>
                        <div>
                            <div class="geo-metric-card__val">${s.villages ?? 1}</div>
                            <div class="geo-metric-card__lbl">Desa Terdata</div>
                        </div>
                    </div>
                </div>

                <div class="geo-divider"></div>

                <div class="geo-section-title">
                    <span>Distribusi Fase Pertanian</span>
                </div>

                <div class="geo-agri-bar">
                    <div class="geo-agri-bar__segment geo-agri-bar__segment--planting"  style="width:${pPct}%"></div>
                    <div class="geo-agri-bar__segment geo-agri-bar__segment--harvest"   style="width:${hPct}%"></div>
                    <div class="geo-agri-bar__segment geo-agri-bar__segment--idle"      style="width:${iPct}%"></div>
                </div>
                <div class="geo-agri-legend">
                    <div class="geo-agri-legend__item"><div class="geo-agri-legend__dot geo-agri-legend__dot--planting"></div>Fase Tanam (${ag.planting ?? 0})</div>
                    <div class="geo-agri-legend__item"><div class="geo-agri-legend__dot geo-agri-legend__dot--harvest"></div>Siap Panen (${ag.harvest_ready ?? 0})</div>
                    <div class="geo-agri-legend__item"><div class="geo-agri-legend__dot geo-agri-legend__dot--idle"></div>Bero / Olah (${ag.idle ?? 0})</div>
                </div>
            `;
        }

        // ──────────────────────────────────────────────────────
        // AGRICULTURE CARD NAVIGATION
        // ──────────────────────────────────────────────────────
        function goToAgriculture(action) {
            const params = new URLSearchParams();

            if (action === 'farmer') {
                params.set('focus', 'farmer');
            }

            if (action === 'farm') {
                params.set('focus', 'farm');
            }

            // Saat panel sedang berada di level Kecamatan
            if (state.level === 'district' && state.districtId) {
                params.set('district_id', state.districtId);
                params.set('district_name', state.districtName || '');
            }

            // Saat panel sedang berada di level Desa
            if (
                (state.level === 'village' || state.level === 'farm') &&
                state.villageId
            ) {
                params.set('village_id', state.villageId);
                params.set('village_name', state.villageName || '');

                if (state.districtId) {
                    params.set('district_id', state.districtId);
                }

                if (state.districtName) {
                    params.set('district_name', state.districtName);
                }
            }

            window.location.href = `/admin/agriculture?${params.toString()}`;
        }

        document.addEventListener('click', function (event) {
            const card = event.target.closest('[data-agriculture-action]');

            if (!card) {
                return;
            }

            goToAgriculture(card.dataset.agricultureAction);
        });

        // ──────────────────────────────────────────────────────
        // PANEL HELPERS
        // ──────────────────────────────────────────────────────
        function openPanel()  {
            sidePanel.classList.add('is-open');
            spBody.scrollTop = 0;
        }
        function closePanel() { sidePanel.classList.remove('is-open'); }

        function goBack() {
            if (state.level === 'farm') {
                drillToVillage(state.districtId, state.districtName);
            } else if (state.level === 'village') {
                loadRegency(state.regencyId, state.regencyName);
            } else if (state.level === 'district') {
                loadProvince(state.provinceId, state.provinceName);
            } else if (state.level === 'province') {
                loadIndonesia();
            } else {
                loadIndonesia();
            }
        }

        // ──────────────────────────────────────────────────────
        // BREADCRUMB
        // ──────────────────────────────────────────────────────
        function updateBreadcrumb() {
            const bc = document.getElementById('geo-breadcrumb');

            let html = `
                <span class="geo-breadcrumb__item ${state.level === 'indonesia' ? 'is-current' : 'is-link'}" data-action="indonesia">Indonesia</span>
            `;

            if (state.provinceName && state.level !== 'indonesia') {
                html += `
                    <span class="geo-breadcrumb__sep">/</span>
                    <span class="geo-breadcrumb__item ${state.level === 'province' ? 'is-current' : 'is-link'}" data-action="province">${state.provinceName}</span>
                `;
            }

            if (state.regencyName && (state.level === 'district' || state.level === 'village' || state.level === 'farm')) {
                html += `
                    <span class="geo-breadcrumb__sep">/</span>
                    <span class="geo-breadcrumb__item ${state.level === 'district' ? 'is-current' : 'is-link'}" data-action="regency">${state.regencyName}</span>
                `;
            }

            if (state.districtName && (state.level === 'village' || state.level === 'farm')) {
                html += `
                    <span class="geo-breadcrumb__sep">/</span>
                    <span class="geo-breadcrumb__item ${state.level === 'village' ? 'is-current' : 'is-link'}" data-action="district">${state.districtName}</span>
                `;
            }

            if (state.villageName && state.level === 'farm') {
                html += `
                    <span class="geo-breadcrumb__sep">/</span>
                    <span class="geo-breadcrumb__item is-current">${state.villageName}</span>
                `;
            }

            bc.innerHTML = html;

            bc.querySelectorAll('[data-action]').forEach(el => {
                el.addEventListener('click', function () {
                    const action = this.dataset.action;
                    if (action === 'indonesia') loadIndonesia();
                    if (action === 'province')  loadProvince(state.provinceId, state.provinceName);
                    if (action === 'regency')   loadRegency(state.regencyId, state.regencyName);
                    if (action === 'district')  drillToVillage(state.districtId, state.districtName);
                });
            });
        }

        // ──────────────────────────────────────────────────────
        // LEVEL PILL & HELPERS
        // ──────────────────────────────────────────────────────
        function updateLevelPill(label) {
            levelPillText.textContent = label;
        }

        function safeFitBounds(layerOrBounds, pad = 0.05) {
            try {
                const b = (layerOrBounds && typeof layerOrBounds.getBounds === 'function')
                    ? layerOrBounds.getBounds()
                    : layerOrBounds;
                if (b && typeof b.isValid === 'function' && b.isValid()) {
                    map.fitBounds(b.pad(pad));
                }
            } catch (e) {
                console.warn('safeFitBounds ignored invalid bounds', e);
            }
        }

        function riskClass(level) {
            if (!level || typeof level !== 'string') return 'is-low';
            const l = level.toUpperCase();
            if (l.includes('TINGGI') || l.includes('BAHAYA') || l.includes('HIGH')) return 'is-high';
            if (l.includes('SEDANG') || l.includes('MEDIUM') || l.includes('WASPADA')) return 'is-medium';
            return 'is-low';
        }

        function waterBadgeClass(status) {
            if (!status || typeof status !== 'string') return 'is-safe';
            const s = status.toUpperCase();
            if (s.includes('KRISIS') || s.includes('KURANG') || s.includes('CRITICAL')) return 'is-danger';
            if (s.includes('TERBATAS') || s.includes('WASPADA') || s.includes('LIMITED')) return 'is-warning';
            return 'is-safe';
        }

    });
    </script>
@endsection
