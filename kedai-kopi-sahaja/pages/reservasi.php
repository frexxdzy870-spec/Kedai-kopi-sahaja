<?php
require_once __DIR__ . '/../includes/functions.php';
$jenis = ['meja' => 'Meja', 'ruang-diskusi' => 'Ruang diskusi', 'event' => 'Acara / event'];
$error = null; $old = [];
$today = date('Y-m-d');
$maxDate = date('Y-m-d', strtotime('+6 months'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach (['nama' => 100, 'whatsapp' => 25, 'jenis' => 30, 'tanggal' => 10, 'jam' => 5, 'jumlah' => 4, 'catatan' => 500] as $k => $max) {
        $old[$k] = input($k, $max);
    }
    $wa = normal_wa($old['whatsapp']);
    $n  = (int) $old['jumlah'];
    $error = spam_check('reservasi')
        ?? ($old['nama'] === '' ? 'Nama wajib diisi.' : null)
        ?? (!$wa ? 'Nomor WhatsApp tidak valid (contoh 081234567890).' : null)
        ?? (!isset($jenis[$old['jenis']]) ? 'Pilih jenis reservasi.' : null)
        ?? (!valid_date($old['tanggal']) || $old['tanggal'] < $today || $old['tanggal'] > $maxDate ? 'Pilih tanggal mulai hari ini sampai 6 bulan ke depan.' : null)
        ?? (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $old['jam']) ? 'Jam tidak valid.' : null)
        ?? ($old['tanggal'] === $today && $old['jam'] <= date('H:i') ? 'Jam tersebut sudah lewat.' : null)
        ?? ($n < 1 || $n > 100 ? 'Jumlah orang 1 sampai 100.' : null);
    if (!$error) {
        query('INSERT INTO reservasi (nama,whatsapp,jenis,tanggal,jam,jumlah,catatan,status,dibuat) VALUES (?,?,?,?,?,?,?,?,?)',
            [$old['nama'], $wa, $old['jenis'], $old['tanggal'], $old['jam'], $n, $old['catatan'], 'baru', date('Y-m-d H:i:s')]);
        $_SESSION['last_reservasi'] = time();
        $_SESSION['reservasi_ok'] = ['nama' => $old['nama'], 'jenis' => $jenis[$old['jenis']], 'tanggal' => tanggal_id($old['tanggal']), 'jam' => $old['jam'], 'jumlah' => $n, 'catatan' => $old['catatan']];
        redirect(url('reservasi?terkirim=1'));
    }
}
$ok = isset($_GET['terkirim']) ? ($_SESSION['reservasi_ok'] ?? null) : null;
$pageTitle = 'Reservasi'; $active = 'reservasi';
require __DIR__ . '/../includes/header.php';
?>
<section class="page-head"><div class="container"><h1>Reservasi</h1><p>Booking meja, ruang diskusi, atau tempat untuk acara.</p></div></section>
<section class="section"><div class="container form-narrow">
<?php if ($ok): ?>
  <div class="alert alert-ok" role="status">
    <h2>Permintaan tercatat 🌻</h2>
    <p>Satu langkah lagi: kirim konfirmasi lewat WhatsApp. Reservasi berlaku setelah dibalas oleh tim kedai.</p>
    <button class="btn" data-wa="reservasi" <?php foreach ($ok as $k => $v): ?>data-<?= $k ?>="<?= e($v) ?>" <?php endforeach; ?>>Konfirmasi via WhatsApp</button>
  </div>
<?php endif; ?>
<?php if ($error): ?><div class="alert alert-err" role="alert"><?= e($error) ?></div><?php endif; ?>
  <div class="panel"><form method="post" action="<?= url('reservasi') ?>" class="form-grid">
    <?= csrf_field() . honeypot() ?>
    <label class="full">Nama *<input name="nama" required maxlength="100" value="<?= e($old['nama'] ?? '') ?>"></label>
    <label>WhatsApp *<input name="whatsapp" type="tel" required value="<?= e($old['whatsapp'] ?? '') ?>"></label>
    <label>Jenis *<select name="jenis"><?php foreach ($jenis as $k => $v): ?><option value="<?= $k ?>" <?= ($old['jenis'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></label>
    <label>Tanggal *<input type="date" name="tanggal" required min="<?= $today ?>" max="<?= $maxDate ?>" value="<?= e($old['tanggal'] ?? '') ?>"></label>
    <label>Jam *<input type="time" name="jam" required value="<?= e($old['jam'] ?? '') ?>"></label>
    <label class="full">Jumlah orang *<input type="number" name="jumlah" min="1" max="100" required value="<?= e($old['jumlah'] ?? '2') ?>"></label>
    <label class="full">Catatan<textarea name="catatan" rows="3" maxlength="500"><?= e($old['catatan'] ?? '') ?></textarea></label>
    <button class="btn full">Kirim permintaan</button>
  </form></div>
</div></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
