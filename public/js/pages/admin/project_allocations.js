function openManualAssignModal(groupId) {
  document.getElementById("manual_group_id").value = groupId;
  ModalHandler.instance.open("#manual-assign-modal");
}

function openReplaceMemberModal(groupId, oldStudentId) {
  document.getElementById("replace_group_id").value = groupId;
  document.getElementById("replace_old_student_id").value = oldStudentId;
  ModalHandler.instance.open("#replace-member-modal");
}

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
          nameDiv.innerHTML = `<span>${m.full_name} (${m.student_code})</span>`;
          if (groupData.is_admin_approved_solo) {
            nameDiv.insertAdjacentHTML(
              "beforeend",
              '<span class="badge ml-1" data-variant="secondary">Làm 1 mình</span>',
            );
          } else if (m.is_leader) {
            nameDiv.insertAdjacentHTML(
              "beforeend",
              '<span class="badge ml-1" data-variant="primary">Nhóm trưởng</span>',
            );
          } else if (!m.is_confirmed) {
            nameDiv.insertAdjacentHTML(
              "beforeend",
              '<span class="badge ml-1" data-variant="warning" title="Chưa xác nhận tham gia nhóm">Chưa xác nhận</span>',
            );
          }
          rowDiv.appendChild(nameDiv);

          if (!m.is_eligible && m.phone) {
            const phoneDiv = document.createElement("div");
            phoneDiv.className = "text-xs mt-1";
            phoneDiv.innerHTML = `<i class="text-xs fa-solid fa-phone mr-1"></i> ${m.phone}`;
            rowDiv.appendChild(phoneDiv);
          }

          container.appendChild(rowDiv);
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
        let isLocked = !!groupData.aspirations[0].locked_at;

        if (!isLocked) {
          container.innerHTML +=
            '<div><span class="badge" data-variant="warning"><i class="fa-solid fa-unlock mr-1"></i> Chưa chốt</span></div>';
        }

        groupData.aspirations.forEach((asp) => {
          const rowDiv = document.createElement("div");
          rowDiv.style.marginBottom = "0.25rem";
          rowDiv.className = "line-clamp-1";
          rowDiv.title = asp.topic_title || "Đề tài #" + asp.topic_id;
          rowDiv.innerHTML = `<span class="badge" data-variant="outline">NV${asp.priority}</span> ${asp.topic_title || "Đề tài #" + asp.topic_id}`;
          container.appendChild(rowDiv);
        });
        container.dataset.rendered = "true";
      } else if (groupData) {
        container.innerHTML =
          '<span class="text-muted italic">Chưa đăng ký</span>';
        container.dataset.rendered = "true";
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
