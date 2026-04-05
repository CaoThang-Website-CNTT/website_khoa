<?php
$errors = request()->session()->getErrors()() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];
?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
</script>

<!-- Toast khi redirect về đây có set flash (ví dụ: sau khi xóa thành công) -->
<?php if ($flash = request()->session()->getFlash()): ?>
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
        Thông tin sinh viên
        <?= '#' . htmlspecialchars($student->student_id) ?>
      </h2>
      <p>Cập nhật thông tin sinh viên tại trang này.</p>
    </div>

    <div class="flex gap-2">
      <div>
        <a href="<?= request()->previous(fallback: 'admin/students') ?>" data-variant="outline" data-size="lg"
          class="btn">
          <i class="fa-solid fa-chevron-left"></i>
          Quay lại
        </a>
      </div>
      <div>
        <button data-modal-trigger="#confirm-modal" id="edit-submit-btn" type="submit" data-variant="primary"
          data-size="lg" class="btn">
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
<form class="detail-layout" id="student-edit-form" action="<?= url('admin/students/' . $student->student_id) ?>"
  method="POST">
  <div class="detail-layout__main">
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
              <input id="full_name" class="field__input" type="text" name="full_name" placeholder="Nguyễn Văn An"
                value="<?= htmlspecialchars($student->full_name) ?>">
            </div>

            <div class="grid grid-cols-3 gap-4">
              <div class="field" data-field-required>
                <label class="field__label" for="dob">Ngày Sinh</label>
                <input id="dob" class="field__input" type="date" name="dob"
                  value="<?= htmlspecialchars($student->dob) ?>">
              </div>

              <div class="field" data-field-required>
                <label class="field__label" for="birth_place">Nơi sinh</label>
                <input id="birth_place" class="field__input" type="text" name="birth_place" placeholder="TPHCM"
                  value="<?= htmlspecialchars($student->birth_place) ?>">
              </div>

              <div class="field" data-field-required data-field-max="12">
                <label class="field__label" for="national_id">CCCD</label>
                <input id="national_id" class="field__input" type="text" name="national_id" placeholder="12 số"
                  value="<?= htmlspecialchars($student->national_id) ?>">
              </div>
            </div>

            <fieldset class="field__set">
              <legend class="field__label">Giới tính</legend>
              <div class="radio-group" data-field-required data-radio-name="gender"
                data-radio-default-value="<?= htmlspecialchars($student->gender) ?>">
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

            <div class="field" data-field-required data-field-max="10">
              <label class="field__label" for="phone">Số điện thoại</label>
              <input id="phone" class="field__input" type="tel" name="phone" placeholder="0901234567"
                value="<?= htmlspecialchars($student->phone) ?>">
            </div>


            <div class="field" data-field-required>
              <label class="field__label" for="address">Địa chỉ thường trú</label>
              <textarea id="address" class="field__input" name="address"
                placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành"><?= htmlspecialchars($student->address) ?></textarea>
            </div>

          </div>
        </div>
      </fieldset>
    </div>
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="field__legend">Thông tin sinh viên</legend>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div class="field-group">

            <div class="field" data-field-required data-field-max="10">
              <label class="field__label" for="student_id">MSSV</label>
              <input id="student_id" class="field__input" type="text" name="student_id"
                value="<?= htmlspecialchars($student->student_id) ?>">
            </div>

            <div class="field" data-field-required>
              <label class="field__label" for="classroom_id">Lớp học</label>
              <select id="classroom_id" class="field__input" name="classroom_id">
                <option value="">
                  -- Chọn lớp học--
                </option>
                <?php foreach ($classrooms as $classroom): ?>
                  <option value=<?= htmlspecialchars($classroom->id) ?>   <?= $classroom->id === $student->classroom_id ? 'selected' : '' ?>>
                    <?= htmlspecialchars($classroom->short_name); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="field">
              <label class="field__label">Ghi chú</label>
              <textarea id="notes" class="field__input" type="tel" name="notes" placeholder="Ghi chú về sinh viên này"
                value="<?= $student->notes ?>"></textarea>
            </div>
          </div>
        </div>
      </fieldset>
    </div>
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="field__legend">Tài khoản sinh viên</legend>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div class="field-group">

            <div class="field" data-field-required data-field-readonly>
              <label class="field__label" for="email">Email</label>
              <input id="email" class="field__input" type="text" name="email"
                value="<?= htmlspecialchars($student->account->email) ?>">
              <p class="field__description">Email sẽ được tự động tạo theo định dạng MSSV@caothang.edu.vn</p>
            </div>
          </div>
        </div>
      </fieldset>
    </div>
  </div>
  <div class="detail-layout__sidebar">
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="field__legend">Trạng thái sinh viên</legend>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <fieldset class="field__set">
            <legend class="field__label">Trạng thái</legend>
            <div class="radio-group" data-field-required data-radio-name="status"
              data-radio-default-value="<?= htmlspecialchars($student->status) ?>">
              <label class="field__label">
                <div class="field" data-orientation="horizontal">
                  <button id="status-studying" class="radio-group__item" type="button" role="radio"
                    value="Đang học"></button>
                  <div class="field__title">
                    Đang học
                  </div>
                </div>
              </label>
              <label class="field__label">
                <div class="field" data-orientation="horizontal">
                  <button id="status-graduated" class="radio-group__item" type="button" role="radio"
                    value="Đã tốt nghiệp"></button>
                  <div class="field__title">
                    Đã tốt nghiệp
                  </div>
                </div>
              </label>
              <label class="field__label">
                <div class="field" data-orientation="horizontal">
                  <button id="status-suspended" class="radio-group__item" type="button" role="radio"
                    value="Tạm ngưng"></button>
                  <div class="field__title">
                    Tạm ngưng
                  </div>
                </div>
              </label>
              <label class="field__label">
                <div class="field" data-orientation="horizontal">
                  <button id="status-dropped_out" class="radio-group__item" type="button" role="radio"
                    value="Thôi học"></button>
                  <div class="field__title">
                    Thôi học
                  </div>
                </div>
              </label>
            </div>
          </fieldset>
        </div>
      </fieldset>
    </div>
    <!-- Metadata -->
    <div class="metadata-card card shadow">
      <div class="card__header">
        Thông tin
      </div>
      <hr class="separator">
      <div class="card__content space-y-4">
        <dl class="flex justify-between">
          <dt>ID</dt>
          <dd><?= htmlspecialchars($student->id) ?></dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Được tạo vào</dt>
          <dd><?= htmlspecialchars($student->created_at) ?></dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Lần cuối cập nhật</dt>
          <dd><?= htmlspecialchars($student->updated_at ? $student->updated_at : "Không có") ?></dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Trạng thái dữ liệu</dt>
          <dd>
            <?php if ($student->deleted_at): ?>
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

<form action="<?= url('admin/students/delete/' . $student->student_id) ?>" method="POST" id="delete-form"></form>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const confirmBtn = document.querySelector('#confirm-modal-btn');
    const deleteConfirmBtn = document.querySelector('#delete-confirm-modal-btn');

    const studentIdInput = document.querySelector('#student_id');
    const nationalIdInput = document.querySelector('#national_id');

    const emailInput = document.querySelector('#email');
    const passwordInput = document.querySelector('#password');

    studentIdInput.addEventListener('input', function () {
      const studentId = this.value.trim();
      emailInput.value = studentId ? `${studentId}@caothang.edu.vn` : '';
    });

    nationalIdInput.addEventListener('input', function () {
      const nationalId = this.value.trim();
      passwordInput.value = nationalId ? nationalId : '';
    });

    // Confirm Btn Event Listener
    confirmBtn.addEventListener('click', function () {
      const form = document.querySelector('#student-edit-form');
      form.submit();
    });

    // Delete Btn Event Listener
    deleteConfirmBtn.addEventListener('click', function () {
      const deleteForm = document.querySelector('#delete-form');
      deleteForm.submit();
    });
  });
</script>