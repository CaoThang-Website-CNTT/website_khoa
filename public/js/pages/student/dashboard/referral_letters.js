document.addEventListener('DOMContentLoaded', () => {
  const container = document.querySelector('.tm-container[data-tm="student_referral_letters_table"]');

  // Event Delegation for Table Buttons
  if (container) {
    container.addEventListener('click', (e) => {
      // Handle Cancel Button
      const cancelBtn = e.target.closest('.btn-cancel');
      if (cancelBtn) {
        const id = cancelBtn.getAttribute('data-id');
        const cancelForm = document.getElementById('rl_cancelForm');
        if (cancelForm) {
          cancelForm.action = `${window.__studentReferralLetters__?.baseUrl || ''}/${id}/cancel`;
        }
      }

      // Handle Detail Button
      const detailBtn = e.target.closest('.btn-detail');
      if (detailBtn) {
        const id = detailBtn.getAttribute('data-id');
        const companyName = detailBtn.getAttribute('data-company-name');
        const taxCode = detailBtn.getAttribute('data-company-tax-code');
        const address = detailBtn.getAttribute('data-company-address');
        const status = detailBtn.getAttribute('data-status');
        const createdAt = detailBtn.getAttribute('data-created-at');
        const cancelReason = detailBtn.getAttribute('data-cancel-reason');

        document.getElementById('dt_company_name').textContent = companyName;
        document.getElementById('dt_tax_code').textContent = taxCode;
        document.getElementById('dt_company_address').textContent = address;
        document.getElementById('dt_created_at').textContent = createdAt;

        const statusBadge = document.getElementById('dt_status');
        if (status === 'pending') {
          statusBadge.innerHTML = '<span class="badge" data-variant="secondary">Chờ xử lý</span>';
        } else if (status === 'printed') {
          statusBadge.innerHTML = '<span class="badge" data-variant="primary">Đã in</span>';
        } else {
          statusBadge.innerHTML = '<span class="badge" data-variant="destructive">Đã hủy</span>';
        }

        const reasonWrapper = document.getElementById("dt_cancel_reason_wrapper");
        if (cancelReason) {
          document.getElementById("dt_cancel_reason").textContent = cancelReason;
          reasonWrapper.classList.remove("hidden");
        } else {
          reasonWrapper.classList.add("hidden");
        }

        // Render students list
        const studentsListContainer = document.getElementById("dt_students_list");
        const studentsRaw = detailBtn.getAttribute("data-students");
        if (studentsRaw) {
          try {
            const students = JSON.parse(studentsRaw);
            let html = '<ul class="pl-4 space-y-1">';
            students.forEach((st) => {
              html += `<li><b>${st.full_name}</b> - ${st.training_program} - ${st.dob || "?"} - ${st.address || "?"}</li>`;
            });
            html += "</ul>";
            studentsListContainer.innerHTML = html;
          } catch (e) {
            studentsListContainer.innerHTML = "<span>Lỗi hiển thị</span>";
          }
        } else {
          studentsListContainer.innerHTML = "";
        }
      }
    });
  }
});
