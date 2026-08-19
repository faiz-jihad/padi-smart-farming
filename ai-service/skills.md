# AI Service Skills

Daftar kemampuan teknis yang relevan untuk AI service.

## FastAPI

- Router dan endpoint.
- Dependency injection.
- File upload.
- Pydantic schema.
- HTTP exception handling.

## Machine Learning

- TensorFlow model loading.
- Image preprocessing dengan OpenCV/Numpy.
- Label mapping.
- Confidence threshold.
- Safe fallback saat model tidak tersedia.

## Domain Policy

- Image quality validation.
- Confidence policy.
- Planting scoring policy.
- Treatment fallback policy.

## External Integration

- Weather client dengan timeout.
- LLM client untuk treatment generation.
- Knowledge base repository.

## Commands

```bash
pip install -e ".[dev]"
pytest
ruff check .
uvicorn app.main:app --reload
```

## Review Checklist

- Endpoint tetap tipis.
- Logic masuk use case/domain/infrastructure sesuai tempatnya.
- Tidak ada diagnosis palsu saat confidence rendah.
- Timeout eksternal ditangani.
- Test unit/integration ditambah.
- `pytest` dan `ruff check .` lulus.
