const cards = [...document.querySelectorAll('[data-faculty-card]')];

function setExpanded(card, expanded) {
  card.setAttribute('aria-expanded', String(expanded));
  card.querySelector('[data-faculty-contact]')?.setAttribute('aria-hidden', String(!expanded));
}

function collapseOthers(activeCard = null) {
  cards.forEach((card) => {
    if (card !== activeCard) setExpanded(card, false);
  });
}

cards.forEach((card) => {
  card.addEventListener('click', (event) => {
    event.stopPropagation();
    const willExpand = card.getAttribute('aria-expanded') !== 'true';
    collapseOthers(card);
    setExpanded(card, willExpand);
  });

  card.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      card.click();
    }
    if (event.key === 'Escape') {
      setExpanded(card, false);
      card.blur();
    }
  });

  card.addEventListener('focus', () => {
    card.querySelector('[data-faculty-contact]')?.setAttribute('aria-hidden', 'false');
  });

  card.addEventListener('blur', () => {
    if (card.getAttribute('aria-expanded') !== 'true') {
      card.querySelector('[data-faculty-contact]')?.setAttribute('aria-hidden', 'true');
    }
  });
});

document.addEventListener('click', () => collapseOthers());
document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') collapseOthers();
});
