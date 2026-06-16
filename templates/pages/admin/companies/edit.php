<?php
$errors = request()->session()->getErrors() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];
?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
</script>



<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">
      Thông tin Công ty
      <?= "#" . htmlspecialchars($company->id) ?>
    </h2>
    <p class="title-wrapper__description">Cập nhật thông tin công ty tại trang này.</p>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url('admin/companies') ?>" data-variant="outline" data-size="lg" class="btn">
      <i class="fa-solid fa-chevron-left"></i>
      Quay lại
    </a>
    <button data-modal-trigger="#confirm-modal" id="edit-submit-btn" type="submit" data-variant="primary" data-size="lg" class="w-full btn">
      <i class="fa-solid fa-floppy-disk"></i>
      Lưu thay đổi
    </button>
    <button data-modal-trigger="#delete-confirm-modal" id="delete-btn" data-variant="destructive" type="button" data-size="lg" class="btn">
      <i class="fa-solid fa-trash"></i>
      Xóa
    </button>
<?php $layout->end() ?>
<form class="detail-layout" id="company-edit-form" action="<?= url('admin/companies/' . $company->id) ?>" method="POST">
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
              <label class="field__label" for="company_name">Tên</label>
              <input id="company_name" class="field__input" type="text" name="company_name"
                placeholder="VD: công ty ABC" value="<?= htmlspecialchars($company->name) ?? '' ?>">
            </div>

            <div class="field">
              <label class="field__label" for="phone">Số điện thoại</label>
              <input id="phone" class="field__input" type="tel" name="phone" placeholder=""
                value="<?= htmlspecialchars($company->phone) ?? '' ?>">
            </div>

            <div class="field">
              <label class="field__label" for="tax_code">Mã số thuế</label>
              <input id="tax_code" class="field__input" type="text" name="tax_code" placeholder=""
                value="<?= htmlspecialchars($company->tax_code) ?? '' ?>">
            </div>

            <div class="field" data-field-required>
              <label class="field__label" for="address">Địa chỉ</label>
              <textarea row="3" id="address" class="field__input" type="text" name="address" placeholder=""
                value="<?= htmlspecialchars($company->address) ?? '' ?>"><?= htmlspecialchars($company->address) ?? '' ?></textarea>
            </div>

            <div class="field">
              <label class="field__label" for="email">Email</label>
              <input id="email" class="field__input" type="email" name="email" placeholder=""
                value="<?= htmlspecialchars($company->email) ?? '' ?>">
            </div>

            <div class="field">
              <label class="field__label" for="website">Website</label>
              <input id="website" class="field__input" type="url" name="website" placeholder=""
                value="<?= htmlspecialchars($company->website) ?? '' ?>">
            </div>

            <div class="field">
              <label class="field__label" for="note">Ghi chú</label>
              <textarea row="3" id="note" class="field__input" type="text" name="note" placeholder=""
                value="<?= htmlspecialchars($company->note) ?? '' ?>"><?= htmlspecialchars($company->note) ?? '' ?></textarea>
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
            <?= htmlspecialchars($company->id) ?>
          </dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Được tạo vào</dt>
          <dd>
            <?= htmlspecialchars($company->created_at) ?>
          </dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Lần cuối cập nhật</dt>
          <dd>
            <?= htmlspecialchars($company->updated_at ? $company->updated_at : "Không có") ?>
          </dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Trạng thái dữ liệu</dt>
          <dd>
            <?php if ($company->deleted_at): ?>
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

<!-- ── Delete Confirm Modal ── -->
<div class="modal" id="delete-confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Bạn có chắc</h2>
    <p class="modal__description">Những thao tác này sẽ không thể hoàn tác.</p>
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

<form action="<?= url("admin/companies/delete/{$company->id}") ?>" method="POST" id="delete-form"><?= csrf_field() ?>
</form>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector('#company-edit-form');
    const confirmBtn = document.querySelector('#confirm-modal-btn');
    const deleteConfirmBtn = document.querySelector('#delete-confirm-modal-btn');

    const nameInput = document.querySelector('#company_name');
    const idInput = document.querySelector('#id');
    const taxCodeInput = document.querySelector('#tax_code');
    const addressInput = document.querySelector('#address');
    const emailInput = document.querySelector('#email');
    const websiteInput = document.querySelector('#website');
    const noteInput = document.querySelector('#note');

    confirmBtn.addEventListener('click', () => {
      form.submit();
    });

    deleteConfirmBtn.addEventListener('click', () => {
      document.querySelector('#delete-form').submit();
    });
  });
</script>