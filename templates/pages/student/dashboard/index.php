<?php

/**
 * View: Trang hồ sơ cá nhân sinh viên
 * Route: /student
 * SEO: Trang thông tin cá nhân và chỉnh sửa hồ sơ sinh viên
 */
$student = $student ?? null;
$errors = request()->session()->getErrors() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];
?>

<?php $layout->start("heading") ?>
<h2 class="title-wrapper__title">Thông tin cá nhân</h2>
  <p class="title-wrapper__description">Quản lý và cập nhật thông tin cá nhân của bạn.</p>
  <?php $layout->end() ?>

  <?php $layout->start("actions") ?>
  <button id="save-profile-btn" type="button" data-variant="primary" data-size="lg" class="btn"
    data-modal-trigger="#confirm-save-modal">

    <span>Lưu</span>
  </button>
  <?php $layout->end() ?>
  <form class="detail-layout" id="student-profile-form" action="<?= url('student/profile/update') ?>" method="POST">
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
                  value="<?= htmlspecialchars($old_input['full_name'] ?? $student->full_name ?? '') ?>">
                <?php if (isset($errors['full_name'])): ?>
                  <span class="field__error"><?= $errors['full_name'][0] ?></span>
                <?php endif; ?>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="field" data-field-required>
                  <label class="field__label" for="dob">Ngày sinh</label>
                  <input id="dob" class="field__input" type="date" name="dob"
                    value="<?= htmlspecialchars($old_input['dob'] ?? $student->dob ?? '') ?>">
                </div>

                <div class="field" data-field-required>
                  <label class="field__label" for="gender">Giới tính</label>
                  <select id="gender" class="field__input" name="gender">
                    <option value="male" <?= ($old_input['gender'] ?? $student->gender ?? '') === 'male' ? 'selected' : '' ?>>Nam</option>
                    <option value="female" <?= ($old_input['gender'] ?? $student->gender ?? '') === 'female' ? 'selected' : '' ?>>Nữ</option>
                    <option value="other" <?= ($old_input['gender'] ?? $student->gender ?? '') === 'other' ? 'selected' : '' ?>>Khác</option>
                  </select>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="field" data-field-required>
                  <label class="field__label" for="phone">Số điện thoại</label>
                  <input id="phone" class="field__input" type="tel" name="phone"
                    value="<?= htmlspecialchars($old_input['phone'] ?? $student->phone ?? '') ?>">
                </div>

                <div class="field" data-field-required>
                  <label class="field__label" for="national_id">CCCD/CMND</label>
                  <input id="national_id" class="field__input" type="text" name="national_id"
                    value="<?= htmlspecialchars($old_input['national_id'] ?? $student->national_id ?? '') ?>">
                </div>
              </div>

              <div class="field" data-field-required>
                <label class="field__label" for="birth_place">Nơi sinh</label>
                <input id="birth_place" class="field__input" type="text" name="birth_place"
                  value="<?= htmlspecialchars($old_input['birth_place'] ?? $student->birth_place ?? '') ?>">
              </div>

              <div class="field" data-field-required>
                <label class="field__label" for="address">Địa chỉ thường trú</label>
                <textarea id="address" class="field__input" name="address"
                  rows="2"><?= htmlspecialchars($old_input['address'] ?? $student->address ?? '') ?></textarea>
              </div>
            </div>
          </div>
        </fieldset>
      </div>

      <!-- Thông tin học tập (Read-only for students) -->
      <div class="card shadow">
        <fieldset class="field__set">
          <div class="card__header">
            <legend class="field__legend">Thông tin học tập</legend>
          </div>
          <hr class="separator" />
          <div class="card__content">
            <div class="field-group">
              <div class="grid grid-cols-2 gap-4">
                <div class="field" data-field-readonly>
                  <label class="field__label">Mã số sinh viên</label>
                  <input class="field__input" type="text" readonly
                    value="<?= htmlspecialchars($student->student_id ?? '') ?>">
                </div>

                <div class="field" data-field-readonly>
                  <label class="field__label">Lớp học</label>
                  <input class="field__input" type="text" readonly
                    value="<?= htmlspecialchars($student->classroom->short_name ?? 'N/A') ?>">
                </div>
              </div>

              <div class="field" data-field-readonly>
                <label class="field__label">Ngành học</label>
                <input class="field__input" type="text" readonly
                  value="<?= htmlspecialchars($student->major_name ?? 'Công nghệ thông tin') ?>">
              </div>
            </div>
          </div>
        </fieldset>
      </div>
    </div>

    <div class="detail-layout__sidebar">
      <!-- Tài khoản -->
      <div class="card shadow">
        <div class="card__header">
          <h3 class="card__title">Tài khoản</h3>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div class="field" data-field-readonly>
            <label class="field__label">Email hệ thống</label>
            <input class="field__input" type="text" readonly
              value="<?= htmlspecialchars($student->account->email ?? '') ?>">
          </div>
        </div>
      </div>

      <!-- Trạng thái -->
      <div class="metadata-card card shadow">
        <div class="card__header">Trạng thái</div>
        <hr class="separator">
        <div class="card__content space-y-4">
          <dl class="flex justify-between items-center">
            <dt class="text-sm font-medium">Tình trạng học tập</dt>
            <dd>
              <span class="status-badge status-badge--info">
                <?= htmlspecialchars($student->status ?? 'Đang học') ?>
              </span>
            </dd>
          </dl>
          <hr class="separator">
          <dl class="flex justify-between items-center">
            <dt class="text-sm font-medium">Cập nhật lần cuối</dt>
            <dd class="text-sm">
              <?= $student->updated_at ? date('d/m/Y H:i', strtotime($student->updated_at)) : 'Chưa cập nhật' ?>
            </dd>
          </dl>
        </div>
      </div>
    </div>
  </form>

  <!-- Add confirm modal -->
  <div class="modal" id="confirm-save-modal" tabindex="-1" data-state="closed">
    <div class="modal__header">
      <h3 class="modal__title">Xác nhận cập nhật</h3>
      <p class="modal__description">
        Bạn có chắc chắn muốn thay đổi thông tin cá nhân của mình?
      </p>
    </div>

    <div class="modal__footer">
      <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
      <button id="confirm-save-btn" data-variant="primary" data-size="lg" class="btn" type="button">Cập nhật
        ngay</button>
    </div>

    <button class="modal__close" type="button" data-modal-close>
      <i class="fa-solid fa-xmark"></i>
    </button>
  </div>

  <?php $layout->start("scripts") ?>
  <script src="<?= url('public/js/pages/student/dashboard/index.js') ?>" type="module"></script>
  <?php $layout->end() ?>
