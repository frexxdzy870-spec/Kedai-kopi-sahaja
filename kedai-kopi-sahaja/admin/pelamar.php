<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
$self = url('admin/pelamar.php');
$statusList = ['baru' => 'Baru', 'ditinjau' => 'Ditinjau', 'interview' => 'Interview', 'diterima' => 'Diterima', 'ditolak' => 'Ditolak'];
$cvPath = fn(string $f) => APP_ROOT . '/assets/uploads/cv/' . basename($f);

// Unduh CV hanya untuk admin yang sudah login
if (isset($_GET['unduh'])) {
    $r = query('SELECT nama, cv_file FROM pelamar WHERE id=?', [(int) $_GET['unduh']])->fetch();
    if (!$r || !is_file($cvPath($r['cv_file']))) { http_response_code(404); exit('File CV tidak ditemukan.'); }
    $nama = trim(preg_replace('/[^A-Za-z0-9]+/', '-', $r['nama']), '-') ?: 'pelamar';
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="CV-' . $nama . '.pdf"');
    header('Content-Length: ' . filesize($cvPath($r['cv_file'])));
    readfile($cvPath($r['cv_file']));
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int) input('id');
    if (input('aksi') === 'hapus') {
        $file = query('SELECT cv_file FROM pelamar WHERE id=?', [$id])->fetchColumn();
        query('DELETE FROM pelamar WHERE id=?', [$id]);
        if ($file && is_file($cvPath($file))) { unlink($cvPath($file)); }
        flash('Data pelamar dan CV dihapus.');
    } elseif (isset($statusList[input('status')])) {
        query('UPDATE pelamar SET status=?, catatan=? WHERE id=?', [input('status'), input('catatan', 1000), $id]);
        flash('Tindak lanjut tersimpan.');
    }
    redirect($self);
}
$filter = is_string($_GET['status'] ?? null) && isset($statusList[$_GET['status']]) ? $_GET['status'] : '';
$rows = $filter ? query('SELECT * FROM pelamar WHERE status=? ORDER BY id DESC', [$filter])->fetchAll() : query('SELECT * FROM pelamar ORDER BY id DESC')->fetchAll();
admin_header('Pelamar');
?>
<div class="chips"><a class="chip <?= $filter === '' ? 'active' : '' ?>" href="<?= $self ?>">Semua</a><?php foreach ($statusList as $k => $v): ?><a class="chip <?= $filter === $k ? 'active' : '' ?>" href="<?= $self ?>?status=<?= $k ?>"><?= $v ?></a><?php endforeach; ?></div>
<section class="panel"><?php if (!$rows): ?><p>Belum ada lamaran.</p><?php else: ?><div class="table-wrap"><table><thead><tr><th>Pelamar</th><th>Detail</th><th>Berkas</th><th>Tindak lanjut</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr>
<td><strong><?= e($r['nama']) ?></strong><br><a target="_blank" rel="noopener" href="<?= e(wa_link("Halo {$r['nama']}, ini Kedai Kopi Sahaja terkait lamaranmu.", $r['whatsapp'])) ?>"><?= e($r['whatsapp']) ?></a><br><?= e($r['email']) ?><br><small><?= e($r['dibuat']) ?></small></td>
<td><?= e($r['posisi']) ?><br><small><?= nl2br(e($r['pesan'])) ?></small></td>
<td><a href="<?= $self ?>?unduh=<?= (int) $r['id'] ?>">Unduh CV</a><?php if ($r['portofolio_url'] && preg_match('#^https?://#i', $r['portofolio_url'])): ?><br><a href="<?= e($r['portofolio_url']) ?>" target="_blank" rel="noopener noreferrer">Portofolio ↗</a><?php endif; ?></td>
<td><form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><select name="status" aria-label="Status"><?php foreach ($statusList as $k => $v): ?><option value="<?= $k ?>" <?= $r['status'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select><input name="catatan" value="<?= e($r['catatan']) ?>" placeholder="Catatan" maxlength="1000" aria-label="Catatan"><button class="btn btn-small">Simpan</button></form></td>
<td><form method="post" data-confirm="Hapus pelamar beserta file CV-nya?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><input type="hidden" name="aksi" value="hapus"><button class="btn btn-small btn-danger">Hapus</button></form></td>
</tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php admin_footer(); ?>
