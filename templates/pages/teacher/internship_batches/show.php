<?php

use App\Enums\BatchStatus;
use App\Models\InternshipBatch;
use App\Core\Auth;

/**
 * @var array $batch
 * @var array $stats
 * @var array $students
 */

// Xử lý dữ liệu danh sách sinh viên
$studentsData = array_map(function ($sv) {
  return [
    'student_code' => $sv['student_code'],
    'full_name' => $sv['full_name'],
    'classroom_name' => $sv['classroom_name'],
    'company_name' => $sv['company_name'],
    'submission_name' => $sv['submission_name'],
    'submission_url' => url('storage/' . ltrim($sv['submission_path'], '/')),
    'submission_count' => (int) $sv['submission_count'],
    'grade' => $sv['grade'] !== null ? (float) $sv['grade'] : null,
    'is_locked' => $sv['grade_lock_at'] !== null ? '1' : '0',
    'grade_color' => ($sv['grade_lock_at'] !== null)
      ? 'var(--primary)'
      : 'var(--muted-foreground)',
    'batch_student_id' => $sv['batch_student_id'],
    // Các trường hỗ trợ filter (0/1)
    'has_submission' => $sv['submission_name'] ? '1' : '0',
    'has_grade' => $sv['grade'] !== null ? '1' : '0',
    'grade_btn_variant' => ($sv['submission_name'] && $sv['grade'] === null) ? 'primary' : 'outline-alt'
  ];
}, $students);

?>
<link rel="stylesheet" href="<?= url('public/css/teacher_batch_detail.css') ?>">


<?php $layout->start("heading") ?>
<h2 class="title-wrapper__title">
  Chi tiết đợt thực tập "<?= htmlspecialchars((string) $batch['title']) ?>"
</h2>
<div class="title-wrapper__description flex items-center">
  <?= htmlspecialchars((string) $batch['description']) ?>
  <?php
  $batchModel = new InternshipBatch();
  $batchModel->status = $batch['status'] ?? 'draft';
  $batchModel->start_at = $batch['start_at'] ?? null;
  $batchModel->end_at = $batch['end_at'] ?? null;
  $effStatus = $batchModel->getEffectiveStatus();
  ?>
  <span class="badge ml-2" data-variant="<?= BatchStatus::getVariant($effStatus) ?>">
    <?= BatchStatus::getLabel($effStatus) ?>
  </span>
</div>
<?php $layout->end() ?>

<?php $layout->start("actions") ?>
<a href="<?= url('teacher/internship_batches') ?>" data-variant="outline" data-size="md" class="btn">
  <i class="fa-solid fa-chevron-left"></i>
  Quay lại
</a>
<a href="<?= url("teacher/internship_batches/{$batch['id']}/weekly_reports") ?>" data-variant="secondary" data-size="md"
  class="btn">
  <i class="fa-solid fa-calendar-week mr-2"></i>
  Báo cáo tuần
</a>
<form id="publishForm" action="<?= url("teacher/internship_batches/{$batch['id']}/publish_grades") ?>" method="POST"
  class="inline-block">
  <?= csrf_field() ?>
  <button type="button" data-modal-trigger="#publish-confirm-modal" class="btn" data-variant="primary" data-size="md">
    <i class="fa-solid fa-lock mr-2"></i> Công bố điểm
  </button>
</form>
<?php $layout->end() ?>

