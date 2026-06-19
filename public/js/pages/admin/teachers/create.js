  document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector('#teacher-add-form');
    const confirmBtn = document.querySelector('#confirm-modal-btn');

    const teacherNameInput = document.querySelector('#full_name');
    const nationalIdInput = document.querySelector('#national_id');

    const emailInput = document.querySelector('#email');
    const passwordInput = document.querySelector('#password');

    teacherNameInput.addEventListener('input', function () {
      const teacherName = Utils.toCleanAscii(this.value).replace(/\s+/g, '');
      emailInput.value = teacherName ? `${teacherName}@caothang.edu.vn` : '';
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
