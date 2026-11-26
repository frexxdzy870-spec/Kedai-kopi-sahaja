<?php
/** Helper yang dipakai di seluruh situs. */
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'path'     => '/',
    ]);
    session_start();
}
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

/* ---------- Output & URL ---------- */
function e(mixed $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    $pages = ['menu', 'event', 'lowongan', 'komunitas', 'tentang-kami', 'reservasi'];
    [$p, $q] = array_pad(explode('?', $path, 2), 2, null);
    if (!CLEAN_URLS && in_array($p, $pages, true)) {
        $p = "pages/$p.php";
    }
    return BASE_URL . '/' . $p . ($q !== null ? "?$q" : '');
}

/** URL file statis, dengan penanda versi agar cache browser ikut diperbarui. */
function asset(?string $path): string
{
    if (!$path) {
        $path = 'assets/images/logo/bunga.png';
    }
    $file = APP_ROOT . '/' . $path;
    return BASE_URL . '/' . $path . (is_file($file) ? '?v=' . filemtime($file) : '');
}

function redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

function query(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

/* ---------- Format ---------- */
function rupiah(mixed $angka): string
{
    return ($angka === null || $angka === '') ? 'Tanya harga' : 'Rp' . number_format((int) $angka, 0, ',', '.');
}

function tanggal_id(?string $tgl): string
{
    if (!$tgl) {
        return '';
    }
    $hari  = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $t = strtotime($tgl);
    return $hari[(int) date('w', $t)] . ', ' . date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t);
}

/* ---------- Input ---------- */
function input(string $key, int $max = 255): string
{
    $v = $_POST[$key] ?? '';
    if (!is_string($v)) {
        return '';
    }
    $v = trim(str_replace(["\0", "\r"], '', $v));
    return mb_substr($v, 0, $max);
}

/** Normalisasi nomor WA Indonesia menjadi 62xxxx, atau null bila tidak valid. */
function normal_wa(string $no): ?string
{
    $n = preg_replace('/[\s\-().+]/', '', $no);
    if (str_starts_with($n, '0')) {
        $n = '62' . substr($n, 1);
    }
    return preg_match('/^628\d{7,12}$/', $n) ? $n : null;
}

function valid_date(string $d): bool
{
    $x = DateTime::createFromFormat('!Y-m-d', $d);
    return $x !== false && $x->format('Y-m-d') === $d;
}

function wa_link(string $pesan = '', string $nomor = WA_NUMBER): string
{
    return 'https://wa.me/' . $nomor . ($pesan !== '' ? '?text=' . rawurlencode($pesan) : '');
}

/* ---------- Keamanan form ---------- */
function csrf_token(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

/** Panggil di awal setiap proses POST. */
function csrf_check(): void
{
    // Jika body melebihi post_max_size, PHP mengosongkan $_POST.
    if (!is_string($_POST['csrf'] ?? null) || !hash_equals(csrf_token(), $_POST['csrf'])) {
        http_response_code(419);
        exit('Sesi form tidak valid atau file terlalu besar. Kembali, muat ulang halaman, lalu coba lagi.');
    }
}

/** Honeypot anti-bot + jeda antar kiriman. Mengembalikan pesan error atau null. */
function spam_check(string $form): ?string
{
    if (input('website') !== '') {
        return 'Kiriman ditolak.';
    }
    if (time() - ($_SESSION['last_' . $form] ?? 0) < 20) {
        return 'Tunggu sebentar sebelum mengirim lagi.';
    }
    return null;
}

function honeypot(): string
{
    return '<div class="hp" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>';
}

function flash(?string $msg = null, string $type = 'ok'): ?array
{
    if ($msg !== null) {
        $_SESSION['flash'] = [$type, $msg];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/* ---------- Upload aman ---------- */
/**
 * Validasi isi file (MIME asli), beri nama acak, lalu simpan.
 * $allowed: ['mime/type' => 'ekstensi']. Mengembalikan nama file.
 */
function upload_aman(array $file, string $dir, array $allowed, int $maxSize): string
{
    $err = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    if (is_array($err) || $err !== UPLOAD_ERR_OK) {
        throw new RuntimeException(in_array($err, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) ? 'Ukuran file terlalu besar.' : 'File gagal diunggah.');
    }
    if ($file['size'] <= 0 || $file['size'] > $maxSize) {
        throw new RuntimeException('Ukuran file maksimal ' . round($maxSize / 1048576) . ' MB.');
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Upload tidak valid.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Format file tidak didukung. Gunakan: ' . strtoupper(implode(', ', $allowed)) . '.');
    }
    if (str_starts_with($mime, 'image/') && getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('File gambar rusak atau tidak valid.');
    }
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        throw new RuntimeException('Folder upload tidak bisa dibuat.');
    }
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], "$dir/$name")) {
        throw new RuntimeException('Gagal menyimpan file. Periksa izin folder.');
    }
    return $name;
}

