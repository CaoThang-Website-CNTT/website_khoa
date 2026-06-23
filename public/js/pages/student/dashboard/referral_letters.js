document.addEventListener('DOMContentLoaded', () => {
  const container = document.querySelector('.tm-container[data-tm="student_referral_letters_table"]');

  // Event Delegation for Table Buttons
  if (container) {
    container.addEventListener('click', (e) => {
      // Handle Cancel Button
      const cancelBtn = e.target.closest('.btn-cancel');
      if (cancelBtn) {
        const id = cancelBtn.getAttribute('data-id');
        const cancelForm = document.getElementById('rl_cancelForm');
        if (cancelForm) {
          cancelForm.action = `${window.__studentReferralLetters__?.baseUrl || ''}/${id}/cancel`;
        }
      }
    });
  }
});
