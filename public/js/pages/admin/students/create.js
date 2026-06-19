  document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector('#student-add-form');
    const confirmBtn = document.querySelector('#confirm-modal-btn');

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
      form.submit();
    });
  });
