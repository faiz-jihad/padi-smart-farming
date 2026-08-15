# AI Service API Design Guide

Panduan desain response dan perilaku API AI service.

## Response

- Response harus konsisten dengan schema Pydantic.
- Error harus jelas dan tidak membocorkan stack trace.
- Confidence dan quality status harus eksplisit.
- Bila hasil tidak cukup yakin, berikan status yang aman dan alasan.

## AI Output

- Hindari bahasa yang terlalu percaya diri bila confidence rendah.
- Treatment recommendation harus praktis dan bisa ditindaklanjuti.
- Planting recommendation harus menjelaskan faktor utama secara ringkas.
- Jangan mengarang data cuaca atau diagnosis.

## API UX

- Endpoint harus cepat gagal untuk input invalid.
- File image harus divalidasi kualitasnya sebelum model inference.
- Timeout eksternal harus punya fallback yang aman.
- Health endpoint harus ringan.

## Consistency

- Gunakan snake_case untuk JSON field.
- Gunakan bahasa Indonesia untuk pesan yang dikonsumsi aplikasi P.A.D.I.
- Gunakan enum/status yang stabil agar frontend mudah parsing.
