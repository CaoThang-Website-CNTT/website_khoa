<?php

/**
 * View: Danh sách sinh viên & Phân công giảng viên
 * Route: /admin/internship_batches/{id}/students
 *
 * @var array $batch
 */

use App\Enums\BatchStatus;
use App\Models\InternshipBatch;

$batch = $batch ?? null;
$batchModel = new InternshipBatch();
$batchModel->status = $batch['status'] ?? BatchStatus::DRAFT;
$batchModel->start_at = $batch['start_at'] ?? null;
$batchModel->end_at = $batch['end_at'] ?? null;
$isReadOnly = in_array($batchModel->getEffectiveStatus(), [BatchStatus::CLOSED, BatchStatus::ENDED]);
?>

<link rel="stylesheet" href="<?= url('public/css/batch_students.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/batch_students_assignment.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/export.css') ?>">



<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">Sinh viên & Phân công</h2>
<p class="title-wrapper__description">Quản lý sinh viên và phân công giảng viên hướng dẫn đợt thực tập
  <span class="font-bold">"<?= htmlspecialchars($batch['title']) ?>"</span>
</p>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url('admin/internship_batches/' . $batch['id']) ?>" data-variant="outline" data-size="lg" class="btn">
  <i class="fa-solid fa-chevron-left"></i> Quay lại
</a>

<!-- Toolbar: Phân công tự động -->
<?php if (!$isReadOnly): ?>
  <button type="button" id="btn-auto-shuffle" class="btn" data-variant="secondary" data-size="lg">
    <i class="fa-solid fa-shuffle"></i> Ngẫu nhiên
  </button>
  <button type="button" id="btn-auto-even" class="btn" data-variant="primary" data-size="lg">
    <i class="fa-solid fa-chart-pie"></i> Chia đều
  </button>
<?php endif; ?>

<?php $layout->end() ?>

<!-- Overall Stats -->
<div class="assignment-stats">
  <div class="stat-box stat-box--primary shadow-sm">
    <div class="stat-box__icon">
      <i class="fa-solid fa-users"></i>
    </div>
    <div class="stat-box__content">
      <span class="stat-box__label">Tổng sinh viên</span>
      <div class="stat-box__value" id="stat-total-students">--</div>
    </div>
  </div>

  <div class="stat-box stat-box--success shadow-sm">
    <div class="stat-box__icon">
      <i class="fa-solid fa-user-check"></i>
    </div>
    <div class="stat-box__content">
      <span class="stat-box__label">Đã phân công</span>
      <div class="stat-box__value" id="stat-assigned-students">--</div>
    </div>
  </div>

  <div class="stat-box shadow-sm">
    <div class="stat-box__icon stat-box__icon--muted">
      <i class="fa-solid fa-user-clock"></i>
    </div>
    <div class="stat-box__content">
      <span class="stat-box__label">Chưa phân công</span>
      <div class="stat-box__value" id="stat-unassigned-students">--</div>
    </div>
  </div>
</div>

