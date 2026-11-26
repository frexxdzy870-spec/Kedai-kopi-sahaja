# Kedai Kopi Sahaja

Website PHP (tanpa framework) + MySQL untuk Kedai Kopi Sahaja, Cukir, Jombang.

## Kebutuhan
- PHP 8.0 atau lebih baru dengan ekstensi `pdo_mysql`, `mbstring`, `fileinfo`
- MySQL 5.7+ / MariaDB 10.3+
- Apache dengan `mod_rewrite` dan `AllowOverride All`

## Pasang di XAMPP
1. Salin folder `kedai-kopi-sahaja` ke `htdocs/`.
2. Jalankan Apache dan MySQL.
3. Buka `http://localhost/phpmyadmin`, tab **Import**, pilih `database/sahaja_db.sql`.
4. Cek `config/database.php` (default XAMPP: user `root`, password kosong).
5. Buka `http://localhost/kedai-kopi-sahaja/`.

## Buat akun admin (wajib, sekali saja)
Tidak ada password bawaan.

1. Buat hash password (di XAMPP Shell / terminal). Ganti `PasswordKuatmu`:

       php -r "echo password_hash('PasswordKuatmu', PASSWORD_DEFAULT), PHP_EOL;"

2. Di phpMyAdmin, pilih `sahaja_db` lalu tab **SQL**:

       INSERT INTO admin (username, password_hash) VALUES ('admin', 'TEMPEL_HASH_DI_SINI');

3. Login di `http://localhost/kedai-kopi-sahaja/admin/login.php`.

Lupa password: buat hash baru, lalu `UPDATE admin SET password_hash='...' WHERE username='admin';`

## Wajib dicek sebelum online
| Yang dicek | Lokasi |
|---|---|
| Nomor WhatsApp (diambil dari poster event, kontak Mazhar) | `config/config.php` → `WA_NUMBER`, `WA_LABEL` |
| Jam buka: isi jam asli, lalu `JAM_BUKA_AKTIF = true` | `config/config.php` |
| Harga menu (masih kosong, tampil "Tanya harga") | Admin → Menu |
| Nama & deskripsi menu (disusun dari foto) | Admin → Menu |
| Lowongan pramukopi masih dibuka atau tidak | Admin → Lowongan |
| Cerita brand, filosofi, tim (teks draf) | `pages/tentang-kami.php` |
| Profil komunitas & cara bergabung (teks draf) | `pages/komunitas.php` |
| Titik peta (saat ini mencari berdasarkan alamat) | `config/config.php`, `index.php` |
| Izin tampil untuk orang yang wajahnya ada di foto/poster | Admin → Galeri / Event |

## Deploy ke hosting
- Upload semua isi folder, termasuk file `.htaccess` (sering tersembunyi).
- Buat database di panel hosting, hapus baris `CREATE DATABASE` dan `USE` pada file SQL, lalu impor.
- Isi `config/database.php`, pastikan `APP_DEBUG = false`, aktifkan HTTPS.
- Folder `assets/uploads/` dan `assets/images/` harus bisa ditulis PHP (755 biasanya cukup).
- **Uji proteksi:** buka `/config/config.php`, `/database/sahaja_db.sql`, dan `/assets/uploads/cv/`. Ketiganya harus **403 Forbidden**.
- Server **Nginx** tidak membaca `.htaccess`: aturan rewrite dan blokir folder harus ditulis di konfigurasi Nginx.
- Jika clean URL 404, aktifkan `mod_rewrite` atau set `CLEAN_URLS = false`. Proteksi folder tetap butuh `.htaccess` aktif.
- Jika upload ditolak, pastikan `upload_max_filesize` dan `post_max_size` di `php.ini` minimal 4M.

## Alur fitur
- **Order:** halaman Menu → + Pesan → pesan otomatis ke WhatsApp (tidak disimpan di database).
- **Reservasi:** tersimpan di database → pengunjung konfirmasi lewat WA → dikelola di Dashboard admin.
- **RSVP:** tampil untuk event mendatang → daftar pendaftar di Admin → Event & RSVP.
- **Lamaran:** CV PDF maks 2 MB, bernama acak, hanya bisa diunduh admin lewat Admin → Pelamar.
  CV berisi data pribadi: hapus yang sudah tidak diperlukan.

## Keamanan yang sudah ada
Prepared statement, escape output, token CSRF, password ter-hash, pembatasan percobaan login,
sesi admin kedaluwarsa setelah 2 jam tidak aktif, validasi MIME upload, honeypot anti-spam.
Honeypot dan jeda kirim hanya menahan bot sederhana. Pertimbangkan CAPTCHA bila muncul spam.
