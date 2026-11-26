<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
$self = url('admin/komunitas.php');
$fields = [
    'judul' => ['Nama kegiatan', 'text', 'required' => true],
    'tanggal' => ['Tanggal', 'date'],
    'gambar' => ['Foto dokumentasi', 'image', 'folder' => 'komunitas'],
    'deskripsi' => ['Cerita kegiatan', 'textarea'],
];
$edit = crud_handle('komunitas', $fields, $self);
$rows = query('SELECT * FROM komunitas ORDER BY tanggal DESC, id DESC')->fetchAll();
admin_header('Komunitas Sahaja Bersama');
crud_form('komunitas', $fields, $edit, $self, 'kegiatan');
?>
<section class="panel"><h2>Kegiatan &amp; dokumentasi</h2><div class="table-wrap"><table><tbody>
<?php foreach ($rows as $r): ?><tr><td><img src="<?= asset($r['gambar']) ?>" alt=""></td><td><?= e($r['judul']) ?></td><td><?= e(tanggal_id($r['tanggal'])) ?></td><td><?= crud_actions('komunitas', (int) $r['id'], $self) ?></td></tr><?php endforeach; ?>
</tbody></table></div></section>
<?php admin_footer(); ?>
