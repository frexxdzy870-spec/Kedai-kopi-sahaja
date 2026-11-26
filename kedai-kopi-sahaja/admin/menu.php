<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
$self = url('admin/menu.php');
$fKategori = ['nama' => ['Nama kategori', 'text', 'required' => true], 'urutan' => ['Urutan', 'number']];
$editKategori = crud_handle('kategori', $fKategori, $self);
$kategori = query('SELECT * FROM kategori ORDER BY urutan, id')->fetchAll();
$fMenu = [
    'kategori_id' => ['Kategori', 'select', 'options' => array_column($kategori, 'nama', 'id')],
    'nama'      => ['Nama menu', 'text', 'required' => true],
    'harga'     => ['Harga (Rp) - kosongkan untuk "Tanya harga"', 'number', 'empty' => null],
    'gambar'    => ['Foto', 'image', 'folder' => 'menu'],
    'deskripsi' => ['Deskripsi', 'textarea'],
    'andalan'   => ['Tampilkan sebagai menu andalan', 'checkbox'],
    'tersedia'  => ['Tersedia', 'checkbox', 'default' => 1],
];
$edit = crud_handle('menu', $fMenu, $self);
$rows = query('SELECT m.*, k.nama AS kategori FROM menu m JOIN kategori k ON k.id=m.kategori_id ORDER BY k.urutan, m.nama')->fetchAll();
admin_header('Menu');
if ($kategori) { crud_form('menu', $fMenu, $edit, $self, 'menu'); }
?>
<section class="panel"><h2>Daftar menu</h2><div class="table-wrap"><table><thead><tr><th>Foto</th><th>Nama</th><th>Kategori</th><th>Harga</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><img src="<?= asset($r['gambar']) ?>" alt=""></td><td><?= e($r['nama']) ?><?= $r['andalan'] ? ' ★' : '' ?></td><td><?= e($r['kategori']) ?></td><td><?= rupiah($r['harga']) ?></td><td><?= $r['tersedia'] ? 'Tersedia' : 'Habis' ?></td><td><?= crud_actions('menu', (int) $r['id'], $self) ?></td></tr><?php endforeach; ?>
</tbody></table></div></section>
<?php crud_form('kategori', $fKategori, $editKategori, $self, 'kategori'); ?>
<section class="panel"><h2>Kategori</h2><div class="table-wrap"><table><tbody>
<?php foreach ($kategori as $k): ?><tr><td><?= e($k['nama']) ?></td><td>Urutan <?= (int) $k['urutan'] ?></td><td><?= crud_actions('kategori', (int) $k['id'], $self, 'Hapus kategori ini BESERTA semua menu di dalamnya?', 'edit_kategori') ?></td></tr><?php endforeach; ?>
</tbody></table></div></section>
<?php admin_footer(); ?>
