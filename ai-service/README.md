# P.A.D.I. AI Service

FastAPI service untuk integrasi model deteksi penyakit daun padi dan rekomendasi pendukung keputusan P.A.D.I. Backend utama memanggil service ini melalui REST API, sehingga aplikasi mobile tetap hanya berkomunikasi dengan backend Laravel.

## Arsitektur

Struktur mengikuti Clean Architecture praktis:

- `app/api`: router FastAPI, dependency, dan response HTTP.
- `app/application`: use case dan DTO alur aplikasi.
- `app/domain`: entity, repository protocol, dan policy bisnis.
- `app/infrastructure`: Ultralytics YOLO, TensorFlow/Keras legacy loader, OpenCV, LLM client, Weather API client, dan persistence KB.
- `app/schemas`: kontrak request/response Pydantic.
- `knowledge_base`: panduan penyakit dan rekomendasi tervalidasi untuk MVP.
- `scripts`: inspeksi model dan smoke test.
- `tests`: unit dan integration test.

Dependency mengarah ke dalam: API memanggil application, application memakai domain, infrastructure mengimplementasikan kontrak domain.

## Model

Model utama:

`models/YOLO11L-Rice-Disease-Detection.pt`

Runtime:

- Format: Ultralytics YOLO `.pt`.
- Backend aplikasi mobile tetap Laravel; Laravel meneruskan gambar ke AI service.
- Loader memilih YOLO otomatis saat `MODEL_PATH` berakhiran `.pt`.
- Label dibaca dari `model.names` milik YOLO. `models/class_labels.json` hanya fallback bila label model tidak tersedia.

Jangan isi `MODEL_REPORTED_ACCURACY` dengan angka perkiraan. Gunakan nilai itu hanya jika ada metrik valid dari evaluasi model yang sama pada dataset uji yang benar.

## Setup Lokal

```powershell
cd ai-service
py -3.11 -m venv .venv
.venv\Scripts\Activate.ps1
pip install -e ".[dev]"
Copy-Item .env.example .env
uvicorn app.main:app --reload --host 0.0.0.0 --port 8001
```

Gunakan Python 3.11 untuk environment lokal. Runtime Docker juga memakai Python 3.11 agar dependency ML native bisa dimuat stabil.
Laravel API memakai port `8000`, jadi aplikasi Flutter tetap hanya terhubung ke
backend Laravel. Untuk runtime Docker lokal, AI service dipetakan ke host port
`8003` dan Laravel memanggilnya melalui `AI_SERVICE_URL=http://127.0.0.1:8003/api/v1`.

Jangan jalankan service deteksi real dengan Python 3.13/3.14. Dependency ML
native bisa gagal terpasang atau gagal load. Jika model tidak siap, health check
akan melaporkan `model_loaded=false`; backend Laravel menolak scan agar tidak
membuat hasil penyakit palsu.

OpenAPI tersedia di `http://127.0.0.1:8003/docs` saat menjalankan Docker Compose.

## Docker

```powershell
cd ai-service
Copy-Item .env.example .env
docker compose up --build
```

Compose me-mount model dari `./models/YOLO11L-Rice-Disease-Detection.pt` ke container sebagai read-only.

## Test dan Quality

```powershell
cd ai-service
ruff check .
pytest
```

Test otomatis mem-mock model, LLM, dan Weather API. Tidak ada panggilan API eksternal saat test.

## Contoh Request

Health:

```bash
curl http://127.0.0.1:8001/api/v1/health
```

Dengan Docker Compose lokal:

```bash
curl http://127.0.0.1:8003/api/v1/health
```

Deteksi penyakit:

```bash
curl -X POST http://127.0.0.1:8001/api/v1/diseases/detect \
  -F "image=@sample.jpg" \
  -F "plant_age_days=45"
```

Tes Postman:

