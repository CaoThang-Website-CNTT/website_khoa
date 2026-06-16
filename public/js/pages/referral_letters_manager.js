import { TableManager } from '../table/index.js';

document.addEventListener('DOMContentLoaded', () => {
  const tableContainer = document.querySelector('[data-tm="referral_letters_table"]');
  const bulkActionBar = document.getElementById('bulk-action-bar');
  const selectedCountEl = document.getElementById('selected-count');
  const btnBulkApprove = document.getElementById('btn-bulk-approve');
  const btnBulkCancel = document.getElementById('btn-bulk-cancel');
  
  // Modals
  const detailModal = document.getElementById('rl_detailModal');
  const cancelModal = document.getElementById('cancel-reason-modal');
  
  let currentSelectedIds = [];
  
  // Table Manager Selection Change
  if (tableContainer) {
    tableContainer.addEventListener('tm:selection-change', (e) => {
      currentSelectedIds = e.detail.rowSelection || e.detail.selectedIds || [];
      const count = currentSelectedIds.length;

      if (count > 0) {
        bulkActionBar.classList.remove('hidden');
        bulkActionBar.setAttribute('data-state', 'open');
        selectedCountEl.textContent = `Đã chọn: ${count}`;
      } else {
        bulkActionBar.classList.add('hidden');
        bulkActionBar.setAttribute('data-state', 'closed');
      }
    });

    // Bulk Action: Cancel selection
    document.querySelector('#btn-cancel-selection')?.addEventListener('click', () => {
      TableManager.clearSelection('referral_letters_table');
    });
    
    // Delegation for Detail buttons inside Table Manager
    tableContainer.addEventListener('click', (e) => {
      const btn = e.target.closest('.btn-detail');
      if (!btn) return;
      
      const id = btn.getAttribute('data-id');
      const student = btn.getAttribute('data-student');
      const companyName = btn.getAttribute('data-company-name');
      const companyTax = btn.getAttribute('data-company-tax');
      const companyAddress = btn.getAttribute('data-company-address');
      const statusLabel = btn.getAttribute('data-status-label');
      const statusVariant = btn.getAttribute('data-status-variant');
      const reason = btn.getAttribute('data-reason');
      
      document.getElementById('dt_student').textContent = student;
      document.getElementById('dt_company_name').textContent = companyName;
      document.getElementById('dt_company_tax').textContent = `MST: ${companyTax || '--'}`;
      document.getElementById('dt_company_address').textContent = `Địa chỉ: ${companyAddress || '--'}`;
      
      document.getElementById('dt_status').innerHTML = `<span class="badge" data-variant="${statusVariant}">${statusLabel}</span>`;
      
      const reasonWrapper = document.getElementById('dt_cancel_reason_wrapper');
      if (reason && reason !== 'null') {
        document.getElementById('dt_cancel_reason').textContent = reason;
        reasonWrapper.classList.remove('hidden');
      } else {
        reasonWrapper.classList.add('hidden');
      }
      
      ModalHandler.instance.open('#rl_detailModal');
    });
  }

  // Bulk Actions
  const callBulkActionApi = async (action, reason = '') => {
    try {
      const res = await fetch(`${window.API_BASE_URL}/internship/batches/${window.BATCH_ID}/management/referral-letters/bulk-action`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': window.CSRF_TOKEN || ''
        },
        body: JSON.stringify({
          ids: currentSelectedIds,
          action: action,
          reason: reason
        })
      });
      
      const result = await res.json();
      
      if (!res.ok) {
        throw new Error(result.message || 'Có lỗi xảy ra khi thực hiện thao tác.');
      }
      
      if (window.toast) {
        window.toast.success('Thành công', result.message || 'Thao tác thành công.');
      }
      
      // Reload the page to reflect new status. Table Manager client mode uses page load.
      setTimeout(() => {
        window.location.reload();
      }, 1000);
      
    } catch (err) {
      if (window.toast) {
        window.toast.error('Lỗi', err.message);
      } else {
        alert(err.message);
      }
    }
  };

  btnBulkApprove.addEventListener('click', () => {
    if (currentSelectedIds.length === 0) return;
    document.getElementById('approve-count').textContent = currentSelectedIds.length;
    ModalHandler.instance.open('#approve-confirm-modal');
  });

  document.getElementById('btn-confirm-approve')?.addEventListener('click', () => {
    const btnConfirm = document.getElementById('btn-confirm-approve');
    btnConfirm.disabled = true;
    btnBulkApprove.disabled = true;
    callBulkActionApi('approve').finally(() => {
      btnConfirm.disabled = false;
      btnBulkApprove.disabled = false;
      ModalHandler.instance.close();
    });
  });

  btnBulkCancel.addEventListener('click', () => {
    if (currentSelectedIds.length === 0) return;
    document.getElementById('cancel-count').textContent = currentSelectedIds.length;
    document.getElementById('cancel_reason_input').value = '';
    ModalHandler.instance.open('#cancel-reason-modal');
  });

  document.getElementById('btn-confirm-cancel')?.addEventListener('click', () => {
    const reason = document.getElementById('cancel_reason_input').value.trim();
    if (!reason) {
      if (window.toast) {
        window.toast.error('Lỗi', 'Vui lòng nhập lý do hủy.');
      } else {
        alert('Vui lòng nhập lý do hủy.');
      }
      return;
    }
    
    document.getElementById('btn-confirm-cancel').disabled = true;
    callBulkActionApi('cancel', reason).finally(() => {
      document.getElementById('btn-confirm-cancel').disabled = false;
      ModalHandler.instance.close();
    });
  });
});
