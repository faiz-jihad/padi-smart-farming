@extends('layouts.admin')

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    <link rel="stylesheet" href="{{ asset('css/admin/weather-map.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/operational.css') }}">

    <div class="admin-page">
        <div class="admin-page__header">
            <div>
                <p class="admin-page__eyebrow">Admin — Geospatial Console</p>
                <h1 class="admin-page__title">Peta Cuaca & Geolocation Tanah</h1>
                <p class="admin-page__description">Visualisasi data cuaca real-time dan analisis geolocation kelembaban/suhu tanah dari AgroMonitoring untuk seluruh wilayah Indonesia.</p>
            </div>
            <div class="admin-page__actions">
                <a href="{{ route('admin.soil.index') }}" class="admin-btn admin-btn--secondary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 2v7.5L4.5 18a2 2 0 0 0 1.7 3h11.6a2 2 0 0 0 1.7-3L14 9.5V2"/><path d="M8.5 2h7"/></svg> Deteksi Tanah
                </a>
                <a href="{{ route('admin.weather.index') }}" class="admin-btn admin-btn--secondary">Kembali ke Dashboard</a>
            </div>
        </div>

        @if (session('status'))
            <div class="admin-alert admin-alert--success">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="admin-alert admin-alert--error">
                {{ session('error') }}
            </div>
        @endif

        <!-- Quick Region Selector & Instructions Bar -->
        <section class="admin-card" style="padding: 1rem 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                    <span style="font-size: 0.85rem; font-weight: 700; color: #334155; display: inline-flex; align-items: center; gap: 4px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/><circle cx="12" cy="10" r="3"/></svg> Lintas Sentra Padi:
                    </span>
                    <button class="admin-btn admin-btn--secondary admin-btn--sm region-btn" data-lat="-6.3031" data-lng="107.3009" data-name="Karawang, Jawa Barat">Karawang</button>
                    <button class="admin-btn admin-btn--secondary admin-btn--sm region-btn" data-lat="-6.4064" data-lng="108.2827" data-name="Indramayu, Jawa Barat">Indramayu</button>
                    <button class="admin-btn admin-btn--secondary admin-btn--sm region-btn" data-lat="-6.5716" data-lng="107.7587" data-name="Subang, Jawa Barat">Subang</button>
                    <button class="admin-btn admin-btn--secondary admin-btn--sm region-btn" data-lat="-7.4042" data-lng="111.4464" data-name="Ngawi, Jawa Timur">Ngawi</button>
                    <button class="admin-btn admin-btn--secondary admin-btn--sm region-btn" data-lat="-3.8996" data-lng="119.8044" data-name="Sidrap, Sulawesi Selatan">Sidrap</button>
                    <button class="admin-btn admin-btn--secondary admin-btn--sm region-btn" data-lat="-8.5412" data-lng="115.1238" data-name="Tabanan, Bali">Tabanan</button>
                </div>
                <div style="font-size: 0.85rem; color: #166534; font-weight: 600; background: #dcfce7; padding: 0.4rem 0.8rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg> Klik di manapun pada peta untuk inspeksi cuaca &amp; data tanah AgroMonitoring
                </div>
            </div>
        </section>

        <!-- Map Container -->
        <section class="admin-card" style="padding: 0; overflow: hidden; position: relative;">
            <div id="weatherMap" class="weather-map" style="height: 580px; width: 100%;"></div>
        </section>

        <!-- Legend Card -->
        <section class="admin-card" style="margin-top: 1rem;">
            <div class="admin-card__header">
                <div class="admin-card__title">
                    <span>Legenda Peta &amp; Geolocation</span>
                    <h2>Indikator Status Lahan &amp; Sensor AgroMonitoring</h2>
                </div>
            </div>
            <div class="weather-legend" style="display: flex; gap: 1.5rem; flex-wrap: wrap; padding: 1rem 1.25rem;">
                <div class="legend-item" style="display: flex; align-items: center; gap: 0.5rem;">
                    <div class="legend-icon" style="background-color: #10b981; color: white; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <span>Lahan Terdaftar (Data Terkini)</span>
                </div>
                <div class="legend-item" style="display: flex; align-items: center; gap: 0.5rem;">
                    <div class="legend-icon" style="background-color: #ef4444; color: white; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </div>
                    <span>Lahan Terdaftar (Data Kadaluarsa)</span>
                </div>
                <div class="legend-item" style="display: flex; align-items: center; gap: 0.5rem;">
                    <div class="legend-icon" style="background-color: #2563eb; color: white; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <span>Titik Lokasi Pilihan / Inspeksi Bebas</span>
                </div>
            </div>
        </section>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const map = L.map('weatherMap').setView([-2.5489, 118.0149], 5);

            const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(map);

            const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri'
            });

            L.control.layers({
                "Peta Jalan": osmLayer,
                "Satelit Agronomi": satelliteLayer
            }, null, { position: 'topright' }).addTo(map);

            const farmsData = @json($farms);
            const weatherMarkers = [];

            const createWeatherIcon = (condition) => {
                let color = '#3b82f6';
                let svgInner = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>';

                if (condition === 'expired') {
                    color = '#ef4444';
                    svgInner = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
                } else if (condition === 'fresh') {
                    color = '#10b981';
                    svgInner = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>';
                } else if (condition === 'loading') {
                    color = '#fbbf24';
                    svgInner = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
                }

                return L.divIcon({
                    html: `<div class="weather-marker" style="background-color: ${color}; color: white; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.25);">${svgInner}</div>`,
                    className: 'weather-marker-container',
                    iconSize: [34, 34],
                    iconAnchor: [17, 17],
                    popupAnchor: [0, -17]
                });
            };

            farmsData.data.forEach(farm => {
                if (farm.latitude && farm.longitude) {
                    const latestWeather = farm.weather_snapshots && farm.weather_snapshots.length > 0 ? farm.weather_snapshots[0] : null;
                    let condition = 'loading';
                    let weatherHtml = '<em>Data tidak tersedia</em>';

                    if (latestWeather) {
                        const expiresAt = new Date(latestWeather.expires_at);
                        const now = new Date();
                        condition = expiresAt > now ? 'fresh' : 'expired';

                        const payload = latestWeather.payload_json || {};
                        const temp = payload.main?.temp || 'N/A';
                        const humidity = payload.main?.humidity || 'N/A';
                        const windSpeed = payload.wind?.speed || 'N/A';
                        const description = payload.weather?.[0]?.description || 'N/A';
                        const icon = payload.weather?.[0]?.icon || '';
                        const observedAt = new Date(latestWeather.observed_at).toLocaleString('id-ID');

                        weatherHtml = `
                            <div style="font-size: 0.85rem;">
                                ${icon ? `<img src="https://openweathermap.org/img/wn/${icon}@2x.png" alt="${description}" style="width: 44px; height: 44px; display: block; margin: 0 auto;">` : ''}
                                <p style="text-align: center; margin: 4px 0;"><strong>${description}</strong></p>
                                <table style="font-size: 0.8rem; border-collapse: collapse; width: 100%; margin-top: 6px;">
                                    <tr><td style="padding: 2px 4px;">Suhu:</td><td style="padding: 2px 4px; font-weight: 700;">${temp}°C</td></tr>
                                    <tr><td style="padding: 2px 4px;">Kelembaban:</td><td style="padding: 2px 4px;">${humidity}%</td></tr>
                                    <tr><td style="padding: 2px 4px;">Angin:</td><td style="padding: 2px 4px;">${windSpeed} m/s</td></tr>
                                    <tr><td style="padding: 2px 4px;">Update:</td><td style="padding: 2px 4px; font-size: 0.7rem; color: #666;">${observedAt}</td></tr>
                                </table>
                            </div>
                        `;
                    }

                    const marker = L.marker([farm.latitude, farm.longitude], {
                        icon: createWeatherIcon(condition)
                    }).addTo(map);

                    const popupContent = `
                        <div class="weather-popup" style="font-family: inherit; min-width: 220px;">
                            <h3 style="margin: 0 0 4px 0; font-size: 1rem; color: #166534;">${farm.name}</h3>
                            <p style="margin: 0 0 6px 0; font-size: 0.8rem; color: #64748b;">Petani: <strong>${farm.farmer?.name || '-'}</strong></p>
                            <p style="margin: 0 0 8px 0; font-size: 0.75rem; color: #94a3b8;">Koordinat: ${farm.latitude}, ${farm.longitude}</p>
                            <hr style="margin: 6px 0; border: none; border-top: 1px solid #e2e8f0;">
                            ${weatherHtml}
                            <hr style="margin: 8px 0; border: none; border-top: 1px solid #e2e8f0;">
                            <div style="display: flex; gap: 6px; flex-direction: column;">
                                <a href="/admin/weather/history?farm_id=${farm.id}" style="color: #166534; font-weight: 700; font-size: 0.8rem; text-decoration: none;">Riwayat Cuaca Lahan &rarr;</a>
                                <a href="/admin/soil/create?farm_id=${farm.id}" style="color: #2563eb; font-weight: 700; font-size: 0.8rem; text-decoration: none;">+ Uji Sampel Tanah Lahan Ini</a>
                            </div>
                        </div>
                    `;

                    marker.bindPopup(popupContent, { maxWidth: 280 });
                    weatherMarkers.push(marker);
                }
            });

            if (weatherMarkers.length > 0) {
                const group = new L.featureGroup(weatherMarkers);
                map.fitBounds(group.getBounds().pad(0.1));
            }

            let activeInspectMarker = null;

            map.on('click', function(e) {
                const lat = e.latlng.lat.toFixed(4);
                const lng = e.latlng.lng.toFixed(4);

                if (activeInspectMarker) {
                    map.removeLayer(activeInspectMarker);
                }

                const customPin = L.divIcon({
                    html: `<div style="background-color: #2563eb; color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 4px 12px rgba(37,99,235,0.4);"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/><circle cx="12" cy="10" r="3"/></svg></div>`,
                    className: 'inspect-pin',
                    iconSize: [36, 36],
                    iconAnchor: [18, 18],
                    popupAnchor: [0, -18]
                });

                activeInspectMarker = L.marker([lat, lng], { icon: customPin }).addTo(map);

                const loadingPopup = `
                    <div style="padding: 10px; text-align: center; min-width: 240px;">
                        <p style="margin: 0; font-weight: 600; color: #1e293b;">Menarik Data Cuaca &amp; Soil Geolocation...</p>
                        <small style="color: #64748b;">Koordinat: ${lat}, ${lng}</small>
                    </div>
                `;

                activeInspectMarker.bindPopup(loadingPopup, { maxWidth: 300 }).openPopup();

                fetch(`/admin/weather/inspect?latitude=${lat}&longitude=${lng}`)
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            const w = res.weather || {};
                            const s = res.soil || {};
                            const desc = w.description || 'Tidak ada deskripsi';
                            const icon = w.weather ? `https://openweathermap.org/img/wn/${res.weather_raw?.weather?.[0]?.icon || '01d'}@2x.png` : '';

                            const popupHtml = `
                                <div style="font-family: inherit; min-width: 250px; padding: 4px;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                                        <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #2563eb; background: #eff6ff; padding: 2px 6px; border-radius: 4px;">Pilihan Titik Lokasi</span>
                                        <span style="font-size: 0.7rem; color: #64748b;">${res.provider}</span>
                                    </div>
                                    <h4 style="margin: 0 0 4px 0; font-size: 0.95rem; color: #0f172a;">Koordinat: ${lat}, ${lng}</h4>

                                    <div style="background: #f8fafc; padding: 8px; border-radius: 6px; border: 1px solid #e2e8f0; margin-top: 8px;">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            ${icon ? `<img src="${icon}" style="width: 40px; height: 40px;">` : ''}
                                            <div>
                                                <strong style="font-size: 0.9rem; display: block; color: #0f172a;">${w.temperature ?? '-'}°C — ${ucfirst(desc)}</strong>
                                                <small style="color: #64748b;">Kelembaban Udara: ${w.humidity ?? '-'}% | Angin: ${w.wind_speed ?? '-'} m/s</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div style="background: #f0fdf4; padding: 8px; border-radius: 6px; border: 1px solid #bbf7d0; margin-top: 8px;">
                                        <span style="font-size: 0.75rem; font-weight: 700; color: #166534; display: flex; align-items: center; gap: 4px; margin-bottom: 4px;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg> Geolocation Kualitas Tanah (AgroMonitoring):
                                        </span>
                                        <table style="font-size: 0.8rem; width: 100%; border-collapse: collapse;">
                                            <tr><td>Suhu Tanah (10cm):</td><td style="font-weight: 700; color: #15803d; text-align: right;">${s.soil_temp_celsius ?? '-'} °C</td></tr>
                                            <tr><td>Kelembaban Tanah:</td><td style="font-weight: 700; color: #0284c7; text-align: right;">${s.moisture_percentage ?? '-'} %</td></tr>
                                            <tr><td>Estimasi pH Tanah:</td><td style="font-weight: 700; color: #16a34a; text-align: right;">6.5 (Ideal)</td></tr>
                                        </table>
                                    </div>

                                    <div style="margin-top: 10px; text-align: center;">
                                        <a href="/admin/soil/create?latitude=${lat}&longitude=${lng}&moisture_percentage=${s.moisture_percentage || 50}&soil_temp_celsius=${s.soil_temp_celsius || ''}" class="admin-btn admin-btn--sm" style="width: 100%; box-sizing: border-box;">
                                            + Buat Uji Sampel Tanah Di Sini
                                        </a>
                                    </div>
                                </div>
                            `;
                            activeInspectMarker.bindPopup(popupHtml, { maxWidth: 300 }).openPopup();
                        }
                    })
                    .catch(err => {
                        activeInspectMarker.bindPopup('<p style="color: red; font-size: 0.85rem;">Gagal mengambil data lokasi ini.</p>').openPopup();
                    });
            });

            document.querySelectorAll('.region-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const lat = parseFloat(this.getAttribute('data-lat'));
                    const lng = parseFloat(this.getAttribute('data-lng'));

                    map.flyTo([lat, lng], 11, { duration: 1.5 });

                    setTimeout(() => {
                        map.fire('click', { latlng: L.latLng(lat, lng) });
                    }, 1200);
                });
            });

            L.control.scale({ position: 'bottomright', metric: true, imperial: false }).addTo(map);

            function ucfirst(string) {
                return string ? string.charAt(0).toUpperCase() + string.slice(1) : '';
            }
        });
    </script>
@endsection