<div class="detail-layout">
  <!-- CỘT CHÍNH (2/3): Thống kê tổng và Danh sách sinh viên -->
  <div class="detail-layout__main">
    <div class="card shadow-sm">
      <div class="tm-container" data-tm="batch_students_table" data-tm-mode="client" data-tm-searchable="true"
        data-tm-selectable="true" data-tm-id-key="batch_student_id">

        <!-- Cột MSSV -->
        <template data-tm-col="student_code" data-tm-label="MSSV" data-tm-filter-type="text" data-tm-sortable
          data-tm-width="120px">
          <span class="text-sm">{{ value }}</span>
        </template>

        <!-- Cột Họ và Tên -->
        <template data-tm-col="student_name" data-tm-label="Họ và Tên" data-tm-filter-type="text" data-tm-sortable>
          <span class="font-medium text-sm">{{ value }}</span>
        </template>

        <!-- Cột Lớp -->
        <template data-tm-col="classroom_name" data-tm-label="Lớp" data-tm-filter-type="select"
          data-tm-filter-options='<?= json_encode($classOptions ?? []) ?>' data-tm-sortable data-tm-width="120px">
          <span class="text-sm font-semibold">{{ value || '--' }}</span>
        </template>

        <!-- Cột Công ty thực tập -->
        <template data-tm-col="company_name" data-tm-label="Công ty thực tập" data-tm-filter-type="select"
          data-tm-filter-options='<?= json_encode($companyOptions ?? []) ?>' data-tm-sortable data-tm-width="250px">
          <div class="company-cell flex flex-col">
            <span class="font-semibold text-sm" title="{{ value || 'Chưa có công ty' }}">
              {{ value || 'Chưa có công ty' }}
            </span>
            <span class="text-xs">
              {{ row.company_tax_code ? 'MST: ' + row.company_tax_code : '' }}
            </span>
            <span class="text-xs" title="{{ row.company_address }}">
              {{ row.company_address || '' }}
            </span>
          </div>
        </template>

        <!-- Cột Giảng viên hướng dẫn -->
        <template data-tm-col="teacher_name" data-tm-label="Giảng viên HD" data-tm-filter-type="select"
          data-tm-filter-options='<?= json_encode($teacherOptions ?? []) ?>' data-tm-sortable data-tm-width="280px">
          <div class="teacher-cell" data-assignment-id="{{ row.assignment_id || '' }}"
            data-batch-student-id="{{ row.batch_student_id }}" data-teacher-id="{{ row.teacher_id || '' }}">
            <div class="teacher-cell__display">
              <span class="teacher-cell__name text-sm font-semibold">{{ value || 'Chưa phân công' }}</span>
              <?php if (!$isReadOnly): ?>
                <button type="button" class="btn-teacher-edit btn-icon" title="Sửa phân công">
                  <i class="fa-solid fa-pen text-xs"></i>
                </button>
              <?php endif; ?>
            </div>
            <div class="teacher-cell__editor hidden">
              <select class="teacher-cell__select field__input">
                <!-- Rendered via JS -->
              </select>
            </div>
          </div>
        </template>

        <template data-tm-pagination></template>
      </div>
    </div>
  </div>

  <!-- CỘT PHỤ (1/3): Thống kê giảng viên -->
  <aside class="detail-layout__sidebar">
    <div class="card shadow-sm h-full flex flex-col">
      <div class="card__header flex justify-between items-center">
        <h3 class="font-bold text-lg"><i class="fa-solid fa-chalkboard-user mr-2"></i>Giảng viên <span class="badge"
            data-variant="primary" id="supervisor-count">0</span></h3>
        <a href="<?= url('admin/internship_batches/' . $batch['id'] . '/teachers') ?>" class="btn" data-variant="outline" data-size="sm" title="Quản lý giảng viên">
          <i class="fa-solid fa-up-right-from-square"></i>
        </a>
      </div>
      <hr class="separator">
      <div id="supervisor-stats-container" class="supervisor-list">
        <!-- Loading state -->
        <div class="flex flex-col items-center justify-center p-8">
          <i class="fa-solid fa-circle-notch fa-spin text-2xl mb-2"></i>
          <span class="text-sm">Đang tải dữ liệu...</span>
        </div>
      </div>
    </div>
  </aside>
</div>

<!-- Modal: Phân công hàng loạt -->
<div id="modal-bulk-assign" class="modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="title text-xl font-semibold">Phân công Giảng viên</h3>
    <p class="text-sm mt-1">Gán <span id="bulk-student-count" class="font-bold">0</span> sinh viên cho giảng viên được
      chọn dưới đây:</p>
  </div>
  <div class="py-4">
    <div class="field">
      <label class="field__label">Chọn Giảng viên hướng dẫn:</label>
      <select id="bulk-teacher-select" class="field__input">
        <!-- Rendered via JS -->
      </select>
    </div>
  </div>
  <div class="modal__footer">
    <button type="button" id="btn-close-bulk-modal" class="btn" data-size="lg" data-variant="outline"
      data-modal-close>Hủy</button>
    <button type="button" id="btn-confirm-bulk-assign" class="btn" data-size="lg" data-variant="primary">Xác nhận phân
      công</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<!-- Modal: Xác nhận Hủy phân công -->
