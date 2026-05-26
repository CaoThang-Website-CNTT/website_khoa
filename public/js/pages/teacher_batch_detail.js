/**
 * teacher_batch_detail.js
 * Logic cho trang chi tiết đợt thực tập của giảng viên.
 */

document.addEventListener('DOMContentLoaded', () => {
  const tableId = 'students_table';

  // 1. Xử lý sự kiện Actions (Xem chi tiết, Nhập điểm) qua Event Delegation
  const tableRoot = document.querySelector(`[data-tm="${tableId}"]`);
  if (tableRoot) {
    tableRoot.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-action]');
      if (!btn) return;

      const action = btn.dataset.action;
      const id = btn.dataset.id;
      const studentName = btn.dataset.name;

      if (action === 'view') {
        alert(`Xem chi tiết sinh viên: ${studentName} (ID: ${id})`);
        // TODO: Chuyển hướng hoặc mở modal chi tiết SV
      } else if (action === 'grade') {
        alert(`Mở form nhập điểm cho: ${studentName}`);
        // TODO: Mở modal nhập điểm
      }
    });

    // 2. Lắng nghe các event từ TableManager nếu cần
    tableRoot.addEventListener('tm:render', () => {
      console.log('[PageJS] Bảng đã render lại.');
    });
  }
});
