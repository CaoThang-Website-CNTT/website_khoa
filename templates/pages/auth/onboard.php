<?php
$role = $role ?? '';
$pendingEmail = $pendingEmail ?? '';
$googleDisplayName = $googleDisplayName ?? '';
$studentIdFromEmail = $studentIdFromEmail ?? '';
$classrooms = $classrooms ?? [];

$isStudent = ($role === 'student');
$errors = request()->session()->getErrors() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];
$onboardSteps = [
  ['label' => 'Thông tin cá nhân'],
  ['label' => 'Học tập / Công tác'],
  ['label' => 'Xác nhận'],
];
?>


<div class="onboard" data-onboard>
  <div id="onboard-step-wizard" class="step-wizard" data-step-wizard-label="Tiến trình đăng ký" aria-live="polite">
  </div>
  <div class="card shadow onboard-form-card">
    <div class="card__header">
      <div class="card__title">Hoàn tất hồ sơ</div>
      <div class="card__description">
        <?= $isStudent
          ? 'Điền thông tin để kích hoạt tài khoản sinh viên (MSSV khớp với email Google của bạn).'
          : 'Điền thông tin để kích hoạt tài khoản giảng viên.' ?>
      </div>
    </div>
    <hr class="separator">
    <form id="onboard-form" class="onboard-form" method="post" action="<?= url('onboarding') ?>"
      data-email="<?= htmlspecialchars($pendingEmail, ENT_QUOTES, 'UTF-8') ?>"
      data-role="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>" novalidate>
      <?= csrf_field() ?>

      <div class="onboard-step" data-step-wizard-panel="0" role="tabpanel">
        <fieldset class="field__set">
          <div class="card__content">
            <div class="field-group">
              <div class="field" data-field-required>
                <label class="field__label" for="onboard-email">Email</label>
                <input id="onboard-email" class="field__input" type="email" readonly
                  value="<?= htmlspecialchars($pendingEmail, ENT_QUOTES, 'UTF-8') ?>">
                <p class="field__description">Email lấy từ tài khoản Google, không thể thay đổi.</p>
              </div>

              <div class="field" data-field-required>
                <label class="field__label" for="full_name">Họ và tên</label>
                <input id="full_name" class="field__input" type="text" name="full_name" required maxlength="255"
                  autocomplete="name"
                  value="<?= htmlspecialchars($old_input['full_name'] ?? $googleDisplayName, ENT_QUOTES, 'UTF-8') ?>">
              </div>

              <div class="grid grid-cols-3 gap-4">
                <div class="field" data-field-required>
                  <label class="field__label" for="dob">Ngày sinh</label>
                  <input id="dob" class="field__input" type="date" name="dob" required
                    value="<?= htmlspecialchars($old_input['dob'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="field" data-field-required>
                  <label class="field__label" for="birth_place">Nơi sinh</label>
                  <input id="birth_place" class="field__input" type="text" name="birth_place" required maxlength="255"
                    value="<?= htmlspecialchars($old_input['birth_place'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="field" data-field-required>
                  <label class="field__label" for="national_id">CCCD</label>
                  <input id="national_id" class="field__input" type="text" name="national_id" required minlength="12"
                    maxlength="12" pattern="[0-9]{12}" inputmode="numeric"
                    value="<?= htmlspecialchars($old_input['national_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
              </div>

              <fieldset class="field__set">
                <legend class="field__label">Giới tính</legend>
                <div class="radio-group" data-field-required data-radio-name="gender"
                  data-radio-default-value="<?= htmlspecialchars($old_input['gender'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                  <label class="field__label">
                    <div class="field" data-orientation="horizontal">
                      <button id="gender-male" class="radio-group__item" type="button" role="radio"
                        value="male"></button>
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
                <input id="phone" class="field__input" type="tel" name="phone" required maxlength="15"
                  pattern="[0-9]{10,15}" inputmode="numeric"
                  value="<?= htmlspecialchars($old_input['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
              </div>

              <div class="field" data-field-required>
                <label class="field__label" for="address">Địa chỉ thường trú</label>
                <textarea id="address" class="field__input" name="address" required rows="3"
                  placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành"><?= htmlspecialchars($old_input['address'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
              </div>
            </div>
          </div>
        </fieldset>
      </div>

      <div class="onboard-step" data-step-wizard-panel="1" role="tabpanel">
        <?php if ($isStudent): ?>
          <fieldset class="field__set">
            <div class="card__content">
              <div class="field-group">
                <div class="field">
                  <label class="field__label" for="student_id_display">MSSV</label>
                  <input id="student_id_display" class="field__input" type="text" readonly
                    value="<?= htmlspecialchars((string) $studentIdFromEmail, ENT_QUOTES, 'UTF-8') ?>">
                  <p class="field__description">Mã số trùng với phần trước @ trong email trường.</p>
                </div>

                <div class="field" data-field-required>
                  <label class="field__label" for="classroom_id">Lớp học</label>
                  <select id="classroom_id" class="field__input" name="classroom_id" required>
                    <option value="">- Chọn lớp học -</option>
                    <?php foreach ($classrooms as $classroom): ?>
                      <option value="<?= (int) $classroom->id ?>" <?= isset($old_input['classroom_id']) && (string) $old_input['classroom_id'] === (string) $classroom->id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($classroom->short_name ?? '', ENT_QUOTES, 'UTF-8') ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <input type="hidden" name="notes"
                  value="<?= htmlspecialchars($old_input['notes'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
              </div>
            </div>
          </fieldset>
        <?php else: ?>
          <fieldset class="field__set">
            <div class="card__content">
              <div class="field-group">
                <div class="field" data-field-required>
                  <label class="field__label" for="degree">Học vị / trình độ</label>
                  <input id="degree" class="field__input" type="text" name="degree" required maxlength="255"
                    value="<?= htmlspecialchars($old_input['degree'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="field">
                  <label class="field__label" for="title">Chức danh</label>
                  <input id="title" class="field__input" type="text" name="title" maxlength="150"
                    value="<?= htmlspecialchars($old_input['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="field" data-field-required>
                  <label class="field__label" for="position">Chức vụ</label>
                  <input id="position" class="field__input" type="text" name="position" required maxlength="255"
                    value="<?= htmlspecialchars($old_input['position'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="field" data-field-required>
                  <label class="field__label" for="department_id">Đơn vị / bộ môn</label>
                  <select id="department_id" class="field__input" name="department_id" required>
                    <option value="">- Chọn đơn vị -</option>
                    <?php foreach ($departments as $department): ?>
                      <option value="<?= (int) $department->id ?>" <?= isset($old_input['department_id']) && (string) $old_input['department_id'] === (string) $department->id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($department->full_name ?? '', ENT_QUOTES, 'UTF-8') ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <!-- TẠM ẨN NGÀY BẮT ĐẦU VÀ NGÀY KẾT THÚC
                <div class="grid grid-cols-1 gap-4" style="grid-template-columns:repeat(auto-fit,minmax(12rem,1fr));">
                  <div class="field" data-field-required>
                    <label class="field__label" for="start_date">Ngày bắt đầu</label>
                    <input id="start_date" class="field__input" type="date" name="start_date" required
                      value="<htmlspecialchars($old_input['start_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                  </div>
                  <div class="field" data-field-required>
                    <label class="field__label" for="end_date">Ngày kết thúc</label>
                    <input id="end_date" class="field__input" type="date" name="end_date" required
                      value="<htmlspecialchars($old_input['end_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                  </div>
                </div>
                -->
                <div class="field">
                  <label class="field__label" for="notes">Ghi chú</label>
                  <textarea id="notes" class="field__input" name="notes"
                    rows="2"><?= htmlspecialchars($old_input['notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
              </div>
            </div>
          </fieldset>
        <?php endif; ?>
      </div>

      <div class="onboard-step" data-step-wizard-panel="2" role="tabpanel">
        <div class="card__content">
          <p class="field__description">Kiểm tra lại thông tin trước khi gửi.</p>
          <dl class="onboard-review">
            <dt>Email</dt>
            <dd data-review-email><?= htmlspecialchars($pendingEmail, ENT_QUOTES, 'UTF-8') ?></dd>
            <dt>Họ và tên</dt>
            <dd data-review="full_name"></dd>
            <dt>Ngày sinh</dt>
            <dd data-review="dob"></dd>
            <dt>Nơi sinh</dt>
            <dd data-review="birth_place"></dd>
            <dt>CCCD</dt>
            <dd data-review="national_id"></dd>
            <dt>Giới tính</dt>
            <dd data-review="gender"></dd>
            <dt>Điện thoại</dt>
            <dd data-review="phone"></dd>
            <dt>Địa chỉ</dt>
            <dd data-review="address"></dd>
            <?php if ($isStudent): ?>
              <dt>MSSV</dt>
              <dd><?= htmlspecialchars((string) $studentIdFromEmail, ENT_QUOTES, 'UTF-8') ?></dd>
              <dt>Lớp học</dt>
              <dd data-review="classroom_id"></dd>
            <?php else: ?>
              <dt>Học vị</dt>
              <dd data-review="degree"></dd>
              <dt>Chức danh</dt>
              <dd data-review="title"></dd>
              <dt>Chức vụ</dt>
              <dd data-review="position"></dd>
              <dt>Đơn vị</dt>
              <dd data-review="department_id"></dd>
              <!-- TẠM ẨN NGÀY BẮT ĐẦU VÀ NGÀY KẾT THÚC
              <dt>Ngày bắt đầu</dt>
              <dd data-review="start_date"></dd>
              <dt>Ngày kết thúc</dt>
              <dd data-review="end_date"></dd>
              -->
              <dt>Ghi chú</dt>
              <dd data-review="notes"></dd>
            <?php endif; ?>
          </dl>
        </div>
      </div>
    </form>
    <div class="card__footer onboard-actions">
      <button type="button" class="btn" data-variant="outline" data-size="lg" id="onboard-back">Quay
        lại</button>
      <button type="button" class="btn" data-variant="primary" data-size="lg" id="onboard-next">Tiếp theo</button>
      <button style="display:none;" type="submit" form="onboard-form" class="btn" data-variant="primary" data-size="lg"
        id="onboard-submit">Hoàn tất đăng
        ký</button>
    </div>
  </div>
</div>

<?php $layout->start("scripts") ?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
</script>
<script>window.__onboardSteps__ = <?= json_encode($onboardSteps, JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="<?= url('public/js/pages/auth/onboard.js') ?>" type="module"></script>
<?php $layout->end() ?>
