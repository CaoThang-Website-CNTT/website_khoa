  document.addEventListener("DOMContentLoaded", () => {
    const confirmBtn = document.querySelector('#confirm-modal-btn');
    const deleteConfirmBtn = document.querySelector('#delete-confirm-modal-btn');

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
      const form = document.querySelector('#teacher-edit-form');
      form.submit();
    });

    // Delete Confirm Btn Event Listener
    deleteConfirmBtn.addEventListener('click', function () {
      const deleteForm = document.querySelector('#delete-form');
      deleteForm.submit();
    });

  });
