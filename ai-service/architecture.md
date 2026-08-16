# AI Service Architecture

AI service memakai FastAPI dengan pemisahan application, domain, infrastructure, dan API.

## Layer

### API

- `app/main.py`: FastAPI app bootstrap.
- `app/api/v1/router.py`: route v1.
- `app/api/v1/endpoints`: endpoint HTTP.
- `app/api/dependencies.py`: dependency provider.

Endpoint tidak boleh menyimpan logic inference atau policy.

### Application

- `app/application/use_cases/detect_disease.py`
- `app/application/use_cases/generate_treatment.py`
- `app/application/use_cases/recommend_planting_time.py`
- `app/application/dto`: DTO output use case.

Use case mengorkestrasi domain policy dan infrastructure adapter.

### Domain

- `app/domain/entities`: entity hasil prediksi/rekomendasi.
- `app/domain/repositories`: interface dependency.
- `app/domain/services`: policy murni seperti confidence, image quality, planting scoring.

Domain tidak bergantung ke FastAPI, TensorFlow, atau HTTP client.

### Infrastructure

- `machine_learning`: preprocessing, label mapper, classifier, model loader.
- `llm`: client dan treatment generator.
- `weather`: client dan repository implementation.
- `persistence`: knowledge base repository.

Infrastructure boleh bergantung ke library eksternal.

## Main Flows

### Disease Detection

1. Endpoint menerima gambar.
2. Image quality policy memvalidasi input.
3. Preprocessor menyiapkan tensor.
4. Classifier menjalankan model.
5. Confidence policy menentukan status.
6. Response dikembalikan lewat schema.

### Treatment Recommendation

1. Endpoint menerima disease/context.
2. Use case membaca knowledge base.
3. LLM generator membuat rekomendasi bila tersedia.
4. Fallback treatment dipakai bila LLM tidak tersedia.

### Planting Recommendation

1. Endpoint menerima lokasi/konteks.
2. Weather repository mengambil data cuaca.
3. Domain scoring policy menghitung rekomendasi.
4. DTO dikonversi ke response schema.

## Testing

```bash
pytest
ruff check .
```

Prioritas test:

- policy domain,
- label mapper,
- image quality,
- model loader fallback,
- endpoint health/detection.
