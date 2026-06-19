  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('student-profile-form');
    const confirmBtn = document.getElementById('confirm-save-btn');

    if (confirmBtn && form) {
      confirmBtn.addEventListener('click', () => {
        // Thêm loading state
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';
        form.submit();
      });
    }
  });
