<?php
$role = $role ?? '';
$pendingEmail = $pendingEmail ?? '';
$googleDisplayName = $googleDisplayName ?? '';
$studentIdFromEmail = $studentIdFromEmail ?? '';
$classrooms = $classrooms ?? [];

$isStudent = ($role === 'student');
$errors = request()->session()->getErrors() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];
?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
</script>

<?php if ($flash = request()->session()->getFlash("notification")): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      toast.<?= $flash['type'] ?>(
        '<?= $flash['title'] ?>',
        '<?= $flash['desc'] ?>'
      );
    });
  </script>
<?php endif; ?>
<div data-tabs data-tabs-id="onboard-step" data-tabs-panel-active="0" data-tabs-sync="false">
  <div class="onboard-progress" aria-live="polite">
    <div class="onboard-progress__dots" role="tablist" aria-label="Tiến trình đăng ký">
      <div class="onboard-progress__step-wrapper">
        <span class="onboard-progress__dot" data-tabs-trigger="0">1</span>
        <span class="onboard-progress__step-label">Thông tin cá nhân</span>
      </div>

      <div class="onboard-progress__step-wrapper">
        <span class="onboard-progress__dot" data-tabs-trigger="1">2</span>
        <span class="onboard-progress__step-label">Học tập / Công tác</span>
      </div>

      <div class="onboard-progress__step-wrapper">
        <span class="onboard-progress__dot" data-tabs-trigger="2">3</span>
        <span class="onboard-progress__step-label">Xác nhận</span>
      </div>
      <div class="onboard-progress__line-placeholder separator">
        <div class="onboard-progress__line"></div>
      </div>
    </div>
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

      <div class="tabs__panel onboard-step" data-tabs-panel="0" role="tabpanel">
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

      <div class="tabs__panel onboard-step" data-tabs-panel="1" role="tabpanel">
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
                  <label class="field__label" for="department">Đơn vị / bộ môn</label>
                  <input id="department" class="field__input" type="text" name="department" required maxlength="255"
                    value="<?= htmlspecialchars($old_input['department'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="grid grid-cols-1 gap-4" style="grid-template-columns:repeat(auto-fit,minmax(12rem,1fr));">
                  <div class="field" data-field-required>
                    <label class="field__label" for="start_date">Ngày bắt đầu</label>
                    <input id="start_date" class="field__input" type="date" name="start_date" required
                      value="<?= htmlspecialchars($old_input['start_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                  </div>
                  <div class="field" data-field-required>
                    <label class="field__label" for="end_date">Ngày kết thúc</label>
                    <input id="end_date" class="field__input" type="date" name="end_date" required
                      value="<?= htmlspecialchars($old_input['end_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                  </div>
                </div>
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

      <div class="tabs__panel onboard-step" data-tabs-panel="2" role="tabpanel">
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
              <dd data-review="department"></dd>
              <dt>Loại hợp đồng</dt>
              <dd data-review="contract_type"></dd>
              <dt>Ngày bắt đầu</dt>
              <dd data-review="start_date"></dd>
              <dt>Ngày kết thúc</dt>
              <dd data-review="end_date"></dd>
              <dt>Ghi chú</dt>
              <dd data-review="notes"></dd>
            <?php endif; ?>
          </dl>
        </div>
      </div>

      <div class="card__footer onboard-actions">
        <button type="button" class="btn" data-variant="outline" data-size="lg" id="onboard-back">Quay
          lại</button>
        <button type="button" class="btn" data-variant="primary" data-size="lg" id="onboard-next">Tiếp theo</button>
        <button style="display:none;" type="submit" class="btn" data-variant="primary" data-size="lg"
          id="onboard-submit" hidden>Hoàn tất đăng
          ký</button>
      </div>
    </form>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('onboard-form');
    if (!form) return;

    const triggers = Array.from(document.querySelectorAll('[data-tabs-trigger]'))
    const tabHandler = new TabHandler({
      syncParams: false
    });
    tabHandler.init();

    const progressDots = Array.from(document.querySelectorAll('.onboard-progress__dot'));
    const panels = Array.from(document.querySelectorAll('[data-tabs-panel]'));
    const stepLabels = Array.from(document.querySelectorAll('.onboard-progress__step-label'));
    const progressLine = document.querySelector('.onboard-progress__line');

    const btnBack = document.getElementById('onboard-back');
    const btnNext = document.getElementById('onboard-next');
    const btnSubmit = document.getElementById('onboard-submit');
    const total = panels.length;

    let maxUnlockedIdx = 0;

    function syncUI(idx) {
      progressDots.forEach((dot, i) => {
        if (i < idx) {
          dot.setAttribute('data-steps-state', 'passed');
        } else if (i === idx) {
          dot.setAttribute('data-steps-state', 'active');
        } else {
          dot.setAttribute('data-steps-state', 'idle');
        }
      });

      // Tính % width progress line
      if (progressLine && total > 1) {
        const percentage = (100 / (total - 1)) * idx;
        progressLine.style.width = percentage + '%';
      }

      btnBack.style.display = idx === 0 ? "none" : "block";
      btnNext.style.display = idx === total - 1 ? "none" : "block";
      btnSubmit.style.display = idx !== total - 1 ? "none" : "block";

      if (idx === total - 1) {
        fillReview();
      }
    }

    // Lấy ra các input hiện hữu
    function visibleInputs(root) {
      return Array.from(root.querySelectorAll('input, select, textarea')).filter(el => {
        if (el.disabled || el.type === 'hidden' || el.hasAttribute('readonly')) return false;
        return true;
      });
    }

    // Validate Step Panel
    function validateStep(stepEl) {
      const list = visibleInputs(stepEl);
      return list.every((input) => {
        if (typeof input.checkValidity === 'function' && !input.checkValidity()) {
          input.reportValidity();
          return false;
        }
        return true;
      });
    }

    function fillReview() {
      form.querySelectorAll('[data-review]').forEach(span => {
        const name = span.getAttribute('data-review');
        const input = form.querySelector(`[name="${name}"]`);
        if (!input) return;

        const radioGroup = input.closest('.radio-group');
        if (radioGroup) {
          const checkedBtn = radioGroup.querySelector('button[data-state="checked"]');
          if (checkedBtn) {
            const label = checkedBtn.closest('label') || checkedBtn.parentElement;
            span.textContent = label.textContent.trim();
          } else {
            span.textContent = '-';
          }
          return;
        }

        if (input.tagName === 'SELECT') {
          const selectedOption = input.options[input.selectedIndex];
          span.textContent = selectedOption ? selectedOption.textContent.trim() : '-';
          return;
        }
        span.textContent = input.value.trim() || '-';
      });
    }

    function getCurrentIndex() {
      const activePanel = document.querySelector('[data-tabs-panel][data-tabs-panel-state="active"]');
      return activePanel ? parseInt(activePanel.getAttribute('data-tabs-panel')) : 0;
    }

    triggers.forEach((trigger, idx) => {
      trigger.addEventListener('click', () => {
        syncUI(idx);
      });
    });

    btnNext.addEventListener('click', function () {
      const currentIdx = getCurrentIndex();
      if (validateStep(panels[currentIdx])) {
        if (currentIdx < total - 1) {
          triggers[currentIdx + 1].click();
          syncUI(currentIdx + 1);
        }
      }
    });

    btnBack.addEventListener('click', function () {
      const currentIdx = getCurrentIndex();
      if (currentIdx > 0) {
        triggers[currentIdx - 1].click();
        syncUI(currentIdx - 1);
      }
    });

    setTimeout(() => {
      syncUI(getCurrentIndex());
    }, 50);
  });
</script>