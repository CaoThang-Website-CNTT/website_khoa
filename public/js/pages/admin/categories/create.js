  document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector('#category-add-form');
    const confirmBtn = document.querySelector('#confirm-modal-btn');
    const nameInput = document.querySelector('#name');
    const slugInput = document.querySelector('#slug');

    // ── Slug auto-generation ──────────────────────────────────────────────
    nameInput.addEventListener('input', () => {
      if (slugInput.dataset.manual) return;
      slugInput.value = nameInput.value
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/đ/g, 'd')
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-');
    });

    slugInput.addEventListener('input', () => {
      slugInput.dataset.manual = 'true';
    });

    confirmBtn.addEventListener('click', function () {
      form.submit();
    });
  });
