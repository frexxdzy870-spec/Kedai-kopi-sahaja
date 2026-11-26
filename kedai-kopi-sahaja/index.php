<?php
require_once __DIR__ . '/includes/functions.php';
$today    = date('Y-m-d');
$andalan  = query('SELECT * FROM menu WHERE andalan=1 AND tersedia=1 ORDER BY id LIMIT 6')->fetchAll();
$promos   = query('SELECT * FROM promo WHERE aktif=1 AND (mulai IS NULL OR mulai<=?) AND (selesai IS NULL OR selesai>=?) ORDER BY id DESC', [$today, $today])->fetchAll();
$events   = query('SELECT * FROM event WHERE tanggal>=? ORDER BY tanggal LIMIT 3', [$today])->fetchAll();
$arsip    = $events ? [] : query('SELECT * FROM event ORDER BY tanggal DESC LIMIT 1')->fetchAll();
$galeri   = query('SELECT * FROM galeri ORDER BY urutan, id DESC LIMIT 4')->fetchAll();
$status   = status_buka();
$active   = 'home';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div class="container hero-grid">
    <div>
      <?php if ($status): ?><p class="badge <?= $status['buka'] ? 'badge-open' : '' ?>">● <?= e($status['teks']) ?></p><?php endif; ?>
      <h1><img src="<?= asset('assets/images/logo/logo-stack.png') ?>" alt="Kedai Kopi Sahaja" class="hero-logo"></h1>
      <p class="hero-quote">nanti juga baik,<br>tenang <em>sahaja.</em></p>
      <p>Mampir ke Cukir, Jombang. Pesan secangkir, duduk sebentar, dan rayakan hal-hal kecil.</p>
      <div class="actions">
        <a class="btn btn-yellow" href="<?= url('menu') ?>">Lihat Menu</a>
        <a class="btn btn-outline-light" href="<?= url('reservasi') ?>">Reservasi Tempat</a>
      </div>
    </div>
    <div class="hero-art">
      <img src="<?= asset('assets/images/hero/barista.jpg') ?>" alt="Barista menyaring racikan di balik bar" class="hero-photo">
      <img src="<?= asset('assets/images/logo/bunga.png') ?>" alt="" class="hero-flower">
    </div>
  </div>
</section>

<?php if ($promos): ?>
<section class="section">
  <div class="container">
    <?php foreach ($promos as $p): ?>
    <article class="promo">
      <?php if ($p['gambar']): ?><img src="<?= asset($p['gambar']) ?>" alt="<?= e($p['judul']) ?>"><?php endif; ?>
      <div><p class="kicker">Promo</p><h2><?= e($p['judul']) ?></h2><p><?= nl2br(e($p['deskripsi'])) ?></p>
      <?php if ($p['selesai']): ?><small>Berlaku sampai <?= e(tanggal_id($p['selesai'])) ?></small><?php endif; ?></div>
    </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="section">
  <div class="container">
    <p class="kicker">Dari bar &amp; dapur</p>
    <h2>Menu andalan</h2>
    <div class="grid cards">
      <?php foreach ($andalan as $m): ?>
      <article class="card">
        <img src="<?= asset($m['gambar']) ?>" alt="<?= e($m['nama']) ?>" loading="lazy">
        <div class="card-body"><h3><?= e($m['nama']) ?></h3><p><?= e($m['deskripsi']) ?></p><p class="price"><?= rupiah($m['harga']) ?></p></div>
      </article>
      <?php endforeach; ?>
    </div>
    <p class="center"><a class="btn" href="<?= url('menu') ?>">Semua menu</a></p>
  </div>
</section>

<section class="section section-maroon">
  <div class="container split">
    <div>
      <p class="kicker">Agenda kedai</p>
      <?php if ($events): ?>
        <h2>Event mendatang</h2>
        <ul class="event-list">
          <?php foreach ($events as $ev): ?><li><a href="<?= url('event') ?>#event-<?= (int) $ev['id'] ?>"><strong><?= e($ev['judul']) ?></strong><span><?= e(tanggal_id($ev['tanggal'])) ?> · <?= e($ev['jam']) ?></span></a></li><?php endforeach; ?>
        </ul>
      <?php else: ?>
        <h2>Agenda baru sedang disiapkan</h2>
        <p>Belum ada jadwal mendatang. Sambil menunggu, intip obrolan yang pernah kami adakan.</p>
      <?php endif; ?>
      <a class="btn btn-yellow" href="<?= url('event') ?>">Lihat event</a>
    </div>
    <?php $poster = ($events[0] ?? $arsip[0] ?? null); if ($poster && $poster['gambar']): ?>
    <button class="poster zoom" data-zoom="<?= asset($poster['gambar']) ?>"><img src="<?= asset($poster['gambar']) ?>" alt="Poster <?= e($poster['judul']) ?>" loading="lazy"></button>
    <?php endif; ?>
  </div>
</section>

<section class="section">
  <div class="container split">
    <img src="<?= asset('assets/images/logo/sahaja-bersama.jpg') ?>" alt="#SahajaBersama" class="rounded" loading="lazy">
    <div>
      <p class="kicker">Komunitas</p>
      <h2>Semuanya diundang, semua boleh datang.</h2>
      <p>#SahajaBersama adalah ruang untuk ngobrol, belajar, dan bertumbuh bareng di Kedai Kopi Sahaja.</p>
      <a class="btn" href="<?= url('komunitas') ?>">Kenalan dengan komunitas</a>
    </div>
  </div>
</section>

<?php if ($galeri): ?>
<section class="section section-cream">
  <div class="container">
    <p class="kicker">Suasana</p><h2>Cerita dari kedai</h2>
    <div class="grid gallery">
      <?php foreach ($galeri as $g): ?><button class="zoom" data-zoom="<?= asset($g['gambar']) ?>"><img src="<?= asset($g['gambar']) ?>" alt="<?= e($g['judul']) ?>" loading="lazy"></button><?php endforeach; ?>
    </div>
    <p class="center"><a class="btn" href="<?= url('tentang-kami') ?>#galeri">Lihat galeri</a></p>
  </div>
</section>
<?php endif; ?>

<section class="section" id="lokasi">
  <div class="container split">
    <div>
      <p class="kicker">Lokasi</p><h2>Temukan kami</h2>
      <p><?= e(ALAMAT) ?></p>
      <div class="actions">
        <a class="btn" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&amp;query=<?= rawurlencode(MAPS_QUERY) ?>">Buka Google Maps</a>
        <a class="btn btn-ghost" target="_blank" rel="noopener" href="<?= e(wa_link('Halo, saya mau tanya arah ke Kedai Kopi Sahaja.')) ?>">Tanya lewat WA</a>
      </div>
    </div>
    <iframe class="map" title="Peta lokasi Kedai Kopi Sahaja" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://maps.google.com/maps?q=<?= rawurlencode(ALAMAT) ?>&amp;output=embed"></iframe>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
