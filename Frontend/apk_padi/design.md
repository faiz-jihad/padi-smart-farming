# Frontend Design Guide

Panduan tampilan Flutter P.A.D.I.

## Gaya Visual

- Gunakan warna hijau dan putih sebagai warna utama.
- Hindari palette ramai dan gradient berlebihan.
- Tampilan app harus terasa operasional, bersih, dan mudah dipakai.
- Admin screen harus padat dan jelas, bukan landing page.
- Gunakan spacing konsisten: 8, 12, 16, 24.
- Radius komponen maksimal 8px kecuali avatar/circle.

## Typography

- Gunakan typography dari theme app.
- Heading harus ringkas dan tidak terlalu besar di panel kecil.
- Jangan pakai teks panjang untuk menjelaskan fitur di layar utama.

## Components

- Button untuk aksi nyata.
- Text field untuk input.
- Segmented/tab control untuk mode/filter.
- Card hanya untuk item berulang atau panel data, jangan card di dalam card.
- Empty state harus jelas dan pendek.
- Loading state harus terlihat tanpa menggeser layout besar.

## Admin Mobile

- Panel admin hanya untuk user `admin`.
- Data admin harus berasal dari backend.
- Action admin harus menampilkan loading dan error state.
- Jangan tampilkan data dummy fallback bila API gagal.

## Accessibility

- Label input harus jelas.
- Kontras hijau/putih harus cukup.
- Tap target minimal nyaman untuk mobile.
