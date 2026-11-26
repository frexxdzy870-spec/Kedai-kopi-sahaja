<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
$self = url('admin/galeri.php');
$fields = [
    'judul' => ['Judul / keterangan', 'text', 'required' => true],
    'urutan' => ['Urutan tampil', 'number'],
    'gambar' => ['Foto', 'image', 'folder' => 'gallery', 'required' => true],
];
$edit = crud_handle('galeri', $fields, $self);
$rows = query('SELECT * FROM galeri ORDER BY urutan, id DESC')->fetchAll();
admin_header('Galeri');
crud_form('galeri', $fields, $edit, $self, 'foto');
?>
<section class="panel"><h2>Foto kedai</h2><div class="grid gallery">
<?php foreach ($rows as $r): ?><figure><img src="<?= asset($r['gambar']) ?>" alt=""><figcaption><?= (int) $r['urutan'] ?>. <?= e($r['judul']) ?></figcaption><?= crud_actions('galeri', (int) $r['id'], $self) ?></figure><?php endforeach; ?>
</div></section>
<?php admin_footer(); ?>
