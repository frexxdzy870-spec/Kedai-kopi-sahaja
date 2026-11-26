<?php
require_once __DIR__ . '/../includes/functions.php';
$galeri = query('SELECT * FROM galeri ORDER BY urutan, id DESC')->fetchAll();
$pageTitle = 'Tentang Kami'; $active = 'tentang-kami';
require __DIR__ . '/../includes/header.php';
?>
<section class="page-head"><div class="container"><h1>Tentang kami</h1><p>Kedai kopi sahaja.</p></div></section>
<!-- Teks cerita di bawah adalah draf. Sesuaikan dengan cerita asli kedai. -->
<section class="section"><div class="container split">
  <div><p class="kicker">Cerita</p><h2>Berawal dari tempat yang dibangun pelan-pelan</h2>
    <p>Sebelum pintunya dibuka, Kedai Kopi Sahaja adalah ruang yang masih penuh semen, kayu, dan rencana. Sedikit demi sedikit ia dibenahi menjadi tempat singgah di Cukir, Jombang.</p>
    <p>Harapannya sederhana: ada tempat untuk duduk tenang, menikmati secangkir kopi, dan bertemu orang-orang baik.</p></div>
  <button class="zoom poster" data-zoom="<?= asset('assets/images/gallery/sebelum-dibuka.jpg') ?>"><img src="<?= asset('assets/images/gallery/sebelum-dibuka.jpg') ?>" alt="Bangunan kedai saat masih direnovasi" loading="lazy"></button>
</div></section>
<section class="section section-maroon"><div class="container">
  <p class="kicker">Filosofi</p><h2>Mengapa “Sahaja”?</h2>
  <p class="lead">Bersahaja berarti sederhana dan apa adanya.</p>
  <div class="grid values">
    <div><h3>Tenang</h3><p>“Nanti juga baik, tenang sahaja.”</p></div>
    <div><h3>Hal kecil</h3><p>“Tolong, hidup untuk hal-hal kecil.”</p></div>
    <div><h3>Bersama</h3><p>“Semuanya diundang, semua boleh datang.”</p></div>
  </div>
</div></section>
<section class="section"><div class="container center">
  <p class="kicker">Tim</p><h2>Orang-orang di balik Sahaja</h2>
  <p><strong>Emirza Haqqy</strong><br>Founder #SahajaBersama</p>
</div></section>
<section class="section section-cream" id="galeri"><div class="container">
  <p class="kicker">Galeri</p><h2>Suasana &amp; cerita</h2>
  <div class="grid gallery">
    <?php foreach ($galeri as $g): ?><figure><button class="zoom" data-zoom="<?= asset($g['gambar']) ?>"><img src="<?= asset($g['gambar']) ?>" alt="<?= e($g['judul']) ?>" loading="lazy"></button><figcaption><?= e($g['judul']) ?></figcaption></figure><?php endforeach; ?>
  </div>
</div></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
