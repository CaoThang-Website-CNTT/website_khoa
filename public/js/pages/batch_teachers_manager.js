document.addEventListener('DOMContentLoaded', () => {
  const tableContainer = document.querySelector('[data-tm="batch_teachers_table"]');
  if (!tableContainer) return;

  // Event Delegation: Click-to-edit quota
  tableContainer.addEventListener('click', (e) => {
    // Nút Sửa hạn mức
    const editBtn = e.target.closest('.btn-quota-edit');
    if (editBtn) {
      const cell = editBtn.closest('.quota-cell');
      openEditor(cell);
      return;
    }

    // Nút Lưu
    const saveBtn = e.target.closest('.btn-quota-save');
    if (saveBtn) {
      const cell = saveBtn.closest('.quota-cell');
      saveQuota(cell);
      return;
    }

    // Nút Hủy
    const cancelBtn = e.target.closest('.btn-quota-cancel');
    if (cancelBtn) {
      const cell = cancelBtn.closest('.quota-cell');
      closeEditor(cell);
      return;
    }
  });

  // Xử lý phím Enter / Escape trong input
  tableContainer.addEventListener('keydown', (e) => {
    if (!e.target.classList.contains('quota-cell__input')) return;

    const cell = e.target.closest('.quota-cell');
    if (e.key === 'Enter') {
      e.preventDefault();
      saveQuota(cell);
    } else if (e.key === 'Escape') {
      closeEditor(cell);
    }
  });

  /**
   * Mở chế độ chỉnh sửa quota
   */
  function openEditor(cell) {
    const display = cell.querySelector('.quota-cell__display');
    const editor = cell.querySelector('.quota-cell__editor');
    const input = cell.querySelector('.quota-cell__input');
    const currentValue = cell.querySelector('.quota-cell__value').textContent.trim();

    display.classList.add('hidden');
    editor.classList.remove('hidden');
    input.value = currentValue;
    input.focus();
    input.select();
  }

  /**
   * Đóng chế độ chỉnh sửa, khôi phục giá trị cũ
   */
  function closeEditor(cell) {
    const display = cell.querySelector('.quota-cell__display');
    const editor = cell.querySelector('.quota-cell__editor');

    editor.classList.add('hidden');
    display.classList.remove('hidden');
  }

  /**
   * Gọi API cập nhật hạn mức
   */
  async function saveQuota(cell) {
    const teacherId = cell.dataset.teacherId;
    const input = cell.querySelector('.quota-cell__input');
    const newQuota = parseInt(input.value, 10);

    if (isNaN(newQuota) || newQuota < 0) {
      if (window.toast) {
        window.toast.error('Lỗi', 'Hạn mức phải là số nguyên không âm.');
      }
      return;
    }

    const saveBtn = cell.querySelector('.btn-quota-save');
    const cancelBtn = cell.querySelector('.btn-quota-cancel');
    saveBtn.disabled = true;
    cancelBtn.disabled = true;
    input.disabled = true;

    try {
      const res = await fetch(
        `${window.API_BASE_URL}/internship/batches/${BATCH_ID}/management/supervisors/${teacherId}`,
        {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.CSRF_TOKEN || ''
          },
          body: JSON.stringify({ max_students: newQuota })
        }
      );

      const result = await res.json();

      if (!res.ok) {
        throw new Error(result.message || 'Có lỗi xảy ra.');
      }

      // Cập nhật giá trị hiển thị
      cell.querySelector('.quota-cell__value').textContent = newQuota;
      closeEditor(cell);

      if (window.toast) {
        window.toast.success('Thành công', result.message || 'Cập nhật hạn mức thành công.');
      }
    } catch (err) {
      if (window.toast) {
        window.toast.error('Lỗi', err.message);
      }
    } finally {
      saveBtn.disabled = false;
      cancelBtn.disabled = false;
      input.disabled = false;
    }
  }
});
