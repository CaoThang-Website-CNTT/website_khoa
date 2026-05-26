import { TableManager } from '../table/index.js';

document.addEventListener('DOMContentLoaded', () => {
  const batchId = window.BATCH_ID;
  const batchStatus = window.BATCH_STATUS;
  const apiBase = window.API_BASE_URL;

  let supervisors = [];
  let tableData = [];

  // DOM Elements
  const bulkActionBar = document.querySelector('#bulk-action-bar');
  const selectedCountText = document.querySelector('#selected-count');
  
  // Modals
  const modalBulkAssign = document.querySelector('#modal-bulk-assign');
  const modalBulkUnassign = document.querySelector('#modal-bulk-unassign');
  const modalAutoAssign = document.querySelector('#modal-auto-assign');

  /**
   * Tải dữ liệu ban đầu
   */
  const loadData = async () => {
    try {
      const [supRes, assRes] = await Promise.all([
        fetch(`${apiBase}/${batchId}/supervisors`),
        fetch(`${apiBase}/${batchId}/assignments`)
      ]);

      if (!supRes.ok || !assRes.ok) throw new Error('Không thể tải dữ liệu từ máy chủ.');

      const supData = await supRes.json();
      const assData = await assRes.json();

      supervisors = supData.data || [];
      tableData = assData.data || [];

      // Khởi tạo/Cập nhật bảng
      renderTable();
    } catch (error) {
      console.error(error);
      if (window.toast) toast.error('Lỗi', error.message);
    }
  };

  /**
   * Đổ dữ liệu vào TableManager
   */
  const renderTable = () => {
    // Đợi TableManager sẵn sàng (max 5 lần thử)
    let retries = 0;
    const interval = setInterval(() => {
      const tm = TableManager.get('batch_students_table');
      if (tm || retries > 5) {
        clearInterval(interval);
        if (tm) {
          tm.adapter.updateInlineRows(tableData);
          tm.render();
          attachTableEvents();
        }
      }
      retries++;
    }, 100);
  };

  /**
   * Xử lý sự kiện trong bảng (Inline Edit)
   */
  const attachTableEvents = () => {
    const tableRoot = document.querySelector('[data-tm="batch_students_table"]');
    
    // Xử lý click để bật editor
    tableRoot.addEventListener('click', (e) => {
      if (batchStatus === 'closed') return;

      const display = e.target.closest('.teacher-cell__display');
      if (display) {
        const cell = display.closest('.teacher-cell');
        const editor = cell.querySelector('.teacher-cell__editor');
        const select = editor.querySelector('select');

        // Render options nếu chưa có
        if (select.children.length === 0) {
          renderTeacherOptions(select, cell.dataset.teacherId);
        }

        display.classList.add('hidden');
        editor.classList.remove('hidden');
        select.focus();
      }
    });

    // Xử lý khi đổi giá trị select (Inline save)
    tableRoot.addEventListener('change', async (e) => {
      if (e.target.classList.contains('teacher-cell__select')) {
        const select = e.target;
        const cell = select.closest('.teacher-cell');
        const batchStudentId = cell.dataset.batchStudentId;
        const assignmentId = cell.dataset.assignmentId;
        const newTeacherId = select.value ? parseInt(select.value) : null;

        await saveAssignments([{
          assignment_id: assignmentId ? parseInt(assignmentId) : null,
          batch_student_id: parseInt(batchStudentId),
          new_teacher_id: newTeacherId
        }], 'Cập nhật giảng viên hướng dẫn trực tiếp từ bảng.');
      }
    });

    // Xử lý khi blur select (Cancel edit)
    tableRoot.addEventListener('focusout', (e) => {
      if (e.target.classList.contains('teacher-cell__select')) {
        setTimeout(() => {
          const select = e.target;
          const cell = select.closest('.teacher-cell');
          cell.querySelector('.teacher-cell__display').classList.remove('hidden');
          cell.querySelector('.teacher-cell__editor').classList.add('hidden');
        }, 200);
      }
    });
  };

  /**
   * Render options cho dropdown giáo viên
   */
  const renderTeacherOptions = (select, currentTeacherId) => {
    let html = '<option value="">-- Chọn Giảng viên --</option>';
    html += '<option value="unassign" class="text-destructive font-bold">Không có (Hủy phân công)</option>';
    html += '<hr>';
    
    supervisors.forEach(sup => {
      const isFull = sup.current_assigned >= sup.max_students && sup.teacher_id != currentTeacherId;
      html += `<option value="${sup.teacher_id}" ${sup.teacher_id == currentTeacherId ? 'selected' : ''} ${isFull ? 'disabled' : ''}>
        ${sup.teacher_name} (${sup.current_assigned}/${sup.max_students}) ${isFull ? '[Hết chỗ]' : ''}
      </option>`;
    });

    select.innerHTML = html;
  };

  /**
   * Gọi API lưu phân công
   */
  const saveAssignments = async (assignments, reason) => {
    try {
      if (batchStatus === 'published' && !reason) {
        reason = prompt('Đợt thực tập đã công bố. Vui lòng nhập lý do thay đổi:', '');
        if (reason === null) {
          loadData(); // Reset UI
          return;
        }
        if (!reason.trim()) {
          toast.warning('Yêu cầu', 'Lý do không được để trống.');
          loadData();
          return;
        }
      }

      const res = await fetch(`${apiBase}/${batchId}/bulk-save`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          reason: reason || 'Quản trị viên cập nhật phân công.',
          assignments: assignments
        })
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data.message || 'Lỗi khi lưu dữ liệu.');

      toast.success('Thành công', 'Đã cập nhật phân công.');
      await loadData();
    } catch (error) {
      toast.error('Lỗi', error.message);
      loadData();
    }
  };

  /**
   * Xử lý Selection Change từ TableManager
   */
  document.querySelector('[data-tm="batch_students_table"]').addEventListener('tm:selection-change', (e) => {
    const selectedIds = e.detail.selectedIds;
    const count = selectedIds.length;

    if (count > 0) {
      bulkActionBar.classList.remove('hidden');
      bulkActionBar.setAttribute('data-state', 'open');
      selectedCountText.textContent = `Đã chọn: ${count}`;
    } else {
      bulkActionBar.classList.add('hidden');
      bulkActionBar.setAttribute('data-state', 'closed');
    }
  });

  // Bulk Action: Cancel
  document.querySelector('#btn-cancel-selection').addEventListener('click', () => {
    TableManager.clearSelection('batch_students_table');
  });

  // Bulk Action: Assign Modal
  document.querySelector('#btn-bulk-assign').addEventListener('click', () => {
    const selectedIds = TableManager.getSelectedIds('batch_students_table');
    document.querySelector('#bulk-student-count').textContent = selectedIds.length;
    
    const select = document.querySelector('#bulk-teacher-select');
    renderTeacherOptions(select, null);
    
    modalBulkAssign.classList.remove('hidden');
    modalBulkAssign.setAttribute('data-state', 'open');
  });

  document.querySelector('#btn-confirm-bulk-assign').addEventListener('click', async () => {
    const teacherId = document.querySelector('#bulk-teacher-select').value;
    if (!teacherId || teacherId === 'unassign') {
      alert('Vui lòng chọn một giảng viên.');
      return;
    }

    const selectedIds = TableManager.getSelectedIds('batch_students_table');
    const assignments = selectedIds.map(sid => {
      const row = tableData.find(r => r.batch_student_id == sid);
      return {
        assignment_id: row?.assignment_id || null,
        batch_student_id: parseInt(sid),
        new_teacher_id: parseInt(teacherId)
      };
    });

    modalBulkAssign.classList.add('hidden');
    modalBulkAssign.setAttribute('data-state', 'closed');
    await saveAssignments(assignments, 'Cập nhật phân công hàng loạt.');
    TableManager.clearSelection('batch_students_table');
  });

  // Bulk Action: Unassign Modal
  document.querySelector('#btn-bulk-unassign').addEventListener('click', () => {
    const selectedIds = TableManager.getSelectedIds('batch_students_table');
    document.querySelector('#bulk-unassign-count').textContent = selectedIds.length;
    modalBulkUnassign.classList.remove('hidden');
    modalBulkUnassign.setAttribute('data-state', 'open');
  });

  document.querySelector('#btn-confirm-bulk-unassign').addEventListener('click', async () => {
    const selectedIds = TableManager.getSelectedIds('batch_students_table');
    const assignments = selectedIds.map(sid => {
      const row = tableData.find(r => r.batch_student_id == sid);
      return {
        assignment_id: row?.assignment_id || null,
        batch_student_id: parseInt(sid),
        new_teacher_id: null
      };
    });

    modalBulkUnassign.classList.add('hidden');
    modalBulkUnassign.setAttribute('data-state', 'closed');
    await saveAssignments(assignments, 'Hủy phân công hàng loạt.');
    TableManager.clearSelection('batch_students_table');
  });

  // Auto Assign Action
  document.querySelector('#btn-auto-assign')?.addEventListener('click', () => {
    modalAutoAssign.classList.remove('hidden');
    modalAutoAssign.setAttribute('data-state', 'open');
  });

  document.querySelector('#btn-confirm-auto-assign')?.addEventListener('click', async () => {
    const method = document.querySelector('input[name="auto_method"]:checked').value;
    
    try {
      const res = await fetch(`${apiBase}/${batchId}/auto-assign`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ method })
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data.message || 'Lỗi khi phân công tự động.');

      toast.success('Thành công', data.message);
      modalAutoAssign.classList.add('hidden');
      modalAutoAssign.setAttribute('data-state', 'closed');
      await loadData();
    } catch (error) {
      toast.error('Lỗi', error.message);
    }
  });

  // Close Modals
  document.querySelectorAll('.btn[id*="close"]').forEach(btn => {
    btn.addEventListener('click', () => {
      modalBulkAssign.classList.add('hidden');
      modalBulkAssign.setAttribute('data-state', 'closed');
      modalBulkUnassign.classList.add('hidden');
      modalBulkUnassign.setAttribute('data-state', 'closed');
      modalAutoAssign.classList.add('hidden');
      modalAutoAssign.setAttribute('data-state', 'closed');
    });
  });

  // Khởi chạy
  loadData();
});
