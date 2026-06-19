  document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector('#account-add-form');
    const confirmBtn = document.querySelector('#confirm-modal-btn');
    const roleSelect = document.querySelector('[data-select-id="role-select"]');
    const roleInput = document.querySelector('#role-input');
    const passwordToggleButtons = document.querySelectorAll('[data-password-toggle]');

    passwordToggleButtons.forEach((button) => {
      const input = document.getElementById(button.dataset.passwordToggle);
      const icon = button.querySelector('i');

      if (!input || !icon) return;

      button.addEventListener('click', () => {
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        icon.classList.toggle('fa-eye', !isPassword);
        icon.classList.toggle('fa-eye-slash', isPassword);
        button.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
      });
    });

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
  });
