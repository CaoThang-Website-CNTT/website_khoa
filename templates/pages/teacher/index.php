<?php

/**
 * View: Trang hồ sơ cá nhân giảng viên
 * Route: /teacher
 * SEO: Trang thông tin cá nhân và chỉnh sửa hồ sơ giảng viên
 */
$teacher = $teacher ?? null;
$errors = request()->session()->getErrors() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];
?>

<!-- Toast khi redirect về -->
<?php if ($flash = request()->session()->getFlash("notification")): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      window.toast?.<?= ($flash['type']) ?>(
        '<?= $flash['title'] ?>',
        '<?= $flash['desc'] ?>'
      );
    });
  </script>
<?php endif; ?>

<!-- ========== title-wrapper start ========== -->
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div>
      <h1 class="title text-2xl font-semibold">Thông tin cá nhân</h1>
      <p>Quản lý và cập nhật thông tin cá nhân của bạn.</p>
    </div>

    <div class="flex gap-2">
      <button id="save-profile-btn" type="button" data-variant="primary" data-size="lg" class="btn" data-modal-trigger="#confirm-save-modal">
        <i class="fa-solid fa-floppy-disk"></i>
        <span>Lưu thay đổi</span>
      </button>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->

<form class="detail-layout" id="teacher-profile-form" action="<?= url('teacher/profile/update') ?>" method="POST">
  <?= csrf_field() ?>

  <div class="detail-layout__main">
    <!-- Thông tin cá nhân -->
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="field__legend">Thông tin cá nhân</legend>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div class="field-group">
            <div class="field" data-field-required>
              <label class="field__label" for="full_name">Họ và tên</label>
              <input id="full_name" class="field__input" type="text" name="full_name"
                value="<?= htmlspecialchars($old_input['full_name'] ?? $teacher->full_name ?? '') ?>">
              <?php if (isset($errors['full_name'])): ?>
                <span class="field__error"><?= $errors['full_name'][0] ?></span>
              <?php endif; ?>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="field" data-field-required>
                <label class="field__label" for="dob">Ngày sinh</label>
                <input id="dob" class="field__input" type="date" name="dob"
                  value="<?= htmlspecialchars($old_input['dob'] ?? $teacher->dob ?? '') ?>">
              </div>

              <div class="field" data-field-required>
                <label class="field__label" for="gender">Giới tính</label>
                <select id="gender" class="field__input" name="gender">
                  <option value="male" <?= ($old_input['gender'] ?? $teacher->gender ?? '') === 'male' ? 'selected' : '' ?>>Nam</option>
                  <option value="female" <?= ($old_input['gender'] ?? $teacher->gender ?? '') === 'female' ? 'selected' : '' ?>>Nữ</option>
                  <option value="other" <?= ($old_input['gender'] ?? $teacher->gender ?? '') === 'other' ? 'selected' : '' ?>>Khác</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="field" data-field-required>
                <label class="field__label" for="phone">Số điện thoại</label>
                <input id="phone" class="field__input" type="tel" name="phone"
                  value="<?= htmlspecialchars($old_input['phone'] ?? $teacher->phone ?? '') ?>">
              </div>

              <div class="field" data-field-required>
                <label class="field__label" for="national_id">CCCD/CMND</label>
                <input id="national_id" class="field__input" type="text" name="national_id"
                  value="<?= htmlspecialchars($old_input['national_id'] ?? $teacher->national_id ?? '') ?>">
              </div>
            </div>

            <div class="field" data-field-required>
              <label class="field__label" for="address">Địa chỉ thường trú</label>
              <textarea id="address" class="field__input" name="address" rows="2"><?= htmlspecialchars($old_input['address'] ?? $teacher->address ?? '') ?></textarea>
            </div>
          </div>
        </div>
      </fieldset>
    </div>

    <!-- Thông tin học tập (Read-only for teachers) -->
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="field__legend">Thông tin học vấn</legend>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div class="field-group">
            <div class="grid grid-cols-2 gap-4">
              <div class="field" data-field-readonly>
                <label class="field__label">Học vị</label>
                <input class="field__input" type="text" readonly value="<?= htmlspecialchars($teacher->degree ?? '') ?>">
              </div>

              <div class="field" data-field-readonly>
                <label class="field__label">Chức vụ</label>
                <input class="field__input" type="text" readonly value="<?= htmlspecialchars($teacher->position ?? 'N/A') ?>">
              </div>
            </div>

            <div class="field" data-field-readonly>
              <label class="field__label">Khoa</label>
              <input class="field__input" type="text" readonly value="<?= htmlspecialchars($teacher->department->full_name ?? 'N/A') ?>">
            </div>
          </div>
        </div>
      </fieldset>
    </div>
  </div>

  <div class="detail-layout__sidebar">
    <!-- Thông tin hệ thống -->
    <div class="card shadow">
      <div class="card__header">
        <h3 class="card__title">Tài khoản</h3>
      </div>
      <hr class="separator" />
      <div class="card__content">
        <div class="field" data-field-readonly>
          <label class="field__label">Email hệ thống</label>
          <input class="field__input" type="text" readonly value="<?= htmlspecialchars($teacher->account->email ?? '') ?>">
        </div>
      </div>
      <hr class="separator">
      <div class="card__content space-y-4">
        <dl class="flex justify-between items-center">
          <dt class="text-sm font-medium">Cập nhật lần cuối</dt>
          <dd class="text-sm">
            <?= $teacher->updated_at ? date('d/m/Y H:i', strtotime($teacher->updated_at)) : 'Chưa cập nhật' ?>
          </dd>
        </dl>
      </div>
    </div>
  </div>
</form>

<!-- Add confirm modal -->
<div class="modal" id="confirm-save-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Xác nhận cập nhật</h2>
    <p class="modal__description">
      Bạn có chắc chắn muốn thay đổi thông tin cá nhân của mình?
    </p>
  </div>

  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="confirm-save-btn" data-variant="primary" data-size="lg" class="btn" type="button">Cập nhật ngay</button>
  </div>

  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('teacher-profile-form');
    const confirmBtn = document.getElementById('confirm-save-btn');

    if (confirmBtn && form) {
      confirmBtn.addEventListener('click', () => {
        // Thêm loading state
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';
        form.submit();
      });
    }
  });
</script>