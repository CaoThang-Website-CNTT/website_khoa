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
