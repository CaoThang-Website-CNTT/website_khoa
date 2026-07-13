document.addEventListener('DOMContentLoaded', () => {
  const container = document.querySelector('.tm-container[data-tm="student_referral_letters_table"]');

  // Event Delegation for Table Buttons
  if (container) {
    container.addEventListener('click', (e) => {
      // Handle Cancel Button
      const cancelBtn = e.target.closest('.btn-cancel');
      if (cancelBtn) {
        const id = cancelBtn.getAttribute('data-id');
        const companyName = cancelBtn.getAttribute('data-company-name');
        const cancelForm = document.getElementById('rl_cancelForm');
        if (cancelForm) {
          cancelForm.action = `${window.__studentReferralLetters__?.baseUrl || ''}/${id}/cancel`;
        }
        const desc = document.getElementById('rl_cancelModal_desc');
        if (desc) desc.textContent = `Bạn có chắc chắn muốn hủy giấy giới thiệu gửi đến công ty ${companyName}?`;
      }
    });
  }

  // Xử lý các lý do hủy đăng ký được gợi ý sẵn
  document.addEventListener("click", (e) => {
    const suggestionBtn = e.target.closest(".btn-cancel-suggestion");
    if (suggestionBtn) {
      const textarea = suggestionBtn
        .closest(".field")
        .querySelector("textarea");
      if (textarea) {
        textarea.value = suggestionBtn.innerText;
        textarea.focus();
      }
    }
  });
});
