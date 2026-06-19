  document.addEventListener("DOMContentLoaded", () => {
    document.querySelector('#confirm-modal-btn').addEventListener('click', () => {
      document.querySelector('#ticket-edit-form').submit();
    });
  });