<!-- Stats Grid -->
<div class="stats-grid">

  <div class="card stats-card">
    <div class="card__header">
      <span class="stats-card__label">Tài liệu báo cáo</span>
      <span class="stats-card__value"><?= number_format($stats['has_submission']) ?></span>
    </div>
    <div class="card__footer">
      Trên tổng số <?= number_format($stats['total_students']) ?> sinh viên
    </div>
  </div>

  <div class="card stats-card">
    <div class="card__header">
      <span class="stats-card__label">Đã có công ty</span>
      <span class="stats-card__value"><?= number_format($stats['has_company']) ?></span>
    </div>
    <div class="card__footer">
      Trên tổng số <?= number_format($stats['total_students']) ?> sinh viên
    </div>
  </div>

  <div class="card stats-card">
    <div class="card__header">
      <span class="stats-card__label">Đã nhập điểm</span>
      <span class="stats-card__value"><?= number_format($stats['has_grade']) ?></span>
    </div>
    <div class="card__footer">
      Trên tổng số <?= number_format($stats['total_students']) ?> sinh viên
    </div>
  </div>

  <div class="card stats-card">
    <div class="card__header">
      <span class="stats-card__label">Điểm đã chốt</span>
      <span class="stats-card__value"><?= number_format($stats['locked_grades'] ?? 0) ?></span>
    </div>
    <div class="card__footer">
      Trên tổng số <?= number_format($stats['total_students']) ?> sinh viên
    </div>
  </div>
</div>

