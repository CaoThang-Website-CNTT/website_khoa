<?php
$errors = request()->session()->getErrors() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];
?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
</script>

<!-- Toast khi redirect về đây có set flash (ví dụ: sau khi xóa thành công) -->
<?php if ($flash = request()->session()->getFlash("notification")): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      toast.<?= ($flash['type']) ?>(
        '<?= $flash['title'] ?>',
        '<?= $flash['desc'] ?>'
      );
    });
  </script>
<?php endif; ?>
<!-- ========== title-wrapper start ========== -->
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div class="col-6 col-md-6">
      <h2 class="title text-2xl font-semibold">
        Thông tin giảng viên
        <?= '#' . htmlspecialchars($teacher->staff_code) ?>
      </h2>
      <p>Cập nhật thông tin giảng viên tại trang này.</p>
    </div>

    <div class="flex gap-2">
      <div>
        <a href="<?= request()->previous(fallback: url('admin/teachers')) ?>" data-variant="outline" data-size="lg"
          class="btn">
          <i class="fa-solid fa-chevron-left"></i>
          Quay lại
        </a>
      </div>
      <div>
        <button data-modal-trigger="#confirm-modal" id="edit-submit-btn" type="submit" data-variant="primary"
          data-size="lg" class="w-full btn">
          <i class="fa-solid fa-floppy-disk"></i>
          Lưu thay đổi
        </button>
      </div>
      <div>
        <button data-modal-trigger="#delete-confirm-modal" id="delete-btn" data-variant="destructive" type="button"
          data-size="lg" class="btn">
          <i class="fa-solid fa-trash"></i>
          Xóa
        </button>
      </div>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->
