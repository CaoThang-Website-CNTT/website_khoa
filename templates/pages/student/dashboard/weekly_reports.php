<?php
$layout->start("head");
?>
<link rel="stylesheet" href="<?= url('public/css/student_weekly_reports.css') ?>">
<?php $layout->end(); ?>

<?php
$statusIcons = [
  'submitted' => '<i class="fa-solid fa-check-circle" style="color: var(--toast-success-color);"></i>',
  'late'      => '<i class="fa-solid fa-triangle-exclamation" style="color: var(--toast-warning-color);"></i>',
  'exempt'    => '<i class="fa-solid fa-circle-minus" style="color: var(--muted-foreground);"></i>',
  'missing'   => '<i class="fa-solid fa-xmark-circle" style="color: var(--destructive);"></i>',
  'current'   => '<i class="fa-solid fa-hourglass-half" style="color: var(--primary);"></i>',
  'future'    => '<i class="fa-solid fa-calendar-minus" style="color: var(--primary-alt);"></i>'
];

$statusLabels = [
  'submitted' => '<span class="badge" data-variant="success">Đã nộp</span>',
  'late'      => '<span class="badge" data-variant="warning">Nộp muộn</span>',
  'exempt'    => '<span class="badge" data-variant="secondary">Nghỉ</span>',
  'missing'   => '<span class="badge" data-variant="destructive">Chưa nộp</span>',
  'current'   => '<span class="badge" data-variant="primary">Tuần hiện tại</span>',
  'future'    => '<span class="badge" data-variant="outline">Chưa đến</span>'
];

// Determine the default selected week (current week or the first missing week)
$defaultWeek = 1;
foreach ($weeks_data as $w) {
  if ($w['status'] === 'current') {
    $defaultWeek = $w['week_number'];
    break;
  }
}
?>

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">Báo cáo công việc hàng tuần</h2>
<p class="title-wrapper__description">Đợt thực tập: <span class="font-semibold"><?= htmlspecialchars($current['title']) ?></span></p>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url("student/internship/{$current['id']}") ?>" class="btn" data-variant="outline" data-size="md">
  <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại Thông tin thực tập
</a>
<?php $layout->end() ?>

<?php $layout->start("content") ?>

<div class="card shadow">
  <div class="card__header">
    <h3 class="card__title">Nộp báo cáo tuần</h3>
  </div>
  <hr class="separator" />
  <div class="card__content">
    <form action="<?= url("student/internship/{$current['id']}/weekly_reports") ?>" method="POST" enctype="multipart/form-data" id="weeklyReportForm">
      <?= csrf_field() ?>

      <div class="field mb-4">
        <label class="field__label">Chọn tuần báo cáo</label>
        <select name="week_number" id="week_number" class="field__input" required>
          <?php foreach ($weeks_data as $w): ?>
            <?php if ($w['status'] !== 'future'): ?>
              <option value="<?= $w['week_number'] ?>" <?= $w['week_number'] === $defaultWeek ? 'selected' : '' ?>>
                Tuần <?= $w['week_number'] ?> (<?= date('d/m/Y', strtotime($w['start'])) ?> - <?= date('d/m/Y', strtotime($w['end'])) ?>)
                - <?= strip_tags($statusLabels[$w['status']]) ?>
              </option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field mb-4" data-orientation="horizontal">
        <input type="checkbox" id="is_exempt" name="is_exempt" value="1" class="field__input">
        <label for="is_exempt" class="field__label">Tuần này em không thực tập (chưa bắt đầu, nghỉ phép hoặc đã kết thúc sớm)</label>
      </div>

      <div id="reportContentSection">
        <div class="field mb-4" data-field-required>
          <label class="field__label">Nội dung công việc</label>
          <textarea name="content" id="report_content" class="field__input" rows="6" placeholder="Liệt kê, ghi chú các công việc đã thực hiện trong tuần..."></textarea>
        </div>

        <div class="field mb-4">
          <label class="field__label">Hình ảnh đính kèm (Không bắt buộc)</label>
          <div class="text-xs mb-2">Tối đa 5 ảnh. Dung lượng tối đa: 5MB/ảnh. Định dạng: JPG, PNG, WEBP.</div>
          <input type="file" name="images[]" id="report_images" class="field__input" accept="image/jpeg,image/png,image/webp" multiple>
          <div id="imagePreviewContainer" class="mt-3 grid grid-cols-2 md:grid-cols-3 gap-2"></div>
        </div>
      </div>

      <div class="flex justify-end mt-4">
        <button type="submit" class="btn" data-variant="primary" data-size="lg" id="submitBtn">
          <i class="fa-solid fa-paper-plane mr-2"></i> Gửi
        </button>
      </div>
    </form>
  </div>
