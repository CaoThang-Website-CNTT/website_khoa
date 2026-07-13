<?php

use App\Enums\ProjectTopicStatus;

$isEdit = isset($topic);
$actionUrl = $isEdit ? url("teacher/project_batches/{$batch['id']}/topics/{$topic['id']}/edit") : url("teacher/project_batches/{$batch['id']}/topics/create");
$old = request()->session()->getOldInputs();

$title = $old['title'] ?? ($topic['title'] ?? '');
$description = $old['description'] ?? ($topic['description'] ?? '');
$maxStudents = $old['max_students'] ?? ($topic['max_students'] ?? 10);
$pdfFilePath = $topic['pdf_file_path'] ?? null;
$status = $topic['status'] ?? null;
$rejectReason = $topic['reject_reason'] ?? null;
?>

<?php $layout->start("heading") ?>
<h2 class="title-wrapper__title">
  <?= $isEdit ? 'Cập nhật đề tài' : 'Thêm đề tài mới' ?>
</h2>
<p class="title-wrapper__description">Đợt: <?= htmlspecialchars($batch['title']) ?></p>
<?php $layout->end() ?>

<?php $layout->start("actions") ?>
<a href="<?= url("teacher/project_batches/{$batch['id']}") ?>" data-variant="outline" data-size="md" class="btn">
  <i class="fa-solid fa-chevron-left"></i>
  Hủy bỏ
</a>
<button type="submit" form="topic_form" name="action" value="draft" class="btn" data-variant="outline" data-size="md">
  <i class="fa-solid fa-floppy-disk mr-1"></i> Lưu nháp
</button>
<button type="button" class="btn" data-variant="primary" data-size="md" data-modal-trigger="#submit-confirm-modal">
  <i class="fa-solid fa-paper-plane mr-1"></i>Gửi duyệt
</button>
<?php $layout->end() ?>

<?php $layout->start("content"); ?>

<?php if ($status === ProjectTopicStatus::REJECTED && $rejectReason): ?>
  <div class="alert mb-4" data-variant="error">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div class="alert-title">Đề tài bị từ chối</div>
    <div class="alert-description">
      <strong>Lý do:</strong> <?= nl2br(htmlspecialchars($rejectReason)) ?><br>
      Vui lòng cập nhật lại thông tin theo yêu cầu và nộp lại.
    </div>
  </div>
<?php endif; ?>

<div class="card shadow-sm">
  <form action="<?= $actionUrl ?>" method="POST" enctype="multipart/form-data" id="topic_form" class="card__content">
    <?= csrf_field() ?>

    <div class="field-group">
      <div class="field" data-field-required>
        <label for="title" class="field__label">Tên đề tài</label>
        <input type="text" name="title" id="title" class="field__input" required
          value="<?= htmlspecialchars($title) ?>"
          placeholder="Nhập tên đề tài...">
      </div>

      <div class="field" data-field-required>
        <label for="description" class="field__label">Mô tả chi tiết</label>
        <textarea name="description" id="description" class="field__input" rows="6" required
          placeholder="Mô tả mục tiêu, yêu cầu, công nghệ sử dụng..."><?= htmlspecialchars($description) ?></textarea>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div class="field" data-field-required>
          <label for="max_students" class="field__label">Số lượng sinh viên tối đa</label>
          <input type="number" name="max_students" id="max_students" class="field__input" required min="2" max="100" step="2"
            value="<?= htmlspecialchars((int) $maxStudents) ?>">
        </div>

        <div class="field" data-field-conditionally-required>
          <label for="pdf_file" class="field__label">Tài liệu mô tả (PDF)</label>
          <input type="file" name="pdf_file" id="pdf_file" class="field__input" accept=".pdf">
          <?php if ($pdfFilePath): ?>
            <div class="field__description">
              Đã tải lên: <a class="btn" data-size="sm" data-variant="secondary" href="<?= url('storage/' . $pdfFilePath) ?>" target="_blank">Xem file</a>. Chọn file mới nếu muốn thay đổi.
            </div>
          <?php else: ?>
            <div class="field__description">Bắt buộc khi gửi duyệt. Dung lượng tối đa 10MB.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </form>
</div>

<!-- Modal xác nhận gửi duyệt -->
<div class="modal" id="submit-confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Xác nhận gửi duyệt đề tài</h3>
    <p class="modal__description">Bạn có chắc chắn muốn nộp đề tài này cho Quản trị viên duyệt? Sau khi nộp sẽ không thể chỉnh sửa.</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" class="btn" data-size="lg" type="button">Hủy</button>
    <button onclick="submitForm()" data-variant="primary" class="btn" data-size="lg" type="button">Nộp duyệt</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<script>
  function submitForm() {
    const form = document.getElementById('topic_form');
    if (form.reportValidity()) {
      let actionInput = document.getElementById('action_value');
      if (!actionInput) {
        actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.id = 'action_value';
        form.appendChild(actionInput);
      }
      actionInput.value = 'submit';
      form.submit();
    } else {
      if (window.ModalHandler) {
        ModalHandler.instance.close();
      }
    }
  }
</script>
<?php $layout->end(); ?>
