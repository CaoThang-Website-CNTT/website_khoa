<?php

use App\Enums\BatchStatus;
use App\Models\InternshipBatch;

$batchObj = (object) $batch;
$stats = $batchObj->stats;
$assignedPercent = $stats['total_students'] > 0 ? round(($stats['assigned_students'] / $stats['total_students']) * 100) : 0;

$batchModel = new InternshipBatch();
$batchModel->status = $batchObj->status ?? 'draft';
$batchModel->start_at = $batchObj->start_at ?? null;
$batchModel->end_at = $batchObj->end_at ?? null;

$effStatus = $batchModel->getEffectiveStatus();

$effStatus = $batchModel->getEffectiveStatus();

$currentStatus = [
  'label' => BatchStatus::getLabel($effStatus),
  'variant' => BatchStatus::getVariant($effStatus),
  'class' => 'badge'
];
?>

<link rel="stylesheet" href="<?= url('public/css/internship_batch_detail.css') ?>">

<script>
  window.CURRENT_BATCH_ID = <?= $batchObj->id ?>;
  window.API_BASE_URL = "<?= url('api/v1/internship/batches/' . $batchObj->id . '/management') ?>";
</script>



<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">
  Chi tiết đợt thực tập #<?= htmlspecialchars($batchObj->id) ?>
</h2>
<p class="title-wrapper__description">Quản lý và cập nhật thông tin đợt thực tập</p>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url('admin/internship_batches') ?>" data-variant="outline" data-size="md" class="btn">
  <i class="fa-solid fa-chevron-left"></i>
  Quay lại
</a>
<button type="button" id="edit-submit-btn" data-modal-trigger="#save-confirm-modal" data-variant="primary"
  data-size="md" class="btn">
  <i class="fa-solid fa-floppy-disk"></i>
  Lưu thay đổi
</button>
<?php if ($batchObj->status === BatchStatus::DRAFT): ?>
  <button type="button" id="publish-btn" data-modal-trigger="#publish-confirm-modal"
    data-action="<?= url('admin/internship_batches/' . $batchObj->id . '/publish') ?>" data-variant="outline-alt"
    data-size="md" class="btn">
    <i class="fa-solid fa-paper-plane"></i>
    Công bố
  </button>
<?php elseif ($batchObj->status === BatchStatus::PUBLISHED): ?>
  <button type="button" id="close-btn" data-modal-trigger="#close-confirm-modal"
    data-action="<?= url('admin/internship_batches/' . $batchObj->id . '/close') ?>" data-variant="destructive"
    data-size="md" class="btn">
    <i class="fa-solid fa-circle-stop"></i>
    Kết thúc đợt
  </button>
<?php endif; ?>
<?php if (!$stats['has_submissions'] && !$stats['has_grades']): ?>
  <button type="button" id="delete-btn" data-modal-trigger="#delete-confirm-modal" data-variant="destructive"
    data-size="md" class="btn">
    <i class="fa-solid fa-trash"></i>
    Xóa
  </button>
