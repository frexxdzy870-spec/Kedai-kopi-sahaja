<?php
require_once __DIR__ . '/../includes/functions.php';
$error = null; $old = [];
$lowongan = query('SELECT * FROM lowongan WHERE buka=1 ORDER BY id DESC')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach (['lowongan_id' => 10, 'nama' => 100, 'whatsapp' => 25, 'email' => 120, 'pesan' => 1500, 'portofolio_url' => 255] as $k => $max) {
        $old[$k] = input($k, $max);
    }
    $job = query('SELECT * FROM lowongan WHERE id=? AND buka=1', [(int) $old['lowongan_id']])->fetch();
    $wa  = normal_wa($old['whatsapp']);
    $link = $old['portofolio_url'];
    $error = spam_check('lamaran')
        ?? (!$job ? 'Posisi ini sudah tidak menerima lamaran.' : null)
        ?? ($old['nama'] === '' ? 'Nama wajib diisi.' : null)
        ?? (!$wa ? 'Nomor WhatsApp tidak valid (contoh 081234567890).' : null)
        ?? ($old['email'] !== '' && !filter_var($old['email'], FILTER_VALIDATE_EMAIL) ? 'Email tidak valid.' : null)
        ?? ($link !== '' && !(filter_var($link, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $link)) ? 'Link portofolio harus diawali http:// atau https://' : null)
        ?? (empty($_POST['setuju']) ? 'Centang persetujuan penggunaan data.' : null);
    if (!$error) {
        $cv = null;
        try {
            $cv = upload_aman($_FILES['cv'] ?? [], APP_ROOT . '/assets/uploads/cv', ['application/pdf' => 'pdf'], MAX_CV_SIZE);
            query('INSERT INTO pelamar (lowongan_id,posisi,nama,whatsapp,email,pesan,portofolio_url,cv_file,status,dibuat) VALUES (?,?,?,?,?,?,?,?,?,?)',
                [$job['id'], $job['posisi'], $old['nama'], $wa, $old['email'] ?: null, $old['pesan'], $link ?: null, $cv, 'baru', date('Y-m-d H:i:s')]);
            $_SESSION['last_lamaran'] = time();
            $_SESSION['lamaran_ok'] = ['nama' => $old['nama'], 'posisi' => $job['posisi']];
            redirect(url('lowongan?terkirim=1'));
        } catch (RuntimeException $ex) {
            if ($cv) { @unlink(APP_ROOT . "/assets/uploads/cv/$cv"); }
            if ($ex instanceof PDOException) { error_log($ex->getMessage()); $error = 'Lamaran gagal disimpan. Coba lagi nanti.'; }
            else { $error = $ex->getMessage(); }
        }
    }
}
$sukses = isset($_GET['terkirim']) ? ($_SESSION['lamaran_ok'] ?? null) : null;
$pageTitle = 'Lowongan Kerja'; $active = 'lowongan'; $scripts = ['lamaran.js'];
require __DIR__ . '/../includes/header.php';
?>
<section class="page-head"><div class="container"><h1>Info loker!</h1><p>Mau ikut menyajikan hal-hal kecil bareng kami?</p></div></section>
<section class="section"><div class="container">
<?php if ($sukses): ?>
  <div class="alert alert-ok" role="status">
    <h2>Lamaran terkirim 🌻</h2>
    <p>Terima kasih, <?= e($sukses['nama']) ?>. Lamaran untuk <?= e($sukses['posisi']) ?> sudah kami terima.</p>
    <button class="btn" data-wa="lamaran" data-nama="<?= e($sukses['nama']) ?>" data-posisi="<?= e($sukses['posisi']) ?>">Konfirmasi via WhatsApp</button>
  </div>
<?php endif; ?>
<?php if ($error): ?><div class="alert alert-err" role="alert"><?= e($error) ?> Pilih ulang file CV sebelum mengirim lagi.</div><?php endif; ?>
<?php if (!$lowongan): ?>
  <p class="empty">Saat ini belum ada lowongan yang dibuka. Pantau Instagram @<?= e(INSTAGRAM) ?>.</p>
<?php else: ?>
  <?php foreach ($lowongan as $l): ?>
  <article class="job">
    <?php if ($l['gambar']): ?><button class="zoom" data-zoom="<?= asset($l['gambar']) ?>"><img src="<?= asset($l['gambar']) ?>" alt="Poster lowongan <?= e($l['posisi']) ?>"></button><?php endif; ?>
    <div><p class="kicker"><?= e($l['tipe']) ?></p><h2><?= e($l['posisi']) ?></h2><p><?= nl2br(e($l['deskripsi'])) ?></p>
      <h3>Kualifikasi</h3><ul><?php foreach (array_filter(array_map('trim', explode("\n", (string) $l['kualifikasi']))) as $q): ?><li><?= e($q) ?></li><?php endforeach; ?></ul>
      <a class="btn" href="#lamar" data-pilih="<?= (int) $l['id'] ?>">Lamar posisi ini</a></div>
  </article>
  <?php endforeach; ?>

  <section class="panel form-narrow" id="lamar">
    <h2>Form lamaran</h2>
    <form method="post" action="<?= url('lowongan') ?>#lamar" enctype="multipart/form-data" class="form-grid" data-lamaran data-max="<?= MAX_CV_SIZE ?>">
      <?= csrf_field() . honeypot() ?>
      <label class="full">Posisi *<select name="lowongan_id" required><?php foreach ($lowongan as $l): ?><option value="<?= (int) $l['id'] ?>" <?= ($old['lowongan_id'] ?? '') == $l['id'] ? 'selected' : '' ?>><?= e($l['posisi']) ?></option><?php endforeach; ?></select></label>
      <label class="full">Nama lengkap *<input name="nama" required maxlength="100" value="<?= e($old['nama'] ?? '') ?>"></label>
      <label>WhatsApp *<input name="whatsapp" type="tel" required value="<?= e($old['whatsapp'] ?? '') ?>"></label>
      <label>Email<input name="email" type="email" maxlength="120" value="<?= e($old['email'] ?? '') ?>"></label>
      <label class="full">Ceritakan dirimu<textarea name="pesan" rows="4" maxlength="1500"><?= e($old['pesan'] ?? '') ?></textarea></label>
      <label class="full">CV (PDF, maks <?= MAX_CV_SIZE / 1048576 ?> MB) *<input type="file" name="cv" accept="application/pdf,.pdf" required><small>Portofolio bisa digabung di PDF ini.</small></label>
      <label class="full">Link portofolio (opsional)<input name="portofolio_url" type="url" maxlength="255" placeholder="https://" value="<?= e($old['portofolio_url'] ?? '') ?>"></label>
      <label class="check full"><input type="checkbox" name="setuju" value="1" required> Saya setuju data ini disimpan dan dipakai untuk proses rekrutmen.</label>
      <p class="form-error full" data-error role="alert" hidden></p>
      <button class="btn full">Kirim lamaran</button>
    </form>
  </section>
<?php endif; ?>
</div></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
