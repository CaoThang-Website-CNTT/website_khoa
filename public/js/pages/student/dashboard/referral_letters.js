  document.addEventListener('DOMContentLoaded', () => {
    // Khởi tạo autocomplete/mst form cho Update Modal
    if (typeof initCompanyFormLogic === 'function') {
      initCompanyFormLogic("rl_upd_");
    }

    // Gắn sự kiện click cho các nút Mở modal Update và Cancel
    const updateButtons = document.querySelectorAll('.btn-update');
    const updateModal = document.getElementById('rl_updateModal');
    const updateForm = document.getElementById('rl_updateForm');

    updateButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-id');
        updateForm.action = `${window.__studentReferralLetters__?.baseUrl || ''}/${id}/update-company`;
      });
    });

    const cancelButtons = document.querySelectorAll('.btn-cancel');
    const cancelModal = document.getElementById('rl_cancelModal');
    const cancelForm = document.getElementById('rl_cancelForm');

    cancelButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-id');
        cancelForm.action = `${window.__studentReferralLetters__?.baseUrl || ''}/${id}/cancel`;
      });
    });

    // Detail Modal logic
    const detailButtons = document.querySelectorAll('.btn-detail');
    const detailModal = document.getElementById('rl_detailModal');
    const dtBtnUpdate = document.getElementById('dt_btnUpdate');

    detailButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-id');
        const companyName = btn.getAttribute('data-company-name');
        const taxCode = btn.getAttribute('data-company-tax-code');
        const address = btn.getAttribute('data-company-address');
        const status = btn.getAttribute('data-status');
        const createdAt = btn.getAttribute('data-created-at');
        const cancelReason = btn.getAttribute('data-cancel-reason');

        document.getElementById('dt_company_name').textContent = companyName;
        document.getElementById('dt_tax_code').textContent = taxCode;
        document.getElementById('dt_company_address').textContent = address;
        document.getElementById('dt_created_at').textContent = createdAt;

        const statusBadge = document.getElementById('dt_status');
        if (status === 'pending') {
          statusBadge.innerHTML = '<span class="badge" data-variant="secondary">Chờ xử lý</span>';
          dtBtnUpdate.classList.remove('hidden');
          dtBtnUpdate.onclick = () => {
            ModalHandler.instance.close();
            updateForm.action = `${window.__studentReferralLetters__?.baseUrl || ''}/${id}/update-company`;
            ModalHandler.instance.open('#rl_updateModal');
          };
        } else if (status === 'printed') {
          statusBadge.innerHTML = '<span class="badge" data-variant="primary">Đã in</span>';
          dtBtnUpdate.classList.add('hidden');
        } else {
          statusBadge.innerHTML = '<span class="badge" data-variant="destructive">Đã hủy</span>';
          dtBtnUpdate.classList.add('hidden');
        }

        const reasonWrapper = document.getElementById('dt_cancel_reason_wrapper');
        if (cancelReason) {
          document.getElementById('dt_cancel_reason').textContent = cancelReason;
          reasonWrapper.classList.remove('hidden');
        } else {
          reasonWrapper.classList.add('hidden');
        }
      });
    });
  });
