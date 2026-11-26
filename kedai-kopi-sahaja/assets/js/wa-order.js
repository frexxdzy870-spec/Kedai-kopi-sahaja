/* Generator pesan WhatsApp: order, reservasi, lamaran */
(() => {
  const cfg = window.SAHAJA;
  if (!cfg) return;
  const kirim = baris => window.open(`https://wa.me/${cfg.wa}?text=${encodeURIComponent(baris.filter(x => x !== null).join('\n'))}`, '_blank', 'noopener');

  // Konfirmasi reservasi & lamaran
  document.querySelectorAll('[data-wa]').forEach(btn => btn.addEventListener('click', () => {
    const d = btn.dataset;
    if (d.wa === 'reservasi') {
      kirim([`Halo ${cfg.nama}, saya mau konfirmasi reservasi.`, '', `Nama: ${d.nama}`, `Jenis: ${d.jenis}`,
        `Tanggal: ${d.tanggal}`, `Jam: ${d.jam}`, `Jumlah: ${d.jumlah} orang`, d.catatan ? `Catatan: ${d.catatan}` : null]);
    } else if (d.wa === 'lamaran') {
      kirim([`Halo ${cfg.nama}, saya ${d.nama}.`, `Saya sudah mengirim lamaran untuk posisi ${d.posisi} lewat website. Terima kasih.`]);
    }
  }));

  // Daftar pesanan di halaman menu
  const modal = document.getElementById('order-modal');
  if (!modal) return;
  const KEY = 'sahaja_pesanan';
  let cart = new Map();
  try { cart = new Map(JSON.parse(sessionStorage.getItem(KEY)) || []); } catch (_) { /* abaikan */ }

  // Harga & ketersediaan selalu diambil dari halaman saat ini
  const katalog = new Map([...document.querySelectorAll('[data-order-add]')].map(b => [b.dataset.orderAdd, b.dataset.price === '' ? null : Number(b.dataset.price)]));
  [...cart.keys()].forEach(k => { if (!katalog.has(k)) cart.delete(k); });

  const rp = n => 'Rp' + n.toLocaleString('id-ID');
  const total = () => {
    let jumlah = 0, belum = false;
    cart.forEach((q, nama) => { const h = katalog.get(nama); if (h === null) belum = true; else jumlah += h * q; });
    if (!cart.size) return '';
    if (belum) return jumlah ? `Total sementara: ${rp(jumlah)} + item yang harganya dikonfirmasi` : 'Total dikonfirmasi oleh kedai';
    return `Perkiraan total: ${rp(jumlah)}`;
  };
  const list = modal.querySelector('[data-order-list]');
  const fab = document.querySelector('[data-order-open]');

  function render() {
    try { sessionStorage.setItem(KEY, JSON.stringify([...cart])); } catch (_) { /* abaikan */ }
    const n = [...cart.values()].reduce((a, b) => a + b, 0);
    fab.hidden = n === 0;
    fab.querySelector('[data-order-count]').textContent = n;
    list.replaceChildren(...[...cart].map(([nama, q]) => {
      const li = document.createElement('li');
      const label = document.createElement('span');
      label.textContent = nama;
      const ctr = document.createElement('span');
      ctr.className = 'qty';
      const tombol = (teks, d, aria) => {
        const b = document.createElement('button');
        b.type = 'button'; b.textContent = teks; b.setAttribute('aria-label', `${aria} ${nama}`);
        b.addEventListener('click', () => { const v = Math.min(50, (cart.get(nama) || 0) + d); v > 0 ? cart.set(nama, v) : cart.delete(nama); render(); });
        return b;
      };
      const angka = document.createElement('b'); angka.textContent = q;
      ctr.append(tombol('−', -1, 'Kurangi'), angka, tombol('+', 1, 'Tambah'));
      li.append(label, ctr);
      return li;
    }));
    if (!cart.size) { const li = document.createElement('li'); li.textContent = 'Belum ada pesanan.'; list.append(li); }
    modal.querySelector('[data-order-total]').textContent = total();
  }

  document.querySelectorAll('[data-order-add]').forEach(b => b.addEventListener('click', () => {
    const k = b.dataset.orderAdd;
    cart.set(k, Math.min(50, (cart.get(k) || 0) + 1));
    render();
  }));
  fab.addEventListener('click', () => SahajaModal.open(modal));
  modal.querySelector('[data-order-form]').addEventListener('submit', e => {
    e.preventDefault();
    if (!cart.size) return;
    const f = new FormData(e.target);
    kirim([`Halo ${cfg.nama}, saya mau pesan:`, '', ...[...cart].map(([nama, q]) => `• ${q}x ${nama}`), '', total(),
      `Nama: ${f.get('nama')}`, f.get('meja') ? `Meja/ambil: ${f.get('meja')}` : null, f.get('catatan') ? `Catatan: ${f.get('catatan')}` : null]);
  });
  render();
})();
