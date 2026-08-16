@extends('layouts.admin')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/admin/operational.css') }}">

    <div class="admin-page">
        <div class="admin-page__header">
            <div>
                <p class="admin-page__eyebrow">Admin</p>
                <h1 class="admin-page__title">Pengaturan Cuaca</h1>
                <p class="admin-page__description">Kelola konfigurasi API cuaca dan pengaturan integrasi penyedia layanan.
                </p>
            </div>
            <div class="admin-page__actions">
                <a href="{{ route('admin.weather.index') }}" class="admin-btn admin-btn--secondary">Kembali</a>
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

        @if ($errors->any())
            <div class="admin-alert admin-alert--error">
                <strong>Validasi Gagal:</strong>
                <ul style="margin-top: 0.5rem; padding-left: 1.5rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="admin-card">
            <div class="admin-card__header">
                <div class="admin-card__title">
                    <span>Konfigurasi</span>
                    <h2>Pengaturan API Cuaca</h2>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.weather.settings.update') }}" class="admin-form">
                @csrf
                @method('PATCH')

                <div class="admin-form-group">
                    <label for="weather_provider">Provider</label>
                    <select name="weather_provider" id="weather_provider" class="admin-input" required>
                        <option value="openweathermap" @if ($provider === 'openweathermap') selected @endif>
                            OpenWeatherMap (Cuaca Umum)
                        </option>
                        <option value="agromonitoring" @if ($provider === 'agromonitoring') selected @endif>
                            AgroMonitoring (Cuaca Pertanian & Sensor Tanah)
                        </option>
                    </select>
                    <small class="admin-form-hint">Penyedia layanan data cuaca dan analisis tanah pertanian yang digunakan.</small>
                </div>

                <div class="admin-form-group">
                    <label for="weather_api_key">API Key</label>
                    <input type="password" name="weather_api_key" id="weather_api_key" class="admin-input"
                        placeholder="Masukkan API key baru (kosongkan untuk tetap gunakan yang ada)">
                    <small class="admin-form-hint">
                        Status saat ini: <strong>{{ $weatherApiKey }}</strong><br>
                        Dapatkan API key dari <a href="https://openweathermap.org/api" target="_blank" rel="noopener">OpenWeatherMap</a> atau <a href="https://home.agromonitoring.com/users/api-keys" target="_blank" rel="noopener">AgroMonitoring API Keys</a>
                    </small>
                </div>

                <div class="admin-form-actions">
                    <button type="submit" class="admin-btn">Simpan Pengaturan</button>
                </div>
            </form>
        </section>

        <section class="admin-card">
            <div class="admin-card__header">
                <div class="admin-card__title">
                    <span>Diagnostik</span>
                    <h2>Tes Koneksi API</h2>
                </div>
            </div>
            <div class="admin-form-group">
                <p class="admin-form-hint">
                    Klik tombol di bawah untuk menguji koneksi API cuaca. Sistem akan mencoba mengambil data dari lokasi
                    test (Jakarta).
                </p>
                <form method="POST" action="{{ route('admin.weather.test-connection') }}" style="margin-top: 1rem;">
                    @csrf
                    <button type="submit" class="admin-btn">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/></svg> Tes Koneksi API
                    </button>
                </form>
            </div>
        </section>

        <section class="admin-card">
            <div class="admin-card__header">
                <div class="admin-card__title">
                    <span>Pemeliharaan</span>
                    <h2>Cache & Penyimpanan</h2>
                </div>
            </div>
            <div class="admin-form-group">
                <p class="admin-form-hint">
                    Bersihkan cache cuaca untuk memaksa pengambilan data baru dari API pada request berikutnya.
                    Gunakan ini jika data terlihat usang atau tidak akurat.
                </p>
                <form method="POST" action="{{ route('admin.weather.clear-cache') }}" style="margin-top: 1rem;">
                    @csrf
                    <button type="submit" class="admin-btn admin-btn--secondary"
                        onclick="return confirm('Apakah Anda yakin ingin menghapus semua cache cuaca?')">
                        🗑️ Bersihkan Cache
                    </button>
                </form>
            </div>
        </section>

        <section class="admin-card">
            <div class="admin-card__header">
                <div class="admin-card__title">
                    <span>Informasi</span>
                    <h2>Dokumentasi API Cuaca</h2>
                </div>
            </div>
            <div class="admin-docs">
                <h3>Endpoint API yang Tersedia</h3>
                <div class="admin-docs-section">
                    <strong>1. Cuaca Saat Ini</strong>
                    <pre><code>POST /api/v1/weather/current
{
    "farm_id": 1,
    "units": "metric",
    "lang": "id",
    "force_refresh": false
}</code></pre>
                </div>
                <div class="admin-docs-section">
                    <strong>2. Prakiraan Cuaca</strong>
                    <pre><code>POST /api/v1/weather/forecast
{
    "farm_id": 1,
    "units": "metric",
    "lang": "id"
}</code></pre>
                </div>
                <div class="admin-docs-section">
                    <strong>3. Riwayat Cuaca</strong>
                    <pre><code>GET /api/v1/weather/history?farm_id=1&limit=30</code></pre>
                </div>
                <div class="admin-docs-section">
                    <strong>4. Cuaca Berdasarkan Kota</strong>
                    <pre><code>POST /api/v1/weather/city
{
    "city": "Jakarta",
    "units": "metric",
    "lang": "id"
}</code></pre>
                </div>

                <h3>Parameter Umum</h3>
                <table class="admin-docs-table">
                    <thead>
                        <tr>
                            <th>Parameter</th>
                            <th>Tipe</th>
                            <th>Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>farm_id</code></td>
                            <td>Integer</td>
                            <td>ID lahan (required untuk endpoint dengan koordinat)</td>
                        </tr>
                        <tr>
                            <td><code>units</code></td>
                            <td>String</td>
                            <td><code>metric</code> (default) atau <code>imperial</code></td>
                        </tr>
                        <tr>
                            <td><code>lang</code></td>
                            <td>String</td>
                            <td><code>id</code> (default) untuk bahasa Indonesia atau bahasa lain</td>
                        </tr>
                        <tr>
                            <td><code>force_refresh</code></td>
                            <td>Boolean</td>
                            <td>Abaikan cache dan ambil data baru dari API</td>
                        </tr>
                    </tbody>
                </table>

                <h3>Response Format</h3>
                <pre><code>{
    "success": true,
    "message": "Data cuaca berhasil diambil",
    "data": {
        "temperature": 28.5,
        "feels_like": 30.2,
        "temp_min": 26.1,
        "temp_max": 31.4,
        "pressure": 1013,
        "humidity": 72,
        "weather": "Cloudy",
        "description": "awan berawan",
        "wind_speed": 5.2,
        "wind_deg": 230,
        "clouds": 75,
        "rain": 0.5,
        "visibility": 10000,
        "uvi": 8.2,
        "timestamp": 1692345600
    }
}</code></pre>
            </div>
        </section>

        <style>
            .admin-form {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }

            .admin-form-group {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }

            .admin-form-group label {
                font-weight: 600;
                font-size: 0.875rem;
                color: #333;
            }

            .admin-input {
                padding: 0.75rem;
                border: 1px solid #ddd;
                border-radius: 6px;
                font-family: Poppins, sans-serif;
                font-size: 0.875rem;
            }

            .admin-input:focus {
                outline: none;
                border-color: #16a34a;
                box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
            }

            .admin-form-hint {
                font-size: 0.8125rem;
                color: #666;
                line-height: 1.5;
            }

            .admin-form-hint a {
                color: #16a34a;
                text-decoration: none;
            }

            .admin-form-hint a:hover {
                text-decoration: underline;
            }

            .admin-form-actions {
                display: flex;
                gap: 1rem;
                margin-top: 1rem;
            }

            .admin-docs {
                background-color: #f9f9f9;
                padding: 1.5rem;
                border-radius: 8px;
                font-size: 0.875rem;
                line-height: 1.6;
            }

            .admin-docs h3 {
                margin-top: 1.5rem;
                margin-bottom: 1rem;
                font-size: 1rem;
                font-weight: 600;
            }

            .admin-docs h3:first-child {
                margin-top: 0;
            }

            .admin-docs-section {
                background: white;
                padding: 1rem;
                border-radius: 6px;
                margin-bottom: 1rem;
                border-left: 3px solid #16a34a;
            }

            .admin-docs-section strong {
                display: block;
                margin-bottom: 0.5rem;
            }

            .admin-docs-section code {
                display: block;
                background-color: #f5f5f5;
                padding: 0.75rem;
                border-radius: 4px;
                overflow-x: auto;
                font-family: 'Courier New', monospace;
                font-size: 0.8125rem;
            }

            .admin-docs-section pre {
                margin: 0;
            }

            .admin-docs-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 1rem;
            }

            .admin-docs-table th,
            .admin-docs-table td {
                padding: 0.75rem;
                text-align: left;
                border-bottom: 1px solid #e0e0e0;
            }

            .admin-docs-table th {
                background-color: #f0f0f0;
                font-weight: 600;
            }

            .admin-docs-table code {
                background-color: #f5f5f5;
                padding: 0.25rem 0.5rem;
                border-radius: 3px;
                display: inline;
                font-size: 0.75rem;
            }

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

            .admin-alert ul {
                margin: 0;
            }
        </style>
    </div>
@endsection
