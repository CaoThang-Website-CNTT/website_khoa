<?php
$batchObj = (object)$batch;
$stats = $batchObj->stats;
$assignedPercent = $stats['total_students'] > 0 ? round(($stats['assigned_students'] / $stats['total_students']) * 100) : 0;

$statusMap = [
  'draft' => ['label' => 'Nháp', 'class' => 'text-muted-foreground'],
  'public' => ['label' => 'Đang mở', 'class' => 'text-primary'],
  'closed' => ['label' => 'Kết thúc', 'class' => 'text-destructive'],
];
$currentStatus = $statusMap[$batchObj->status] ?? $statusMap['draft'];
?>

<link rel="stylesheet" href="<?= url('public/css/internship_batch_detail.css') ?>">

<?php if ($flash = request()->session()->getFlash("notification")): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      toast.<?= ($flash['type']) ?>(
        '<?= $flash['title'] ?>',
        '<?= $flash['desc'] ?>'
      );
    });
  </script>
<?php endif; ?>

<!-- ========== title-wrapper start ========== -->
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div class="col-6">
      <h2 class="title text-2xl font-semibold">
        Chi tiết đợt thực tập #<?= htmlspecialchars($batchObj->id) ?>
      </h2>
      <p class="text-sm text-muted-foreground">Quản lý và cập nhật thông tin đợt thực tập</p>
    </div>

    <div class="flex gap-2">
      <a href="<?= url('admin/internship_batches') ?>" data-variant="outline" data-size="md" class="btn">
        <i class="fa-solid fa-chevron-left"></i>
        Quay lại
      </a>

      <button type="button" id="edit-submit-btn" data-modal-trigger="#save-confirm-modal" data-variant="primary" data-size="md" class="btn">
        <i class="fa-solid fa-floppy-disk"></i>
        Lưu thay đổi
      </button>

      <?php if ($batchObj->status === 'draft'): ?>
        <button type="button" id="publish-btn" data-modal-trigger="#publish-confirm-modal"
          data-action="<?= url('admin/internship_batches/' . $batchObj->id . '/publish') ?>"
          data-variant="outline-alt" data-size="md" class="btn">
          <i class="fa-solid fa-paper-plane"></i>
          Công bố
        </button>
      <?php elseif ($batchObj->status === 'public'): ?>
        <button type="button" id="close-btn" data-modal-trigger="#close-confirm-modal"
          data-action="<?= url('admin/internship_batches/' . $batchObj->id . '/close') ?>"
          data-variant="destructive" data-size="md" class="btn">
          <i class="fa-solid fa-circle-stop"></i>
          Kết thúc đợt
        </button>
      <?php endif; ?>

      <?php if (!$stats['has_submissions'] && !$stats['has_grades']): ?>
        <button type="button" id="delete-btn" data-modal-trigger="#delete-confirm-modal" data-variant="destructive" data-size="md" class="btn">
          <i class="fa-solid fa-trash"></i>
          Xóa
        </button>
      <?php endif; ?>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->

