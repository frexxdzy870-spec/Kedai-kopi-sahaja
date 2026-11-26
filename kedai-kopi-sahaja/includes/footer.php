<?php /** Info kedai, jam buka, sosmed, copyright, dan JS. Set $scripts untuk JS tambahan. */ ?>
</main>
<footer class="footer">
  <div class="container footer-grid">
    <div>
      <img src="<?= asset('assets/images/logo/logo-stack.png') ?>" alt="<?= e(SITE_NAME) ?>" class="footer-logo">
      <p class="quote"><?= e(SITE_TAGLINE) ?></p>
    </div>
    <div>
      <h3>Mampir</h3>
      <p><?= e(ALAMAT) ?></p>
      <p><a href="https://www.google.com/maps/search/?api=1&amp;query=<?= rawurlencode(MAPS_QUERY) ?>" target="_blank" rel="noopener">Cari di Google Maps ↗</a></p>
    </div>
    <div>
      <h3>Jam buka</h3>
      <?php if (JAM_BUKA_AKTIF): ?>
        <ul class="hours">
        <?php foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $i => $h): $j = JAM_BUKA[$i + 1] ?? null; ?>
          <li><span><?= $h ?></span><span><?= $j ? e("$j[0] - $j[1]") : 'Libur' ?></span></li>
        <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p>Cek info terbaru di Instagram kami atau tanyakan lewat WhatsApp.</p>
      <?php endif; ?>
    </div>
    <div>
      <h3>Terhubung</h3>
      <p><a href="https://instagram.com/<?= e(INSTAGRAM) ?>" target="_blank" rel="noopener">Instagram @<?= e(INSTAGRAM) ?></a></p>
      <p><a href="<?= e(wa_link('Halo Kedai Kopi Sahaja!')) ?>" target="_blank" rel="noopener">WhatsApp <?= e(WA_LABEL) ?></a></p>
    </div>
  </div>
  <p class="copyright">© <?= date('Y') ?> <?= e(SITE_NAME) ?> · #SahajaBersama</p>
</footer>
<div class="modal" id="lightbox" role="dialog" aria-modal="true" aria-label="Pratinjau gambar" hidden>
  <button class="modal-close" data-close aria-label="Tutup">×</button>
  <img alt="">
</div>
<script>window.SAHAJA = <?= json_encode(['wa' => WA_NUMBER, 'nama' => SITE_NAME], JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<script src="<?= asset('assets/js/main.js') ?>"></script>
<script src="<?= asset('assets/js/wa-order.js') ?>"></script>
<?php foreach ($scripts ?? [] as $s): ?><script src="<?= asset("assets/js/$s") ?>"></script><?php endforeach; ?>
</body>
</html>
