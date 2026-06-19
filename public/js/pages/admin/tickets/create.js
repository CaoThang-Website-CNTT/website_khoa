  document.addEventListener("DOMContentLoaded", () => {
    document.querySelector('#confirm-modal-btn').addEventListener('click', () => {
      document.querySelector('#ticket-create-form').submit();
    });
  });
