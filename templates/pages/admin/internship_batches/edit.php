<?php
$batchObj = (object)$batch;
$stats = $batchObj->stats;
$assignedPercent = $stats['total_students'] > 0 ? round(($stats['assigned_students'] / $stats['total_students']) * 100) : 0;

$statusMap = [
  'draft' => ['label' => 'Nháp', 'class' => 'text-muted-foreground'],
  'published' => ['label' => 'Đang mở', 'class' => 'text-primary'],
  'closed' => ['label' => 'Kết thúc', 'class' => 'text-destructive'],
];
$currentStatus = $statusMap[$batchObj->status] ?? $statusMap['draft'];
?>

<link rel="stylesheet" href="<?= url('public/css/internship_batch_detail.css') ?>">

<script>
  window.CURRENT_BATCH_ID = <?= $batchObj->id ?>;
  window.API_BASE_URL = "<?= url('api/v1/internship/batches/' . $batchObj->id . '/management') ?>";
</script>

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
      <p class="text-sm">Quản lý và cập nhật thông tin đợt thực tập</p>
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
      <?php elseif ($batchObj->status === 'published'): ?>
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

      <a href="<?= url('admin/internship_batches/' . $batchObj->id . '/assignments') ?>" data-variant="primary" data-size="md" class="btn">
        <i class="fa-solid fa-users-gear"></i>
        Phân công hướng dẫn
      </a>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->

<div class="detail-layout mt-6">
  <!-- CỘT CHÍNH (TRÁI) -->
  <div class="detail-layout__main">

    <!-- Stats Grid -->
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

    <!-- Tabs: Quản lý chi tiết -->
    <div class="tabs-container mt-6">
      <div class="tabs-list border-b mb-4">
        <button class="tab-trigger" data-tab="overview" data-state="active">Tổng quan</button>
        <button class="tab-trigger" data-tab="students" data-state="inactive">Danh sách Sinh viên (<?= $stats['total_students'] ?>)</button>
        <button class="tab-trigger" data-tab="supervisors" data-state="inactive">Giảng viên hướng dẫn (<?= $stats['total_supervisors'] ?>)</button>
      </div>

      <!-- Tab 1: Tổng quan -->
      <div class="tab-content" id="tab-overview" data-state="active">
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
      </div>

      <!-- Tab 2: Sinh viên -->
      <div class="tab-content hidden" id="tab-students" data-state="inactive">
        <div class="card shadow-sm">
          <div class="card__header flex justify-between items-center p-4">
            <h4 class="font-semibold text-sm">Sinh viên tham gia</h4>
            <button type="button" class="btn" data-variant="outline" data-size="sm" id="btn-add-student">
              <i class="fa-solid fa-plus mr-1"></i> Thêm sinh viên
            </button>
          </div>
          <div class="table-wrapper">
            <table class="table" id="table-batch-students">
              <thead>
                <tr>
                  <th>MSSV</th>
                  <th>Họ và tên</th>
                  <th>Lớp</th>
                  <th>Trạng thái</th>
                  <th>Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="5" class="text-center p-4">Đang tải dữ liệu...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Tab 3: Giảng viên -->
      <div class="tab-content hidden" id="tab-supervisors" data-state="inactive">
        <div class="card shadow-sm">
          <div class="card__header flex justify-between items-center p-4">
            <h4 class="font-semibold text-sm">Giảng viên hướng dẫn</h4>
            <button type="button" class="btn" data-variant="outline" data-size="sm" id="btn-add-supervisor">
              <i class="fa-solid fa-plus mr-1"></i> Thêm giảng viên
            </button>
          </div>
          <div class="table-wrapper">
            <table class="table" id="table-batch-supervisors">
              <thead>
                <tr>
                  <th>Họ và tên</th>
                  <th>Khoa</th>
                  <th class="text-center">Hạn mức (Đã giao/Tổng)</th>
                  <th>Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="4" class="text-center p-4">Đang tải dữ liệu...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- SIDEBAR -->
  <div class="detail-layout__sidebar">
    <!-- Card: Đối tượng áp dụng -->
    <div class="card shadow-sm">
      <div class="card__header text-sm font-semibold">Đối tượng áp dụng</div>
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

<!-- Modal: Thêm Sinh viên -->
<div class="modal modal--lg" id="modal-add-student" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Thêm sinh viên vào đợt</h2>
    <p class="modal__description">Tìm kiếm sinh viên từ danh sách toàn trường để thêm vào đợt thực tập này.</p>
  </div>
  <div class="modal__content p-6">
    <div class="flex gap-4 mb-4">
      <div class="flex-1">
        <input type="text" id="search-student-query" class="field__input" placeholder="Tìm theo tên hoặc MSSV...">
      </div>
      <div class="w-48">
        <select id="search-student-classroom" class="field__input">
          <option value="">Tất cả lớp</option>
        </select>
      </div>
      <button type="button" class="btn" data-variant="primary" id="btn-do-search-student">
        <i class="fa-solid fa-magnifying-glass"></i>
      </button>
    </div>
    <div class="table-wrapper max-h-96 overflow-y-auto">
      <table class="table" id="table-search-student-results">
        <thead>
          <tr>
            <th width="40"></th>
            <th>MSSV</th>
            <th>Họ tên</th>
            <th>Lớp</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="4" class="text-center p-8 text-muted">Nhập thông tin để tìm kiếm...</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" class="btn" type="button">Hủy</button>
    <button id="btn-confirm-add-students" data-variant="primary" class="btn" type="button" disabled>Thêm sinh viên đã chọn</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<!-- Modal: Thêm Giảng viên -->
<div class="modal" id="modal-add-supervisor" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Thêm giảng viên hướng dẫn</h2>
    <p class="modal__description">Chọn giảng viên và thiết lập định mức hướng dẫn tối đa.</p>
  </div>
  <div class="modal__content p-6">
    <div class="field mb-4">
      <label class="field__label">Tìm giảng viên</label>
      <div class="flex gap-2">
        <input type="text" id="search-teacher-query" class="field__input" placeholder="Tên giảng viên...">
        <button type="button" class="btn" data-variant="outline" id="btn-do-search-teacher">Tìm</button>
      </div>
    </div>
    <div class="field mb-4">
      <label class="field__label">Chọn giảng viên</label>
      <select id="select-teacher-id" class="field__input">
        <option value="">-- Chọn giảng viên --</option>
      </select>
    </div>
    <div class="field">
      <label class="field__label">Định mức tối đa (Sinh viên)</label>
      <input type="number" id="input-teacher-quota" class="field__input" value="15" min="1">
    </div>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" class="btn" type="button">Hủy</button>
    <button id="btn-confirm-add-supervisor" data-variant="primary" class="btn" type="button">Xác nhận thêm</button>
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