<?php endif; ?>
<?php $layout->end() ?>
<div class="detail-layout">
  <!-- CỘT CHÍNH (TRÁI) -->
  <div class="detail-layout__main">

    <!-- Stats Grid -->
    <div class="stats-grid">
      <!-- Sinh viên Card -->
      <a href="<?= url('admin/internship_batches/' . $batchObj->id . '/students') ?>"
        class="stat-card stat-card--interactive shadow-sm">
        <div class="flex justify-between items-start">
          <span class="stat-card__label">Sinh viên</span>
          <i class="fa-solid fa-chevron-right stat-card__arrow text-xs"></i>
        </div>
        <span class="stat-card__value"><?= $stats['total_students'] ?></span>
        <div class="stat-card__footer">
          Đã phân công: <?= $stats['assigned_students'] ?>
          <div class="progress-container">
            <div class="progress-bar" style="--progress: <?= $assignedPercent ?>%"></div>
          </div>
        </div>
      </a>

      <!-- Giảng viên Card -->
      <a href="<?= url('admin/internship_batches/' . $batchObj->id . '/teachers') ?>"
        class="stat-card stat-card--interactive shadow-sm">
        <div class="flex justify-between items-start">
          <span class="stat-card__label">Giảng viên</span>
          <i class="fa-solid fa-chevron-right stat-card__arrow text-xs"></i>
        </div>
        <span class="stat-card__value"><?= $stats['total_supervisors'] ?></span>
        <div class="stat-card__footer">
          Xem danh sách giảng viên
        </div>
      </a>

      <!-- Giấy giới thiệu Card -->
      <a href="<?= url('admin/internship_batches/' . $batchObj->id . '/referral_letters') ?>"
        class="stat-card stat-card--interactive shadow-sm">
        <div class="flex justify-between items-start">
          <span class="stat-card__label">Giấy giới thiệu</span>
          <i class="fa-solid fa-chevron-right stat-card__arrow text-xs"></i>
        </div>
        <span class="stat-card__value"><?= $stats['total_referrals'] ?></span>
        <div class="stat-card__footer">
          Chờ duyệt: <span class="stat-card__badge-pending"><?= $stats['pending_referrals'] ?></span>
        </div>
      </a>

      <!-- Trạng thái Card -->
      <div class="stat-card shadow-sm">
        <span class="stat-card__label">Trạng thái</span>
        <span class="stat-card__value">
          <span class="<?= $currentStatus['class'] ?>"
            data-variant="<?= $currentStatus['variant'] ?>"><?= $currentStatus['label'] ?></span>
        </span>
      </div>
    </div>

    <!-- Thông tin cơ bản -->
    <div class="card shadow-sm">
      <form id="batch-edit-form" action="<?= url('admin/internship_batches/' . $batchObj->id) ?>" method="POST">
        <?= csrf_field() ?>
        <div class="card__header">
          <h3 class="font-semibold">Thông tin cơ bản</h3>
        </div>
        <hr class="separator">
        <div class="card__content p-6">
          <div class="field-group">
            <div class="field" data-field-required>
              <label class="field__label" for="title">Tên đợt thực tập</label>
              <input type="text" id="title" name="title" class="field__input"
                value="<?= htmlspecialchars($batchObj->title) ?>" required>
            </div>

            <div class="field">
              <label class="field__label" for="description">Mô tả</label>
              <textarea id="description" name="description" class="field__input"
                rows="6"><?= htmlspecialchars($batchObj->description ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="field" data-field-required>
                <label class="field__label" for="start_at">Ngày bắt đầu</label>
                <input type="date" id="start_at" name="start_at" class="field__input"
                  value="<?= date('Y-m-d', strtotime($batchObj->start_at)) ?>" required>
              </div>
              <div class="field" data-field-required>
                <label class="field__label" for="end_at">Ngày kết thúc</label>
                <input type="date" id="end_at" name="end_at" class="field__input"
                  value="<?= date('Y-m-d', strtotime($batchObj->end_at)) ?>" required>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>

  </div>

  <!-- SIDEBAR -->
  <div class="detail-layout__sidebar">

    <!-- Card: Thông tin khác -->
    <div class="card shadow-sm">
      <div class="card__header text-sm font-semibold">Thông tin khác</div>
      <hr class="separator">
      <div class="card__content p-4 text-xs space-y-3">
        <div class="flex justify-between">
          <span>ID:</span>
          <span class="font-medium">#<?= $batchObj->id ?></span>
        </div>
        <div class="flex justify-between">
          <span>Ngày tạo:</span>
          <span class="font-medium"><?= date('d/m/Y H:i', strtotime($batchObj->created_at)) ?></span>
        </div>
        <div class="flex justify-between">
          <span>Cập nhật cuối:</span>
          <span
            class="font-medium"><?= $batchObj->updated_at ? date('d/m/Y H:i', strtotime($batchObj->updated_at)) : 'N/A' ?></span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modals & Scripts -->
<!-- Modal: Xác nhận Lưu -->
<div class="modal" id="save-confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Lưu thay đổi?</h2>
    <p class="modal__description">Bạn có chắc chắn muốn cập nhật các thông tin cơ bản cho đợt thực tập này?</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" class="btn" type="button">Hủy</button>
    <button id="save-confirm-modal-btn" data-variant="primary" class="btn" type="button">Xác nhận lưu</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<!-- Modal: Xác nhận Xóa -->
<div class="modal" id="delete-confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Xóa đợt thực tập?</h2>
    <p class="modal__description">Hành động này không thể hoàn tác. Mọi dữ liệu về sinh viên tham gia và phân công sẽ bị
      xóa bỏ.</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" class="btn" type="button">Hủy</button>
    <button id="delete-confirm-modal-btn" data-variant="destructive" class="btn" type="button">Tôi muốn xóa</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<!-- Form ẩn cho hành động Xóa -->
<form action="<?= url('admin/internship_batches/delete/' . $batchObj->id) ?>" method="POST" id="delete-form"
  class="hidden">
  <?= csrf_field() ?>
</form>

<!-- Modal: Xác nhận Công bố đợt thực tập -->
<div class="modal" id="publish-confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Công bố đợt thực tập?</h2>
    <p class="modal__description">Sinh viên và giảng viên sẽ nhìn thấy đợt thực tập này sau khi công bố.</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" class="btn" type="button">Hủy</button>
    <button id="publish-confirm-modal-btn" data-variant="primary" class="btn" type="button">Xác nhận công bố</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<!-- Modal: Xác nhận Kết thúc -->
<div class="modal" id="close-confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Kết thúc đợt thực tập?</h2>
    <p class="modal__description">Hệ thống sẽ khóa mọi hoạt động nộp bài và chấm điểm cho đợt thực tập này.</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" class="btn" type="button">Hủy</button>
    <button id="close-confirm-modal-btn" data-variant="destructive" class="btn" type="button">Xác nhận kết thúc</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<script src="<?= url('public/js/pages/internship_batch_detail.js') ?>"></script>