/* Interaksi UI: navbar, filter menu/event, modal, konfirmasi hapus */
(() => {
  const $ = (s, el = document) => el.querySelector(s);
  const $$ = (s, el = document) => [...el.querySelectorAll(s)];

  const toggle = $('.nav-toggle'), nav = $('#nav');
  toggle?.addEventListener('click', () => {
    const open = nav.classList.toggle('open');
    toggle.setAttribute('aria-expanded', open);
  });

  // Filter kategori + pencarian
  let kategori = 'semua', kata = '';
  const items = $$('[data-item]');
  const apply = () => {
    let tampil = 0;
    items.forEach(it => {
      const ok = (kategori === 'semua' || it.dataset.category === kategori) && (!kata || (it.dataset.text || '').includes(kata));
      it.hidden = !ok;
      if (ok) tampil++;
    });
    const kosong = $('[data-empty]');
    if (kosong) kosong.hidden = tampil > 0;
  };
  $$('[data-filter]').forEach(btn => btn.addEventListener('click', () => {
    kategori = btn.dataset.filter;
    $$('[data-filter]').forEach(b => { b.classList.toggle('active', b === btn); b.setAttribute('aria-pressed', b === btn); });
    apply();
  }));
  $('[data-search]')?.addEventListener('input', e => { kata = e.target.value.trim().toLowerCase(); apply(); });

  // Modal
  let pemicu = null;
  window.SahajaModal = {
    open(m) { pemicu = document.activeElement; m.hidden = false; document.body.style.overflow = 'hidden'; $('[data-close]', m)?.focus(); },
    close(m) { m.hidden = true; document.body.style.overflow = ''; pemicu?.focus(); }
  };
  $$('.modal').forEach(m => m.addEventListener('click', e => {
    if (e.target === m || e.target.closest('[data-close]')) SahajaModal.close(m);
  }));
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') $$('.modal:not([hidden])').forEach(m => SahajaModal.close(m));
  });
  $$('[data-zoom]').forEach(b => b.addEventListener('click', () => {
    const box = $('#lightbox'), img = $('img', box);
    img.src = b.dataset.zoom;
    img.alt = $('img', b)?.alt || '';
    SahajaModal.open(box);
  }));

  $$('form[data-confirm]').forEach(f => f.addEventListener('submit', e => {
    if (!confirm(f.dataset.confirm)) e.preventDefault();
  }));
})();
