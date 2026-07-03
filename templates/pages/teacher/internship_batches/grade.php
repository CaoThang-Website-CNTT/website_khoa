<?php

/**
 * View: Chấm điểm thực tập (Teacher)
 * Route: /teacher/internship_batches/{batchId}/grade/{batchStudentId}
 */

$batchId = $batchId ?? null;
$batchStudentId = $batchStudentId ?? null;
$data = $data ?? [];
$canGrade = $canGrade ?? false;

$student = $data['student'] ?? null;
$submissions = $data['submissions'] ?? [];
$allSubmissions = $data['all_submissions'] ?? [];
$grade = $data['grade'] ?? null;
$deadline = $data['deadline'] ?? null;
$timeline = $timeline ?? [];

$historyByType = [];
foreach ($allSubmissions as $sub) {
  $type = $sub['type'] ?? 'internship_report';
  if (!isset($historyByType[$type])) {
    $historyByType[$type] = [];
  }
  $historyByType[$type][] = $sub;
}

$companyName = $student['company_name'] ?? 'Chưa có thông tin';
?>

<?php
$statusIcons = [
  'submitted' => '<i class="fa-solid fa-check-circle text-lg" style="color: var(--toast-success-color);"></i>',
  'late'      => '<i class="fa-solid fa-triangle-exclamation text-lg" style="color: var(--toast-warning-color);"></i>',
  'exempt'    => '<i class="fa-solid fa-circle-minus text-lg" style="color: var(--muted-foreground);"></i>',
  'missing'   => '<i class="fa-solid fa-xmark-circle text-lg" style="color: var(--destructive);"></i>',
  'current'   => '<i class="fa-solid fa-hourglass-half text-lg" style="color: var(--primary);"></i>',
  'future'    => '<i class="fa-solid fa-calendar-minus text-lg" style="color: var(--primary-alt);"></i>'
];

$statusLabels = [
  'submitted' => '<span class="badge" data-variant="success">Đã nộp</span>',
  'late'      => '<span class="badge" data-variant="warning">Nộp muộn</span>',
  'exempt'    => '<span class="badge" data-variant="secondary">Nghỉ</span>',
  'missing'   => '<span class="badge" data-variant="destructive">Chưa nộp</span>',
  'current'   => '<span class="badge" data-variant="primary">Tuần hiện tại</span>',
  'future'    => '<span class="badge" data-variant="outline">Chưa đến</span>'
];
?>

<link rel="stylesheet" href="<?= url('public/css/teacher_grading.css') ?>">

