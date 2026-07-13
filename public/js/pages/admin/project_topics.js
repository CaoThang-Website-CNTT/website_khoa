import { TableManager } from '../../table/index.js';

const root = document.getElementById('project-topics-page');
const configElement = document.getElementById('project-topics-config');
if (!root || !configElement) throw new Error('Thiếu cấu hình trang quản lý đề tài.');

const config = JSON.parse(configElement.textContent);
const table = TableManager.get('topics_table');
const errorBox = root.querySelector('[data-topic-error]');
const errorMessage = root.querySelector('[data-topic-error-message]');
const allowedStatuses = new Set(['all', 'draft', 'pending', 'approved', 'rejected']);
const query = new URLSearchParams(window.location.search);
const state = {
  status: allowedStatuses.has(query.get('status')) ? query.get('status') : 'all',
  search: (query.get('search') || '').slice(0, 200),
  teacher_id: query.get('teacher_id') || '',
  page: Math.max(1, Number.parseInt(query.get('page') || '1', 10) || 1),
  limit: Math.min(100, Math.max(5, Number.parseInt(query.get('limit') || '15', 10) || 15)),
};
let activeRequest = null;
let reviewTopicId = null;

const openModal = selector => ModalHandler.instance.open(selector);
const closeModal = () => ModalHandler.instance.close();

function setButtonLoading(button, loading, label) {
  if (!button) return;
  if (!button.dataset.originalLabel) button.dataset.originalLabel = button.textContent;
  button.disabled = loading;
  button.textContent = loading ? label : button.dataset.originalLabel;
}

function setActiveTab() {
  root.querySelectorAll('[data-topic-status]').forEach(tab => {
    const active = tab.dataset.topicStatus === state.status;
    tab.dataset.tabsTriggerState = active ? 'active' : 'idle';
    tab.setAttribute('aria-selected', active ? 'true' : 'false');
    tab.tabIndex = active ? 0 : -1;
  });
}

function updateUrl(replace = false) {
  const params = new URLSearchParams();
  if (state.status !== 'all') params.set('status', state.status);
  if (state.search) params.set('search', state.search);
  if (state.teacher_id) params.set('teacher_id', state.teacher_id);
  if (state.page > 1) params.set('page', String(state.page));
  if (state.limit !== 15) params.set('limit', String(state.limit));
  const url = `${window.location.pathname}${params.size ? `?${params}` : ''}`;
  window.history[replace ? 'replaceState' : 'pushState']({ ...state }, '', url);
}

function showError(message = '') {
  errorMessage.textContent = message;
  errorBox.classList.toggle('hidden', !message);
}

function updateCounts(counts) {
  Object.entries(counts || {}).forEach(([status, count]) => {
    const badge = root.querySelector(`[data-topic-count="${status}"]`);
    if (badge) badge.textContent = String(count);
  });
}

function syncBulkSelectionUi() {
  const enabled = state.status === 'pending';
  table.root.querySelectorAll('colgroup > col:first-child, [data-tm-col-key="_checkbox"], .tm-tr > .tm-td:first-child').forEach(element => {
    element.hidden = !enabled;
    element.style.display = enabled ? '' : 'none';
  });
  table.root.querySelectorAll('.tm-row-checkbox, .tm-check-all').forEach(input => {
    input.disabled = !enabled;
  });
  if (!enabled) table.clearSelection();
}

async function request(url, options = {}) {
  const response = await fetch(url, options);
  const payload = await response.json().catch(() => null);
  if (!response.ok || !payload?.success) {
    throw new Error(payload?.message || `Yêu cầu thất bại (HTTP ${response.status}).`);
  }
  return payload;
}

async function loadTopics({ updateHistory = false, replaceHistory = false } = {}) {
  activeRequest?.abort();
  activeRequest = new AbortController();
  root.setAttribute('aria-busy', 'true');
  showError();
  setActiveTab();
  if (updateHistory) updateUrl(replaceHistory);

  const params = new URLSearchParams({ status: state.status, page: state.page, limit: state.limit });
  if (state.search) params.set('search', state.search);
  if (state.teacher_id) params.set('teacher_id', state.teacher_id);

  try {
    const payload = await request(`${config.listUrl}?${params}`, { signal: activeRequest.signal });
    const data = payload.data;
    table.clearSelection();
    table.loadData({ rows: data.rows, total: data.pagination.total, page: data.pagination.page, limit: data.pagination.limit });
    syncBulkSelectionUi();
    state.page = data.pagination.page;
    updateCounts(data.counts);
  } catch (error) {
    if (error.name !== 'AbortError') {
      showError(error.message);
      table.loadData({ rows: [], total: 0, page: 1, limit: state.limit });
    }
  } finally {
    root.setAttribute('aria-busy', 'false');
  }
}

