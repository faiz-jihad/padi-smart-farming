@extends('layouts.admin')

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    <link rel="stylesheet" href="{{ asset('css/admin/weather-map.css') }}">

    <div class="admin-page">
        <div class="admin-page__header">
            <div>
                <p class="admin-page__eyebrow">Admin</p>
                <h1 class="admin-page__title">Peta Cuaca Indonesia</h1>
                <p class="admin-page__description">Visualisasi real-time data cuaca dari seluruh lahan pertanian P.A.D.I di
                    Indonesia.</p>
            </div>
            <div class="admin-page__actions">
                <a href="{{ route('admin.weather.index') }}" class="admin-btn admin-btn--secondary">Kembali ke Dashboard</a>
            </div>
        </div>

        @if (session('status'))
            <div class="admin-alert admin-alert--success">
                ✓ {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="admin-alert admin-alert--error">
                ✕ {{ session('error') }}
            </div>
        @endif

        <section class="admin-card" style="padding: 0; overflow: hidden;">
            <div id="weatherMap" class="weather-map"></div>
        </section>

        <section class="admin-card" style="margin-top: 2rem;">
            <div class="admin-card__header">
                <div class="admin-card__title">
                    <span>Legenda</span>
                    <h2>Indikator Cuaca</h2>
                </div>
            </div>
            <div class="weather-legend">
                <div class="legend-item">
                    <div class="legend-icon" style="background-color: #3b82f6;">ℹ️</div>
                    <span>Lahan dengan data cuaca</span>
                </div>
                <div class="legend-item">
                    <div class="legend-icon" style="background-color: #fbbf24;">⚠️</div>
                    <span>Data sedang dimuat / Tidak ada data</span>
                </div>
                <div class="legend-item">
                    <div class="legend-icon" style="background-color: #10b981;">✓</div>
                    <span>Data cuaca terbaru</span>
                </div>
                <div class="legend-item">
                    <div class="legend-icon" style="background-color: #ef4444;">✕</div>
                    <span>Data kadaluarsa</span>
                </div>
            </div>
        </section>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi peta dengan pusat di Indonesia
            const map = L.map('weatherMap').setView([-2.5489, 118.0149], 5);

            // Tambahkan tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19,
                tileSize: 256
            }).addTo(map);

            // Data lahan dengan cuaca
            const farmsData = @json($farms);
            const weatherMarkers = [];

            // Custom icons untuk berbagai kondisi
            const createWeatherIcon = (condition) => {
                let color = '#3b82f6'; // default biru
                let icon = 'ℹ️';

                if (condition === 'expired') {
                    color = '#ef4444'; // merah untuk kadaluarsa
                    icon = '✕';
                } else if (condition === 'fresh') {
                    color = '#10b981'; // hijau untuk baru
                    icon = '✓';
                } else if (condition === 'loading') {
                    color = '#fbbf24'; // kuning untuk loading
                    icon = '⚠️';
                }

                return L.divIcon({
                    html: `<div class="weather-marker" style="background-color: ${color};">${icon}</div>`,
                    className: 'weather-marker-container',
                    iconSize: [40, 40],
                    iconAnchor: [20, 20],
                    popupAnchor: [0, -20]
                });
            };

            // Tambahkan marker untuk setiap lahan
            farmsData.data.forEach(farm => {
                if (farm.latitude && farm.longitude) {
                    const latestWeather = farm.weather_snapshots && farm.weather_snapshots.length > 0 ?
                        farm.weather_snapshots[0] :
                        null;

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
                    <div style="font-size: 0.875rem;">
                        ${icon ? `<img src="https://openweathermap.org/img/wn/${icon}@2x.png" alt="${description}" style="width: 50px; height: 50px;">` : ''}
                        <p><strong>${description}</strong></p>
                        <table style="font-size: 0.8rem; border-collapse: collapse;">
                            <tr><td style="padding: 3px 5px;">Suhu:</td><td style="padding: 3px 5px;"><strong>${temp}°C</strong></td></tr>
                            <tr><td style="padding: 3px 5px;">Kelembaban:</td><td style="padding: 3px 5px;">${humidity}%</td></tr>
                            <tr><td style="padding: 3px 5px;">Angin:</td><td style="padding: 3px 5px;">${windSpeed} m/s</td></tr>
                            <tr><td style="padding: 3px 5px;">Update:</td><td style="padding: 3px 5px; font-size: 0.7rem;">${observedAt}</td></tr>
                        </table>
                    </div>
                `;
                    }

                    const marker = L.marker([farm.latitude, farm.longitude], {
                        icon: createWeatherIcon(condition)
                    }).addTo(map);

                    const popupContent = `
                <div class="weather-popup">
                    <h3>${farm.name}</h3>
                    <p><small>${farm.farmer?.name || '-'}</small></p>
                    <p><strong>Lokasi:</strong> ${farm.latitude}, ${farm.longitude}</p>
                    <hr style="margin: 8px 0; border: none; border-top: 1px solid #ddd;">
                    <div class="weather-info">
                        ${weatherHtml}
                    </div>
                    <hr style="margin: 8px 0; border: none; border-top: 1px solid #ddd;">
                    <a href="/admin/weather/history?farm_id=${farm.id}" class="weather-popup-link">Lihat Riwayat →</a>
                </div>
            `;

                    marker.bindPopup(popupContent, {
                        maxWidth: 300,
                        className: 'weather-popup-wrapper'
                    });

                    weatherMarkers.push(marker);
                }
            });

            // Tambahkan kontrol untuk zoom ke semua marker
            if (weatherMarkers.length > 0) {
                const group = new L.featureGroup(weatherMarkers);
                map.fitBounds(group.getBounds().pad(0.1));
            }

            // Tambahkan kontrol skala
            L.control.scale({
                position: 'bottomright',
                metric: true,
                imperial: false
            }).addTo(map);

            // Refresh data setiap 5 menit
            setInterval(function() {
                location.reload();
            }, 5 * 60 * 1000);
        });
    </script>

    <style>
        .admin-alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            font-weight: 500;
        }

        .admin-alert--success {
            background-color: #dcfce7;
            color: #166534;
            border-left: 4px solid #16a34a;
        }

        .admin-alert--error {
            background-color: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #dc2626;
        }
    </style>
@endsection
