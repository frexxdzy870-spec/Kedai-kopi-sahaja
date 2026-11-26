<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
$self = url('admin/index.php');
$statusList = ['baru' => 'Baru', 'dikonfirmasi' => 'Dikonfirmasi', 'selesai' => 'Selesai', 'batal' => 'Batal'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int) input('id');
    if (input('aksi') === 'hapus') {
        query('DELETE FROM reservasi WHERE id=?', [$id]);
        flash('Reservasi dihapus.');
    } elseif (isset($statusList[input('status')])) {
        query('UPDATE reservasi SET status=? WHERE id=?', [input('status'), $id]);
        flash('Status reservasi diperbarui.');
    }
    redirect($self);
}
$today = date('Y-m-d');
$count = fn(string $sql, array $p = []) => (int) query($sql, $p)->fetchColumn();
$stats = [
    ['Menu', $count('SELECT COUNT(*) FROM menu'), 'menu'],
    ['Promo aktif', $count('SELECT COUNT(*) FROM promo WHERE aktif=1 AND (mulai IS NULL OR mulai<=?) AND (selesai IS NULL OR selesai>=?)', [$today, $today]), 'promo'],
    ['Event mendatang', $count('SELECT COUNT(*) FROM event WHERE tanggal>=?', [$today]), 'event'],
    ['Lowongan buka', $count('SELECT COUNT(*) FROM lowongan WHERE buka=1'), 'lowongan'],
    ['Total pelamar', $count('SELECT COUNT(*) FROM pelamar'), 'pelamar'],
    ['Pelamar baru', $count("SELECT COUNT(*) FROM pelamar WHERE status='baru'"), 'pelamar'],
    ['Foto galeri', $count('SELECT COUNT(*) FROM galeri'), 'galeri'],
];
$reservasi = query('SELECT * FROM reservasi ORDER BY id DESC LIMIT 100')->fetchAll();
admin_header('Dashboard');
?>
<div class="grid stats"><?php foreach ($stats as [$l, $n, $page]): ?><a class="stat" href="<?= url("admin/$page.php") ?>"><b><?= $n ?></b><?= e($l) ?></a><?php endforeach; ?></div>
<section class="panel"><h2>Reservasi terbaru</h2>
<?php if (!$reservasi): ?><p>Belum ada reservasi.</p><?php else: ?>
<div class="table-wrap"><table><thead><tr><th>Jadwal</th><th>Pemesan</th><th>Detail</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($reservasi as $r): ?><tr>
  <td><?= e(tanggal_id($r['tanggal'])) ?><br><?= e($r['jam']) ?></td>
  <td><?= e($r['nama']) ?><br><a href="<?= e(wa_link("Halo {$r['nama']}, ini Kedai Kopi Sahaja soal reservasimu.", $r['whatsapp'])) ?>" target="_blank" rel="noopener"><?= e($r['whatsapp']) ?></a></td>
  <td><?= e($r['jenis']) ?> · <?= (int) $r['jumlah'] ?> orang<br><small><?= e($r['catatan']) ?></small></td>
  <td><form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><select name="status" aria-label="Status"><?php foreach ($statusList as $k => $v): ?><option value="<?= $k ?>" <?= $r['status'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select><button class="btn btn-small">Simpan</button></form></td>
  <td><form method="post" data-confirm="Hapus reservasi ini?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><input type="hidden" name="aksi" value="hapus"><button class="btn btn-small btn-danger">Hapus</button></form></td>
</tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?>
</section>
<?php admin_footer(); ?>
