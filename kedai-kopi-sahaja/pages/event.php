<?php
require_once __DIR__ . '/../includes/functions.php';
$today = date('Y-m-d');
$error = null; $old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $old = ['event_id' => (int) input('event_id'), 'nama' => input('nama', 100), 'whatsapp' => input('whatsapp', 25), 'jumlah' => (int) input('jumlah')];
    $ev = query('SELECT * FROM event WHERE id=? AND rsvp_buka=1 AND tanggal>=?', [$old['event_id'], $today])->fetch();
    $wa = normal_wa($old['whatsapp']);
    $error = spam_check('rsvp')
        ?? (!$ev ? 'Pendaftaran event ini sudah ditutup.' : null)
        ?? ($old['nama'] === '' ? 'Nama wajib diisi.' : null)
        ?? (!$wa ? 'Nomor WhatsApp tidak valid (contoh 081234567890).' : null)
        ?? ($old['jumlah'] < 1 || $old['jumlah'] > 10 ? 'Jumlah orang 1 sampai 10.' : null);
    if (!$error) {
        query('INSERT INTO rsvp (event_id,nama,whatsapp,jumlah,dibuat) VALUES (?,?,?,?,?)', [$ev['id'], $old['nama'], $wa, $old['jumlah'], date('Y-m-d H:i:s')]);
        $_SESSION['last_rsvp'] = time();
        flash("RSVP untuk \"{$ev['judul']}\" sudah tercatat. Sampai jumpa!");
        redirect(url('event'));
    }
}

$mendatang = query('SELECT * FROM event WHERE tanggal>=? ORDER BY tanggal', [$today])->fetchAll();
$arsip     = query('SELECT * FROM event WHERE tanggal<? ORDER BY tanggal DESC', [$today])->fetchAll();
$jenis     = ['event' => 'Event', 'diskusi' => 'Diskusi', 'workshop' => 'Workshop', 'live-music' => 'Live Music'];
$pageTitle = 'Event'; $active = 'event';
require __DIR__ . '/../includes/header.php';
?>
<section class="page-head"><div class="container"><h1>Event &amp; agenda</h1><p>Diskusi, workshop, dan live music di kedai.</p></div></section>
<section class="section"><div class="container">
  <?php if ($f = flash()): ?><div class="alert alert-<?= e($f[0]) ?>" role="status"><?= e($f[1]) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-err" role="alert"><?= e($error) ?></div><?php endif; ?>
  <div class="chips" data-filter-group>
    <button class="chip active" data-filter="semua" aria-pressed="true">Semua</button>
    <?php foreach ($jenis as $k => $v): ?><button class="chip" data-filter="<?= $k ?>" aria-pressed="false"><?= $v ?></button><?php endforeach; ?>
  </div>
  <?php foreach (['Mendatang' => $mendatang, 'Arsip' => $arsip] as $judul => $list): ?>
  <h2><?= $judul ?></h2>
  <?php if (!$list): ?><p><?= $judul === 'Mendatang' ? 'Belum ada jadwal baru. Pantau Instagram @' . e(INSTAGRAM) . ' ya.' : 'Belum ada arsip.' ?></p><?php endif; ?>
  <div class="grid events">
    <?php foreach ($list as $ev): ?>
    <article class="card" id="event-<?= (int) $ev['id'] ?>" data-item data-category="<?= e($ev['jenis']) ?>">
      <?php if ($ev['gambar']): ?><button class="zoom" data-zoom="<?= asset($ev['gambar']) ?>"><img src="<?= asset($ev['gambar']) ?>" alt="Poster <?= e($ev['judul']) ?>" loading="lazy"></button><?php endif; ?>
      <div class="card-body">
        <p class="kicker"><?= e($jenis[$ev['jenis']] ?? 'Event') ?></p>
        <h3><?= e($ev['judul']) ?></h3>
        <p><strong><?= e(tanggal_id($ev['tanggal'])) ?></strong> · <?= e($ev['jam']) ?></p>
        <p><?= nl2br(e($ev['deskripsi'])) ?></p>
        <?php if ($judul === 'Mendatang' && $ev['rsvp_buka']): $mine = ($old['event_id'] ?? 0) === (int) $ev['id']; ?>
        <details <?= $mine ? 'open' : '' ?>><summary class="btn btn-small">Daftar / RSVP</summary>
          <form method="post" action="<?= url('event') ?>" class="form-grid">
            <?= csrf_field() . honeypot() ?><input type="hidden" name="event_id" value="<?= (int) $ev['id'] ?>">
            <label class="full">Nama *<input name="nama" required maxlength="100" value="<?= $mine ? e($old['nama']) : '' ?>"></label>
            <label>WhatsApp *<input name="whatsapp" type="tel" required value="<?= $mine ? e($old['whatsapp']) : '' ?>"></label>
            <label>Jumlah orang<input name="jumlah" type="number" min="1" max="10" value="1" required></label>
            <button class="btn full">Kirim RSVP</button>
          </form>
        </details>
        <?php endif; ?>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <?php endforeach; ?>
  <p class="empty" data-empty hidden>Tidak ada event di kategori ini.</p>
</div></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
