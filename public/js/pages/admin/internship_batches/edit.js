  document.addEventListener("DOMContentLoaded", () => {
    const setupConfirmAction = (confirmBtnId, formId) => {
      const confirmBtn = document.getElementById(confirmBtnId);
      if (confirmBtn) {
        confirmBtn.addEventListener("click", () => {
          const form = document.getElementById(formId);
          if (form) form.submit();
        });
      }
    };

    setupConfirmAction("save-confirm-modal-btn", "batch-edit-form");

    setupConfirmAction("delete-confirm-modal-btn", "delete-form");

    setupConfirmAction("publish-confirm-modal-btn", "publish-form");
    setupConfirmAction("close-confirm-modal-btn", "close-form");
  });
