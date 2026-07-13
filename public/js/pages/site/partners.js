document.addEventListener('DOMContentLoaded', () => {
  const directory = document.querySelector('[data-partners-directory]');
  if (!directory) return;

  const dataElement = directory.querySelector('[data-partners-json]');
  const detail = directory.querySelector('[data-partner-detail]');
  const cards = [...directory.querySelectorAll('[data-partner-card]')];
  if (!dataElement || !detail || !cards.length) return;

  let partners = [];
  try {
    partners = JSON.parse(dataElement.textContent || '[]');
  } catch {
    return;
  }

  const image = detail.querySelector('[data-partner-detail-image]');
  const title = detail.querySelector('[data-partner-detail-title]');
  const description = detail.querySelector('[data-partner-detail-description]');
  const website = detail.querySelector('[data-partner-detail-link]');
  const mobileQuery = window.matchMedia('(max-width: 767px)');
  if (!image || !title || !description || !website) return;

  const selectPartner = (index, shouldMoveToDetail = false) => {
    const partner = partners[index];
    if (!partner) return;

    cards.forEach((card, cardIndex) => {
      const isActive = cardIndex === index;
      card.classList.toggle('partner-grid-card--active', isActive);
      card.setAttribute('aria-pressed', String(isActive));
    });

    image.src = partner.image?.src || '';
    image.alt = partner.image?.alt || partner.name || '';
    title.textContent = partner.name || '';
    description.textContent = partner.description || '';
    website.href = partner.url || '#';

    if (shouldMoveToDetail && mobileQuery.matches) {
      detail.scrollIntoView({ behavior: 'smooth', block: 'start' });
      window.setTimeout(() => detail.focus({ preventScroll: true }), 350);
    }
  };

  directory.addEventListener('click', (event) => {
    const card = event.target.closest('[data-partner-card]');
    if (!card || !directory.contains(card)) return;

    selectPartner(Number.parseInt(card.dataset.partnerIndex || '0', 10), true);
  });
});
