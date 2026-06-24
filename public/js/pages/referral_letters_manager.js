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

  // Handle predefined cancel reasons
  document.addEventListener('click', (e) => {
    const suggestionBtn = e.target.closest('.btn-cancel-suggestion');
    if (suggestionBtn) {
      const input = document.getElementById('cancel_reason_input');
      if (input) {
        input.value = suggestionBtn.innerText;
        input.focus();
      }
    }
  });
});
