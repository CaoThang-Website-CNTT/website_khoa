document.addEventListener('DOMContentLoaded', () => {
  const track = document.querySelector('[data-partners-track]');
  const dialog = document.querySelector('[data-partner-dialog]');
  if (!track || !dialog) return;

  const step = () => Math.max(280, track.clientWidth * 0.75);
  document.querySelector('[data-partners-prev]')?.addEventListener('click', () => track.scrollBy({ left: -step(), behavior: 'smooth' }));
  document.querySelector('[data-partners-next]')?.addEventListener('click', () => track.scrollBy({ left: step(), behavior: 'smooth' }));

  track.querySelectorAll('[data-partner]').forEach((card) => card.addEventListener('click', () => {
    dialog.querySelector('[data-partner-dialog-title]').textContent = card.dataset.title || '';
    dialog.querySelector('[data-partner-dialog-description]').textContent = card.dataset.description || '';
    const image = dialog.querySelector('[data-partner-dialog-image]');
    image.src = card.dataset.image || '';
    image.alt = card.dataset.title || '';
    const link = dialog.querySelector('[data-partner-dialog-link]');
    link.href = card.dataset.url || '#';
    link.hidden = !card.dataset.url;
    dialog.showModal();
  }));
  dialog.querySelector('[data-partner-close]')?.addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
});
