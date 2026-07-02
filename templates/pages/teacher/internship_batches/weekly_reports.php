<?php
$layout->start("head");
?>
<link rel="stylesheet" href="<?= url('public/css/teacher_weekly_reports.css') ?>">
<link rel="stylesheet" href="<?= url('public/css/batch_students_assignment.css') ?>">
<?php $layout->end(); ?>

<?php
// Determine the prev and next week
$prevWeek = $current_week > 1 ? $current_week - 1 : null;
$nextWeek = $current_week < count($weeks_data) ? $current_week + 1 : null;

// Thống kê nhanh tuần này
$totalStudents = $reports_data->getTotal();
$submitted = 0;
$late = 0;
$exempt = 0;
$missing = 0;

$processedData = [];

foreach ($reports_data->getItems() as $student) {
  if ($student['report_id'] === null) {
    $missing++;
  } else if ($student['is_exempt']) {
    $exempt++;
  } else if ($student['is_late']) {
    $late++;
  } else {
    $submitted++;
  }

  $statusKey = 'missing';
  if ($student['report_id']) {
    $statusKey = $student['is_exempt'] ? 'exempt' : ($student['is_late'] ? 'late' : 'submitted');
  } else if ($weeks_data[$current_week - 1]['status'] === 'future') {
    $statusKey = 'future';
  } else if ($weeks_data[$current_week - 1]['status'] === 'current') {
    $statusKey = 'current';
  }
  $student['status_key'] = $statusKey;
  $student['submitted_at_formatted'] = $student['submitted_at'] ? date('d/m/Y H:i', strtotime($student['submitted_at'])) : '';

  $processedData[] = $student;
}
?>

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">Báo cáo hàng tuần: Tuần <?= $current_week ?></h2>
<p class="title-wrapper__description">Đợt thực tập: <span class="font-semibold"><?= htmlspecialchars($batch['title']) ?></span></p>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url("teacher/internship_batches/{$batch['id']}") ?>" class="btn" data-variant="outline" data-size="md">
  <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại
</a>
<?php $layout->end() ?>

<?php $layout->start("content") ?>

<!-- Phần chọn tuần -->
<div class="flex items-center justify-center gap-4">
  <a href="<?= $prevWeek ? '?week=' . $prevWeek : '#' ?>" class="btn btn-icon <?= !$prevWeek ? 'disabled' : '' ?>" <?= !$prevWeek ? 'disabled' : '' ?> data-variant="outline" data-size="md">
    <i class="fa-solid fa-chevron-left"></i>
  </a>

  <div class="field mb-0 w-full flex-1">
    <select class="field__input text-center font-medium" onchange="window.location.href='?week='+this.value">
      <?php foreach ($weeks_data as $w): ?>
        <option value="<?= $w['week_number'] ?>" <?= $w['week_number'] == $current_week ? 'selected' : '' ?>>
          Tuần <?= $w['week_number'] ?> (<?= date('d/m/Y', strtotime($w['start'])) ?> - <?= date('d/m/Y', strtotime($w['end'])) ?>)
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <a href="<?= $nextWeek ? '?week=' . $nextWeek : '#' ?>" class="btn btn-icon <?= !$nextWeek ? 'disabled' : '' ?>" <?= !$nextWeek ? 'disabled' : '' ?> data-variant="outline" data-size="md">
    <i class="fa-solid fa-chevron-right"></i>
  </a>
</div>

<!-- Phần thống kê -->
<div class="assignment-stats">
  <div class="stat-box stat-box__primary shadow-sm">
    <div class="stat-box__icon">
      <i class="fa-solid fa-users"></i>
    </div>
    <div class="stat-box__content">
      <span class="stat-box__label">Tổng SV hướng dẫn</span>
      <div class="stat-box__value"><?= $totalStudents ?></div>
    </div>
  </div>

  <div class="stat-box stat-box__success shadow-sm">
    <div class="stat-box__icon">
      <i class="fa-solid fa-check-circle"></i>
    </div>
    <div class="stat-box__content">
      <span class="stat-box__label">Đã nộp</span>
      <div class="stat-box__value text-success"><?= $submitted + $late ?></div>
    </div>
  </div>

  <div class="stat-box stat-box__warning shadow-sm">
    <div class="stat-box__icon">
      <i class="fa-solid fa-triangle-exclamation"></i>
    </div>
    <div class="stat-box__content">
      <span class="stat-box__label">Nộp muộn</span>
      <div class="stat-box__value text-warning"><?= $late ?></div>
    </div>
  </div>

  <div class="stat-box shadow-sm">
    <div class="stat-box__icon" style="background-color: var(--destructive); color: white;">
      <i class="fa-solid fa-xmark-circle"></i>
    </div>
    <div class="stat-box__content">
      <span class="stat-box__label">Chưa nộp</span>
      <div class="stat-box__value text-destructive"><?= $missing ?></div>
    </div>
  </div>