<?php $layout->start("heading") ?>
<h2 class="title-wrapper__title">
  Chấm điểm: <?= htmlspecialchars($student['full_name'] ?? '') ?>
  </h2>
  <div class="title-wrapper__description flex items-center">
    MSSV: <?= htmlspecialchars($student['student_code'] ?? '--') ?> - Công ty: <?= htmlspecialchars($companyName) ?>
  </div>
  <?php $layout->end() ?>

  <?php $layout->start("actions") ?>
  <a href="<?= url("teacher/internship_batches/{$batchId}") ?>" data-variant="outline" data-size="md" class="btn">
    <i class="fa-solid fa-chevron-left"></i>
    Quay lại
  </a>
  <?php $layout->end() ?>
  <div class="detail-layout">
    <!-- CỘT CHÍNH (TRÁI) -->
    <div class="detail-layout__main">
      <div class="viewer-tabs">
        <?php if (empty($submissions)): ?>
          <button class="tab-btn active">Không có tài liệu</button>
        <?php else: ?>
          <?php
          $typeLabels = [
            'internship_report' => 'Báo cáo TT',
            'evaluation_form' => 'Phiếu đánh giá',
            'company_survey' => 'Khảo sát DN',
            'related_photo' => 'Ảnh khác'
          ];
          $isFirst = true;
          foreach ($submissions as $sub):
            $type = $sub['type'] ?? 'internship_report';
            ?>
            <button class="tab-btn <?= $isFirst ? 'active' : '' ?>" data-target="viewer-<?= $sub['id'] ?>">
              <?= $typeLabels[$type] ?? 'Tài liệu' ?>
            </button>
            <?php
            $isFirst = false;
          endforeach; ?>
        <?php endif; ?>
        
        <?php if (!empty($timeline)): ?>
          <button class="tab-btn <?= empty($submissions) ? 'active' : '' ?>" data-target="viewer-weekly-reports">
            Báo cáo tuần
          </button>
        <?php endif; ?>
      </div>

      <div class="viewer-content">
        <?php if (empty($submissions)): ?>
          <div class="empty-state h-full flex flex-col items-center justify-center"
            style="color: var(--muted-foreground);">
            <i class="fa-solid fa-file-circle-xmark text-4xl mb-4"></i>
            <p>Sinh viên chưa nộp bất kỳ tài liệu nào.</p>
          </div>
        <?php else: ?>
          <?php
          $isFirst = true;
          foreach ($submissions as $sub):
            $type = $sub['type'] ?? 'internship_report';
            $history = $historyByType[$type] ?? [];
            ?>
            <div id="viewer-<?= $sub['id'] ?>" class="viewer-pane <?= $isFirst ? 'active' : '' ?>">
              <?php if ($type === 'related_photo'): ?>
                <!-- Hiển thị toàn bộ ảnh -->
                <div class="image-gallery p-4 h-full">
                  <?php foreach ($history as $imgSub):
                    $imgUrl = url("api/v1/teacher/submissions/{$imgSub['id']}/preview");
                    ?>
                    <div class="image-gallery__item mb-6">
                      <div class="mb-2 text-sm font-medium" style="color: var(--muted-foreground);">
                        <i class="fa-regular fa-clock mr-1"></i> <?= date('d/m/Y H:i', strtotime($imgSub['submitted_at'])) ?>
                        <span class="badge"
                          data-variant="secondary"><?= htmlspecialchars($imgSub['original_file_name']) ?></span>
                      </div>
                      <img src="<?= $imgUrl ?>" class="js-lightbox-trigger image-gallery__img shadow-sm border"
                        alt="<?= htmlspecialchars($imgSub['original_file_name']) ?>">
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <?php if (count($history) > 1): ?>
                  <div class="version-selector p-2 flex items-center justify-between">
                    <span class="text-sm font-medium" style="color: var(--muted-foreground)"><i
                        class="fa-solid fa-clock-rotate-left mr-1"></i> Lịch sử nộp:</span>
                    <select class="field__input js-version-select" data-target="iframe-<?= $sub['id'] ?>">
                      <?php foreach ($history as $index => $hSub):
                        $hUrl = url("api/v1/teacher/submissions/{$hSub['id']}/preview");
                        ?>
                        <option value="<?= $hUrl ?>">
                          <?= date('d/m/Y H:i', strtotime($hSub['submitted_at'])) ?>           <?= $index === 0 ? '(Bản mới nhất)' : '' ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                <?php endif; ?>
                <iframe id="iframe-<?= $sub['id'] ?>" src="<?= url("api/v1/teacher/submissions/{$sub['id']}/preview") ?>"
                  class="pdf-viewer" frameborder="0"
                  style="<?= count($history) > 1 ? 'height: calc(100% - 45px);' : 'height: 100%;' ?>"></iframe>
              <?php endif; ?>
            </div>
            <?php
            $isFirst = false;
          endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($timeline)): ?>
        <div id="viewer-weekly-reports" class="viewer-pane <?= empty($submissions) ? 'active' : '' ?>">
          <div class="h-full p-4">
            <div class="card shadow">
              <div class="card__header">
                <h3 class="card__title font-semibold">
                  <i class="fa-solid fa-list mr-2"></i>Tiến độ báo cáo tuần
                </h3>
              </div>
              <hr class="separator" />
              <div class="card__content p-4">
                <?php
                $reportStats = ['submitted' => 0, 'late' => 0, 'missing' => 0, 'exempt' => 0];
                foreach ($timeline['weeks'] as $w) {
                  if (isset($reportStats[$w['status']])) {
                    $reportStats[$w['status']]++;
                  }
                }
                ?>
                <div class="flex flex-wrap justify-center gap-4 mb-6 p-4 rounded-lg border">
                  <div class="flex items-center gap-2">
                    <i class="fa-solid fa-calendar-week" style="color: var(--primary);"></i>
                    <span class="text-sm font-medium">Tổng số: <span class="font-bold"><?= count($timeline['weeks']) ?> tuần</span></span>
                  </div>
                  <div class="flex items-center gap-2">
                    <i class="fa-solid fa-check-circle" style="color: var(--toast-success-color);"></i>
                    <span class="text-sm font-medium">Đã nộp: <span class="font-bold"><?= $reportStats['submitted'] + $reportStats['late'] ?></span></span>
                  </div>
                  <div class="flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation" style="color: var(--toast-warning-color);"></i>
                    <span class="text-sm font-medium">Nộp muộn: <span class="font-bold"><?= $reportStats['late'] ?></span></span>
                  </div>
                  <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-xmark" style="color: var(--toast-error-color);"></i>
                    <span class="text-sm font-medium">Chưa nộp: <span class="font-bold"><?= $reportStats['missing'] ?></span></span>
                  </div>
                  <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-minus" style="color: var(--muted-foreground);"></i>
                    <span class="text-sm font-medium">Nghỉ: <span class="font-bold"><?= $reportStats['exempt'] ?></span></span>
                  </div>
                </div>

                <div class="weekly-timeline">
                  <?php foreach ($timeline['weeks'] as $w): ?>
                    <div class="weekly-timeline__item">
                      <div class="weekly-timeline__icon">
                        <?= $statusIcons[$w['status']] ?>
                      </div>
                      <div class="weekly-timeline__content">
                        <div class="flex justify-between items-center mb-1 weekly-timeline__header">
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
                          <div class="weekly-timeline__details hidden mt-3">
                            <div class="rounded-md p-4 text-sm border shadow-sm">
                              <?php if (!$w['report']['is_exempt']): ?>
                                <div class="mb-4"><?= nl2br(htmlspecialchars($w['report']['content'])) ?></div>

                                <?php if (!empty($w['report']['images'])): ?>
                                  <div class="mb-2">
                                    <div class="text-xs font-semibold mb-2">Hình ảnh đính kèm:</div>
                                    <div class="flex flex-wrap gap-2">
                                      <?php foreach ($w['report']['images'] as $img): ?>
                                        <img src="<?= url('public/media/' . $img['file_path']) ?>" alt="Đính kèm" class="object-cover border rounded-md js-lightbox-trigger weekly-timeline__img" title="<?= htmlspecialchars($img['original_file_name']) ?>">
                                      <?php endforeach; ?>
                                    </div>
                                  </div>
                                <?php endif; ?>
                              <?php else: ?>
                                <div class="mb-2">Không thực tập trong tuần này.</div>
                              <?php endif; ?>

                              <div class="text-xs flex items-center mt-3 pt-4">
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
          </div>
        </div>
      <?php endif; ?>
      </div>
    </div>

    <!-- SIDEBAR -->
    <div class="detail-layout__sidebar">
      <div class="card shadow h-full flex flex-col">
        <div class="card__header">
          <h3 class="card__title">Đánh giá</h3>
          <?php if (!$canGrade): ?>
            <div class="mt-2 text-sm text-destructive"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Đã hết hạn
              chấm điểm.</div>
          <?php elseif ($deadline): ?>
            <div class="mt-2 text-sm text-warning"><i class="fa-solid fa-clock mr-1"></i> Hạn chót:
              <?= date('d/m/Y', strtotime($deadline)) ?></div>
          <?php endif; ?>
        </div>
        <hr class="separator" />

        <div class="card__content flex-1">
          <?php $isLocked = !empty($grade['grade_lock_at']); ?>
          <form action="<?= url("teacher/internship_batches/{$batchId}/grade/{$batchStudentId}") ?>" method="POST"
            id="gradingForm">
            <?= csrf_field() ?>

            <div class="field mb-4" data-field-required>
              <label class="field__label">Điểm tổng kết</label>
              <input type="number" name="score" class="field__input score-input" step="0.25" min="0" max="10"
                value="<?= $grade['final_score'] ?? '' ?>" <?= (!$canGrade || $isLocked) ? 'disabled' : 'required' ?>>
            </div>

            <div class="field mb-4">
              <label class="field__label">Diễn giải điểm</label>
              <textarea name="score_reason" class="field__input" rows="3" <?= (!$canGrade || $isLocked) ? 'disabled' : '' ?>
                placeholder="VD: Báo cáo: 5đ, Chuyên cần: 2đ, Điểm doanh nghiệp: 3đ"><?= htmlspecialchars($grade['score_reason'] ?? '') ?></textarea>
            </div>

            <div class="field mb-4">
              <label class="field__label">Nhận xét của GVHD</label>
              <textarea name="feedback" class="field__input" rows="5" <?= (!$canGrade || $isLocked) ? 'disabled' : '' ?>><?= htmlspecialchars($grade['feedback'] ?? '') ?></textarea>
            </div>
          </form>
        </div>

        <?php if ($canGrade && !$isLocked): ?>
          <hr class="separator" />
          <div class="card__footer p-4">
            <button type="submit" name="action" value="draft" form="gradingForm" class="btn w-full" data-variant="primary" data-size="lg">
              <i class="fa-solid fa-save mr-2"></i>Lưu điểm (Nháp)
            </button>
          </div>
        <?php elseif ($isLocked): ?>
          <hr class="separator" />
          <div class="card__footer p-4">
            <div class="alert text-center" data-variant="success">
              <i class="fa-solid fa-circle-check mr-1"></i> Điểm đã được chốt và công bố
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Lightbox Overlay -->
  <div id="lightbox" class="lightbox-overlay hidden">
    <div class="lightbox-close" title="Đóng"><i class="fa-solid fa-xmark"></i></div>
    <img id="lightbox-img" src="" alt="Phóng to">
  </div>

  <?php $layout->start("scripts") ?>
  <script src="<?= url('public/js/pages/teacher_grading.js') ?>" type="module"></script>
  <?php $layout->end() ?>
