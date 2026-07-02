import { TableManager } from '../table/index.js';

document.addEventListener('DOMContentLoaded', () => {
  const tableContainer = document.querySelector('[data-tm="referral_letters_table"]');
  let currentSelectedIds = [];
  const actionLabels = {
    approve: 'duyệt',
    reject: 'từ chối',
    complete: 'xác nhận hoàn thành',
  };

  const apiErrorMessage = (result, fallback) => {
    const topLevel = typeof result?.message === 'string' ? result.message.trim() : '';
    const nested = typeof result?.data?.message === 'string' ? result.data.message.trim() : '';
    const genericMessages = ['Đã có lỗi xảy ra', 'Có lỗi xảy ra'];
    if (topLevel && !genericMessages.includes(topLevel)) return topLevel;
    return nested || topLevel || fallback;
  };

  if (tableContainer) {
    TableManager.registerBulkActions('referral_letters_table', {
      countLabel: count => `Đã chọn: ${count}`,
      actions: [
        {
          id: 'approve',
          label: 'Duyệt',
          icon: 'fa-solid fa-check',
          onClick: ({ selectedIds }) => {
            currentSelectedIds = selectedIds;
            callBulkActionApi('approve');
          },
        },

        {
          id: 'reject',
          label: 'Từ chối giấy giới thiệu',
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
        {
          id: 'print',
          label: 'In giấy đã chọn',
          icon: 'fa-solid fa-print',
          onClick: ({ selectedIds }) => submitBulkPrint(selectedIds),
        },
        {
          id: 'complete',
          label: 'Hoàn thành',
          icon: 'fa-solid fa-circle-check',
          onClick: ({ selectedIds }) => {
            currentSelectedIds = selectedIds;
            callBulkActionApi('complete');
          },
        },
      ],
    });


    tableContainer.addEventListener('click', (e) => {
      const approveBtn = e.target.closest('.btn-approve');
      if (approveBtn) {
        currentSelectedIds = [approveBtn.getAttribute('data-id')];
        callBulkActionApi('approve');
        return;
      }
      const receiveBtn = e.target.closest('.btn-receive');
      if (receiveBtn) {
        currentSelectedIds = [receiveBtn.dataset.id];
        document.getElementById('recipient_name').value = receiveBtn.dataset.name || '';
        document.getElementById('recipient_phone').value = receiveBtn.dataset.phone || '';
        document.getElementById('recipient_email').value = receiveBtn.dataset.email || '';
        ModalHandler.instance.open('#receive-modal');
        return;
      }
      const btn = e.target.closest('.btn-cancel');
      if (!btn) return;

      const id = btn.getAttribute('data-id');
      currentSelectedIds = [id];
      document.getElementById('cancel-count').textContent = '1';
      document.getElementById('cancel_reason_input').value = '';
      ModalHandler.instance.open('#cancel-reason-modal');
    });
  }

  const submitBulkPrint = (ids) => {
    if (!ids.length) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.target = '_blank';
    form.action = `${window.ADMIN_BATCH_URL}/referral_letters/bulk-print`;
    const token = document.createElement('input');
    token.type = 'hidden'; token.name = '_token'; token.value = window.CSRF_TOKEN || '';
    form.appendChild(token);
    ids.forEach(id => {
      const input = document.createElement('input');
      input.type = 'hidden'; input.name = 'ids[]'; input.value = id;
      form.appendChild(input);
    });
    document.body.appendChild(form);
    form.submit();
    form.remove();
  };

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

      const result = await res.json().catch(() => ({}));
      if (!res.ok) {
        const verb = actionLabels[action] || 'xử lý';
        throw new Error(apiErrorMessage(result, `Không thể ${verb} các giấy giới thiệu đã chọn. Vui lòng kiểm tra trạng thái của từng giấy.`));
      }

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
        window.toast.error('Lỗi', 'Vui lòng nhập lý do từ chối.');
      } else {
        alert('Vui lòng nhập lý do từ chối.');
      }
      return;
    }

    const btnConfirm = document.getElementById('btn-confirm-cancel');
    btnConfirm.disabled = true;
    callBulkActionApi('reject', reason).finally(() => {
      btnConfirm.disabled = false;
      ModalHandler.instance.close();
    });
  });

  document.getElementById('btn-confirm-receive')?.addEventListener('click', async () => {
    const payload = {
      recipient_name: document.getElementById('recipient_name').value.trim(),
      recipient_phone: document.getElementById('recipient_phone').value.trim(),
      recipient_email: document.getElementById('recipient_email').value.trim(),
    };
    try {
      const response = await fetch(`${window.API_BASE_URL}/internship/batches/${window.BATCH_ID}/management/referral-letters/${currentSelectedIds[0]}/receive`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.CSRF_TOKEN || '' },
        body: JSON.stringify(payload),
      });
      const result = await response.json().catch(() => ({}));
      if (!response.ok) throw new Error(apiErrorMessage(result, 'Không thể xác nhận nhận giấy. Giấy phải ở trạng thái Hoàn thành và thông tin người nhận phải hợp lệ.'));
      window.toast?.success('Thành công', result.message || 'Đã xác nhận nhận giấy.');
      setTimeout(() => window.location.reload(), 700);
    } catch (error) {
      window.toast?.error('Lỗi', error.message);
    }
  });

  // Xử lý các lý do từ chối được gợi ý sẵn
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