</div>


<div class="card shadow">
  <div class="card__header">
    <h3 class="card__title">
      <i class="fa-solid fa-list mr-2"></i>
      Tiến độ báo cáo
    </h3>
  </div>
  <hr class="separator" />
  <div class="card__content">
    <div class="weekly-timeline">
      <?php foreach ($weeks_data as $w): ?>
        <div class="weekly-timeline__item">
          <div class="weekly-timeline__icon">
            <?= $statusIcons[$w['status']] ?>
          </div>
          <div class="weekly-timeline__content">
            <div class="flex justify-between items-center mb-1 weekly-timeline__header" data-week="<?= $w['week_number'] ?>">
              <h5 class="font-semibold text-sm">
                Tuần <?= $w['week_number'] ?>
                <span class="text-xs ml-1">(<?= date('d/m', strtotime($w['start'])) ?> - <?= date('d/m', strtotime($w['end'])) ?>)</span>
              </h5>
              <div class="flex items-center gap-2">
                <?= $statusLabels[$w['status']] ?>
                <?php if ($w['report']): ?>
                  <i class="fa-solid fa-chevron-down text-xs"></i>
                <?php endif; ?>
              </div>
            </div>

            <?php if ($w['report']): ?>
              <div class="weekly-timeline__details hidden" id="details-week-<?= $w['week_number'] ?>">
                <div class="rounded p-3 text-sm mt-2 border">
                  <?php if (!$w['report']['is_exempt']): ?>
                    <div class="mb-3"><?= nl2br(htmlspecialchars($w['report']['content'])) ?></div>

                    <?php if (!empty($w['report']['images'])): ?>
                      <div class="mb-3">
                        <div class="text-xs mb-2">Hình ảnh đính kèm:</div>
                        <div class="flex flex-wrap gap-2">
                          <?php foreach ($w['report']['images'] as $img): ?>
                            <a href="<?= url('public/media/' . $img['file_path']) ?>" target="_blank" class="block border rounded overflow-hidden" title="<?= htmlspecialchars($img['original_file_name']) ?>">
                              <img src="<?= url('public/media/' . $img['file_path']) ?>" alt="Đính kèm" class="object-cover">
                            </a>
                          <?php endforeach; ?>
                        </div>
                      </div>
                    <?php endif; ?>
                  <?php else: ?>
                    <div class="mb-3">Không có nội dung báo cáo.</div>
                  <?php endif; ?>

                  <div class="text-xs flex items-center mt-2 p-2 border rounded-md">
                    <span><i class="fa-regular fa-clock mr-1"></i> Nộp lúc: <?= date('d/m/Y H:i', strtotime($w['report']['submitted_at'])) ?></span>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php $layout->end() ?>

<?php $layout->start("scripts"); ?>
<script type="application/json" id="weeklyDataJson">
  <?= json_encode(array_combine(array_column($weeks_data, 'week_number'), $weeks_data)) ?>
</script>
<script src="<?= url('public/js/pages/student_weekly_reports.js') ?>"></script>
<?php $layout->end(); ?>