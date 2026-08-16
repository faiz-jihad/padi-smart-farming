# Backend Admin Design Guide

Panduan ini mengatur tampilan Blade admin backend.

## Gaya Visual

- Warna utama hanya putih dan hijau.
- Hindari warna biru, merah, ungu, oranye, kuning untuk status visual.
- Hindari hero marketing, badge palsu, teks promosi, dan layout yang terlihat generated.
- UI admin harus terasa seperti panel operasional: padat, bersih, mudah dipindai.
- Gunakan radius maksimal 8px untuk card, input, dan button.
- Jangan pakai dekorasi gradient besar, blob, orb, atau background abstrak.

## Font

- Gunakan `Poppins` untuk body, form, tabel, dan navigasi.
- Gunakan `Montserrat` untuk heading penting.
- Jangan pakai font lain kecuali fallback system.

## Admin Pages

- Login harus fokus ke form, bukan landing page.
- Dashboard harus menampilkan data ringkas yang real.
- Halaman modul harus punya:
  - judul singkat,
  - statistik dari database,
  - tabel data real,
  - action nyata bila modul memang bisa dimoderasi.

## Notifikasi

- Badge notifikasi harus berasal dari DB.
- Event realtime harus memperbarui UI tanpa reload.
- Tombol "Tandai dibaca" harus memanggil route backend dan mengubah `read_at`.

## File Styling

- Login: `public/css/admin/auth.css`.
- Tema global admin: `public/css/admin/theme.css`.
- Layout operasional: `public/css/admin/operational.css`.
- Jangan menambah inline style di Blade kecuali benar-benar darurat.
