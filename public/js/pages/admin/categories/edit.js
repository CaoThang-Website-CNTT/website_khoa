  document.addEventListener("DOMContentLoaded", () => {
    if (window.__categoryEdit__?.isEditable) {
      const form = document.querySelector('#category-edit-form');
      const confirmBtn = document.querySelector('#confirm-modal-btn');
      const deleteBtn = document.querySelector('#delete-confirm-btn');
      const deleteForm = document.querySelector('#category-delete-form');
      const nameInput = document.querySelector('#name');
      const slugInput = document.querySelector('#slug');

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

      confirmBtn.addEventListener('click', () => form.submit());
      deleteBtn.addEventListener('click', () => deleteForm.submit());
    }
  });
