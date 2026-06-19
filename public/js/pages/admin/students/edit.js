  document.addEventListener("DOMContentLoaded", () => {
    const confirmBtn = document.querySelector('#confirm-modal-btn');
    const deleteConfirmBtn = document.querySelector('#delete-confirm-modal-btn');

    const studentIdInput = document.querySelector('#student_id');
    const nationalIdInput = document.querySelector('#national_id');

    const emailInput = document.querySelector('#email');
    const passwordInput = document.querySelector('#password');

    studentIdInput.addEventListener('input', function () {
      const studentId = this.value.trim();
      emailInput.value = studentId ? `${studentId}@caothang.edu.vn` : '';
    });

    nationalIdInput.addEventListener('input', function () {
      const nationalId = this.value.trim();
      passwordInput.value = nationalId ? nationalId : '';
    });

    // Confirm Btn Event Listener
    confirmBtn.addEventListener('click', function () {
      const form = document.querySelector('#student-edit-form');
      form.submit();
    });

    // Delete Btn Event Listener
    deleteConfirmBtn.addEventListener('click', function () {
      const deleteForm = document.querySelector('#delete-form');
      deleteForm.submit();
    });
  });
