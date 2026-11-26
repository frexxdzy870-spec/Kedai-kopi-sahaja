<?php
require_once __DIR__ . '/../includes/functions.php';
$kategori = query('SELECT * FROM kategori ORDER BY urutan, id')->fetchAll();
$items    = query('SELECT m.*, k.nama AS kategori FROM menu m JOIN kategori k ON k.id=m.kategori_id ORDER BY k.urutan, k.id, m.nama')->fetchAll();
$pageTitle = 'Menu'; $active = 'menu';
require __DIR__ . '/../includes/header.php';
?>
<section class="page-head"><div class="container"><h1>Menu</h1><p>Pilih, masukkan ke daftar pesanan, lalu kirim lewat WhatsApp.</p></div></section>
<section class="section"><div class="container">
  <div class="toolbar">
    <div class="chips" data-filter-group>
      <button class="chip active" data-filter="semua" aria-pressed="true">Semua</button>
      <?php foreach ($kategori as $k): ?><button class="chip" data-filter="<?= (int) $k['id'] ?>" aria-pressed="false"><?= e($k['nama']) ?></button><?php endforeach; ?>
    </div>
    <input type="search" class="search" data-search placeholder="Cari menu..." aria-label="Cari menu">
  </div>
  <div class="grid cards">
    <?php foreach ($items as $m): ?>
    <article class="card <?= $m['tersedia'] ? '' : 'soldout' ?>" data-item data-category="<?= (int) $m['kategori_id'] ?>" data-text="<?= e(mb_strtolower($m['nama'] . ' ' . $m['deskripsi'])) ?>">
      <img src="<?= asset($m['gambar']) ?>" alt="<?= e($m['nama']) ?>" loading="lazy">
      <div class="card-body">
        <p class="kicker"><?= e($m['kategori']) ?></p>
        <h3><?= e($m['nama']) ?></h3><p><?= e($m['deskripsi']) ?></p>
        <div class="card-foot">
          <span class="price"><?= rupiah($m['harga']) ?></span>
          <?php if ($m['tersedia']): ?><button class="btn btn-small" data-order-add="<?= e($m['nama']) ?>" data-price="<?= e($m['harga']) ?>">+ Pesan</button>
          <?php else: ?><span class="badge">Habis</span><?php endif; ?>
        </div>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <p class="empty" data-empty <?= $items ? 'hidden' : '' ?>>Belum ada menu yang cocok.</p>
</div></section>

<button class="order-fab btn btn-yellow" data-order-open hidden>Pesanan (<span data-order-count>0</span>)</button>
<div class="modal" id="order-modal" role="dialog" aria-modal="true" aria-labelledby="order-title" hidden>
  <div class="modal-box">
    <button class="modal-close" data-close aria-label="Tutup">×</button>
    <h2 id="order-title">Pesananmu</h2>
    <ul class="order-list" data-order-list></ul>
    <p class="price" data-order-total></p>
    <form data-order-form class="form-grid">
      <label class="full">Nama *<input name="nama" required maxlength="100"></label>
      <label class="full">Meja / cara ambil<input name="meja" maxlength="60" placeholder="Meja 3, atau ambil sendiri"></label>
      <label class="full">Catatan<textarea name="catatan" rows="2" maxlength="300"></textarea></label>
      <button class="btn full">Kirim via WhatsApp</button>
    </form>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
