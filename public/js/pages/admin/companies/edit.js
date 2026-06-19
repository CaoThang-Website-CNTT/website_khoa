  document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector('#company-edit-form');
    const confirmBtn = document.querySelector('#confirm-modal-btn');
    const deleteConfirmBtn = document.querySelector('#delete-confirm-modal-btn');

    const nameInput = document.querySelector('#company_name');
    const idInput = document.querySelector('#id');
    const taxCodeInput = document.querySelector('#tax_code');
    const addressInput = document.querySelector('#address');
    const emailInput = document.querySelector('#email');
    const websiteInput = document.querySelector('#website');
    const noteInput = document.querySelector('#note');

    confirmBtn.addEventListener('click', () => {
      form.submit();
    });

    deleteConfirmBtn.addEventListener('click', () => {
      document.querySelector('#delete-form').submit();
    });
  });
