document.addEventListener("DOMContentLoaded", () => {
  const setupConfirmAction = (
    btnId,
    modalId,
    confirmBtnId,
    formId = null,
    actionUrl = null,
  ) => {
    const confirmBtn = document.getElementById(confirmBtnId);
    if (confirmBtn) {
      confirmBtn.addEventListener("click", () => {
        if (formId) {
          const form = document.getElementById(formId);
          if (form) form.submit();
        } else if (actionUrl) {
          const hiddenForm = document.createElement("form");
          hiddenForm.method = "POST";
          hiddenForm.action = actionUrl;
          const csrfToken = document.querySelector('input[name="_token"]');
          if (csrfToken) {
            const csrfInput = document.createElement("input");
            csrfInput.type = "hidden";
            csrfInput.name = "_token";
            csrfInput.value = csrfToken.value;
            hiddenForm.appendChild(csrfInput);
          }
          document.body.appendChild(hiddenForm);
          hiddenForm.submit();
        }
      });
    }
  };

  setupConfirmAction(
    "edit-submit-btn",
    "#save-confirm-modal",
    "save-confirm-modal-btn",
    "batch-edit-form",
  );
  
  setupConfirmAction(
    "delete-btn",
    "#delete-confirm-modal",
    "delete-confirm-modal-btn",
    "delete-form",
  );

  const publishBtn = document.getElementById("publish-btn");
  if (publishBtn) {
    setupConfirmAction(
      "publish-btn",
      "#publish-confirm-modal",
      "publish-confirm-modal-btn",
      null,
      publishBtn.getAttribute("data-action"),
    );
  }

  const closeBtn = document.getElementById("close-btn");
  if (closeBtn) {
    setupConfirmAction(
      "close-btn",
      "#close-confirm-modal",
      "close-confirm-modal-btn",
      null,
      closeBtn.getAttribute("data-action"),
    );
  }
});
