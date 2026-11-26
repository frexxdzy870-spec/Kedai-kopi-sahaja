<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
$self = url('admin/event.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && input('aksi') === 'hapus_rsvp') {
    csrf_check();
    query('DELETE FROM rsvp WHERE id=?', [(int) input('id')]);
    flash('RSVP dihapus.');
    redirect($self);
}
$fields = [
    'judul' => ['Judul', 'text', 'required' => true],
    'jenis' => ['Jenis', 'select', 'options' => ['event' => 'Event', 'diskusi' => 'Diskusi', 'workshop' => 'Workshop', 'live-music' => 'Live Music']],
    'tanggal' => ['Tanggal', 'date', 'required' => true],
    'jam' => ['Jam (mis. 20.00 - selesai)', 'text'],
    'gambar' => ['Poster', 'image', 'folder' => 'events'],
    'deskripsi' => ['Deskripsi', 'textarea'],
    'rsvp_buka' => ['Buka pendaftaran RSVP', 'checkbox', 'default' => 1],
];
$edit = crud_handle('event', $fields, $self);
$rows = query('SELECT e.*, (SELECT COALESCE(SUM(jumlah),0) FROM rsvp r WHERE r.event_id=e.id) AS peserta FROM event e ORDER BY tanggal DESC')->fetchAll();
$rsvp = query('SELECT r.*, e.judul FROM rsvp r JOIN event e ON e.id=r.event_id ORDER BY r.id DESC LIMIT 200')->fetchAll();
admin_header('Event & RSVP');
crud_form('event', $fields, $edit, $self, 'event');
?>
<section class="panel"><h2>Jadwal</h2><div class="table-wrap"><table><thead><tr><th>Poster</th><th>Judul</th><th>Tanggal</th><th>Peserta RSVP</th><th>Aksi</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><img src="<?= asset($r['gambar']) ?>" alt=""></td><td><?= e($r['judul']) ?></td><td><?= e(tanggal_id($r['tanggal'])) ?></td><td><?= (int) $r['peserta'] ?></td><td><?= crud_actions('event', (int) $r['id'], $self, 'Hapus event beserta data RSVP-nya?') ?></td></tr><?php endforeach; ?>
</tbody></table></div></section>
<section class="panel"><h2>Pendaftar (RSVP)</h2><?php if (!$rsvp): ?><p>Belum ada pendaftar.</p><?php else: ?><div class="table-wrap"><table><tbody>
<?php foreach ($rsvp as $r): ?><tr><td><?= e($r['nama']) ?></td><td><a href="<?= e(wa_link('', $r['whatsapp'])) ?>" target="_blank" rel="noopener"><?= e($r['whatsapp']) ?></a></td><td><?= (int) $r['jumlah'] ?> orang</td><td><?= e($r['judul']) ?></td>
<td><form method="post" data-confirm="Hapus RSVP ini?"><?= csrf_field() ?><input type="hidden" name="aksi" value="hapus_rsvp"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><button class="btn btn-small btn-danger">Hapus</button></form></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></section>
<?php admin_footer(); ?>