<form class="detail-layout" id="teacher-edit-form" action="<?= url('admin/teachers/' . $teacher->id) ?>" method="POST">
  <?= csrf_field() ?>
  <div class="detail-layout__main">
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="field__legend">Thông tin cá nhân</legend>
          <p class="field__description">
            Vui lòng điền đầy đủ thông tin cá nhân của giảng viên. Những trường có dấu * là bắt buộc.
          </p>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div class="field-group">
            <div class="field" data-field-required>
              <label class="field__label" for="full_name">Họ và tên</label>
              <input id="full_name" class="field__input" type="text" name="full_name" placeholder="Nguyễn Văn An"
                value="<?= htmlspecialchars($teacher->full_name) ?>">
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="field" data-field-required>
                <label class="field__label" for="dob">Ngày Sinh</label>
                <input id="dob" class="field__input" type="date" name="dob"
                  value="<?= htmlspecialchars($teacher->dob) ?>">
              </div>

              <div class="field" data-field-required>
                <label class="field__label" for="national_id">CCCD</label>
                <input id="national_id" class="field__input" type="text" name="national_id" placeholder="12 số"
                  value="<?= htmlspecialchars($teacher->national_id) ?>">
              </div>
            </div>

            <fieldset class="field__set">
              <legend class="field__label">Giới tính</legend>
              <div class="radio-group" data-radio-name="gender"
                data-radio-default-value="<?= htmlspecialchars($teacher->gender) ?>">
                <label class="field__label">
                  <div class="field" data-orientation="horizontal">
                    <button id="gender-male" class="radio-group__item" type="button" role="radio" value="male"></button>
                    <div class="field__title">
                      Nam
                    </div>
                  </div>
                </label>
                <label class="field__label">
                  <div class="field" data-orientation="horizontal">
                    <button id="gender-female" class="radio-group__item" type="button" role="radio"
                      value="female"></button>
                    <div class="field__title">
                      Nữ
                    </div>
                  </div>
                </label>
              </div>
            </fieldset>

            <div class="field" data-field-required>
              <label class="field__label" for="phone">Số điện thoại</label>
              <input id="phone" class="field__input" type="tel" name="phone" placeholder="0901234567"
                value="<?= htmlspecialchars($teacher->phone) ?>">
            </div>

            <div class="field" data-field-required>
              <label class="field__label" for="address">Địa chỉ thường trú</label>
              <textarea id="address" class="field__input" type="tel" name="address"
                placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành"><?= htmlspecialchars($teacher->address) ?></textarea>
            </div>

          </div>
        </div>
      </fieldset>
    </div>
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="field__legend">Thông tin công việc</legend>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div class="field-group">
            <div class="field" data-field-required data-field-max="10">
              <label class="field__label" for="staff_code">Mã giảng viên</label>
              <input id="staff_code" class="field__input" type="text" name="staff_code"
                value="<?= htmlspecialchars($teacher->staff_code) ?>">
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="field" data-field-required>
                <label class="field__label" for="degree">Học vị</label>
                <input id="degree" class="field__input" type="text" name="degree"
                  value="<?= htmlspecialchars($teacher->degree) ?>">
              </div>

              <div class="field">
                <label class="field__label" for="title">Học hàm</label>
                <input id="title" class="field__input" type="text" name="title"
                  value="<?= htmlspecialchars($teacher->title) ?>">
              </div>

              <div class="field" data-field-required>
                <label class="field__label" for="position">Chức vụ</label>
                <input id="position" class="field__input" type="text" name="position"
                  value="<?= htmlspecialchars($teacher->position) ?>">
              </div>

              <div class="field" data-field-required>
                <label class="field__label" for="department">Phòng ban / Khoa</label>
                <input id="department" class="field__input" type="text" name="department"
                  value="<?= htmlspecialchars($teacher->department) ?> ">
              </div>
            </div>
          </div>
        </div>
      </fieldset>
    </div>
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="field__legend">Thông tin hợp đồng</legend>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div class="field-group">
            <fieldset class="field__set">
              <legend class="field__label">Loại hợp đồng</legend>
              <div class="radio-group" data-radio-name="contract_type"
                data-radio-default-value="<?= htmlspecialchars($teacher->contract_type) ?>">
                <div class="grid grid-cols-2 gap-4">
                  <label class="field__label">
                    <div class="field" data-orientation="horizontal">
                      <button id="contract-type-full-time" class="radio-group__item" type="button" role="radio"
                        value="full_time"></button>
                      <div class="field__title">
                        Full-time
                      </div>
                    </div>
                  </label>
                  <label class="field__label">
                    <div class="field" data-orientation="horizontal">
                      <button id="contract-type-part-time" class="radio-group__item" type="button" role="radio"
                        value="part_time"></button>
                      <div class="field__title">
                        Part-time
                      </div>
                    </div>
                  </label>
                  <label class="field__label">
                    <div class="field" data-orientation="horizontal">
                      <button id="contract-type-visiting" class="radio-group__item" type="button" role="radio"
                        value="visiting"></button>
                      <div class="field__title">
                        Thỉnh giảng
                      </div>
                    </div>
                  </label>
                  <label class="field__label">
                    <div class="field" data-orientation="horizontal">
                      <button id="contract-type-contract" class="radio-group__item" type="button" role="radio"
                        value="contract"></button>
                      <div class="field__title">
                        Hợp đồng khác
                      </div>
                    </div>
                  </label>
                </div>
              </div>
            </fieldset>

            <div class="grid grid-cols-2 gap-4">
              <div class="field" data-field-required>
                <label class="field__label" for="start_date">Ngày bắt đầu</label>
                <input id="start_date" class="field__input" type="date" name="start_date"
                  value="<?= htmlspecialchars($teacher->start_date) ?>">
              </div>

              <div class="field" data-field-required>
                <label class="field__label" for="end_date">Ngày kết thúc</label>
                <input id="end_date" class="field__input" type="date" name="end_date"
                  value="<?= htmlspecialchars($teacher->end_date) ?>">
              </div>
            </div>

            <div class="field">
              <label class="field__label">Ghi chú</label>
              <textarea id="notes" class="field__input" type="tel" name="notes"
                placeholder="Ghi chú về giảng viên/hợp đồng này"><?= htmlspecialchars($teacher->notes) ?></textarea>
            </div>
          </div>
        </div>
      </fieldset>
    </div>
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="field__legend">Tài khoản giảng viên</legend>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div class="field-group">

            <div class="field" data-field-required data-field-readonly>
              <label class="field__label" for="email">Email</label>
              <input id="email" class="field__input" type="text" name="email"
                value="<?= htmlspecialchars($teacher->account->email) ?>">
              <p class="field__description">Email sẽ được tự động tạo theo định dạng hovaten@caothang.edu.vn</p>
            </div>

            <div class="field" data-field-required data-field-readonly>
              <label class="field__label" for="password">Mật khẩu</label>
              <input id="password" class="field__input" type="password" name="password" value="0306231298">
              <p class="field__description">Mật khẩu mặc định là CCCD</p>
            </div>

          </div>
        </div>
      </fieldset>
    </div>
  </div>
  <div class="detail-layout__sidebar">
    <!-- Metadata -->
    <div class="metadata-card card shadow">
      <div class="card__header">
        Thông tin
      </div>
      <hr class="separator">
      <div class="card__content space-y-4">
        <dl class="flex justify-between">
          <dt>ID</dt>
          <dd>
            <?= htmlspecialchars($teacher->id) ?>
          </dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Được tạo vào</dt>
          <dd>
            <?= htmlspecialchars($teacher->created_at) ?>
          </dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Lần cuối cập nhật</dt>
          <dd>
            <?= htmlspecialchars($teacher->updated_at ? $teacher->updated_at : "Không có") ?>
          </dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Trạng thái dữ liệu</dt>
          <dd>
            <?php if ($teacher->deleted_at): ?>
              <span class="badge" data-variant="destructive">
                Đã xóa
              </span>
            <?php else: ?>
              <span class="badge" data-variant="primary">Hoạt động</span>
            <?php endif; ?>
          </dd>
        </dl>
      </div>
    </div>
  </div>
