<?php
/**
 * Konfigurasi global Kedai Kopi Sahaja.
 * Ubah nilai di file ini sesuai kondisi kedai.
 */
declare(strict_types=1);

date_default_timezone_set('Asia/Jakarta');

// BASE_URL terdeteksi otomatis (jalan di root domain maupun subfolder XAMPP).
// Jika salah, isi manual, contoh: define('BASE_URL', '/kedai-kopi-sahaja');
if (!defined('BASE_URL')) {
    $root = str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
    $app  = str_replace('\\', '/', (string) realpath(dirname(__DIR__)));
    define('BASE_URL', ($root !== '' && str_starts_with($app, $root)) ? rtrim(substr($app, strlen($root)), '/') : '');
}

// true = URL rapi (/menu). Butuh Apache mod_rewrite. false = /pages/menu.php
const CLEAN_URLS = true;
// true hanya saat development: menampilkan error di layar.
const APP_DEBUG = false;

const APP_ROOT      = __DIR__ . '/..';
const SITE_NAME     = 'Kedai Kopi Sahaja';
const SITE_TAGLINE  = 'nanti juga baik, tenang sahaja.';
const SITE_DESC     = 'Kedai Kopi Sahaja di Cukir, Diwek, Jombang. Kopi, makanan hangat, dan ruang bertemu bersama komunitas #SahajaBersama.';
const ALAMAT        = 'Jl. Tebuireng gg. 4 no. 4, Cukir, Diwek, Jombang';

// Nomor WhatsApp format internasional tanpa "+" (diambil dari poster event - pastikan masih benar).
const WA_NUMBER     = '6285732683940';
const WA_LABEL      = '0857-3268-3940 (Mazhar)';

const INSTAGRAM     = 'sahaja.kopi';
const MAPS_QUERY    = 'Kedai Kopi Sahaja, Jl. Tebuireng gg. 4 no. 4 Cukir Diwek Jombang';

// JAM BUKA. Nilai di bawah hanya CONTOH. Sesuaikan, lalu ubah JAM_BUKA_AKTIF jadi true.
// Selama false, situs tidak menampilkan jam / status buka.
const JAM_BUKA_AKTIF = false;
// 1 = Senin ... 7 = Minggu. null = libur. Jam tutup boleh lewat tengah malam (mis. 01:00).
const JAM_BUKA = [
    1 => ['10:00', '23:00'], 2 => ['10:00', '23:00'], 3 => ['10:00', '23:00'],
    4 => ['10:00', '23:00'], 5 => ['10:00', '23:00'], 6 => ['10:00', '23:00'],
    7 => ['10:00', '23:00'],
];

const MAX_CV_SIZE    = 2 * 1024 * 1024;   // 2 MB
const MAX_IMAGE_SIZE = 3 * 1024 * 1024;   // 3 MB

error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
