import { TableManager } from '../../table/index.js';

const root = document.querySelector('[data-server-table-url]');
if (root) {
  const table = TableManager.get(root.dataset.tm);
  let controller;

  const load = async (state = {}) => {
    controller?.abort();
    controller = new AbortController();
    const url = new URL(root.dataset.serverTableUrl, window.location.origin);
    const page = state.pagination ? state.pagination.pageIndex + 1 : (state.page || 1);
    const limit = state.pagination?.pageSize || state.limit || 15;
    url.searchParams.set('page', page);
    url.searchParams.set('limit', limit);
    if (state.search) url.searchParams.set('search', state.search);
    if (root.dataset.serverTableFilter) url.searchParams.set('filter', root.dataset.serverTableFilter);
    if (state.sort?.col) {
      url.searchParams.set('sort[col]', state.sort.col);
      url.searchParams.set('sort[dir]', state.sort.dir);
    }
    (state.filters || []).forEach((filter, index) => {
      url.searchParams.set(`filters[${index}][col]`, filter.col);
      url.searchParams.set(`filters[${index}][op]`, filter.op);
      url.searchParams.set(`filters[${index}][value]`, filter.value);
    });

    try {
      const response = await fetch(url, { signal: controller.signal, headers: { Accept: 'application/json' } });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      const json = await response.json();
      const payload = Array.isArray(json.data) ? json : (json.data ?? json);
      const rows = Array.isArray(payload.data)
        ? payload.data
        : (Array.isArray(payload.rows) ? payload.rows : []);
      const meta = payload.meta ?? payload;
      table.loadData({
        rows,
        total: meta.total ?? payload.total ?? rows.length,
        page: meta.current_page ?? payload.page ?? page,
        limit: meta.per_page ?? payload.limit ?? limit,
      });
    } catch (error) {
      if (error.name !== 'AbortError') {
        console.error(`[TableManager] Failed to load ${root.dataset.tm}`, error);
        window.toast?.error?.('Không thể tải dữ liệu bảng.');
      }
    }
  };

  root.addEventListener('tm:state-change', event => load(event.detail.state));
  load();
}