async function reviewTopic(action, reason, button) {
  if (!reviewTopicId || button.disabled) return;
  setButtonLoading(button, true, 'Đang xử lý...');
  try {
    const payload = await request(`${config.topicApiUrl}/${encodeURIComponent(reviewTopicId)}/${action}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken },
      body: reason == null ? undefined : JSON.stringify({ reason }),
    });
    window.toast?.success('Thành công', payload.message);
    closeModal(action === 'approve' ? '#approve-topic-modal' : '#reject-topic-modal');
    await loadTopics();
  } catch (error) {
    window.toast?.error('Không thể cập nhật', error.message);
  } finally {
    setButtonLoading(button, false);
  }
}

root.querySelector('[data-topic-tabs]').addEventListener('click', event => {
  const tab = event.target.closest('[data-topic-status]');
  if (!tab || tab.dataset.topicStatus === state.status) return;
  state.status = tab.dataset.topicStatus;
  state.page = 1;
  loadTopics({ updateHistory: true });
});

table.root.addEventListener('tm:state-change', event => {
  if (event.detail.reason === 'search') {
    state.search = event.detail.state.search.trim().slice(0, 200);
    state.page = 1;
    loadTopics({ updateHistory: true, replaceHistory: true });
  } else if (event.detail.reason === 'filter') {
    const teacherFilter = event.detail.state.filters.find(f => f.col === 'teacher');
    state.teacher_id = teacherFilter ? teacherFilter.value : '';
    state.page = 1;
    loadTopics({ updateHistory: true, replaceHistory: true });
  }
});

table.root.addEventListener('tm:pagination:change', event => {
  state.page = event.detail.page;
  state.limit = event.detail.limit;
  loadTopics({ updateHistory: true });
});

table.root.addEventListener('tm:render', syncBulkSelectionUi);

table.root.addEventListener('click', event => {
  const approve = event.target.closest('.btn-approve');
  const reject = event.target.closest('.btn-reject');
  const reason = event.target.closest('.btn-reason');
  if (approve) {
    reviewTopicId = approve.dataset.id;
    const topicTitle = approve.dataset.title;
    const desc = document.getElementById('approve-topic-desc');
    if (desc) desc.innerHTML = `Bạn có chắc chắn muốn duyệt đề tài <span class="font-semibold">${topicTitle}</span>? Đề tài đã duyệt sẽ sẵn sàng để công bố cho sinh viên đăng ký.`;
    openModal('#approve-topic-modal');
  } else if (reject) {
    reviewTopicId = reject.dataset.id;
    const topicTitle = reject.dataset.title;
    const desc = document.getElementById('reject-topic-desc');
    if (desc) desc.innerHTML = `Bạn có chắc chắn muốn từ chối đề tài <span class="font-semibold">${topicTitle}</span>? Giảng viên sẽ nhận được lý do này để chỉnh sửa đề tài.`;
    const reasonInput = document.getElementById('reject-topic-reason');
    const reasonError = document.getElementById('reject-topic-error');
    if (reasonInput) {
      reasonInput.value = '';
      reasonInput.removeAttribute('aria-invalid');
    }
    reasonError?.classList.add('hidden');
    openModal('#reject-topic-modal');
  } else if (reason) {
    document.querySelector('[data-topic-reason-text]').textContent = reason.dataset.reason;
    openModal('#topic-reason-modal');
  }
}, true);

document.getElementById('confirm-approve-topic').addEventListener('click', function () {
  reviewTopic('approve', null, this);
});

document.getElementById('confirm-reject-topic').addEventListener('click', function () {
  const input = document.getElementById('reject-topic-reason');
  const fieldError = document.getElementById('reject-topic-error');
  if (!input) {
    window.toast?.error('Không thể từ chối đề tài', 'Không tìm thấy trường nhập lý do. Vui lòng tải lại trang.');
    return;
  }
  const reason = input.value.trim();
  fieldError?.classList.toggle('hidden', Boolean(reason));
  input.toggleAttribute('aria-invalid', !reason);
  if (!reason) return input.focus();
  reviewTopic('reject', reason, this);
});

TableManager.registerBulkActions('topics_table', {
  countLabel: count => `Đã chọn: ${count}`,
  actions: [{
    id: 'approve', label: 'Duyệt đã chọn', icon: 'fa-solid fa-check', variant: 'primary',
    confirm: { message: 'Duyệt các đề tài đã chọn?' },
    onClick: async ({ selectedIds }) => {
      table.setBulkActionLoading(true, 'Đang duyệt đề tài...');
      try {
        const payload = await request(`${config.topicApiUrl}/bulk-approve`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken },
          body: JSON.stringify({ topic_ids: selectedIds }),
        });
        const skipped = payload.data.skipped || [];
        if (skipped.length) {
          window.toast?.warning('Đã xử lý một phần', `${payload.message} ${skipped.map(item => `#${item.id}: ${item.reason}`).join(' · ')}`);
        } else {
          window.toast?.success('Thành công', payload.message);
        }
        await loadTopics();
      } catch (error) {
        window.toast?.error('Không thể duyệt đề tài', error.message);
      } finally {
        table.setBulkActionLoading(false);
      }
    },
  }],
});

root.querySelector('[data-topic-retry]').addEventListener('click', () => loadTopics());
window.addEventListener('popstate', () => {
  const params = new URLSearchParams(window.location.search);
  state.status = allowedStatuses.has(params.get('status')) ? params.get('status') : 'all';
  state.search = (params.get('search') || '').slice(0, 200);
  state.teacher_id = params.get('teacher_id') || '';
  state.page = Math.max(1, Number.parseInt(params.get('page') || '1', 10) || 1);
  state.limit = Math.min(100, Math.max(5, Number.parseInt(params.get('limit') || '15', 10) || 15));
  loadTopics();
});

setActiveTab();
const searchInput = table.root.querySelector('.tm-search__input');
if (searchInput) searchInput.value = state.search;
loadTopics({ updateHistory: true, replaceHistory: true });