<div class="detail-layout mt-6">
  <!-- CỘT CHÍNH (TRÁI) -->
  <div class="detail-layout__main">

    <!-- Stats Grid (Mobile-friendly) -->
    <div class="stats-grid">
      <div class="stat-card shadow-sm">
        <span class="stat-card__label">Sinh viên</span>
        <span class="stat-card__value"><?= $stats['total_students'] ?></span>
        <div class="stat-card__footer">
          Đã phân công: <?= $stats['assigned_students'] ?>
          <div class="progress-container">
            <div class="progress-bar" style="--progress: <?= $assignedPercent ?>%"></div>
          </div>
        </div>
      </div>
      <div class="stat-card shadow-sm">
        <span class="stat-card__label">Giảng viên</span>
        <span class="stat-card__value"><?= $stats['total_supervisors'] ?></span>
      </div>
      <div class="stat-card shadow-sm">
        <span class="stat-card__label">Trạng thái</span>
        <span class="stat-card__value">
          <span class="<?= $currentStatus['class'] ?>"><?= $currentStatus['label'] ?></span>
        </span>
      </div>
    </div>

    <!-- Card: Thông tin cơ bản -->
    <div class="card shadow-sm">
      <form id="batch-edit-form" action="<?= url('admin/internship_batches/' . $batchObj->id) ?>" method="POST">
        <?= csrf_field() ?>
        <div class="card__header">
          <h3 class="font-semibold">Thông tin chung</h3>
        </div>
        <hr class="separator">
        <div class="card__content p-6">
          <div class="field-group">
            <div class="field" data-field-required>
              <label class="field__label" for="title">Tên đợt thực tập</label>
              <input type="text" id="title" name="title" class="field__input" value="<?= htmlspecialchars($batchObj->title) ?>" required>
            </div>

            <div class="field">
              <label class="field__label" for="description">Mô tả</label>
              <textarea id="description" name="description" class="field__input" rows="6"><?= htmlspecialchars($batchObj->description ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="field" data-field-required>
                <label class="field__label" for="start_at">Ngày bắt đầu</label>
                <input type="date" id="start_at" name="start_at" class="field__input" value="<?= date('Y-m-d', strtotime($batchObj->start_at)) ?>" required>
              </div>
              <div class="field" data-field-required>
                <label class="field__label" for="end_at">Ngày kết thúc</label>
                <input type="date" id="end_at" name="end_at" class="field__input" value="<?= date('Y-m-d', strtotime($batchObj->end_at)) ?>" required>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>

    <!-- Tabs: Quản lý chi tiết -->
    <div class="tabs-container">
      <div class="tabs-list">
        <button class="tab-trigger" data-tab="students" data-state="active">Sinh viên & Phân công</button>
        <button class="tab-trigger" data-tab="supervisors" data-state="inactive">Giảng viên hướng dẫn</button>
      </div>

      <div class="tab-content" id="tab-students" data-state="active">
        <div class="card shadow-sm">
          <div class="card__content p-12 text-center">
            <div class="flex flex-col items-center gap-4">
              <i class="fa-solid fa-users-gear text-4xl text-muted"></i>
              <div>
                <h4 class="font-semibold">Quản lý phân công sinh viên</h4>
                <p class="text-sm text-muted-foreground mt-1">Sử dụng công cụ này để gán sinh viên cho giảng viên hướng dẫn.</p>
              </div>
              <a href="<?= url('admin/internship_batches/' . $batchObj->id . '/assignments') ?>" data-variant="primary" class="btn mt-2">
                Quản lý phân công <i class="fa-solid fa-arrow-right ml-1"></i>
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="tab-content" id="tab-supervisors" data-state="inactive">
        <div class="card shadow-sm">
          <div class="card__content p-12 text-center text-muted-foreground">
            <i class="fa-solid fa-chalkboard-user text-3xl mb-2"></i>
            <p>Danh sách giảng viên hướng dẫn sẽ được hiển thị tại đây.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- SIDEBAR -->
  <div class="detail-layout__sidebar">
    <!-- Card (Readonly) -->
    <div class="card shadow-sm">
      <div class="card__header">
        <h3 class="font-semibold text-sm">Đối tượng áp dụng</h3>
      </div>
      <hr class="separator">
      <div class="card__content p-4 space-y-1">
        <div class="readonly-info">
          <span class="readonly-info__label">Niên khóa:</span>
          <span class="readonly-info__value">Khóa <?= htmlspecialchars($batchObj->class_of) ?></span>
        </div>
        <div class="readonly-info">
          <span class="readonly-info__label">Bậc học:</span>
          <span class="readonly-info__value"><?= htmlspecialchars($batchObj->level) ?></span>
        </div>
      </div>
    </div>

    <!-- Card: Thông tin khác -->
    <div class="card shadow-sm">
      <div class="card__header text-sm font-semibold">Thông tin khác</div>
      <hr class="separator">
      <div class="card__content p-4 text-xs space-y-3">
        <div class="flex justify-between">
          <span class="text-muted-foreground">ID:</span>
          <span class="font-medium">#<?= $batchObj->id ?></span>
        </div>
        <div class="flex justify-between">
          <span class="text-muted-foreground">Ngày tạo:</span>
          <span class="font-medium"><?= date('d/m/Y H:i', strtotime($batchObj->created_at)) ?></span>
        </div>
        <div class="flex justify-between">
          <span class="text-muted-foreground">Cập nhật cuối:</span>
          <span class="font-medium"><?= $batchObj->updated_at ? date('d/m/Y H:i', strtotime($batchObj->updated_at)) : 'N/A' ?></span>
        </div>
      </div>
    </div>
  </div>
</div>

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
    <p class="modal__description">Hành động này không thể hoàn tác. Mọi dữ liệu về sinh viên tham gia và phân công sẽ bị xóa bỏ.</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" class="btn" type="button">Hủy</button>
    <button id="delete-confirm-modal-btn" data-variant="destructive" class="btn" type="button">Tôi muốn xóa</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<!-- Form ẩn cho hành động Xóa -->
<form action="<?= url('admin/internship_batches/delete/' . $batchObj->id) ?>" method="POST" id="delete-form" class="hidden">
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