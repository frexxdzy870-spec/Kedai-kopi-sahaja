<?php
require_once __DIR__ . '/../includes/functions.php';
$kegiatan  = query('SELECT * FROM komunitas ORDER BY tanggal DESC, id DESC')->fetchAll();
$mendatang = query('SELECT * FROM event WHERE tanggal>=? ORDER BY tanggal LIMIT 5', [date('Y-m-d')])->fetchAll();
$pageTitle = 'Komunitas Sahaja Bersama'; $active = 'komunitas';
require __DIR__ . '/../includes/header.php';
?>
<section class="page-head"><div class="container center"><h1 class="script">#Sahaja<br><span>bersama.</span></h1><p>Semuanya diundang, semua boleh datang.</p></div></section>
<section class="section"><div class="container split">
  <div><p class="kicker">Profil</p><h2>Komunitas Sahaja Bersama</h2>
    <p>Ruang bertemu di Kedai Kopi Sahaja untuk ngobrol, berbagi cerita, dan belajar bareng. Tidak perlu jadi serba bisa. Cukup datang sebagai diri sendiri.</p></div>
  <div><p class="kicker">Agenda</p><h2>Yang akan datang</h2>
    <?php if ($mendatang): ?><ul><?php foreach ($mendatang as $m): ?><li><a href="<?= url('event') ?>#event-<?= (int) $m['id'] ?>"><?= e($m['judul']) ?></a> · <?= e(tanggal_id($m['tanggal'])) ?></li><?php endforeach; ?></ul>
    <?php else: ?><p>Agenda berikutnya akan diumumkan di Instagram @<?= e(INSTAGRAM) ?>.</p><?php endif; ?></div>
</div></section>
<section class="section section-cream"><div class="container">
  <h2>Cara bergabung</h2>
  <ol class="steps">
    <li><strong>Pantau agenda</strong><span>Cek halaman Event atau Instagram kami.</span></li>
    <li><strong>Sapa kami</strong><span>Kirim pesan WhatsApp supaya kami bisa kabari kegiatan berikutnya.</span></li>
    <li><strong>Datang saja</strong><span>Ajak teman, pesan minuman, dan ikut ngobrol.</span></li>
  </ol>
  <a class="btn" target="_blank" rel="noopener" href="<?= e(wa_link('Halo, saya ingin bergabung dengan komunitas #SahajaBersama.')) ?>">Gabung via WhatsApp</a>
</div></section>
<section class="section"><div class="container">
  <h2>Dokumentasi kegiatan</h2>
  <?php if (!$kegiatan): ?><p>Belum ada dokumentasi.</p><?php endif; ?>
  <div class="grid cards">
    <?php foreach ($kegiatan as $k): ?>
    <article class="card">
      <?php if ($k['gambar']): ?><button class="zoom" data-zoom="<?= asset($k['gambar']) ?>"><img src="<?= asset($k['gambar']) ?>" alt="<?= e($k['judul']) ?>" loading="lazy"></button><?php endif; ?>
      <div class="card-body"><h3><?= e($k['judul']) ?></h3><?php if ($k['tanggal']): ?><p class="kicker"><?= e(tanggal_id($k['tanggal'])) ?></p><?php endif; ?><p><?= nl2br(e($k['deskripsi'])) ?></p></div>
    </article>
    <?php endforeach; ?>
  </div>
</div></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