- Method: `POST`
- URL Docker lokal: `http://127.0.0.1:8003/api/v1/diseases/detect`
- Body: `form-data`
- Key `image`: pilih type `File`, lalu upload foto daun padi.
- Key opsional: `plant_age_days`, `latitude`, `longitude`.
- Jangan set header `Content-Type` manual; biarkan Postman membuat multipart boundary.

Rekomendasi penanganan:

```bash
curl -X POST http://127.0.0.1:8001/api/v1/treatments/recommend \
  -H "Content-Type: application/json" \
  -d '{"disease_code":"blast","confidence":0.91,"plant_age_days":45,"severity":"medium","affected_area_percentage":12,"weather_condition":"humid","actions_already_taken":[]}'
```

Rekomendasi tanam:

```bash
curl -X POST http://127.0.0.1:8001/api/v1/planting/recommend \
  -H "Content-Type: application/json" \
  -d '{"latitude":-6.3266,"longitude":108.32,"rice_variety":"Ciherang","irrigation_type":"technical","land_area_hectares":1,"preferred_start_date":"2026-08-13"}'
```

## Format Response

Sukses:

```json
{
  "success": true,
  "data": {},
  "meta": {
    "request_id": "uuid"
  }
}
```

Error:

```json
{
  "success": false,
  "error": {
    "code": "IMAGE_TOO_BLURRY",
    "message": "Foto terlalu buram. Dekatkan kamera dan ambil ulang foto."
  },
  "meta": {
    "request_id": "uuid"
  }
}
```

## Error Code

- `EMPTY_IMAGE`: file upload kosong.
- `IMAGE_TOO_LARGE`: ukuran file melebihi `MAX_IMAGE_SIZE_MB`.
- `UNSUPPORTED_IMAGE_TYPE`: MIME type bukan JPEG atau PNG.
- `INVALID_IMAGE_SIGNATURE`: signature byte tidak sesuai MIME type.
- `INVALID_IMAGE`: file tidak dapat didecode sebagai gambar.
- `IMAGE_TOO_BLURRY`: foto terlalu buram.
- `IMAGE_TOO_DARK`: foto terlalu gelap.
- `IMAGE_TOO_BRIGHT`: foto terlalu terang.
- `MODEL_NOT_FOUND`: path model tidak ditemukan.
- `MODEL_LOAD_FAILED`: model gagal dimuat.
- `MODEL_UNAVAILABLE`: model belum siap untuk inferensi.
- `WEATHER_TIMEOUT`: Weather API timeout.
- `LLM_TIMEOUT`: LLM timeout, rekomendasi fallback dari knowledge base.

## Integrasi Backend Utama

Backend Laravel memanggil endpoint service internal:

- `GET /api/v1/health` untuk readiness internal.
- `POST /api/v1/diseases/detect` untuk scan gambar.
- `POST /api/v1/treatments/recommend` untuk rekomendasi penanganan.
- `POST /api/v1/planting/recommend` untuk rekomendasi waktu tanam.

Simpan `request_id` dari response untuk audit dan korelasi log. Jangan kirim service ini langsung ke aplikasi mobile.

## Mengganti Model

1. Letakkan model baru di lokasi yang tidak hardcoded ke source code.
2. Ubah `MODEL_PATH`, `MODEL_VERSION`, dan `MODEL_CLASS_MAPPING`.
3. Jalankan `python scripts/inspect_model.py <path-model>`.
4. Jalankan `pytest` dan smoke test.
5. Verifikasi label, threshold, dan model card sebelum production.

## Keterbatasan

Pastikan label bawaan YOLO (`model.names`) sesuai dengan kode penyakit aplikasi: `healthy`, `blast`, `tungro`, dan `bacterial_leaf_blight`. Jika nama class dari training berbeda, tambahkan alias di `label_mapper.py` atau perbaiki label saat export model sebelum dipakai production.

Service ini adalah pendukung keputusan, bukan pengganti diagnosis resmi dari penyuluh pertanian, agronom, atau laboratorium.
