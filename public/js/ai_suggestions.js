(function () {
  const modal = document.querySelector('#cms-ai-suggestion-modal');
  if (!modal) return;

  const ideaInput = modal.querySelector('#cms-ai-suggestion-idea');
  const submit = modal.querySelector('[data-cms-ai-submit]');
  const apply = modal.querySelector('[data-cms-ai-apply]');
  const preview = modal.querySelector('[data-cms-ai-preview]');
  const previewText = modal.querySelector('[data-cms-ai-preview-text]');
  const status = modal.querySelector('[data-cms-ai-status]');
  const statusText = modal.querySelector('[data-cms-ai-status-text]');
  const statusIcon = modal.querySelector('[data-cms-ai-status-icon]');
  let activeButton = null;
  let suggestion = '';
  let thinkingTimer = null;
  let generatingTimer = null;

  const csrfToken = () => document.querySelector('#cms-page-form input[name="_token"]')?.value || window.CSRF_TOKEN || '';
  const setStatus = (state, text, icon) => {
    status.dataset.state = state;
    statusText.textContent = text;
    statusIcon.className = icon;
  };

  document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-cms-ai-suggest]');
    if (!button) return;
    activeButton = button;
    ideaInput.value = '';
    suggestion = '';
    preview.hidden = true;
    apply.hidden = true;
    submit.hidden = false;
    submit.disabled = false;
    setStatus('ready', 'Nhập yêu cầu để bắt đầu.', 'fa-regular fa-lightbulb');
    window.setTimeout(() => ideaInput.focus(), 0);
  });

  submit?.addEventListener('click', async () => {
    if (!activeButton || !window.CmsEditor) return;
    const idea = ideaInput.value.trim();
    if (!idea) {
      window.toast?.error?.('Vui lòng nhập ý tưởng hoặc yêu cầu.');
      ideaInput.focus();
      return;
    }

    const path = activeButton.dataset.cmsAiSuggest;
    submit.disabled = true;
    setStatus('connecting', 'Đang kết nối với Gemini...', 'fa-solid fa-plug');
    submit.innerHTML = '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Đang gửi...';
    try {
      thinkingTimer = window.setTimeout(() => setStatus('thinking', 'Gemini đang suy nghĩ về yêu cầu...', 'fa-solid fa-brain'), 450);
      generatingTimer = window.setTimeout(() => setStatus('generating', 'Gemini đang tạo nội dung...', 'fa-solid fa-wand-magic-sparkles'), 1400);
      const response = await fetch(window.AI_SUGGESTION_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        body: JSON.stringify({ ...window.CmsEditor.getAiFieldContext(path), idea, mode: 'improve' }),
      });
      const json = await response.json();
      if (!response.ok || !json.success || !json.data?.suggestion) throw new Error(json.message || 'Không thể tạo gợi ý AI.');
      suggestion = json.data.suggestion;
      previewText.textContent = suggestion;
      preview.hidden = false;
    submit.hidden = true;
    apply.hidden = false;
      setStatus('reviewing', 'Đã có đề xuất. Hãy kiểm tra trước khi áp dụng.', 'fa-regular fa-eye');
    } catch (error) {
      setStatus('error', error.message || 'Không thể tạo gợi ý AI.', 'fa-solid fa-circle-exclamation');
      submit.hidden = false;
      apply.hidden = true;
      window.toast?.error?.(error.message || 'Không thể tạo gợi ý AI.');
    } finally {
      window.clearTimeout(thinkingTimer);
      window.clearTimeout(generatingTimer);
      submit.disabled = false;
      submit.innerHTML = 'Nhận đề xuất';
    }
  });

  apply?.addEventListener('click', () => {
    if (!activeButton || !suggestion) return;
    if (window.CmsEditor.applyAiSuggestion(activeButton.dataset.cmsAiSuggest, suggestion)) {
      setStatus('applied', 'Đã áp dụng vào bản nháp. Bạn vẫn cần bấm Lưu để ghi dữ liệu.', 'fa-solid fa-check');
      apply.hidden = true;
      window.toast?.success?.('Đã áp dụng gợi ý vào bản nháp.');
      window.setTimeout(() => modal.querySelector('[data-modal-close]')?.click(), 450);
    }
  });
})();
