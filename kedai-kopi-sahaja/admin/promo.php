<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
$self = url('admin/promo.php');
$fields = [
    'judul' => ['Judul promo', 'text', 'required' => true],
    'gambar' => ['Banner', 'image', 'folder' => 'hero'],
    'mulai' => ['Mulai', 'date'], 'selesai' => ['Selesai', 'date'],
    'deskripsi' => ['Deskripsi', 'textarea'],
    'aktif' => ['Aktif', 'checkbox', 'default' => 1],
];
$edit = crud_handle('promo', $fields, $self);
$rows = query('SELECT * FROM promo ORDER BY id DESC')->fetchAll();
admin_header('Promo');
crud_form('promo', $fields, $edit, $self, 'promo');
?>
<section class="panel"><h2>Daftar promo</h2><?php if (!$rows): ?><p>Belum ada promo. Banner tampil di beranda setelah ditambahkan.</p><?php endif; ?><div class="table-wrap"><table><tbody>
<?php foreach ($rows as $r): ?><tr><td><img src="<?= asset($r['gambar']) ?>" alt=""></td><td><?= e($r['judul']) ?></td><td><?= e($r['mulai'] ?: '-') ?> s/d <?= e($r['selesai'] ?: '-') ?></td><td><?= $r['aktif'] ? 'Aktif' : 'Nonaktif' ?></td><td><?= crud_actions('promo', (int) $r['id'], $self) ?></td></tr><?php endforeach; ?>
</tbody></table></div></section>
<?php admin_footer(); ?>
