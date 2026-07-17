<?php

/**
 * View: Danh sách sinh viên & Phân công giảng viên
 * Route: /admin/internship_batches/{id}/students
 *
 * @var array $batch
 */

use App\Enums\BatchStatus;

$batch = $batch ?? null;
$isReadOnly = ($batch['status'] ?? null) === BatchStatus::CLOSED;
?>

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

<div id="batch-students-export-action"></div>

<?php if (!$isReadOnly): ?>
  <form id="publishGradesForm" style="display:none;"></form>
  <button type="button" id="btn-publish-grades" class="btn" data-variant="primary" data-size="lg"
    <?= ($batch['stats']['locked_grades'] ?? 0) === 0 ? 'disabled' : '' ?>>
    <i class="fa-solid fa-bullhorn"></i> Công bố điểm
  </button>
  <button type="button" id="btn-auto-shuffle" class="btn" data-variant="secondary" data-size="lg">
    <i class="fa-solid fa-shuffle"></i> Ngẫu nhiên
  </button>
  <button type="button" id="btn-auto-even" class="btn" data-variant="outline" data-size="lg">
    <i class="fa-solid fa-chart-pie"></i> Chia đều
  </button>
<?php endif; ?>

<?php $layout->end() ?>

<?php if ($batch['grades_published_at']): ?>
  <div class="alert mb-2" data-variant="warning">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <span>Kết quả điểm của đợt thực tập này đã được công bố cho sinh viên vào lúc <strong><?= date('H:i d/m/Y', strtotime($batch['grades_published_at'])) ?></strong>.</span>
  </div>
<?php endif; ?>
<!-- Overall Stats -->
<div class="stats-grid">
  <div class="card stats-card">
    <div class="card__header">
      <span class="stats-card__label">Tổng sinh viên</span>
      <span class="stats-card__value" id="stat-total-students"><?= $batch['stats']['total_students'] ?? 0 ?></span>
    </div>
  </div>
  <div class="card stats-card">
    <div class="card__header">
      <span class="stats-card__label">Đã phân công</span>
      <span class="stats-card__value" id="stat-assigned-students"><?= $batch['stats']['assigned_students'] ?? 0 ?></span>
    </div>
  </div>
  <div class="card stats-card">
    <div class="card__header">
      <span class="stats-card__label">Chưa phân công</span>
      <span class="stats-card__value" id="stat-unassigned-students"><?= $batch['stats']['total_students'] - ($batch['stats']['assigned_students'] ?? 0) ?></span>
    </div>
  </div>
  <div class="card stats-card">
    <div class="card__header">
      <span class="stats-card__label">Tiến độ chấm điểm</span>
      <span class="stats-card__value" id="stat-locked-grades"><?= $batch['stats']['locked_grades'] ?? 0 ?></span>
    </div>
  </div>
</div>

