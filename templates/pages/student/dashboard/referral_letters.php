<?php

/**
 * View: Quản lý giấy giới thiệu (Sinh viên)
 * Route: /student/internship/{batch_id}/referral_letters
 */
$student = $student ?? null;
$current = $current ?? null;
$referralLetters = $referralLetters ?? [];
?>
<?php if ($flash = request()->session()->getFlash("notification")): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      window.toast?.<?= ($flash['type']) ?>('<?= $flash['title'] ?>', '<?= $flash['desc'] ?>');
    });
  </script>
<?php endif; ?>

<link rel="stylesheet" href="<?= url('public/css/student_dashboard.css') ?>">

<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div class="col-6 col-md-6">
      <h2 class="title text-2xl font-semibold">
        Giấy giới thiệu thực tập
      </h2>
      <p class="text-sm">Danh sách các giấy giới thiệu bạn đã đăng ký trong đợt "<?= $current['title'] ?? '' ?>".</p>
    </div>

    <div class="flex gap-2 items-center">
      <a href="<?= url('student/internship/' . ($current['id'] ?? '')) ?>" data-variant="outline" data-size="md"
        class="btn">
        <i class="fa-solid fa-chevron-left"></i>
        Quay lại
      </a>
      <button type="button" id="btn-request" data-modal-trigger="#rl_requestModal" class="btn" data-variant="primary"
        data-size="md">
        <i class="fa-solid fa-plus mr-2"></i>
        Đăng ký mới
      </button>
    </div>
  </div>
</div>

<div class="table-wrapper shadow rounded-md">
  <table class="data-table">
    <thead>
      <tr>
        <th></th>
        <th>
          <h6>Ngày đăng ký</h6>
        </th>
        <th>
          <h6>Tên công ty</h6>
        </th>
        <th>
          <h6>Mã số thuế</h6>
        </th>
        <th>
          <h6>Trạng thái</h6>
        </th>
        <th>
          <h6>Thao tác</h6>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($referralLetters)): ?>
        <tr>
          <td colspan="99" class="text-center py-8">
            <i class="fa-solid fa-file-lines text-6xl"></i>
            <p class="text-xl mt-2">Chưa có giấy giới thiệu nào được đăng ký.</p>
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($referralLetters as $index => $rl): ?>
          <tr>
            <td><?= $index + 1 ?></td>
            <td><?= date('d/m/Y H:i', strtotime($rl['created_at'])) ?></td>
            <td class="font-medium" title="<?= htmlspecialchars($rl['company_name']) ?>">
              <div class="line-clamp-1 max-w-[250px]"><?= htmlspecialchars($rl['company_name']) ?></div>
            </td>
            <td><?= htmlspecialchars($rl['company_tax_code']) ?: '--' ?></td>
            <td>
              <?php if ($rl['status'] === 'pending'): ?>
                <span class="badge" data-variant="secondary">Chờ xử lý</span>
              <?php elseif ($rl['status'] === 'printed'): ?>
                <span class="badge" data-variant="primary">Đã in</span>
              <?php else: ?>
                <span class="badge" data-variant="destructive">Đã hủy</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="flex gap-2">
                <button type="button" class="btn btn-detail" data-variant="outline" data-size="sm"
                  data-id="<?= $rl['id'] ?>" data-company-name="<?= htmlspecialchars($rl['company_name']) ?>"
                  data-company-tax-code="<?= htmlspecialchars($rl['company_tax_code']) ?: '--' ?>"
                  data-company-address="<?= htmlspecialchars($rl['company_address']) ?>" data-status="<?= $rl['status'] ?>"
                  data-created-at="<?= date('d/m/Y H:i', strtotime($rl['created_at'])) ?>"
                  data-cancel-reason="<?= htmlspecialchars($rl['cancel_reason'] ?? '') ?>">
                  <i class="fa-solid fa-eye"></i> Chi tiết
                </button>
                <?php if ($rl['status'] === 'pending'): ?>
                  <button type="button" class="btn btn-update" data-variant="outline" data-size="sm"
                    data-id="<?= $rl['id'] ?>">
                    <i class="fa-solid fa-pen"></i> Đổi công ty
                  </button>
                  <button type="button" class="btn btn-cancel" data-variant="destructive" data-size="sm"
                    data-id="<?= $rl['id'] ?>">
                    <i class="fa-solid fa-xmark"></i> Hủy
                  </button>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</div>

