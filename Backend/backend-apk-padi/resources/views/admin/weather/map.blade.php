@extends('layouts.admin')

@section('title', 'Geo Intelligence Map - Administrative Hierarchy')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    <link rel="stylesheet" href="{{ asset('css/admin/weather-map.css') }}" />
@endpush

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    <link rel="stylesheet" href="{{ asset('css/admin/weather-map.css') }}" />

    <div class="admin-page">

        {{-- Header Bar --}}
        <div class="admin-page__header">
            <div>
                <h1 class="admin-page__title">P.A.D.I Geo Intelligence Map</h1>
                <p class="admin-page__subtitle">Hierarchical Administrative Geographic Map &bull; Multi-Level Drill-Down</p>
            </div>
            <div class="admin-page__actions">
                <a href="{{ route('admin.weather.index') }}" class="admin-btn admin-btn--secondary">Cuaca &amp; Dashboard</a>
                <a href="{{ route('admin.soil.index') }}" class="admin-btn admin-btn--secondary">Deteksi Tanah</a>
            </div>
        </div>

        @if (session('status'))
            <div class="admin-alert admin-alert--success">{{ session('status') }}</div>
        @endif

        {{-- ═══════════════════════════════════════════════ --}}
        {{-- Region Selector Bar                            --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <section class="admin-card" style="padding: 0; overflow: hidden;">
            <div class="geo-region-bar">
                <button id="btn-indonesia" type="button" class="admin-btn admin-btn--secondary" style="padding: 6px 12px; font-size: 0.8rem; white-space: nowrap;">
                    Peta Indonesia
                </button>

                <span class="geo-region-bar__label" style="margin-left: 0.5rem;">Provinsi:</span>
                <select id="province-select" class="geo-region-select">
                    <option value="">-- Pilih Provinsi --</option>
                </select>

                <span class="geo-region-bar__label" style="margin-left: 0.5rem;">Kabupaten/Kota:</span>
                <select id="regency-select" class="geo-region-select">
                    <option value="">-- Pilih Kabupaten/Kota --</option>
                </select>

                <div style="margin-left: auto; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 0.78rem; color: #4b7c5e; font-weight: 500;">Klik polygon wilayah untuk detail drill-down</span>
                </div>
            </div>

            {{-- Map + Side Panel container --}}
            <div class="geo-map-wrapper">
                {{-- Loading overlay --}}
                <div id="geo-loading" class="geo-loading-overlay">
                    <div class="geo-loading-spinner"></div>
                </div>

                {{-- Breadcrumb Navigation --}}
                <div id="geo-breadcrumb" class="geo-breadcrumb">
                    <span class="geo-breadcrumb__item is-link" data-action="indonesia">Indonesia</span>
                </div>

                {{-- Level Indicator --}}
                <div id="geo-level-pill" class="geo-level-pill">Tingkat: Kecamatan</div>

                {{-- Leaflet Map --}}
                <div id="geoMap" class="geo-map"></div>

                {{-- Side Panel: Dynamic Content per Administrative Level --}}
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

                    <div id="sp-body" class="geo-side-panel__body">
                        {{-- Dynamically populated by JS --}}
                    </div>

                    <div class="geo-side-panel__footer">
                        <button id="sp-drill-btn" type="button" class="geo-panel-btn geo-panel-btn--primary" style="display: none;"></button>
                        <button id="sp-back-btn" type="button" class="geo-panel-btn geo-panel-btn--back" style="display: none;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                            Kembali ke Atas
                        </button>
                    </div>
                </div>
            </div>

            {{-- Legend --}}
            <div class="geo-legend-strip" style="border-top: 1px solid #d6ead8;">
                <div class="geo-legend-item">
                    <div class="geo-legend-swatch" style="background: rgba(15,118,110,0.18); border: 2px solid #0f766e;"></div>
                    <span>Batas Provinsi</span>
                </div>
                <div class="geo-legend-item">
                    <div class="geo-legend-swatch" style="background: rgba(22,101,52,0.18); border: 2px solid #16a34a;"></div>
                    <span>Batas Kab/Kota</span>
                </div>
                <div class="geo-legend-item">
                    <div class="geo-legend-swatch" style="background: rgba(37,99,235,0.18); border: 2px solid #2563eb;"></div>
                    <span>Batas Kecamatan</span>
                </div>
                <div class="geo-legend-item">
                    <div class="geo-legend-swatch" style="background: rgba(234,88,12,0.2); border: 2px solid #ea580c;"></div>
                    <span>Batas Desa</span>
                </div>
                <div class="geo-legend-item">
                    <div class="geo-legend-swatch" style="background: rgba(22,163,74,0.35); border: 2px solid #15803d;"></div>
                    <span>Polygon Lahan</span>
                </div>
                <div class="geo-legend-item">
                    <div class="geo-legend-swatch" style="background: #166534; border-radius: 50%; width: 12px; height: 12px;"></div>
                    <span>Titik Lahan</span>
                </div>
            </div>
        </section>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        // ──────────────────────────────────────────────────────
        // STATE
        // ──────────────────────────────────────────────────────
        const state = {
            level: 'district',           // 'indonesia' | 'province' | 'district' | 'village' | 'farm'
            provinceId: null,
            provinceName: 'Jawa Barat',
            regencyId: null,
            regencyName: 'Indramayu',
            districtId: null,
            districtName: null,
            villageId: null,
            villageName: null,
        };

        // ──────────────────────────────────────────────────────
        // MAP INIT
        // ──────────────────────────────────────────────────────
        const map = L.map('geoMap', { zoomControl: true }).setView([-6.32, 108.20], 10);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19
        }).addTo(map);

        L.control.scale({ position: 'bottomleft', metric: true, imperial: false }).addTo(map);

        setTimeout(() => { map.invalidateSize(); }, 150);
        setTimeout(() => { map.invalidateSize(); }, 500);
        window.addEventListener('resize', () => { map.invalidateSize(); });

        // Layer groups for each hierarchical level
        const layers = {
            province: L.layerGroup().addTo(map),
            regency:  L.layerGroup().addTo(map),
            district: L.layerGroup().addTo(map),
            village:  L.layerGroup().addTo(map),
            farm:     L.layerGroup().addTo(map),
        };

        let activeLayer = null;

        // ──────────────────────────────────────────────────────
        // DOM REFS
        // ──────────────────────────────────────────────────────
        const loadingEl      = document.getElementById('geo-loading');
        const sidePanel      = document.getElementById('geo-side-panel');
        const spBadge        = document.getElementById('sp-badge');
        const spTitle        = document.getElementById('sp-title');
        const spSubtitle     = document.getElementById('sp-subtitle');
        const spBody         = document.getElementById('sp-body');
        const spDrillBtn     = document.getElementById('sp-drill-btn');
        const spBackBtn      = document.getElementById('sp-back-btn');
        const levelPill      = document.getElementById('geo-level-pill');
        const btnIndonesia   = document.getElementById('btn-indonesia');
        const provinceSelect = document.getElementById('province-select');
        const regencySelect  = document.getElementById('regency-select');

        document.getElementById('sp-close').addEventListener('click', closePanel);
        spBackBtn.addEventListener('click', goBack);
        btnIndonesia.addEventListener('click', loadIndonesia);

        // ──────────────────────────────────────────────────────
        // LOADING HELPERS
        // ──────────────────────────────────────────────────────
        function showLoading() { loadingEl.classList.add('is-visible'); }
        function hideLoading() { loadingEl.classList.remove('is-visible'); }

        function clearAllLayers() {
            layers.province.clearLayers();
            layers.regency.clearLayers();
            layers.district.clearLayers();
            layers.village.clearLayers();
            layers.farm.clearLayers();
            activeLayer = null;
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

                // Default to Jawa Barat on first load
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

                    // Prefer Indramayu on initial load
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
        // LEVEL 1: INDONESIA (38 PROVINCE POLYGONS)
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
            showLoading();
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
                            fillOpacity: 0.30,
                        },
                        onEachFeature: function (feature, layer) {
                            const p = feature.properties;
                            layer.bindTooltip(
                                `<strong>Provinsi ${p.name}</strong><br>${p.regency_count} Kabupaten/Kota`,
                                { className: 'geo-polygon-tooltip', sticky: true }
                            );

                            layer.on('mouseover', function () {
                                if (activeLayer !== this) {
                                    this.setStyle({ fillOpacity: 0.50, weight: 3, color: '#115e59' });
                                }
                            });

                            layer.on('mouseout', function () {
                                if (activeLayer !== this) {
                                    this.setStyle({ fillOpacity: 0.30, weight: 2, color: '#0f766e' });
                                }
                            });

                            layer.on('click', function () {
                                activeLayer = this;
                                this.setStyle({ fillOpacity: 0.45, weight: 3, color: '#042f2e' });
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
        // LEVEL 2: PROVINCE (REGENCY POLYGONS PER PROVINCE)
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
            showLoading();
            closePanel();
            clearAllLayers();

            // Refresh regency dropdown in background
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
                            fillOpacity: 0.30,
                        },
                        onEachFeature: function (feature, layer) {
                            const p = feature.properties;
                            layer.bindTooltip(
                                `<strong>${p.type_label} ${p.name}</strong><br>${p.district_count} Kecamatan`,
                                { className: 'geo-polygon-tooltip', sticky: true }
                            );

                            layer.on('mouseover', function () {
                                if (activeLayer !== this) {
                                    this.setStyle({ fillOpacity: 0.50, weight: 3, color: '#14532d' });
                                }
                            });

                            layer.on('mouseout', function () {
                                if (activeLayer !== this) {
                                    this.setStyle({ fillOpacity: 0.30, weight: 2, color: '#166534' });
                                }
                            });

                            layer.on('click', function () {
                                activeLayer = this;
                                this.setStyle({ fillOpacity: 0.45, weight: 3, color: '#052e16' });
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
        // LEVEL 3: REGENCY → load Districts
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
            showLoading();
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
                        // Fallback: Fetch direct Regency GeoJSON polygon
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
                                                `<strong>${regFeature.properties?.type_label || 'Kab/Kota'} ${regencyName}</strong><br>Batas Polygon Wilayah Aktif`,
                                                { className: 'geo-polygon-tooltip', sticky: true }
                                            );
                                        }
                                    }).addTo(layers.regency);
                                    safeFitBounds(layer, 0.05);
                                }

                                // Show side panel summary for regency
                                spBadge.textContent    = 'Kabupaten / Kota';
                                spTitle.textContent    = regencyName;
                                spSubtitle.textContent = `Provinsi ${state.provinceName || 'Indonesia'}`;
                                spBody.innerHTML = `
                                    <div class="geo-stat-section">
                                        <div class="geo-stat-section__label">Status Pemetaan</div>
                                        <div style="padding: 12px; background: #f8fafc; border: 1px solid #d6ead8; border-radius: 8px; font-size: 0.8rem; color: #166534; line-height: 1.6;">
                                            <strong>${regencyName}</strong> aktif di peta. Batas polygon kabupaten telah terpasang. Data polygon tingkat kecamatan untuk wilayah ini siap dipetakan.
                                        </div>
                                    </div>
                                `;
                                spDrillBtn.style.display = 'none';
                                spBackBtn.style.display  = 'flex';
                                spBackBtn.onclick        = () => loadProvince(state.provinceId, state.provinceName);
                                openPanel();
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
                weight:      2.5,
                fillColor:   '#22c55e',
                fillOpacity: 0.35,
            };
        }

        function districtHoverStyle() {
            return { fillOpacity: 0.55, weight: 3.5, color: '#14532d', fillColor: '#16a34a' };
        }

        function districtSelectedStyle() {
            return { fillOpacity: 0.65, weight: 4, color: '#052e16', fillColor: '#15803d' };
        }

        function onEachDistrict(feature, layer) {
            const props = feature.properties;

            layer.bindTooltip(
                `<strong>Kecamatan ${props.name}</strong><br>${props.farm_count} lahan &bull; ${props.total_area_ha} Ha`,
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

            layer.on('click', function () {
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

            showLoading();

            fetch(`/admin/map/districts/${districtId}/summary`)
                .then(r => r.json())
                .then(data => {
                    hideLoading();
                    renderDistrictPanel(data);
                    openPanel();
                    updateBreadcrumb();
                })
                .catch(() => hideLoading());
        }

        function renderDistrictPanel(d) {
            const s  = d.statistics || {};
            const ag = d.agriculture || {};
            const ir = d.irrigation  || {};
            const ri = d.risk        || {};

            spBadge.textContent    = 'Kecamatan';
            spTitle.textContent    = d.district?.name || '—';
            spSubtitle.textContent = d.district?.regency || '—';

            const total = (ag.planting || 0) + (ag.harvest_ready || 0) + (ag.idle || 0) || 1;
            const pPct  = Math.round((ag.planting      || 0) / total * 100);
            const hPct  = Math.round((ag.harvest_ready || 0) / total * 100);
            const iPct  = 100 - pPct - hPct;

            spBody.innerHTML = `
                <div class="geo-stat-section">
                    <div class="geo-stat-section__label">Statistik Wilayah</div>
                    <div class="geo-stat-grid">
                        <div class="geo-stat-card">
                            <div class="geo-stat-card__value">${s.villages ?? 0}</div>
                            <div class="geo-stat-card__label">Desa</div>
                        </div>
                        <div class="geo-stat-card">
                            <div class="geo-stat-card__value">${s.farmers ?? 0}</div>
                            <div class="geo-stat-card__label">Petani</div>
                        </div>
                        <div class="geo-stat-card">
                            <div class="geo-stat-card__value">${s.farms ?? 0}</div>
                            <div class="geo-stat-card__label">Lahan</div>
                        </div>
                        <div class="geo-stat-card">
                            <div class="geo-stat-card__value">${s.farm_area_hectare ?? 0}</div>
                            <div class="geo-stat-card__label">Hektar</div>
                        </div>
                    </div>
                </div>

                <div class="geo-divider"></div>

                <div class="geo-stat-section">
                    <div class="geo-stat-section__label">Kondisi Pertanian</div>
                    <div class="geo-agri-bar">
                        <div class="geo-agri-bar__segment geo-agri-bar__segment--planting"  style="width:${pPct}%"></div>
                        <div class="geo-agri-bar__segment geo-agri-bar__segment--harvest"   style="width:${hPct}%"></div>
                        <div class="geo-agri-bar__segment geo-agri-bar__segment--idle"      style="width:${iPct}%"></div>
                    </div>
                    <div class="geo-agri-legend">
                        <div class="geo-agri-legend__item"><div class="geo-agri-legend__dot geo-agri-legend__dot--planting"></div>Tanam (${ag.planting ?? 0})</div>
                        <div class="geo-agri-legend__item"><div class="geo-agri-legend__dot geo-agri-legend__dot--harvest"></div>Panen (${ag.harvest_ready ?? 0})</div>
                        <div class="geo-agri-legend__item"><div class="geo-agri-legend__dot geo-agri-legend__dot--idle"></div>Bero (${ag.idle ?? 0})</div>
                    </div>
                </div>

                <div class="geo-divider"></div>

                <div class="geo-stat-section">
                    <div class="geo-stat-section__label">Irigasi &amp; Air</div>
                    <div class="geo-progress" style="margin-bottom: 10px;">
                        <div class="geo-progress__label">
                            <span>Cakupan Irigasi</span>
                            <span class="geo-progress__value">${ir.coverage_percentage ?? 0}%</span>
                        </div>
                        <div class="geo-progress__track">
                            <div class="geo-progress__fill" style="width:${ir.coverage_percentage ?? 0}%"></div>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.78rem;">
                        <span style="color: #475569; font-weight: 500;">Status Air:</span>
                        <span class="geo-water-badge ${waterBadgeClass(ir.water_status)}">${ir.water_status ?? '-'}</span>
                    </div>
                </div>

                <div class="geo-divider"></div>

                <div class="geo-stat-section">
                    <div class="geo-stat-section__label">Penilaian Risiko</div>
                    <div class="geo-risk-grid">
                        <div class="geo-risk-row">
                            <span class="geo-risk-row__name">Kekeringan</span>
                            <span class="geo-risk-badge ${riskClass(ri.drought)}">${ri.drought ?? '-'}</span>
                        </div>
                        <div class="geo-risk-row">
                            <span class="geo-risk-row__name">Banjir</span>
                            <span class="geo-risk-badge ${riskClass(ri.flood)}">${ri.flood ?? '-'}</span>
                        </div>
                        <div class="geo-risk-row">
                            <span class="geo-risk-row__name">Penyakit</span>
                            <span class="geo-risk-badge ${riskClass(ri.disease)}">${ri.disease ?? '-'}</span>
                        </div>
                    </div>
                </div>
            `;

            if (d.has_sub_villages && s.villages > 0) {
                spDrillBtn.style.display = 'flex';
                spDrillBtn.textContent   = `Lihat Desa / Kelurahan (${s.villages} Desa)`;
                spDrillBtn.onclick       = () => drillToVillage(state.districtId, state.districtName);
            } else {
                spDrillBtn.style.display = 'flex';
                spDrillBtn.textContent   = 'Fokus Peta Kecamatan';
                spDrillBtn.onclick       = () => {
                    if (activeLayer) safeFitBounds(activeLayer, 0.08);
                };
            }

            spBackBtn.style.display  = 'flex';
            spBackBtn.onclick        = () => loadProvince(state.provinceId, state.provinceName);
        }

        // ──────────────────────────────────────────────────────
        // LEVEL 4: DISTRICT → load Villages
        // ──────────────────────────────────────────────────────
        function drillToVillage(districtId, districtName) {
            state.level        = 'village';
            state.districtId   = districtId;
            state.districtName = districtName;
            state.villageId    = null;
            state.villageName  = null;

            updateBreadcrumb();
            updateLevelPill(`Desa (${districtName})`);
            showLoading();
            closePanel();

            fetch(`/admin/map/geo/villages?district_id=${districtId}`)
                .then(r => r.json())
                .then(geojson => {
                    hideLoading();
                    if (!geojson.features || geojson.features.length === 0) {
                        // Fallback: keep district layer fully visible and open district summary
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
                            fillOpacity: 0.18,
                        },
                        onEachFeature: function (feature, layer) {
                            const p = feature.properties;
                            layer.bindTooltip(
                                `<strong>Desa ${p.name}</strong><br>${p.farm_count} lahan &bull; ${p.total_area_ha} Ha`,
                                { className: 'geo-polygon-tooltip', sticky: true }
                            );

                            layer.on('mouseover', function () {
                                if (activeLayer !== this) {
                                    this.setStyle({ fillOpacity: 0.38, weight: 3, color: '#c2410c' });
                                }
                            });

                            layer.on('mouseout', function () {
                                if (activeLayer !== this) {
                                    this.setStyle({ fillOpacity: 0.18, weight: 2, color: '#ea580c' });
                                }
                            });

                            layer.on('click', function () {
                                activeLayer = this;
                                this.setStyle({ fillOpacity: 0.5, weight: 3, color: '#9a3412', fillColor: '#ea580c' });
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

            showLoading();

            fetch(`/admin/map/villages/${villageId}/summary`)
                .then(r => r.json())
                .then(data => {
                    hideLoading();
                    renderVillagePanel(data);
                    openPanel();
                    updateBreadcrumb();
                })
                .catch(() => hideLoading());
        }

        function renderVillagePanel(d) {
            const s  = d.statistics || {};
            const ag = d.agriculture || {};
            const ir = d.irrigation  || {};
            const ri = d.risk        || {};
            const wt = d.weather     || {};

            spBadge.textContent    = 'Desa / Kelurahan';
            spTitle.textContent    = d.village?.name || '—';
            spSubtitle.textContent = `Kec. ${d.village?.district || '—'}, ${d.village?.regency || '—'}`;

            const total = (ag.planting || 0) + (ag.harvest_ready || 0) + (ag.idle || 0) || 1;
            const pPct  = Math.round((ag.planting      || 0) / total * 100);
            const hPct  = Math.round((ag.harvest_ready || 0) / total * 100);
            const iPct  = 100 - pPct - hPct;

            spBody.innerHTML = `
                <div class="geo-stat-section">
                    <div class="geo-stat-section__label">Statistik Desa</div>
                    <div class="geo-stat-grid">
                        <div class="geo-stat-card">
                            <div class="geo-stat-card__value">${s.farmers ?? 0}</div>
                            <div class="geo-stat-card__label">Petani</div>
                        </div>
                        <div class="geo-stat-card">
                            <div class="geo-stat-card__value">${s.farms ?? 0}</div>
                            <div class="geo-stat-card__label">Lahan</div>
                        </div>
                        <div class="geo-stat-card">
                            <div class="geo-stat-card__value">${s.farm_area_hectare ?? 0}</div>
                            <div class="geo-stat-card__label">Hektar</div>
                        </div>
                        <div class="geo-stat-card">
                            <div class="geo-stat-card__value">${wt.temperature ? wt.temperature + '&deg;C' : '—'}</div>
                            <div class="geo-stat-card__label">Suhu</div>
                        </div>
                    </div>
                </div>

                <div class="geo-divider"></div>

                <div class="geo-stat-section">
                    <div class="geo-stat-section__label">Kondisi Lahan</div>
                    <div class="geo-agri-bar">
                        <div class="geo-agri-bar__segment geo-agri-bar__segment--planting"  style="width:${pPct}%"></div>
                        <div class="geo-agri-bar__segment geo-agri-bar__segment--harvest"   style="width:${hPct}%"></div>
                        <div class="geo-agri-bar__segment geo-agri-bar__segment--idle"      style="width:${iPct}%"></div>
                    </div>
                    <div class="geo-agri-legend">
                        <div class="geo-agri-legend__item"><div class="geo-agri-legend__dot geo-agri-legend__dot--planting"></div>Tanam (${ag.planting ?? 0})</div>
                        <div class="geo-agri-legend__item"><div class="geo-agri-legend__dot geo-agri-legend__dot--harvest"></div>Panen (${ag.harvest_ready ?? 0})</div>
                        <div class="geo-agri-legend__item"><div class="geo-agri-legend__dot geo-agri-legend__dot--idle"></div>Bero (${ag.idle ?? 0})</div>
                    </div>
                </div>

                <div class="geo-divider"></div>

                <div class="geo-stat-section">
                    <div class="geo-stat-section__label">Irigasi &amp; Air</div>
                    <div class="geo-progress" style="margin-bottom: 10px;">
                        <div class="geo-progress__label">
                            <span>Cakupan Irigasi</span>
                            <span class="geo-progress__value">${ir.coverage_percentage ?? 0}%</span>
                        </div>
                        <div class="geo-progress__track">
                            <div class="geo-progress__fill" style="width:${ir.coverage_percentage ?? 0}%"></div>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.78rem;">
                        <span style="color: #475569; font-weight: 500;">Status Air:</span>
                        <span class="geo-water-badge ${waterBadgeClass(wt.status)}">${wt.status ?? '-'}</span>
                    </div>
                </div>

                <div class="geo-divider"></div>

                <div class="geo-stat-section">
                    <div class="geo-stat-section__label">Penilaian Risiko</div>
                    <div class="geo-risk-grid">
                        <div class="geo-risk-row">
                            <span class="geo-risk-row__name">Kekeringan</span>
                            <span class="geo-risk-badge ${riskClass(ri.drought)}">${ri.drought ?? '-'}</span>
                        </div>
                        <div class="geo-risk-row">
                            <span class="geo-risk-row__name">Banjir</span>
                            <span class="geo-risk-badge ${riskClass(ri.flood)}">${ri.flood ?? '-'}</span>
                        </div>
                        <div class="geo-risk-row">
                            <span class="geo-risk-row__name">Penyakit</span>
                            <span class="geo-risk-badge ${riskClass(ri.disease)}">${ri.disease ?? '-'}</span>
                        </div>
                    </div>
                </div>
            `;

            spDrillBtn.style.display = 'flex';
            spDrillBtn.textContent   = 'Lihat Lahan & Titik Sawah';
            spDrillBtn.onclick       = () => drillToFarm(state.villageId, state.villageName);

            spBackBtn.style.display  = 'flex';
            spBackBtn.onclick        = () => drillToVillage(state.districtId, state.districtName);
        }

        // ──────────────────────────────────────────────────────
        // LEVEL 5: VILLAGE → load Farms
        // ──────────────────────────────────────────────────────
        function drillToFarm(villageId, villageName) {
            state.level     = 'farm';
            state.villageId = villageId;

            updateBreadcrumb();
            updateLevelPill(`Lahan Pertanian (${villageName})`);
            showLoading();
            closePanel();

            layers.village.eachLayer(l => l.setStyle({ fillOpacity: 0.05, weight: 1, color: '#94a3b8' }));
            layers.farm.clearLayers();

            fetch(`/admin/map/geo/farms?village_id=${villageId}`)
                .then(r => r.json())
                .then(geojson => {
                    hideLoading();
                    if (!geojson.features || geojson.features.length === 0) return;

                    const geoLayer = L.geoJSON(geojson, {
                        style: function (feature) {
                            return {
                                color:       '#15803d',
                                weight:      2,
                                fillColor:   '#16a34a',
                                fillOpacity: 0.35,
                            };
                        },
                        pointToLayer: function (feature, latlng) {
                            return L.circleMarker(latlng, {
                                radius:      7,
                                fillColor:   '#166534',
                                color:       '#ffffff',
                                weight:      2,
                                opacity:     1,
                                fillOpacity: 0.9,
                            });
                        },
                        onEachFeature: function (feature, layer) {
                            const p = feature.properties;
                            layer.on('click', function (e) {
                                renderFarmPopup(p, e.latlng || layer.getLatLng?.());
                            });
                        }
                    });

                    geoLayer.addTo(layers.farm);
                    safeFitBounds(geoLayer, 0.12);
                })
                .catch(() => hideLoading());
        }

        function renderFarmPopup(props, latlng) {
            const statusLabel = {
                'active':   'Aktif',
                'inactive': 'Nonaktif',
                'verified': 'Terverifikasi',
            }[props.status] || props.status || '-';

            const html = `
                <div class="geo-farm-popup">
                    <div class="geo-farm-popup__title">${props.name || 'Lahan'}</div>
                    <div class="geo-farm-popup__subtitle">Pemilik: ${props.farmer_name || '-'}</div>
                    <div class="geo-farm-popup__row">
                        <span class="geo-farm-popup__key">Luas</span>
                        <span class="geo-farm-popup__val">${props.area_ha ?? 0} Ha</span>
                    </div>
                    <div class="geo-farm-popup__row">
                        <span class="geo-farm-popup__key">Status</span>
                        <span class="geo-farm-popup__val">${statusLabel}</span>
                    </div>
                    <a class="geo-farm-popup__link" href="/admin/weather/history?farm_id=${props.id}">Riwayat Cuaca Lahan &rarr;</a>
                    <a class="geo-farm-popup__link" href="/admin/soil/create?farm_id=${props.id}" style="margin-top: 4px;">+ Uji Sampel Tanah</a>
                </div>
            `;

            L.popup({ maxWidth: 270 })
                .setLatLng(latlng)
                .setContent(html)
                .openOn(map);
        }

        // ──────────────────────────────────────────────────────
        // PANEL HELPERS
        // ──────────────────────────────────────────────────────
        function openPanel()  { sidePanel.classList.add('is-open'); }
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

            // Attach click handlers
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
        // LEVEL PILL
        // ──────────────────────────────────────────────────────
        function updateLevelPill(label) {
            levelPill.textContent = `Tingkat: ${label}`;
        }

        // ──────────────────────────────────────────────────────
        // UTILITIES & SAFE HELPERS
        // ──────────────────────────────────────────────────────
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
            if (!level || typeof level !== 'string') return '';
            const l = level.toUpperCase();
            if (l.includes('TINGGI') || l.includes('BAHAYA') || l.includes('WASPADA')) return 'is-high';
            if (l.includes('SEDANG')) return 'is-medium';
            return 'is-low';
        }

        function waterBadgeClass(status) {
            if (!status || typeof status !== 'string') return '';
            const s = status.toUpperCase();
            if (s.includes('KRISIS') || s.includes('KURANG') || s.includes('RENDAH')) return 'is-danger';
            if (s.includes('WASPADA')) return 'is-warning';
            return 'is-safe';
        }

    });
    </script>
@endsection
