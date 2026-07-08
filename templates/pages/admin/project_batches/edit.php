<?php

use App\Enums\ProjectBatchStatus;
use App\Models\ProjectBatch;

$batchObj = (object) $batch;
$stats = $batchObj->stats;
$assignedPercent = $stats['total_groups'] > 0 ? round(($stats['approved_topics'] / $stats['total_groups']) * 100) : 0;

$batchModel = new ProjectBatch();
$batchModel->status = $batchObj->status ?? 'draft';
$batchModel->topic_proposal_start = $batchObj->topic_proposal_start ?? null;
$batchModel->topic_proposal_end = $batchObj->topic_proposal_end ?? null;
$batchModel->registration_start = $batchObj->registration_start ?? null;
$batchModel->registration_end = $batchObj->registration_end ?? null;

$effStatus = $batchModel->getEffectivePhase();

$currentStatus = [
  'label' => ProjectBatchStatus::getLabel($effStatus),
  'variant' => ProjectBatchStatus::getVariant($effStatus),
  'class' => 'badge'
];
?>

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">
  Chi tiết đợt đồ án "<?= htmlspecialchars($batchObj->title) ?>"
</h2>
<p class="title-wrapper__description">Quản lý và cập nhật thông tin đợt đồ án</p>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url("admin/project_batches") ?>" data-variant="outline" data-size="md" class="btn">
  <i class="fa-solid fa-chevron-left"></i>
  Quay lại
</a>
<button type="button" id="edit-submit-btn" data-modal-trigger="#save-confirm-modal" data-variant="primary"
  data-size="md" class="btn">
  <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
  Lưu
</button>
<?php if ($batchObj->status === ProjectBatchStatus::DRAFT): ?>
  <button type="button" id="publish-btn" data-modal-trigger="#publish-confirm-modal" data-variant="outline-alt"
    data-size="md" class="btn">
    <i class="fa-solid fa-paper-plane"></i>
    Công bố
  </button>
<?php elseif ($batchObj->status === ProjectBatchStatus::PUBLISHED): ?>
  <button type="button" id="close-btn" data-modal-trigger="#close-confirm-modal" data-variant="destructive"
    data-size="md" class="btn">
    <i class="fa-solid fa-circle-stop"></i>
    Kết thúc đợt
  </button>
<?php endif; ?>
<?php if ($stats['total_topics'] == 0 && $stats['total_groups'] == 0): ?>
  <button type="button" id="delete-btn" data-modal-trigger="#delete-confirm-modal" data-variant="destructive"
    data-size="md" class="btn">
    <i class="fa-solid fa-trash"></i>
    Xóa
  </button>
<?php endif; ?>
<?php $layout->end() ?>

<?php if ($batchObj->status === ProjectBatchStatus::PUBLISHED && (empty($batchObj->registration_start) || empty($batchObj->registration_end))): ?>
  <div class="alert" data-variant="warning">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div class="alert-content">
      <h3 class="alert-title font-semibold">Chưa cấu hình thời gian đăng ký đề tài</h3>
      <p class="alert-description">
        Đợt đồ án đã công bố nhưng chưa được thiết lập thời gian đăng ký đề tài (bắt đầu/kết thúc). Do đó, sinh viên hiện tại <span class="font-semibold">chưa thể xem đợt, xem đề tài và đăng ký</span>. Vui lòng cập nhật thời gian đăng ký.
      </p>
    </div>
  </div>
<?php endif; ?>

