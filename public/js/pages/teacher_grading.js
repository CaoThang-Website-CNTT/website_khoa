document.addEventListener('DOMContentLoaded', () => {
  // Tab Switching
  const tabBtns = document.querySelectorAll('.tab-btn');
  const viewerPanes = document.querySelectorAll('.viewer-pane');

  tabBtns.forEach(btn => {
    if (btn.hasAttribute('data-target')) {
      btn.addEventListener('click', () => {
        // Remove active from all
        tabBtns.forEach(b => b.classList.remove('active'));
        viewerPanes.forEach(p => p.classList.remove('active'));

        // Add active to clicked
        btn.classList.add('active');
        const targetId = btn.getAttribute('data-target');
        if (targetId) {
          const targetPane = document.getElementById(targetId);
          if (targetPane) {
            targetPane.classList.add('active');
          }
        }
      });
    }
  });

  // Toggle timeline details
  const timelineHeaders = document.querySelectorAll(".weekly-timeline__header");
  timelineHeaders.forEach((header) => {
    header.addEventListener("click", () => {
      const details = header.nextElementSibling;
      if (details) {
        details.classList.toggle("hidden");
        const icon = header.querySelector(".fa-chevron-down, .fa-chevron-up");
        if (icon) {
          icon.classList.toggle("fa-chevron-down");
          icon.classList.toggle("fa-chevron-up");
        }
      }
    });
  });

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
  const lightboxClose = document.querySelector('.lightbox-close');
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
      setTimeout(() => { lightboxImg.src = ''; }, 200);
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
});
