<?php $batchObj = (object)$batch; ?>

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">Giảng viên phụ trách</h2>
<p class="title-wrapper__description">Đợt: <?= htmlspecialchars($batchObj->title) ?></p>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url("admin/project_batches/{$batchObj->id}") ?>" class="btn" data-variant="outline" data-size="md">
  <i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Quay lại
</a>
<button type="button" class="btn" data-variant="primary" data-size="md" onclick="ModalHandler.instance.open('#add-teacher-modal')">
  <i class="fa-solid fa-plus"></i> Thêm giảng viên
</button>
<?php $layout->end() ?>

<?php
$totalCapacity = array_reduce($teachers, function ($c, $i) {
  return $c + ($i['max_students'] ?? 0);
}, 0);
$totalAssigned = array_reduce($teachers, function ($c, $i) {
  return $c + ($i['current_load'] ?? 0);
}, 0); // Giả định có trường current_load
?>

<div class="stats-grid assignment-stats">
  <div class="card stats-card">
    <div class="card__header">
      <span class="stats-card__label">Tổng giảng viên</span>
      <span class="stats-card__value"><?= count($teachers) ?></span>
    </div>
  </div>
  <div class="card stats-card">
    <div class="card__header">
      <span class="stats-card__label">Tổng sức chứa (SV)</span>
      <span class="stats-card__value"><?= $totalCapacity ?></span>
    </div>
  </div>
  <div class="card stats-card">
    <div class="card__header">
      <span class="stats-card__label">SV đã phân công</span>
      <span class="stats-card__value"><?= $totalAssigned ?></span>
    </div>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card__header">
    <h3 class="font-semibold">Danh sách giảng viên</h3>
  </div>
  <hr class="separator">
  <div class="card__content">
    <?php if (empty($teachers)): ?>
      <div class="empty">
        <h3 class="empty__title">Chưa có giảng viên phụ trách</h3>
        <p class="empty__description">Đợt đồ án này chưa được phân công giảng viên.</p>
      </div>
    <?php else: ?>
      <div class="tm-container" data-tm="project_batch_teachers" data-tm-mode="client" data-tm-searchable>
        <template data-tm-col="full_name" data-tm-label="Giảng viên" data-tm-sortable data-tm-filter-type="text">
          <div class="flex flex-col">
            <span class="font-medium">{{ value }}</span>
            <span class="text-sm">{{ row.email }}</span>
          </div>
        </template>
        <template data-tm-col="min_students" data-tm-label="Tối thiểu" data-tm-sortable></template>
        <template data-tm-col="max_students" data-tm-label="Tối đa" data-tm-sortable>
          <span class="font-medium">{{ row.max_students == 0 ? 'Không giới hạn' : row.max_students }}</span>
        </template>
        <template data-tm-col="current_load" data-tm-label="Đang hướng dẫn">
          <span class="font-medium text-sm">{{ row.current_load || 0 }} / {{ row.max_students == 0 ? '∞' : row.max_students }}</span>
        </template>
        <template data-tm-col="_actions" data-tm-label="" data-tm-align="right">
          <div class="actions-container text-right" data-teacher-id="{{ row.teacher_id }}"></div>
        </template>
        <template data-tm-pagination></template>
        <script type="application/json" data-tm-data="project_batch_teachers">
          <?= json_encode([
            'rows' => array_values($teachers),
            'total' => count($teachers),
            'page' => 1,
            'limit' => max(count($teachers), 15),
          ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>
        </script>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal Thêm giảng viên -->
<div class="modal" id="add-teacher-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Thêm giảng viên phụ trách</h3>
  </div>
  <form id="add-teacher-form" action="<?= url("admin/project_batches/{$batch['id']}/teachers/add") ?>" method="POST">
    <div class="modal__content py-4">
      <?= csrf_field() ?>
      <div class="space-y-4">
        <div class="field">
          <label class="field__label">Chọn giảng viên</label>
          <select name="teacher_id" class="field__input" required>
            <option value="">-- Chọn giảng viên khoa CNTT --</option>
            <?php foreach ($availableTeachers as $at): ?>
              <option value="<?= $at['teacher_id'] ?>"><?= htmlspecialchars($at['full_name'] . ' (' . $at['email'] . ')') ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="field">
            <label class="field__label">SV tối thiểu</label>
            <input type="number" name="min_students" class="field__input" value="0" min="0" step="2" required>
          </div>
          <div class="field">
            <label class="field__label">SV tối đa</label>
            <input type="number" name="max_students" class="field__input" value="20" min="0" step="2" required>
            <span class="text-xs mt-1 block">Nhập 0 nếu không giới hạn</span>
          </div>
        </div>
      </div>
    </div>
    <div class="modal__footer flex justify-end gap-2">
      <button type="button" class="btn" data-variant="outline" data-size="md" data-modal-close>Hủy</button>
      <button type="submit" class="btn" data-variant="primary" data-size="md">Thêm giảng viên</button>
    </div>
  </form>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<!-- Modal Sửa chỉ tiêu -->
<div class="modal" id="edit-capacity-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Sửa chỉ tiêu sinh viên</h3>
  </div>
  <form id="edit-capacity-form" action="<?= url("admin/project_batches/{$batchObj->id}/teachers/update-capacity") ?>" method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="teacher_id" id="edit_teacher_id">
    <div class="modal__content py-2">
      <div class="field" data-field-required>
        <label for="min_students" class="field__label">Số lượng sinh viên tối thiểu</label>
        <input type="number" name="min_students" id="edit_min_students" class="field__input" required min="0" step="2">
      </div>
      <div class="field mt-4" data-field-required>
        <label for="max_students" class="field__label">Số lượng sinh viên tối đa</label>
        <input type="number" name="max_students" id="edit_max_students" class="field__input" required min="0" step="2">
      </div>
    </div>
    <div class="modal__footer">
      <button data-modal-close type="button" class="btn" data-variant="outline" data-size="lg">Hủy</button>
      <button type="submit" class="btn btn-confirm-action" data-variant="primary" data-size="lg" data-confirm-msg="Xác nhận lưu chỉ tiêu mới?" data-modal-trigger="#action-confirm-modal">Lưu thay đổi</button>
    </div>
  </form>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<!-- Modal Xác nhận thao tác chung -->
<div class="modal" id="action-confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Xác nhận thao tác</h3>
    <p class="modal__description" id="action-confirm-msg">Bạn có chắc chắn muốn thực hiện thao tác này?</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="md" class="btn" type="button">Hủy</button>
    <button id="action-confirm-btn" data-variant="primary" data-size="md" class="btn" type="button">Chắc chắn</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<?php $layout->start('scripts') ?>
<script>
  window.BATCH_ID = <?= json_encode($batchObj->id) ?>;
  window.CSRF_FIELD = `<?= csrf_field() ?>`;
  window.API_URL_TEACHERS = <?= json_encode(url("admin/project_batches/{$batchObj->id}/teachers")) ?>;
</script>
<script src="<?= url('public/js/pages/admin/project_batch_teachers.js') ?>"></script>
<?php $layout->end() ?>