<!-- Stats Grid -->
<div class="stats-grid">
  <!-- Đề tài Card -->
  <a href="<?= url('admin/project_batches/' . $batchObj->id . '/topics') ?>" class="card stats-card hover:bg-muted/50 transition-colors">
    <div class="card__header">
      <div class="flex justify-between">
        <span class="stats-card__label">Đề tài</span>
        <i class="fa-solid fa-up-right-from-square"></i>
      </div>
      <span class="stats-card__value"><?= $stats['total_topics'] ?></span>
    </div>
    <div class="card__footer">
      Đã duyệt: <?= $stats['approved_topics'] ?>
    </div>
  </a>

  <!-- Nhóm sinh viên Card -->
  <a href="<?= url('admin/project_batches/' . $batchObj->id . '/allocation') ?>" class="card stats-card hover:bg-muted/50 transition-colors">
    <div class="card__header">
      <div class="flex justify-between">
        <span class="stats-card__label">Nhóm sinh viên</span>
        <i class="fa-solid fa-up-right-from-square"></i>
      </div>
      <span class="stats-card__value"><?= $stats['total_groups'] ?></span>
    </div>
    <div class="card__footer">
      Tổng số Sinh viên: <?= $stats['total_students'] ?>
    </div>
  </a>

  <!-- Giảng viên Card -->
  <a href="<?= url('admin/project_batches/' . $batchObj->id . '/teachers') ?>" class="card stats-card hover:bg-muted/50 transition-colors">
    <div class="card__header">
      <div class="flex justify-between">
        <span class="stats-card__label">Giảng viên</span>
        <i class="fa-solid fa-up-right-from-square"></i>
      </div>
      <span class="stats-card__value"><?= $stats['total_supervisors'] ?? 0 ?></span>
    </div>
    <div class="card__footer">
      Đang tham gia hướng dẫn
    </div>
  </a>

  <!-- Trạng thái Card -->
  <div class="card stats-card">
    <div class="card__header">
      <span class="stats-card__label">Giai đoạn</span>
      <span class="badge" data-variant="<?= $currentStatus['variant'] ?>">
        <?= $currentStatus['label'] ?>
      </span>
    </div>
  </div>
</div>

<div class="detail-layout">
  <!-- CỘT CHÍNH (TRÁI) -->
  <div class="detail-layout__main">

    <!-- Thông tin cơ bản -->
    <form class="card shadow-sm" id="batch-edit-form"
      action="<?= url('admin/project_batches/' . $batchObj->id) ?>" method="POST">
      <?= csrf_field() ?>
      <div class="card__header">
        <h3 class="font-semibold">Thông tin cơ bản</h3>
      </div>
      <hr class="separator">
      <div class="card__content space-y-4">
        <div class="field-group">
          <div class="field" data-field-required>
            <label class="field__label" for="title">Tên đợt đồ án</label>
            <input type="text" id="title" name="title" class="field__input"
              value="<?= htmlspecialchars($batchObj->title) ?>" required>
          </div>

          <div class="field">
            <label class="field__label" for="description">Mô tả</label>
            <textarea id="description" name="description" class="field__input"
              rows="4"><?= htmlspecialchars($batchObj->description ?? '') ?></textarea>
          </div>

          <div class="grid grid-cols-3 gap-4">
            <div class="field" data-field-required>
              <label class="field__label" for="min_class_of">Khóa áp dụng (Từ)</label>
              <input type="number" id="min_class_of" class="field__input" name="min_class_of" value="<?= htmlspecialchars($batchObj->min_class_of) ?>" min="1" required>
            </div>
            <div class="field" data-field-required>
              <label class="field__label" for="max_class_of">Khóa áp dụng (Đến)</label>
              <input type="number" id="max_class_of" class="field__input" name="max_class_of" value="<?= htmlspecialchars($batchObj->max_class_of) ?>" min="1" required>
            </div>
            <div class="field" data-field-required>
              <label class="field__label" for="max_aspirations">Số nguyện vọng tối đa</label>
              <input type="number" id="max_aspirations" class="field__input" name="max_aspirations" value="<?= htmlspecialchars($batchObj->max_aspirations) ?>" min="1" required>
            </div>
          </div>



          <hr class="separator mt-4 mb-2" />
          <h4 class="font-medium mb-4">Thời gian Đề xuất đề tài</h4>
          <div class="grid grid-cols-2 gap-4">
            <div class="field" data-field-required>
              <label class="field__label" for="topic_proposal_start">Bắt đầu đề xuất</label>
              <input type="date" id="topic_proposal_start" class="field__input" name="topic_proposal_start" value="<?= !empty($batchObj->topic_proposal_start) ? date('Y-m-d', strtotime($batchObj->topic_proposal_start)) : '' ?>" required>
            </div>
            <div class="field" data-field-required>
              <label class="field__label" for="topic_proposal_end">Kết thúc đề xuất</label>
              <input type="date" id="topic_proposal_end" class="field__input" name="topic_proposal_end" value="<?= !empty($batchObj->topic_proposal_end) ? date('Y-m-d', strtotime($batchObj->topic_proposal_end)) : '' ?>" required>
            </div>
          </div>

          <hr class="separator mt-4 mb-2" />
          <h4 class="font-medium mb-4">Thời gian Đăng ký đề tài</h4>
          <div class="grid grid-cols-2 gap-4">
            <div class="field">
              <label class="field__label" for="registration_start">Bắt đầu đăng ký</label>
              <input type="date" id="registration_start" class="field__input" name="registration_start" value="<?= !empty($batchObj->registration_start) ? date('Y-m-d', strtotime($batchObj->registration_start)) : '' ?>">
            </div>
            <div class="field">
              <label class="field__label" for="registration_end">Kết thúc đăng ký</label>
              <input type="date" id="registration_end" class="field__input" name="registration_end" value="<?= !empty($batchObj->registration_end) ? date('Y-m-d', strtotime($batchObj->registration_end)) : '' ?>">
            </div>
          </div>

        </div>
      </div>
    </form>

  </div>

  <!-- SIDEBAR -->
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
            <?= htmlspecialchars($batchObj->id) ?>
          </dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Được tạo vào</dt>
          <dd>
            <?= htmlspecialchars($batchObj->created_at) ?>
          </dd>
        </dl>
      </div>
    </div>
  </div>
