/* Validasi form lamaran & file CV (server tetap memvalidasi ulang) */
(() => {
  const form = document.querySelector('[data-lamaran]');
  if (!form) return;
  const max = Number(form.dataset.max);
  const err = form.querySelector('[data-error]');
  const tampil = pesan => { err.textContent = pesan; err.hidden = !pesan; };
  const cekFile = () => {
    const file = form.cv.files[0];
    if (!file) return 'Lampirkan CV dalam format PDF.';
    if (!/\.pdf$/i.test(file.name) || (file.type && file.type !== 'application/pdf')) return 'CV harus berformat PDF.';
    if (file.size > max) return `Ukuran CV maksimal ${max / 1048576} MB.`;
    return '';
  };
  form.cv.addEventListener('change', () => tampil(cekFile()));
  form.addEventListener('submit', e => {
    const no = form.whatsapp.value.replace(/[\s\-().+]/g, '');
    const pesan = !/^(08|628)\d{7,12}$/.test(no) ? 'Nomor WhatsApp tidak valid (contoh 081234567890).' : cekFile();
    tampil(pesan);
    if (pesan) e.preventDefault();
  });
  document.querySelectorAll('[data-pilih]').forEach(a => a.addEventListener('click', () => { form.lowongan_id.value = a.dataset.pilih; }));
})();