<div class="detail-layout">
  <!-- CỘT CHÍNH (2/3): Thống kê tổng và Danh sách sinh viên -->
  <div class="detail-layout__main">
    <div class="card shadow-sm">
      <div class="card__content">
        <div class="tm-container" data-tm="batch_students_table" data-tm-mode="server" data-tm-searchable="true"
          data-tm-selectable="true" data-tm-id-key="batch_student_id">

          <!-- Cột MSSV -->
          <template data-tm-col="student_code" data-tm-label="MSSV" data-tm-filter-type="text" data-tm-sortable
            data-tm-width="120px">
            <span class="text-sm">{{ value }}</span>
          </template>

          <!-- Cột Họ và Tên -->
          <template data-tm-col="student_name" data-tm-label="Họ và Tên" data-tm-filter-type="text" data-tm-sortable>
            <div class="flex flex-col">
              <span class="font-medium text-sm">{{ value }}</span>
              <span class="text-xs" style="color: var(--muted-foreground);">{{ row.classroom_name || '--' }}</span>
            </div>
          </template>

          <!-- Cột Chi tiết -->
          <template data-tm-col="details" data-tm-label="Chi tiết" data-tm-width="80px" data-tm-align="center">
            <button type="button" class="btn-icon btn-view-details" data-id="{{ row.batch_student_id }}" title="Xem chi tiết">
              <i class="fa-solid fa-circle-info" style="color: var(--primary);"></i>
            </button>
          </template>

          <!-- Cột Giảng viên hướng dẫn -->
          <template data-tm-col="teacher_name" data-tm-label="Giảng viên HD" data-tm-filter-type="select"
            data-tm-filter-options='<?= json_encode($teacherOptions ?? []) ?>' data-tm-sortable data-tm-width="220px">
            <div class="teacher-cell" data-assignment-id="{{ row.assignment_id || '' }}"
              data-batch-student-id="{{ row.batch_student_id }}" data-teacher-id="{{ row.teacher_id || '' }}">
              <div class="teacher-cell__display flex flex-col">
                <span class="teacher-cell__name text-sm font-semibold">{{ value || 'Chưa phân công' }}</span>
                <?php if (!$isReadOnly): ?>
                  <button type="button" class="btn-teacher-edit text-xs text-left" style="color: var(--primary);">
                    Thay đổi
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

          <!-- Cột Điểm -->
          <template data-tm-col="grade" data-tm-label="Điểm" data-tm-width="120px" data-tm-sortable data-tm-align="center">
            <div class="flex items-center justify-center gap-2">
              <span class="font-bold"
                style="color: {{ row.grade_lock_at ? 'var(--primary)' : (row.grade !== null ? 'var(--muted-foreground)' : 'inherit') }}"
                title="{{ row.grade_lock_at ? 'Đã chốt' : (row.grade !== null ? 'Bản nháp' : '') }}">
                {{ row.grade !== null ? row.grade : 'Chưa có' }}
              </span>
              <?php if (!$isReadOnly): ?>
                <button type="button" class="btn-icon btn-edit-grade" data-id="{{ row.batch_student_id }}" title="Sửa điểm" style="display: {{ row.grade !== null ? 'block' : 'none' }}">
                  <i class="fa-solid fa-pen text-xs"></i>
                </button>
              <?php endif; ?>
            </div>
          </template>

          <template data-tm-pagination></template>
        </div>
      </div>
    </div>
  </div>

  <!-- CỘT PHỤ (1/3): Thống kê giảng viên -->
  <aside class="detail-layout__sidebar">
    <div class="card shadow-sm h-full flex flex-col">
      <div class="card__header">
        <h3 class="card__title"><i class="fa-solid fa-chalkboard-user mr-2"></i>Giảng viên <span class="badge"
            data-variant="primary" id="supervisor-count">0</span></h3>
        <a href="<?= url('admin/internship_batches/' . $batch['id'] . '/teachers') ?>" class="btn card__action"
          data-variant="outline" data-size="sm" title="Quản lý giảng viên">
          <i class="fa-solid fa-up-right-from-square"></i>
        </a>
      </div>
      <hr class="separator">
      <div class="card__content">
        <div id="supervisor-stats-container" class="supervisor-list">
          <!-- Loading state -->
          <div class="flex flex-col items-center justify-center p-8">
            <i class="fa-solid fa-circle-notch fa-spin text-2xl mb-2"></i>
            <span class="text-sm">Đang tải dữ liệu...</span>
          </div>
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
    <p class="modal__description">Phân công đều số lượng sinh viên cho giảng viên theo thứ tự từ trên xuống. Nếu đợt đã công bố, hệ thống sẽ gửi thông báo email cho sinh viên và giảng viên liên quan.</p>
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
    <p class="modal__description">Phân công ngẫu nhiên sinh viên cho giảng viên. Nếu đợt đã công bố, hệ thống sẽ gửi thông báo email cho sinh viên và giảng viên liên quan.</p>
  </div>
  <div class="modal__footer">
    <button type="button" id="btn-close-shuffle-modal" class="btn" data-size="lg" data-variant="outline"
      data-modal-close>Hủy</button>
    <button type="button" id="btn-confirm-auto-shuffle" class="btn" data-size="lg" data-variant="primary">Thực
      hiện</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<!-- Modal: Xác nhận thay đổi khi đợt đã công bố -->
