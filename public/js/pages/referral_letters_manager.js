import { TableManager } from '../table/index.js';

document.addEventListener('DOMContentLoaded', () => {
  const tableContainer = document.querySelector('[data-tm="referral_letters_table"]');
  let currentSelectedIds = [];

  if (tableContainer) {
    TableManager.registerBulkActions('referral_letters_table', {
      countLabel: count => `Đã chọn: ${count}`,
      actions: [

        {
          id: 'cancel',
          label: 'Hủy giấy giới thiệu',
          icon: 'fa-solid fa-xmark',
          destructive: true,
          confirm: false,
          onClick: ({ selectedIds }) => {
            currentSelectedIds = selectedIds;
            document.getElementById('cancel-count').textContent = currentSelectedIds.length;
            document.getElementById('cancel_reason_input').value = '';
            ModalHandler.instance.open('#cancel-reason-modal');
          },
        },
      ],
    });

    /* [TẠM ẨN - Sẽ triển khai sau chức năng Xem chi tiết]
    tableContainer.addEventListener('click', (e) => {
      const btn = e.target.closest('.btn-detail');
      if (!btn) return;

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
    */

    tableContainer.addEventListener('click', (e) => {
      const btn = e.target.closest('.btn-cancel');
      if (!btn) return;

      const id = btn.getAttribute('data-id');
      currentSelectedIds = [id];
      document.getElementById('cancel-count').textContent = '1';
      document.getElementById('cancel_reason_input').value = '';
      ModalHandler.instance.open('#cancel-reason-modal');
    });
  }

  const callBulkActionApi = async (action, reason = '') => {
    try {
      TableManager.setBulkActionLoading('referral_letters_table', true, 'Đang xử lý...');

      const res = await fetch(`${window.API_BASE_URL}/internship/batches/${window.BATCH_ID}/management/referral-letters/bulk-action`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': window.CSRF_TOKEN || '',
        },
        body: JSON.stringify({
          ids: currentSelectedIds,
          action,
          reason,
        }),
      });

      const result = await res.json();
      if (!res.ok) throw new Error(result.message || 'Có lỗi xảy ra khi thực hiện thao tác.');

      TableManager.clearSelection('referral_letters_table');
      if (window.toast) {
        window.toast.success('Thành công', result.message || 'Thao tác thành công.');
      }

      setTimeout(() => {
        window.location.reload();
      }, 1000);
    } catch (err) {
      if (window.toast) {
        window.toast.error('Lỗi', err.message);
      } else {
        alert(err.message);
      }
    } finally {
      TableManager.setBulkActionLoading('referral_letters_table', false);
    }
  };

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

    const btnConfirm = document.getElementById('btn-confirm-cancel');
    btnConfirm.disabled = true;
    callBulkActionApi('cancel', reason).finally(() => {
      btnConfirm.disabled = false;
      ModalHandler.instance.close();
    });
  });
});
