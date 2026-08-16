# Leaflet.js Weather Map - Implementation Guide

## ✅ Fitur yang Ditambahkan

### 📍 Peta Interaktif Indonesia dengan Leaflet.js

- **File:** `resources/views/admin/weather/map.blade.php`
- **Route:** `GET /admin/weather/map` (named: `admin.weather.map`)
- **CSS:** `public/css/admin/weather-map.css`

### Fitur Peta:

1. **Visualisasi Lokasi Lahan**
    - Menampilkan semua lahan pertanian di Indonesia pada peta OpenStreetMap
    - Pusatkan peta otomatis ke koordinat Indonesia (-2.5489, 118.0149)
    - Zoom level default: 5

2. **Marker Cuaca Dinamis**
    - Marker berwarna berdasarkan kondisi data:
        - 🔵 **Biru** = Data cuaca tersedia
        - 🟢 **Hijau** = Data cuaca terbaru (segar)
        - 🟡 **Kuning** = Data sedang dimuat / tidak ada
        - 🔴 **Merah** = Data kadaluarsa

3. **Popup Interaktif**
    - Klik marker untuk melihat detail cuaca:
        - Nama lahan & petani
        - Koordinat GPS
        - Icon cuaca real-time
        - Suhu (°C)
        - Kelembaban (%)
        - Kecepatan angin (m/s)
        - Deskripsi cuaca
        - Waktu observasi
        - Link ke riwayat lengkap

4. **Kontrol Peta**
    - Zoom in/out buttons
    - Scale indicator (km/m)
    - Auto-fit bounds untuk semua marker
    - Responsif untuk mobile

5. **Legenda**
    - Penjelasan warna & indikator marker
    - Memudahkan pemahaman status cuaca

6. **Auto-Refresh**
    - Peta refresh otomatis setiap 5 menit
    - Menampilkan data terbaru tanpa perlu reload manual

## Struktur File

```
resources/views/admin/weather/
├── index.blade.php      (Dashboard - ditambah tombol Peta)
├── history.blade.php    (Riwayat)
├── settings.blade.php   (Pengaturan)
└── map.blade.php        (⭐ NEW - Peta Leaflet)

public/css/admin/
└── weather-map.css      (⭐ NEW - Styling peta)

app/Http/Controllers/Admin/
└── WeatherController.php (map() method ditambahkan)

routes/
└── web.php              (map route ditambahkan)
```

## Cara Kerja

### 1. Data Flow

```
GET /admin/weather/map
    ↓
WeatherController@map
    ↓
Query: Farm::with('weatherSnapshots', 'farmer')
    ↓
View: admin/weather/map.blade.php
    ↓
Leaflet.js render map + markers
```

### 2. Leaflet Initialization

```javascript
- Initialize L.map() centered on Indonesia
- Add OpenStreetMap tile layer
- Loop through farms data
- Create markers with custom icons
- Bind popups dengan weather info
- Auto-fit bounds ke semua marker
- Add scale control
```

### 3. Marker Color Logic

```javascript
const createWeatherIcon = (condition) => {
    - 'fresh' → #10b981 (hijau)
    - 'expired' → #ef4444 (merah)
    - 'loading' → #fbbf24 (kuning)
    - default → #3b82f6 (biru)
}
```

## Teknologi yang Digunakan

### Libraries

- **Leaflet.js 1.9.4** - Map library
- **OpenStreetMap** - Tile provider (gratis, no API key)
- **CDN Links:**
    ```html
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css"
    />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    ```

## Styling Details

### Marker

- 40x40px circular marker
- White border (2px)
- Box shadow untuk depth
- Hover: Scale 1.2x + shadow increase
- Responsive padding

### Popup

- Max width: 300px
- Menampilkan tabel cuaca dengan formatting
- Link ke halaman riwayat
- Border & spacing yang jelas

### Legend

- Grid responsive (1-4 kolom)
- Warna sesuai marker
- Background subtle (#f9f9f9)
- Padding & border konsisten

### Responsive

- Desktop: Map height 600px
- Mobile (< 768px): Map height 400px
- Legend menjadi single column
- Font size scaled down

## Environment Variables

Tidak memerlukan environment variables tambahan. OpenStreetMap tile gratis dan public.
Weather data sudah dari database lokal.

## Performance

- Map renders dalam < 1 detik
- Leaflet optimized untuk mobile
- Auto-refresh 5 menit (bisa dikonfigure)
- Lightweight implementation (no heavy libraries)

## Browser Compatibility

✓ Chrome/Edge
✓ Firefox
✓ Safari
✓ Mobile browsers

## Potential Enhancements

1. **Clustering** - GroupMarker untuk daerah dengan banyak farm
2. **Real-time Updates** - WebSocket untuk live weather updates
3. **Heatmap** - Visualisasi distribusi cuaca
4. **Weather Alerts** - Marker berubah saat ada alert
5. **Export Map** - Screenshot/PDF peta
6. **Custom Base Map** - Satellite, terrain options
7. **Drawing Tools** - Buat area monitoring
8. **Weather Animation** - Animated weather overlay

## Testing

Untuk test peta:

1. Pastikan ada farm dengan latitude/longitude
2. Akses `/admin/weather/map`
3. Peta harus centered di Indonesia
4. Klik marker untuk popup
5. Verifikasi warna marker sesuai status

## Troubleshooting

### Peta tidak tampil

- Check browser console untuk JS errors
- Pastikan CDN Leaflet accessible
- Verify farm data punya latitude/longitude

### Marker tidak muncul

- Check weather_snapshots table punya data
- Verify farm.latitude & farm.longitude not null
- Koordinat valid untuk Indonesia range

### Popup tidak muncul

- Ensure weather_snapshots.payload_json valid JSON
- Check nested object access (main.temp, etc.)

## Keamanan

- Map hanya accessible oleh authenticated admin
- Middleware: auth, admin.web
- Data tidak di-expose ke public
- CDN Leaflet adalah trusted resource

## License

Leaflet.js: BSD 2-Clause License
OpenStreetMap: ODbL 1.0
