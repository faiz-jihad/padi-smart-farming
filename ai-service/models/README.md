# Model Placement

Model yang ditemukan di repository:

`../AI/model_penyakit_padi_v2_finetuned.h5`

Metadata yang berhasil diinspeksi:

- Format: Keras H5.
- Arsitektur: Sequential dengan backbone MobileNetV2.
- Input: `[None, 224, 224, 3]`.
- Output: Dense softmax dengan `10` unit.

File label asli, urutan class training, dan preprocessing training tidak ditemukan di repository. Karena itu `MODEL_CLASS_MAPPING` wajib diverifikasi oleh pemilik model sebelum production. Default mapping hanya placeholder agar integrasi MVP dapat berjalan dan bisa diganti tanpa mengubah kode use case.
