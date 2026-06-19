<?php
$errors = request()->session()->getErrors() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];
?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
</script>

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">Thêm công ty mới</h2>
<p class="title-wrapper__description">Điền thông tin công ty mới vào các trường dưới đây</p>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url('admin/companies') ?>" data-variant="outline" data-size="lg" class="btn">
  <i class="fa-solid fa-chevron-left"></i>
  Quay lại
</a>
<button data-modal-trigger="#confirm-modal" id="create-submit-btn" type="button" data-variant="primary" data-size="lg" class="btn">
  <i class="fa-solid fa-floppy-disk"></i>
  Thêm
</button>
<?php $layout->end() ?>
<form class="detail-layout" id="company-add-form" action="<?= url('admin/companies') ?>" method="POST">
  <?= csrf_field() ?>
  <div class="detail-layout__main">
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="field__legend">Thông tin công ty</legend>
          <p class="field__description">
            Vui lòng điền đầy đủ thông tin. Những trường có dấu * là bắt buộc.
          </p>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div class="field-group">

            <div class="field" data-field-required>
              <label class="field__label" for="company_name">Tên công ty</label>
              <input id="company_name" class="field__input" type="text" name="company_name"
                placeholder="VD: Công ty TNHH ABC" maxlength="255" value="">
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="field" data-field-required>
                <label class="field__label" for="tax_code">Mã số thuế</label>
                <input id="tax_code" class="field__input" type="text" name="tax_code"
                  placeholder="VD: 0123456789" maxlength="50" value="">
              </div>

              <div class="field">
                <label class="field__label" for="phone">Số điện thoại</label>
                <input id="phone" class="field__input" type="tel" name="phone"
                  placeholder="0901234567" value="">
              </div>
            </div>

            <div class="field" data-field-required>
              <label class="field__label" for="address">Địa chỉ</label>
              <textarea id="address" class="field__input" name="address" rows="3"
                placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="field">
                <label class="field__label" for="email">Email</label>
                <input id="email" class="field__input" type="email" name="email"
                  placeholder="info@abc.com" value="">
              </div>

              <div class="field">
                <label class="field__label" for="website">Website</label>
                <input id="website" class="field__input" type="url" name="website"
                  placeholder="https://abc.com" value="">
              </div>
            </div>

            <div class="field">
              <label class="field__label" for="note">Ghi chú</label>
              <textarea id="note" class="field__input" name="note" rows="3"
                placeholder="Ghi chú về công ty này"></textarea>
            </div>
          </div>
        </div>
      </fieldset>
    </div>
  </div>
</form>

<!-- ── Confirm Modal ── -->
<div class="modal" id="confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Bạn có chắc</h2>
    <p class="modal__description">Những thao tác này sẽ không thể hoàn tác.</p>
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
    const form = document.querySelector('#company-add-form');
    const confirmBtn = document.querySelector('#confirm-modal-btn');
    const errors = window.__errors__ ?? {};
    const old = window.__old__ ?? {};

    // Khôi phục old input sau validation fail
    if (Object.keys(old).length > 0) {
      Object.entries(old).forEach(([key, value]) => {
        const el = form.querySelector(`[name="${key}"]`);
        if (el) {
          if (el.tagName === 'TEXTAREA') {
            el.textContent = value;
          } else {
            el.value = value;
          }
        }
      });
    }

    // Hiển thị lỗi validation
    if (Object.keys(errors).length > 0) {
      Object.entries(errors).forEach(([field, messages]) => {
        const fieldEl = form.querySelector(`[name="${field}"]`);
        if (fieldEl) {
          const wrapper = fieldEl.closest('.field');
          if (wrapper) {
            wrapper.setAttribute('data-field-error', '');
            const errorMsg = document.createElement('p');
            errorMsg.className = 'field__error';
            errorMsg.textContent = messages[0];
            wrapper.appendChild(errorMsg);
          }
        }
      });
    }

    // Confirm → Submit
    confirmBtn.addEventListener('click', () => {
      form.submit();
    });
  });
</script>