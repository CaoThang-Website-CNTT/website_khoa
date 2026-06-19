  document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector('#account-edit-form');
    const confirmBtn = document.querySelector('#confirm-modal-btn');
    const deleteBtn = document.querySelector('#delete-confirm-btn');
    const deleteForm = document.querySelector('#account-delete-form');
    const roleSelect = document.querySelector('[data-select-id="role-select"]');
    const roleInput = document.querySelector('#role-input');
    const passwordInput = document.querySelector('#password');
    const passwordConfirmationField = document.querySelector('#password-confirmation-field');
    const passwordConfirmationInput = document.querySelector('#password_confirmation');
    if (passwordInput && passwordConfirmationField && passwordConfirmationInput) {
      const syncPasswordConfirmation = () => {
        const shouldShow = passwordInput.value.trim() !== '';
        passwordConfirmationField.classList.toggle('hidden', !shouldShow);
        passwordConfirmationInput.required = shouldShow;

        if (!shouldShow) {
          passwordConfirmationInput.value = '';
        }
      };

      passwordInput.addEventListener('input', syncPasswordConfirmation);
      syncPasswordConfirmation();
    }

    if (roleSelect && roleInput) {
      roleSelect.addEventListener('select:change', (e) => {
        roleInput.value = e.detail.value || '';
      });
    }

    if (confirmBtn && form) {
      confirmBtn.addEventListener('click', () => {
        form.submit();
      });
    }

    if (deleteBtn && deleteForm) {
      deleteBtn.addEventListener('click', () => {
        deleteForm.submit();
      });
    }
  });
