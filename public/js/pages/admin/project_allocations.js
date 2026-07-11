function openManualAssignModal(groupId) {
  document.getElementById("manual_group_id").value = groupId;
  ModalHandler.instance.open("#manual-assign-modal");
}

window.openReplaceMemberModal = function (groupId, oldStudentId) {
  console.log(
    `[DEBUG] openReplaceMemberModal called for group ${groupId}, oldStudent: ${oldStudentId}`,
  );
  const modal = document.getElementById("replace-member-modal");
  if (modal) {
    modal.querySelector("#replace_group_id").value = groupId;
    modal.querySelector("#replace_old_student_id").value = oldStudentId;

    if (typeof ModalHandler !== "undefined") {
      ModalHandler.instance.open("#replace-member-modal");
    } else {
      modal.dataset.state = "open";
    }
  } else {
    console.error("[DEBUG] replace-member-modal not found!");
  }
};

document.addEventListener("DOMContentLoaded", () => {});

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
    } else {
      ModalHandler.instance.open("#action-confirm-modal");
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

(() => {
  const root = document.getElementById("allocation_table");
  if (!root) return;

  root.addEventListener("tm:render", (e) => {
    const visibleRows = e.detail.visibleRows;

    root.querySelectorAll(".members-container").forEach((container) => {
      if (container.dataset.rendered) return;

      const groupId = container.dataset.groupId;
      const groupData = visibleRows.find(
        (r) => String(r.id) === String(groupId),
      );

      if (groupData && groupData.members) {
        container.innerHTML = "";

        groupData.members.forEach((m) => {
          const rowDiv = document.createElement("div");
          rowDiv.className = "allocation-table__member flex flex-col";
          if (!m.is_eligible) {
            rowDiv.classList.add("allocation-table__member--ineligible");
          }

          const nameDiv = document.createElement("div");
          nameDiv.className = "popover";

          let badgesHTML = "";
          if (groupData.is_admin_approved_solo) {
            badgesHTML +=
              '<span class="badge ml-1" data-variant="secondary">Làm 1 mình</span>';
          } else if (m.is_leader) {
            badgesHTML +=
              '<span class="badge ml-1" data-variant="primary">Nhóm trưởng</span>';
          } else if (!m.is_confirmed) {
            badgesHTML +=
              '<span class="badge ml-1" data-variant="warning" title="Chưa xác nhận tham gia nhóm">Chưa xác nhận</span>';
          }

          nameDiv.innerHTML = `
            <div class="popover__trigger flex items-center">
              <i class="fa-solid fa-circle-info mr-2"></i>
              <span class="font-medium">${m.full_name}</span>
              ${badgesHTML}
            </div>
            <div class="popover__content" data-side="right" data-align="start">
              <div class="text-sm font-semibold mb-2">Thông tin sinh viên</div>
              <div class="text-sm space-y-2">
                <div><i class="fa-solid fa-id-card w-4 text-center mr-1"></i> MSSV: <strong>${m.student_code}</strong></div>
                <div><i class="fa-solid fa-graduation-cap w-4 text-center mr-1"></i> Lớp: <strong>${m.classroom_name || "Chưa cập nhật"}</strong></div>
                <div><i class="fa-solid fa-phone w-4 text-center mr-1"></i> SĐT: <strong>${m.phone || "Chưa cập nhật"}</strong></div>
                <div><i class="fa-solid fa-envelope w-4 text-center mr-1"></i> Email: <strong>${m.email || "Chưa cập nhật"}</strong></div>
              </div>
            </div>
          `;

          rowDiv.appendChild(nameDiv);

          container.appendChild(rowDiv);

          // Register the new popover
          if (typeof PopoverHandler !== "undefined") {
            PopoverHandler.instance.register(nameDiv);
          }
        });

        container.dataset.rendered = "true";
      }
    });

    root.querySelectorAll(".aspirations-container").forEach((container) => {
      if (container.dataset.rendered) return;

      const groupId = container.dataset.groupId;
      const groupData = visibleRows.find(
        (r) => String(r.id) === String(groupId),
      );

      if (
        groupData &&
        groupData.aspirations &&
        groupData.aspirations.length > 0
      ) {
        container.innerHTML = "";

        if (groupData.assigned_topic_id) {
          const acceptedAsp = groupData.aspirations.find(
            (a) => String(a.topic_id) === String(groupData.assigned_topic_id),
          );
          if (acceptedAsp) {
            container.innerHTML = `<span class="badge" data-variant="success">Đạt NV${acceptedAsp.priority}</span>`;
          } else {
            container.innerHTML = `<span class="badge" data-variant="secondary">Phân bổ thủ công</span>`;
          }
        } else {
          let isLocked = !!groupData.aspirations[0].locked_at;

          if (!isLocked) {
            container.innerHTML +=
              '<div><span class="badge" data-variant="warning"><i class="fa-solid fa-unlock mr-1"></i> Chưa chốt</span></div>';
          }

          groupData.aspirations.forEach((asp) => {
            const rowDiv = document.createElement("div");
            rowDiv.style.marginBottom = "0.25rem";
            rowDiv.className = "line-clamp-1 text-xs";
            rowDiv.title = asp.topic_title || "Đề tài #" + asp.topic_id;
            rowDiv.innerHTML = `<span class="badge" data-variant="outline">NV${asp.priority}</span> ${asp.topic_title || "Đề tài #" + asp.topic_id}`;
            container.appendChild(rowDiv);
          });
        }
        container.dataset.rendered = "true";
      } else if (groupData) {
        container.innerHTML =
          '<span class="text-muted italic">Chưa đăng ký</span>';
        container.dataset.rendered = "true";
      }
    });

    root.querySelectorAll(".actions-container").forEach((container) => {
      if (container.dataset.rendered) return;
      const groupId = container.dataset.groupId;
      const groupData = visibleRows.find(
        (r) => String(r.id) === String(groupId),
      );
      if (!groupData) return;

      let isEligible = true;
      let eligibleCount = 0;
      let ineligibleCount = 0;
      let oldStudentId = null;
      let leader = null;

      if (groupData.members) {
        groupData.members.forEach((m) => {
          if (!m.is_eligible) {
            isEligible = false;
            ineligibleCount++;
            oldStudentId = m.student_id;
          } else {
            eligibleCount++;
          }
          if (m.is_leader) leader = m;
        });
        if (!leader && groupData.members.length > 0)
          leader = groupData.members[0];
      }

      const groupName = leader
        ? `${leader.full_name} (${leader.student_code})`
        : `nhóm #${groupId}`;

      let actionsHTML = `
        <div class="dropdown" data-dropdown>
          <button type="button" class="btn btn-icon dropdown__trigger" data-variant="secondary" data-size="md" data-dropdown-trigger data-dropdown-trigger-mode="click">
            <i class="fa-solid fa-ellipsis-vertical"></i>
          </button>
          <div class="dropdown__content dropdown__content--right" data-dropdown-content>
            <div class="dropdown__menu">
      `;

      if (isEligible) {
        actionsHTML += `
          <button type="button" class="dropdown__item" onclick="openManualAssignModal('${groupId}')">
            <i class="fa-solid fa-pen-to-square w-4 text-center mr-2"></i> Gán thủ công
          </button>
        `;
      } else {
        actionsHTML += `
          <form action="${window.ALLOCATION_BASE_URL}/dissolve-group" method="POST">
            ${window.CSRF_FIELD || ""}
            <input type="hidden" name="group_id" value="${groupId}">
            <button type="button" class="dropdown__item btn-confirm-action" data-confirm-msg="Bạn có chắc chắn muốn giải tán nhóm của ${groupName}?" data-modal-trigger="#action-confirm-modal">
              <i class="fa-solid fa-trash-can w-4 text-center mr-2"></i> Giải tán nhóm
            </button>
          </form>
        `;

        if (eligibleCount === 1 && ineligibleCount === 1) {
          actionsHTML += `
            <form action="${window.ALLOCATION_BASE_URL}/approve-solo" method="POST">
              ${window.CSRF_FIELD || ""}
              <input type="hidden" name="group_id" value="${groupId}">
              <button type="button" class="dropdown__item btn-confirm-action" data-confirm-msg="Xác nhận cho phép nhóm của ${groupName} làm đồ án 1 mình?" data-modal-trigger="#action-confirm-modal">
                <i class="fa-solid fa-user w-4 text-center mr-2"></i> Cho phép làm 1 mình
              </button>
            </form>
            <button type="button" class="dropdown__item" onclick="openReplaceMemberModal(${groupId}, ${oldStudentId})">
              <i class="fa-solid fa-user-pen w-4 text-center mr-2"></i> Thay thế thành viên
            </button>
          `;
        }
      }

      actionsHTML += `
            </div>
          </div>
        </div>
      `;
      container.innerHTML = actionsHTML;
      container.dataset.rendered = "true";

      console.log(`[DEBUG] Rendered dropdown for group ${groupId}`);

      // Attach confirm event listeners for dynamically added buttons BEFORE DropdownHandler portals them to body
      container.querySelectorAll(".btn-confirm-action").forEach((btn) => {
        btn.addEventListener("click", function (e) {
          console.log(
            `[DEBUG] Clicked .btn-confirm-action for group ${groupId}`,
            this,
          );
          e.preventDefault(); // Just in case
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
          } else {
            console.error(`[DEBUG] Modal ${modalId} not found!`);
          }
        });
      });

      if (typeof DropdownHandler !== "undefined") {
        const dropdown = container.querySelector(".dropdown");
        if (dropdown) {
          console.log(
            `[DEBUG] Registering dropdown for group ${groupId} with DropdownHandler`,
          );
          DropdownHandler.instance.register(dropdown);
        } else {
          console.warn(`[DEBUG] Dropdown root not found for group ${groupId}`);
        }
      } else {
        console.error(`[DEBUG] DropdownHandler is undefined!`);
      }
    });
  });

  root.addEventListener("tm:state-change", async (e) => {
    const { reason, state } = e.detail;
    const tm = window.TableManager?.get("allocation_table");

    if (!tm || !state.pagination) return;

    const page = (state.pagination?.pageIndex || 0) + 1;
    const limit = state.pagination?.pageSize || 15;

    const url = new URL(window.API_URL_ALLOCATIONS, window.location.origin);
    url.searchParams.set("page", page);
    url.searchParams.set("limit", limit);

    if (state.search) url.searchParams.set("search", state.search);

    const activeTab = document.querySelector(
      ".tabs__trigger[data-tabs-trigger-state='active']",
    );
    if (activeTab) {
      const status = activeTab.dataset.tabsTrigger;
      if (status !== "all") {
        url.searchParams.set("status", status);
      }
    }

    if (state.filters && state.filters.teacher_id) {
      url.searchParams.set("teacher_id", state.filters.teacher_id);
    }

    if (state.sort?.col) {
      url.searchParams.set("sort[col]", state.sort.col);
      url.searchParams.set("sort[dir]", state.sort.dir);
    }

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has("status")) {
      url.searchParams.set("status", urlParams.get("status"));
    }

    try {
      const response = await fetch(url.toString());
      const data = await response.json();
      if (data.success) {
        tm.loadData({
          rows: data.data.data,
          total: data.data.total,
          page: data.data.page,
          limit: data.data.limit,
        });
      } else {
        console.error("Lỗi API:", data.message);
      }
    } catch (err) {
      console.error("Lỗi khi tải dữ liệu phân bổ:", err);
    }
  });

  const initTable = () => {
    const tm = window.TableManager?.get("allocation_table");
    if (tm) {
      const state =
        typeof tm.getState === "function" ? tm.getState() : tm.state;
      tm.root.dispatchEvent(
        new CustomEvent("tm:state-change", {
          detail: {
            reason: "pagination",
            state: state,
          },
        }),
      );
    } else {
      setTimeout(initTable, 50);
    }
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initTable);
  } else {
    initTable();
  }
})();