<div id="modal-confirm-published-assignment" class="modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Xác nhận thay đổi phân công</h3>
    <p class="modal__description">Thao tác này sẽ cập nhật phân công và gửi thông báo email cho sinh viên, giảng viên liên quan. Bạn có muốn tiếp tục?</p>
  </div>
  <div class="modal__footer">
    <button type="button" id="btn-close-published-assignment" class="btn" data-size="lg" data-variant="outline" data-modal-close>Hủy</button>
    <button type="button" id="btn-confirm-published-assignment" class="btn" data-size="lg" data-variant="primary">Tiếp tục</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<!-- Modal: Chi tiết Sinh viên -->
<div id="modal-publish-grades" class="modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Xác nhận công bố điểm</h3>
    <p class="modal__description">Bạn có chắc chắn muốn công bố tất cả điểm đã chốt của đợt thực tập này? Sinh viên sẽ có thể xem điểm của mình.</p>
  </div>
  <div class="modal__footer">
    <button type="button" class="btn" data-size="lg" data-variant="outline" data-modal-close>Hủy</button>
    <button type="button" id="btn-confirm-publish-grades" class="btn" data-size="lg" data-variant="primary">Công bố</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<div id="modal-student-details" class="modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Chi tiết Sinh viên</h3>
  </div>
  <div class="py-4 space-y-3 text-sm" id="student-details-content">
    <!-- Rendered via JS -->
  </div>
  <div class="modal__footer">
    <button type="button" class="btn" data-size="lg" data-variant="outline" data-modal-close>Đóng</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<!-- Modal: Sửa điểm thủ công -->
<div id="modal-edit-grade" class="modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Sửa điểm Sinh viên</h3>
    <p class="modal__description">Bạn đang thay đổi điểm cho sinh viên <span id="edit-grade-student-name" class="font-bold"></span></p>
  </div>
  <form id="edit-grade-form" class="py-4 space-y-4">
    <input type="hidden" id="edit-grade-batch-student-id">
    <div class="field">
      <label class="field__label">Điểm số (0-10) <span class="text-danger">*</span></label>
      <input type="number" id="edit-grade-score" class="field__input" step="0.25" min="0" max="10" required>
    </div>
    <div class="field">
      <label class="field__label">Lý do điều chỉnh (nếu có)</label>
      <input type="text" id="edit-grade-reason" class="field__input" placeholder="Ví dụ: Phúc khảo điểm...">
    </div>
    <div class="field">
      <label class="field__label">Nhận xét chi tiết</label>
      <textarea id="edit-grade-feedback" class="field__input" rows="3"></textarea>
    </div>
  </form>
  <div class="modal__footer">
    <button type="button" class="btn" data-size="lg" data-variant="outline" data-modal-close>Hủy</button>
    <button type="submit" form="edit-grade-form" class="btn" data-size="lg" data-variant="primary">Lưu thay đổi</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<?php $layout->start("scripts") ?>
<script>
  window.BATCH_ID = <?= json_encode($batch['id']) ?>;
  window.BATCH_STATUS = <?= json_encode($batch['status']) ?>;
  window.BATCH_TITLE = <?= json_encode($batch['title'] ?? '', JSON_UNESCAPED_UNICODE) ?>;
  window.BATCH_START = <?= json_encode($batch['start_at'] ? date('d/m/Y', strtotime($batch['start_at'])) : '') ?>;
  window.BATCH_END = <?= json_encode($batch['end_at'] ? date('d/m/Y', strtotime($batch['end_at'])) : '') ?>;
  window.API_BASE_URL = <?= json_encode(url('api/v1/internship/batches')) ?>;
  window.CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;
</script>
<script type="module" src="<?= url('public/js/pages/batch_students_manager.js') ?>"></script>
<?php $layout->end() ?>