</div>

<!-- Bảng thông tin -->
<div class="card shadow-sm">
  <div class="tm-container" data-tm="weekly_reports_table" data-tm-mode="client" data-tm-searchable="true" data-tm-id-key="batch_student_id">

    <!-- Cột MSSV -->
    <template data-tm-col="student_code" data-tm-label="MSSV" data-tm-filter-type="text" data-tm-sortable data-tm-width="120px">
      <span class="font-medium">{{ value }}</span>
    </template>

    <!-- Cột Họ tên -->
    <template data-tm-col="full_name" data-tm-label="Họ tên" data-tm-filter-type="text" data-tm-sortable data-tm-width="240px">
      <div class="font-medium">{{ value }}</div>
      <div class="text-xs">{{ row.classroom_name }}</div>
    </template>

    <!-- Cột Nội dung công việc -->
    <template data-tm-col="content" data-tm-label="Nội dung công việc">
      <div class="w-full">
        <!-- Khi Không có report -->
        <div style="display: {{ row.report_id ? 'none' : 'block' }}">
          <span>Chưa nộp báo cáo</span>
        </div>

        <!-- Khi không có thực tập -->
        <div style="display: {{ row.report_id ? (row.is_exempt ? 'block' : 'none') : 'none' }}">
          <span>Không thực tập trong tuần</span>
        </div>

        <!-- Khi có report -->
        <div style="display: {{ row.report_id ? (row.is_exempt ? 'none' : 'block') : 'none' }}">
          <div class="report-content-preview">{{ value }}</div>
        </div>
      </div>
    </template>

    <!-- Cột Số ảnh -->
    <template data-tm-col="image_count" data-tm-label="Số ảnh" data-tm-sortable data-tm-width="100px" data-tm-align="center">
      <div style="display: {{ row.report_id ? (value ? 'block' : 'none') : 'none' }}">
        <span class="badge" data-variant="secondary">
          <i class="fa-regular fa-image mr-1"></i> {{ value }} ảnh
        </span>
      </div>
      <div style="display: {{ row.report_id ? (value ? 'none' : 'block') : 'block' }}">
        0
      </div>
    </template>

    <!-- Cột Trạng thái -->
    <template data-tm-col="status_key" data-tm-label="Trạng thái" data-tm-filter-type="select" data-tm-filter-options='[{"label":"Đã nộp", "value":"submitted"}, {"label":"Nộp muộn", "value":"late"}, {"label":"Nghỉ", "value":"exempt"}, {"label":"Chưa nộp", "value":"missing"}, {"label":"Tuần hiện tại", "value":"current"}, {"label":"Chưa đến", "value":"future"}]' data-tm-sortable data-tm-width="140px">
      <div style="display: {{ value === 'submitted' ? 'block' : 'none' }}">
        <span class="badge" data-variant="success"><i class="fa-solid fa-check-circle"></i><span class="text-xs">Đã nộp</span></span>
      </div>
      <div style="display: {{ value === 'late' ? 'block' : 'none' }}">
        <span class="badge" data-variant="warning"><i class="fa-solid fa-triangle-exclamation"></i><span class="text-xs">Nộp muộn</span></span>
      </div>
      <div style="display: {{ value === 'exempt' ? 'block' : 'none' }}">
        <span class="badge" data-variant="secondary"><i class="fa-solid fa-circle-minus"></i><span class="text-xs">Nghỉ</span></span>
      </div>
      <div style="display: {{ value === 'missing' ? 'block' : 'none' }}">
        <span class="badge" data-variant="destructive"><i class="fa-solid fa-xmark-circle"></i><span class="text-xs">Chưa nộp</span></span>
      </div>
      <div style="display: {{ value === 'current' ? 'block' : 'none' }}">
        <span class="badge" data-variant="primary"><i class="fa-solid fa-hourglass-half"></i><span class="text-xs">Tuần hiện tại</span></span>
      </div>
      <div style="display: {{ value === 'future' ? 'block' : 'none' }}">
        <span class="badge" data-variant="outline"><i class="fa-solid fa-calendar-minus"></i><span class="text-xs">Chưa đến</span></span>
      </div>
    </template>

    <!-- Cột Thao tác -->
    <template data-tm-col="actions" data-tm-label="Thao tác" data-tm-width="80px" data-tm-align="right">
      <button class="btn btn-icon" data-variant="outline" data-size="sm" title="Xem ảnh">
        <i class="fa-solid fa-eye"></i>
      </button>
    </template>

    <template data-tm-pagination></template>
  </div>
</div>

<?php $layout->end() ?>

<?php $layout->start("scripts") ?>
<script type="application/json" data-tm-data="weekly_reports_table">
  <?= json_encode(['rows' => $processedData], JSON_UNESCAPED_UNICODE) ?>
</script>
<?php $layout->end() ?>