/** Upload gambar ke assets/images/<folder>. Null bila tidak ada file dipilih. */
function upload_gambar(string $field, string $folder): ?string
{
    if (($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $name = upload_aman($_FILES[$field], APP_ROOT . "/assets/images/$folder",
        ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'], MAX_IMAGE_SIZE);
    return "assets/images/$folder/$name";
}

/** Hapus hanya file hasil upload (nama acak 32 hex), aset bawaan tidak disentuh. */
function hapus_gambar(?string $path): void
{
    if ($path && preg_match('#^assets/images/[a-z]+/[a-f0-9]{32}\.(jpg|png|webp)$#', $path) && is_file(APP_ROOT . '/' . $path)) {
        unlink(APP_ROOT . '/' . $path);
    }
}

/* ---------- Status buka ---------- */
/** Return ['buka'=>bool,'teks'=>string] atau null bila jam belum diaktifkan. */
function status_buka(?DateTimeInterface $now = null): ?array
{
    if (!JAM_BUKA_AKTIF) {
        return null;
    }
    $now ??= new DateTimeImmutable();
    $day  = (int) $now->format('N');
    $time = $now->format('H:i');
    // Shift kemarin yang melewati tengah malam
    $prev = JAM_BUKA[$day === 1 ? 7 : $day - 1] ?? null;
    if ($prev && $prev[1] < $prev[0] && $time < $prev[1]) {
        return ['buka' => true, 'teks' => "Buka sampai {$prev[1]}"];
    }
    $today = JAM_BUKA[$day] ?? null;
    if ($today) {
        [$o, $c] = $today;
        if ($time >= $o && ($c < $o || $time < $c)) {
            return ['buka' => true, 'teks' => "Buka sampai $c"];
        }
        if ($time < $o) {
            return ['buka' => false, 'teks' => "Tutup, buka pukul $o"];
        }
    }
    return ['buka' => false, 'teks' => 'Sedang tutup'];
}

/* ---------- Admin ---------- */
function require_login(): void
{
    if (empty($_SESSION['admin_id']) || time() - ($_SESSION['admin_seen'] ?? 0) > 7200) {
        unset($_SESSION['admin_id']);
        redirect(url('admin/login.php'));
    }
    $_SESSION['admin_seen'] = time();
    header('Cache-Control: no-store');
}

function admin_header(string $title): void
{
    $nav = ['index' => 'Dashboard', 'menu' => 'Menu', 'promo' => 'Promo', 'event' => 'Event & RSVP', 'lowongan' => 'Lowongan',
        'pelamar' => 'Pelamar', 'komunitas' => 'Komunitas', 'galeri' => 'Galeri'];
    $cur = basename($_SERVER['SCRIPT_NAME'], '.php'); ?>
<!doctype html>
<html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= e($title) ?> · Admin Sahaja</title>
<link rel="icon" href="<?= asset('assets/images/logo/favicon.png') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/responsive.css') ?>">
</head><body class="admin">
<aside class="admin-side">
  <a class="admin-brand" href="<?= url('admin/index.php') ?>"><img src="<?= asset('assets/images/logo/bunga.png') ?>" alt=""> Admin Sahaja</a>
  <nav>
  <?php foreach ($nav as $file => $label): ?>
    <a href="<?= url("admin/$file.php") ?>"<?= $cur === $file ? ' class="active" aria-current="page"' : '' ?>><?= e($label) ?></a>
  <?php endforeach; ?>
    <a href="<?= url('') ?>" target="_blank" rel="noopener">Lihat situs ↗</a>
  </nav>
  <form method="post" action="<?= url('admin/logout.php') ?>"><?= csrf_field() ?><button class="btn btn-outline-light">Keluar</button></form>
</aside>
<main class="admin-main">
<h1><?= e($title) ?></h1>
<?php if ($f = flash()): ?><div class="alert alert-<?= e($f[0]) ?>" role="status"><?= e($f[1]) ?></div><?php endif;
}

function admin_footer(): void
{
    echo '</main><script src="' . asset('assets/js/main.js') . '"></script></body></html>';
}

/**
 * CRUD generik untuk tabel konten admin.
 * $fields: nama => [label, tipe, opsi]  tipe: text|textarea|number|date|select|checkbox|image
 * Nama tabel & kolom hanya berasal dari kode (bukan input pengguna).
 */
function crud_handle(string $table, array $fields, string $self): ?array
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && input('crud') === $table) {
        csrf_check();
        $id  = (int) input('id');
        $old = $id ? query("SELECT * FROM $table WHERE id=?", [$id])->fetch() : null;
        try {
            if (input('aksi') === 'hapus') {
                // Kumpulkan gambar anak sebelum baris induk dihapus (cascade).
                $extra = $table === 'kategori' ? query('SELECT gambar FROM menu WHERE kategori_id=?', [$id])->fetchAll(PDO::FETCH_COLUMN) : [];
                query("DELETE FROM $table WHERE id=?", [$id]);
                array_map('hapus_gambar', [...$extra, $old['gambar'] ?? null]);
                flash('Data dihapus.');
                redirect($self);
            }
            $data = [];
            $newImage = null;
            foreach ($fields as $name => $f) {
                [$label, $type] = $f;
                $req = $f['required'] ?? false;
                if ($type === 'image') {
                    continue;
                }
                $v = $type === 'checkbox' ? (isset($_POST[$name]) ? 1 : 0) : input($name, $type === 'textarea' ? 5000 : 150);
                if ($req && $v === '') {
                    throw new RuntimeException("$label wajib diisi.");
                }
                if ($type === 'number') {
                    if ($v !== '' && (!ctype_digit($v) || (int) $v > 100000000)) {
                        throw new RuntimeException("$label harus angka 0 sampai 100.000.000.");
                    }
                    $v = $v === '' ? (array_key_exists('empty', $f) ? $f['empty'] : 0) : (int) $v;
                }
                if ($type === 'date') {
                    if ($v !== '' && !valid_date($v)) {
                        throw new RuntimeException("$label tidak valid.");
                    }
                    $v = $v === '' ? null : $v;
                }
                if ($type === 'select' && !array_key_exists($v, $f['options'])) {
                    throw new RuntimeException("Pilihan $label tidak valid.");
                }
                $data[$name] = $v;
            }
            // Upload paling akhir supaya tidak ada file yatim saat validasi gagal.
            foreach ($fields as $name => $f) {
                if ($f[1] !== 'image') {
                    continue;
                }
                $newImage = upload_gambar($name, $f['folder']);
                if ($newImage) {
                    $data[$name] = $newImage;
                } elseif (!$old && !empty($f['required'])) {
                    throw new RuntimeException("{$f[0]} wajib diunggah.");
                }
            }
            if ($old) {
                $set = implode(',', array_map(fn($c) => "$c=?", array_keys($data)));
                query("UPDATE $table SET $set WHERE id=?", [...array_values($data), $id]);
                if ($newImage) {
                    hapus_gambar($old['gambar']);
                }
            } else {
                $cols = implode(',', array_keys($data));
                $marks = implode(',', array_fill(0, count($data), '?'));
                query("INSERT INTO $table ($cols) VALUES ($marks)", array_values($data));
            }
            flash('Data tersimpan.');
        } catch (RuntimeException $ex) {
            if ($ex instanceof PDOException) {
                error_log($ex->getMessage());
                flash('Gagal menyimpan ke database.', 'err');
            } else {
                flash($ex->getMessage(), 'err');
            }
        }
        redirect($self);
    }
    $edit = (int) ($_GET[$table === 'kategori' ? 'edit_kategori' : 'edit'] ?? 0);
    return $edit ? (query("SELECT * FROM $table WHERE id=?", [$edit])->fetch() ?: null) : null;
}

function crud_form(string $table, array $fields, ?array $row, string $self, string $judul): void
{ ?>
<section class="panel">
<h2><?= $row ? 'Ubah ' : 'Tambah ' ?><?= e($judul) ?></h2>
<form method="post" action="<?= e($self) ?>" enctype="multipart/form-data" class="form-grid">
<?= csrf_field() ?><input type="hidden" name="crud" value="<?= e($table) ?>"><input type="hidden" name="id" value="<?= (int) ($row['id'] ?? 0) ?>">
<?php foreach ($fields as $name => $f): [$label, $type] = $f;
    $val = $row[$name] ?? ($f['default'] ?? '');
    $req = !empty($f['required']); ?>
  <?php if ($type === 'checkbox'): ?>
  <label class="check"><input type="checkbox" name="<?= $name ?>" <?= $val ? 'checked' : '' ?>> <?= e($label) ?></label>
  <?php else: ?>
  <label class="<?= $type === 'textarea' ? 'full' : '' ?>"><?= e($label) ?><?= $req ? ' *' : '' ?>
    <?php if ($type === 'textarea'): ?><textarea name="<?= $name ?>" rows="4" <?= $req ? 'required' : '' ?>><?= e($val) ?></textarea>
    <?php elseif ($type === 'select'): ?><select name="<?= $name ?>"><?php foreach ($f['options'] as $k => $o): ?><option value="<?= e($k) ?>" <?= (string) $k === (string) $val ? 'selected' : '' ?>><?= e($o) ?></option><?php endforeach; ?></select>
    <?php elseif ($type === 'image'): ?><input type="file" name="<?= $name ?>" accept="image/jpeg,image/png,image/webp" <?= $req && !$row ? 'required' : '' ?>><small>JPG/PNG/WebP, maks <?= MAX_IMAGE_SIZE / 1048576 ?> MB<?= $row ? '. Kosongkan jika tidak diganti.' : '' ?></small>
    <?php else: ?><input type="<?= $type ?>" name="<?= $name ?>" value="<?= e($val) ?>" <?= $type === 'number' ? 'min="0"' : 'maxlength="150"' ?> <?= $req ? 'required' : '' ?>><?php endif; ?>
  </label>
  <?php endif; ?>
<?php endforeach; ?>
  <div class="full actions"><button class="btn">Simpan</button><?php if ($row): ?> <a class="btn btn-ghost" href="<?= e($self) ?>">Batal</a><?php endif; ?></div>
</form>
</section>
<?php }

function crud_actions(string $table, int $id, string $self, string $konfirmasi = 'Hapus data ini?', string $param = 'edit'): string
{
    return '<div class="actions"><a class="btn btn-small btn-ghost" href="' . e("$self?$param=$id") . '">Ubah</a>'
        . '<form method="post" action="' . e($self) . '" data-confirm="' . e($konfirmasi) . '">' . csrf_field()
        . '<input type="hidden" name="crud" value="' . e($table) . '"><input type="hidden" name="id" value="' . $id . '">'
        . '<input type="hidden" name="aksi" value="hapus"><button class="btn btn-small btn-danger">Hapus</button></form></div>';
}
