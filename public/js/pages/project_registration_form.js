(() => {
  const source = document.querySelector('.project-form-editor__source');
  const frame = document.querySelector('.project-form-editor__preview');
  const printCss = document.querySelector('link[href*="project_registration_form.css"]').href;
  frame.srcdoc = `<!doctype html><html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="${printCss}"></head><body><main>${source.innerHTML}</main></body></html>`;

  const formatDate = value => {
    if (!value) return '.../.../......';
    const [year, month, day] = value.split('-');
    return `${day}/${month}/${year}`;
  };
  const pagesFor = ids => ids.map(id => frame.contentDocument?.querySelector(`[data-print-group="${id}"]`)).filter(Boolean);
  const syncSet = set => {
    const ids = JSON.parse(set.dataset.groupIds || '[]');
    pagesFor(ids).forEach(page => {
      set.querySelectorAll('[data-field]').forEach(field => {
        const target = page.querySelector(`[data-preview-field="${field.dataset.field}"]`);
        if (!target) return;
        target[field.matches('[contenteditable]') ? 'innerHTML' : 'textContent'] = field.type === 'date' ? formatDate(field.value) : field.innerHTML;
      });
    });
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
