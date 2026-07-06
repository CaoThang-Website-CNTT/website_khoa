<?php
$layout->start("head");
?>
<link rel="stylesheet" href="<?= url('public/css/teacher_weekly_reports.css') ?>">
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
  $student['grading_url'] = url("teacher/internship_batches/{$batch['id']}/grade/{$student['batch_student_id']}") . '#teacher-grading:weekly';

  $processedData[] = $student;
}
?>

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">Báo cáo tuần</h2>
<p class="title-wrapper__description flex flex-wrap items-center gap-2">
  <span class="font-semibold"><?= htmlspecialchars($batch['title']) ?></span>
  <span aria-hidden="true">•</span>
  <span>Tuần <?= $current_week ?></span>
  <span aria-hidden="true">•</span>
  <span><?= date('d/m/Y', strtotime($weeks_data[$current_week - 1]['start'])) ?> –
    <?= date('d/m/Y', strtotime($weeks_data[$current_week - 1]['end'])) ?></span>
</p>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url("teacher/internship_batches/{$batch['id']}") ?>" class="btn" data-variant="outline" data-size="md">
  <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại
</a>
<?php $layout->end() ?>

<?php $layout->start("content") ?>

<!-- Phần chọn tuần -->
<div class="flex flex-col md:flex-row md:items-end gap-3 mb-4">
  <a href="<?= $prevWeek ? '?week=' . $prevWeek : '#' ?>"
    class="btn w-full md:w-fit <?= !$prevWeek ? 'disabled' : '' ?>" <?= !$prevWeek ? 'aria-disabled="true" tabindex="-1"' : '' ?> data-variant="outline" data-size="md">
    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Tuần trước
  </a>

  <div class="field mb-0 w-full flex-1 min-w-0">
    <button type="button" class="select w-full" data-select-id="teacher-weekly-report-week"
      data-select-default-value="<?= $current_week ?>" data-select-placeholder="Chọn tuần" role="listbox"
      aria-labelledby="weekly-report-week-label">
      <div class="select__content">
        <?php foreach ($weeks_data as $w): ?>
          <div class="select__item" data-select-value="<?= $w['week_number'] ?>">
            Tuần <?= $w['week_number'] ?> (<?= date('d/m/Y', strtotime($w['start'])) ?> -
            <?= date('d/m/Y', strtotime($w['end'])) ?>)
          </div>
        <?php endforeach; ?>
      </div>
    </button>
  </div>

  <a href="<?= $nextWeek ? '?week=' . $nextWeek : '#' ?>"
    class="btn w-full md:w-fit <?= !$nextWeek ? 'disabled' : '' ?>" <?= !$nextWeek ? 'aria-disabled="true" tabindex="-1"' : '' ?> data-variant="outline" data-size="md">
    Tuần sau <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
  </a>
</div>

<!-- Phần thống kê -->
<div class="stats-grid mb-4">
  <div class="card stats-card">
    <div class="card__header"><span class="stats-card__label">Tổng sinh viên</span><span
        class="stats-card__value"><?= $totalStudents ?></span></div>
  </div>
  <div class="card stats-card">
    <div class="card__header"><span class="stats-card__label">Đã nộp</span><span
        class="stats-card__value"><?= $submitted + $late ?></span></div>
  </div>
  <div class="card stats-card">
    <div class="card__header"><span class="stats-card__label">Nộp muộn</span><span
        class="stats-card__value"><?= $late ?></span></div>
  </div>
  <div class="card stats-card">
    <div class="card__header"><span class="stats-card__label">Chưa nộp</span><span
        class="stats-card__value"><?= $missing ?></span></div>
  </div>
</div>

<!-- Bảng thông tin -->
<div class="overflow-x-auto">
  <div class="tm-container" data-tm="weekly_reports_table" data-tm-mode="client" data-tm-searchable="true"
    data-tm-id-key="batch_student_id">

    <!-- Cột MSSV -->
    <template data-tm-col="student_code" data-tm-label="MSSV" data-tm-filter-type="text" data-tm-sortable
      data-tm-width="120px">
      <span class="font-medium">{{ value }}</span>
    </template>

    <!-- Cột Họ tên -->
    <template data-tm-col="full_name" data-tm-label="Họ tên" data-tm-filter-type="text" data-tm-sortable
      data-tm-width="240px">
      <div class="font-medium">{{ value }}</div>
      <div class="text-xs text-muted-foreground">{{ row.classroom_name }}</div>
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
    <template data-tm-col="image_count" data-tm-label="Số ảnh" data-tm-sortable data-tm-width="100px"
      data-tm-align="center">
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
    <template data-tm-col="status_key" data-tm-label="Trạng thái" data-tm-filter-type="select"
      data-tm-filter-options='[{"label":"Đã nộp", "value":"submitted"}, {"label":"Nộp muộn", "value":"late"}, {"label":"Nghỉ", "value":"exempt"}, {"label":"Chưa nộp", "value":"missing"}, {"label":"Tuần hiện tại", "value":"current"}, {"label":"Chưa đến", "value":"future"}]'
      data-tm-sortable data-tm-width="140px">
      <span class="badge"
        data-variant="{{ value === 'submitted' ? 'success' : (value === 'late' ? 'warning' : (value === 'exempt' ? 'secondary' : (value === 'missing' ? 'destructive' : (value === 'current' ? 'primary' : 'outline')))) }}">
        {{ value === 'submitted' ? 'Đã nộp' : (value === 'late' ? 'Nộp muộn' : (value === 'exempt' ? 'Nghỉ' : (value ===
        'missing' ? 'Chưa nộp' : (value === 'current' ? 'Tuần hiện tại' : 'Chưa đến')))) }}
      </span>
    </template>

    <!-- Cột Thao tác -->
    <template data-tm-col="actions" data-tm-label="Thao tác" data-tm-width="150px" data-tm-align="right">
      <a href="{{ row.grading_url }}" class="btn" data-variant="outline" data-size="sm">
        <i class="fa-solid fa-eye" aria-hidden="true"></i> Xem báo cáo
      </a>
    </template>

    <template data-tm-pagination></template>
  </div>
</div>

<?php $layout->end() ?>

<?php $layout->start("scripts") ?>
<script type="application/json" data-tm-data="weekly_reports_table">
  <?= json_encode(['rows' => $processedData], JSON_UNESCAPED_UNICODE) ?>
</script>
<script src="<?= url('public/js/pages/teacher_weekly_reports.js') ?>" type="module"></script>
<?php $layout->end() ?>