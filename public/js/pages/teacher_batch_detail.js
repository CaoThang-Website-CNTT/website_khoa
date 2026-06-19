document.addEventListener("DOMContentLoaded", () => {
  const tableId = "students_table";

  // 1. Xử lý sự kiện Actions (Xem chi tiết, Nhập điểm) qua Event Delegation
  const tableRoot = document.querySelector(`[data-tm="${tableId}"]`);
  if (tableRoot) {
    tableRoot.addEventListener("click", (e) => {
      const btn = e.target.closest("[data-action]");
      if (!btn) return;

      const action = btn.dataset.action;
      const id = btn.dataset.id;
      const studentName = btn.dataset.name;

      if (action === "view") {
        alert(`Xem chi tiết sinh viên: ${studentName} (ID: ${id})`);
        // TODO: Chuyển hướng hoặc mở modal chi tiết SV
      } else if (action === "grade") {
        const currentUrl = window.location.pathname.replace(/\/$/, '');
        window.location.href = `${currentUrl}/grade/${id}`;
      }
    });

    // 2. Lắng nghe các event từ TableManager nếu cần
    tableRoot.addEventListener("tm:render", () => {
      console.log("[PageJS] Bảng đã render lại.");
    });
  }
});
