(() => {
  const source = document.querySelector('.project-form-editor__source');
  const frame = document.querySelector('.project-form-editor__preview');
  const editorRoot = document.querySelector('.document-editor-shell');
  const editorPanel = document.getElementById('be-right');
  const panelToggle = document.getElementById('toggle-editor-panel');
  const panelToggleLabel = panelToggle?.querySelector('span');
  const narrowViewport = window.matchMedia('(max-width: 900px)');
  const printCss = document.querySelector('link[href*="project_registration_form.css"]').href;
  frame.srcdoc = `<!doctype html><html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="${printCss}"></head><body><main>${source.innerHTML}</main></body></html>`;

  const resizePreview = () => {
    const documentElement = frame.contentDocument?.documentElement;
    if (!documentElement) return;
    documentElement.style.overflow = 'hidden';
    frame.style.height = `${documentElement.scrollHeight}px`;
  };
  frame.addEventListener('load', resizePreview);

  const formatDate = value => {
    if (!value) return '.../.../......';
    const [year, month, day] = value.split('-');
    return `${day}/${month}/${year}`;
  };
  const pagesFor = ids => ids.map(id => frame.contentDocument?.querySelector(`[data-print-group="${id}"]`)).filter(Boolean);
  const setPanelOpen = (isOpen, { focusPanel = false } = {}) => {
    if (!editorRoot || !editorPanel || !panelToggle) return;
    editorPanel.dataset.bePanelState = isOpen ? 'expanded' : 'collapsed';
    panelToggle.setAttribute('aria-expanded', String(isOpen));
    panelToggleLabel.textContent = isOpen ? 'Ẩn chỉnh sửa' : 'Chỉnh sửa nội dung';
    editorPanel.setAttribute('aria-hidden', String(!isOpen));
    editorPanel.inert = !isOpen;
    if (isOpen && focusPanel) editorPanel.querySelector('[contenteditable], input, button')?.focus();
  };

  setPanelOpen(!narrowViewport.matches);
  panelToggle?.addEventListener('click', () => {
    const willOpen = editorPanel.dataset.bePanelState !== 'expanded';
    setPanelOpen(willOpen, { focusPanel: willOpen && narrowViewport.matches });
  });
  document.querySelectorAll('[data-close-editor-panel]').forEach(button => {
    button.addEventListener('click', () => {
      setPanelOpen(false);
      panelToggle?.focus();
    });
  });
  document.addEventListener('keydown', event => {
    if (event.key !== 'Escape' || editorPanel?.dataset.bePanelState !== 'expanded') return;
    setPanelOpen(false);
    panelToggle?.focus();
  });
  narrowViewport.addEventListener('change', event => setPanelOpen(!event.matches));

  const syncSet = set => {
    const ids = JSON.parse(set.dataset.groupIds || '[]');
    pagesFor(ids).forEach(page => {
      set.querySelectorAll('[data-field]').forEach(field => {
        const target = page.querySelector(`[data-preview-field="${field.dataset.field}"]`);
        if (!target) return;
        target[field.matches('[contenteditable]') ? 'innerHTML' : 'textContent'] = field.type === 'date' ? formatDate(field.value) : field.innerHTML;
      });
    });
    requestAnimationFrame(resizePreview);
  };

  document.addEventListener('click', event => {
    const command = event.target.closest('[data-command]');
    if (!command) return;
    event.preventDefault();
    const editor = command.closest('.field').querySelector('[contenteditable]');
    editor.focus();
    document.execCommand(command.dataset.command, false);
    editor.dispatchEvent(new Event('input', { bubbles:true }));
  });
  document.querySelectorAll('[data-editor-set]').forEach(set => {
    set.addEventListener('input', () => syncSet(set));
    set.addEventListener('change', () => syncSet(set));
  });

  document.getElementById('save-print').addEventListener('click', async () => {
    const button = document.getElementById('save-print');
    const message = document.getElementById('save-message');
    const forms = [];
    document.querySelectorAll('[data-editor-set]').forEach(set => {
      const data = Object.fromEntries([...set.querySelectorAll('[data-field]')].map(field => [field.dataset.field, field.matches('[contenteditable]') ? field.innerHTML : field.value]));
      JSON.parse(set.dataset.groupIds || '[]').forEach(groupId => forms.push({ group_id:groupId, ...data }));
    });
    button.disabled = true; message.textContent = 'Đang lưu...';
    try {
      const body = new FormData(); body.append('_token', window.CSRF_TOKEN); body.append('forms', JSON.stringify(forms));
      const response = await fetch(window.PROJECT_FORM_SAVE_URL, { method:'POST', body });
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.message || 'Không thể lưu phiếu.');
      message.textContent = result.message;
      frame.contentWindow.print();
    } catch (error) {
      message.textContent = error.message;
    } finally {
      button.disabled = false;
    }
  });
})();