<!-- Modal Đăng ký mới -->
<div id="rl_requestModal" class="modal" data-state="closed">
  <div class="modal__content modal--lg">
    <div class="modal__header">
      <h3 class="modal__title">Đăng ký giấy giới thiệu thực tập</h3>
      <button class="modal__close" data-modal-close="rl_requestModal">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="modal__body">
      <form action="<?= url('student/internship/' . $current['id'] . '/referral_letters') ?>" method="POST"
        id="rl_requestForm">
        <?= csrf_field() ?>

        <div class="field mb-4" data-orientation="horizontal">
          <input type="checkbox" id="rl_is_manual" name="is_manual" value="1" class="field__input">
          <label for="rl_is_manual" class="field__label">Tôi không tìm thấy mã số thuế / Công ty không có mã số
            thuế</label>
        </div>

        <div class="field mb-4" data-field-required>
          <label class="field__label">Mã số thuế</label>
          <div class="field__input-group">
            <input type="text" name="tax_code" id="rl_tax_code" class="field__input" required>
            <button type="button" id="rl_btnCheckMST" data-variant="outline" data-size="md" class="btn">Kiểm
              tra</button>
          </div>
          <div id="rl_mstLoading" class="field__description hidden"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải
            thông tin...</div>
          <div id="rl_mstError" class="field__error hidden"></div>
        </div>

        <div class="field mb-4" data-field-required>
          <label class="field__label">Tên công ty</label>
          <div class="field__suggest-wrapper">
            <input type="text" name="name" id="rl_company_name" class="field__input relative" required readonly
              autocomplete="off">
            <div id="rl_companySuggestions" class="suggestions-list hidden"></div>
          </div>
        </div>

        <div class="field mb-4" data-field-required>
          <label class="field__label">Địa chỉ</label>
          <textarea name="address" id="rl_company_address" class="field__input" required readonly></textarea>
        </div>
      </form>
    </div>
    <div class="modal__footer">
      <button type="button" class="btn" data-variant="outline" data-modal-close="rl_requestModal">Hủy</button>
      <button type="submit" form="rl_requestForm" class="btn" data-variant="primary">Gửi đăng ký</button>
    </div>
  </div>
</div>

<!-- Modal Đổi công ty -->
<div id="rl_updateModal" class="modal" data-state="closed">
  <div class="modal__content modal--lg">
    <div class="modal__header">
      <h3 class="modal__title">Thay đổi công ty (sẽ tạo giấy mới, hủy giấy cũ)</h3>
      <button class="modal__close" data-modal-close="rl_updateModal">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="modal__body">
      <form method="POST" id="rl_updateForm">
        <?= csrf_field() ?>

        <div class="field mb-4" data-orientation="horizontal">
          <input type="checkbox" id="rl_upd_is_manual" name="is_manual" value="1" class="field__input">
          <label for="rl_upd_is_manual" class="field__label">Tôi không tìm thấy mã số thuế / Công ty không có mã số
            thuế</label>
        </div>

        <div class="field mb-4" data-field-required>
          <label class="field__label">Mã số thuế</label>
          <div class="field__input-group">
            <input type="text" name="tax_code" id="rl_upd_tax_code" class="field__input" required>
            <button type="button" id="rl_upd_btnCheckMST" data-variant="outline" data-size="md" class="btn">Kiểm
              tra</button>
          </div>
          <div id="rl_upd_mstLoading" class="field__description hidden"><i class="fa-solid fa-spinner fa-spin"></i> Đang
            tải thông tin...</div>
          <div id="rl_upd_mstError" class="field__error hidden"></div>
        </div>

        <div class="field mb-4" data-field-required>
          <label class="field__label">Tên công ty</label>
          <div class="field__suggest-wrapper">
            <input type="text" name="name" id="rl_upd_company_name" class="field__input relative" required readonly
              autocomplete="off">
            <div id="rl_upd_companySuggestions" class="suggestions-list hidden"></div>
          </div>
        </div>

        <div class="field mb-4" data-field-required>
          <label class="field__label">Địa chỉ</label>
          <textarea name="address" id="rl_upd_company_address" class="field__input" required readonly></textarea>
        </div>
      </form>
    </div>
    <div class="modal__footer">
      <button type="button" class="btn" data-variant="outline" data-modal-close="rl_updateModal">Hủy bỏ</button>
      <button type="submit" form="rl_updateForm" class="btn" data-variant="primary">Xác nhận cập nhật</button>
    </div>
  </div>
