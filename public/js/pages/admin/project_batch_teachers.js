// Handle action confirm modal
let currentForm = null;

document.addEventListener("click", (e) => {
  const btn = e.target.closest(".btn-confirm-action");
  if (btn) {
    currentForm = btn.closest("form");
    const msg =
      btn.getAttribute("data-confirm-msg") ||
      "Bạn có chắc chắn muốn thực hiện thao tác này?";

    const confirmMsg = document.getElementById("action-confirm-msg");
    if (confirmMsg) confirmMsg.textContent = msg;

    const modal = document.getElementById("action-confirm-modal");
    if (window.Modal) {
      window.Modal.open(modal);
    } else if (typeof ModalHandler !== "undefined") {
      ModalHandler.instance.open("#action-confirm-modal");
    } else {
      modal.dataset.state = "open";
    }
  }
});

const confirmBtn = document.getElementById("action-confirm-btn");
if (confirmBtn) {
  confirmBtn.addEventListener("click", () => {
    if (currentForm) {
      currentForm.submit();
    }
  });
}

function openEditCapacityModal(teacherId, minStudents, maxStudents) {
  document.getElementById("edit_teacher_id").value = teacherId;
  document.getElementById("edit_min_students").value = minStudents;
  document.getElementById("edit_max_students").value = maxStudents;
  if (typeof ModalHandler !== "undefined") {
    ModalHandler.instance.open("#edit-capacity-modal");
  } else {
    document.getElementById("edit-capacity-modal").dataset.state = "open";
  }
}

(() => {
  const root = document.querySelector('[data-tm="project_batch_teachers"]');
  if (!root) return;

  root.addEventListener("tm:render", (e) => {
    const visibleRows = e.detail.visibleRows;

    root.querySelectorAll(".actions-container").forEach((container) => {
      if (container.dataset.rendered) return;
      const teacherId = container.dataset.teacherId;
      const teacherData = visibleRows.find(
        (r) => String(r.teacher_id) === String(teacherId),
      );
      if (!teacherData) return;

      const currentLoad = teacherData.current_load || 0;
      const minStudents = teacherData.min_students || 0;
      const maxStudents = teacherData.max_students || 0;

      let actionsHTML = `
        <div class="dropdown" data-dropdown>
          <button type="button" class="btn btn-icon dropdown__trigger" data-variant="secondary" data-size="md" data-dropdown-trigger data-dropdown-trigger-mode="click">
            <i class="fa-solid fa-ellipsis-vertical"></i>
          </button>
          <div class="dropdown__content dropdown__content--right" data-dropdown-content>
            <div class="dropdown__menu">
              <button type="button" class="dropdown__item" onclick="openEditCapacityModal('${teacherId}', ${minStudents}, ${maxStudents})">
                <i class="fa-solid fa-pen-to-square w-4 text-center mr-2"></i> Sửa chỉ tiêu
              </button>
      `;

      if (currentLoad === 0) {
        actionsHTML += `
              <form action="${window.API_URL_TEACHERS}/remove" method="POST">
                ${window.CSRF_FIELD || ""}
                <input type="hidden" name="teacher_id" value="${teacherId}">
                <button type="button" class="dropdown__item btn-confirm-action" data-confirm-msg="Bạn có chắc chắn muốn xóa giảng viên ${teacherData.full_name || "này"} khỏi đợt đồ án?" data-modal-trigger="#action-confirm-modal">
                  <i class="fa-solid fa-trash-can w-4 text-center mr-2"></i> Loại khỏi đợt
                </button>
              </form>
        `;
      } else {
        actionsHTML += `
              <button type="button" class="dropdown__item opacity-50 cursor-not-allowed" title="Không thể xóa do giảng viên đang hướng dẫn sinh viên" disabled>
                <i class="fa-solid fa-trash-can w-4 text-center mr-2"></i> Loại khỏi đợt
              </button>
        `;
      }

      actionsHTML += `
            </div>
          </div>
        </div>
      `;
      container.innerHTML = actionsHTML;
      container.dataset.rendered = "true";

      // Attach confirm event listeners
      container.querySelectorAll(".btn-confirm-action").forEach((btn) => {
        btn.addEventListener("click", function (e) {
          e.preventDefault();
          const msg = this.dataset.confirmMsg;
          const modalId = this.dataset.modalTrigger;
          const modal = document.querySelector(modalId);
          if (modal) {
            const msgEl = modal.querySelector("#action-confirm-msg");
            if (msgEl) msgEl.textContent = msg;

            const confirmBtn = modal.querySelector("#action-confirm-btn");
            if (confirmBtn) {
              const clone = confirmBtn.cloneNode(true);
              confirmBtn.parentNode.replaceChild(clone, confirmBtn);
              clone.addEventListener("click", () => {
                this.closest("form").submit();
              });
            }
            if (typeof ModalHandler !== "undefined") {
              ModalHandler.instance.open(modalId);
            } else {
              modal.dataset.state = "open";
            }
          }
        });
      });

      if (typeof DropdownHandler !== "undefined") {
        const dropdown = container.querySelector(".dropdown");
        if (dropdown) {
          DropdownHandler.instance.register(dropdown);
        }
      }
    });
  });
})();
