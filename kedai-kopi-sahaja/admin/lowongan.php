<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
$self = url('admin/lowongan.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && input('aksi') === 'toggle') {
    csrf_check();
    query('UPDATE lowongan SET buka = 1 - buka WHERE id=?', [(int) input('id')]);
    flash('Status lowongan diubah.');
    redirect($self);
}
$fields = [
    'posisi' => ['Posisi', 'text', 'required' => true],
    'tipe' => ['Tipe (Part time / Full time)', 'text', 'default' => 'Part time', 'required' => true],
    'gambar' => ['Poster', 'image', 'folder' => 'events'],
    'deskripsi' => ['Deskripsi', 'textarea'],
    'kualifikasi' => ['Kualifikasi (satu poin per baris)', 'textarea', 'required' => true],
    'buka' => ['Lowongan dibuka', 'checkbox', 'default' => 1],
];
$edit = crud_handle('lowongan', $fields, $self);
$rows = query('SELECT * FROM lowongan ORDER BY id DESC')->fetchAll();
admin_header('Lowongan');
crud_form('lowongan', $fields, $edit, $self, 'lowongan');
?>
<section class="panel"><h2>Daftar posisi</h2><div class="table-wrap"><table><tbody>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['posisi']) ?></td><td><?= e($r['tipe']) ?></td>
<td><form method="post"><?= csrf_field() ?><input type="hidden" name="aksi" value="toggle"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><button class="btn btn-small <?= $r['buka'] ? '' : 'btn-ghost' ?>" title="Klik untuk mengubah"><?= $r['buka'] ? 'Dibuka' : 'Ditutup' ?></button></form></td>
<td><?= crud_actions('lowongan', (int) $r['id'], $self, 'Hapus lowongan ini? Data pelamar tetap tersimpan.') ?></td></tr><?php endforeach; ?>
</tbody></table></div></section>
<?php admin_footer(); ?>
