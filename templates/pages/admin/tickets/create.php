<?php
$old_input = request()->session()->getOldInputs() ?? [];
?>

<?php $layout->start("heading") ?>
<h2 class="title-wrapper__title">Phản hồi</h2>
<p class="title-wrapper__description">Gửi phản hồi tới đội ngũ phát triển</p>
<?php $layout->end() ?>

<?php $layout->start("actions") ?>
<button data-modal-trigger="#confirm-modal" data-variant="primary" data-size="lg" class="btn" type="button">
  <i class="fa-solid fa-paper-plane"></i>
  Gửi
</button>
<?php $layout->end() ?>

<?php $layout->start("content") ?>
<form class="detail-layout" id="ticket-create-form" action="<?= url('admin/tickets') ?>" method="POST">
  <?= csrf_field() ?>
  <div class="detail-layout__main">
    <div class="card shadow">
      <div class="card__content">
        <div class="field-group">
          <div class="field" data-field-required>
            <label class="field__label" for="title">Tiêu đề</label>
            <input id="title" class="field__input" type="text" name="title"
              value="<?= htmlspecialchars($old_input['title'] ?? '') ?>">
          </div>

          <div class="field" data-field-required>
            <label class="field__label" for="reporter_email">Email người báo cáo</label>
            <input id="reporter_email" class="field__input" type="email" name="reporter_email"
              value="<?= htmlspecialchars($old_input['reporter_email'] ?? '') ?>">
          </div>

          <fieldset class="field__set">
            <legend class="field__label">Loại</legend>
            <div class="radio-group" data-field-required data-radio-name="type">
              <?php foreach ($types as $type): ?>
                <label class="field__label">
                  <div class="field" data-orientation="horizontal">
                    <button id="type-<?= $type ?>" class="radio-group__item" type="button" role="radio"
                      value="<?= $type ?>"></button>
                    <div class="field__title">
                      <?= ucfirst($type) ?>
                    </div>
                  </div>
                </label>
              <?php endforeach; ?>
            </div>
          </fieldset>

          <div class="field" data-field-required>
            <label class="field__label" for="description">Mô tả</label>
            <textarea id="description" class="field__input" name="description"
              rows="6"><?= htmlspecialchars($old_input['description'] ?? '') ?></textarea>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

<div class="modal" id="confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Xác nhận</h3>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="confirm-modal-btn" data-variant="primary" data-size="lg" class="btn" type="button">Xác nhận</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>
<?php $layout->end() ?>

<?php $layout->start("scripts") ?>
<script src="<?= url('public/js/pages/admin/tickets/create.js') ?>" type="module"></script>
<?php $layout->end() ?>
