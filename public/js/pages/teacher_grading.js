document.addEventListener('DOMContentLoaded', () => {
  // Version Selector Logic
  const versionSelects = document.querySelectorAll('.js-version-select');
  versionSelects.forEach(select => {
    select.addEventListener('change', (e) => {
      const targetId = select.getAttribute('data-target');
      const iframe = document.getElementById(targetId);
      if (iframe) {
        iframe.src = e.target.value;
      }
    });
  });

  // Lightbox Logic
  const lightbox = document.getElementById('lightbox');
  const lightboxImg = document.getElementById('lightbox-img');
  const lightboxClose = lightbox?.querySelector('.lightbox-close');
  const lightboxTriggers = document.querySelectorAll('.js-lightbox-trigger');

  if (lightbox && lightboxImg) {
    // Open lightbox
    lightboxTriggers.forEach(trigger => {
      trigger.addEventListener('click', () => {
        lightboxImg.src = trigger.src;
        lightbox.classList.remove('hidden');
      });
    });

    // Close lightbox
    const closeLightbox = () => {
      lightbox.classList.add('hidden');
      lightboxImg.src = '';
    };

    if (lightboxClose) {
      lightboxClose.addEventListener('click', closeLightbox);
    }
    
    // Close on overlay click
    lightbox.addEventListener('click', (e) => {
      if (e.target === lightbox) {
        closeLightbox();
      }
    });

    // Close on Esc key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !lightbox.classList.contains('hidden')) {
        closeLightbox();
      }
    });
  }

  // Sync Score Select to Hidden Input
  const scoreSelect = document.querySelector('[data-select-id="grading-score"]');
  const scoreInput = document.querySelector('#score-input');
  if (scoreSelect && scoreInput) {
    scoreSelect.addEventListener('select:change', (e) => {
      scoreInput.value = e.detail.value || '';
    });
  }
});