<div class="detail-layout">
  <!-- CỘT CHÍNH (TRÁI) -->
  <div class="detail-layout__main">

    <!-- Table Sinh Viên -->
    <div class="card shadow-sm">
      <div class="card__header">
        <h3 class="font-semibold">Danh sách sinh viên hướng dẫn</h3>
      </div>

      <hr class="separator">
      <div class="card__content">
        <div class="tm-container" data-tm="students_table" data-tm-mode="client" data-tm-searchable>
          <!-- Cột MSSV -->
          <template data-tm-col="student_code" data-tm-label="MSSV" data-tm-width="120px" data-tm-sortable
            data-tm-filter-type="text">
            <span class="font-medium text-md">{{ value }}</span>
          </template>

          <!-- Cột Họ và tên -->
          <template data-tm-col="full_name" data-tm-label="Họ và tên" data-tm-sortable data-tm-filter-type="text">
            <div class="flex flex-col">
              <span class="student-table__name font-medium">{{ value }}</span>
              <span class="text-xs" style="color: var(--muted-foreground);">{{ row.classroom_name || '-' }}</span>
            </div>
          </template>

          <!-- Cột Công ty -->
          <template data-tm-col="company_name" data-tm-label="Công ty TT" data-tm-sortable>
            <div>
              <span class="badge" data-variant="secondary" style="{{ value ? 'display:none' : '' }}">Chưa có</span>
              <div title="{{ value }}" style="{{ value ? '' : 'display:none' }}" class="company-truncate">{{ value }}
              </div>
            </div>
          </template>

          <!-- Cột Tài liệu -->
          <template data-tm-col="has_submission" data-tm-label="Tài liệu TT">
            <span class="badge" data-variant="secondary" style="{{ value === '1' ? 'display:none' : '' }}">Chưa
              nộp</span>
            <span class="badge" data-variant="primary" style="{{ value === '1' ? '' : 'display:none' }}">Đã nộp</span>
          </template>

          <!-- Cột Điểm -->
          <template data-tm-col="has_grade" data-tm-label="Điểm" data-tm-width="100px" data-tm-sortable
            data-tm-align="center">
            <span class="badge" data-variant="secondary" style="{{ value === '1' ? 'display:none' : '' }}">Chưa
              nhập</span>
            <span class="font-bold" style="{{ value === '1' ? '' : 'display:none' }}; color: {{ row.grade_color }};"
              title="{{ row.is_locked === '1' ? 'Đã chốt' : 'Bản nháp' }}">
              {{ row.grade }}
            </span>
          </template>

          <!-- Cột Thao tác -->
          <template data-tm-col="actions" data-tm-label="Thao tác" data-tm-width="100px" data-tm-align="center">
            <div class="flex gap-2 justify-center">
              <button class="btn btn-icon" data-variant="outline" data-size="sm" title="Xem chi tiết" data-action="view"
                data-id="{{ row.batch_student_id }}" data-name="{{ row.full_name }}">
                <i class="fa-solid fa-eye"></i>
              </button>
              <button class="btn btn-icon" data-variant="{{ row.grade_btn_variant }}" data-size="sm" title="Nhập điểm"
                data-action="grade" data-id="{{ row.batch_student_id }}" data-name="{{ row.full_name }}">
                <i class="fa-solid fa-pen-to-square"></i>
              </button>
            </div>
          </template>

          <template data-tm-pagination></template>
        </div>
      </div>
    </div>

  </div>


  <!-- SIDEBAR -->
  <div class="detail-layout__sidebar">

    <!-- Thông tin cơ bản -->
    <div class="card shadow-sm">
      <div class="card__header">
        <h3 class="font-semibold">Thông tin cơ bản</h3>
      </div>
      <hr class="separator">
      <div class="card__content">
        <div class="field-group">
          <div class="field" data-field-readonly>
            <label class="field__label" for="title">Tên đợt thực tập</label>
            <input type="text" id="title" name="title" class="field__input"
              value="<?= htmlspecialchars($batch['title']) ?>" readonly>
          </div>

          <div class="field" data-field-readonly>
            <label class="field__label" for="description">Mô tả</label>
            <textarea id="description" name="description" class="field__input" rows="6"
              readonly><?= htmlspecialchars($batch['description'] ?? '') ?></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="field" data-field-readonly>
              <label class="field__label" for="start_at">Ngày bắt đầu</label>
              <input type="date" id="start_at" name="start_at" class="field__input"
                value="<?= date('Y-m-d', strtotime($batch['start_at'])) ?>" readonly>
            </div>
            <div class="field" data-field-readonly>
              <label class="field__label" for="end_at">Ngày kết thúc</label>
              <input type="date" id="end_at" name="end_at" class="field__input"
                value="<?= date('Y-m-d', strtotime($batch['end_at'])) ?>" readonly>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Thông tin thời gian -->
    <div class="metadata-card card shadow">
      <div class="card__header">
        <div class="card__title">Thông tin thời gian</div>
      </div>
      <hr class="separator">
      <div class="card__content space-y-4">
        <dl class="flex justify-between">
          <dt>ID</dt>
          <dd>#<?= htmlspecialchars((string) $batch['id']) ?></dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Được tạo vào</dt>
          <dd><?= $batch['created_at'] ? date('d/m/Y H:i', strtotime($batch['created_at'])) : 'N/A' ?></dd>
        </dl>
        <?php if ($batch['published_at']): ?>
          <hr class="separator">
          <dl class="flex justify-between">
            <dt>Được công bố vào</dt>
            <dd><?= date('d/m/Y H:i', strtotime($batch['published_at'])) ?></dd>
          </dl>
        <?php endif; ?>
        <?php if ($batch['closed_at']): ?>
          <hr class="separator">
          <dl class="flex justify-between">
            <dt>Được kết thúc vào</dt>
            <dd class="text-danger"><?= date('d/m/Y H:i', strtotime($batch['closed_at'])) ?></dd>
          </dl>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Xác nhận Công bố điểm -->
<div class="modal" id="publish-confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Xác nhận công bố điểm</h3>
    <p class="modal__description">Hành động này sẽ chốt và công bố <span class="font-semibold">TOÀN BỘ</span> điểm nháp
      hiện tại của bạn cho sinh viên. Các sinh viên chưa được nhập điểm sẽ bị bỏ qua. Bạn có chắc chắn muốn tiếp tục?
    </p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" class="btn" data-size="lg" type="button">Hủy</button>
    <button form="publishForm" data-variant="primary" class="btn" data-size="lg" type="submit">Xác nhận</button>
  </div>
  <button class="modal__close" type="button" data-modal-close><i class="fa-solid fa-xmark"></i></button>
</div>

<!-- JSON Data Source cho TableManager -->

<?php $layout->start("scripts") ?>
<script type="application/json" data-tm-data="students_table">
  <?= json_encode(['rows' => $studentsData]) ?>
</script>
<script src="<?= url('public/js/pages/teacher_batch_detail.js') ?>" type="module"></script>
<?php $layout->end() ?>
