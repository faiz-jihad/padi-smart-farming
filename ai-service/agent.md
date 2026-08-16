# AI Service Agent Guide

Dokumen ini menjadi aturan kerja untuk agent yang mengubah AI service P.A.D.I.

## Prinsip

- Endpoint FastAPI harus tipis.
- Use case aplikasi berada di `app/application/use_cases`.
- Rule domain berada di `app/domain/services`.
- Integrasi eksternal berada di `app/infrastructure`.
- Schema request/response berada di `app/schemas`.
- Jangan campur logic model ML langsung di endpoint.
- Jangan membuat fallback palsu yang terlihat seperti hasil model sungguhan.
- Semua perubahan AI harus punya test unit atau integration sesuai risiko.

## Struktur Yang Harus Dijaga

- `app/api`: router, endpoint, dependency wiring.
- `app/application`: use case dan DTO.
- `app/domain`: entity, repository interface, policy.
- `app/infrastructure`: model loader, preprocessing, LLM, weather, persistence.
- `tests/unit`: test policy, loader, mapper, use case.
- `tests/integration`: test endpoint.

## Pola Implementasi

1. Definisikan schema request/response.
2. Tambah use case atau service domain.
3. Tambah infrastructure adapter bila menyentuh file/model/API eksternal.
4. Wire dependency di `app/api/dependencies.py`.
5. Endpoint hanya memanggil use case.
6. Tambahkan test.

## Larangan

- Jangan hardcode path model di endpoint.
- Jangan membuka file knowledge base dari endpoint langsung.
- Jangan panggil HTTP eksternal langsung dari use case tanpa repository/client.
- Jangan mengubah confidence policy tanpa update test.
