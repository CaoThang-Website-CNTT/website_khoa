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
    'submission_url' => url('public/media/' . $sv['submission_path']),
    'submission_count' => (int) $sv['submission_count'],
    'grade' => $sv['grade'] !== null ? (float) $sv['grade'] : null,
    'batch_student_id' => $sv['batch_student_id'],
    // Các trường hỗ trợ filter (0/1)
    'has_submission' => $sv['submission_name'] ? '1' : '0',
    'has_grade' => $sv['grade'] !== null ? '1' : '0'
  ];
}, $students);

// Tạo danh sách lớp cho filter dropdown
$classrooms = array_unique(array_filter(array_column($students, 'classroom_name')));
sort($classrooms);
$classOptions = [];
foreach ($classrooms as $c) {
  $classOptions[] = ['label' => $c, 'value' => $c];
}
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
<?php $layout->end() ?>
<div class="detail-layout">
  <!-- CỘT CHÍNH (TRÁI) -->
  <div class="detail-layout__main">

    <!-- Stats Grid -->
    <div class="stats-grid">
      <div class="stat-card card p-4">
        <div class="stat-card__icon stat-card__icon--primary">
          <i class="fa-solid fa-user-graduate"></i>
        </div>
        <div class="stat-card__info">
          <div class="stat-card__label">SV Hướng dẫn</div>
          <div class="stat-card__value"><?= number_format($stats['total_students']) ?></div>
        </div>
      </div>

      <div class="stat-card card p-4">
        <div class="stat-card__icon stat-card__icon--info">
          <i class="fa-solid fa-file-alt"></i>
        </div>
        <div class="stat-card__info">
          <div class="stat-card__label">Tài liệu đã nộp</div>
          <div class="stat-card__value">
            <?= number_format($stats['has_submission']) ?> <span class="text-sm font-normal"
              style="color: var(--muted-foreground)">/ <?= number_format($stats['total_students']) ?></span>
          </div>
        </div>
      </div>

      <div class="stat-card card p-4">
        <div class="stat-card__icon stat-card__icon--success">
          <i class="fa-solid fa-building"></i>
        </div>
        <div class="stat-card__info">
          <div class="stat-card__label">Đã có công ty</div>
          <div class="stat-card__value">
            <?= number_format($stats['has_company']) ?> <span class="text-sm font-normal"
              style="color: var(--muted-foreground)">/ <?= number_format($stats['total_students']) ?></span>
          </div>
        </div>
      </div>

      <div class="stat-card card p-4">
        <div class="stat-card__icon stat-card__icon--warning">
          <i class="fa-solid fa-star"></i>
        </div>
        <div class="stat-card__info">
          <div class="stat-card__label">Đã nhập điểm</div>
          <div class="stat-card__value">
            <?= number_format($stats['has_grade']) ?> <span class="text-sm font-normal"
              style="color: var(--muted-foreground)">/ <?= number_format($stats['total_students']) ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Sinh Viên -->
    <div class="card shadow-sm">
      <div class="card__header">
        <h3 class="font-semibold">Danh sách sinh viên hướng dẫn</h3>
      </div>

      <hr class="separator">
      <div class="card__content p-4">
        <div class="tm-container" data-tm="students_table" data-tm-mode="client" data-tm-searchable>
          <!-- Cột MSSV -->
          <template data-tm-col="student_code" data-tm-label="MSSV" data-tm-width="120px" data-tm-sortable
            data-tm-filter-type="text">
            <span class="font-medium text-md">{{ value }}</span>
          </template>

          <!-- Cột Họ và tên -->
          <template data-tm-col="full_name" data-tm-label="Họ và tên" data-tm-sortable data-tm-filter-type="text">
            <span class="student-table__name">{{ value }}</span>
          </template>

          <!-- Cột Lớp -->
          <template data-tm-col="classroom_name" data-tm-label="Lớp" data-tm-width="120px" data-tm-sortable
            data-tm-filter-type="select" data-tm-filter-options='<?= json_encode($classOptions) ?>'>
            {{ value || '-' }}
          </template>

          <!-- Cột Công ty -->
          <template data-tm-col="company_name" data-tm-label="Công ty TT" data-tm-sortable>
            <div>
              <span class="badge" data-variant="secondary" style="{{ value ? 'display:none' : '' }}">Chưa có</span>
              <span title="{{ value }}" style="{{ value ? '' : 'display:none' }}">{{ value }}</span>
            </div>
          </template>

          <!-- Cột Tài liệu -->
          <template data-tm-col="has_submission" data-tm-label="Tài liệu TT">
            <span class="badge" data-variant="secondary" style="{{ value === '1' ? 'display:none' : '' }}">Chưa
              nộp</span>
            <div style="{{ value === '1' ? 'display:inline-flex' : 'display:none' }}" class="student-table__submission">
              <span class="student-table__filename text-sm" title="{{ row.submission_name }}"
                style="color: var(--primary)">
                {{ row.submission_name && row.submission_name.length > 20 ? row.submission_name.substring(0, 17) + '...'
                : row.submission_name }}
              </span>
              <a href="{{ row.submission_url }}" target="_blank" class="btn" data-variant="primary" data-size="sm"
                title="Tải xuống" style="{{ row.submission_url ? '' : 'display:none' }}">
                <i class="fa-solid fa-download"></i>
              </a>
              <span class="badge ml-1" data-variant="primary"
                style="{{ row.submission_count > 1 ? '' : 'display:none' }}; font-size: 10px; height: 16px; padding: 0 4px;">
                +{{ row.submission_count - 1 }}
              </span>
            </div>
          </template>

          <!-- Cột Điểm -->
          <template data-tm-col="has_grade" data-tm-label="Điểm" data-tm-width="100px" data-tm-sortable
            data-tm-align="center">
            <span class="badge" data-variant="secondary" style="{{ value === '1' ? 'display:none' : '' }}">Chưa
              nhập</span>
            <span class="font-bold {{ row.grade >= 5 ? 'text-success' : 'text-danger' }}"
              style="{{ value === '1' ? '' : 'display:none' }}">
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
              <button class="btn btn-icon" data-variant="outline-alt" data-size="sm" title="Nhập điểm"
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
      <div class="card__content p-6">
        <div class="field-group">
          <div class="field" data-field-readonly>
            <label class="field__label" for="title">Tên đợt thực tập</label>
            <input type="text" id="title" name="title" class="field__input"
              value="<?= htmlspecialchars($batch['title']) ?>" required>
          </div>

          <div class="field" data-field-readonly>
            <label class="field__label" for="description">Mô tả</label>
            <textarea id="description" name="description" class="field__input"
              rows="6"><?= htmlspecialchars($batch['description'] ?? '') ?></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="field" data-field-readonly>
              <label class="field__label" for="start_at">Ngày bắt đầu</label>
              <input type="date" id="start_at" name="start_at" class="field__input"
                value="<?= date('Y-m-d', strtotime($batch['start_at'])) ?>" required>
            </div>
            <div class="field" data-field-readonly>
              <label class="field__label" for="end_at">Ngày kết thúc</label>
              <input type="date" id="end_at" name="end_at" class="field__input"
                value="<?= date('Y-m-d', strtotime($batch['end_at'])) ?>" required>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Thông tin thời gian -->
    <div class="card shadow-sm">
      <div class="card__header">
        <h3 class="font-semibold">Thông tin khác</h3>
      </div>
      <hr class="separator">
      <div class="card__content p-4 text-xs space-y-3">
        <div class="flex justify-between">
          <span>ID:</span>
          <span class="font-medium">#<?= $batch['id'] ?></span>
        </div>
        <div class="flex justify-between">
          <span>Ngày tạo:</span>
          <span><?= $batch['created_at'] ? date('d/m/Y H:i', strtotime($batch['created_at'])) : 'N/A' ?></span>
        </div>
        <?php if ($batch['published_at']): ?>
          <div class="flex justify-between">
            <span>Ngày công bố:</span>
            <span><?= $batch['published_at'] ? date('d/m/Y H:i', strtotime($batch['published_at'])) : 'N/A' ?></span>
          </div>
        <?php endif; ?>
        <?php if ($batch['closed_at']): ?>
          <div class="flex justify-between">
            <span>Ngày kết thúc:</span>
            <span
              class="text-danger"><?= $batch['closed_at'] ? date('d/m/Y H:i', strtotime($batch['closed_at'])) : 'N/A' ?></span>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- JSON Data Source cho TableManager -->
<script type="application/json" data-tm-data="students_table">
  <?= json_encode(['rows' => $studentsData]) ?>
</script>

<script src="<?= url('public/js/pages/teacher_batch_detail.js') ?>" type="module"></script>