<div id="modal-bulk-unassign" class="modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Xác nhận Hủy phân công</h3>
    <p class="modal__description">Bạn có chắc chắn muốn hủy phân công cho <span id="bulk-unassign-count"
        class="font-bold">0</span> sinh viên đã
      chọn</p>
  </div>
  <div class="modal__footer">
    <button type="button" id="btn-close-unassign-modal" class="btn" data-size="lg" data-variant="outline"
      data-modal-close>Hủy
      bỏ</button>
    <button type="button" id="btn-confirm-bulk-unassign" class="btn" data-size="lg" data-variant="destructive">Xác
      nhận Hủy</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<!-- Modal: Xác nhận Phân công Tự động (Chia đều) -->
<div id="modal-auto-even" class="modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Phân công đều</h3>
    <p class="modal__description">Phân công đều số lượng sinh viên cho giảng viên theo thứ tự từ trên xuống.</p>
  </div>
  <div class="modal__footer">
    <button type="button" id="btn-close-even-modal" class="btn" data-size="lg" data-variant="outline"
      data-modal-close>Hủy</button>
    <button type="button" id="btn-confirm-auto-even" class="btn" data-size="lg" data-variant="primary">Thực
      hiện</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<!-- Modal: Xác nhận Phân công Tự động (Ngẫu nhiên) -->
<div id="modal-auto-shuffle" class="modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Phân công ngẫu nhiên</h3>
    <p class="modal__description">Phân công ngẫu nhiên sinh viên cho giảng viên.</p>
  </div>
  <div class="modal__footer">
    <button type="button" id="btn-close-shuffle-modal" class="btn" data-size="lg" data-variant="outline"
      data-modal-close>Hủy</button>
    <button type="button" id="btn-confirm-auto-shuffle" class="btn" data-size="lg" data-variant="primary">Thực
      hiện</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<!-- Modal: Xác nhận Thay đổi Phân công -->
<div id="modal-confirm-assignment" class="modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title" id="confirm-assignment-title">Xác nhận</h3>
    <p class="modal__description" id="confirm-assignment-desc" style="white-space: pre-wrap;"></p>
  </div>
  <div class="modal__footer">
    <button type="button" id="btn-cancel-assignment" class="btn" data-size="lg" data-variant="outline"
      data-modal-close>Hủy</button>
    <button type="button" id="btn-confirm-assignment" class="btn" data-size="lg" data-variant="primary">Xác nhận</button>
  </div>
  <button class="modal__close" type="button" data-modal-close id="btn-close-assignment-modal"><i class="fa-solid fa-xmark"></i></button>
</div>

<?php $layout->start("scripts") ?>
<script>
  window.BATCH_ID = <?= json_encode($batch['id']) ?>;
  window.BATCH_STATUS = <?= json_encode($batch['status']) ?>;
  window.BATCH_TITLE = <?= json_encode($batch['title'] ?? '', JSON_UNESCAPED_UNICODE) ?>;
  window.BATCH_START = <?= json_encode($batch['start_at'] ? date('d/m/Y', strtotime($batch['start_at'])) : '') ?>;
  window.BATCH_END = <?= json_encode($batch['end_at'] ? date('d/m/Y', strtotime($batch['end_at'])) : '') ?>;
  window.IS_READONLY = <?= json_encode($isReadOnly) ?>;
  window.API_BASE_URL = <?= json_encode(url('api/v1/internship/batches')) ?>;
</script>
<script type="module" src="<?= url('public/js/pages/batch_students_manager.js') ?>"></script>
<?php $layout->end() ?>
