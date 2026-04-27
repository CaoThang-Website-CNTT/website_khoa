document.addEventListener("DOMContentLoaded", () => {
  const tabTriggers = document.querySelectorAll(".tab-trigger");
  const tabContents = document.querySelectorAll(".tab-content");

  tabTriggers.forEach((trigger) => {
    trigger.addEventListener("click", () => {
      const targetTab = trigger.getAttribute("data-tab");

      tabTriggers.forEach((t) => t.setAttribute("data-state", "inactive"));
      trigger.setAttribute("data-state", "active");

      tabContents.forEach((content) => {
        if (content.id === `tab-${targetTab}`) {
          content.setAttribute("data-state", "active");
        } else {
          content.setAttribute("data-state", "inactive");
        }
      });
    });
  });

  const setupConfirmAction = (
    btnId,
    modalId,
    confirmBtnId,
    formId = null,
    actionUrl = null,
  ) => {
    const btn = document.getElementById(btnId);
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

          const csrfToken = document.querySelector('input[name="csrf_token"]');
          if (csrfToken) {
            const csrfInput = document.createElement("input");
            csrfInput.type = "hidden";
            csrfInput.name = "csrf_token";
            csrfInput.value = csrfToken.value;
            hiddenForm.appendChild(csrfInput);
          }

          document.body.appendChild(hiddenForm);
          hiddenForm.submit();
        }
      });
    }
  };

  // Nút Lưu thay đổi
  setupConfirmAction(
    "edit-submit-btn",
    "#save-confirm-modal",
    "save-confirm-modal-btn",
    "batch-edit-form",
  );

  // Nút Xóa
  setupConfirmAction(
    "delete-btn",
    "#delete-confirm-modal",
    "delete-confirm-modal-btn",
    "delete-form",
  );

  // Nút Công bố
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

  // Nút Kết thúc
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
