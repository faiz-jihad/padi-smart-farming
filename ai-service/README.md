# P.A.D.I. AI Service

FastAPI service untuk integrasi model deteksi penyakit daun padi dan rekomendasi pendukung keputusan P.A.D.I. Backend utama memanggil service ini melalui REST API, sehingga aplikasi mobile tetap hanya berkomunikasi dengan backend Laravel.

## Arsitektur

Struktur mengikuti Clean Architecture praktis:

- `app/api`: router FastAPI, dependency, dan response HTTP.
- `app/application`: use case dan DTO alur aplikasi.
- `app/domain`: entity, repository protocol, dan policy bisnis.
- `app/infrastructure`: TensorFlow/Keras, OpenCV, LLM client, Weather API client, dan persistence KB.
- `app/schemas`: kontrak request/response Pydantic.
- `knowledge_base`: panduan penyakit dan rekomendasi tervalidasi untuk MVP.
- `scripts`: inspeksi model dan smoke test.
- `tests`: unit dan integration test.

Dependency mengarah ke dalam: API memanggil application, application memakai domain, infrastructure mengimplementasikan kontrak domain.

## Model

Model yang ditemukan:

`../AI/model_penyakit_padi_v2_finetuned.h5`

Hasil inspeksi:

- Format: Keras H5.
- Backbone: MobileNetV2.
- Input: `224x224x3`.
- Output: softmax `10` kelas.

Urutan class asli, label training, dan preprocessing training tidak ditemukan di repository. Service menyediakan `MODEL_CLASS_MAPPING` agar mapping output model dapat diverifikasi dan diganti dari environment tanpa mengubah kode.

## Setup Lokal

```powershell
cd ai-service
python -m venv .venv
.venv\Scripts\Activate.ps1
pip install -e ".[dev]"
Copy-Item .env.example .env
uvicorn app.main:app --reload --host 0.0.0.0 --port 8000
```

OpenAPI tersedia di `http://127.0.0.1:8000/docs`.

## Docker

```powershell
cd ai-service
Copy-Item .env.example .env
docker compose up --build
```

Compose me-mount model dari `../AI/model_penyakit_padi_v2_finetuned.h5` ke container sebagai read-only.

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
curl http://127.0.0.1:8000/api/v1/health
```

Deteksi penyakit:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/diseases/detect \
  -F "image=@sample.jpg" \
  -F "plant_age_days=45"
```

Rekomendasi penanganan:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/treatments/recommend \
  -H "Content-Type: application/json" \
  -d '{"disease_code":"blast","confidence":0.91,"plant_age_days":45,"severity":"medium","affected_area_percentage":12,"weather_condition":"humid","actions_already_taken":[]}'
```

Rekomendasi tanam:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/planting/recommend \
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

Label class asli dan preprocessing training belum ditemukan. Default preprocessing mengikuti pola MobileNetV2, yaitu resize `224x224` dan normalisasi ke rentang `[-1, 1]`. Mapping default adalah placeholder yang harus divalidasi oleh pemilik model sebelum digunakan untuk keputusan lapangan.

Service ini adalah pendukung keputusan, bukan pengganti diagnosis resmi dari penyuluh pertanian, agronom, atau laboratorium.