</form>

<!-- Add confirm modal -->
<div class="modal" id="confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Bạn có chắc</h2>
    <p class="modal__description">
      Những thao tác này sẽ không thể hoàn tác.
    </p>
  </div>

  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="confirm-modal-btn" data-variant="primary" data-size="lg" class="btn" type="button">Chắc
      chắn</button>
  </div>

  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<!-- Delete confirm modal -->
<div class="modal" id="delete-confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Bạn có chắc</h2>
    <p class="modal__description">
      Những thao tác này sẽ không thể hoàn tác.
    </p>
  </div>

  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="delete-confirm-modal-btn" data-variant="primary" data-size="lg" class="btn" type="button">Chắc
      chắn</button>
  </div>

  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<form action="<?= url('admin/teachers/delete/' . $teacher->id) ?>" method="POST" id="delete-form"><?= csrf_field() ?>
</form>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const confirmBtn = document.querySelector('#confirm-modal-btn');
    const deleteConfirmBtn = document.querySelector('#delete-confirm-modal-btn');

    const teacherNameInput = document.querySelector('#full_name');
    const nationalIdInput = document.querySelector('#national_id');

    const emailInput = document.querySelector('#email');
    const passwordInput = document.querySelector('#password');

    teacherNameInput.addEventListener('input', function () {
      const teacherName = Utils.toCleanAscii(this.value).replace(/\s+/g, '');
      emailInput.value = teacherName ? `${teacherName}@caothang.edu.vn` : '';
    });

    nationalIdInput.addEventListener('input', function () {
      const nationalId = this.value.trim();
      passwordInput.value = nationalId ? nationalId : '';
    });

    // Confirm Btn Event Listener
    confirmBtn.addEventListener('click', function () {
      const form = document.querySelector('#teacher-edit-form');
      form.submit();
    });

    // Delete Confirm Btn Event Listener
    deleteConfirmBtn.addEventListener('click', function () {
      const deleteForm = document.querySelector('#delete-form');
      deleteForm.submit();
    });

  });
</script>