</div>

<!-- Modal Hủy đăng ký -->
<div id="rl_cancelModal" class="modal" data-state="closed">
  <div class="modal__content">
    <div class="modal__header">
      <h3 class="modal__title">Hủy giấy giới thiệu</h3>
      <button class="modal__close" data-modal-close="rl_cancelModal">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="modal__body">
      <form method="POST" id="rl_cancelForm">
        <?= csrf_field() ?>
        <p class="mb-4">Bạn có chắc chắn muốn hủy giấy giới thiệu này?</p>
        <div class="field" data-field-required>
          <label class="field__label">Lý do hủy</label>
          <textarea name="cancel_reason" class="field__input" required rows="3"
            placeholder="Nhập lý do hủy..."></textarea>
        </div>
      </form>
    </div>
    <div class="modal__footer">
      <button type="button" class="btn" data-variant="outline" data-modal-close="rl_cancelModal">Đóng</button>
      <button type="submit" form="rl_cancelForm" class="btn" data-variant="destructive">Hủy giấy giới thiệu</button>
    </div>
  </div>
</div>
<!-- Modal Chi tiết -->
<div id="rl_detailModal" class="modal" data-state="closed">
  <div class="modal__content">
    <div class="modal__header">
      <h3 class="modal__title">Chi tiết giấy giới thiệu</h3>
      <button class="modal__close" data-modal-close="rl_detailModal">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="modal__body">
      <div class="grid gap-4">
        <div class="detail-item">
          <div class="text-xs font-semibold">Tên công ty</div>
          <div id="dt_company_name" class="font-medium"></div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="detail-item">
            <div class="text-xs font-semibold">Mã số thuế</div>
            <div id="dt_tax_code"></div>
          </div>
          <div class="detail-item">
            <div class="text-xs font-semibold">Ngày đăng ký</div>
            <div id="dt_created_at"></div>
          </div>
        </div>
        <div class="detail-item">
          <div class="text-xs font-semibold">Địa chỉ</div>
          <div id="dt_company_address"></div>
        </div>
        <div class="detail-item">
          <div class="text-xs font-semibold">Trạng thái</div>
          <div id="dt_status"></div>
        </div>
        <div id="dt_cancel_reason_wrapper" class="detail-item hidden">
          <div class="text-xs font-semibold">Lý do hủy</div>
          <div id="dt_cancel_reason"></div>
        </div>
      </div>
    </div>
    <div class="modal__footer">
      <button type="button" class="btn" data-variant="outline" data-modal-close="rl_detailModal">Đóng</button>
      <button type="button" id="dt_btnUpdate" class="btn" data-variant="primary">Đổi công ty</button>
    </div>
  </div>
</div>
<script>
  window.API_BASE_URL = '<?= url('api/v1') ?>';
  const BATCH_ID = <?= $current['id'] ?>;
</script>
<script src="<?= url('public/js/pages/student_dashboard.js') ?>"></script>
<script>
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
        updateForm.action = `<?= url("student/internship/{$current['id']}/referral_letters") ?>/${id}/update-company`;
        window.modal.open('#rl_updateModal');
      });
    });

    const cancelButtons = document.querySelectorAll('.btn-cancel');
    const cancelModal = document.getElementById('rl_cancelModal');
    const cancelForm = document.getElementById('rl_cancelForm');

    cancelButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-id');
        cancelForm.action = `<?= url("student/internship/{$current['id']}/referral_letters") ?>/${id}/cancel`;
        window.modal.open('#rl_cancelModal');
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
            window.modal.close();
            updateForm.action = `<?= url("student/internship/{$current['id']}/referral_letters") ?>/${id}/update-company`;
            window.modal.open('#rl_updateModal');
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

        window.modal.open('#rl_detailModal');
      });
    });
  });
</script>