// Script thực thi dưới dạng module (defer) nên không cần DOMContentLoaded
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

// Teacher Feedback Logic
const feedbackForms = document.querySelectorAll('.teacher-feedback-form');
feedbackForms.forEach(form => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const reportId = form.getAttribute('data-report-id');
    const feedback = form.querySelector('textarea[name="feedback"]').value.trim();
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalSubmitHtml = submitBtn.innerHTML;
    
    const url = form.getAttribute('data-url');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    
    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ report_id: reportId, feedback: feedback })
      });
      
      const json = await res.json();
      if (!res.ok || !json.success) throw new Error(json.message || 'Lỗi hệ thống');
      window.toast?.success('Thành công', json.message);
      
      const statusSpan = form.querySelector('.teacher-feedback-status');
      statusSpan.innerHTML = '<span class="badge" data-variant="primary"><i class="fa-solid fa-check mr-1"></i> Đã duyệt</span>';
      
      const markSeenBtn = form.querySelector('.mark-seen-btn');
      if (markSeenBtn) markSeenBtn.remove();
      
    } catch (err) {
      window.toast?.error('Lỗi', err.message);
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalSubmitHtml;
    }
  });
  
  const markSeenBtn = form.querySelector('.mark-seen-btn');
  if (markSeenBtn) {
    markSeenBtn.addEventListener('click', () => {
      form.querySelector('textarea[name="feedback"]').value = '';
      form.requestSubmit();
    });
  }
});

// UX Improvement: Auto open and scroll to specific week if provided in URL
window.addEventListener('load', () => {
  setTimeout(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const targetWeek = urlParams.get('week');
    if (targetWeek) {
      const weekAccordionVal = `teacher-week-${targetWeek}`;
      const triggerBtn = document.querySelector(`.weekly-timeline__item[data-accordion-value="${weekAccordionVal}"] .weekly-timeline__trigger`);
      if (triggerBtn) {
        if (triggerBtn.getAttribute('data-state') !== 'open') {
          triggerBtn.click();
        }
        setTimeout(() => {
          triggerBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 150);
      }
    }
  }, 150);
});