</div>

<!-- Modals -->
<div class="modal" id="save-confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Xác nhận chỉnh sửa</h3>
    <p class="modal__description">Bạn có chắc muốn lưu các thay đổi này?</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" class="btn" data-size="lg" type="button">Hủy</button>
    <button onclick="document.getElementById('batch-edit-form').submit()" data-variant="primary" class="btn" data-size="lg" type="button">Lưu</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<div class="modal" id="delete-confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Xác nhận xóa đợt</h3>
    <p class="modal__description">Đợt <strong><?= htmlspecialchars($batchObj->title) ?></strong> sẽ bị xóa và không thể khôi phục. Bạn có chắc chắn?</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" class="btn" data-size="lg" type="button">Hủy</button>
    <button onclick="document.getElementById('delete-form').submit()" data-variant="destructive" class="btn" data-size="lg" type="button">Xóa</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<form action="<?= url('admin/project_batches/delete/' . $batchObj->id) ?>" method="POST" id="delete-form" class="hidden">
  <?= csrf_field() ?>
</form>

<?php if ($batchObj->status === ProjectBatchStatus::DRAFT): ?>
  <form action="<?= url('admin/project_batches/' . $batchObj->id . '/publish') ?>" method="POST" id="publish-form" class="hidden">
    <?= csrf_field() ?>
  </form>
<?php elseif ($batchObj->status === ProjectBatchStatus::PUBLISHED): ?>
  <form action="<?= url('admin/project_batches/' . $batchObj->id . '/close') ?>" method="POST" id="close-form" class="hidden">
    <?= csrf_field() ?>
  </form>
<?php endif; ?>

<div class="modal" id="publish-confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Xác nhận công bố</h3>
    <p class="modal__description">Các đề tài đã duyệt sẽ được công bố cho sinh viên xem và đăng ký. Giảng viên phụ trách đã có thể cung cấp đề tài ngay từ giai đoạn bản nháp.</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" class="btn" data-size="lg" type="button">Hủy</button>
    <button onclick="document.getElementById('publish-form').submit()" data-variant="primary" class="btn" data-size="lg" type="button">Xác nhận</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<div class="modal" id="close-confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Xác nhận kết thúc</h3>
    <p class="modal__description">Hệ thống sẽ khóa đợt đồ án này.</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" class="btn" data-size="lg" type="button">Hủy</button>
    <button onclick="document.getElementById('close-form').submit()" data-variant="destructive" class="btn" data-size="lg" type="button">Xác nhận</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>