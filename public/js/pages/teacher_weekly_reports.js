const weekSelect = document.querySelector('[data-select-id="teacher-weekly-report-week"]');

weekSelect?.addEventListener("select:change", (event) => {
  const week = String(event.detail.value || "");
  if (!week) return;

  const target = new URL(window.location.href);
  const currentWeek = target.searchParams.get("week") || weekSelect.dataset.selectDefaultValue || "";
  if (week === String(currentWeek)) return;

  target.searchParams.set("week", week);
  window.location.assign(target.toString());
});

const table = document.querySelector('[data-tm="weekly_reports_table"]');

const enhanceEmptyState = () => {
  const cell = table?.querySelector(".tm-empty");
  if (!cell || cell.querySelector(".empty")) return;

  cell.textContent = "";
  cell.innerHTML = `
    <div class="empty">
      <div class="empty__header">
        <div class="empty__media"><i class="fa-solid fa-user-clock" aria-hidden="true"></i></div>
        <div class="empty__title">Không có sinh viên phù hợp</div>
        <div class="empty__description">Chưa có sinh viên được phân công hoặc không có kết quả phù hợp với bộ lọc hiện tại.</div>
      </div>
    </div>`;
};

table?.addEventListener("tm:render", enhanceEmptyState);
requestAnimationFrame(enhanceEmptyState);

// Xử lý nút Đánh dấu đã duyệt tất cả
const markAllSeenBtn = document.getElementById('markAllSeenBtn');
if (markAllSeenBtn) {
  markAllSeenBtn.addEventListener('click', async () => {
    try {
      const dataStr = document.getElementById('weeklyDataJson')?.textContent || document.querySelector('[data-tm-data="weekly_reports_table"]')?.textContent;
      if (!dataStr) return;
      const data = JSON.parse(dataStr);
      
      const reportIds = data.rows
        .filter(row => row.report_id && (!row.is_seen_by_teacher || row.is_seen_by_teacher === 0))
        .map(row => row.report_id);
        
      if (reportIds.length === 0) {
        window.toast?.info('Thông báo', 'Tất cả báo cáo trong tuần này đã được duyệt.');
        return;
      }
      
      const url = markAllSeenBtn.getAttribute('data-url');
      if (!url) throw new Error('Không tìm thấy đường dẫn xử lý');
      
      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      
      markAllSeenBtn.disabled = true;
      markAllSeenBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Đang xử lý...';
      
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ report_ids: reportIds })
      });
      
      const json = await res.json();
      if (!res.ok || !json.success) {
        throw new Error(json.message || 'Lỗi hệ thống');
      }
      
      window.toast?.success('Thành công', json.message);
      setTimeout(() => location.reload(), 500);
    } catch (e) {
      window.toast?.error('Lỗi', e.message);
      markAllSeenBtn.disabled = false;
      markAllSeenBtn.innerHTML = '<i class="fa-solid fa-check-double mr-2"></i> Đánh dấu đã duyệt tất cả';
    }
  });
}
