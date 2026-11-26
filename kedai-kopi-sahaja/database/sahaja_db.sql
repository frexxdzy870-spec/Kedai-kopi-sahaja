-- Kedai Kopi Sahaja - skema database (MySQL 5.7+ / MariaDB 10.3+)
-- Di hosting: buat database lewat panel, hapus 2 baris CREATE DATABASE & USE, lalu impor.
CREATE DATABASE IF NOT EXISTS sahaja_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sahaja_db;

CREATE TABLE admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE kategori (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(60) NOT NULL,
  urutan INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE menu (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kategori_id INT NOT NULL,
  nama VARCHAR(100) NOT NULL,
  deskripsi TEXT,
  harga INT NULL,                 -- NULL = "tanya harga"
  gambar VARCHAR(255),
  andalan TINYINT NOT NULL DEFAULT 0,
  tersedia TINYINT NOT NULL DEFAULT 1,
  FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE promo (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(150) NOT NULL,
  deskripsi TEXT,
  gambar VARCHAR(255),
  mulai DATE NULL,
  selesai DATE NULL,
  aktif TINYINT NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE event (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(150) NOT NULL,
  jenis VARCHAR(30) NOT NULL DEFAULT 'event',   -- event | workshop | live-music | diskusi
  deskripsi TEXT,
  tanggal DATE NOT NULL,
  jam VARCHAR(40),
  gambar VARCHAR(255),
  rsvp_buka TINYINT NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE rsvp (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  nama VARCHAR(100) NOT NULL,
  whatsapp VARCHAR(20) NOT NULL,
  jumlah INT NOT NULL DEFAULT 1,
  dibuat DATETIME NOT NULL,
  FOREIGN KEY (event_id) REFERENCES event(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE lowongan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  posisi VARCHAR(120) NOT NULL,
  tipe VARCHAR(40) NOT NULL DEFAULT 'Part time',
  deskripsi TEXT,
  kualifikasi TEXT,               -- satu poin per baris
  gambar VARCHAR(255),
  buka TINYINT NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE pelamar (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lowongan_id INT NULL,
  posisi VARCHAR(120) NOT NULL,
  nama VARCHAR(100) NOT NULL,
  whatsapp VARCHAR(20) NOT NULL,
  email VARCHAR(120),
  pesan TEXT,
  portofolio_url VARCHAR(255),
  cv_file VARCHAR(100) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'baru',
  catatan TEXT,
  dibuat DATETIME NOT NULL,
  FOREIGN KEY (lowongan_id) REFERENCES lowongan(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE reservasi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  whatsapp VARCHAR(20) NOT NULL,
  jenis VARCHAR(30) NOT NULL,     -- meja | ruang-diskusi | event
  tanggal DATE NOT NULL,
  jam VARCHAR(5) NOT NULL,
  jumlah INT NOT NULL,
  catatan TEXT,
  status VARCHAR(20) NOT NULL DEFAULT 'baru',
  dibuat DATETIME NOT NULL
) ENGINE=InnoDB;

CREATE TABLE komunitas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(150) NOT NULL,
  deskripsi TEXT,
  tanggal DATE NULL,
  gambar VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE galeri (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(150) NOT NULL,
  gambar VARCHAR(255) NOT NULL,
  urutan INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- ===== Data awal (dari materi brand) =====
INSERT INTO kategori (id, nama, urutan) VALUES (1,'Kopi',1),(2,'Non-Kopi',2),(3,'Makanan',3);

INSERT INTO menu (kategori_id, nama, deskripsi, harga, gambar, andalan, tersedia) VALUES
(1,'Kopi Cangkir Hijau','Kopi hitam pekat yang disajikan di cangkir klasik.',NULL,'assets/images/menu/kopi-cangkir-hijau.jpg',1,1),
(1,'Kopi Hijau Susu','Kopi hijau dengan tuangan susu.',NULL,'assets/images/menu/kopi-hijau-susu.jpg',1,1),
(3,'Mie Goreng Telur','Mie goreng hangat dengan telur ceplok.',NULL,'assets/images/menu/mie-telur.jpg',1,1);

INSERT INTO event (judul, jenis, deskripsi, tanggal, jam, gambar, rsvp_buka) VALUES
('Menjadi Utuh, Bukan Serba Bisa','diskusi','Obrolan #SahajaBersama bersama Nizar Luthfi, Emirza Haqqy, dan Erdogan Thayyib. Free entry, free welcome drink. Semuanya diundang, semua boleh datang.','2026-02-05','20.00 - selesai','assets/images/events/menjadi-utuh.jpg',0);

INSERT INTO lowongan (posisi, tipe, deskripsi, kualifikasi, gambar, buka) VALUES
('Pramukopi / Penyaji','Part time','Menyiapkan dan menyajikan pesanan, serta menyambut tamu kedai.','Berpenampilan menarik\nTerampil\nKomunikatif\nUsia maksimal 25 tahun\nJujur dan bertanggung jawab','assets/images/events/loker-pramukopi.jpg',1);

INSERT INTO komunitas (judul, deskripsi, tanggal, gambar) VALUES
('Menjadi Utuh, Bukan Serba Bisa','Agenda obrolan #SahajaBersama di Kedai Kopi Sahaja.','2026-02-05','assets/images/komunitas/menjadi-utuh.jpg');

INSERT INTO galeri (judul, gambar, urutan) VALUES
('Meracik di balik bar','assets/images/gallery/meracik-di-bar.jpg',1),
('Dua cangkir andalan','assets/images/gallery/dua-cangkir.jpg',2),
('Merayakan hal kecil','assets/images/gallery/merayakan-hal-kecil.jpg',3),
('Nanti juga baik, tenang sahaja','assets/images/gallery/tenang-sahaja.jpg',4),
('Sebelum kedai dibuka','assets/images/gallery/sebelum-dibuka.jpg',5),
('Hidup untuk hal-hal kecil','assets/images/gallery/hal-hal-kecil.jpg',6),
('Cangkir-cangkir kita','assets/images/gallery/nanti-juga-baik.jpg',7);

-- Akun admin TIDAK dibuat otomatis (tanpa password bawaan). Lihat README.md bagian "Buat akun admin".
