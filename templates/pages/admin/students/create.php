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
        Thêm sinh viên mới
      </h2>
      <p>Điền thông tin sinh viên mới vào các trường dưới đây</p>
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
        <button data-modal-trigger="#confirm-modal" id="create-submit-btn" type="submit" data-variant="primary"
          data-size="lg" class="w-full btn">
          <i class="fa-solid fa-floppy-disk"></i>
          Thêm
        </button>
      </div>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->
<form class="detail-layout" id="student-add-form" action="<?= url('admin/students') ?>" method="POST">
  <?= csrf_field() ?>
  <div class="detail-layout__main">
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="field__legend">Thông tin cá nhân</legend>
          <p class="field__description">
            Vui lòng điền đầy đủ thông tin cá nhân của sinh viên. Những trường có dấu * là bắt buộc.
          </p>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div class="field-group">
            <div class="field" data-field-required>
              <label class="field__label" for="full_name">Họ và tên</label>
              <input id="full_name" class="field__input" type="text" name="full_name" placeholder="Nguyễn Văn An"
                value="">
            </div>

            <div class="grid grid-cols-3 gap-4">
              <div class="field" data-field-required>
                <label class="field__label" for="dob">Ngày Sinh</label>
                <input id="dob" class="field__input" type="date" name="dob" value="">
              </div>

              <div class="field" data-field-required>
                <label class="field__label" for="birth_place">Nơi sinh</label>
                <input id="birth_place" class="field__input" type="text" name="birth_place" placeholder="TPHCM"
                  value="">
              </div>

              <div class="field" data-field-required data-field-max="12">
                <label class="field__label" for="national_id">CCCD</label>
                <input id="national_id" class="field__input" type="text" name="national_id" placeholder="12 số"
                  value="">
              </div>
            </div>

            <fieldset class="field__set">
              <legend class="field__label">Giới tính</legend>
              <div class="radio-group" data-field-required data-radio-name="gender">
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
              <input id="phone" class="field__input" type="tel" name="phone" placeholder="0901234567" value="">
            </div>


            <div class="field" data-field-required>
              <label class="field__label" for="address">Địa chỉ thường trú</label>
              <textarea id="address" class="field__input" name="address"
                placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành" value=""></textarea>
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
              <input id="student_id" class="field__input" type="text" name="student_id" value="">
            </div>

            <div class="field" data-field-required>
              <label class="field__label" for="classroom_id">Lớp học</label>
              <select id="classroom_id" class="field__input" name="classroom_id">
                <option value="" selected>
                  -- Chọn lớp học--
                </option>
                <?php foreach ($classrooms as $classroom): ?>
                  <option value=<?= htmlspecialchars($classroom->id) ?>>
                    <?= htmlspecialchars($classroom->short_name); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="field">
              <label class="field__label">Ghi chú</label>
              <textarea id="notes" class="field__input" type="tel" name="notes" placeholder="Ghi chú về sinh viên này"
                value=""></textarea>
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
              <input id="email" class="field__input" type="text" name="email" value="">
              <p class="field__description">Email sẽ được tự động tạo theo định dạng MSSV@caothang.edu.vn</p>
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
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="field__legend">Trạng thái sinh viên</legend>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <fieldset class="field__set">
            <legend class="field__label">Trạng thái</legend>
            <div class="radio-group" data-field-required data-radio-name="status">
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
    <button id="confirm-modal-btn" data-variant="primary" data-size="lg" class="btn" type="button">Chắc chắn</button>
  </div>

  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector('#student-add-form');
    const confirmBtn = document.querySelector('#confirm-modal-btn');

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
      form.submit();
    });
  });
</script>