/**
 * P.A.D.I. Admin Dashboard - Interactive Client Engine
 * Features: Chart.js Telemetry & Forecasts, Leaflet Mini GIS Radar, AJAX Live Refresh,
 * Quick Broadcast & Mitigation SOP Modals, Count-Up Animations, and Instant Filters.
 */

(function () {
    'use strict';

    let dashboardChart = null;
    let miniGisMap = null;
    let mapMarkers = [];
    let mapPolygons = [];
    let streetLayer = null;
    let satelliteLayer = null;
    let activeMapLayer = 'street';
    let activePeriod = '24h';

    // =========================================================================
    // 1. Animated Count-Up Numbers
    // =========================================================================
    function animateCountUp(element, target, duration = 800, suffix = '', decimals = 0) {
        if (!element) return;
        const start = parseFloat(element.dataset.currentVal || '0') || 0;
        const startTime = performance.now();

        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            // Ease out cubic
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const current = start + (target - start) * easeOut;

            if (decimals > 0) {
                element.textContent = current.toFixed(decimals).replace('.', ',') + suffix;
            } else {
                element.textContent = Math.round(current).toLocaleString('id-ID') + suffix;
            }

            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                element.dataset.currentVal = String(target);
            }
        }

        requestAnimationFrame(update);
    }

    function initCountUp() {
        document.querySelectorAll('[data-countup]').forEach((el) => {
            const target = parseFloat(el.getAttribute('data-countup') || '0');
            const suffix = el.getAttribute('data-countup-suffix') || '';
            const decimals = parseInt(el.getAttribute('data-countup-decimals') || '0', 10);
            animateCountUp(el, target, 900, suffix, decimals);
        });
    }

    // =========================================================================
    // 2. Interactive Chart.js Controller
    // =========================================================================
    function initDashboardChart() {
        const canvas = document.getElementById('dashboardTelemetryChart');
        if (!canvas || typeof Chart === 'undefined') return;

        const ctx = canvas.getContext('2d');
        const data = window.dashboardData || {};
        const hourly = data.hourlyTelemetry || { labels: [], temperatures: [], soil_moistures: [], humidities: [], rain_chances: [] };

        // Gradients
        const gradTemp = ctx.createLinearGradient(0, 0, 0, 320);
        gradTemp.addColorStop(0, 'rgba(16, 185, 129, 0.35)');
        gradTemp.addColorStop(1, 'rgba(16, 185, 129, 0.00)');

        const gradMoisture = ctx.createLinearGradient(0, 0, 0, 320);
        gradMoisture.addColorStop(0, 'rgba(59, 130, 246, 0.28)');
        gradMoisture.addColorStop(1, 'rgba(59, 130, 246, 0.00)');

        const gradRain = ctx.createLinearGradient(0, 0, 0, 320);
        gradRain.addColorStop(0, 'rgba(245, 158, 11, 0.25)');
        gradRain.addColorStop(1, 'rgba(245, 158, 11, 0.00)');

        const chartConfig = {
            type: 'line',
            data: {
                labels: hourly.labels || [],
                datasets: [
                    {
                        label: 'Suhu Udara (°C)',
                        data: hourly.temperatures || [],
                        borderColor: '#10b981',
                        backgroundColor: gradTemp,
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                        yAxisID: 'yTemp',
                        pointRadius: 2,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#10b981',
                    },
                    {
                        label: 'Lengas Tanah (%)',
                        data: hourly.soil_moistures || [],
                        borderColor: '#3b82f6',
                        backgroundColor: gradMoisture,
                        borderWidth: 2.2,
                        fill: true,
                        tension: 0.35,
                        yAxisID: 'yPercent',
                        pointRadius: 2,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#3b82f6',
                    },
                    {
                        label: 'Kelembapan Udara (%)',
                        data: hourly.humidities || [],
                        borderColor: '#06b6d4',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0.35,
                        yAxisID: 'yPercent',
                        pointRadius: 0,
                        pointHoverRadius: 5,
                    },
                    {
                        label: 'Peluang Hujan (%)',
                        data: hourly.rain_chances || [],
                        borderColor: '#f59e0b',
                        backgroundColor: gradRain,
                        borderWidth: 2,
                        fill: true,
                        tension: 0.35,
                        yAxisID: 'yPercent',
                        pointRadius: 0,
                        pointHoverRadius: 5,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            boxWidth: 12,
                            boxHeight: 12,
                            borderRadius: 3,
                            usePointStyle: false,
                            font: { family: 'inherit', size: 12, weight: '600' },
                            color: '#475569',
                            padding: 16
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.92)',
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 12 },
                        padding: 12,
                        cornerRadius: 8,
                        boxPadding: 6,
                        callbacks: {
                            label: function (context) {
                                let unit = context.dataset.yAxisID === 'yTemp' ? ' °C' : ' %';
                                return `  ${context.dataset.label}: ${context.parsed.y}${unit}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        ticks: { font: { size: 11 }, color: '#64748b', maxTicksLimit: 12 }
                    },
                    yTemp: {
                        type: 'linear',
                        position: 'left',
                        title: { display: true, text: 'Suhu (°C)', color: '#10b981', font: { weight: '700', size: 11 } },
                        grid: { color: 'rgba(226, 232, 240, 0.5)' },
                        ticks: { color: '#64748b', stepSize: 2 }
                    },
                    yPercent: {
                        type: 'linear',
                        position: 'right',
                        min: 0,
                        max: 100,
                        title: { display: true, text: 'Persentase (%)', color: '#3b82f6', font: { weight: '700', size: 11 } },
                        grid: { drawOnChartArea: false },
                        ticks: { color: '#64748b', stepSize: 25 }
                    }
                }
            }
        };

        dashboardChart = new Chart(ctx, chartConfig);
    }

    window.switchChartPeriod = function (period) {
        if (!dashboardChart) return;
        activePeriod = period;

        document.querySelectorAll('.chart-tab-btn').forEach((btn) => {
            btn.classList.toggle('is-active', btn.getAttribute('data-period') === period);
        });

        const data = window.dashboardData || {};
        const ctx = dashboardChart.ctx;

        if (period === '24h') {
            const hourly = data.hourlyTelemetry || { labels: [], temperatures: [], soil_moistures: [], humidities: [], rain_chances: [] };

            const gradTemp = ctx.createLinearGradient(0, 0, 0, 320);
            gradTemp.addColorStop(0, 'rgba(16, 185, 129, 0.35)');
            gradTemp.addColorStop(1, 'rgba(16, 185, 129, 0.00)');

            const gradMoisture = ctx.createLinearGradient(0, 0, 0, 320);
            gradMoisture.addColorStop(0, 'rgba(59, 130, 246, 0.28)');
            gradMoisture.addColorStop(1, 'rgba(59, 130, 246, 0.00)');

            dashboardChart.data.labels = hourly.labels;
            dashboardChart.data.datasets = [
                {
                    label: 'Suhu Udara (°C)',
                    data: hourly.temperatures,
                    borderColor: '#10b981',
                    backgroundColor: gradTemp,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    yAxisID: 'yTemp',
                },
                {
                    label: 'Lengas Tanah (%)',
                    data: hourly.soil_moistures,
                    borderColor: '#3b82f6',
                    backgroundColor: gradMoisture,
                    borderWidth: 2.2,
                    fill: true,
                    tension: 0.35,
                    yAxisID: 'yPercent',
                },
                {
                    label: 'Kelembapan Udara (%)',
                    data: hourly.humidities,
                    borderColor: '#06b6d4',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    fill: false,
                    tension: 0.35,
                    yAxisID: 'yPercent',
                },
                {
                    label: 'Peluang Hujan (%)',
                    data: hourly.rain_chances,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.15)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.35,
                    yAxisID: 'yPercent',
                }
            ];
            dashboardChart.options.scales.yTemp.display = true;
            dashboardChart.options.scales.yPercent.display = true;
            dashboardChart.update();

            const insight = document.getElementById('chartInsightText');
            if (insight) insight.textContent = 'Grafik 24 jam menunjukkan fluktuasi suhu dan kelembaban harian di zona perakaran sawah.';
        } else if (period === '7d') {
            const forecast = data.forecastDays || [];
            const labels = forecast.map(f => f.day + ' (' + f.date + ')');
            const maxTemps = forecast.map(f => f.temp_max);
            const minTemps = forecast.map(f => f.temp_min);
            const rainPops = forecast.map(f => f.rain_pop);

            dashboardChart.data.labels = labels;
            dashboardChart.data.datasets = [
                {
                    label: 'Suhu Maksimum (°C)',
                    data: maxTemps,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 2.5,
                    fill: false,
                    tension: 0.3,
                    yAxisID: 'yTemp'
                },
                {
                    label: 'Suhu Minimum (°C)',
                    data: minTemps,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2.5,
                    fill: false,
                    tension: 0.3,
                    yAxisID: 'yTemp'
                },
                {
                    label: 'Peluang Hujan BMKG (%)',
                    data: rainPops,
                    borderColor: '#0284c7',
                    backgroundColor: 'rgba(2, 132, 199, 0.3)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'yPercent'
                }
            ];
            dashboardChart.options.scales.yTemp.display = true;
            dashboardChart.options.scales.yPercent.display = true;
            dashboardChart.update();

            const insight = document.getElementById('chartInsightText');
            if (insight) insight.textContent = 'Prakiraan BMKG 5 hari ke depan mengindikasikan stabilitas curah hujan di awal pekan.';
        } else if (period === 'monthly') {
            const trends = data.monthlyTrends || { labels: [], disease_reports: [], harvest_counts: [], marketplace_deals: [] };

            dashboardChart.data.labels = trends.labels;
            dashboardChart.data.datasets = [
                {
                    label: 'Laporan Penyakit Selesai',
                    data: trends.disease_reports,
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.25)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'yPercent'
                },
                {
                    label: 'Panen Tercatat (Ha)',
                    data: trends.harvest_counts,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.25)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'yPercent'
                },
                {
                    label: 'Kontrak Marketplace',
                    data: trends.marketplace_deals,
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.2)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'yPercent'
                }
            ];
            dashboardChart.options.scales.yTemp.display = false;
            dashboardChart.options.scales.yPercent.display = true;
            dashboardChart.options.scales.yPercent.title.text = 'Jumlah Aktivitas';
            dashboardChart.update();

            const insight = document.getElementById('chartInsightText');
            if (insight) insight.textContent = 'Tren 6 bulan menunjukkan peningkatan produktivitas panen dan transaksi hasil bumi.';
        }
    };

    // =========================================================================
    // 3. Embedded Leaflet Mini GIS Radar Map
    // =========================================================================
    function initMiniGisMap() {
        const mapContainer = document.getElementById('dashboard-mini-gis-map');
        if (!mapContainer || typeof L === 'undefined') return;

        const defaultLat = -7.2500;
        const defaultLng = 112.7500;

        miniGisMap = L.map('dashboard-mini-gis-map', {
            scrollWheelZoom: false,
            zoomControl: true,
            attributionControl: false
        }).setView([defaultLat, defaultLng], 12);

        streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(miniGisMap);

        satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19
        });

        renderMapPins();

        setTimeout(() => {
            if (miniGisMap) {
                miniGisMap.invalidateSize();
            }
        }, 200);
    }

    function renderMapPins() {
        if (!miniGisMap) return;

        // Clear existing markers & polygons
        mapMarkers.forEach(m => miniGisMap.removeLayer(m));
        mapPolygons.forEach(p => miniGisMap.removeLayer(p));
        mapMarkers = [];
        mapPolygons = [];

        const farms = (window.dashboardData && window.dashboardData.farmsForMap) || [];
        const bounds = [];

        farms.forEach(farm => {
            const lat = parseFloat(farm.latitude);
            const lng = parseFloat(farm.longitude);
            if (isNaN(lat) || isNaN(lng)) return;

            bounds.push([lat, lng]);

            // Polygon boundary if available
            if (farm.boundary_coordinates) {
                let points = [];
                if (typeof farm.boundary_coordinates === 'string') {
                    try { points = JSON.parse(farm.boundary_coordinates); } catch (e) { }
                } else if (Array.isArray(farm.boundary_coordinates)) {
                    points = farm.boundary_coordinates;
                }

                if (points && points.length >= 3) {
                    const latLngs = points.map(p => [parseFloat(p.lat), parseFloat(p.lng)]);
                    const color = farm.status === 'danger' ? '#ef4444' : (farm.status === 'warning' ? '#f59e0b' : '#10b981');
                    const poly = L.polygon(latLngs, {
                        color: color,
                        weight: 2,
                        fillColor: color,
                        fillOpacity: 0.35
                    }).addTo(miniGisMap);
                    mapPolygons.push(poly);
                }
            }

            // Pin styling based on status
            const statusColor = farm.status === 'danger' ? '#ef4444' : (farm.status === 'warning' ? '#f59e0b' : '#10b981');
            const pulseClass = farm.status === 'danger' ? 'gis-pin--pulse' : '';

            const customIcon = L.divIcon({
                className: 'custom-gis-pin-wrap',
                html: `
                    <div class="gis-pin-dot ${pulseClass}" style="background-color: ${statusColor};">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#ffffff" stroke-width="2.5">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                `,
                iconSize: [28, 28],
                iconAnchor: [14, 14],
                popupAnchor: [0, -14]
            });

            const marker = L.marker([lat, lng], { icon: customIcon }).addTo(miniGisMap);

            const popupContent = `
                <div class="gis-popup-card">
                    <div class="gis-popup-head">
                        <h4>${farm.name}</h4>
                        <span class="gis-popup-badge gis-popup-badge--${farm.status}">
                            ${farm.status === 'danger' ? 'Bahaya' : (farm.status === 'warning' ? 'Waspada' : 'Optimal')}
                        </span>
                    </div>
                    <p class="gis-popup-farmer">Petani: <strong>${farm.farmer_name}</strong> • ${farm.area_ha} Ha</p>
                    <div class="gis-popup-metrics">
                        <div>
                            <span>Suhu:</span>
                            <strong>${farm.temperature}°C</strong>
                        </div>
                        <div>
                            <span>Lengas:</span>
                            <strong>${farm.soil_moisture}%</strong>
                        </div>
                        <div>
                            <span>Kelembaban:</span>
                            <strong>${farm.humidity}%</strong>
                        </div>
                    </div>
                    <div class="gis-popup-footer">
                        <span>Kondisi: ${farm.condition}</span>
                        <a href="javascript:void(0)" onclick="selectFarmById(${farm.id})" class="gis-popup-btn">Pilih Lahan →</a>
                    </div>
                </div>
            `;

            marker.bindPopup(popupContent, { maxWidth: 260 });
            mapMarkers.push(marker);

            if (farm.is_selected) {
                setTimeout(() => {
                    marker.openPopup();
                }, 300);
            }
        });

        if (bounds.length > 0) {
            miniGisMap.fitBounds(bounds, { padding: [30, 30], maxZoom: 14 });
        }
    }

    window.toggleMapLayer = function (layerType) {
        if (!miniGisMap) return;
        activeMapLayer = layerType;

        document.querySelectorAll('.map-layer-btn').forEach((btn) => {
            btn.classList.toggle('is-active', btn.getAttribute('data-layer') === layerType);
        });

        if (layerType === 'satellite') {
            miniGisMap.removeLayer(streetLayer);
            satelliteLayer.addTo(miniGisMap);
        } else {
            miniGisMap.removeLayer(satelliteLayer);
            streetLayer.addTo(miniGisMap);
        }
    };

    window.focusFarmOnMap = function (farmId) {
        if (!miniGisMap || !window.dashboardData || !window.dashboardData.farmsForMap) return;
        const farm = window.dashboardData.farmsForMap.find(f => String(f.id) === String(farmId));
        if (farm && farm.latitude && farm.longitude) {
            miniGisMap.flyTo([farm.latitude, farm.longitude], 15, { duration: 1.2 });
            const marker = mapMarkers.find(m => {
                const pos = m.getLatLng();
                return Math.abs(pos.lat - farm.latitude) < 0.0001 && Math.abs(pos.lng - farm.longitude) < 0.0001;
            });
            if (marker) {
                setTimeout(() => marker.openPopup(), 1200);
            }
        }
    };

    // =========================================================================
    // 4. AJAX Live Refresh & Farm Filter Sync + Auto-Update Engine
    // =========================================================================
    const AUTO_SYNC_INTERVAL_SEC = 300; // 5 Menit
    let autoSyncSecondsLeft = AUTO_SYNC_INTERVAL_SEC;
    let autoSyncTimerId = null;
    let isAutoSyncPaused = false;

    function formatTimeLeft(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${secs < 10 ? '0' : ''}${secs}`;
    }

    function initAutoSync() {
        if (autoSyncTimerId) clearInterval(autoSyncTimerId);
        autoSyncSecondsLeft = AUTO_SYNC_INTERVAL_SEC;
        updateAutoSyncPill();

        autoSyncTimerId = setInterval(() => {
            if (isAutoSyncPaused) return;

            autoSyncSecondsLeft--;
            updateAutoSyncPill();

            if (autoSyncSecondsLeft <= 0) {
                autoSyncSecondsLeft = AUTO_SYNC_INTERVAL_SEC;
                const farmSelect = document.getElementById('farm_select');
                const farmId = farmSelect ? farmSelect.value : '';
                // Silent auto-refresh for real-time weather and soil updates every 5 minutes
                fetchDashboardData(farmId, true);
            }
        }, 1000);
    }

    function updateAutoSyncPill() {
        const countdownEl = document.getElementById('autoSyncCountdown');
        const pillBtn = document.getElementById('autoSyncToggleBtn');
        const iconEl = document.getElementById('autoSyncIcon');

        if (countdownEl) {
            countdownEl.textContent = isAutoSyncPaused ? 'Jeda' : formatTimeLeft(autoSyncSecondsLeft);
        }
        if (pillBtn) {
            pillBtn.classList.toggle('is-paused', isAutoSyncPaused);
        }
        if (iconEl) {
            iconEl.innerHTML = isAutoSyncPaused
                ? '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>'
                : '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>';
        }
    }

    window.toggleAutoSync = function () {
        isAutoSyncPaused = !isAutoSyncPaused;
        updateAutoSyncPill();
        showToast(isAutoSyncPaused ? 'Auto-update cuaca & tanah dijeda.' : 'Auto-update cuaca & tanah aktif (setiap 5 menit).', 'info');
    };

    // Debounce Utility
    function debounce(func, wait = 300) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    window.selectFarmById = function (farmId) {
        const select = document.getElementById('farm_select');
        if (select) {
            select.value = String(farmId);
            fetchDashboardData(farmId);
        }
    };

    let isFetchInFlight = false;

    window.fetchDashboardData = async function (farmId = '', isAutoSync = false, forceSync = false) {
        if (isFetchInFlight && !isAutoSync) return; // Prevent double-trigger
        isFetchInFlight = true;

        const syncBtn = document.getElementById('btnSyncDashboard');
        if (syncBtn) syncBtn.classList.add('is-loading');

        try {
            const url = new URL(window.location.origin + '/admin');
            if (farmId) url.searchParams.set('farm_id', farmId);
            if (forceSync) url.searchParams.set('force_sync', '1');

            const res = await fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (res.status === 429) {
                const errData = await res.json().catch(() => ({}));
                showToast(errData.message || 'Batas laju pembaruan tercapai. Harap tunggu beberapa detik.', 'warning');
                return;
            }

            if (!res.ok) throw new Error('Gagal mengambil data dashboard (Kode: ' + res.status + ')');
            const result = await res.json();
            const data = result.data || {};

            window.dashboardData = data;

            // Update UI components dynamically
            updateWeatherCards(data.liveWeather);
            updateGauges(data.liveWeather);
            updateDisasterThreats(data.disasterThreats, data.disasterSummary);

            if (dashboardChart) {
                window.switchChartPeriod(activePeriod);
            }

            renderMapPins();
            if (farmId && !isAutoSync) {
                window.focusFarmOnMap(farmId);
            }

            // Reset auto-sync countdown on manual refresh
            if (!isAutoSync) {
                autoSyncSecondsLeft = AUTO_SYNC_INTERVAL_SEC;
                updateAutoSyncPill();
                showToast('Data telemetri cuaca & tanah berhasil disinkronkan!', 'success');
            }
        } catch (err) {
            console.error(err);
            if (!isAutoSync) {
                showToast('Gagal sinkronisasi data: ' + err.message, 'error');
            }
        } finally {
            isFetchInFlight = false;
            if (syncBtn) syncBtn.classList.remove('is-loading');
        }
    };

    function updateWeatherCards(w) {
        if (!w) return;

        const tempEl = document.getElementById('kpi-weather-temp');
        if (tempEl) animateCountUp(tempEl, w.temp, 600, '°C', 1);

        const humEl = document.getElementById('kpi-weather-humidity');
        if (humEl) animateCountUp(humEl, w.humidity, 600, '%', 0);

        const rainEl = document.getElementById('kpi-weather-rain');
        if (rainEl) animateCountUp(rainEl, w.rain_chance, 600, '%', 0);

        const soilEl = document.getElementById('kpi-weather-soil');
        if (soilEl) animateCountUp(soilEl, w.soil_moisture, 600, '%', 0);

        const soilTempEl = document.getElementById('kpi-soil-temp-val');
        if (soilTempEl && w.soil_temp !== undefined) {
            soilTempEl.textContent = `${Number(w.soil_temp).toFixed(1).replace('.', ',')}°C`;
        }

        const condEl = document.getElementById('kpi-weather-condition');
        if (condEl) condEl.textContent = `${w.condition_title || 'Berawan'} • Terasa ${w.feels_like}°C`;

        const locationEl = document.getElementById('kpi-weather-location');
        if (locationEl) locationEl.textContent = `Angin ${w.wind_speed} km/j • ${w.location_name}`;
    }

    function updateGauges(w) {
        if (!w) return;
        const soilMoisture = w.soil_moisture || 68;
        const rainChance = w.rain_chance || 40;

        const soilRing = document.getElementById('gaugeSoilProgress');
        if (soilRing) {
            const circumference = 2 * Math.PI * 36; // r=36
            const offset = circumference - (soilMoisture / 100) * circumference;
            soilRing.style.strokeDashoffset = offset;
        }

        const rainRing = document.getElementById('gaugeRainProgress');
        if (rainRing) {
            const circumference = 2 * Math.PI * 36;
            const offset = circumference - (rainChance / 100) * circumference;
            rainRing.style.strokeDashoffset = offset;
        }
    }

    function updateDisasterThreats(threats, summary) {
        if (summary) {
            const badge = document.getElementById('disasterSystemBadge');
            if (badge) {
                badge.className = `dashboard-threat-summary-badge dashboard-threat-summary-badge--${summary.system_status}`;
                badge.textContent = summary.system_status === 'danger' ? 'Status Bahaya' : (summary.system_status === 'warning' ? 'Status Siaga' : 'Status Aman');
            }
        }
    }

    // =========================================================================
    // 5. Quick Actions & Modal Management
    // =========================================================================
    window.openQuickBroadcastModal = function (presetType = '') {
        const modal = document.getElementById('modalQuickBroadcast');
        if (!modal) return;
        modal.classList.add('is-active');

        if (presetType) {
            window.applyBroadcastPreset(presetType);
        }
    };

    window.closeQuickBroadcastModal = function () {
        const modal = document.getElementById('modalQuickBroadcast');
        if (modal) modal.classList.remove('is-active');
    };

    window.applyBroadcastPreset = function (presetKey) {
        const titleInput = document.getElementById('qb_title');
        const typeSelect = document.getElementById('qb_type');
        const msgInput = document.getElementById('qb_message');

        if (!titleInput || !typeSelect || !msgInput) return;

        const presets = {
            flood: {
                title: 'Peringatan Dini: Potensi Limpasan Air & Banjir Lahan',
                type: 'warning',
                msg: 'Yth. Petani P.A.D.I., sensor mendeteksi peningkatan curah hujan ekstrem 85 mm/hari. Harap periksa dan buka pintu pembuangan sekunder petak sawah Anda segera serta tunda pemupukan cair.'
            },
            pest: {
                title: 'Waspada Serangan Hama Wereng Batang Coklat & Blas',
                type: 'warning',
                msg: 'Kelembapan mikroklimat lahan mencapai >80% yang memicu spora jamur dan wereng. Lakukan pengeringan berkala dan semprotkan agen hayati trichoderma pada pangkal rumpun padi.'
            },
            storm: {
                title: 'Peringatan Angin Kencang di Hamparan Sawah Terbuka',
                type: 'warning',
                msg: 'Kecepatan hembusan angin diprediksi mencapai 20 km/jam pada sore hari. Pasang pancang bambu penahan pada rumpun padi fase bunting/masak susu guna mencegah roboh.'
            },
            fertilizer: {
                title: 'Rekomendasi Waktu Pemupukan Berimbang',
                type: 'info',
                msg: 'Kondisi cuaca 24 jam ke depan cerah berawan dengan lengas tanah 65%. Waktu ideal untuk aplikasi pupuk NPK susulan ke-2.'
            }
        };

        const p = presets[presetKey];
        if (p) {
            titleInput.value = p.title;
            typeSelect.value = p.type;
            msgInput.value = p.msg;
        }
    };

    window.openThreatDetailModal = function (threatId) {
        const modal = document.getElementById('modalThreatDetail');
        if (!modal || !window.dashboardData || !window.dashboardData.disasterThreats) return;

        const threat = window.dashboardData.disasterThreats.find(t => t.id === threatId);
        if (!threat) return;

        document.getElementById('td_category').textContent = threat.category_label;
        document.getElementById('td_severity').textContent = threat.severity_label;
        document.getElementById('td_severity').className = `modal-threat-badge modal-threat-badge--${threat.severity}`;
        document.getElementById('td_title').textContent = threat.title;
        document.getElementById('td_subtitle').textContent = threat.subtitle;
        document.getElementById('td_timeframe').textContent = threat.timeframe;
        document.getElementById('td_impact').textContent = threat.impact_area;
        document.getElementById('td_recom').textContent = threat.recommendation;

        const metricsContainer = document.getElementById('td_metrics_grid');
        if (metricsContainer) {
            metricsContainer.innerHTML = Object.entries(threat.metrics || {}).map(([k, v]) => `
                <div class="td-metric-box">
                    <span>${k}</span>
                    <strong>${v}</strong>
                </div>
            `).join('');
        }

        const broadcastBtn = document.getElementById('td_btn_broadcast');
        if (broadcastBtn) {
            broadcastBtn.onclick = function () {
                window.closeThreatDetailModal();
                window.openQuickBroadcastModal(threat.type === 'flood' ? 'flood' : (threat.type === 'storm' ? 'storm' : 'pest'));
            };
        }

        modal.classList.add('is-active');
    };

    window.closeThreatDetailModal = function () {
        const modal = document.getElementById('modalThreatDetail');
        if (modal) modal.classList.remove('is-active');
    };

    window.openQuickReportModal = function () {
        const modal = document.getElementById('modalQuickReport');
        if (modal) modal.classList.add('is-active');
    };

    window.closeQuickReportModal = function () {
        const modal = document.getElementById('modalQuickReport');
        if (modal) modal.classList.remove('is-active');
    };

    
    
    // =========================================================================
    // 6. Live Search & Filter for Recent Activity
    // =========================================================================
    function initActivityFilter() {
        const searchInput = document.getElementById('activitySearchInput');
        const filterTabs = document.querySelectorAll('.activity-filter-tab');
        const items = document.querySelectorAll('.dashboard-activity-item');

        let currentFilter = 'all';
        let currentSearch = '';

        function filterList() {
            let visibleCount = 0;

            items.forEach((item) => {
                const module = (item.getAttribute('data-module') || '').toLowerCase();
                const text = item.textContent.toLowerCase();

                const matchesFilter = currentFilter === 'all' || module.includes(currentFilter);
                const matchesSearch = !currentSearch || text.includes(currentSearch);

                if (matchesFilter && matchesSearch) {
                    item.style.display = 'flex';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            const emptyNotice = document.getElementById('activitySearchEmpty');
            if (emptyNotice) {
                emptyNotice.style.display = visibleCount === 0 ? 'flex' : 'none';
            }
        }

        if (searchInput) {
            const debouncedSearch = debounce((val) => {
                currentSearch = val;
                filterList();
            }, 200);

            searchInput.addEventListener('input', (e) => {
                debouncedSearch(e.target.value.toLowerCase().trim());
            });
        }

        filterTabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                filterTabs.forEach(t => t.classList.remove('is-active'));
                tab.classList.add('is-active');
                currentFilter = tab.getAttribute('data-filter') || 'all';
                filterList();
            });
        });
    }

    // =========================================================================
    // 7. WebSocket / Broadcast Real-Time Push Listener
    // =========================================================================
    function initWebSockets() {
        if (typeof window.Echo === 'undefined') return;

        try {
            // Channel 1: Telemetri Cuaca & Sensor Tanah Real-Time
            window.Echo.channel('agri-telemetry')
                .listen('.telemetry.updated', (data) => {
                    if (data && data.payload) {
                        const currentFarmSelect = document.getElementById('farm_select');
                        const selectedId = currentFarmSelect ? currentFarmSelect.value : '';
                        if (!selectedId || String(selectedId) === String(data.farm_id)) {
                            const liveData = {
                                temp: data.payload.main?.temp ?? 25,
                                humidity: data.payload.main?.humidity ?? 75,
                                rain_chance: (data.payload.main?.humidity ?? 75) >= 80 ? 85 : 40,
                                soil_moisture: data.payload.soil?.moisture_percentage ?? 68,
                                soil_temp: data.payload.soil?.soil_temp_celsius ?? 26,
                                condition_title: data.payload.weather?.[0]?.description ?? 'Cerah Berawan',
                                feels_like: data.payload.main?.feels_like ?? 26,
                                wind_speed: data.payload.wind?.speed ?? 10,
                                location_name: 'Update Sensor Real-Time'
                            };
                            updateWeatherCards(liveData);
                            updateGauges(liveData);
                            showToast('Telemetri diperbarui seketika via WebSocket.', 'info');
                        }
                    }
                });

            // Channel 2: Peringatan Dini & Bencana
            window.Echo.channel('disaster-alerts')
                .listen('.disaster.alert', (data) => {
                    if (data && data.alert) {
                        showToast('Peringatan Dini: ' + (data.alert.title || 'Ada imbauan siaga baru.'), 'warning');
                    }
                });
        } catch (err) {
            console.warn('[WebSocket] Init failed:', err);
        }
    }

    // =========================================================================
    // 8. Toast Notifications
    // =========================================================================
    function showToast(message, type = 'info') {
        let container = document.getElementById('dashboardToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'dashboardToastContainer';
            container.className = 'dashboard-toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `dashboard-toast dashboard-toast--${type}`;
        toast.innerHTML = `
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                ${type === 'success'
                ? '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/>'
                : '<circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>'}
            </svg>
            <span>${message}</span>
        `;

        container.appendChild(toast);
        setTimeout(() => {
            toast.classList.add('is-leaving');
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    // =========================================================================
    // 9. Dom Content Loaded Bootstrapper
    // =========================================================================
    document.addEventListener('DOMContentLoaded', function () {
        initCountUp();
        initDashboardChart();
        initMiniGisMap();
        initActivityFilter();
        initAutoSync();
        initWebSockets();

        // Bind AJAX Farm Select
        const farmSelect = document.getElementById('farm_select');
        if (farmSelect) {
            farmSelect.addEventListener('change', function (e) {
                e.preventDefault();
                fetchDashboardData(this.value);
            });
        }

        // Bind Sync Button (forces fresh API snapshot)
        const syncBtn = document.getElementById('btnSyncDashboard');
        if (syncBtn) {
            syncBtn.addEventListener('click', function (e) {
                e.preventDefault();
                const currentFarm = farmSelect ? farmSelect.value : '';
                fetchDashboardData(currentFarm, false, true);
            });
        }

        // Close modals with Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                window.closeQuickBroadcastModal();
                window.closeThreatDetailModal();
                window.closeQuickReportModal();
            }
        });
    });

})();
