<?php $layout->start("heading") ?>
<h2 class="title-wrapper__title">Cập nhật ticket #<?= htmlspecialchars($ticket->id) ?></h2>
<?php $layout->end() ?>

<?php $layout->start("actions") ?>
<button data-modal-trigger="#confirm-modal" data-variant="primary" data-size="lg" class="btn" type="button">
  Lưu
</button>
<?php $layout->end() ?>

<?php $layout->start("content") ?>
<form class="detail-layout" id="ticket-edit-form" action="<?= url('admin/tickets/' . $ticket->id) ?>" method="POST">
  <?= csrf_field() ?>
  <div class="detail-layout__main">
    <div class="card shadow">
      <div class="card__content field-group">
        <div class="field">
          <label class="field__label">Tiêu đề</label>
          <input class="field__input" type="text" value="<?= htmlspecialchars($ticket->title) ?>" readonly>
        </div>
        <div class="field" data-field-required>
          <label class="field__label" for="status">Trạng thái</label>
          <select id="status" class="field__input" name="status">
            <?php foreach ($statuses as $status): ?>
              <option value="<?= htmlspecialchars($status) ?>" <?= $ticket->status === $status ? 'selected' : '' ?>>
                <?= htmlspecialchars(ucfirst($status)) ?>
              </option>
            <?php endforeach; ?>
          </select>
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
    <button id="confirm-modal-btn" data-variant="primary" data-size="lg" class="btn" type="button">Lưu</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>
<?php $layout->end() ?>

<?php $layout->start("scripts") ?>
<script src="<?= url('public/js/pages/admin/tickets/edit.js') ?>" type="module"></script>
<?php $layout->end() ?>
