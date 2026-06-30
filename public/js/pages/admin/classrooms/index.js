import { TableManager } from '../../../table/index.js';

document.addEventListener('DOMContentLoaded', () => {
  const tm = TableManager.get("classrooms_table");
  if (!tm) return;

  tm.root.addEventListener("tm:state-change", async (e) => {
    const { reason, state } = e.detail;
    
    if (reason === "search" || reason === "filter" || reason === "sort" || reason === "pagination") {
      try {
        const url = new URL(window.API_CLASSROOMS_URL);

        if (state.search) url.searchParams.set("search", `%${state.search}%`);
        if (state.page) url.searchParams.set("page", state.page);
        if (state.limit) url.searchParams.set("limit", state.limit);
        
        if (state.sort && state.sort.col) {
          url.searchParams.set("sort[col]", state.sort.col);
          url.searchParams.set("sort[dir]", state.sort.dir);
        }
        
        if (state.filters && state.filters.length > 0) {
          state.filters.forEach((f, i) => {
            url.searchParams.set(`filters[${i}][col]`, f.col);
            url.searchParams.set(`filters[${i}][op]`, f.op);
            url.searchParams.set(`filters[${i}][value]`, f.value);
          });
        }

        const response = await fetch(url);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const result = await response.json();

        const payload = result.data;
        tm.loadData({
          rows: payload.data,
          total: payload.total,
          page: payload.page,
          limit: payload.limit
        });
      } catch (error) {
        console.error("Lỗi khi fetch dữ liệu:", error);
      }
    }
  });

  tm.root.addEventListener("tm:pagination:change", (e) => {
    const { page, limit } = e.detail;
    const url = new URL(window.location.href);
    url.searchParams.set("page", page);
    url.searchParams.set("limit", limit);
    window.history.replaceState({}, '', url);
  });

  if (window.INITIAL_DATA) {
    tm.loadData(window.INITIAL_DATA);
  }

  // Xử lý Xóa Lớp Học
  const deleteForm = document.getElementById('delete-form');
  const deleteNameEl = document.getElementById('delete-classroom-name');
  const deleteConfirmBtn = document.getElementById('delete-confirm-btn');

  let pendingDeleteId = null;

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-delete-classroom');
    if (!btn) return;
    pendingDeleteId = btn.dataset.classroomId;
    if (deleteNameEl) {
      deleteNameEl.textContent = btn.dataset.classroomName;
    }
  });

  deleteConfirmBtn?.addEventListener('click', () => {
    if (!pendingDeleteId || !deleteForm) return;
    deleteForm.action = window.DELETE_CLASSROOM_URL + pendingDeleteId;
    deleteForm.submit();
  });
});
